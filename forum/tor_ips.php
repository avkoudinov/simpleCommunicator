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

$tor_ips = array();
$fmanager->get_tor_ip_list($tor_ips);

//------------------------------------------------------------------
$title = text("TorIPs") . " - " . get_site_name(current_language());
$ogtitle = text("TorIPs") . " - " . get_site_name(current_language());
//------------------------------------------------------------------
$_SESSION["last_url"] = val_or_empty($_SERVER["REQUEST_URI"]);
//------------------------------------------------------------------
require_once "include/check_new_inc.php";
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "tor_ips.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>