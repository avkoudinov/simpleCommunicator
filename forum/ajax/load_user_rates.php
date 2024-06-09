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
if(reqvar_empty("uid"))
{
  exit;
}
//------------------------------------------------------------------
$total_likes = 0;
$total_liked = 0;
$likes = array();
$liked = array();

$total_dislikes = 0;
$total_disliked = 0;
$dislikes = array();
$disliked = array();

if(!$fmanager->get_user_rating_info(reqvar("uid"), 
                  $likes, $liked, $total_likes, $total_liked,
                  $dislikes, $disliked, $total_dislikes, $total_disliked
             ))
{
  exit;
}

$fmanager->track_hit("", "");

require $view_path . "user_rates_inc.php";

require_once "../include/final_inc.php";
?>
