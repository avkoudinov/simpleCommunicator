<?php
//------------------------------------------------------------------
session_set_cookie_params(0, str_replace(basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
$bot_data = detect_bot(val_or_empty($_SERVER["HTTP_USER_AGENT"]));
if(!empty($bot_data) && empty($bot_data["allowed"])) {
    echo "no data";
    exit;
}
//------------------------------------------------------------------
$forum_data = array();

$user_data = array();
$is_private = 0;

$final_url = $_SERVER['REQUEST_URI'];

$fid = reqvar("fid");
if($fid == "private")
{
  $is_private = 1;
  
  if(!$fmanager->is_logged_in())
  {
    $_SESSION["last_url"] = val_or_empty($_SERVER["REQUEST_URI"]);
    header("Location: login.php");
    exit;
  }

  if($fmanager->is_master_admin())
  {
    MessageHandler::setWarning(text("MsgMasterAdminWarning"));
    header("Location: " . $target_url);
    exit;
  }

  if (!$fmanager->is_admin() && !$fmanager->is_privileged()) {
    $time_since_registration = 0;
    if (!$fmanager->get_time_since_registration($time_since_registration)) {
        header("location: " . $target_url);
        exit;
    }
    
    if ($time_since_registration < 1 * 24 * 3600) {
        MessageHandler::setError(sprintf(text("ErrMessagePrivateMessagesAllowed"), 1));
        header("Location: " . $target_url);
        exit;
    }
  }

  if(!reqvar_empty("receiver"))
  {
    if(!$fmanager->get_user_data(reqvar("receiver"), $user_data))
    {
      header("location: " . $target_url);
      exit;
    }
  }
  else
  {
    $is_private = 2;
  }

  if(!$fmanager->get_private_forum_data($forum_data))
  {
    header("location: " . $target_url);
    exit;
  }

  $fid = $forum_data["id"];
  $fid_for_url = "private";
}
else
{
  if(!$fmanager->get_forum_data($fid, $forum_data))
  {
    header("location: " . $target_url);
    exit;
  }

  $fid_for_url = $fid;
}

if($fmanager->need_forum_password("", $fid))
{
  $_SESSION["last_url"] = val_or_empty($_SERVER["REQUEST_URI"]);
  header("location: ask_password.php?fid=" . $fid);
  exit;
}

$forum_name = "-";
if(!$fmanager->has_access_to_forum($fid, $forum_name, true))
{
  if (!$fmanager->is_logged_in()) {
      MessageHandler::setWarning(text("MsgTryLogin"));
      
      $_SESSION["last_url_asklogin"] = val_or_empty($_SERVER["REQUEST_URI"]);
      $target_url = "login.php";
  }

  header("location: " . $target_url);
  exit;
}

//------------------------------------------------------------------
$title = text("NewTopic");
$forum_title = text("Forum");
if($fmanager->get_user_name()) MessageHandler::setFocusElement("subject");
else                           MessageHandler::setFocusElement("author");
//------------------------------------------------------------------
if(!empty($forum_data["forum_name"]))
{
  $forum_title = $forum_data["forum_name"];
}

if($is_private == 2)
{
  $title = text("CreatePrivateTopic");
}
elseif($is_private == 1)
{
  $title = text("SendPersonalMessage");
}

$title .= " - " . get_site_name(current_language());
$ogtitle = $title;

$max_att_size = $settings["max_att_size"];
if($max_att_size > 1024)
{
  $max_att_size = number_format($max_att_size/1024, 1, ",", "");
  $max_att_size .= " " . text("MB");
}
else
{
  $max_att_size .= " " . text("KB");
}

$max_att_size_audiovideo = $settings["max_att_size_audiovideo"];
if($max_att_size_audiovideo > 1024)
{
  $max_att_size_audiovideo = number_format($max_att_size_audiovideo/1024, 1, ",", "");
  $max_att_size_audiovideo .= " " . text("MB");
}
else
{
  $max_att_size_audiovideo .= " " . text("KB");
}

$may_write_to_forum = true;

if (!empty($forum_data["closed"])) {
    $may_write_to_forum = false;
}

if (!empty($forum_data["blocked"])) {
    $may_write_to_forum = false;
}

if ($fmanager->is_admin() || $fmanager->is_forum_moderator($fid)) {
    $may_write_to_forum = true;
}

if (!empty($_SESSION["blocked"])) {
    $may_write_to_forum = false;
}

if ($fmanager->is_logged_in() && empty($_SESSION["approved"])) {
    $may_write_to_forum = false;
}

if ($fmanager->is_logged_in() && empty($_SESSION["activated"])) {
    $may_write_to_forum = false;
}

if (!$fmanager->is_logged_in() && !empty($_SESSION["ip_blocked"])) {
    $may_write_to_forum = false;
}

if (!empty($settings["archive_mode"]))
{
    $may_write_to_forum = false;
}

//------------------------------------------------------------------
$fmanager->track_hit("", $fid);
//------------------------------------------------------------------
require_once "include/check_new_inc.php";
//------------------------------------------------------------------
$forum_data['topics_with_new_count'] = 0;

if (!empty($_SESSION["new_messages_info_cache"]["data"]["forums"][$fid])) {
    $forum_data['topics_with_new_count'] = count($_SESSION["new_messages_info_cache"]["data"]["forums"][$fid]);
}

if ($is_private && !empty($_SESSION["new_messages_info_cache"]["data"]["private_topics"])) {
    $forum_data['topics_with_new_count'] = count($_SESSION["new_messages_info_cache"]["data"]["private_topics"]);
}
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "new_topic.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>