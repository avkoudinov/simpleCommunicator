<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_readonly_inc.php";
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
shrink_spaces($_REQUEST["author"], true);

invert_dates($_REQUEST["start_date"], $_REQUEST["end_date"], text("DateFormat"));

if (reqvar_empty("author_mode") && reqvar_empty("news_digest")) {
    $_REQUEST["author_mode"] = "wrote_post";
}

// The symbol : in the search in a topic means search in this topic only
$search_keys = trim(reqvar("search_keys"));
if (!empty($search_keys) && $search_keys[0] == ":") {
    $_REQUEST["search_keys"] = ltrim($search_keys, ":");
    
    if (!reqvar_empty("favourite_posts") || !reqvar_empty("favourite_posts_only")) {
        $_REQUEST["favourites_only"] = 0;
        $_REQUEST["favourite_posts_only"] = 1;
    } elseif (!reqvar_empty("tid")) {
        // nothing
    } else {
        $_REQUEST["topics_only"] = 1;
    }
}

// without : in topic, search in the whole forum
if (!reqvar_empty("quick_search") && (empty($search_keys) || $search_keys[0] != ":")) {
    $_REQUEST["tid"] = "";
    
    if (!reqvar_empty("favourite_posts") || !reqvar_empty("favourite_posts_only")) {
        $_REQUEST["favourites_only"] = 1;
        $_REQUEST["favourite_posts_only"] = 0;
    }
}    
//------------------------------------------------------------------
$search_params = $fmanager->build_search_param_string();
$search_hash = $fmanager->build_search_param_hash();

// If no parameters, the search is performed, if there are no results in the cache.
// If there are results in the cache, thez are taken. Required for the pagination
// over the result.

// do_search=1 forces the new search even if there are results in the cache available.

// new_search=1 does not intiate search event if some search parameters are specified.
// It is used for the calling the search mask with the same paramters that can be adjusted
// for the new search.

$do_search = !reqvar_empty("do_search");

$in_search = trim(reqvar("search_keys"), ":") != "" ||
    !reqvar_empty("hot_topics") ||
    !reqvar_empty("polls_only") ||
    !reqvar_empty("deleted_topics_only") ||
    !reqvar_empty("has_attachment") ||
    !reqvar_empty("has_picture") ||
    !reqvar_empty("has_video") ||
    !reqvar_empty("has_audio") ||
    !reqvar_empty("has_adult") ||
    !reqvar_empty("has_link") ||
    !reqvar_empty("has_code") ||
    !reqvar_empty("thematic_only") ||
    !reqvar_empty("replies_to") ||
    !reqvar_empty("non_ignored_by_author") ||
    !reqvar_empty("rate_statistics") ||
    !reqvar_empty("news_digest") ||
    !reqvar_empty("start_date") ||
    !reqvar_empty("end_date") ||
    !reqvar_empty("author") ||
    !reqvar_empty("ip") ||
    !empty($_REQUEST["tags"]);

// somebody entered the search url with parameters but there is no valid last search
// for these criteria
if ($in_search && !$fmanager->search_cache_exists($search_hash)) {
    $do_search = 1;
}

// we want to change the existing search params to do new search
if (!reqvar_empty("new_search")) {
    $in_search = false;
    $do_search = false;
    $_REQUEST["tid"] = "";
    $_REQUEST["replies_to"] = "";
}

// direct jump from the quick search but without search keys
if (!reqvar_empty("quick_search") && trim($search_keys, ":") == "") {
    $in_search = false;
    $do_search = false;
}

if (reqvar("author_mode") == "last_posts" || !reqvar_empty("rate_statistics"))
{
    $sort = "desc";
} elseif (reqvar_empty("post_sort")) {
    if (!reqvar_empty("news_digest")) {
        $_REQUEST["post_sort"] = "desc";
        $sort = "desc";
    } else {
        $sort = "asc";
    }
} elseif (reqvar("post_sort") == "desc") {
    $sort = "desc";
} else {
    $sort = "asc";
}

//------------------------------------------------------------------
$user_tags = array();
$fmanager->get_user_tags($user_tags, $fmanager->get_user_id());
//------------------------------------------------------------------
$topic_list = array();
$pagination_info = array();
$pagination_info["total_count"] = 0;
$pagination_info["page_count"] = 1;
$pagination_info["page"] = reqvar_empty("spage") ? 1 : reqvar("spage");

$pagination_info["base_url"] = "search.php?" . $search_params;
$pagination_info["base_url_pagination"] = "search.php?" . $search_params . "&spage=$";

