<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
$title = text("Registration") . " - " . get_site_name(current_language());
$ogtitle = text("Registration") . " - " . get_site_name(current_language());

if($fmanager->is_logged_in()) {
    MessageHandler::setWarning(text("ErrLogoutForRegistration"));
} else {
    MessageHandler::setFocusElement("user_name");
    MessageHandler::setWarning(text("RegistrationWarning"));
}
//------------------------------------------------------------------
$fmanager->track_hit("", "");
//------------------------------------------------------------------
require_once "include/check_new_inc.php";
//------------------------------------------------------------------
$_SESSION["last_url"] = val_or_empty($_SERVER["REQUEST_URI"]);
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "registration.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>