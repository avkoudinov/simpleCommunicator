<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
$tid = reqvar("tid");
$fid = "";

$final_url = $_SERVER['REQUEST_URI'];
$final_url = preg_replace("/&msg=\d*/", "", $final_url);
$final_url = preg_replace("/&all=\d*/", "", $final_url);
$final_url = preg_replace("/&tpage=\d*/", "", $final_url);
$final_url = preg_replace("/&startmsg=[^&]*/", "", $final_url);
$_SESSION["ensure_anchor_visible"] = "";

$gotomsg = reqvar("msg");

// This is only for backward compatibility
if (!reqvar_empty("gotomsg")) {
    $gotomsg = reqvar("gotomsg");
}

if (empty($tid) && empty($gotomsg)) {
    header("Location: forums.php");
    exit;
}

$is_pid_pinned = 0;

if (!empty($gotomsg)) {
    if (!is_numeric($gotomsg)) {
        MessageHandler::setError(sprintf(text("ErrMessageDoesNotExist"), $gotomsg));
        header("Location: forums.php");
        exit;
    }
    
    // if msg was deleted, gotomsg will change
    // set done for deleted mark
    if (!reqvar_empty("setdone") && !empty($gotomsg)) {
        $fmanager->set_post_events_done($gotomsg);
    }
    
    $fmanager->get_message_topic_id($gotomsg, $tid, $fid, $is_pid_pinned);
    if ($tid == -1) {
        MessageHandler::setError(sprintf(text("ErrMessageDoesNotExist"), $gotomsg));
        header("Location: forums.php");
        exit;
    }
}

if (!reqvar_empty("do_post")) {
    $_SESSION["do_post"] = 1;
}
if (!reqvar_empty("do_write")) {
    $_SESSION["do_write"] = reqvar("do_write");
}
if (!reqvar_empty("do_answer")) {
    $_SESSION["do_answer"] = reqvar("do_answer");
    $_SESSION["answer_author"] = "";
}
if (!reqvar_empty("answer_author")) {
    $_SESSION["answer_author"] = reqvar("answer_author");
}
if (!reqvar_empty("do_citate")) {
    $_SESSION["do_citate"] = reqvar("do_citate");
}

$hide_from_robots = 0;
$fmanager->get_topic_forum_id($tid, $fid, $hide_from_robots);
$fid_for_url = $fid;

if (!empty($hide_from_robots) && detect_bot(val_or_empty($_SERVER["HTTP_USER_AGENT"])) != "") {
    echo "no data";
    exit;
}

$private_fid = $fmanager->get_private_forum_id();
$is_private = false;
if ($fid == $private_fid) {
    $is_private = true;
    $fid_for_url = "private";
    
    if (!$fmanager->is_logged_in()) {
        $_SESSION["last_url"] = val_or_empty($_SERVER["REQUEST_URI"]);
        header("Location: login.php?fid=private");
        exit;
    }
    
    if ($fmanager->is_master_admin()) {
        if (empty($target_url) || $target_url == val_or_empty($_SERVER["REQUEST_URI"])) {
            $target_url = "forums.php";
        }
        
        MessageHandler::setWarning(text("MsgMasterAdminWarning"));
        header("Location: " . $target_url);
        exit;
    }
}

if ($fmanager->need_forum_password($tid, $fid)) {
    $_SESSION["last_url_askpwd"] = val_or_empty($_SERVER["REQUEST_URI"]);
    header("location: ask_password.php?fid=" . $fid);
    exit;
}

if (!$fmanager->has_access_to_topic($tid, true)) {
    if (!$fmanager->is_logged_in()) {
        MessageHandler::setWarning(text("MsgTryLogin"));
        
        $_SESSION["last_url_asklogin"] = val_or_empty($_SERVER["REQUEST_URI"]);
        $target_url = "login.php?fid=$fid";
    }
    
    header("location: " . $target_url);
    exit;
}

// we need do it here before get_topic_data
// to get private topic visits

$fmanager->track_hit($tid, $fid);

if (!reqvar_empty("force_comments")) {
    unset($_SESSION["filtered_topics"][$tid]);
}

$topic_data = array();
if (!$fmanager->get_topic_data($tid, $topic_data)) {
    header("location: " . $target_url);
    exit;
}

