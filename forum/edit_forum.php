<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
if(!$fmanager->is_admin())
{
  MessageHandler::setError(text("ErrActionNotAllowed"));
  header("Location: " . $target_url);
  exit;
}
//------------------------------------------------------------------
if(reqvar_empty("fid"))
  $title = text("CreateForum");
else
  $title = text("EditForum");

MessageHandler::setFocusElement("forum_name");
//------------------------------------------------------------------
$forum_data = array();
if(!reqvar_empty("fid") && !$fmanager->get_forum_data(reqvar("fid"), $forum_data))
{
  header("Location: " . $target_url);
  exit;
}

if(!empty($forum_data["is_sys"]))
{
  MessageHandler::setError(text("ErrSystemForumEdit"));
  header("Location: " . $target_url);
  exit;
}

$moderator_list = array();
if(!reqvar_empty("fid")) 
{
  $fmanager->get_moderator_list(reqvar("fid"), $moderator_list);
  $fmanager->track_hit("", reqvar("fid"));
}
//------------------------------------------------------------------
require_once "include/check_new_inc.php";
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "edit_forum.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>