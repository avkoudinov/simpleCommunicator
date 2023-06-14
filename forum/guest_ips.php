<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
if(!$fmanager->is_admin())
{
  MessageHandler::setError(text("ErrActionNotAllowed"));
  header("Location: " . $target_url);
  exit;
}

if($fmanager->demo_mode())
{
  MessageHandler::setWarning(text("MsgDemoMode"));
  header("Location: " . $target_url);
  exit;
}

$fmanager->refresh_tor_ips();

$guest_ips = array();
$ips = "";
$fmanager->get_guest_ip_list($guest_ips, $ips);

//------------------------------------------------------------------
$title = text("GuestIPs");
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
$view = "guest_ips.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>