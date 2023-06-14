<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
$forum_data = array();
if(!$fmanager->get_forum_data(reqvar("fid"), $forum_data))
{
  header("location: forums.php");
  exit;
}
//------------------------------------------------------------------
if (!empty($forum_data["hide_from_robots"]) && detect_bot(val_or_empty($_SERVER["HTTP_USER_AGENT"])) != "") {
    echo "no data";
    exit;
}
//------------------------------------------------------------------
$title = text("Password");
$subtitle = text("Password");
$entrance_warning = sprintf(text("EntranceWarning"), $forum_data["forum_name"]);
MessageHandler::setFocusElement("password");
//------------------------------------------------------------------
$fmanager->track_hit("", reqvar("fid"));
//------------------------------------------------------------------
require_once "include/check_new_inc.php";
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "ask_password.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>