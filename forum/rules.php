<?php
//------------------------------------------------------------------
session_set_cookie_params(0, str_replace(basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
$title = text("Rules") . " - " . get_site_name(current_language());
$ogtitle = text("Rules") . " - " . get_site_name(current_language());
//------------------------------------------------------------------
$fmanager->track_hit("", "");
//------------------------------------------------------------------
$_SESSION["last_url"] = val_or_empty($_SERVER["REQUEST_URI"]);
//------------------------------------------------------------------
require_once "include/check_new_inc.php";
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "rules.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>