if(!empty($topic_data["profiled_topic"])) {
  $topic_data["thematic_only"] = 0;
  if (!empty($_SESSION["filtered_topics"][$tid])) {
      $topic_data["thematic_only"] = 1;
  }
}

if (!empty($topic_data["merge_target_topic"])) {
    MessageHandler::setWarning(text("WarnTopicMerged"));
    header("location: topic.php?fid=" . val_or_empty($topic_data["merge_target_forum"]) . "&tid=" . val_or_empty($topic_data["merge_target_topic"]) . "&gotonew=1");
    exit;
}

if (!reqvar_empty("gotonew")) {
    $fmanager->define_first_new_message($tid, $gotomsg);
}

if (!empty($gotomsg)) {
    if (!reqvar_empty("setdone")) {
        // we do it twice to remove the mark from deleted message
        $fmanager->set_event_done($gotomsg);
    }
    
    $_SESSION["ensure_anchor_visible"] = $gotomsg;
}

//------------------------------------------------------------------
$max_att_size = $settings["max_att_size"];
if ($max_att_size > 1024) {
    $max_att_size = number_format($max_att_size / 1024, 1, ",", "");
    $max_att_size .= " " . text("MB");
} else {
    $max_att_size .= " " . text("KB");
}

$max_att_size_audiovideo = $settings["max_att_size_audiovideo"];
if ($max_att_size_audiovideo > 1024) {
    $max_att_size_audiovideo = number_format($max_att_size_audiovideo / 1024, 1, ",", "");
    $max_att_size_audiovideo .= " " . text("MB");
} else {
    $max_att_size_audiovideo .= " " . text("KB");
}
//------------------------------------------------------------------
$forum_data = array();

if (!empty($topic_data["is_private"])) {
    if (!$fmanager->get_private_forum_data($forum_data)) {
        header("location: " . $target_url);
        exit;
    }
} else {
    if (!$fmanager->get_forum_data($fid, $forum_data)) {
        header("location: " . $target_url);
        exit;
    }
}

$title = text("Topic");
$topic_title = text("Topic");
$forum_title = text("Forum");

if (!empty($topic_data["topic_name"])) {
    $title = postprocess_message($topic_data["topic_name"]);
    $topic_title = postprocess_message($topic_data["topic_name"]);
}

if (!empty($forum_data["forum_name"])) {
    $title .= " / " . $forum_data["forum_name"];
}

if (!empty($forum_data["forum_description"])) {
    $ogdescription = $forum_data["forum_description"];
}

$seo_post_with_attachment = false;
$seo_post = $gotomsg;
if (empty($seo_post)) 
{
    $seo_post = $topic_data["first_topic_message"];
}

if (!empty($seo_post))
{
    $message = "";
    $image = "";
    $fmanager->get_post_seo($seo_post, $message, $image);
    
    if (!empty($message)) {
        $ogdescription = $message;
    }
    
    if (!empty($image)) {
        $seo_post_with_attachment = true;
        $ogimage = $image;
    }
}

$title .= " - " . get_site_name(current_language());
$ogtitle = $title;

$fpage_appendix = "";
if(!reqvar_empty("fpage")) $fpage_appendix .= "&fpage=" . reqvar("fpage");

if(!reqvar_empty("search_keys")) $fpage_appendix .= "&search_keys=" . xrawurlencode(reqvar("search_keys"));
if(!reqvar_empty("with_morphology")) $fpage_appendix .= "&with_morphology=1";
if(!reqvar_empty("from_search")) $fpage_appendix .= "&from_search=1";

$base_url = "topic.php?fid=" . $fid_for_url . "&tid=" . $tid . $fpage_appendix;

// do cleaning jobs

$fmanager->execute_forum_jobs(true);

if (!empty($forum_data["forum_name"])) {
    $forum_title = $forum_data["forum_name"];
}

// msg is important, it is used to include the deleted post of the user
// if he wants to see it
$_REQUEST["msg"] = $_SESSION["ensure_anchor_visible"];

$user_data = array();
$post_list = array();

