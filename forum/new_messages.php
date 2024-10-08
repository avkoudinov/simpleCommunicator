<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
$bot_data = detect_bot(val_or_empty($_SERVER["HTTP_USER_AGENT"]));
if(!empty($bot_data) && empty($bot_data["allowed"])) {
    echo "no data";
    exit;
}
//------------------------------------------------------------------
if (!$fmanager->is_logged_in() && $fmanager->check_tor_ip(val_or_empty($_SERVER["REMOTE_ADDR"])) == "tor_block_read") {
    MessageHandler::setError(text("ErrTorNodeBlocked"));
    
    header("location: login.php");
    exit;
}
//------------------------------------------------------------------
$forum_title = text("NewMessages");
//------------------------------------------------------------------
$fid = reqvar("fid");
$fid_for_url = $fid;
$forum_data = array();

$is_private = false;
$private_fid = $fmanager->get_private_forum_id();
if ($fid == "private" || $fid == $private_fid) {
    $is_private = true;
    $fid = $private_fid;
    $fid_for_url = "private";
    $forum_title = text("PrivateTopics") . ": " . text("NewMessages");

    if (!$fmanager->get_forum_data($private_fid, $forum_data)) {
        header("location: " . $target_url);
        exit;
    }
} elseif ($fid == "favourites" || $fid == -1) {
    $fid = -1;
    $fid_for_url = "favourites";
    $forum_title = text("Favourites") . ": " . text("NewMessages");
} elseif ($fid == "my_topics" || $fid == -2) {
    $fid = -2;
    $fid_for_url = "my_topics";
    $forum_title = text("MyTopics") . ": " . text("NewMessages");
} elseif ($fid == "my_part_topics" || $fid == -3) {
    $fid = -3;
    $fid_for_url = "my_part_topics";
    $forum_title = text("ParticipatedTopics") . ": " . text("NewMessages");
} elseif (!empty($fid)) {
    if ($fmanager->need_forum_password("", $fid)) {
        $_SESSION["last_url_askpwd"] = val_or_empty($_SERVER["REQUEST_URI"]);
        header("location: ask_password.php?fid=" . $fid);
        exit;
    }
    
    $forum_name = "-";
    if (!$fmanager->has_access_to_forum($fid, $forum_name, true)) {
        if (!$fmanager->is_logged_in()) {
            MessageHandler::setWarning(text("MsgTryLogin"));
            $_SESSION["last_url_asklogin"] = val_or_empty($_SERVER["REQUEST_URI"]);
            $target_url = "login.php";
        }
        
        header("location: " . $target_url);
        exit;
    }

    if (!$fmanager->get_forum_data($fid, $forum_data)) {
        header("location: " . $target_url);
        exit;
    }

    $forum_title = $forum_name . ": " . text("NewMessages");
}

$title = $forum_title . " - " . get_site_name(current_language());
$ogtitle = $title;

//------------------------------------------------------------------

$base_url = "";
$base_url_concat = "?";
$base_url_complete = "";

if(!empty($fid_for_url))
{
  $base_url .= "?fid=" . $fid_for_url;
  $base_url_complete .= "?fid=" . $fid_for_url;
  $base_url_concat = "&";
}

$fpage_appendix = "";
if(!reqvar_empty("fpage"))
{
  if(empty($base_url_complete)) $base_url_complete .= "?fpage=" . reqvar("fpage");
  else                          $base_url_complete .= "&fpage=" . reqvar("fpage");
  
  $fpage_appendix = "&fpage=" . reqvar("fpage");
}

$base_url = "new_messages.php" . $base_url;
$base_url_complete = "new_messages.php" . $base_url_complete;
$base_url_concat = $base_url . $base_url_concat;

//------------------------------------------------------------------
$fmanager->check_new_events($new_events_count, $new_mod_events_count);
$fmanager->calculate_new_messages(true /* no cache */);

$topics_with_new_count = empty($_SESSION["new_messages_info_cache"]["data"]["visible_topics"]) ? 0 : count($_SESSION["new_messages_info_cache"]["data"]["visible_topics"]);
$favourites_with_new_count = empty($_SESSION["new_messages_info_cache"]["data"]["favourites"]) ? 0 : count($_SESSION["new_messages_info_cache"]["data"]["favourites"]);
$my_topics_with_new_count = empty($_SESSION["new_messages_info_cache"]["data"]["my_topics"]) ? 0 : count($_SESSION["new_messages_info_cache"]["data"]["my_topics"]);
$my_part_topics_with_new_count = empty($_SESSION["new_messages_info_cache"]["data"]["my_part_topics"]) ? 0 : count($_SESSION["new_messages_info_cache"]["data"]["my_part_topics"]);
$private_topics_with_new_count = empty($_SESSION["new_messages_info_cache"]["data"]["private_topics"]) ? 0 : count($_SESSION["new_messages_info_cache"]["data"]["private_topics"]);
$subscription_authors_new_messages_count = empty($_SESSION["new_messages_info_cache"]["data"]["subscription_authors_new_messages_count"]) ? 0 : $_SESSION["new_messages_info_cache"]["data"]["subscription_authors_new_messages_count"];
$subscription_authors_new_topics_count = empty($_SESSION["new_messages_info_cache"]["data"]["subscription_authors_new_topics_count"]) ? 0 : $_SESSION["new_messages_info_cache"]["data"]["subscription_authors_new_topics_count"];
//------------------------------------------------------------------

