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
require_once "include/final_inc.php";
//------------------------------------------------------------------
require_once $view_path . "maintenance.php";
//------------------------------------------------------------------
?>