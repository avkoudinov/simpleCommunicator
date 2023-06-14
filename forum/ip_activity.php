<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
if(!$fmanager->is_admin())
{
  MessageHandler::setError(text("ErrActionNotAllowed"));

  header("location: " . $target_url);
  exit;
}
//------------------------------------------------------------------
$title = text("IPActivity");
//------------------------------------------------------------------
$ip_activity_list = array();
$fmanager->get_ip_activity(reqvar("ip"), $ip_activity_list);
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
$view = "ip_activity.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>