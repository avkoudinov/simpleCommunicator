<?php
//-----------------------------------------------------------------------
session_set_cookie_params(0, str_replace("ajax/" . basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "../include/session_start_inc.php";

$ajax_processing = true;
require_once "../include/general_inc.php";
//-----------------------------------------------------------------------
if (detect_bot(val_or_empty($_SERVER["HTTP_USER_AGENT"])) != "") {
    exit;
}
//------------------------------------------------------------------
if (reqvar_empty("tid")) {
    exit;
}

if (!$fmanager->check_hash()) {
    exit;
}

$hide_from_robots = 0;
$tid = reqvar("tid");
$fid = "";
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

$user_data = array();
$post_list = array();

if (!$fmanager->get_new_topic_posts(reqvar("last_read_message"), reqvar("limit"), $fid, $tid, $post_list, $user_data, !empty($topic_data["deleted"]))) {
    exit;
}

if (!empty($_SESSION["skip_next_hit"])) {
    unset($_SESSION["skip_next_hit"]);
} else {
    $fmanager->track_hit($tid, $fid);
}

$fmanager->reset_unnecessary_events($post_list);

$online_users = array();
$forum_readers = array();
$topic_readers = array();
$topic_ignorers = array();
$topic_blocked_users = array();
$fmanager->get_online_users($online_users, $forum_readers, $topic_readers, $topic_ignorers, $topic_blocked_users, $fid, $tid);

// handle the cases if the there is no or one new message
$pids = array_keys($post_list);

$last_message = array_pop($pids);
if (empty($last_message)) {
    $last_message = reqvar("last_read_message");
}
if (empty($last_message)) {
    $last_message = 0;
}

$first_new_message = 0;

$fmanager->update_forum_read_status($fid);

//debug_message("----------------------------------------------");
//debug_message("user: " . $fmanager->get_user_name());
//debug_message($_SERVER["PHP_SELF"]);

//debug_message("starting ajax calculation of the remaining new posts for the topic: " . $tid);

$last_post_read_date = 0;

if (count($post_list) > 0) {
    //debug_message("got " . count($post_list) . " posts");
    foreach ($post_list as $pid => $pdata) {
        if (empty($first_new_message)) {
            $first_new_message = $pid;
        }
        
        $last_post_read_date = max($last_post_read_date, $pdata["creation_date_sec"]);
    }
}

$original_new_posts_count = 0;
$remaining_new_posts_count = 0;

if (!empty($_SESSION["new_messages_info_cache"]["data"]["ignored_topics"][$tid])) {
    $original_new_posts_count = $_SESSION["new_messages_info_cache"]["data"]["ignored_topics"][$tid];
} elseif (!empty($_SESSION["new_messages_info_cache"]["data"]["private_topics"][$tid])) {
    $original_new_posts_count = $_SESSION["new_messages_info_cache"]["data"]["private_topics"][$tid];
} elseif (!empty($_SESSION["new_messages_info_cache"]["data"]["topics"][$tid])) {
    $original_new_posts_count = $_SESSION["new_messages_info_cache"]["data"]["topics"][$tid];
}

if (!empty($last_post_read_date)) {
    $fmanager->update_topic_read_status($tid, $fid, $last_post_read_date);

    $fmanager->calculate_new_topic_messages($last_post_read_date, $fid, $tid, $remaining_new_posts_count, !empty($topic_data["deleted"]));
    
    $fmanager->update_topic_new_messages_cache($tid, $remaining_new_posts_count, $last_post_read_date);
}

//debug_message("actual count of new posts stored in the session: " . $original_new_posts_count);
//debug_message("remaining posts count: " . $remaining_new_posts_count);

// If there were new posts, but all loaded posts for this page are ignored (new post count was not reduced),
// we redirect to new page

$force_redirect = 0;
if ($original_new_posts_count > 0 && $remaining_new_posts_count == $original_new_posts_count) {
    $force_redirect = 1;
    //debug_message("new count remained unchanged, force redirect");
}

/*
debug_message("last_read_message: " . reqvar("last_read_message"));
debug_message("limit: " . reqvar("limit"));
debug_message("first_new_message: " . $first_new_message);
debug_message("last_message: " . $last_message);
debug_message("loaded_new_posts_count: " . count($post_list));
debug_message("original_new_posts_count: " . $original_new_posts_count);
debug_message("remaining_new_posts_count: " . $remaining_new_posts_count);
debug_message("force_redirect: " . $force_redirect);
*/

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
//-----------------------------------------------------------------------
?>

<?php foreach ($post_list as $pid => $pinfo): ?>
    
    <?php
    if (!empty($pinfo["warn_year_interval"])) {
        echo "<div class='year_period_warning'>" . escape_html(text("OverYearInerval")) . "</div>";
    }
    ?>

    <div id="post_<?php echo_html($pid); ?>">
        <?php
        require $view_path . "topic_message_tpl_inc.php";
        ?>
    </div>

<?php endforeach; ?>

<!-- This is a data transfer div container -->

<div id="ajax_data" style="display:none"
     data-last_message="<?php echo_html($last_message); ?>"
     data-first_new_message="<?php echo_html($first_new_message); ?>"
     data-force_redirect="<?php echo_html($force_redirect); ?>"
     data-remaining_new_posts_count="<?php echo_html($remaining_new_posts_count); ?>"
     data-loaded_new_posts_count="<?php echo_html(count($post_list)); ?>"
    
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
