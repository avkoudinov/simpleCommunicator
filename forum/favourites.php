<?php
//------------------------------------------------------------------
session_set_cookie_params(0, str_replace(basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
if(!$fmanager->is_logged_in() && $fmanager->check_tor_ip(val_or_empty($_SERVER["REMOTE_ADDR"])) == "tor_block_read")
{
    MessageHandler::setError(text("ErrTorNodeBlocked"));
    
    header("location: login.php");
    exit;
}
//------------------------------------------------------------------
$title = text("Favourites");
$forum_title = text("Favourites");
//------------------------------------------------------------------
$forum_data = array();
$fmanager->get_favourites_data($forum_data);

if(!empty($forum_data["forum_name"]))
{
  $title = $forum_data["forum_name"];
  $forum_title = $forum_data["forum_name"];
}

$title = $forum_title . " - " . get_site_name(current_language());
$ogtitle = $title;

$topic_list = array();
$pagination_info = array();
$pagination_info["total_count"] = val_or_empty($forum_data["topic_count"]);
$pagination_info["ignored_count"] = val_or_empty($forum_data["ignored_topic_count"]);
$pagination_info["page_count"] = 1;
$pagination_info["page"] = reqvar_empty("fpage") ? 1 : reqvar("fpage");
$pagination_info["base_url"] = "favourites.php";
$pagination_info["base_url_pagination"] = "favourites.php?fpage=$";

$fmanager->get_forum_topics("favourites", -1, $topic_list, $pagination_info);

//------------------------------------------------------------------
$fmanager->track_hit("", "");

$online_users = array();
$forum_readers = array();
$topic_readers = array();
$topic_ignorers = array();
$topic_blocked_users = array();
$fmanager->get_online_users($online_users, $forum_readers, $topic_readers, $topic_ignorers, $topic_blocked_users, "", "");
//------------------------------------------------------------------
$_SESSION["last_url"] = val_or_empty($_SERVER["REQUEST_URI"]);
//------------------------------------------------------------------
require_once "include/check_new_inc.php";
//------------------------------------------------------------------
$fmanager->get_topics_new_status($topic_list);
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "favourites.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>