$pagination_info = array();
$pagination_info["posts_per_page"] = $fmanager->get_posts_per_page();
$pagination_info["total_count"] = val_or_empty($topic_data["post_count"]);
$pagination_info["ignored_hidden"] = (!empty($_SESSION["hide_ignored"]) && !$fmanager->is_forum_moderator($fid) && !$fmanager->is_topic_moderator($tid));
$pagination_info["ignored_count"] = val_or_empty($topic_data["ignored_post_count"]);
$pagination_info["ignored_comment_count"] = val_or_empty($topic_data["ignored_comment_count"]);

$pagination_info["first_topic_message"] = val_or_empty($topic_data["first_topic_message"]);
$pagination_info["first_topic_pinned_message"] = val_or_empty($topic_data["first_topic_pinned_message"]);
$pagination_info["last_topic_message"] = val_or_empty($topic_data["last_topic_message"]);
$pagination_info["topic_has_pinned_post"] = val_or_empty($topic_data["has_pinned_post"]);
$pagination_info["topic_has_deleted_posts"] = val_or_empty($topic_data["post_count_total"]) > val_or_empty($topic_data["post_count_nondeleted"]);

$pagination_info["loaded_message_count"] = 0;
$pagination_info["pinned_message_count"] = 0;
$pagination_info["first_page_message"] = 0;
$pagination_info["last_page_message"] = 0;

$pagination_info["base_url"] = $base_url;

/*
Modes:

- topic_begin
- gotolast
- gotomsg
- startmsg
- all
- download

startmsg=first,last,number
offset=-1,-2,-3,1,2,3
*/

$pagination_info["msg"] = "";
$pagination_info["mode"] = "topic_begin";
if (!reqvar_empty("download") && $fmanager->is_logged_in()) {
    $pagination_info["mode"] = "download";
    $pagination_info["startmsg"] = reqvar("startmsg");
} elseif (!reqvar_empty("all")) {
    if ($pagination_info["total_count"] > 500) {
        MessageHandler::setWarning(text("MsgTopicTooLarge"));
        
        if (!reqvar_empty("startmsg")) {
            $pagination_info["mode"] = "startmsg";
            $pagination_info["startmsg"] = reqvar("startmsg");
        }
    } else {
        $pagination_info["mode"] = "all";
        $_SESSION["ensure_anchor_visible"] = $gotomsg;
        $pagination_info["msg"] = $gotomsg;
    }
} elseif (!reqvar_empty("gotolast")) {
    $pagination_info["mode"] = "gotolast";
} elseif (!empty($gotomsg)) {
    $pagination_info["mode"] = "gotomsg";
    $pagination_info["msg"] = $gotomsg;
    
    if(reqvar("startmsg") == "msg")
    {
      $pagination_info["mode"] = "startmsg";
      $pagination_info["startmsg"] = $gotomsg;
    }

    if ($is_pid_pinned) {
        $pagination_info["mode"] = "topic_begin";
        $pagination_info["startmsg"] = "";
        $pagination_info["msg"] = "";
    }
} elseif (!reqvar_empty("startmsg")) {
    $pagination_info["mode"] = "startmsg";
    $pagination_info["startmsg"] = reqvar("startmsg");
    $pagination_info["offset"] = reqvar("offset");
} elseif (!reqvar_empty("tpage")) {
    $pagination_info["mode"] = "gotopage";
    $pagination_info["page"] = reqvar("tpage");
}

$fmanager->get_topic_posts($fid, $tid, $post_list, $user_data, $pagination_info, !empty($topic_data["deleted"]));

$first_message = $pagination_info["first_page_message"];
$last_message = $pagination_info["last_page_message"];

if ($pagination_info["mode"] == "gotolast") {
    $_SESSION["ensure_anchor_visible"] = $last_message;
    
    if (!reqvar_empty("setdone")) {
        $fmanager->set_event_done($_SESSION["ensure_anchor_visible"]);
    }
}

$pagination_info["startmsg"] = $first_message;

if ($pagination_info["mode"] == "topic_begin" ||
    $pagination_info["first_page_message"] == $pagination_info["first_topic_message"] ||
    $pagination_info["first_page_message"] == $pagination_info["first_topic_pinned_message"]
) {
    $pagination_info["startmsg"] = "";
    
    if ($pagination_info["mode"] != "topic_begin" && empty($_SESSION["ensure_anchor_visible"])) {
        // by paging jump to the first message of the page
        $_SESSION["ensure_anchor_visible"] = "top_new_message";
    }    
} elseif (empty($_SESSION["ensure_anchor_visible"])) {
    // by paging jump to the first message of the page
    $_SESSION["ensure_anchor_visible"] = "top_new_message";
}

