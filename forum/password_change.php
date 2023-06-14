<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
if(!$fmanager->is_master_admin())
{
  MessageHandler::setError(text("ErrActionNotAllowed"));

  header("location: " . $target_url);
  exit;
}
//------------------------------------------------------------------
$title = text("PasswordChange");
$view = "password_change.php";
MessageHandler::setFocusElement("current_password");
//------------------------------------------------------------------
$fmanager->track_hit("", "");
//------------------------------------------------------------------
require_once "include/check_new_inc.php";
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>