<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
if (detect_bot(val_or_empty($_SERVER["HTTP_USER_AGENT"])) != "") {
    echo "no data";
    exit;
}
//------------------------------------------------------------------
$query_string = "";
$fmanager->apply_mlog_filter(reqvar("apply_filter"), $query_string);
if (!reqvar_empty("apply_filter")) {
    header("Location: moderation_log.php" . $query_string);
    exit;
}

if (!$fmanager->is_moderator_log_visible()) {
    MessageHandler::setError(text("ErrActionNotAllowed"));
    header("Location: " . $target_url);
    exit;
}

$param_string = $fmanager->build_mlog_paramter_string();
if(!empty($param_string)) $param_string = "?" . $param_string;
//------------------------------------------------------------------
$title = text("ModeratorLog") . " - " . get_site_name(current_language());
$ogtitle = text("ModeratorLog") . " - " . get_site_name(current_language());
//------------------------------------------------------------------
$event_list = array();
$pagination_info = array();
$pagination_info["page_count"] = 1;
$pagination_info["page"] = reqvar_empty("mpage") ? 1 : reqvar("mpage");
$pagination_info["base_url"] = "moderation_log.php{$param_string}";
$pagination_info["base_url_pagination"] = empty($param_string) ? "moderation_log.php?mpage=$" : "moderation_log.php{$param_string}&mpage=$";

$fmanager->get_moderator_events($event_list, $pagination_info);

$action_list = array();
$fmanager->build_action_list($action_list);

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
$view = "moderation_log.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>