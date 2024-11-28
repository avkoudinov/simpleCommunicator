<?php
//-----------------------------------------------------------------------
session_set_cookie_params(0, str_replace("ajax/" . basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "../include/session_start_inc.php";

$ajax_processing = true;

define('STATISTICS_REQUEST', -13);
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

$_SESSION["country_stats"] = array();
$_SESSION["country_bot_stats"] = array();
$_SESSION["city_stats"] = array();
$_SESSION["proxy_stats"] = array();
$_SESSION["ip_type_stats"] = array();

if(!$fmanager->get_geo_stat($_SESSION["country_stats"], $_SESSION["country_bot_stats"], $_SESSION["city_stats"], $_SESSION["proxy_stats"], $_SESSION["ip_type_stats"]))
{
    exit;
}

require $view_path . "load_geo_stats_inc.php";

require_once "../include/final_inc.php";
?>
