<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
if(!$fmanager->is_logged_in())
{
  $_SESSION["last_url_asklogin"] = val_or_empty($_SERVER["REQUEST_URI"]);
  header("Location: login.php");
  exit;
}

if($fmanager->is_master_admin())
{
  MessageHandler::setWarning(text("MsgMasterAdminWarning"));
  
  header("Location: " . $target_url);
  exit;
}
//------------------------------------------------------------------
$title = text("Profile");
//------------------------------------------------------------------
$skin_list = array();
$property_list = array();
$fmanager->get_skin_list($skin_list, $property_list);

$all_forum_list = array();
$fmanager->get_forum_list($all_forum_list, false);

$user_data = array();
if(!$fmanager->get_user_data($fmanager->get_user_id(), $user_data))
{
  header("location: " . $target_url);
  exit;
}

if (!empty($user_data["user_name"])) {
  $title .= ": " . $user_data["user_name"];
}

$ogtype = "profile";
$title .= " - " . get_site_name(current_language());
$ogtitle = $title;

if (!empty($user_data["photo"])) {
  $ogimage = $user_data["photo"];
}  
elseif (!empty($user_data["avatar"])) {
  $ogimage = $user_data["avatar"];
}  

if (empty($ogimage)) {
  $ogimage = $view_path . "images/guest.jpg";
}

$ignores = array();
$ignored = array();
if(!$fmanager->get_user_ignore_info($fmanager->get_user_id(), $ignores, $ignored))
{
  header("location: " . $target_url);
  exit;
}

$start_date = date(text("DateFormat"), xstrtotime("-30 days"));

//------------------------------------------------------------------
$fmanager->track_hit("", "");
//------------------------------------------------------------------
require_once "include/check_new_inc.php";
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "profile.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>