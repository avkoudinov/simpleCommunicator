<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
$title = text("Authorization");
MessageHandler::setFocusElement("user_login");
//------------------------------------------------------------------
$failed_login_count = 0;

$fmanager->count_failed_logins($failed_login_count);

if(stripos(val_or_empty($_SERVER['HTTP_REFERER']), get_host_address() . get_url_path()) !== FALSE &&
   stripos(val_or_empty($_SERVER['HTTP_REFERER']), "login.php") === FALSE &&
   stripos(val_or_empty($_SERVER['HTTP_REFERER']), "password_reset.php") === FALSE &&
   stripos(val_or_empty($_SERVER['HTTP_REFERER']), "password_restore.php") === FALSE &&
   stripos(val_or_empty($_SERVER['HTTP_REFERER']), "registration.php") === FALSE
  )
{
  $_SESSION["last_url"] = str_replace(get_host_address(), "", val_or_empty($_SERVER["HTTP_REFERER"]));
}
//------------------------------------------------------------------
$fmanager->track_hit("", "");
//------------------------------------------------------------------
require_once "include/check_new_inc.php";
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "login.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>