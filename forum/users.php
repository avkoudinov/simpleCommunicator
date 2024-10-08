<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
$bot_data = detect_bot(val_or_empty($_SERVER["HTTP_USER_AGENT"]));
if (!empty($settings["hide_users_from_robots"]) && !empty($bot_data) && empty($bot_data["allowed"])) {
  echo "no data";
  exit;
}
//------------------------------------------------------------------
$title = text("Users") . " - " . get_site_name(current_language());
$ogtitle = text("Users") . " - " . get_site_name(current_language());
//------------------------------------------------------------------
MessageHandler::setFocusElement("user_name");
//------------------------------------------------------------------
if(!reqvar_empty("sort"))
  $_SESSION["last_user_sort"] = reqvar("sort");
  
if(empty($_SESSION["last_user_sort"]))
  $_SESSION["last_user_sort"] = "new_members";

$_REQUEST["sort"] = $_SESSION["last_user_sort"];

shrink_spaces($_REQUEST["user_name"]);

$user_list = array();
$pagination_info = array();
$pagination_info["total_count"] = 0;
$pagination_info["page_count"] = 1;
$pagination_info["page"] = reqvar_empty("upage") ? 1 : reqvar("upage");
$pagination_info["base_url"] = "users.php";
$pagination_info["base_url_pagination"] = "users.php?upage=$";

$fmanager->get_user_list($user_list, $pagination_info);
//------------------------------------------------------------------
$fmanager->track_hit("", "");
//------------------------------------------------------------------
$_SESSION["last_url"] = val_or_empty($_SERVER["REQUEST_URI"]);
//------------------------------------------------------------------
require_once "include/check_new_inc.php";
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "users.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>