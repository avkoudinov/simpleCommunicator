<?php
//------------------------------------------------------------------
session_set_cookie_params(0, str_replace(basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "include/session_start_readonly_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
$bot_data = detect_bot(val_or_empty($_SERVER["HTTP_USER_AGENT"]));
if (!empty($settings["hide_users_from_robots"]) && !empty($bot_data) && empty($bot_data["allowed"])) {
  echo "no data";
  exit;
}
//------------------------------------------------------------------
$ogtype = "profile";
$title = text("GuestProfile");
$ogtitle = text("GuestProfile");
//------------------------------------------------------------------

$aname = reqvar("aname");
if (reqvar("guest") == "admin") {
  $aname = "admin";
}

$guest_data = array();
if(!$fmanager->get_guest_data_for_view(reqvar("guest"), $aname, $guest_data))
{
  // we are in non-blocking readonly modus
  save_session();
  
  header("location: " . $target_url);
  exit;
}

if (!empty($guest_data["user_name"])) {
  $title = text("GuestProfile") . ": " . $fmanager->get_display_name($guest_data["user_name"]);
}

$title .= " - " . get_site_name(current_language());
$ogtitle = $title;

$ogtype = "profile";
if (!empty($guest_data["avatar"])) {
  $ogimage = $guest_data["avatar"];
}  

if (empty($ogimage)) {
  $ogimage = $view_path . "images/guest.jpg";
}

$online_users = array();
$forum_readers = array();
$topic_readers = array();
$topic_ignorers = array();
$topic_blocked_users = array();
$fmanager->get_online_users($online_users, $forum_readers, $topic_readers, $topic_ignorers, $topic_blocked_users, "", "");

$read_topics = array();
if(!$fmanager->get_guest_read_topics(reqvar("guest"), $read_topics))
{
  // we are in non-blocking readonly modus
  save_session();
  
  header("location: " . $target_url);
  exit;
}

$ignores = array();
$ignored = array();
$ignored_topics = array();
$hides = array();
$hidden = array();

if (!$fmanager->get_guest_ignore_info(reqvar("guest"), $aname, $ignores, $ignored, $ignored_topics, $hides, $hidden)) {
  // we are in non-blocking readonly modus
  save_session();

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
$view = "view_guest_profile.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>
