<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
if(!is_maintenance())
{
  header("Location: forums.php");
  exit;
}
//------------------------------------------------------------------
if (!empty($maintenance_comment_lang[current_language()])) $maintenance_comment = $maintenance_comment_lang[current_language()];
if (!empty($maintenance_link[current_language()])) $maintenance_link = $maintenance_link[current_language()];
//------------------------------------------------------------------
$title = text("Maintenance") . " - " . get_site_name(current_language());
$ogtitle = text("Maintenance") . " - " . get_site_name(current_language());
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
require_once $view_path . "maintenance.php";
//------------------------------------------------------------------
?>