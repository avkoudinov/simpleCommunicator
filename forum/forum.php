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
$fid = reqvar("fid");
$fid_for_url = $fid;

$is_private = false;
$private_fid = $fmanager->get_private_forum_id();
if ($fid == "private" || $fid == $private_fid) {
    $is_private = true;
    $fid = $private_fid;
    $fid_for_url = "private";
}

if ($is_private && !$fmanager->is_logged_in()) {
    MessageHandler::setWarning(text("MsgTryLogin"));

    $_SESSION["last_url_asklogin"] = val_or_empty($_SERVER["REQUEST_URI"]);
    header("Location: login.php?fid=private");
    exit;
}

if ($is_private && $fmanager->is_master_admin()) {
    MessageHandler::setWarning(text("MsgMasterAdminWarning"));
    header("Location: " . $target_url);
    exit;
}

$forum_data = array();
if ($is_private) {
    if (!$fmanager->get_private_forum_data($forum_data)) {
        header("location: " . $target_url);
        exit;
    }
    
    $fid = $forum_data["id"];
} else {
    $fid = reqvar("fid");
    if (!$fmanager->get_forum_data($fid, $forum_data)) {
        header("location: " . $target_url);
        exit;
    }
}

if (!empty($forum_data["hide_from_robots"]) && detect_bot(val_or_empty($_SERVER["HTTP_USER_AGENT"])) != "") {
    echo "no data";
    exit;
}

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
        $target_url = "login.php?fid=$fid";
    }

    header("location: " . $target_url);
    exit;
}

$base_url = "forum.php?fid=" . $fid_for_url;
//------------------------------------------------------------------
$title = text("Forum") . " - " . get_site_name(current_language());
$ogtitle = text("Forum") . " - " . get_site_name(current_language());
$forum_title = text("Forum");
//------------------------------------------------------------------
if (!empty($forum_data["forum_name"])) {
    $title = $forum_data["forum_name"] . " - " . get_site_name(current_language());
    $ogtitle = $forum_data["forum_name"] . " - " . get_site_name(current_language());
    $forum_title = $forum_data["forum_name"];
}

if (!empty($forum_data["forum_description"])) {
    $ogdescription = $forum_data["forum_description"];
}

$topic_list = array();
$pagination_info = array();
$pagination_info["ignored_count"] = val_or_empty($forum_data["ignored_topic_count"]);
$pagination_info["page_count"] = 1;
$pagination_info["page"] = reqvar_empty("fpage") ? 1 : reqvar("fpage");
$pagination_info["base_url"] = $base_url;
$pagination_info["base_url_pagination"] = $base_url . "&fpage=$";


$fmanager->update_forum_read_status($fid);

// for the mode hide ignored
$pagination_info["total_count"] = $forum_data["topic_count"] - $forum_data["hidden_topic_count"];

$fmanager->get_forum_topics("default", $fid, $topic_list, $pagination_info);

$pagination_info["total_count"] = $forum_data["topic_count"];

$may_write = true;
if (!empty($forum_data["closed"])) {
    $may_write = false;
}

if (!empty($forum_data["blocked"])) {
    $may_write = false;
}

if ($fmanager->is_forum_moderator($fid) || $fmanager->is_admin()) {
    $may_write = true;
}

if (!empty($_SESSION["blocked"])) {
    $may_write = false;
}

if ($fmanager->is_logged_in() && empty($_SESSION["approved"])) {
    $may_write = false;
}

if ($fmanager->is_logged_in() && empty($_SESSION["activated"])) {
    $may_write = false;
}

if (!$fmanager->is_logged_in() && !empty($_SESSION["ip_blocked"])) {
    $may_write = false;
}

if (!empty($settings["archive_mode"]))
{
    $may_write = false;
}

$topic_limit = 0;
$topic_time_to_next = 0;
$fmanager->check_topic_limit($fid, $topic_limit, $topic_time_to_next);
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
require_once "include/check_new_inc.php";
//------------------------------------------------------------------
$forum_data['topics_with_new_count'] = 0;

if (!empty($_SESSION["new_messages_info_cache"]["data"]["forums"][$fid])) {
    $forum_data['topics_with_new_count'] = count($_SESSION["new_messages_info_cache"]["data"]["forums"][$fid]);
}

if ($is_private && !empty($_SESSION["new_messages_info_cache"]["data"]["private_topics"])) {
    $forum_data['topics_with_new_count'] = count($_SESSION["new_messages_info_cache"]["data"]["private_topics"]);
}

$fmanager->get_topics_new_status($topic_list);
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "forum.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>