<?php
//-----------------------------------------------------------------------
session_set_cookie_params(0, str_replace("ajax/" . basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "../include/session_start_readonly_inc.php";

$ajax_processing = true;
require_once "../include/general_inc.php";
//-----------------------------------------------------------------------
if (detect_bot(val_or_empty($_SERVER["HTTP_USER_AGENT"])) != "") {
    exit;
}
//------------------------------------------------------------------
if (!$fmanager->check_hash()) {
    exit;
}

if (empty(reqvar("post")) || !is_numeric(reqvar("post"))) {
    exit;
}

$user_data = array();
$post_list = array();

$fid = "";
$tid = "";
if (!$fmanager->get_post_data(reqvar("post"), $fid, $tid, $post_list, $user_data)) {
    exit;
}

$fmanager->track_hit($tid, $fid);

$hide_from_robots = 0;
$fmanager->get_topic_forum_id($tid, $fid, $hide_from_robots);
$fid_for_url = $fid;

if (!empty($hide_from_robots) && detect_bot(val_or_empty($_SERVER["HTTP_USER_AGENT"])) != "") {
    exit;
}

if ($fid == $fmanager->get_private_forum_id()) {
    $fid_for_url = "private";
    
    if (!$fmanager->is_logged_in()) {
        exit;
    }
    
    if ($fmanager->is_master_admin()) {
        MessageHandler::setWarning(text("MsgMasterAdminWarning"));
        exit;
    }
}

if (!$fmanager->has_access_to_topic($tid, true)) {
    exit;
}

if ($fmanager->need_forum_password($tid, $fid)) {
    exit;
}

$topic_data = array();
if (!$fmanager->get_topic_data($tid, $topic_data)) {
    header("location: " . $target_url);
    exit;
}

if (!empty($topic_data["merge_target_topic"])) {
    exit;
}
//------------------------------------------------------------------
$title = text("Topic");
$topic_title = text("Topic");
$forum_title = text("Forum");

if (!empty($topic_data["topic_name"])) {
    $title = $topic_data["topic_name"];
    $topic_title = $topic_data["topic_name"];
}

$forum_data = array();

if (!empty($topic_data["is_private"])) {
    if (!$fmanager->get_private_forum_data($forum_data)) {
        exit;
    }
} else {
    if (!$fmanager->get_forum_data($fid, $forum_data)) {
        exit;
    }
}

if (!empty($forum_data["forum_name"])) {
    $forum_title = $forum_data["forum_name"];
}

$online_users = array();
$forum_readers = array();
$topic_readers = array();
$topic_ignorers = array();
$topic_blocked_users = array();
$fmanager->get_online_users($online_users, $forum_readers, $topic_readers, $topic_ignorers, $topic_blocked_users, $fid, $tid);

$in_search = !reqvar_empty("in_search");

$bulk_delete_count = 5;
if (defined('BULK_DELETE_COUNT') && is_numeric(BULK_DELETE_COUNT)) {
    $bulk_delete_count = BULK_DELETE_COUNT;
}

$may_write_to_topic = true;
if (!empty($topic_data["closed"])) {
    $may_write_to_topic = false;
}

if ($fmanager->is_topic_moderator($tid)) {
    $may_write_to_topic = true;
}

if (!empty($forum_data["closed"])) {
    $may_write_to_topic = false;
}

if (!empty($forum_data["blocked"])) {
    $may_write_to_topic = false;
}

if (!empty($topic_data["blocked"])) {
    $may_write_to_topic = false;
}

if ($fmanager->is_admin() || $fmanager->is_forum_moderator($fid)) {
    $may_write_to_topic = true;
}

if (!empty($_SESSION["blocked"])) {
    $may_write_to_topic = false;
}

if ($fmanager->is_logged_in() && empty($_SESSION["approved"])) {
    $may_write_to_topic = false;
}

if ($fmanager->is_logged_in() && empty($_SESSION["activated"])) {
    $may_write_to_topic = false;
}

if (!$fmanager->is_logged_in() && !empty($_SESSION["ip_blocked"])) {
    $may_write_to_topic = false;
}

$poll_rendered = true;
if (!empty($topic_data["first_topic_pinned_message"])) {
    if ($topic_data["first_topic_pinned_message"] == reqvar("post")) {
        $poll_rendered = false;
    }
} else {
    if (val_or_empty($topic_data["first_topic_message"]) == reqvar("post")) {
        $poll_rendered = false;
    }
}

//-----------------------------------------------------------------------
?>

<?php foreach ($post_list as $pid => $pinfo): ?>
    
    <?php
    require $view_path . "topic_message_tpl_inc.php";
    ?>

<?php endforeach; ?>

<!-- This is a data transfer div contaainer -->

<div id="ajax_data" style="display:none"
    <?php if (MessageHandler::infosExist()): ?>
        data-INFO_MESSAGE="<?php echo_html(MessageHandler::getInfos()); ?>"
    <?php endif; ?>
    <?php if (MessageHandler::warningsExist()): ?>
        data-WARNING_MESSAGE="<?php echo_html(MessageHandler::getWarnings()); ?>"
    <?php endif; ?>
    <?php if (defined('SHOW_PROGRAM_WARNINGS') && SHOW_PROGRAM_WARNINGS && MessageHandler::progWarningsExist()): ?>
        data-PROG_WARNING="<?php echo_html(MessageHandler::getProgWarnings()); ?>"
    <?php endif; ?>
    <?php if (MessageHandler::errorsExist()): ?>
        data-ERROR_MESSAGE="<?php echo_html(MessageHandler::getErrors()); ?>"
    <?php endif; ?>
    <?php if (MessageHandler::debugMessageExists()): ?>
        data-DEBUG_MESSAGE="<?php echo_html(MessageHandler::getDebugMessages()); ?>"
    <?php endif; ?>

     data-AUTO_HIDE_INFO="<?php echo_html(MessageHandler::autoHideInfo()); ?>"
     data-ACTIVE_TAB="<?php echo_html(MessageHandler::getActiveTab()); ?>"
     data-FOCUS_ELEMENT="<?php echo_html(MessageHandler::getFocusElement()); ?>"
     data-ERROR_ELEMENT="<?php echo_html(MessageHandler::getErrorElement()); ?>"
></div>

<?php
require_once "../include/final_inc.php";
?>