//------------------------------------------------------------------
do {
    //-------------------------------------------------
    // first entering or search submission with no params
    //-------------------------------------------------
    if (!$in_search) {
        MessageHandler::setFocusElement("search_keys");
        
        if ($do_search) {
            MessageHandler::setWarning(text("WarnSearchCriteriaNotSpecified"));
        }
        
        break;
    }
    //-------------------------------------------------
    // search submission
    //-------------------------------------------------
    if ($do_search) {
        $found_topic_count = 0;
        
        if (!$fmanager->fill_search_cache($search_hash, $found_topic_count, $sort)) {
            break;
        }
        
        if (!reqvar_empty("mark_read")) {
            $authors = array();
            if (!reqvar_empty("author") && reqvar("author") != try_translate("Subscription")) {
                $authors[] = reqvar("author");
            }
            
            if (!$fmanager->mark_subscriptions_read($authors)) {
                break;
            }
        }
        
        if ($found_topic_count == 0) {
            break;
        }
        
        // this part comes only when the search was successful
        
        if (!reqvar_empty("post_sort") && reqvar("author_mode") != "last_posts" && reqvar_empty("rate_statistics")) {
            $search_params .= "&post_sort=" . urlencode(reqvar("post_sort"));
        }
        
        if (!reqvar_empty("start_from")) {
            $search_params .= "&start_from=" . urlencode(reqvar("start_from"));
        }
        
        // the user wants last posts
        if (reqvar("author_mode") == "last_posts") {
            // we are in non-blocking readonly modus
            save_session();
            start_redirection_time_measure();
            header("Location: search_topic.php?" . $search_params);
            exit;
        }
        
        // the user wants to combine all found posts in one virtual topic, redirect him to the topic view
        if (!reqvar_empty("post_list")) {
            // we are in non-blocking readonly modus
            save_session();
            start_redirection_time_measure();
            header("Location: search_topic.php?" . $search_params);
            exit;
        }
        
        // the user wants to search only one topic and combine the found posts in one virtual topic, redirect him to the topic view
        if (!reqvar_empty("tid")) {
            // we are in non-blocking readonly modus
            save_session();
            start_redirection_time_measure();
            header("Location: search_topic.php?" . $search_params);
            exit;
        }
        
        if (!reqvar_empty("favourite_posts_only")) {
            // we are in non-blocking readonly modus
            save_session();
            start_redirection_time_measure();
            header("Location: search_topic.php?" . $search_params);
            exit;
        }
        
        // the user wants replies
        if (!reqvar_empty("replies_to")) {
            // we are in non-blocking readonly modus
            save_session();
            start_redirection_time_measure();
            header("Location: search_topic.php?" . $search_params);
            exit;
        }
        
        // the user wants digest
        if (!reqvar_empty("news_digest")) {
            // we are in non-blocking readonly modus
            save_session();
            start_redirection_time_measure();
            header("Location: search_topic.php?" . $search_params);
            exit;
        }
        
        // the user wants to see rated posts in one virtual topic, redirect him to the topic view
        if (!reqvar_empty("rate_statistics") || in_array(reqvar("author_mode"), array("author_likes", "author_liked", "author_dislikes", "author_disliked"))) {
            // we are in non-blocking readonly modus
            save_session();
            start_redirection_time_measure();
            header("Location: search_topic.php?" . $search_params);
            exit;
        }
        
        // we are in non-blocking readonly modus
        save_session();
        start_redirection_time_measure();
        header("Location: search.php?" . $search_params);
        exit;
    }
    
    //-------------------------------------------------
    // after search submission or pagination
    //-------------------------------------------------
    $fmanager->get_found_topics($search_hash, $topic_list, $pagination_info);
} while (false);
//------------------------------------------------------------------
if (!reqvar_empty("news_digest")) {
    // we are in non-blocking readonly modus
    save_session();
    start_redirection_time_measure();
    header("Location: new_messages.php" . (reqvar_empty("fid") ? "" : "?fid=" . reqvar("fid")));
    exit;
}
//------------------------------------------------------------------
$search_title = text("Search");

$search_title_appendix = $fmanager->build_search_title();
if (!empty($search_title_appendix)) {
    $search_title .= ": " . $search_title_appendix;
}
//------------------------------------------------------------------
$tid = reqvar("tid");
$fid = reqvar("fid");
if (empty($fid) && !empty($_REQUEST["forums"]) && count($_REQUEST["forums"]) == 1) {
    $fid = $_REQUEST["forums"][0];
}

$fid_for_url = $fid;
$forum_title = "";
$is_private = false;
$topic_name = "";
$clear_topic_name = "";
$forum_data = array();

if (!reqvar_empty("favourite_posts_only")) {
    $search_title = text("FavouriteMessages") . ", " . $search_title;
}

if (!empty($tid)) {
    $fid = "";
    $fmanager->get_topic_name($tid, $topic_name, $clear_topic_name, $fid);
    
    $fmanager->get_forum_data($fid, $forum_data);
    
    $forum_title = val_or_empty($forum_data["forum_name"]);
    $fid = val_or_empty($forum_data["id"]);
    $fid_for_url = $fid;
    if ($fid == $fmanager->get_private_forum_id()) {
        $fid_for_url = "private";
        $is_private = true;
        $forum_title = text("PrivateTopics");
    }
    
    // search in a single topic, prepend its name to the titles
    $search_title = $clear_topic_name . ", " . $search_title;
} elseif (!empty($fid)) {
    $fid_for_url = $fid;
    
    $is_private = false;
    $private_fid = $fmanager->get_private_forum_id();
    if ($fid == "private" || $fid == $private_fid) {
        $is_private = true;
        $fid = $private_fid;
        $fid_for_url = "private";
        $forum_title = text("PrivateTopics");
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
$ogtitle = $title;

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
$fmanager->track_hit($tid, $fid);

$all_forum_list = array();
$fmanager->get_forum_list($all_forum_list, false);

$online_users = array();
$forum_readers = array();
$topic_readers = array();
$topic_ignorers = array();
$topic_blocked_users = array();
$fmanager->get_online_users($online_users, $forum_readers, $topic_readers, $topic_ignorers, $topic_blocked_users, -1, -1);
//------------------------------------------------------------------
require_once "include/check_new_inc.php";
//------------------------------------------------------------------
$fmanager->get_topics_new_status($topic_list);
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
// we are in non-blocking readonly modus - save state the the messages are already shown
save_session();
//------------------------------------------------------------------
$view = "search.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>