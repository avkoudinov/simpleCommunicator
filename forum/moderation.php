<?php
//------------------------------------------------------------------
session_set_cookie_params(0, str_replace(basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
if(!$fmanager->is_moderator())
{
  MessageHandler::setError(text("ErrActionNotAllowed"));
  header("Location: " . $target_url);
  exit;
}
//------------------------------------------------------------------
$title = text("Moderation") . " - " . get_site_name(current_language());
$ogtitle = text("Moderation") . " - " . get_site_name(current_language());
//------------------------------------------------------------------
MessageHandler::setFocusElement("ip");
//------------------------------------------------------------------
$moderated_forum_list = array();
$moderated_restricted_forum_list = array();
$fmanager->get_moderated_forums($moderated_forum_list, $moderated_restricted_forum_list);

$start_date = adjust_and_format_timezone(time(), text("DateFormat"));
//------------------------------------------------------------------
$fmanager->track_hit("", "");
//------------------------------------------------------------------
$_SESSION["last_url"] = val_or_empty($_SERVER["REQUEST_URI"]);
//------------------------------------------------------------------
require_once "include/check_new_inc.php";
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "moderation.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>