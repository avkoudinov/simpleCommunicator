<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
$title = text("Contact") . " - " . get_site_name(current_language());
$ogtitle = text("Contact") . " - " . get_site_name(current_language());
//------------------------------------------------------------------
MessageHandler::setFocusElement("email");
//------------------------------------------------------------------
$sender_email = "";
if(!empty($_SESSION["sender_email"])) $sender_email = $_SESSION["sender_email"];
elseif(!empty($_SESSION["user_email"])) $sender_email = $_SESSION["user_email"];
//------------------------------------------------------------------
$fmanager->track_hit("", "");
//------------------------------------------------------------------
require_once "include/check_new_inc.php";
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "contact.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>