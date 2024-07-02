<?php
//-----------------------------------------------------------------------
session_set_cookie_params(0, str_replace("ajax/" . basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "../include/session_start_readonly_inc.php";

$ajax_processing = true;

define('STATISTICS_REQUEST', -4);
require_once "../include/general_inc.php";
//-----------------------------------------------------------------------
if(detect_bot(val_or_empty($_SERVER["HTTP_USER_AGENT"])) != "")
{
    exit;
}
//------------------------------------------------------------------
if (!$fmanager->check_hash()) {
    exit;
}

$fmanager->track_hit("", "");

if (!reqvar_empty("period")) {
    $period_appendix = "&statistics_period=" . reqvar("period");
} else {
    $_REQUEST["period"] = "last_half_year";
    $period_appendix = "&statistics_period=last_half_year";
}

$forum_appendix = "";
if (!reqvar_empty("fid")) {
    $forum_appendix = "&forums[]=" . reqvar("fid");
}

$total_likes = 0;
$total_dislikes = 0;
$total_rates = 0;

if (!$fmanager->get_total_rating_info(reqvar("fid"), reqvar("period"), $total_likes, $total_dislikes, $total_rates)) {
    exit;
}

require $view_path . "total_rates_inc.php";

require_once "../include/final_inc.php";
?>
