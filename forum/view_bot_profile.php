<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_readonly_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
if(detect_bot(val_or_empty($_SERVER["HTTP_USER_AGENT"])) != "" && !empty($settings["hide_users_from_robots"]))
{
  echo "no data";
  exit;
}
//------------------------------------------------------------------
$ogtype = "profile";
$title = text("BotProfile") . " - " . get_site_name(current_language());
$ogtitle = text("BotProfile") . " - " . get_site_name(current_language());
//------------------------------------------------------------------

$bot_data = array();
if(!$fmanager->get_bot_data_for_view(reqvar("bot"), $bot_data))
{
  // we are in non-blocking readonly modus
  save_session();
  
  header("location: " . $target_url);
  exit;
}

$online_users = array();
$forum_readers = array();
$topic_readers = array();
$topic_ignorers = array();
$topic_blocked_users = array();
$fmanager->get_online_users($online_users, $forum_readers, $topic_readers, $topic_ignorers, $topic_blocked_users, "", "");

$read_topics = array();
if(!$fmanager->get_guest_read_topics("#bot#" . reqvar("bot"), $read_topics))
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
$view = "view_bot_profile.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>
