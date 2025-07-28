<?php
//------------------------------------------------------------------
session_set_cookie_params(0, str_replace(basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
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
$title = text("Settings") . " - " . get_site_name(current_language());
$ogtitle = text("Settings") . " - " . get_site_name(current_language());
//------------------------------------------------------------------
$settings = array();
$fmanager->get_settings($settings, true);

$skin_list = array();
$property_list = array();
$fmanager->get_skin_list($skin_list, $property_list);
//------------------------------------------------------------------
$fmanager->track_hit("", "");
//------------------------------------------------------------------
require_once "include/check_new_inc.php";
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "settings.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>