$_REQUEST["fid"] = $fid_for_url;

$final_url = "topic.php?fid=" . $fid_for_url . "&tid=" . $tid;

if (!reqvar_empty("fpage")) {
    $final_url .= "&fpage=" . reqvar("fpage");
}
if (!reqvar_empty("from_search")) {
    $final_url .= "&from_search=1";
}
if (!reqvar_empty("search_keys")) {
    $final_url .= "&search_keys=" . urlencode(reqvar("search_keys"));
}
if (!reqvar_empty("with_morphology")) {
    $final_url .= "&with_morphology=1";
}
if ($pagination_info["mode"] == "all") {
    $final_url .= "&all=1";
}
if ($pagination_info["mode"] == "download") {
    $final_url .= "&download=1";
}
if (!empty($pagination_info["startmsg"])) {
    $final_url .= "&startmsg=" . $pagination_info["startmsg"];
}

$last_post_read_date = 0;
if (!empty($post_list)) {
    foreach ($post_list as $pdata) {
        $last_post_read_date = max($last_post_read_date, $pdata["creation_date_sec"]);
    }
}

if (reqvar_empty("leave_unread")) {
    $fmanager->update_forum_read_status($fid);
    $fmanager->update_topic_read_status($tid, $fid, $last_post_read_date);
}

$all_entry_post = $first_message;

$fmanager->reset_unnecessary_events($post_list);

$topic_members = array();
$blocked_users = array();
if (!empty($tid)) {
    $fmanager->get_private_member_list($tid, $topic_members);
    
    $fmanager->get_topic_blocked_users_list($tid, $blocked_users);
}

$online_users = array();
$forum_readers = array();
$topic_readers = array();
$topic_ignorers = array();
$topic_blocked_users = array();
$topic_blocked_users = array();
$fmanager->get_online_users($online_users, $forum_readers, $topic_readers, $topic_ignorers, $topic_blocked_users, $topic_blocked_users, $fid, $tid);

$user_tags = array();
$fmanager->get_user_tags($user_tags, $fmanager->get_user_id());

$ignored_users = array();
if (!empty($_SESSION["ignored_users"])) {
    $fmanager->get_user_names(implode(",", $_SESSION["ignored_users"]), $ignored_users);
}

//------------------------------------------------------------------
$_SESSION["last_url"] = val_or_empty($_SERVER["REQUEST_URI"]);
//------------------------------------------------------------------
require_once "include/check_new_inc.php";
//------------------------------------------------------------------

$topic_data["new_messages_count"] = 0;
$forum_data['topics_with_new_count'] = 0;

if (!empty($topic_data["is_private"]) && !empty($_SESSION["new_messages_info_cache"]["data"]["private_topics"])) {
    $forum_data['topics_with_new_count'] = count($_SESSION["new_messages_info_cache"]["data"]["private_topics"]);
} elseif (!empty($_SESSION["new_messages_info_cache"]["data"]["forums"][$fid])) {
    $forum_data['topics_with_new_count'] = count($_SESSION["new_messages_info_cache"]["data"]["forums"][$fid]);
}

// when we enter a topic with new messages, we decrease the number of the topics with new messages by 1

$topic_is_ignored = false;
$topic_is_in_the_new_cache = false;
if (!empty($_SESSION["new_messages_info_cache"]["data"]["ignored_topics"][$tid])) {
    $topic_is_in_the_new_cache = true;
    $topic_is_ignored = true;

    // exclusion of the topic count not necessary because the ignored topics are not counted
    // moderated ignored topics fall into normal case and counted
} elseif (!empty($_SESSION["new_messages_info_cache"]["data"]["topics"][$tid])) {
    $topic_is_in_the_new_cache = true;

    // we exclude the current topic from the count
    if (!empty($topics_with_new_count) && reqvar_empty("leave_unread")) {
        $topics_with_new_count--;
    }
    
    if (!empty($forum_data['topics_with_new_count']) && reqvar_empty("leave_unread")) {
        $forum_data['topics_with_new_count']--;
    }
} elseif (!empty($_SESSION["new_messages_info_cache"]["data"]["private_topics"][$tid])) {
    $topic_is_in_the_new_cache = true;

    // we exclude the current topic from the count
    if (!empty($private_topics_with_new_count) && reqvar_empty("leave_unread")) {
        $private_topics_with_new_count--;
    }
    
    if (!empty($forum_data['topics_with_new_count']) && reqvar_empty("leave_unread")) {
        $forum_data['topics_with_new_count']--;
    }
}

