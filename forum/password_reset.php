<?php
//------------------------------------------------------------------
session_set_cookie_params(0, str_replace(basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
$user_data = array();
if(!$fmanager->verify_reset_link($user_data))
{
  header("Location: password_restore.php");
  exit;
}
//------------------------------------------------------------------
$title = text("PasswordReset") . " - " . get_site_name(current_language());
$ogtitle = text("PasswordReset") . " - " . get_site_name(current_language());
//------------------------------------------------------------------
MessageHandler::setFocusElement("password");
//------------------------------------------------------------------
$fmanager->track_hit("", "");
//------------------------------------------------------------------
require_once "include/check_new_inc.php";
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "password_reset.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>