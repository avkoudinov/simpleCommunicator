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
$title = text("Forums") . " - " . get_site_name(current_language());
$ogtitle = text("Forums") . " - " . get_site_name(current_language());
//------------------------------------------------------------------
$groupped_forum_list = array();
$fmanager->get_groupped_forum_list($groupped_forum_list, !empty($_SESSION["hide_ignored"]));

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
$fmanager->get_forums_new_status($groupped_forum_list);
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "forums.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>