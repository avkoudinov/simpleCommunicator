<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
if(!$fmanager->is_moderator())
{
  MessageHandler::setError(text("ErrActionNotAllowed"));

  header("location: " . $target_url);
  exit;
}
//------------------------------------------------------------------
$title = text("ModerateRates");
//------------------------------------------------------------------
$user_data = array();
if(!$fmanager->get_user_data(reqvar("uid"), $user_data))
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

$rate_info = array();
if(!$fmanager->get_last_user_rates(reqvar("uid"), $rate_info))
{
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
$view = "rate_moderation.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>