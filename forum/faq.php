<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
$title = text("FAQ") . " - " . get_site_name(current_language());
$ogtitle = text("FAQ") . " - " . get_site_name(current_language());
//------------------------------------------------------------------
$fmanager->track_hit("", "");
//------------------------------------------------------------------
require_once "include/check_new_inc.php";
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "faq.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>