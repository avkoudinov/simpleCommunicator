<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
if (detect_bot(val_or_empty($_SERVER["HTTP_USER_AGENT"])) != "" && !empty($settings["hide_users_from_robots"])) {
    echo "no data";
    exit;
}
//------------------------------------------------------------------
$query_string = "";
$fmanager->apply_mlog_filter(reqvar("apply_filter"), $query_string);
if (!reqvar_empty("apply_filter")) {
    $_SESSION["jump_to_log"] = 1;
    header("Location: user_moderation.php" . $query_string);
    exit;
}
//------------------------------------------------------------------
if (!$fmanager->is_moderator_log_visible() &&
    $fmanager->get_user_id() != reqvar("uid")) {
    MessageHandler::setError(text("ErrActionNotAllowed"));
    header("Location: " . $target_url);
    exit;
}

$user_data = array();
if (!$fmanager->get_user_data(reqvar("uid"), $user_data)) {
    header("location: " . $target_url);
    exit;
}

//------------------------------------------------------------------
if ($fmanager->is_moderator()) {
    $title = text("ModerateUser");
} else {
    $title = text("ModeratorLog");
}

if (!empty($user_data["user_name"])) {
    $title .= ": " . $user_data["user_name"];
}

$ogtype = "profile";
$title .= " - " . get_site_name(current_language());
$ogtitle = $title;

if (!empty($user_data["photo"])) {
  $ogimage = $user_data["photo"];
}  
elseif (!empty($user_data["avatar"])) {
  $ogimage = $user_data["avatar"];
}  


//------------------------------------------------------------------
$event_list = array();
$pagination_info = array();
$pagination_info["page_count"] = 1;
$pagination_info["page"] = reqvar_empty("mpage") ? 1 : reqvar("mpage");

$fmanager->get_moderator_events($event_list, $pagination_info);

$action_list = array();
$fmanager->build_action_list($action_list);

$reason_list = array();

$reason_list["roughness"] = text("Roughness");
$reason_list["relatives_insult"] = text("RelativesInsult");
$reason_list["flood"] = text("Flood");
$reason_list["spam"] = text("Spam");
$reason_list["author_wish"] = text("AuthorWish");
$reason_list["illegal_content"] = text("IllegalContent");
$reason_list["unlawful_statement"] = text("UnlawfulStatement");
$reason_list["ethnic_hatred"] = text("EthnicHatred");
$reason_list["extremism"] = text("Extremism");

if ($fmanager->global_ban_allowed()) {
    $reason_list["author_death"] = text("AuthorDeath");
    $reason_list["account_loss"] = text("AccountLoss");
}

$reason_list["other_reason"] = text("OtherReason");

$moderated_forum_list = array();
$moderated_restricted_forum_list = array();
$fmanager->get_moderated_forums($moderated_forum_list, $moderated_restricted_forum_list);
//------------------------------------------------------------------
$fmanager->track_hit("", "");

$online_users = array();
$forum_readers = array();
$topic_readers = array();
$topic_ignorers = array();
$fmanager->get_online_users($online_users, $forum_readers, $topic_readers, $topic_ignorers, -1, -1);
//------------------------------------------------------------------
require_once "include/check_new_inc.php";
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "user_moderation.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>