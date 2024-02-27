<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
if (!$fmanager->is_logged_in() && $fmanager->check_tor_ip(val_or_empty($_SERVER["REMOTE_ADDR"])) == "tor_block_read") {
    MessageHandler::setError(text("ErrTorNodeBlocked"));
    
    header("location: login.php");
    exit;
}
//------------------------------------------------------------------
if (detect_bot(val_or_empty($_SERVER["HTTP_USER_AGENT"])) != "") {
    echo "no data";
    exit;
}
//------------------------------------------------------------------
$final_url = $_SERVER['REQUEST_URI'];
$final_url = preg_replace("/&msg=\d*/", "", $final_url);
$final_url = preg_replace("/&all=\d*/", "", $final_url);
$final_url = preg_replace("/&tpage=\d*/", "", $final_url);
$final_url = preg_replace("/&startmsg=[^&]*/", "", $final_url);
$final_url = preg_replace("/&post_sort=[^&]*/", "", $final_url);
$_SESSION["ensure_anchor_visible"] = reqvar("msg");

$search_params = $fmanager->build_search_param_string();

$tid = reqvar("tid");
$search_topic_appendix = "";

if (!reqvar_empty("do_search")) {
    $search_topic_appendix = "&do_search=1";
}

if (!reqvar_empty("post_sort")) {
    $search_topic_appendix .= "&post_sort=" . urlencode(reqvar("post_sort"));
}

if (!reqvar_empty("do_search")) {
    //debug_message("New search desired (do_search=1), forcing new fill search");
    
    if (!reqvar_empty("start_from")) {
        $search_params .= "&start_from=" . urlencode(reqvar("start_from"));
    }

    header("location: search.php?" . $search_params . $search_topic_appendix);
    exit;
}

$search_hash = $fmanager->build_search_param_hash();

$cache_exists = false;
if (!reqvar_empty("favourite_posts")) {
    // favourite posts are always retrieved new
    $cache_exists = true;
}

// Hash of searching single topic is with tid=nnn,
// try to check this case first
if (!$cache_exists) {
    $cache_exists = $fmanager->search_cache_exists($search_hash);
}

// The search might have been done over many topics, but now, the user
// goes into single topic and says - shown only found. In this case,
// the hash does not have tid=nnn, but is valid also for going into
// single topic.
if (!$cache_exists) {
    $search_hash = $fmanager->build_search_param_hash(true /* exclude tid */);

    $cache_exists = $fmanager->search_cache_exists($search_hash);
}

// the last search is no more valid
if (!$cache_exists) {
    if (!reqvar_empty("start_from")) {
        $search_params .= "&start_from=" . urlencode(reqvar("start_from"));
    }

    header("location: search.php?" . $search_params . $search_topic_appendix);
    exit;
}
//------------------------------------------------------------------
//debug_message("Cache valid, showing results");

$search_title = text("Search");

$search_title_appendix = $fmanager->build_search_title();
if (!empty($search_title_appendix)) {
    $search_title .= ": " . $search_title_appendix;
}

// special case: search in the virtual topic "favourite posts"
if (!reqvar_empty("favourite_posts_only")) {
    $search_title = text("FavouriteMessages") . ", " . $search_title;
}
//------------------------------------------------------------------
$fid = reqvar("fid");
if (empty($fid) && !empty($_REQUEST["forums"]) && count($_REQUEST["forums"]) == 1) {
    $fid = $_REQUEST["forums"][0];
}

$fid_for_url = $fid;
$forum_url = "";
$forum_title = "";
$private_fid = "";
$is_private = false;
$topic_data = array();
$forum_data = array();

