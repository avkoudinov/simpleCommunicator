<?php
//------------------------------------------------------------------
session_set_cookie_params(0, str_replace(basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
$title = text("UserAgreement") . " - " . get_site_name(current_language());
$ogtitle = text("UserAgreement") . " - " . get_site_name(current_language());
//------------------------------------------------------------------
$fmanager->track_hit("", "");
//------------------------------------------------------------------
require_once "include/check_new_inc.php";
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "user_agreement.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>