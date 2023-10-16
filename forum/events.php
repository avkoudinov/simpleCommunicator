<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
if (!$fmanager->is_logged_in()) {
    $_SESSION["last_url"] = val_or_empty($_SERVER["REQUEST_URI"]);
    header("Location: login.php");
    exit;
}

if ($fmanager->is_master_admin()) {
    MessageHandler::setWarning(text("MsgMasterAdminWarning"));
    header("Location: " . $target_url);
    exit;
}

//------------------------------------------------------------------
$title = text("Events") . " - " . get_site_name(current_language());
$ogtitle = text("Events") . " - " . get_site_name(current_language());
$forum_title = text("Events");
//------------------------------------------------------------------
if ($fmanager->check_hash()) {
    $uri = val_or_empty($_SERVER["REQUEST_URI"]);
    
    $uri = preg_replace("/store_event_filter=\\d&?/", "", $uri);
    $uri = preg_replace("/reset_event_filter=\\d&?/", "", $uri);
    $uri = preg_replace("/hash=.+&?/", "", $uri);
    $uri = rtrim($uri, "&?");
    
    if (!reqvar_empty("store_event_filter")) {
        MessageHandler::setInfo(text("MsgFilterStored"));
        set_cookie("q_stored_event_filter", reqvar("event_type"), time() + 90 * 24 * 3600);
        header("Location: " . $uri);
        exit;
    }
    if (!reqvar_empty("reset_event_filter")) {
        set_cookie("q_stored_event_filter", "", time());
        
        header("Location: " . $uri);
        exit;
    }
}

$query_string = "";
$fmanager->apply_elog_filter(reqvar("apply_filter"), $query_string);
if (!reqvar_empty("apply_filter")) {
    header("Location: events.php" . $query_string);
    exit;
}

$param_string = "";
if(!reqvar_empty("filter")) $param_string = "filter=" . xrawurlencode(reqvar("filter"));
if(!empty($param_string)) $param_string = "?" . $param_string;

$base_url = "events.php{$param_string}";
//------------------------------------------------------------------
$pagination_info = array();
$pagination_info["total_count"] = 0;
$pagination_info["page_count"] = 1;
$pagination_info["page"] = reqvar_empty("epage") ? 1 : reqvar("epage");
$pagination_info["base_url"] = "events.php{$param_string}";
$pagination_info["base_url_pagination"] = empty($param_string) ? "events.php?epage=$" : "events.php{$param_string}&epage=$";

$event_list = array();
$fmanager->get_event_list($event_list, $pagination_info);

if ((reqvar_empty("event_type") || reqvar("event_type") == "all_events") &&
    reqvar_empty("author") && reqvar_empty("start_date") && reqvar_empty("end_date") &&
    reqvar_empty("forum") && reqvar_empty("topic_name") &&
    empty($_SESSION["event_log_filter"]["event_type"])
   ) {
    $fmanager->track_last_events_read_date();
} else {
    $fmanager->unset_events_new_status($event_list);
}
//------------------------------------------------------------------
$filter_list = array();

$filter_list["all_events"] = text("AllEvents");
$filter_list["unprocessed_events"] = text("UnprocessedEvents");
$filter_list["mod_events"] = text("ModerationEvents");
$filter_list["unprocessed_mod_events"] = text("UnprocessedModerationEvents");
$filter_list["complaints"] = text("Complaints");
$filter_list["warnings"] = text("Warnings");
$filter_list["attention_events"] = text("AttentionEvents");
$filter_list["likes"] = text("Likes");
$filter_list["dislikes"] = text("Dislikes");
$filter_list["replies"] = text("Answers");

//------------------------------------------------------------------
$fmanager->track_hit("", "");

$online_users = array();
$forum_readers = array();
$topic_readers = array();
$topic_ignorers = array();
$fmanager->get_online_users($online_users, $forum_readers, $topic_readers, $topic_ignorers, -1, -1);
//------------------------------------------------------------------
$_SESSION["last_url"] = val_or_empty($_SERVER["REQUEST_URI"]);
//------------------------------------------------------------------
require_once "include/check_new_inc.php";
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "events.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>