$private_fid = $fmanager->get_private_forum_id();
//------------------------------------------------------------------
if (!empty($tid)) {
    $fid = "";

    if ($fmanager->need_forum_password($tid, $fid)) {
        $_SESSION["last_url"] = val_or_empty($_SERVER["REQUEST_URI"]);
        header("location: ask_password.php?fid=" . $fid);
        exit;
    }
    
    if (!$fmanager->get_topic_data($tid, $topic_data)) {
        header("location: " . $target_url);
        exit;
    }

    if (!$fmanager->has_access_to_topic($tid, true)) {
        if (!$fmanager->is_logged_in()) {
            MessageHandler::setWarning(text("MsgTryLogin"));
            
            $_SESSION["last_url_asklogin"] = val_or_empty($_SERVER["REQUEST_URI"]);
            $target_url = "login.php?fid=" . val_or_empty($topic_data["forum_id"]);
        }
        
        header("location: " . $target_url);
        exit;
    }
    
    if (!$fmanager->get_forum_data(val_or_empty($topic_data["forum_id"]), $forum_data)) {
        header("location: " . $target_url);
        exit;
    }
    
    $forum_title = val_or_empty($forum_data["forum_name"]);
    $fid = val_or_empty($forum_data["id"]);
    $fid_for_url = $fid;
    if ($fid == $fmanager->get_private_forum_id()) {
        $fid_for_url = "private";
        $is_private = true;
        $forum_title = text("PrivateTopics");
    }
    
    // search in a single topic, prepend its name to the titles
    $search_title = $topic_data["topic_name"] . ", " . $search_title;
} elseif (!empty($fid)) {
    $fid_for_url = $fid;
    
    $is_private = false;
    if ($fid == "private" || $fid == $private_fid) {
        $is_private = true;
        $fid = $private_fid;
        $fid_for_url = "private";
        $forum_title = text("PrivateTopics");

        if (!$fmanager->get_forum_data($private_fid, $forum_data)) {
            header("location: " . $target_url);
            exit;
        }
    } elseif ($fid == "favourites" || $fid == -1) {
        $fid = -1;
        $fid_for_url = "favourites";
    } elseif ($fid == "my_topics" || $fid == -2) {
        $fid = -2;
        $fid_for_url = "my_topics";
    } elseif ($fid == "my_part_topics" || $fid == -3) {
        $fid = -3;
        $fid_for_url = "my_part_topics";
    } else {
        if (!$fmanager->get_forum_data(val_or_empty($fid), $forum_data)) {
            header("location: " . $target_url);
            exit;
        }
        
        $forum_title = val_or_empty($forum_data["forum_name"]);
        $fid = val_or_empty($forum_data["id"]);
        $fid_for_url = $fid;
    }
}

$title = $search_title . " - " . get_site_name(current_language());

$base_url = "search_topic.php?" . $search_params;
if(!reqvar_empty("favourite_posts"))
{
  $base_url = "search_topic.php?favourite_posts=1";
} 
elseif(!reqvar_empty("news_digest"))
{
  $base_url = "search_topic.php?news_digest=1";
  if(!empty($fid_for_url)) {
    $base_url .= "&fid=" . $fid_for_url;
  }
}

if(!reqvar_empty("post_sort")) 
{
  $base_url .= "&post_sort=" . xrawurlencode(reqvar("post_sort"));
}

//------------------------------------------------------------------
$user_data = array();
$post_list = array();

$pagination_info = array();
$pagination_info["posts_per_page"] = $fmanager->get_posts_per_page();
$pagination_info["total_count"] = 0;
$pagination_info["ignored_hidden"] = (!empty($_SESSION["hide_ignored"]) && !$fmanager->is_forum_moderator($fid) && !$fmanager->is_topic_moderator($tid));
$pagination_info["ignored_count"] = 0;

$pagination_info["first_topic_message"] = 0;
$pagination_info["first_topic_pinned_message"] = 0;
$pagination_info["last_topic_message"] = 0;

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
        $_SESSION["ensure_anchor_visible"] = reqvar("msg");
        $pagination_info["msg"] = reqvar("msg");
    }
} elseif (!reqvar_empty("gotolast")) {
    $pagination_info["mode"] = "gotolast";
} elseif (!empty($gotomsg)) {
    $pagination_info["mode"] = "gotomsg";
    $pagination_info["msg"] = $gotomsg;
} elseif (!reqvar_empty("startmsg")) {
    $pagination_info["mode"] = "startmsg";
    $pagination_info["startmsg"] = reqvar("startmsg");
    $pagination_info["offset"] = reqvar("offset");
} elseif (!reqvar_empty("tpage")) {
    $pagination_info["mode"] = "gotopage";
    $pagination_info["page"] = reqvar("tpage");
}

if (reqvar("author_mode") == "last_posts" || !reqvar_empty("rate_statistics"))
{
    $sort = "desc";
} elseif (reqvar("post_sort") == "desc") {
    $sort = "desc";
} else {
    $sort = "asc";
}

