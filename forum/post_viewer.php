<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------

$title = "Просмотр постов";
$view = "post_viewer.php";

$user_tags = array();
$fmanager->get_user_tags($user_tags, $fmanager->get_user_id());

$fmanager->track_hit("", "");
//------------------------------------------------------------------
require_once "include/check_new_inc.php";
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>