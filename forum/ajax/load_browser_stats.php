<?php
//-----------------------------------------------------------------------
session_set_cookie_params(0, str_replace("ajax/" . basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "../include/session_start_readonly_inc.php";

$ajax_processing = true;

define('STATISTICS_REQUEST', 1);
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

$browser_stat = array();
$os_stat = array();
$bot_stat = array();

if(!$fmanager->get_browser_stat($browser_stat, $os_stat, $bot_stat))
{
    exit;
}

$fmanager->track_hit("", "");

require $view_path . "load_browser_stats_inc.php";

require_once "../include/final_inc.php";
?>
