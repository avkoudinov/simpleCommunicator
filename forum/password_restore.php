<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
$title = text("PasswordRestoration");
MessageHandler::setFocusElement("user_email");
MessageHandler::setWarning(text("PasswordRestoreWarning"));
//------------------------------------------------------------------
$fmanager->track_hit("", "");
//------------------------------------------------------------------
require_once "include/check_new_inc.php";
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "password_restore.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>