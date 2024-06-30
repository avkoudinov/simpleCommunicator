<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_inc.php";

define('STATISTICS_REQUEST', 1);
require_once "include/general_inc.php";
//------------------------------------------------------------------
$bot_data = detect_bot(val_or_empty($_SERVER["HTTP_USER_AGENT"]));
if(!empty($bot_data) && empty($bot_data["allowed"])) {
  echo "no data";
  exit;
}
//------------------------------------------------------------------
$fmanager->track_hit("", "");
//------------------------------------------------------------------
$title = text("Statistics") . " - " . get_site_name(current_language());
$ogtitle = text("Statistics") . " - " . get_site_name(current_language());
//------------------------------------------------------------------

if(!reqvar_empty("period"))
{
  $period_appendix = "&statistics_period=" . reqvar("period");
} else {
  $_REQUEST["period"] = "last_year";
  $period_appendix = "&statistics_period=last_year";
}

$query_string = "?period=" . reqvar("period");

$forum_appendix = "";
if(!reqvar_empty("fid"))
{
  $forum_appendix = "&forums[]=" . reqvar("fid");
  $query_string .= "&fid=" . reqvar("fid");
}

if(!reqvar_empty("apply_filter"))
{
  header("Location: statistics.php" . $query_string);
  exit;
}

if(!$fmanager->get_forum_activity())
{
  header("location: " . $target_url);
  exit;
}
//------------------------------------------------------------------
$_SESSION["last_url"] = val_or_empty($_SERVER["REQUEST_URI"]);
//------------------------------------------------------------------
require_once "include/check_new_inc.php";
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "statistics.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>
