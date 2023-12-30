<?php
//-----------------------------------------------------------------------
session_set_cookie_params(0, str_replace("ajax/" . basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "../include/session_start_readonly_inc.php";

$ajax_processing = true;
require_once "../include/general_inc.php";
//-----------------------------------------------------------------------
if(detect_bot(val_or_empty($_SERVER["HTTP_USER_AGENT"])) != "")
{
  exit;
}
//------------------------------------------------------------------
if(reqvar_empty("topic") || reqvar_empty("ctrl") || reqvar_empty("base_url"))
{
  exit;
}

if(!$fmanager->check_hash())
{
  exit;
}

if(!file_exists(APPLICATION_ROOT . $view_path . reqvar("ctrl") . "_inc.php"))
{
  exit;
}

$hide_from_robots = 0;
$tid = reqvar("topic");
$fid = "";
$fmanager->get_topic_forum_id($tid, $fid, $hide_from_robots);
$fid_for_url = $fid;

if(!empty($hide_from_robots) && detect_bot(val_or_empty($_SERVER["HTTP_USER_AGENT"])) != "")
{
  exit;
}

$is_private = false;
if($fid == $fmanager->get_private_forum_id())
{
  $is_private = true;
  $fid_for_url = "private";

  if(!$fmanager->is_logged_in())
  {
    exit;
  }

  if($fmanager->is_master_admin())
  {
    MessageHandler::setWarning(text("MsgMasterAdminWarning"));
    exit;
  }
}

if(!$fmanager->has_access_to_topic(reqvar("topic"), true))
{
  exit;
}

if($fmanager->need_forum_password(reqvar("topic"), $fid))
{
  exit;
}

$topic_data = array();
if(!$fmanager->get_topic_data(reqvar("topic"), $topic_data))
{
  exit;
}

if(!empty($topic_data["merge_target_topic"]))
{
  exit;
}

$pagination_info = array();
$pagination_info["posts_per_page"] = $fmanager->get_posts_per_page();
$pagination_info["total_count"] = val_or_empty($topic_data["post_count"]);
$pagination_info["ignored_hidden"] = (!empty($_SESSION["hide_ignored"]) && !$fmanager->is_forum_moderator($fid) && !$fmanager->is_topic_moderator($tid));
$pagination_info["ignored_count"] = val_or_empty($topic_data["ignored_post_count"]);
$pagination_info["ignored_comment_count"] = val_or_empty($topic_data["ignored_comment_count"]);
$pagination_info["first_topic_message"] = val_or_empty($topic_data["first_topic_message"]);
$pagination_info["first_topic_pinned_message"] = val_or_empty($topic_data["first_topic_pinned_message"]);
$pagination_info["last_topic_message"] = val_or_empty($topic_data["last_topic_message"]);
$pagination_info["topic_has_pinned_post"] = val_or_empty($topic_data["has_pinned_post"]);
$pagination_info["topic_has_deleted_posts"] = val_or_empty($topic_data["post_count_total"]) > val_or_empty($topic_data["post_count_nondeleted"]);

$pagination_info["mode"] = reqvar("mode");
$pagination_info["startmsg"] = reqvar("startmsg");
$pagination_info["msg"] = reqvar("msg");

$pagination_info["pinned_message_count"] = reqvar("pinned_message_count");
$pagination_info["first_page_message"] = reqvar("first_page_message");
$pagination_info["last_page_message"] = reqvar("last_page_message");

$pagination_info["loaded_message_count"] = reqvar("loaded_message_count");

$show_deleted = !empty($_SESSION["show_deleted"]);

// If we enter a deleted topic and we may do this,
// we want to see the posts.
if (!empty($topic_data["deleted"])) {
  $show_deleted = true;
}

if (!$fmanager->calculate_message_positions($tid, $pagination_info, $show_deleted)) {
  exit;
}

$base_url = reqvar("base_url");
$all_entry_post = reqvar("all_entry_post");

require $view_path . reqvar("ctrl") . "_inc.php";

require_once "../include/final_inc.php";
?>
