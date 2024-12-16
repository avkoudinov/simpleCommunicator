<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_inc.php";

define('STATISTICS_REQUEST', -8);
require_once "include/general_inc.php";
//------------------------------------------------------------------
$bot_data = detect_bot(val_or_empty($_SERVER["HTTP_USER_AGENT"]));
if(!empty($bot_data) && empty($bot_data["allowed"]))
{
  echo "no data";
  exit;
}
//------------------------------------------------------------------
$fmanager->track_hit("", "");
//------------------------------------------------------------------
$title = text("LoadStatistics") . " - " . get_site_name(current_language());
$ogtitle = text("LoadStatistics") . " - " . get_site_name(current_language());
//------------------------------------------------------------------
$query_string = "";
$fmanager->apply_load_statistics_filter($query_string);
if(!reqvar_empty("apply_filter"))
{
  header("Location: load_statistics.php" . $query_string);
  exit;
}

$total_user_hits_count = 0;
$total_ip_hits_count = 0;
$total_agents_hits_count = 0;
$user_activity = array();
$ip_activity = array();
$agent_activity = array();
if(!$fmanager->gen_activity_statistics($user_activity, $ip_activity, $agent_activity, $total_user_hits_count, $total_ip_hits_count, $total_agents_hits_count))
{
  header("location: " . $target_url);
  exit;
}

$banned_ips = array();
if(!$fmanager->get_banned_ips($banned_ips))
{
  header("location: " . $target_url);
  exit;
}

//------------------------------------------------------------------
$online_users = array();
$forum_readers = array();
$topic_readers = array();
$topic_ignorers = array();
$topic_blocked_users = array();
$fmanager->get_online_users($online_users, $forum_readers, $topic_readers, $topic_ignorers, $topic_blocked_users, -1, -1);
//------------------------------------------------------------------
$_SESSION["last_url"] = val_or_empty($_SERVER["REQUEST_URI"]);
//------------------------------------------------------------------
require_once "include/check_new_inc.php";
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "load_statistics.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>
