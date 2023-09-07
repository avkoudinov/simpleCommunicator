<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
if ($fmanager->is_logged_in() && !$fmanager->is_master_admin()) {
    header("Location: profile.php");
    exit;
}
//------------------------------------------------------------------
$title = text("Profile") . " - " . get_site_name(current_language());
$ogtitle = text("Profile") . " - " . get_site_name(current_language());
//------------------------------------------------------------------
MessageHandler::setFocusElement("user_name");
//------------------------------------------------------------------
$skin_list = array();
$property_list = array();
$fmanager->get_skin_list($skin_list, $property_list);

$all_forum_list = array();
$fmanager->get_forum_list($all_forum_list, false);

$user_data = array();
if (!$fmanager->get_guest_data($user_data)) {
    header("location: " . $target_url);
    exit;
}

$ignores = array();
$ignored = array();
$ignored_topics = array();
$hides = array();
$hidden = array();
$aname = System::generateHash($READ_MARKER . val_or_empty($_SESSION["user_name"]), SALT_KEY);

if (!$fmanager->get_guest_ignore_info("", $aname, $ignores, $ignored, $ignored_topics, $hides, $hidden)) {
    header("location: " . $target_url);
    exit;
}

//------------------------------------------------------------------
$fmanager->track_hit("", "");
//------------------------------------------------------------------
require_once "include/check_new_inc.php";
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "guest_profile.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>