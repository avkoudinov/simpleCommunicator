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
$title = text("ForumGroups") . " - " . get_site_name(current_language());
$ogtitle = text("ForumGroups") . " - " . get_site_name(current_language());
//------------------------------------------------------------------
$forum_groups = array();
$fmanager->get_forum_groups($forum_groups);

$skin_list = array();
$property_list = array();
$fmanager->get_skin_list($skin_list, $property_list);
//------------------------------------------------------------------
$fmanager->track_hit("", "");
//------------------------------------------------------------------
require_once "include/check_new_inc.php";
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "forum_groups.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>