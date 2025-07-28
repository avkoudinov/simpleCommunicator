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
$title = text("UserProfile");
//------------------------------------------------------------------
$user_data = array();
if(!$fmanager->get_user_data(reqvar("uid"), $user_data))
{
  // we are in non-blocking readonly modus
  save_session();

  header("location: " . $target_url);
  exit;
}

if (!empty($user_data["user_name"])) {
  $title .= ": " . $user_data["user_name"];
}

$ogtype = "profile";
if (!empty($user_data["photo"])) {
  $ogimage = $user_data["photo"];
}  
elseif (!empty($user_data["avatar"])) {
  $ogimage = $user_data["avatar"]; 
}  

if (empty($ogimage)) {
  $ogimage = $view_path . "images/guest.jpg";
}

$title .= " - " . get_site_name(current_language());
$ogtitle = $title;

$ignores = array();
$ignored = array();
if(!$fmanager->get_user_ignore_info(reqvar("uid"), $ignores, $ignored))
{
  // we are in non-blocking readonly modus
  save_session();

  header("location: " . $target_url);
  exit;
}

$hides = array();
$hidden = array();
if(!$fmanager->get_user_hide_info(reqvar("uid"), $hides, $hidden))
{
  // we are in non-blocking readonly modus
  save_session();

  header("location: " . $target_url);
  exit;
}

$total_post_count = $user_data["post_count"];
$activity_data = array();
if(!$fmanager->get_user_forum_activity(reqvar("uid"), $activity_data, $total_post_count))
{
  // we are in non-blocking readonly modus
  save_session();

  header("location: " . $target_url);
  exit;
}

$read_topics = array();
if(!$fmanager->get_read_topics(reqvar("uid"), $read_topics))
{
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
$view = "view_profile.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>
