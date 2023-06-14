<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
if(!$fmanager->is_admin())
{
  MessageHandler::setError(text("ErrActionNotAllowed"));

  header("location: " . $target_url);
  exit;
}
//------------------------------------------------------------------
$title = text("ReadmarkerModeration");
MessageHandler::setFocusElement("search_key");
//------------------------------------------------------------------
if(!reqvar_empty("sort"))
  $_SESSION["last_read_marker_sort"] = reqvar("sort");
  
if(empty($_SESSION["last_read_marker_sort"]))
  $_SESSION["last_read_marker_sort"] = "last_activity";

$_REQUEST["sort"] = $_SESSION["last_read_marker_sort"];

shrink_spaces($_REQUEST["search_key"]);

$read_marker_list = array();
$pagination_info = array();
$pagination_info["total_count"] = 0;
$pagination_info["page_count"] = 1;
$pagination_info["page"] = reqvar_empty("rmpage") ? 1 : reqvar("rmpage");
$fmanager->get_read_marker_list($read_marker_list, $pagination_info);
//------------------------------------------------------------------
$fmanager->track_hit("", "");

$online_users = array();
$forum_readers = array();
$topic_readers = array();
$topic_ignorers = array();
$fmanager->get_online_users($online_users, $forum_readers, $topic_readers, $topic_ignorers, -1, -1);
//------------------------------------------------------------------
$_SESSION["last_url"] = val_or_empty($_SERVER["REQUEST_URI"]);
//------------------------------------------------------------------
require_once "include/check_new_inc.php";
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "rm_moderation.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>