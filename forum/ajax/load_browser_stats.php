<?php
//-----------------------------------------------------------------------
session_set_cookie_params(0, str_replace("ajax/" . basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "../include/session_start_inc.php";

$ajax_processing = true;

define('STATISTICS_REQUEST', -2);
require_once "../include/general_inc.php";
//-----------------------------------------------------------------------
if(detect_bot(val_or_empty($_SERVER["HTTP_USER_AGENT"])) != "")
{
    exit;
}
//------------------------------------------------------------------
if(!$fmanager->check_hash())
{
  exit;
}
//------------------------------------------------------------------
$fmanager->track_hit("", "");

$_SESSION["browser_stats"] = array();
$_SESSION["os_stats"] = array();
$_SESSION["bot_stats"] = array();

if(!$fmanager->get_browser_stat($_SESSION["browser_stats"], $_SESSION["os_stats"], $_SESSION["bot_stats"]))
{
    exit;
}

require $view_path . "load_browser_stats_inc.php";

require_once "../include/final_inc.php";
?>