if (!empty($_SESSION["new_messages_info_cache"]["data"]["favourites"][$tid]) && !$topic_is_ignored) {
    // we exclude the current topic from the count
    if (!empty($favourites_with_new_count) && reqvar_empty("leave_unread")) {
        $favourites_with_new_count--;
    }
}

if (!empty($_SESSION["new_messages_info_cache"]["data"]["my_topics"][$tid]) && !$topic_is_ignored) {
    // we exclude the current topic from the count
    if (!empty($my_topics_with_new_count) && reqvar_empty("leave_unread")) {
        $my_topics_with_new_count--;
    }
}

if (!empty($_SESSION["new_messages_info_cache"]["data"]["my_part_topics"][$tid]) && !$topic_is_ignored) {
    // we exclude the current topic from the count
    if (!empty($my_part_topics_with_new_count) && reqvar_empty("leave_unread")) {
        $my_part_topics_with_new_count--;
    }
}

// It is offline downloading, no new marks
if ($pagination_info["mode"] == "download" && $fmanager->is_logged_in()) {
    $forum_data["topics_with_new_count"] = 0;
    $topic_data["new_messages_count"] = 0;
    $topics_with_new_count = 0;
    $private_topics_with_new_count = 0;
    $new_events_count = 0;
    $favourites_with_new_count = 0;
    $my_topics_with_new_count = 0;
} elseif (reqvar_empty("leave_unread") && $topic_is_in_the_new_cache) {
    if (!empty($_SESSION["new_messages_info_cache"]["data"]["topic_last_read_date"][$tid])) {
          $last_topic_read_date = $_SESSION["new_messages_info_cache"]["data"]["topic_last_read_date"][$tid];
    }

    $last_topic_read_date = max($last_topic_read_date, $last_post_read_date);
    
    $fmanager->calculate_new_topic_messages($last_topic_read_date, $fid, $tid, $topic_data["new_messages_count"], !empty($topic_data["deleted"]));

    $fmanager->update_topic_new_messages_cache($tid, $topic_data["new_messages_count"], $last_post_read_date);
} elseif (!reqvar_empty("leave_unread")) {
    $topic_data["new_messages_count"] = 0;
}

$bulk_delete_count = 5;
if (defined('BULK_DELETE_COUNT') && is_numeric(BULK_DELETE_COUNT)) {
    $bulk_delete_count = BULK_DELETE_COUNT;
}

$may_write_to_topic = true;
if (!empty($topic_data["closed"])) {
    $may_write_to_topic = false;
}

if ($fmanager->is_topic_moderator($tid)) {
    $may_write_to_topic = true;
}

if (!empty($forum_data["closed"])) {
    $may_write_to_topic = false;
}

if (!empty($forum_data["blocked"])) {
    $may_write_to_topic = false;
}

if (!empty($topic_data["blocked"])) {
    $may_write_to_topic = false;
}

if ($fmanager->is_admin() || $fmanager->is_forum_moderator($fid)) {
    $may_write_to_topic = true;
}

if (!empty($_SESSION["blocked"])) {
    $may_write_to_topic = false;
}

if ($fmanager->is_logged_in() && empty($_SESSION["approved"])) {
    $may_write_to_topic = false;
}

if ($fmanager->is_logged_in() && empty($_SESSION["activated"])) {
    $may_write_to_topic = false;
}

if (!$fmanager->is_logged_in() && !empty($_SESSION["ip_blocked"])) {
    $may_write_to_topic = false;
}

if (!empty($settings["archive_mode"]))
{
    $may_write_to_topic = false;
}

//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "topic.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>