$pagination_info = array();

$pagination_info["ignored_count"] = 0;
$pagination_info["total_count"] = $topics_with_new_count;

if ($fid == -1 || $fid == "favourites") {
    $pagination_info["total_count"] = $favourites_with_new_count;
    if ($favourites_with_new_count) {
        $pagination_info["ignored_count"] = $fmanager->calculate_ignored_topics(array_keys($_SESSION["new_messages_info_cache"]["data"]["favourites"]));
    }
} elseif ($fid == -2 || $fid == "my_topics") {
    $pagination_info["total_count"] = $my_topics_with_new_count;
    if ($my_topics_with_new_count) {
        $pagination_info["ignored_count"] = $fmanager->calculate_ignored_topics(array_keys($_SESSION["new_messages_info_cache"]["data"]["my_topics"]));
    }
} elseif ($fid == -3 || $fid == "my_part_topics") {
    $pagination_info["total_count"] = $my_part_topics_with_new_count;
    if ($my_part_topics_with_new_count) {
        $pagination_info["ignored_count"] = $fmanager->calculate_ignored_topics(array_keys($_SESSION["new_messages_info_cache"]["data"]["my_part_topics"]));
    }
} elseif ($is_private) {
    $pagination_info["total_count"] = $private_topics_with_new_count;
    if ($private_topics_with_new_count) {
        $pagination_info["ignored_count"] = $fmanager->calculate_ignored_topics(array_keys($_SESSION["new_messages_info_cache"]["data"]["private_topics"]));
    }
} elseif (!empty($fid)) {
    if (!empty($_SESSION["new_messages_info_cache"]["data"]["forums"][$fid])) {
        $pagination_info["total_count"] = count($_SESSION["new_messages_info_cache"]["data"]["forums"][$fid]);
        $pagination_info["ignored_count"] = $fmanager->calculate_ignored_topics(array_keys($_SESSION["new_messages_info_cache"]["data"]["forums"][$fid]));
    } else {
        $pagination_info["total_count"] = 0;
    }
} else {
    if (!empty($_SESSION["new_messages_info_cache"]["data"]["visible_topics"])) {
        $pagination_info["ignored_count"] = $fmanager->calculate_ignored_topics(array_keys($_SESSION["new_messages_info_cache"]["data"]["visible_topics"]));
    }
}

$pagination_info["page_count"] = 1;
$pagination_info["page"] = reqvar_empty("fpage") ? 1 : reqvar("fpage");
$pagination_info["base_url"] = $base_url;
$pagination_info["base_url_pagination"] = $base_url_concat . "fpage=$";

$topic_list = array();
$fmanager->get_forum_topics("new_messages", $fid, $topic_list, $pagination_info);

//------------------------------------------------------------------
$fmanager->track_hit("", $fid);

$online_users = array();
$forum_readers = array();
$topic_readers = array();
$topic_ignorers = array();
$topic_blocked_users = array();
$fmanager->get_online_users($online_users, $forum_readers, $topic_readers, $topic_ignorers, $topic_blocked_users, $fid, "");
//------------------------------------------------------------------
$_SESSION["last_url"] = val_or_empty($_SERVER["REQUEST_URI"]);
//------------------------------------------------------------------
// check_new_inc.php is not necessary because called explicitly
//------------------------------------------------------------------
$forum_data['topics_with_new_count'] = 0;

if (!empty($_SESSION["new_messages_info_cache"]["data"]["forums"][$fid])) {
    $forum_data['topics_with_new_count'] = count($_SESSION["new_messages_info_cache"]["data"]["forums"][$fid]);
}

if ($is_private && !empty($_SESSION["new_messages_info_cache"]["data"]["private_topics"])) {
    $forum_data['topics_with_new_count'] = count($_SESSION["new_messages_info_cache"]["data"]["private_topics"]);
}
elseif(val_or_empty($fid_for_url) == "favourites" && !empty($_SESSION["new_messages_info_cache"]["data"]["favourites"])) {
    $forum_data['topics_with_new_count'] = count($_SESSION["new_messages_info_cache"]["data"]["favourites"]);
}
elseif(val_or_empty($fid_for_url) == "my_topics" && !empty($_SESSION["new_messages_info_cache"]["data"]["my_topics"])) {
    $forum_data['topics_with_new_count'] = count($_SESSION["new_messages_info_cache"]["data"]["my_topics"]);
}
elseif(val_or_empty($fid_for_url) == "my_part_topics" && !empty($_SESSION["new_messages_info_cache"]["data"]["my_part_topics"])) {
    $forum_data['topics_with_new_count'] = count($_SESSION["new_messages_info_cache"]["data"]["my_part_topics"]);
}

$fmanager->get_topics_new_status($topic_list);
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "new_messages.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>