if (!reqvar_empty("news_digest")) { // News digest
    $pagination_info["page"] = 1;
    if ($pagination_info["mode"] != "all") {
        $pagination_info["mode"] = "gotopage";
        $pagination_info["page"] = reqvar("tpage");
        if (empty($pagination_info["page"])) {
           $pagination_info["page"] = 1;
        }
    }

    $pagination_info["base_url"] = $base_url;
    $pagination_info["base_url_pagination"] = $base_url . "&tpage=$";

    if (!$fmanager->get_paginated_found_posts($search_hash, $post_list, $user_data, $pagination_info, $sort)) {
        header("location: new_messages.php" . (empty($fid_for_url) ? "" : "?fid=" . $fid_for_url));
        exit;
    }
    
    $fmanager->check_new_events($new_events_count, $new_mod_events_count);
    $fmanager->calculate_new_messages();
    
    $fmanager->reset_unnecessary_events($post_list);
    
    $last_post_date = "";
    $current_topic_id = "";
    $current_forum_id = "";
    $post_count = 0;
    foreach ($post_list as $pid => $pinfo) {
        
        if (val_or_empty($current_topic_id) != $pinfo["topic_id"]) {
            if (!empty($current_topic_id)) {
                $fmanager->update_topic_read_status($current_topic_id, $current_forum_id, $last_post_date);
                
                $remaining_new = 0;
                if (!empty($_SESSION["new_messages_info_cache"]["data"]["topics"][$current_topic_id])) {
                    $remaining_new = $_SESSION["new_messages_info_cache"]["data"]["topics"][$current_topic_id] - $post_count;
                }
                
                if (!empty($_SESSION["new_messages_info_cache"]["data"]["private_topics"][$current_topic_id])) {
                    $remaining_new = $_SESSION["new_messages_info_cache"]["data"]["private_topics"][$current_topic_id] - $post_count;
                }
                
                if ($remaining_new < 0) {
                    $remaining_new = 0;
                }
                
                $fmanager->update_topic_new_messages_cache($current_topic_id, $remaining_new, $last_post_date);
            }
            
            $current_topic_id = $pinfo["topic_id"];
            $current_forum_id = $pinfo["forum_id"];
            $post_count = 0;
        }
        
        $last_post_date = $pinfo["creation_date_sec"];
        $post_count++;
    }
    
    if (!empty($current_topic_id)) {
        $fmanager->update_topic_read_status($current_topic_id, $current_forum_id, $last_post_date);
        
        $remaining_new = 0;
        if (!empty($_SESSION["new_messages_info_cache"]["data"]["topics"][$current_topic_id])) {
            $remaining_new = $_SESSION["new_messages_info_cache"]["data"]["topics"][$current_topic_id] - $post_count;
        }
        
        if (!empty($_SESSION["new_messages_info_cache"]["data"]["private_topics"][$current_topic_id])) {
            $remaining_new = $_SESSION["new_messages_info_cache"]["data"]["private_topics"][$current_topic_id] - $post_count;
        }
        
        if ($remaining_new < 0) {
            $remaining_new = 0;
        }
        
        $fmanager->update_topic_new_messages_cache($current_topic_id, $remaining_new, $last_post_date);
    }
    
    unset($current_topic_id);
    unset($current_forum_id);
    
    $topics_with_new_count = empty($_SESSION["new_messages_info_cache"]["data"]["visible_topics"]) ? 0 : count($_SESSION["new_messages_info_cache"]["data"]["visible_topics"]);
    $favourites_with_new_count = empty($_SESSION["new_messages_info_cache"]["data"]["favourites"]) ? 0 : count($_SESSION["new_messages_info_cache"]["data"]["favourites"]);
    $my_topics_with_new_count = empty($_SESSION["new_messages_info_cache"]["data"]["my_topics"]) ? 0 : count($_SESSION["new_messages_info_cache"]["data"]["my_topics"]);
    $my_part_topics_with_new_count = empty($_SESSION["new_messages_info_cache"]["data"]["my_part_topics"]) ? 0 : count($_SESSION["new_messages_info_cache"]["data"]["my_part_topics"]);
    $private_topics_with_new_count = empty($_SESSION["new_messages_info_cache"]["data"]["private_topics"]) ? 0 : count($_SESSION["new_messages_info_cache"]["data"]["private_topics"]);
    $subscription_authors_new_messages_count = empty($_SESSION["new_messages_info_cache"]["data"]["subscription_authors_new_messages_count"]) ? 0 : $_SESSION["new_messages_info_cache"]["data"]["subscription_authors_new_messages_count"];
    $subscription_authors_new_topics_count = empty($_SESSION["new_messages_info_cache"]["data"]["subscription_authors_new_topics_count"]) ? 0 : $_SESSION["new_messages_info_cache"]["data"]["subscription_authors_new_topics_count"];
    
    $title = text("NewMessageDigest");
    $search_title = text("NewMessageDigest");
    
    $forum_title = text("NewMessages");
    $forum_url = "new_messages.php";
    if ($fid == "private" || $fid == $private_fid) {
        $forum_url = "new_messages.php?fid=private";
        $forum_title = text("PrivateTopics") . ": " . text("NewMessages");
        $title = text("PrivateTopics") . ": " . text("NewMessageDigest");
    } elseif ($fid == "favourites" || $fid == -1) {
        $forum_url = "new_messages.php?fid=favourites";
        $forum_title = text("Favourites") . ": " . text("NewMessages");
        $title = text("Favourites") . ": " . text("NewMessageDigest");
    } elseif ($fid == "my_topics" || $fid == -2) {
        $forum_url = "new_messages.php?fid=my_topics";
        $forum_title = text("MyTopics") . ": " . text("NewMessages");
        $title = text("MyTopics") . ": " . text("NewMessageDigest");
    } elseif ($fid == "my_part_topics" || $fid == -3) {
        $forum_url = "new_messages.php?fid=my_part_topics";
        $forum_title = text("ParticipatedTopics") . ": " . text("NewMessages");
        $title = text("ParticipatedTopics") . ": " . text("NewMessageDigest");
    } elseif (!empty($fid)) {
        $forum_url = "new_messages.php?fid=" . $fid_for_url;
        $forum_title = $forum_data["forum_name"] . ": " . text("NewMessages");
        $title = $forum_data["forum_name"] . ": " . text("NewMessageDigest");
    }
    
    $final_url = "search_topic.php?" . $search_params;
    if (!reqvar_empty("post_sort")) {
        $final_url .= "&post_sort=" . urlencode(reqvar("post_sort"));
    }
    if (!empty($pagination_info["page"]) && $pagination_info["page"] > 1) {
        $final_url .= "&tpage=" . $pagination_info["page"];
    }
    if ($pagination_info["mode"] == "all") {
        $final_url .= "&all=1";
    }
    if (!empty($pagination_info["msg"])) {
        // post for highlighting
        $_SESSION["ensure_anchor_visible"] = $pagination_info["msg"];
    }

    // by paging jump to the first message of the page
    if (empty($_SESSION["ensure_anchor_visible"]) && !reqvar_empty("tpage")) {
        $_SESSION["ensure_anchor_visible"] = "top_new_message";
    }
} elseif (!reqvar_empty("favourite_posts")) { // Favourites
    if (!$fmanager->get_found_posts("favourite_posts", "", $post_list, $user_data, $pagination_info, $sort)) {
        header("location: favourites.php");
        exit;
    }
    
    if (!reqvar_empty("gotolast")) {
        // post for highlighting
        $_SESSION["ensure_anchor_visible"] = $pagination_info["last_page_message"];
    }

    if (!empty($pagination_info["msg"])) {
        // post for highlighting
        $_SESSION["ensure_anchor_visible"] = $pagination_info["msg"];
    }
    
    $pagination_info["startmsg"] = $pagination_info["first_page_message"];
    
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
    
    $title = text("FavouriteMessages");
    
    $final_url = "search_topic.php?favourite_posts=1";
    if (!reqvar_empty("post_sort")) {
        $final_url .= "&post_sort=" . urlencode(reqvar("post_sort"));
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
} elseif (!reqvar_empty("rate_statistics")) { 
    $pagination_info["page"] = 1;
    if ($pagination_info["mode"] != "all") {
        $pagination_info["mode"] = "gotopage";
        $pagination_info["page"] = reqvar("tpage");
        if (empty($pagination_info["page"])) {
           $pagination_info["page"] = 1;
        }
    }

    if (!$fmanager->get_paginated_found_posts($search_hash, $post_list, $user_data, $pagination_info, $sort)) {
        header("location: search.php?" . $search_params . $search_topic_appendix);
        exit;
    }
    
    if (!reqvar_empty("post_sort")) {
        $final_url .= "&post_sort=" . urlencode(reqvar("post_sort"));
    }
    if (!empty($pagination_info["page"]) && $pagination_info["page"] > 1) {
        $final_url .= "&tpage=" . $pagination_info["page"];
    }
    if ($pagination_info["mode"] == "all") {
        $final_url .= "&all=1";
    }
    if (!empty($pagination_info["msg"])) {
        // post for highlighting
        $_SESSION["ensure_anchor_visible"] = $pagination_info["msg"];
    }
    
    // by paging jump to the first message of the page
    if (empty($_SESSION["ensure_anchor_visible"]) && !reqvar_empty("tpage")) {
        $_SESSION["ensure_anchor_visible"] = "top_new_message";
    }
} else { // Normal search
    if (!$fmanager->get_found_posts($search_hash, $tid, $post_list, $user_data, $pagination_info, $sort)) {
        header("location: search.php?" . $search_params . $search_topic_appendix);
        exit;
    }
    
    $pagination_info["startmsg"] = $pagination_info["first_page_message"];
    
    if (!reqvar_empty("gotolast")) {
        // post for highlighting
        $_SESSION["ensure_anchor_visible"] = $pagination_info["last_page_message"];
    }
    
    if (!empty($pagination_info["msg"])) {
        // post for highlighting
        $_SESSION["ensure_anchor_visible"] = $pagination_info["msg"];
    }

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
    
    $final_url = "search_topic.php?" . $search_params;
    if (!reqvar_empty("post_sort") && reqvar("author_mode") != "last_posts" && reqvar_empty("rate_statistics")) {
        $final_url .= "&post_sort=" . urlencode(reqvar("post_sort"));
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
    if (!empty($pagination_info["msg"])) {
        // post for highlighting
        $_SESSION["ensure_anchor_visible"] = $pagination_info["msg"];
    }
}
//------------------------------------------------------------------
$title = $title . " - " . get_site_name(current_language());;
$ogtitle = $title;
//------------------------------------------------------------------
$forum_data['topics_with_new_count'] = 0;
if (!empty($fid)) {
    if (!empty($_SESSION["new_messages_info_cache"]["data"]["forums"][$fid])) {
        $forum_data['topics_with_new_count'] = count($_SESSION["new_messages_info_cache"]["data"]["forums"][$fid]);
    }
    
    if ($is_private && !empty($_SESSION["new_messages_info_cache"]["data"]["private_topics"])) {
        $forum_data['topics_with_new_count'] = count($_SESSION["new_messages_info_cache"]["data"]["private_topics"]);
    }
}
//------------------------------------------------------------------
$first_message = $pagination_info["first_page_message"];
$last_message = $pagination_info["last_page_message"];
//------------------------------------------------------------------
$user_tags = array();
$fmanager->get_user_tags($user_tags, $fmanager->get_user_id());
//------------------------------------------------------------------
$ignored_users = array();
if (!empty($_SESSION["ignored_users"])) {
    $fmanager->get_user_names(implode(",", $_SESSION["ignored_users"]), $ignored_users);
}
//------------------------------------------------------------------

$_REQUEST["fid"] = $fid_for_url;
$_REQUEST["msg"] = $_SESSION["ensure_anchor_visible"];

$fmanager->track_hit(val_or_empty($topic_data["id"]), val_or_empty($fid));

$online_users = array();
$forum_readers = array();
$topic_readers = array();
$topic_ignorers = array();
$topic_blocked_users = array();
$fmanager->get_online_users($online_users, $forum_readers, $topic_readers, $topic_ignorers, $topic_blocked_users, $fid, $tid);
//------------------------------------------------------------------
$_SESSION["last_url"] = val_or_empty($_SERVER["REQUEST_URI"]);
//------------------------------------------------------------------
if (reqvar_empty("news_digest")) {
    // In the case of digest it is already called
    require_once "include/check_new_inc.php";
}
//------------------------------------------------------------------
$bulk_delete_count = 5;
if (defined('BULK_DELETE_COUNT') && is_numeric(BULK_DELETE_COUNT)) {
    $bulk_delete_count = BULK_DELETE_COUNT;
}

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

$may_write_to_topic = true;
if (!empty($tid)) {
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

//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$in_search = true;
$poll_rendered = true;

$view = "search_topic.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>