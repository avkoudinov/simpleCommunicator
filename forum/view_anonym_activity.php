<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
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
$title = text("AnonymActivity");
$ogtitle = text("AnonymActivity");
//------------------------------------------------------------------

profile_message("-------------------");
profile_message("Start getting anonym activity");

$guest_data = array();
if(!$fmanager->get_anonym_activity($guest_data))
{
  // we are in non-blocking readonly modus
  save_session();
  
  header("location: " . $target_url);
  exit;
}

profile_point("get_anonym_activity executed");

$title .= " - " . get_site_name(current_language());
$ogtitle = $title;

$ogtype = "profile";
if (!empty($guest_data["avatar"])) {
  $ogimage = $guest_data["avatar"];
}  

if (empty($ogimage)) {
  $ogimage = $view_path . "images/guests.jpg";
}

$online_users = array();
$forum_readers = array();
$topic_readers = array();
$topic_ignorers = array();
$topic_blocked_users = array();
$fmanager->get_online_users($online_users, $forum_readers, $topic_readers, $topic_ignorers, $topic_blocked_users, "", "");

profile_point("get_online_users executed");

$read_topics = array();
if(!$fmanager->get_guest_read_topics("", $read_topics))
{
  // we are in non-blocking readonly modus
  save_session();
  
  header("location: " . $target_url);
  exit;
}

profile_point("get_guest_read_topics executed");

//------------------------------------------------------------------
$fmanager->track_hit("", "");

profile_point("track_hit executed");
//------------------------------------------------------------------
require_once "include/check_new_inc.php";
profile_point("check_new_inc executed");
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "view_anonym_activity.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>
