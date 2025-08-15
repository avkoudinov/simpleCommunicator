<?php
//------------------------------------------------------------------
session_set_cookie_params(0, str_replace(basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
$tid = reqvar("tid");
$fid = "";

if (empty($tid)) {
    header("Location: forums.php");
    exit;
}

$hide_from_robots = 0;
$fmanager->get_topic_forum_id($tid, $fid, $hide_from_robots);
$fid_for_url = $fid;

$bot_data = detect_bot(val_or_empty($_SERVER["HTTP_USER_AGENT"]));
if (!empty($hide_from_robots) && !empty($bot_data) && empty($bot_data["allowed"])) {
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
    $title = text("ReadingTopic") . ": " . postprocess_message($topic_data["topic_name"]);
    $topic_title = postprocess_message($topic_data["topic_name"]);
}

if (!empty($forum_data["forum_name"])) {
    $title .= " / " . $forum_data["forum_name"];
}

if (!empty($forum_data["forum_description"])) {
    $ogdescription = $forum_data["forum_description"];
}

$title .= " - " . get_site_name(current_language());
$ogtitle = $title;

if(!reqvar_empty("search_keys")) $fpage_appendix .= "&search_keys=" . xrawurlencode(reqvar("search_keys"));
if(!reqvar_empty("with_morphology")) $fpage_appendix .= "&with_morphology=1";
if(!reqvar_empty("from_search")) $fpage_appendix .= "&from_search=1";

$base_url = "topic.php?fid=" . $fid_for_url . "&tid=" . $tid;

if (!empty($forum_data["forum_name"])) {
    $forum_title = $forum_data["forum_name"];
}

$_REQUEST["fid"] = $fid_for_url;

$topic_members = array();
$blocked_users = array();
if (!empty($tid)) {
    $fmanager->get_private_member_list($tid, $topic_members);
    
    $fmanager->get_topic_blocked_users_list($tid, $blocked_users);
}

$all_topic_readers = [];
$fmanager->get_topic_readers($tid, $all_topic_readers);

$online_users = array();
$forum_readers = array();
$topic_readers = array();
$topic_ignorers = array();
$topic_blocked_users = array();
$fmanager->get_online_users($online_users, $forum_readers, $topic_readers, $topic_ignorers, $topic_blocked_users, $fid, $tid);

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
} elseif (!empty($_SESSION["new_messages_info_cache"]["data"]["topics"][$tid])) {
    $topic_is_in_the_new_cache = true;
} elseif (!empty($_SESSION["new_messages_info_cache"]["data"]["private_topics"][$tid])) {
    $topic_is_in_the_new_cache = true;
}

if ($topic_is_in_the_new_cache) {
    if (!empty($_SESSION["new_messages_info_cache"]["data"]["topic_last_read_date"][$tid])) {
          $last_topic_read_date = $_SESSION["new_messages_info_cache"]["data"]["topic_last_read_date"][$tid];
    }

    $fmanager->calculate_new_topic_messages($last_topic_read_date, $fid, $tid, $topic_data["new_messages_count"], !empty($topic_data["deleted"]));
} elseif (!reqvar_empty("leave_unread")) {
    $topic_data["new_messages_count"] = 0;
}
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "topic_readers.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>