<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
if (!$fmanager->is_logged_in()) {
    $_SESSION["last_url"] = val_or_empty($_SERVER["REQUEST_URI"]);
    header("Location: login.php");
    exit;
}

if ($fmanager->is_master_admin()) {
    MessageHandler::setWarning(text("MsgMasterAdminWarning"));
    header("Location: " . $target_url);
    exit;
}

//------------------------------------------------------------------
$title = text("Subscription") . " - " . get_site_name(current_language());
$ogtitle = text("Subscription") . " - " . get_site_name(current_language());
$forum_title = text("Subscription");
//------------------------------------------------------------------
$fmanager->check_new_events($new_events_count, $new_mod_events_count);
$fmanager->calculate_new_messages();

$topics_with_new_count = empty($_SESSION["new_messages_info_cache"]["data"]["visible_topics"]) ? 0 : count($_SESSION["new_messages_info_cache"]["data"]["visible_topics"]);
$favourites_with_new_count = empty($_SESSION["new_messages_info_cache"]["data"]["favourites"]) ? 0 : count($_SESSION["new_messages_info_cache"]["data"]["favourites"]);
$my_topics_with_new_count = empty($_SESSION["new_messages_info_cache"]["data"]["my_topics"]) ? 0 : count($_SESSION["new_messages_info_cache"]["data"]["my_topics"]);
$my_part_topics_with_new_count = empty($_SESSION["new_messages_info_cache"]["data"]["my_part_topics"]) ? 0 : count($_SESSION["new_messages_info_cache"]["data"]["my_part_topics"]);
$private_topics_with_new_count = empty($_SESSION["new_messages_info_cache"]["data"]["private_topics"]) ? 0 : count($_SESSION["new_messages_info_cache"]["data"]["private_topics"]);
$subscription_authors_new_messages_count = empty($_SESSION["new_messages_info_cache"]["data"]["subscription_authors_new_messages_count"]) ? 0 : $_SESSION["new_messages_info_cache"]["data"]["subscription_authors_new_messages_count"];
$subscription_authors_new_topics_count = empty($_SESSION["new_messages_info_cache"]["data"]["subscription_authors_new_topics_count"]) ? 0 : $_SESSION["new_messages_info_cache"]["data"]["subscription_authors_new_topics_count"];
//------------------------------------------------------------------

$online_users = array();
$forum_readers = array();
$topic_readers = array();
$topic_ignorers = array();
$topic_blocked_users = array();
$fmanager->get_online_users($online_users, $forum_readers, $topic_readers, $topic_ignorers, $topic_blocked_users, "", "");

if (empty($_SESSION["subscribed_authors"])) {
    $_SESSION["subscribed_authors"] = array();
}

$subscribed_authors = array();
$fmanager->get_subscribed_authors($subscribed_authors);

$fmanager->get_authors_new_status($subscribed_authors);

$start_date = date(text("DateFormat"), xstrtotime("-30 days"));

//------------------------------------------------------------------
$fmanager->track_hit("", "");
//------------------------------------------------------------------
$_SESSION["last_url"] = val_or_empty($_SERVER["REQUEST_URI"]);
//------------------------------------------------------------------
// check_new_inc.php is not necessary because called explicitly
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "subscription.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>