<?php

abstract class ForumAPIManager
{
    //-----------------------------------------------------------------
    abstract function get_query_topic_list($prfx, $where, $limit, $sort);
    abstract function get_query_post_list($prfx, $where, $uid, $limit, $sort);

    //-----------------------------------------------------------------
    protected $forum_manager;
    protected $format_manager;
    protected $attachment_manager;

    //-----------------------------------------------------------------
    static $inst_object;
    
    //-----------------------------------------------------------------
    static function instance()
    {
        if (!empty(self::$inst_object)) {
            return self::$inst_object;
        }
        
        self::$inst_object = System::getClassInstance(__CLASS__);
        
        return self::$inst_object;
    } // instance

    //------------------------------------
    function __construct()
    {
        $this->forum_manager = ForumManager::instance();
        $this->format_manager = System::getClassInstance("FormatManager");
        $this->format_manager->forum_manager = $this->forum_manager;

        $this->attachment_manager = System::getClassInstance("AttachmentManager");
        $this->attachment_manager->forum_manager = $this->forum_manager;
    }   

    //-----------------------------------------------------------------
    function check_token($api_token) 
    {
        global $READ_MARKER;
        
        if (empty($api_token)) {
            throw new ForumAPIException(text("ErrAPITokenInvalid"), ForumAPIException::ERR_CODE_LOGIN_ERROR);
        }

        $dbw = System::getDBWorker();
        if (!$dbw) {
            throw new ForumAPIException(text("ErrDbInaccessible"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $prfx = $dbw->escape(System::getDBPrefix());

        $failed_login_count = 0;
        $this->forum_manager->count_failed_logins($failed_login_count);
        
        if ($failed_login_count >= 5) {
            $this->forum_manager->track_failed_login($api_token);
            throw new ForumAPIException(sprintf(text("ErrTooManyFailedLogins"), "5", "10"), ForumAPIException::ERR_CODE_LOGIN_ERROR);
        }

        $ip = System::getIPAddress();
        $ip = $dbw->escape($ip);

        $api_token_db = $dbw->escape($api_token);
        
        if (!$dbw->execute_query("select id, login, user_name, email, turnoff_events, no_private_messages, send_notifications, donot_hide_adult_pictures,
                             donot_notify_on_rates, hide_pictures, hide_user_info, hide_user_avatars, hide_ignored, skin, self_blocked,
                             custom_css, custom_smiles, skin_properties, time_zone, interface_language, read_marker, activated, approved, rating_blocked, is_admin, privileged, last_logout_date,
                             privileged_topic_moderator, global_ban_allowed, show_ip, ignore_new_guests, ignore_guests_blacklist, ignore_guests_whitelist, blocked, block_expires, block_reason, password_hash
                             from {$prfx}_user
                             where api_token = '$api_token_db' and api_active = 1")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        if (!$dbw->fetch_row()) {
            $dbw->free_result();
            
            $this->forum_manager->track_failed_login($api_token);
            $failed_login_count++;
            
            throw new ForumAPIException(text("ErrAPITokenInvalid"), ForumAPIException::ERR_CODE_LOGIN_ERROR);
        }
        
        if ($dbw->field_by_name("self_blocked") == 2) {
            $dbw->free_result();
            
            throw new ForumAPIException(text("ErrAccountBlockedBecauseDeath"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        if ($dbw->field_by_name("self_blocked") == 3) {
            $dbw->free_result();
            
            throw new ForumAPIException(text("ErrAccountBlockedBecauseLossSuspect"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $_SESSION["logged_in"] = 1;
        $_SESSION["user_id"] = $dbw->field_by_name("id");
        $_SESSION["login_date"] = time();
        $_SESSION["is_admin"] = $dbw->field_by_name("is_admin");
        $_SESSION["user_name"] = $dbw->field_by_name("user_name");
        
        $_SESSION["activated"] = $dbw->field_by_name("activated");
        $_SESSION["approved"] = $dbw->field_by_name("approved");
        $_SESSION["privileged"] = $dbw->field_by_name("privileged");
        $_SESSION["privileged_topic_moderator"] = $dbw->field_by_name("privileged_topic_moderator");
        $_SESSION["global_ban_allowed"] = $dbw->field_by_name("global_ban_allowed");
        $_SESSION["show_ip"] = $dbw->field_by_name("show_ip");
        
        $_SESSION["rating_blocked"] = $dbw->field_by_name("rating_blocked");
        
        $_SESSION["user_login"] = $dbw->field_by_name("login");
        $_SESSION["user_name"] = $dbw->field_by_name("user_name");
        $_SESSION["user_email"] = $dbw->field_by_name("email");
        
        $_SESSION["turnoff_events"] = $dbw->field_by_name("turnoff_events");
        $_SESSION["no_private_messages"] = $dbw->field_by_name("no_private_messages");
        $_SESSION["send_notifications"] = $dbw->field_by_name("send_notifications");
        $_SESSION["donot_notify_on_rates"] = $dbw->field_by_name("donot_notify_on_rates");
        
        $_SESSION["hide_pictures"] = $dbw->field_by_name("hide_pictures");
        $_SESSION["donot_hide_adult_pictures"] = $dbw->field_by_name("donot_hide_adult_pictures");
        $_SESSION["hide_user_info"] = $dbw->field_by_name("hide_user_info");
        $_SESSION["hide_user_avatars"] = $dbw->field_by_name("hide_user_avatars");
        $_SESSION["hide_ignored"] = $dbw->field_by_name("hide_ignored");
        
        $_SESSION["time_zone"] = $dbw->field_by_name("time_zone");
        if (empty($_SESSION["time_zone"]) || !in_array($_SESSION["time_zone"], $GLOBALS['time_zones'])) {
            $_SESSION["time_zone"] = TIME_ZONE;
        }
        
        $_SESSION["ignore_new_guests"] = $dbw->field_by_name("ignore_new_guests");
        $_SESSION["ignore_guests_blacklist"] = $dbw->field_by_name("ignore_guests_blacklist");
        $_SESSION["ignore_guests_whitelist"] = $dbw->field_by_name("ignore_guests_whitelist");
        
        if ($dbw->field_by_name("read_marker")) {
            $READ_MARKER = $dbw->field_by_name("read_marker");
        }
        
        $dbw->free_result();
        
        // login was ok, delete the failed tries
        
        if (!$dbw->execute_query("delete from {$prfx}_user_login_tries where ip = '$ip'")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $failed_login_count = 0;
        
        $uid = $dbw->escape($_SESSION["user_id"]);
        if (empty($uid)) {
            $uid = 0; 
        }
        
        // clear logout status
        
        if (!$dbw->execute_query("update {$prfx}_user set logout = '0' where id = $uid")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $start_time = $dbw->format_datetime(time() - 2*3600);

        $query = "select read_marker, author
                  from {$prfx}_read_marker_activity 
                  where (current_name_start > '$start_time' or current_name_hits < 500)
                  and author is not NULL
                  and not exists (select 1 from {$prfx}_user where {$prfx}_user.user_name = {$prfx}_read_marker_activity.author)";
        
        if (!$dbw->execute_query($query)) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $_SESSION["ignored_new_guests"] = array();
        while ($dbw->fetch_row()) {
            if ($dbw->field_by_name("author") == "admin") {
                continue;
            }
            
            $_SESSION["ignored_new_guests"][$dbw->field_by_name("read_marker")] = $dbw->field_by_name("author");
        }
        
        $dbw->free_result();

        $now = $dbw->format_datetime(time());

        if (!$dbw->execute_query("update {$prfx}_user set blocked = 0, block_expires = NULL, block_reason = NULL
                             where id = $uid and block_expires <= '$now'")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        if (!$dbw->execute_query("delete from {$prfx}_forum_blocked
                             where user_id = $uid and block_expires <= '$now'")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
    
        $host = $dbw->escape(get_host_address());
    
        if (!$dbw->execute_query("update {$prfx}_user set last_host = '$host', last_visit_date = '$now' where id = $uid")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }

        // check if forum member
        
        $_SESSION["forum_member"] = array();
        
        if (!$dbw->execute_query("select forum_id from {$prfx}_forum_member where user_id = $uid")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        while ($dbw->fetch_row()) {
            $_SESSION["forum_member"][$dbw->field_by_name("forum_id")] = 1;
        }
        
        $dbw->free_result();
        
        // check if forum moderator
        
        $_SESSION["forum_moderator"] = array();
        
        if (!$dbw->execute_query("select forum_id from {$prfx}_forum_moderator where user_id = $uid")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        while ($dbw->fetch_row()) {
            $_SESSION["forum_moderator"][$dbw->field_by_name("forum_id")] = $dbw->field_by_name("forum_id");
        }
        
        $dbw->free_result();
        
        // check if topic moderator
        
        $_SESSION["topic_moderator"] = array();
        
        if (!$dbw->execute_query("select topic_id from {$prfx}_topic_moderator where user_id = $uid")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        while ($dbw->fetch_row()) {
            $_SESSION["topic_moderator"][$dbw->field_by_name("topic_id")] = $dbw->field_by_name("topic_id");
        }
        
        $dbw->free_result();
        
        // ignored forums
        
        $_SESSION["ignored_forums"] = array();
        
        if (!$dbw->execute_query("select forum_id from {$prfx}_ignored_forums where user_id = $uid")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        while ($dbw->fetch_row()) {
            $fid = $dbw->field_by_name("forum_id");
            
            $_SESSION["ignored_forums"][$fid] = $fid;
        }
        
        $dbw->free_result();

        // blocked forums
        
        $_SESSION["blocked_forums"] = array();
        
        if (!$dbw->execute_query("select forum_id from {$prfx}_forum_blocked where user_id = $uid and (block_expires is NULL or block_expires > '$now')")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        while ($dbw->fetch_row()) {
            $fid = $dbw->field_by_name("forum_id");
            
            $_SESSION["blocked_forums"][$fid] = $fid;
        }
        
        $dbw->free_result();

        // ignored guests
        
        $_SESSION["ignored_guests_blacklist"] = array();
        $_SESSION["ignored_guests_whitelist"] = array();
        
        $query = "select guest_name, whitelist from {$prfx}_ignored_guests where user_id = $uid order by guest_name";
        
        if (!$dbw->execute_query($query)) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        while ($dbw->fetch_row()) {
            if ($dbw->field_by_name("whitelist")) {
                $_SESSION["ignored_guests_whitelist"][utf8_strtolower($dbw->field_by_name("guest_name"))] = $dbw->field_by_name("guest_name");
            } else {
                $_SESSION["ignored_guests_blacklist"][utf8_strtolower($dbw->field_by_name("guest_name"))] = $dbw->field_by_name("guest_name");
            }
        }
        
        $dbw->free_result();
        
        // ignored users
        
        $_SESSION["ignored_users"] = array();
        
        $query = "select ignored_user_id from {$prfx}_ignored_users where user_id = $uid";
        
        if (!$dbw->execute_query($query)) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        while ($dbw->fetch_row()) {
            $_SESSION["ignored_users"][$dbw->field_by_name("ignored_user_id")] = $dbw->field_by_name("ignored_user_id");
        }
        
        $dbw->free_result();
        
        // hidden guest profiles
        
        $_SESSION["hidden_guest_profiles"] = array();
        
        $query = "select avatar from {$prfx}_hide_guest_avatars where user_id = $uid";
        
        if (!$dbw->execute_query($query)) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        while ($dbw->fetch_row()) {
            $_SESSION["hidden_guest_profiles"][$dbw->field_by_name("avatar")] = $dbw->field_by_name("avatar");
        }
        
        $dbw->free_result();
        
        // hidden user profiles
        
        $_SESSION["hidden_profiles"] = array();
        
        $query = "select hidden_user_id from {$prfx}_hide_profile where user_id = $uid";
        
        if (!$dbw->execute_query($query)) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        while ($dbw->fetch_row()) {
            $_SESSION["hidden_profiles"][$dbw->field_by_name("hidden_user_id")] = $dbw->field_by_name("hidden_user_id");
        }
        
        $dbw->free_result();
        
        // subscribed authors
        
        $_SESSION["subscribed_authors"] = array();
        
        $query = "select subscribed_user_id, subscribed_user_name, user_name, last_visit_date, logout,
                  tm, last_view
                  from {$prfx}_user_subscription
                  left join {$prfx}_user on ({$prfx}_user_subscription.subscribed_user_id = {$prfx}_user.id)
                  where user_id = $uid
                  order by last_visit_date desc";
        
        if (!$dbw->execute_query($query)) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        while ($dbw->fetch_row()) {
            if ($dbw->field_by_name("subscribed_user_id")) {
                $_SESSION["subscribed_authors"]["u_" . $dbw->field_by_name("subscribed_user_id")] = array(
                    "uid" => $dbw->field_by_name("subscribed_user_id"),
                    "user_name" => $dbw->field_by_name("user_name"),
                    "online" => (xstrtotime($dbw->field_by_name("last_visit_date")) > (time() - KEEP_ONLINE_PERIOD) && $dbw->field_by_name("logout") == 0),
                    "tm" => smart_date2(xstrtotime($dbw->field_by_name("tm"))),
                    "last_activity" => smart_date2(xstrtotime($dbw->field_by_name("last_visit_date"))),
                    "last_view" => smart_date2(xstrtotime($dbw->field_by_name("last_view")))
                );
            } else {
                $_SESSION["subscribed_authors"]["g_" . utf8_strtolower($dbw->field_by_name("subscribed_user_name"))] = array(
                    "user_name" => $dbw->field_by_name("subscribed_user_name"),
                    "tm" => smart_date2(xstrtotime($dbw->field_by_name("tm"))),
                    "last_activity" => "",
                    "last_view" => smart_date2(xstrtotime($dbw->field_by_name("last_view")))
                );
            }
        }
        
        $dbw->free_result();
        
        // ignored topics
        
        $_SESSION["ignored_topics"] = array();
        
        $query = "select topic_id from {$prfx}_ignored_topics where user_id = $uid";
        
        if (!$dbw->execute_query($query)) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        while ($dbw->fetch_row()) {
            $_SESSION["ignored_topics"][$dbw->field_by_name("topic_id")] = $dbw->field_by_name("topic_id");
        }
        
        $dbw->free_result();
        
        // pinned topics
        
        $_SESSION["pinned_topics"] = array();
        
        $query = "select topic_id from {$prfx}_pinned_topics where user_id = $uid";
        
        if (!$dbw->execute_query($query)) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        while ($dbw->fetch_row()) {
            $_SESSION["pinned_topics"][$dbw->field_by_name("topic_id")] = $dbw->field_by_name("topic_id");
        }
        
        $dbw->free_result();
        
        // favourite topics
        
        $_SESSION["favourite_topics"] = array();
        
        $query = "select topic_id from {$prfx}_favourite_topics where user_id = $uid";
        
        if (!$dbw->execute_query($query)) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        while ($dbw->fetch_row()) {
            $_SESSION["favourite_topics"][$dbw->field_by_name("topic_id")] = $dbw->field_by_name("topic_id");
        }
        
        $dbw->free_result();
        
        // favourite posts
        
        $_SESSION["favourite_posts"] = array();
        
        $query = "select post_id from {$prfx}_favourite_posts where user_id = $uid";
        
        if (!$dbw->execute_query($query)) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        while ($dbw->fetch_row()) {
            $_SESSION["favourite_posts"][$dbw->field_by_name("post_id")] = $dbw->field_by_name("post_id");
        }
        
        $dbw->free_result();
        
        // subscribed posts
        
        $_SESSION["subscribed_posts"] = array();
        
        $query = "select post_id from {$prfx}_post_subscription where user_id = $uid";
        
        if (!$dbw->execute_query($query)) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        while ($dbw->fetch_row()) {
            $_SESSION["subscribed_posts"][$dbw->field_by_name("post_id")] = $dbw->field_by_name("post_id");
        }
        
        $dbw->free_result();
    } // check_token
    
    //-----------------------------------------------------------------
    function check_access_to_topic($dbw, $tid)
    {
        if (empty($tid)) {
            throw new ForumAPIException(sprintf(text("ErrTopicDoesNotExist"), "-"), ForumAPIException::ERR_CODE_NOT_FOUND_ERROR);
        }
        
        if (!is_numeric($tid)) {
            throw new ForumAPIException(sprintf(text("ErrTopicDoesNotExist"), $tid), ForumAPIException::ERR_CODE_NOT_FOUND_ERROR);
        }

        $prfx = $dbw->escape(System::getDBPrefix());
        
        $tid = $dbw->escape($tid);
        
        $uid = $dbw->escape($this->forum_manager->get_user_id());
        if (empty($uid)) {
            $uid = 0;
        }

        if (!$dbw->execute_query("select forum_id, {$prfx}_forum.name forum_name, {$prfx}_topic.deleted, {$prfx}_forum.deleted forum_deleted,
                             publish_delay, is_private, author, {$prfx}_topic.user_id
                             from
                             {$prfx}_topic
                             inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
                             where {$prfx}_topic.id = $tid")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $fid = 0;
        $delayed = false;
        $deleted = false;
        $forum_name = "";
        $author_id = "";
        $is_private = false;
        
        if ($dbw->fetch_row()) {
            $fid = $dbw->field_by_name("is_private") ? "private" : $dbw->field_by_name("forum_id");
            $forum_name = $dbw->field_by_name("is_private") ? text("PrivateTopics") : $dbw->field_by_name("forum_name");
            
            $deleted = $dbw->field_by_name("deleted") || $dbw->field_by_name("forum_deleted");
            $delayed = $dbw->field_by_name("publish_delay");
            $author_id = $dbw->field_by_name("user_id");
            $is_private = $dbw->field_by_name("is_private");
        } else {
            $dbw->free_result();
            throw new ForumAPIException(sprintf(text("ErrTopicDoesNotExist"), $tid), ForumAPIException::ERR_CODE_NOT_FOUND_ERROR);
        }
        
        $dbw->free_result();
        
        if ($deleted) {
            $has_access = false;
            
            if ($is_private == 2 && $this->is_topic_moderator($tid)) {
                $has_access = true;
            } elseif (empty($is_private) && ($this->is_forum_moderator($fid) || $this->is_admin())) {
                $has_access = true;
            }
            
            if (!$has_access) {
                throw new ForumAPIException(text("WarnTopicDeleted"), ForumAPIException::ERR_CODE_ACCESS_ERROR);
            }
        }
        
        if ($delayed && $author_id != $uid) {
            throw new ForumAPIException(text("ErrTopicNotPublished"), ForumAPIException::ERR_CODE_ACCESS_ERROR);
        }
        
        if (empty($is_private)) {
            $this->check_access_to_forum($dbw, $fid);

            return;
        }
        
        if (empty($uid)) {
            throw new ForumAPIException(text("ErrNoAccessToPrivateTopic"), ForumAPIException::ERR_CODE_ACCESS_ERROR);
        }
        
        if (!$dbw->execute_query("select 1 from {$prfx}_private_topics where topic_id = $tid and participant_id = $uid")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        if ($dbw->fetch_row()) {
            $dbw->free_result();
            return;
        }
        
        $dbw->free_result();
        
        throw new ForumAPIException(text("ErrNoAccessToPrivateTopic"), ForumAPIException::ERR_CODE_ACCESS_ERROR);
    } // check_access_to_topic
    
    //-----------------------------------------------------------------
    function check_access_to_forum($dbw, $fid)
    {
        $forum_name = "-";
        
        if (empty($fid)) {
            throw new ForumAPIException(sprintf(text("ErrForumDoesNotExist"), "-"), ForumAPIException::ERR_CODE_NOT_FOUND_ERROR);
        }
        
        if (!is_numeric($fid)) {
            throw new ForumAPIException(sprintf(text("ErrForumDoesNotExist"), $request_data["forum_id"]), ForumAPIException::ERR_CODE_NOT_FOUND_ERROR);
        }
        
        $prfx = $dbw->escape(System::getDBPrefix());
        
        $fid = $dbw->escape($fid);
        
        $uid = $dbw->escape($this->forum_manager->get_user_id());
        if (empty($uid)) {
            $uid = 0;
        }
            
        $forum_name = "-";
        
        if (!$dbw->execute_query("select name, deleted, protected_by_password, restricted_access from {$prfx}_forum where id = $fid")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $protected_by_password = false;
        $restricted_access = 0;
        
        if ($dbw->fetch_row()) {
            $deleted = $dbw->field_by_name("deleted");
            $protected_by_password = $dbw->field_by_name("protected_by_password");
            $restricted_access = $dbw->field_by_name("restricted_access");
            $forum_name = $dbw->field_by_name("name");
            
            if ($forum_name == "PRIVATE_MESSAGES") {
                $forum_name = text("PrivateTopics");
                $dbw->free_result();
                
                if (empty($uid)) {
                    throw new ForumAPIException(sprintf(text("ErrForumNotAccessible"), $forum_name), ForumAPIException::ERR_CODE_ACCESS_ERROR);
                }  
                
                return;
            }
        } else {
            $dbw->free_result();
            throw new ForumAPIException(sprintf(text("ErrForumDoesNotExist"), $fid), ForumAPIException::ERR_CODE_NOT_FOUND_ERROR);
        }
        
        $dbw->free_result();
        
        if ($deleted && !$this->forum_manager->is_admin() && !$this->forum_manager->is_forum_moderator($fid)) {
            throw new ForumAPIException(text("WarnForumDeleted"), ForumAPIException::ERR_CODE_ACCESS_ERROR);
        }
        
        if (!$dbw->execute_query("select block_expires
                           from {$prfx}_forum_blocked
                           where user_id = $uid and forum_id = $fid")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $blocked = false;
        $block_expires = "";
        $block_time_left = "";
        
        if ($dbw->fetch_row()) {
            $blocked = true;
            $block_expires = "";
            $block_time_left = "";
            if ($dbw->field_by_name("block_expires")) {
                if (xstrtotime($dbw->field_by_name("block_expires")) < time()) {
                    $blocked = false;
                } else {
                    $block_expires = adjust_and_format_timezone(xstrtotime($dbw->field_by_name("block_expires")), text("DateTimeFormat"));
                    $block_time_left = format_duration(xstrtotime($dbw->field_by_name("block_expires")) - time());
                }
            }
        }
        
        $dbw->free_result();

        if ($blocked && $restricted_access > 0) {
            if (!empty($block_expires)) {
                throw new ForumAPIException(sprintf(text("ErrAccountIsBlockedUntilOnForum"), $forum_name, $block_expires, $block_time_left), ForumAPIException::ERR_CODE_ACCESS_ERROR);
            } else {
                throw new ForumAPIException(sprintf(text("ErrAccountIsBlockedOnForum"), $forum_name), ForumAPIException::ERR_CODE_ACCESS_ERROR);
            }
        }
        
        $where = "where {$prfx}_forum.id = $fid and {$prfx}_forum.name <> 'PRIVATE_MESSAGES'";
        
        if (!$this->forum_manager->is_logged_in()) {
            $where .= " and restricted_access = 0";
        } elseif ($this->forum_manager->is_admin() && !$this->forum_manager->demo_mode()) {
            // no restrxtions
        } else {
            $where .= " and (((restricted_access = 0 or restricted_access = 2) and deleted <> 1) or
                       {$prfx}_forum.id in (select forum_id from {$prfx}_forum_moderator where user_id = $uid) or
                       ({$prfx}_forum.id in (select forum_id from {$prfx}_forum_member where user_id = $uid) and deleted <> 1)
                      )";
        }
        
        if (!$dbw->execute_query("select 1 from {$prfx}_forum $where")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        if ($dbw->fetch_row()) {
            $dbw->free_result();
            return;
        }
        
        $dbw->free_result();
        
        throw new ForumAPIException(sprintf(text("ErrForumNotAccessible"), $forum_name), ForumAPIException::ERR_CODE_ACCESS_ERROR);
    } // check_access_to_forum
    
    //-----------------------------------------------------------------
    function get_forum_list(&$groupped_forum_list)
    {
        global $READ_MARKER;
        
        $rodbw = System::getRODBWorker();
        if (!$rodbw) {
            throw new ForumAPIException(text("ErrDbInaccessible"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $prfx = $rodbw->escape(System::getDBPrefix());
        
        $forum_list_tmp = array();

        $where = "where {$prfx}_forum.name <> 'PRIVATE_MESSAGES'";
        
        $forum_restriction_appendix = $this->forum_manager->get_forum_restriction_appendix($rodbw, $prfx, true);
        if (!empty($forum_restriction_appendix)) {
            $where .= " and $forum_restriction_appendix";
        }
        
        if (!$rodbw->execute_query("select {$prfx}_forum.id, {$prfx}_forum.name, description, {$prfx}_forum.creation_date,
                             user_posting_as_guest,
                             topic_count, topic_count_total,
                             last_message_date, forum_group_id,
                             {$prfx}_forum.deleted, closed, disable_ignore,
                             {$prfx}_post.user_id, {$prfx}_post.author, {$prfx}_user.user_name, {$prfx}_post.read_marker,
                             last_visit_date, logout, {$prfx}_forum_group.name forum_group_name
                             from {$prfx}_forum
                             left join {$prfx}_forum_group on ({$prfx}_forum.forum_group_id = {$prfx}_forum_group.id)
                             inner join {$prfx}_forum_statistics on ({$prfx}_forum.id = {$prfx}_forum_statistics.forum_id)
                             left join {$prfx}_post on ({$prfx}_forum_statistics.last_message_id = {$prfx}_post.id)
                             left join {$prfx}_user on ({$prfx}_post.user_id = {$prfx}_user.id)
                             $where
                             order by case when {$prfx}_forum.forum_group_id is NULL then 1 else 0 end,
                             {$prfx}_forum_group.sort_order, {$prfx}_forum_group.name, {$prfx}_forum.sort_order, {$prfx}_forum.name")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $_SESSION["has_forums_with_user_guest_posting"] = false;
        $_SESSION["has_forum_groups"] = false;
        
        while ($rodbw->fetch_row()) {
            if ($rodbw->field_by_name("user_posting_as_guest")) {
                $_SESSION["has_forums_with_user_guest_posting"] = true;
            }            
            
            if ($rodbw->field_by_name("forum_group_name")) {
                $_SESSION["has_forum_groups"] = true;
            }            

            $fid = $rodbw->field_by_name("id");
            
            $topic_count = $rodbw->field_by_name("topic_count");
            $deleted = $rodbw->field_by_name("deleted");
            
            if (!empty($_SESSION["show_deleted"])) {
                if ($deleted && !$this->forum_manager->is_admin() && !$this->forum_manager->is_forum_moderator($fid)) {
                    continue;
                }
                
                if ($this->forum_manager->is_admin() || $this->forum_manager->is_forum_moderator($fid)) {
                    $topic_count = $rodbw->field_by_name("topic_count_total");
                }
            } elseif ($deleted) {
                continue;
            }
            
            $last_author_id = $rodbw->field_by_name("user_id");
            $last_author_readmarker = $rodbw->field_by_name("read_marker");
            $last_author = $rodbw->field_by_name("user_name") ? $rodbw->field_by_name("user_name") : $rodbw->field_by_name("author");
            $last_author_online = (xstrtotime($rodbw->field_by_name("last_visit_date")) > (time() - KEEP_ONLINE_PERIOD) && $rodbw->field_by_name("logout") == 0);
            
            $last_author_ignored = false;
            if (!$rodbw->field_by_name("disable_ignore")) {
                $this->forum_manager->clear_if_ignored($last_author_id, $last_author, $last_author_readmarker, $last_author_online, $last_author_ignored, $fid, "");
            }
            
            $forum_list_tmp[$fid] = array(
                "name" => $rodbw->field_by_name("name"),
                "description" => $rodbw->field_by_name("description"),
                "forum_group_id" => $rodbw->field_by_name("forum_group_id"),
                "forum_group_name" => $rodbw->field_by_name("forum_group_name"),
                "last_message_date" => smart_date2(xstrtotime($rodbw->field_by_name("last_message_date"))),
                "topic_count" => $topic_count,
                "last_author" => $last_author,
                "last_author_id" => $last_author_id,
                "last_author_online" => $last_author_online,
                "last_author_ignored" => $last_author_ignored,
                "closed" => $rodbw->field_by_name("closed"),
                "disable_ignore" => $rodbw->field_by_name("disable_ignore"),
                "deleted" => $deleted,
                "in_ignored" => !empty($_SESSION["ignored_forums"][$fid]),
                "topics_with_new_count" => 0
            );
        }
        
        $rodbw->free_result();
        
        if (!$rodbw->execute_query("select user_id, forum_id, user_name,
                             last_visit_date, logout
                             from
                             {$prfx}_forum_moderator
                             inner join {$prfx}_user on ({$prfx}_forum_moderator.user_id = {$prfx}_user.id)
                             order by user_name")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        while ($rodbw->fetch_row()) {
            if (empty($forum_list_tmp[$rodbw->field_by_name("forum_id")])) {
                continue;
            }
            
            $forum_list_tmp[$rodbw->field_by_name("forum_id")]["moderators"][$rodbw->field_by_name("user_id")] = array(
                "name" => $rodbw->field_by_name("user_name"),
                "online" => (xstrtotime($rodbw->field_by_name("last_visit_date")) > (time() - KEEP_ONLINE_PERIOD) && $rodbw->field_by_name("logout") == 0),
            );
        }
        
        $rodbw->free_result();
        
        $groupped_forum_list = $forum_list_tmp;
    } // get_forum_list
    
    //-----------------------------------------------------------------
    function get_topic_list(&$topic_list, &$request_data)
    {
        global $READ_MARKER;
        
        if (empty($request_data["forum_id"])) {
            throw new ForumAPIException(sprintf(text("ErrForumDoesNotExist"), "-"), ForumAPIException::ERR_CODE_NOT_FOUND_ERROR);
        }
        
        if ($request_data["forum_id"] == "private") {
            $request_data["forum_id"] = $this->forum_manager->get_private_forum_id();
        }
        
        if (!is_numeric($request_data["forum_id"])) {
            throw new ForumAPIException(sprintf(text("ErrForumDoesNotExist"), $request_data["forum_id"]), ForumAPIException::ERR_CODE_NOT_FOUND_ERROR);
        }
        
        if (empty($request_data["limit"]) || ! is_numeric($request_data["limit"]) || $request_data["limit"] < 1 || $request_data["limit"] > 100) {
            $limit = 100;
        } else {
            $limit = $request_data["limit"];
        }
        
        $sort = "desc";
        if (($request_data["sort"] ?? "") == "asc") {
            $sort = "asc";
        }

        $rodbw = System::getRODBWorker();
        if (!$rodbw) {
            throw new ForumAPIException(text("ErrDbInaccessible"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $this->check_access_to_forum($rodbw, $request_data["forum_id"]);
        
        $prfx = $rodbw->escape(System::getDBPrefix());

        $fid = $rodbw->escape($request_data["forum_id"]);
        
        if (!$rodbw->execute_query("select id from {$prfx}_forum where id = $fid")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        if (!$rodbw->fetch_row()) {
            $rodbw->free_result();

            throw new ForumAPIException(sprintf(text("ErrForumDoesNotExist"), $request_data["forum_id"]), ForumAPIException::ERR_CODE_NOT_FOUND_ERROR);
        }
        
        $rodbw->free_result();

        $where = "where {$prfx}_topic.forum_id = $fid";
        
        if (!empty($request_data["continue_at"])) {
            $start_timestamp = xstrtotime($request_data["continue_at"]);
            if ($start_timestamp === false) {
                throw new ForumAPIException(sprintf(text("ErrWrongDateFormat"), "2024-11-30 12:44:53"), ForumAPIException::ERR_CODE_NOT_FOUND_ERROR);
            }

            $start_timestamp = $rodbw->format_datetime($start_timestamp);
            
            if ($sort == "desc") {
                $where .= " and {$prfx}_topic_statistics.last_message_date < '$start_timestamp'";
            } else {
                $where .= " and {$prfx}_topic_statistics.last_message_date > '$start_timestamp'";
            }
        }        

        $uid = $rodbw->escape($this->forum_manager->get_user_id());
        if (empty($uid)) {
            $uid = 0;
        }
        
        $private_fid = $this->forum_manager->get_private_forum_id();

        if ($private_fid == $fid) {
            if (empty($uid)) {
                return;
            }
            
            $delete_appendix = "";
            if (!empty($_SESSION["show_deleted"])) {
                $delete_appendix = " or ({$prfx}_topic.deleted = 1 and {$prfx}_topic.id in (select {$prfx}_topic_moderator.topic_id from {$prfx}_topic_moderator where user_id = $uid))";
            }
            
            $where = "where exists (select 1 from {$prfx}_private_topics where {$prfx}_private_topics.topic_id = {$prfx}_topic.id and {$prfx}_private_topics.participant_id = $uid)
                       and ({$prfx}_topic.deleted <> 1 $delete_appendix)";
        }

        $where .= $this->forum_manager->get_ignore_topic_where_appendix($rodbw, $prfx, 0);
        
        $where .= $this->forum_manager->get_deleted_where_appendix($rodbw, $prfx, !empty($_SESSION["show_deleted"]), false);
        
        $user_delayed_topic_appendix = " and ({$prfx}_topic.publish_delay = 0";
        if (!empty($uid)) {
            $user_delayed_topic_appendix .= " or ({$prfx}_topic.publish_delay = 1 and {$prfx}_topic.user_id = $uid)";
        }
        $user_delayed_topic_appendix .= ")";
        
        $where .= $user_delayed_topic_appendix;
        
        if (!$rodbw->execute_query($this->get_query_topic_list($prfx, $where, $limit, $sort))) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        while ($rodbw->fetch_row()) {
            $tid = $rodbw->field_by_name("id");
            $forum_id = $rodbw->field_by_name("forum_id");
            
            $post_count = $rodbw->field_by_name("post_count");
            if (!empty($_SESSION["show_deleted"]) && ($this->is_admin() || $this->is_forum_moderator($forum_id) || $this->is_topic_moderator($tid))) {
                $post_count = $rodbw->field_by_name("post_count_total");
            }
            
            $forum_name = $rodbw->field_by_name("forum_name");
            if ($forum_name == "PRIVATE_MESSAGES") {
                $forum_id = "private";
                $forum_name = text("PrivateTopics");
            }
            
            $user_id = $rodbw->field_by_name("user_id");
            $author = $rodbw->field_by_name("user_name") ? $rodbw->field_by_name("user_name") : $rodbw->field_by_name("author");
            $author_readmarker = $rodbw->field_by_name("read_marker");
            $online = (xstrtotime($rodbw->field_by_name("last_visit_date")) > (time() - KEEP_ONLINE_PERIOD) && $rodbw->field_by_name("logout") == 0);
            
            $author_ignored = false;
            if (!$rodbw->field_by_name("disable_ignore")) {
                $this->forum_manager->clear_if_ignored($user_id, $author, $author_readmarker, $online, $author_ignored, $forum_id, $tid);
            }
            
            $topic_list[$tid] = array(
                "name" => $rodbw->field_by_name("name"),
                "creation_date" => smart_date2(xstrtotime($rodbw->field_by_name("creation_date"))),
                "last_message_date" => smart_date2(xstrtotime($rodbw->field_by_name("last_message_date"))),
                "post_count" => $post_count,
                "hits_count" => $rodbw->field_by_name("hits_count"),
                "bot_hits_count" => $rodbw->field_by_name("bot_hits_count"),
                "has_pinned_post" => $rodbw->field_by_name("has_pinned_post"),
                "pinned" => $rodbw->field_by_name("pinned") || !empty($_SESSION["pinned_topics"][$tid]),
                "is_poll" => $rodbw->field_by_name("is_poll"),
                "is_blocked" => $rodbw->field_by_name("no_guests") && !$this->forum_manager->is_logged_in(),
                "profiled_topic" => $rodbw->field_by_name("profiled_topic"),
                "publish_delay" => $rodbw->field_by_name("publish_delay"),
                "closed" => $rodbw->field_by_name("closed"),
                "deleted" => $rodbw->field_by_name("deleted") || $rodbw->field_by_name("forum_deleted"),
                "forum_id" => $forum_id,
                "forum_name" => $forum_name,
                "user_id" => $user_id,
                "author" => $author,
                "online" => $online,
                "author_ignored" => $author_ignored
            );
        }
        
        $rodbw->free_result();
        
        if (count($topic_list) == 0) {
            measure_action_time("get page topics");
            return true;
        }
        
        $in_list = implode(",", array_keys($topic_list));
        
        // last authors
        
        $query = "select {$prfx}_topic.id, forum_id,
            {$prfx}_post.user_id last_author_id, {$prfx}_post.author last_author, {$prfx}_post.read_marker last_author_readmarker,
            last_user.last_visit_date last_user_last_visit_date, last_user.logout last_user_logout,
            disable_ignore
            from
            {$prfx}_topic 
            inner join {$prfx}_forum on ({$prfx}_forum.id = {$prfx}_topic.forum_id)
            inner join {$prfx}_topic_statistics on ({$prfx}_topic.id = {$prfx}_topic_statistics.topic_id)
            left join {$prfx}_post on ({$prfx}_topic_statistics.last_message_id = {$prfx}_post.id)
            left join {$prfx}_user last_user on ({$prfx}_post.user_id = last_user.id)
            where {$prfx}_topic.id in ($in_list)
            ";
        
        if (!$rodbw->execute_query($query)) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        while ($rodbw->fetch_row()) {
            $fid = $rodbw->field_by_name("forum_id");
            $tid = $rodbw->field_by_name("id");
            
            $topic_list[$tid]["last_author_id"] = $rodbw->field_by_name("last_author_id");
            $topic_list[$tid]["last_author"] = $rodbw->field_by_name("last_author");
            $topic_list[$tid]["last_author_online"] = (xstrtotime($rodbw->field_by_name("last_user_last_visit_date")) > (time() - KEEP_ONLINE_PERIOD) && $rodbw->field_by_name("last_user_logout") == 0);
            
            $last_author_ignored = false;
            if (!$rodbw->field_by_name("disable_ignore")) {
                $this->forum_manager->clear_if_ignored($topic_list[$tid]["last_author_id"], $topic_list[$tid]["last_author"], $topic_list[$tid]["last_author_readmarker"], $topic_list[$tid]["last_author_online"], $last_author_ignored, $fid, $tid);
            }
            
            $topic_list[$tid]["last_author_ignored"] = $last_author_ignored;
        }
        
        $rodbw->free_result();
        
        // participants
        
        if (!$rodbw->execute_query("select topic_id, participant_id, user_name,
                             {$prfx}_user.last_visit_date, {$prfx}_user.logout
                             from {$prfx}_private_topics
                             inner join {$prfx}_user on ({$prfx}_private_topics.participant_id = {$prfx}_user.id)
                             where topic_id in ($in_list)
                             order by {$prfx}_private_topics.last_visit_date desc")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        while ($rodbw->fetch_row()) {
            if ($this->forum_manager->is_user_ignored($rodbw->field_by_name("participant_id"))) {
                continue;
            }
            
            $tid = $rodbw->field_by_name("topic_id");
            
            $topic_list[$tid]["participants"][$rodbw->field_by_name("participant_id")] = array(
                "name" => $rodbw->field_by_name("user_name"),
                "online" => (xstrtotime($rodbw->field_by_name("last_visit_date")) > (time() - KEEP_ONLINE_PERIOD) && $rodbw->field_by_name("logout") == 0)
            );
        }
        
        $rodbw->free_result();
        
        // topic moderators
        
        if (!$rodbw->execute_query("select topic_id, user_id, user_name,
                             last_visit_date, logout
                             from {$prfx}_topic_moderator
                             inner join {$prfx}_user on ({$prfx}_topic_moderator.user_id = {$prfx}_user.id)
                             where topic_id in ($in_list)
                             order by user_name")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        while ($rodbw->fetch_row()) {
            $topic_list[$rodbw->field_by_name("topic_id")]["moderators"][$rodbw->field_by_name("user_id")] = array(
                "name" => $rodbw->field_by_name("user_name"),
                "online" => (xstrtotime($rodbw->field_by_name("last_visit_date")) > (time() - KEEP_ONLINE_PERIOD) && $rodbw->field_by_name("logout") == 0)
            );
        }
        
        $rodbw->free_result();
        
        // hots
        
        $now = $rodbw->format_datetime(time() - 60 * 60); // 60 min
        
        $query = "select topic_id
              from
              {$prfx}_post
              inner join {$prfx}_topic on ({$prfx}_post.topic_id = {$prfx}_topic.id)
              where {$prfx}_post.creation_date >= '$now'
              group by topic_id
              having count(*) >= 15 and count(distinct {$prfx}_post.author) > 2";
              
        if (!$rodbw->execute_query($query)) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        while ($rodbw->fetch_row()) {
            if (empty($topic_list[$rodbw->field_by_name("topic_id")])) {
                continue;
            }
            
            $topic_list[$rodbw->field_by_name("topic_id")]["hot"] = true;
        }
        
        $rodbw->free_result();
        
        $now = $rodbw->format_datetime(time() - 24 * 60 * 60); // day
        
        $query = "select topic_id
              from
              {$prfx}_post
              inner join {$prfx}_topic on ({$prfx}_post.topic_id = {$prfx}_topic.id)
              where {$prfx}_post.creation_date >= '$now'
              group by topic_id
              having count(*) >= 100 and count(distinct {$prfx}_post.author) > 2";
        if (!$rodbw->execute_query($query)) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        while ($rodbw->fetch_row()) {
            if (empty($topic_list[$rodbw->field_by_name("topic_id")])) {
                continue;
            }

            $topic_list[$rodbw->field_by_name("topic_id")]["hot"] = true;
        }
        
        $rodbw->free_result();

        // blocking in topic

        if (!empty($uid)) {
            $query = "select topic_id from {$prfx}_topic_blocked where user_id = $uid and topic_id in ($in_list)";
        
            if (!$rodbw->execute_query($query)) {
                throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
            }
            
            while ($rodbw->fetch_row()) {
                $topic_list[$rodbw->field_by_name("topic_id")]["is_blocked"] = true;
            }
            
            $rodbw->free_result();
        }
        
        // favourites
        
        if (empty($uid)) {
            if (empty($_SESSION["favourite_topics"])) {
                $favourite_in_list = "-1";
            } else {
                $favourite_in_list = $rodbw->escape(implode(",", $_SESSION["favourite_topics"]));
            }
            
            $query = "select id topic_id from {$prfx}_topic where id in ($favourite_in_list) and id in ($in_list)";
        } else {
            $query = "select topic_id from {$prfx}_favourite_topics where user_id = $uid and topic_id in ($in_list)";
        }
        
        if (!$rodbw->execute_query($query)) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        while ($rodbw->fetch_row()) {
            $topic_list[$rodbw->field_by_name("topic_id")]["topic_in_favourites"] = true;
        }
        
        $rodbw->free_result();
        
        // ignores
        
        if (empty($uid)) {
            if (empty($_SESSION["ignored_topics"])) {
                $ignored_in_list = "-1";
            } else {
                $ignored_in_list = $rodbw->escape(implode(",", $_SESSION["ignored_topics"]));
            }
            
            $query = "select id topic_id from {$prfx}_topic where id in ($ignored_in_list) and id in ($in_list)";
        } else {
            $query = "select topic_id from {$prfx}_ignored_topics where user_id = $uid and topic_id in ($in_list)";
        }
        
        if (!$rodbw->execute_query($query)) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        while ($rodbw->fetch_row()) {
            $topic_list[$rodbw->field_by_name("topic_id")]["topic_ignored"] = true;
        }
        
        $rodbw->free_result();
    } // get_topic_list

    //-----------------------------------------------------------------
    function get_post_list(&$post_list, &$request_data)
    {
        global $READ_MARKER;

        if (empty($request_data["topic_id"])) {
            throw new ForumAPIException(sprintf(text("ErrTopicDoesNotExist"), "-"), ForumAPIException::ERR_CODE_NOT_FOUND_ERROR);
        }
        
        if (!is_numeric($request_data["topic_id"])) {
            throw new ForumAPIException(sprintf(text("ErrTopicDoesNotExist"), $request_data["topic_id"]), ForumAPIException::ERR_CODE_NOT_FOUND_ERROR);
        }
        
        if (empty($request_data["limit"]) || ! is_numeric($request_data["limit"]) || $request_data["limit"] < 1 || $request_data["limit"] > 100) {
            $limit = 100;
        } else {
            $limit = $request_data["limit"];
        }

        $sort = "desc";
        if (($request_data["sort"] ?? "") == "asc") {
            $sort = "asc";
        }

        $rodbw = System::getRODBWorker();
        if (!$rodbw) {
            throw new ForumAPIException(text("ErrDbInaccessible"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $this->check_access_to_topic($rodbw, $request_data["topic_id"]);

        $prfx = $rodbw->escape(System::getDBPrefix());

        $uid = $rodbw->escape($this->forum_manager->get_user_id());
        if (empty($uid)) {
            $uid = 0;
        }

        $tid = $rodbw->escape($request_data["topic_id"]);
        
        if (!$rodbw->execute_query("select id from {$prfx}_topic where id = $tid")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        if (!$rodbw->fetch_row()) {
            $rodbw->free_result();

            throw new ForumAPIException(sprintf(text("ErrTopicDoesNotExist"), $request_data["topic_id"]), ForumAPIException::ERR_CODE_NOT_FOUND_ERROR);
        }
        
        $rodbw->free_result();
        
        $show_deleted = !empty($_SESSION["show_deleted"]);

        $deleted_where_appendix = $this->forum_manager->get_deleted_where_appendix($rodbw, $prfx, $show_deleted, true, "");
        $ignore_post_where_appendix = $this->forum_manager->get_ignore_post_where_appendix($rodbw, $prfx);
        $ignore_comment_where_appendix = $this->forum_manager->get_ignore_comment_where_appendix($rodbw, $prfx);

        $where = "where {$prfx}_post.topic_id = $tid 
                  $deleted_where_appendix
                  $ignore_post_where_appendix
                  $ignore_comment_where_appendix";

        if (!empty($request_data["continue_at"])) {
            $start_timestamp = xstrtotime($request_data["continue_at"]);
            if ($start_timestamp === false) {
                throw new ForumAPIException(sprintf(text("ErrWrongDateFormat"), "2024-11-30 12:44:53"), ForumAPIException::ERR_CODE_NOT_FOUND_ERROR);
            }

            $start_timestamp = $rodbw->format_datetime($start_timestamp);
            
            if ($sort == "desc") {
                $where .= " and {$prfx}_post.creation_date < '$start_timestamp'";
            } else {
                $where .= " and {$prfx}_post.creation_date > '$start_timestamp'";
            }
        }        

        if (!$rodbw->execute_query($this->get_query_post_list($prfx, $where, $uid, $limit, $sort))) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }

        $user_ids = [];
        
        $this->forum_manager->collect_posts($rodbw, $uid, $post_list, $user_ids);
        
        $rodbw->free_result();
        
        foreach ($post_list as &$post_data) {
            unset($post_data["creation_date_sec"]);
            unset($post_data["read_marker"]);
            unset($post_data["user_marker"]);
            unset($post_data["user_agent"]);
            unset($post_data["aname"]);
            unset($post_data["ip"]);
            unset($post_data["topic_creation_date_sec"]);
            unset($post_data["topic_author_read_marker"]);
            unset($post_data["self_edited"]);
            unset($post_data["editable"]);
            unset($post_data["moderatable"]);
        }
        
        $post_in_list = implode(",", array_keys($post_list));
        if (empty($post_list)) {
            $post_in_list = "-1";
        }

        $query = "select nr, origin_name, type, deleted, post_id
              from
              {$prfx}_attachment
              where post_id in ($post_in_list)";
        if (!$rodbw->execute_query($query)) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        while ($rodbw->fetch_row()) {
            $post_list[$rodbw->field_by_name("post_id")]["attachments"][] = [
              "attachment_id" => $rodbw->field_by_name("post_id"),
              "nr" => $rodbw->field_by_name("nr"),
              "file_name" => $rodbw->field_by_name("origin_name"),
              "mime_type" => $rodbw->field_by_name("type"),
              "deleted" => $rodbw->field_by_name("deleted")
            ];
        }
        
        $rodbw->free_result();
        
    } // get_post_list
    
    //-----------------------------------------------------------------
    function get_attachment(&$request_data)
    {
        global $READ_MARKER;

        if (empty($request_data["attachment_id"])) {
            throw new ForumAPIException(sprintf(text("ErrAttachmentDoesNotExist"), "-"), ForumAPIException::ERR_CODE_NOT_FOUND_ERROR);
        }
        
        if (!is_numeric($request_data["attachment_id"])) {
            throw new ForumAPIException(sprintf(text("ErrAttachmentDoesNotExist"), $request_data["attachment_id"]), ForumAPIException::ERR_CODE_NOT_FOUND_ERROR);
        }

        if (empty($request_data["nr"]) || !is_numeric($request_data["nr"])) {
            $request_data["nr"] = 1;
        }

        $rodbw = System::getRODBWorker();
        if (!$rodbw) {
            throw new ForumAPIException(text("ErrDbInaccessible"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $prfx = $rodbw->escape(System::getDBPrefix());

        $attachment_id = $rodbw->escape($request_data["attachment_id"]);
        $nr = $rodbw->escape($request_data["nr"]);
        
        if (!$rodbw->execute_query("select {$prfx}_attachment.name, {$prfx}_attachment.origin_name, {$prfx}_attachment.type,
                           forum_id, topic_id, {$prfx}_attachment.deleted,
                           {$prfx}_post.deleted post_deleted,
                           {$prfx}_topic.deleted topic_deleted,
                           {$prfx}_forum.deleted forum_deleted,
                           is_private,
                           {$prfx}_post.user_id, {$prfx}_post.read_marker
                           from {$prfx}_attachment
                           inner join {$prfx}_post on ({$prfx}_attachment.post_id = {$prfx}_post.id)
                           inner join {$prfx}_topic on ({$prfx}_post.topic_id = {$prfx}_topic.id)
                           inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
                           where {$prfx}_attachment.post_id = $attachment_id and nr = $nr")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $attachment_data = [];
        
        if ($rodbw->fetch_row()) {
            $attachment_data["path"] = APPLICATION_ROOT . "user_data/attachments/" . $rodbw->field_by_name("name");
            $attachment_data["origin_path"] = APPLICATION_ROOT . "user_data/attachments/" . $rodbw->field_by_name("name");
            $attachment_data["name"] = $rodbw->field_by_name("origin_name");
            $attachment_data["type"] = $rodbw->field_by_name("type");
            $attachment_data["deleted"] = $rodbw->field_by_name("deleted") || $rodbw->field_by_name("topic_deleted") || $rodbw->field_by_name("forum_deleted");
            
            $attachment_data["indirect_deleted"] = $rodbw->field_by_name("post_deleted") || $rodbw->field_by_name("topic_deleted") || $rodbw->field_by_name("forum_deleted");
            
            $attachment_data["topic_id"] = $rodbw->field_by_name("topic_id");
            $attachment_data["forum_id"] = $rodbw->field_by_name("forum_id");
            $attachment_data["topic_private"] = $rodbw->field_by_name("is_private");
            
            $attachment_data["post_read_marker"] = $rodbw->field_by_name("read_marker");
            $attachment_data["post_user_id"] = $rodbw->field_by_name("user_id");
        } else {
            $rodbw->free_result();
            throw new ForumAPIException(sprintf(text("ErrAttachmentDoesNotExist"), $request_data["attachment_id"] . "-" . $request_data["nr"]), ForumAPIException::ERR_CODE_NOT_FOUND_ERROR);
        }
        
        $rodbw->free_result();
        
        $this->check_access_to_topic($rodbw, $attachment_data["topic_id"]);
        
        if ($this->forum_manager->is_admin() ||
            $this->forum_manager->is_forum_moderator($attachment_data["forum_id"]) ||
            $this->forum_manager->is_topic_moderator($attachment_data["topic_id"]) ||
            (($this->forum_manager->get_user_id() != "" && $attachment_data["post_user_id"] == $this->forum_manager->get_user_id()) || $attachment_data["post_read_marker"] == $READ_MARKER)
        ) {
            $attachment_data["deleted"] = false;
        }
        
        if ($attachment_data["deleted"]) {
            throw new ForumAPIException(text("ErrAttachmentIsDeleted"), ForumAPIException::ERR_CODE_ACCESS_ERROR);
        }
        
        if (!file_exists($attachment_data["path"])) {
            throw new ForumAPIException(sprintf(text("ErrAttachmentDoesNotExist"), $request_data["attachment_id"] . "-" . $request_data["nr"]), ForumAPIException::ERR_CODE_NOT_FOUND_ERROR);
        }
        
        header("Content-type: " . $attachment_data["type"]);
        header("Content-Disposition: attachment; filename=\"$attachment_data[name]\"");

        $filesize = sprintf("%u", filesize($attachment_data["path"]));
        header("Content-Length: $filesize");
        readfile($attachment_data["path"]);
        exit();
    }
    
    //-----------------------------------------------------------------
    function check_post_ip($dbw, $ip)
    {
        global $settings;

        if (empty($ip) || !defined("MAX_POSTS_PER_MINUTE") || MAX_POSTS_PER_MINUTE < 1) {
            return true;
        }
        
        if ($this->forum_manager->is_admin() || $this->forum_manager->is_privileged()) {
            return true;
        }
        
        $prfx = $dbw->escape(System::getDBPrefix());
        
        $ip = $dbw->escape($ip);
        
        $now = $dbw->format_datetime(time());
        
        if (!$dbw->execute_query("select 1
                             from {$prfx}_banned_ips
                             where banned_until > '$now' and ip = '$ip'")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        if ($dbw->fetch_row()) {
            $dbw->free_result();
            
            return false;
        }
        
        $dbw->free_result();
        
        // check the count of the posts in a minute
        
        $check_period = 60; // 1 minute
        
        $now = $dbw->format_datetime(time() - $check_period);
        
        if (!$dbw->execute_query("select count(*) cnt
                             from {$prfx}_post
                             where creation_date >= '$now' and ip = '$ip'")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $atype = "POST";
        $limit = MAX_POSTS_PER_MINUTE;
        $hits = 0;
        
        if ($dbw->fetch_row()) {
            $hits = $dbw->field_by_name("cnt");
        }
        
        $dbw->free_result();
        
        $wait_time_after_attack = 30;
        if (defined("WAIT_TIME_AFTER_ATTACK") && WAIT_TIME_AFTER_ATTACK > 0) {
            $wait_time_after_attack = WAIT_TIME_AFTER_ATTACK;
        }
        
        if ($hits <= $limit) {
            return true;
        }
        
        $banned_until = $dbw->format_datetime(time() + $wait_time_after_attack * 60);
        
        if (!$dbw->execute_query("insert into {$prfx}_banned_ips (banned_until, ip, hits, check_period, hit_limit, atype, statistics_request) values ('$banned_until', '$ip', $hits, $check_period, $limit, '$atype', 0)")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        // get the list of the administrators
        
        $administrators = array();
        
        if (!$dbw->execute_query("select id, email, user_name, interface_language, last_host, time_zone
                             from {$prfx}_user
                             where is_admin = 1 and login <> 'demoadmin'")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        while ($dbw->fetch_row()) {
            $administrators[$dbw->field_by_name("id")] = array(
                "name" => $dbw->field_by_name("user_name"),
                "email" => $dbw->field_by_name("email"),
                "last_host" => $dbw->field_by_name("last_host"),
                "author_time_zone" => $dbw->field_by_name("time_zone") ? $dbw->field_by_name("time_zone") : TIME_ZONE,
                "interface_language" => $dbw->field_by_name("interface_language")
            );
        }
        
        $dbw->free_result();
        
        // there is no administrators, send to the master admin
        
        if (empty($administrators)) {
            $administrators[0] = array(
                "name" => text("Administrator", defined('DEFAULT_LANGUAGE') ? DEFAULT_LANGUAGE : "en"),
                "email" => $settings["receiver"],
                "last_host" => "",
                "author_time_zone" => TIME_ZONE,
                "interface_language" => defined('DEFAULT_LANGUAGE') ? DEFAULT_LANGUAGE : "en"
            );
        }
        
        $params = array();
        $params["{ip}"] = $ip;
        $params["{hits}"] = $hits;
        $params["{atype}"] = $atype;
        $params["{check_period}"] = $check_period;
        
        $params["{total_attacks}"] = '-';
        $attack_data["{first_attack}"] = '-';
        $attack_data["{last_attack}"] = '-';
        
        if (!$dbw->execute_query("select
                                  min(banned_until) first_attack,
                                  max(banned_until) last_attack,
                                  count(*) attacks_count
                                  from {$prfx}_banned_ips
                                  where ip = '$ip'")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        if ($dbw->fetch_row()) {
            $params["{total_attacks}"] = $dbw->field_by_name("attacks_count");
            $attack_data["{first_attack}"] = xstrtotime($dbw->field_by_name("first_attack"));
            $attack_data["{last_attack}"] = xstrtotime($dbw->field_by_name("last_attack"));
        }
        
        $dbw->free_result();
        
        foreach ($administrators as $id => $uinfo) {
            $params["{administrator_name}"] = $uinfo["name"];
            
            $params["{site_url}"] = get_host_address($uinfo["last_host"]) . get_url_path();
            $params["{statistics_url}"] = get_host_address($uinfo["last_host"]) . get_url_path() . "load_statistics.php#banned_ips";
            
            $params["{first_attack}"] = date(text("DateTimeFormat", $uinfo["interface_language"]), convert_timezone($attack_data["{first_attack}"], TIME_ZONE, $uinfo["author_time_zone"]));
            $params["{last_attack}"] = date(text("DateTimeFormat", $uinfo["interface_language"]), convert_timezone($attack_data["{last_attack}"], TIME_ZONE, $uinfo["author_time_zone"]));
            $this->forum_manager->email_manager->send_email($settings["default_sender"], $uinfo["email"], "email_attack_detected.txt", $params, $uinfo["interface_language"]);
        }
        
        return true;
    } // check_post_ip

    //-----------------------------------------------------------------
    function check_name_usage($dbw, $user_name, $uid, $registration)
    {
        if (empty($user_name)) {
            return 0;
        }
        
        $prfx = $dbw->escape(System::getDBPrefix());
        
        $uid = $dbw->escape($uid);
        
        $appendix = "";
        if (!empty($uid)) {
            $appendix = " and id < $uid";
        }
        
        shrink_spaces($user_name);
        
        $user_name_hash = $dbw->escape($this->hash_user_name($user_name));
        
        $query = "select 1 from {$prfx}_user where user_name_hash = '$user_name_hash' $appendix";
        
        if (!$dbw->execute_query($query)) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        if ($dbw->fetch_row()) {
            $dbw->free_result();
            return 1;
        }
        
        $dbw->free_result();
        
        if (stripos($user_name, "#user_name#") !== false) {
            return 2;
        }
        
        if (!$this->is_master_admin()) {
            $query = "select 1 from {$prfx}_reserved_names where user_name_hash = '$user_name_hash'";
            if (!$dbw->execute_query($query)) {
                throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
            }
            
            if ($dbw->fetch_row()) {
                $dbw->free_result();
                return 2;
            }
            
            $dbw->free_result();
        }
        
        if (!$registration) {
            return 0;
        }
        
        $query = "select 1 from {$prfx}_protected_guests where guest_name_hash = '$user_name_hash'";
        if (!$dbw->execute_query($query)) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        if ($dbw->fetch_row()) {
            $dbw->free_result();
            return 3;
        }
        
        $dbw->free_result();
        
        return 0;
    } // check_name_usage

    //---------------------------------------------------------------
    function get_white_list_ips($dbw, &$ips, &$ip_array)
    {
        $prfx = $dbw->escape(System::getDBPrefix());
        
        $query = "select ip from {$prfx}_ip_white_list order by ip";
        
        if (!$dbw->execute_query($query)) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $ips = "";
        
        while ($dbw->fetch_row()) {
            $ip = $dbw->field_by_name("ip");
            
            $ip_array[$ip] = $ip;
            $ips .= $ip . "\n";
        }
        
        $dbw->free_result();
        
        return true;
    } // get_white_list_ips

    //------------------------------------------------------
    function check_message_limit($dbw, $fid, $tid, $new_topic)
    {
        global $settings;
        global $READ_MARKER;
        
        if ($this->forum_manager->is_admin() || $this->forum_manager->is_privileged()) {
            return true;
        }
        
        if ($this->forum_manager->is_forum_moderator($fid) || (!empty($tid) && $this->forum_manager->is_topic_moderator($tid) && $this->forum_manager->is_privileged_topic_moderator())) {
            return true;
        }
        
        $prfx = $dbw->escape(System::getDBPrefix());
        
        $uid = $this->forum_manager->get_user_id();
        
        $message_restrictions = array();

        if (!empty($settings["max_messages_minute"])) {
            $message_restrictions[] = array(
                "limit" => $settings["max_messages_minute"],
                "start" => $dbw->format_datetime(time() - 60),
                "message" => text("MessageLimitMinuteText")
            );
        }
        
        if (!empty($settings["max_messages_hour"])) {
            $message_restrictions[] = array(
                "limit" => $settings["max_messages_hour"],
                "start" => $dbw->format_datetime(time() - 60*60),
                "message" => text("MessageLimitHourText")
            );
        }
        
        if (!empty($settings["max_messages_day"])) {
            $message_restrictions[] = array(
                "limit" => $settings["max_messages_day"],
                "start" => $dbw->format_datetime(time() - 24*60*60),
                "message" => text("MessageLimitDayText")
            );
        }
        
        foreach($message_restrictions as $restrcition)
        {
            if (empty($uid)) {
                // we use readmarker because it is a cookie and remains longer
                $rm = $dbw->escape($READ_MARKER);
                $query = "select count(*) cnt from {$prfx}_post where read_marker = '$rm' and creation_date >= '$restrcition[start]'";
            } else {
                $uid = $dbw->escape($uid);
                $query = "select count(*) cnt from {$prfx}_post where user_id = $uid and creation_date >= '$restrcition[start]'";
            }
            
            if (!$dbw->execute_query($query)) {
                throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
            }
            
            $cnt = 0;
            if ($dbw->fetch_row()) {
                $cnt = $dbw->field_by_name("cnt");
            }
            
            $dbw->free_result();
            
            if ($cnt >= $restrcition["limit"]) {
                throw new ForumAPIException(sprintf(text("ErrMessageLimitExceeded"), sprintf($restrcition["message"], $restrcition["limit"])), ForumAPIException::ERR_CODE_ACCESS_ERROR);
            }
        }
        
        if (empty($settings["max_topics_day"]) || empty($new_topic)) {
            return true;
        }
        
        $this->check_topic_limit($dbw, $fid);
        
        return true;
    } // check_message_limit

    //------------------------------------------------------
    function check_topic_limit($dbw, $fid)
    {
        global $settings;
        global $READ_MARKER;
        
        if (empty($settings["max_topics_day"])) {
            return true;
        }
        
        $limit = $settings["max_topics_day"];
        $time_to_next = 0;
        
        if ($this->forum_manager->is_admin() || $this->forum_manager->is_forum_moderator($fid) || $this->forum_manager->is_privileged()) {
            return true;
        }
        
        $prfx = $dbw->escape(System::getDBPrefix());
        
        $fid = $dbw->escape($fid);
        $uid = $this->forum_manager->get_user_id();
        
        $first_topic_date = time() - 24 * 3600;
        $now = $dbw->format_datetime($first_topic_date);
        
        if (empty($uid)) {
            // we use readmarker because it is a cookie and remains longer
            $rm = $dbw->escape($READ_MARKER);
            $query = "select count(*) cnt, min(creation_date) first_topic_date
                from {$prfx}_topic
                where read_marker = '$rm' and creation_date >= '$now' and publish_delay <> 1 and is_private < 1";
        } elseif ($this->forum_manager->get_private_forum_id() == $fid) {
            // we count topics for the private messages separately
            $uid = $dbw->escape($uid);
            $receiver_id = $dbw->escape(reqvar("receiver"));
            if (empty($receiver_id) || !is_numeric($receiver_id)) {
                $receiver_id = 0;
            }
            $query = "select count(*) cnt, min(creation_date) first_topic_date
                from {$prfx}_topic
                inner join {$prfx}_private_topics on ({$prfx}_topic.id = {$prfx}_private_topics.topic_id and participant_id = $receiver_id)
                where
                user_id = $uid and
                creation_date >= '$now' and
                publish_delay <> 1 and
                is_private > 0";
        } else {
            // we do not count topics for the private messages and moderated forums
            $uid = $dbw->escape($uid);
            $query = "select count(*) cnt, min(creation_date) first_topic_date
                from {$prfx}_topic where
                user_id = $uid and
                creation_date >= '$now' and
                publish_delay <> 1 and
                is_private < 1 and
                not exists (select 1 from {$prfx}_forum_moderator where user_id = $uid and {$prfx}_forum_moderator.forum_id = {$prfx}_topic.forum_id)";
        }
        
        if (!$dbw->execute_query($query)) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $cnt = 0;
        if ($dbw->fetch_row()) {
            $cnt = $dbw->field_by_name("cnt");
            $first_topic_date = xstrtotime($dbw->field_by_name("first_topic_date"));
        }
        
        $dbw->free_result();
        
        if ($cnt >= $settings["max_topics_day"]) {
            $time_to_next = $first_topic_date - (time() - 24 * 3600);

            throw new ForumAPIException(sprintf(text("ErrTopicLimitExceeded"), $limit, format_duration($time_to_next)), ForumAPIException::ERR_CODE_ACCESS_ERROR);
        }
        
        return true;
    } // check_topic_limit

    //-----------------------------------------------------------------
    function check_blocked($dbw, $fid, $forced_guest_posting, $user_marker)
    {
        if ($this->forum_manager->is_admin()) {
            return;
        }
        
        $prfx = $dbw->escape(System::getDBPrefix());
        
        $uid = $dbw->escape($this->forum_manager->get_user_id());
        
        $forum_name = "-";
        
        if (!empty($fid) && !empty($uid)) {
            if (!is_numeric($fid)) {
                return;
            }
            
            $fid = $dbw->escape($fid);
            
            if (!$dbw->execute_query("select name from {$prfx}_forum where id = $fid")) {
                throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
            }
            
            if ($dbw->fetch_row()) {
                $forum_name = $dbw->field_by_name("name");
                
                if ($forum_name == "PRIVATE_MESSAGES") {
                    $forum_name = text("PrivateTopics");
                }
            }
            
            $dbw->free_result();
            
            if (!$dbw->execute_query("select block_expires
                               from {$prfx}_forum_blocked
                               where user_id = $uid and forum_id = $fid")) {
                throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
            }
            
            $blocked = false;
            $block_expires = "";
            $block_time_left = "";
            
            if ($dbw->fetch_row()) {
                $blocked = true;
                $block_expires = "";
                $block_time_left = "";
                if ($dbw->field_by_name("block_expires")) {
                    if (xstrtotime($dbw->field_by_name("block_expires")) < time()) {
                        $blocked = false;
                    } else {
                        $block_expires = adjust_and_format_timezone(xstrtotime($dbw->field_by_name("block_expires")), text("DateTimeFormat"));
                        $block_time_left = format_duration(xstrtotime($dbw->field_by_name("block_expires")) - time());
                    }
                }
            }
            
            $dbw->free_result();
            
            if ($blocked) {
                if (!empty($block_expires)) {
                    throw new ForumAPIException(sprintf(text("ErrAccountIsBlockedUntilOnForum"), $forum_name, $block_expires, $block_time_left), ForumAPIException::ERR_CODE_ACCESS_ERROR);
                } else {
                    throw new ForumAPIException(sprintf(text("ErrAccountIsBlockedOnForum"), $forum_name), ForumAPIException::ERR_CODE_ACCESS_ERROR);
                }
                
                return;
            }
        }
        
        if ($this->forum_manager->is_moderator()) {
            return;
        }
        
        if (!empty($uid) && empty($forced_guest_posting)) {
            if (!$dbw->execute_query("select approved, activated, blocked, block_expires, block_reason, privileged,
                               registration_date
                               from {$prfx}_user
                               where id = $uid")) {
                throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
            }
            
            $approved = false;
            $activated = false;
            $privileged = false;
            
            $time_since_registration = 0;
            
            $blocked = false;
            $block_reason = "";
            $block_expires = "";
            $block_time_left = "";
            
            if ($dbw->fetch_row()) {
                $approved = $dbw->field_by_name("approved");
                $activated = $dbw->field_by_name("activated");
                $privileged = $dbw->field_by_name("privileged");
                
                $time_since_registration = time() - xstrtotime($dbw->field_by_name("registration_date"));
                
                $blocked = $dbw->field_by_name("blocked");
                $block_reason = Emoji::Decode($dbw->field_by_name("block_reason"));
                $block_expires = "";
                $block_time_left = "";
                if ($dbw->field_by_name("block_expires")) {
                    if (xstrtotime($dbw->field_by_name("block_expires")) < time()) {
                        $blocked = false;
                    } else {
                        $block_expires = adjust_and_format_timezone(xstrtotime($dbw->field_by_name("block_expires")), text("DateTimeFormat"));
                        $block_time_left = format_duration(xstrtotime($dbw->field_by_name("block_expires")) - time());
                    }
                }
            }
            
            $dbw->free_result();
            
            if ($blocked && !empty($block_reason)) {
                $this->format_manager->format_message_simple($dbw, $prfx, $block_reason, "warning");
                postprocess_message($block_reason);
                
                $block_reason = text("Reason") . ":\n\n[html]" . $block_reason . "[/html]";
            }
            
            if (!$approved) {
                throw new ForumAPIException(text("ErrAccountNotApproved"), ForumAPIException::ERR_CODE_ACCESS_ERROR);
            }
            
            if (!$activated) {
                throw new ForumAPIException(text("ErrAccountNotActivated"), ForumAPIException::ERR_CODE_ACCESS_ERROR);
            }
            
            if ($blocked) {
                if (!empty($block_expires)) {
                    throw new ForumAPIException(sprintf(text("ErrAccountIsBlockedUntil"), $block_expires, $block_time_left), ForumAPIException::ERR_CODE_ACCESS_ERROR);
                } else {
                    throw new ForumAPIException(text("ErrAccountIsBlocked"), ForumAPIException::ERR_CODE_ACCESS_ERROR);
                }
            }
            
            // restrictions
            
            if (!empty($privileged)) {
                return;
            }
            
            $err = "";
            
            if (empty($fid)) {
                return;
            }
            
            if (!$dbw->execute_query("select no_guests, restricted_access, access_duration, access_message_count,
                               protected_by_password, password
                               from {$prfx}_forum
                               where id = $fid")) {
                throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
            }
            
            $access_duration = 0;
            $access_message_count = 0;
            $protected_by_password = false;
            
            if ($dbw->fetch_row()) {
                $protected_by_password = $dbw->field_by_name("protected_by_password");
                
                if (($dbw->field_by_name("no_guests") || $dbw->field_by_name("restricted_access") == 2) && $dbw->field_by_name("protected_by_password") == 0) {
                    $access_duration = $dbw->field_by_name("access_duration");
                    $access_message_count = $dbw->field_by_name("access_message_count");
                }
            }
            
            $dbw->free_result();
            
            if (!empty($access_duration) && $time_since_registration < $access_duration * 24 * 3600) {
                $err .= text("MinDurationComment") . ": " . $access_duration . "\n";
            }
            
            if (!empty($access_message_count)) {
                $query = "select count(*) cnt
                  from {$prfx}_post
                  where
                  user_id = $uid and
                  topic_id not in (select id from {$prfx}_topic where is_private > 0)";
                
                if (!$dbw->execute_query($query)) {
                    throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
                }
                
                $message_count = 0;
                if ($dbw->fetch_row()) {
                    $message_count = $dbw->field_by_name("cnt");
                }
                
                $dbw->free_result();
                
                if ($message_count < $access_message_count) {
                    $err .= text("MinMessageCountComment") . ": " . $access_message_count . "\n";
                }
            }
            
            if (!empty($err)) {
                $err = sprintf(text("ErrMessagePostNotAllowed"), $forum_name) . ":\n\n" . trim($err);
                
                throw new ForumAPIException($err, ForumAPIException::ERR_CODE_ACCESS_ERROR);
            }
            
            if ($protected_by_password && empty($_SESSION["verified_protected_forums"][$fid])) {
                throw new ForumAPIException(sprintf(text("ErrForumNotAccessible"), $forum_name), ForumAPIException::ERR_CODE_ACCESS_ERROR);
            }
            
            return;
        } // if logged user
        
        // guests and IP
        $ip = $dbw->escape(System::getIPAddress());
        
        if (!$dbw->execute_query("select block_expires, block_reason
                             from {$prfx}_ip_blocked
                             where ip = '$ip' and tp = 'IP'")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $blocked = false;
        $block_expires = "";
        $block_reason = "";
        $block_time_left = "";
        
        if ($dbw->fetch_row()) {
            $blocked = true;
            $block_reason = Emoji::Decode($dbw->field_by_name("block_reason"));
            $block_expires = "";
            $block_time_left = "";
            if ($dbw->field_by_name("block_expires")) {
                if (xstrtotime($dbw->field_by_name("block_expires")) < time()) {
                    $blocked = false;
                } else {
                    $block_expires = adjust_and_format_timezone(xstrtotime($dbw->field_by_name("block_expires")), text("DateTimeFormat"));
                    $block_time_left = format_duration(xstrtotime($dbw->field_by_name("block_expires")) - time());
                }
            }
        }
        
        $dbw->free_result();
        
        if ($blocked) {
            if (!empty($block_expires)) {
                throw new ForumAPIException(sprintf(text("ErrIPIsBlockedUntil"), System::getIPAddress(), $block_expires, $block_time_left), ForumAPIException::ERR_CODE_ACCESS_ERROR);
            } else {
                throw new ForumAPIException(sprintf(text("ErrIPIsBlocked"), System::getIPAddress()), ForumAPIException::ERR_CODE_ACCESS_ERROR);
            }
        }
        
        // guests and FingerPrint
        
        $user_marker = $dbw->escape($user_marker);
        
        if (!$dbw->execute_query("select block_expires, block_reason
                             from {$prfx}_ip_blocked
                             where ip = '$user_marker' and tp = 'UM'")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $blocked = false;
        $block_reason = "";
        $block_expires = "";
        $block_time_left = "";
        
        if ($dbw->fetch_row()) {
            $blocked = true;
            $block_reason = Emoji::Decode($dbw->field_by_name("block_reason"));
            $block_expires = "";
            $block_time_left = "";
            if ($dbw->field_by_name("block_expires")) {
                if (xstrtotime($dbw->field_by_name("block_expires")) < time()) {
                    $blocked = false;
                } else {
                    $block_expires = adjust_and_format_timezone(xstrtotime($dbw->field_by_name("block_expires")), text("DateTimeFormat"));
                    $block_time_left = format_duration(xstrtotime($dbw->field_by_name("block_expires")) - time());
                }
            }
        }
        
        $dbw->free_result();
        
        if ($blocked) {
            if (!empty($block_expires)) {
                throw new ForumAPIException(sprintf(text("ErrFingerPrintIsBlockedUntil"), $block_expires, $block_time_left), ForumAPIException::ERR_CODE_ACCESS_ERROR);
            } else {
                throw new ForumAPIException(text("ErrFingerPrintIsBlocked"), ForumAPIException::ERR_CODE_ACCESS_ERROR);
            }
        }
    } // check_blocked

    //-----------------------------------------------------------------
    function check_blocked_in_topic($dbw, $tid, $forced_guest_posting)
    {
        if ($this->forum_manager->is_admin()) {
            return;
        }
        
        $prfx = $dbw->escape(System::getDBPrefix());
        
        $uid = $dbw->escape($this->forum_manager->get_user_id());
        if (empty($uid) || $forced_guest_posting) {
            return;
        }
        
        $tid = $dbw->escape($tid);
        
        if (!$dbw->execute_query("select user_id from {$prfx}_topic_blocked where user_id = $uid and topic_id = $tid")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        if ($dbw->fetch_row()) {
            $dbw->free_result();
            
            throw new ForumAPIException(text("ErrAccountIsBlockedInTopic"), ForumAPIException::ERR_CODE_ACCESS_ERROR);
        }
        
        $dbw->free_result();
    } // check_blocked_in_topic

    //-----------------------------------------------------------------
    function handle_topic_ignorance(&$dbw, $prfx, $tid, $uid, $author)
    {
        if (empty($tid) || !is_numeric($tid)) {
            return;
        }
        
        $tid = $dbw->escape($tid);
        
        if (!$dbw->execute_query("select disable_ignore
                             from {$prfx}_topic
                             inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
                             where {$prfx}_topic.id = $tid")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $disable_ignore = 0;
        
        if ($dbw->fetch_row()) {
            $disable_ignore = $dbw->field_by_name("disable_ignore");
        }
        
        $dbw->free_result();
        
        if ($disable_ignore) {
            return;
        }

        $now = $dbw->format_datetime(time() - 30 * 24 * 3600);

        if (!empty($uid) && is_numeric($uid)) {
            $uid = $dbw->escape($uid);
            
            $query = "insert into {$prfx}_ignored_topics (topic_id, user_id, auto_ignored)
                select $tid, user_id, 1
                from {$prfx}_ignored_users
                where ignored_user_id = $uid 
                and user_id in (select id from {$prfx}_user where last_visit_date > '$now')
                and (select 1 from {$prfx}_ignored_topics where user_id = $uid and topic_id = $tid) is NULL
                and (select 1 from {$prfx}_topic where id = $tid and is_private > 0) is NULL
                ";
            if (!$dbw->execute_query($query)) {
                throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
            }
        } elseif (!empty($author)) {
            $author = $dbw->escape($author);
            
            // for users who ignore all guests
            
            $query = "insert into {$prfx}_ignored_topics (topic_id, user_id, auto_ignored)
                select $tid, id, 1
                from {$prfx}_user
                where ignore_guests_whitelist = '1'
                and last_visit_date > '$now'
                and id not in (select user_id from {$prfx}_ignored_guests where whitelist = 1)
                and (select 1 from {$prfx}_ignored_topics where user_id = {$prfx}_user.id and topic_id = $tid) is NULL";
            if (!$dbw->execute_query($query)) {
                throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
            }
            
            // for users who use guest blacklist
            
            $query = "insert into {$prfx}_ignored_topics (topic_id, user_id, auto_ignored)
                select $tid, id, 1
                from {$prfx}_user
                where ignore_guests_blacklist = '1'
                and last_visit_date > '$now'
                and id in (select user_id from {$prfx}_ignored_guests where guest_name = '$author' and whitelist = 0)
                and (select 1 from {$prfx}_ignored_topics where user_id = {$prfx}_user.id and topic_id = $tid) is NULL";
            if (!$dbw->execute_query($query)) {
                throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
            }
            
            // for users who use guest whitelist
            
            $query = "insert into {$prfx}_ignored_topics (topic_id, user_id, auto_ignored)
                select $tid, id, 1
                from {$prfx}_user
                where ignore_guests_whitelist = '1'
                and last_visit_date > '$now'
                and (select 1 from {$prfx}_ignored_guests where user_id = {$prfx}_user.id and guest_name = '$author' and whitelist = 1) is NULL
                and (select 1 from {$prfx}_ignored_topics where user_id = {$prfx}_user.id and topic_id = $tid) is NULL";
            if (!$dbw->execute_query($query)) {
                throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
            }
        }
    } // handle_topic_ignorance

    //-----------------------------------------------------------------
    function handle_topic_ignorance_archive(&$dbw, $prfx, $tid)
    {
        if (empty($tid) || !is_numeric($tid)) {
            return;
        }
        
        $tid = $dbw->escape($tid);
        
        $query = "insert into {$prfx}_ignored_topics
                  (topic_id, user_id, auto_ignored)
                  select
                  topic_id, user_id, auto_ignored
                  from {$prfx}_ignored_topics_archive
                  where
                  {$prfx}_ignored_topics_archive.topic_id = $tid
                  and not exists (select 1 from {$prfx}_ignored_topics where {$prfx}_ignored_topics.user_id = {$prfx}_ignored_topics_archive.user_id and {$prfx}_ignored_topics.topic_id = $tid)
                 ";
        if (!$dbw->execute_query($query)) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $query = "delete from {$prfx}_ignored_topics_archive where
                  topic_id = $tid";
        if (!$dbw->execute_query($query)
        ) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
    } // handle_topic_ignorance_archive

    //-----------------------------------------------------------------
    function post_message(&$request_data, &$post)
    {
        global $settings;
        global $READ_MARKER;
        global $BB_PARSER_VERSION;
        
        $_SESSION["api_posting"] = 1;
        
        if (empty($BB_PARSER_VERSION)) {
            $BB_PARSER_VERSION = 1;
        }

        if (empty($request_data["forum_id"]) && empty($request_data["topic_id"])) {
            throw new ForumAPIException(text("ErrNoForumSelected"), ForumAPIException::ERR_CODE_MISSING_REQUEST_DATA);
        }
        
        if (empty($request_data["topic_id"]) && !empty($request_data["forum_id"]) &&
            ($request_data["forum_id"] == "private" || $request_data["forum_id"] == $this->forum_manager->get_private_forum_id())
           ) {
            throw new ForumAPIException("Creation of the private topics is not supported!", ForumAPIException::ERR_CODE_SYSTEM_ERROR);
        }
        
        if (!empty($request_data["topic_id"]) && !is_numeric($request_data["topic_id"])) {
            throw new ForumAPIException(sprintf(text("ErrTopicDoesNotExist"), $request_data["topic_id"]), ForumAPIException::ERR_CODE_NOT_FOUND_ERROR);
        }
        
        if (!empty($request_data["forum_id"]) && !is_numeric($request_data["forum_id"])) {
            throw new ForumAPIException(sprintf(text("ErrForumDoesNotExist"), $request_data["forum_id"]), ForumAPIException::ERR_CODE_NOT_FOUND_ERROR);
        }
        
        $dbw = System::getDBWorker();
        if (!$dbw) {
            throw new ForumAPIException(text("ErrDbInaccessible"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $prfx = $dbw->escape(System::getDBPrefix());

        if (!empty($request_data["forum_id"])) {
            $this->check_access_to_forum($dbw, $request_data["forum_id"]);
        }

        if (!empty($request_data["topic_id"])) {
            $this->check_access_to_topic($dbw, $request_data["topic_id"]);
        }

        if (!empty($settings["archive_mode"])) {
            throw new ForumAPIException(text("MsgArchiveMode"), ForumAPIException::ERR_CODE_ACCESS_ERROR);
        }
        
        $ip = System::getIPAddress();
        
        if (!$this->check_post_ip($dbw, $ip)) {
            throw new ForumAPIException(text("ErrTooManyPostsFromIP"), ForumAPIException::ERR_CODE_ACCESS_ERROR);
        }
        
        if (!empty($request_data["post_as_guest"])) {
            shrink_spaces($request_data["post_as_guest"]);

            $symbols = "";
            if (!$this->forum_manager->check_author($request_data["post_as_guest"], $symbols)) {
                $error = text("ErrStringContainsInvalidSymbols");
                if (!empty($symbols)) $error .= "\n\n[" . $symbols . "]";
                
                throw new ForumAPIException($error, ForumAPIException::ERR_CODE_INVALID_REQUEST_DATA);
            }
        }  

        $new_topic = empty($request_data["topic_id"]);        
        
        if ($new_topic) {
            $request_data["subject"] = trim($this->forum_manager->strip_subject($request_data["subject"] ?? ""));
            
            if (empty($request_data["subject"]) && (string)$request_data["subject"] !== "0") {
                throw new ForumAPIException(text("ErrSubjectEmpty"), ForumAPIException::ERR_CODE_MISSING_REQUEST_DATA);
            }
            
            $symbols = "";
            if (!$this->forum_manager->check_subject($request_data["subject"], $symbols)) {
                $error = text("ErrStringContainsInvalidSymbols");
                if (!empty($symbols)) $error .= "\n\n[" . $symbols . "]";
                
                throw new ForumAPIException($error, ForumAPIException::ERR_CODE_INVALID_REQUEST_DATA);
            }
            
            if (utf8_strlen($request_data["subject"]) > $settings["max_topic_name_symbols"]) {
                throw new ForumAPIException(sprintf(text("ErrSubjectTooLong"), $settings["max_topic_name_symbols"]), ForumAPIException::ERR_CODE_INVALID_REQUEST_DATA);
            }
        }

        if ($new_topic) {
            $fid = $dbw->escape($request_data["forum_id"]);
            
            $query = "select
                id forum_id,
                {$prfx}_forum.name forum_name,
                {$prfx}_forum.closed topic_closed,
                {$prfx}_forum.closed forum_closed,
                {$prfx}_forum.no_guests, restricted_guest_mode, user_posting_as_guest,
                0 topic_no_guests,
                0 profiled_topic,
                -1 is_private,
                0 publish_delay
                from
                {$prfx}_forum
                where {$prfx}_forum.id = $fid";
        } else {
            $tid = $dbw->escape($request_data["topic_id"]);

            $query = "select
                forum_id,
                {$prfx}_forum.name forum_name,
                {$prfx}_topic.closed topic_closed,
                {$prfx}_forum.closed forum_closed,
                {$prfx}_forum.no_guests, restricted_guest_mode, user_posting_as_guest,
                {$prfx}_topic.no_guests topic_no_guests,
                {$prfx}_topic.profiled_topic,
                is_private,
                publish_delay
                from
                {$prfx}_topic
                inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
                where {$prfx}_topic.id = $tid";
        }
        
        if (!$dbw->execute_query($query)) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $topic_closed = false;
        $forum_closed = false;
        $no_guests_forum = false;
        $no_guests_topic = false;
        $profiled_topic = false;
        $restricted_guest_mode = false;
        $user_posting_as_guest = false;
        $is_private = 0;
        $publish_delay = 0;
        
        if ($dbw->fetch_row()) {
            $fid = $dbw->field_by_name("forum_id");
            $topic_closed = $dbw->field_by_name("topic_closed");
            $forum_closed = $dbw->field_by_name("forum_closed");
            $no_guests_forum = $dbw->field_by_name("no_guests"); 
            $no_guests_topic = $dbw->field_by_name("topic_no_guests");
            $restricted_guest_mode = $dbw->field_by_name("restricted_guest_mode");
            $user_posting_as_guest = $dbw->field_by_name("user_posting_as_guest");
            
            $profiled_topic = $dbw->field_by_name("profiled_topic");
            
            $is_private = $dbw->field_by_name("is_private");
            $publish_delay = $dbw->field_by_name("publish_delay");
            
            if ($is_private == -1) {
                $is_private = ($dbw->field_by_name("forum_name") == 'PRIVATE_MESSAGES') ? (empty($request_data["receiver"]) ? 2 : 1) : 0;
            }
        } else {
            throw new ForumAPIException(text("ErrNoForumSelected"), ForumAPIException::ERR_CODE_MISSING_REQUEST_DATA);
        }
        
        $dbw->free_result();

        $forced_guest_posting = false;
        if ($this->forum_manager->is_logged_in() && !empty($user_posting_as_guest) && !empty($request_data["post_as_guest"]) &&
            $request_data["post_as_guest"] != $this->forum_manager->get_status_user_name()
        ) {
            $forced_guest_posting = true;
        }    
        
        if ($this->forum_manager->is_master_admin()) {
            $request_data["author"] = "admin";
        } elseif ($forced_guest_posting) {
            $request_data["author"] = $request_data["post_as_guest"];
        } elseif ($this->forum_manager->is_logged_in()) {
            $request_data["author"] = $this->forum_manager->get_user_name();
        } 

        $author = $dbw->quotes_or_null($request_data["author"]);

        $tor_check = $this->forum_manager->check_tor_ip($ip);
        if (!$this->forum_manager->is_logged_in() && ($tor_check == "tor_block_write" || $tor_check == "tor_block_read")) {
            throw new ForumAPIException(text("ErrTorNodeBlocked"), ForumAPIException::ERR_CODE_ACCESS_ERROR);
        }

        // check the author
        
        if ($forced_guest_posting) {
            $check = $this->forum_manager->check_name_usage($request_data["author"], "", false);
            if ($check == 1) {
                throw new ForumAPIException(text("ErrUserNameInUse"), ForumAPIException::ERR_CODE_MISSING_REQUEST_DATA);
            } elseif ($check == 2) {
                throw new ForumAPIException(text("ErrUserNameReserved"), ForumAPIException::ERR_CODE_MISSING_REQUEST_DATA);
            } elseif ($check == 3) {
                throw new ForumAPIException(text("ErrUserNameProtected"), ForumAPIException::ERR_CODE_MISSING_REQUEST_DATA);
            }
        }
        
        $ip_rules = array();
        $ips = "";
        $matched_rule = "";
        $this->get_white_list_ips($dbw, $ips, $ip_rules);
        if ($restricted_guest_mode && $forced_guest_posting) {
            if (!$this->forum_manager->is_ip_whitelisted(System::getIPAddress(), $ip_rules, $matched_rule) &&
                $diff = $this->forum_manager->check_guest_read_marker($dbw, $READ_MARKER)) {
                throw new ForumAPIException(sprintf(text("ErrMessageGuestRestricted"), format_duration($diff)), ForumAPIException::ERR_CODE_ACCESS_ERROR);
            }
        }
        
        $this->check_message_limit($dbw, $fid, $tid ?? "", $new_topic);
        
        $this->check_blocked($dbw, $fid, $forced_guest_posting, $request_data["api_token"]);
        
        if (!empty($tid)) {
            $this->check_blocked_in_topic($dbw, $tid, $forced_guest_posting);
        }
        
        if (!$this->forum_manager->is_admin() && !$this->forum_manager->is_forum_moderator($fid) && $forum_closed) {
            throw new ForumAPIException(text("ErrForumClosed"), ForumAPIException::ERR_CODE_ACCESS_ERROR);
        }
        
        if ($forced_guest_posting && $no_guests_forum && !$user_posting_as_guest) {
            throw new ForumAPIException(text("ErrForumGuestsNotAllowed"), ForumAPIException::ERR_CODE_ACCESS_ERROR);
        }
        
        if ($forced_guest_posting && $no_guests_topic) {
            throw new ForumAPIException(text("ErrForumGuestsNotAllowed"), ForumAPIException::ERR_CODE_ACCESS_ERROR);
        }
        
        if (!$new_topic && !$this->forum_manager->is_admin() && !$this->forum_manager->is_forum_moderator($fid) && !$this->forum_manager->is_topic_moderator($tid) && $topic_closed) {
            throw new ForumAPIException(text("ErrTopicClosed"), ForumAPIException::ERR_CODE_ACCESS_ERROR);
            return false;
        }
        
        $has_attachments = false;

        if (!empty($request_data["attachments"])) {
            if (count($request_data["attachments"]) > $this->forum_manager->get_attachments_per_post()) {
                throw new ForumAPIException(sprintf(text("MaxAttachmentCount"), $this->forum_manager->get_attachments_per_post()), ForumAPIException::ERR_CODE_INVALID_REQUEST_DATA);
            }
          
            $counter = 1;
            foreach ($request_data["attachments"] as $attachment_data) {
                if (empty($attachment_data["file_name"])) {
                    throw new ForumAPIException("Name of the attachment (file_name) is not specified!", ForumAPIException::ERR_CODE_MISSING_REQUEST_DATA);
                }

                if (empty($attachment_data["mime_type"])) {
                    throw new ForumAPIException("Mime type of the attachment (mime_type) is not specified!", ForumAPIException::ERR_CODE_MISSING_REQUEST_DATA);
                }

                if (empty($attachment_data["base64_contents"])) {
                    throw new ForumAPIException("Contents of the attachment (base64_contents) is not specified!", ForumAPIException::ERR_CODE_MISSING_REQUEST_DATA);
                }

                $idx = ($counter == 1) ? "" : $counter;
                $counter++;
                
                $attachment_base_name = session_id();
                if (!empty($idx)) {
                    $attachment_base_name .= "-" . $idx;
                }
                
                $pi = pathinfo($attachment_data["file_name"]);
                $attachment_extension = val_or_empty($pi['extension']);
                
                $attachment_name = $attachment_base_name;
                if (!empty($attachment_extension)) {
                    $attachment_name .= "." . strtolower($attachment_extension);
                }

                if (!file_put_contents(APPLICATION_ROOT . "tmp/" . $attachment_name, base64_decode($attachment_data["base64_contents"]))) {
                    throw new ForumAPIException(sprintf(text("ErrFileUpload"), $attachment_data["file_name"]), ForumAPIException::ERR_CODE_PROCESSING_ERROR);
                }

                $_FILES["attachment$idx"]["type"] = $attachment_data["mime_type"];
                $_FILES["attachment$idx"]["name"] = $attachment_data["file_name"];
                $_FILES["attachment$idx"]["size"] = filesize(APPLICATION_ROOT . "tmp/" . $attachment_name);
                $_FILES["attachment$idx"]["tmp_name"] = APPLICATION_ROOT . "tmp/" . $attachment_name;
            }
          
            if (!$this->attachment_manager->handle_attachments()) {
                throw new ForumAPIException(MessageHandler::getErrors(), ForumAPIException::ERR_CODE_PROCESSING_ERROR);
            }

            $has_attachments = true;
        }

        $request_data["message"] = trim($request_data["message"] ?? "");

        if (empty($request_data["message"]) && !$has_attachments) {
            throw new ForumAPIException(text("ErrMessageEmpty"), ForumAPIException::ERR_CODE_MISSING_REQUEST_DATA);
        }

        $uid = $dbw->escape($this->forum_manager->get_user_id());
        if (empty($uid) || $forced_guest_posting) {
            $uid = "NULL";
        }
        
        $now = $dbw->format_datetime(time());
        
        $rm = $dbw->escape($READ_MARKER);
        
        $user_marker = $dbw->quotes_or_null($request_data["api_token"]);
        
        $ip = $dbw->escape($ip);
        
        if (!$dbw->start_transaction()) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }

        $search_words_appendix = "";
        $is_poll = 0;
        $is_pinned = 0;
        $poll_results_delayed = 0;
        $no_guests = 0;
        $poll_comment = "NULL";        

        $agent = $dbw->quotes_or_null("Forum API Version 1.0");
        
        $is_comment = 0;
        if ($profiled_topic && empty($request_data["is_thematic"])) {
            $is_comment = 1;
        }
        
        $is_adult = empty($request_data["is_adult"]) ? 0 : 1;
        
        if ($new_topic) {
            $search_words_appendix .= " " . $request_data["subject"];
            $subject = $dbw->quotes_or_null($request_data["subject"]);
            
            $is_blog = empty($request_data["blog"]) ? "0" : "2";
            $request_moderation = empty($request_data["request_moderation"]) ? "0" : "2";

            $no_guests = empty($request_data["no_guests"]) ? "0" : "1";
            if ($forced_guest_posting || $is_private) {
                $no_guests = "0";
            }

            $query = "insert into {$prfx}_topic (forum_id, user_id, author, name, creation_date, read_marker, user_marker, is_private, is_poll, poll_comment, poll_results_delayed, has_pinned_post, publish_delay, request_moderation, no_guests, profiled_topic)
                values ($fid, $uid, $author, $subject, '$now', '$rm', $user_marker, $is_private, $is_poll, $poll_comment, $poll_results_delayed, $is_pinned, $publish_delay, $request_moderation, $no_guests, $is_blog)";
            if (!$dbw->execute_query($query)) {
                $dbw->rollback_transaction();
                throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
            }
            
            $tid = $dbw->insert_id();
            
            $query = "insert into {$prfx}_topic_statistics (topic_id) values ($tid)";
            if (!$dbw->execute_query($query)) {
                $dbw->rollback_transaction();
                throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
            }

            $query = "update {$prfx}_forum_statistics set
            topic_count = topic_count + 1,
            topic_count_total = topic_count_total + 1
            where forum_id = $fid";
            if (!$dbw->execute_query($query)) {
                $dbw->rollback_transaction();
                throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
            }
            
            if (!$forced_guest_posting && !$is_private) {
                $query = "update {$prfx}_user_statistics set
                          topic_count = topic_count + 1
                          where user_id = $uid";
                if (!$dbw->execute_query($query)) {
                    $dbw->rollback_transaction();
                    throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
                }
                
                $query = "update {$prfx}_user set last_ip = '$ip' where id = $uid";
                if (!$dbw->execute_query($query)) {
                    $dbw->rollback_transaction();
                    throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
                }
            }

            try {
                $this->handle_topic_ignorance($dbw, $prfx, $tid, $forced_guest_posting ? "" : $this->forum_manager->get_user_id(), $request_data["author"]);
            } catch (\Exception $ex) {
                $dbw->rollback_transaction();
                throw $ex;
            }
        } // new topic

        $query = "insert into {$prfx}_post (topic_id, user_id, author, creation_date, read_marker, user_marker, ip, pinned, is_comment, is_adult, self_edited, user_agent, bb_parser_version)
              values ($tid, $uid, $author, '$now', '$rm', $user_marker, '$ip', $is_pinned, $is_comment, $is_adult, 1, $agent, $BB_PARSER_VERSION)";
        if (!$dbw->execute_query($query)) {
            $dbw->rollback_transaction();
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $post_id = $dbw->insert_id();
        
        if ($uid != "NULL" && empty($forced_guest_posting)) {
            $query = "insert into {$prfx}_topic_participants (user_id, topic_id)
                  select $uid, $tid from {$prfx}_dual
                  where not exists (select 1 from {$prfx}_topic_participants where user_id = $uid and topic_id = $tid)";
            if (!$dbw->execute_query($query)) {
                $dbw->rollback_transaction();
                throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
            }
        }
        
        $message = $request_data["message"];
        
        $has_attachment = 0;
        
        // finalize attachments
        
        $message = preg_replace("/\\[attachment1\\]/", "[attachment=$post_id]", $message);
        
        $attachments_per_post = $this->forum_manager->get_attachments_per_post();
        for ($i = 1; $i <= $attachments_per_post; $i++) {
            $idx = ($i == 1) ? "" : $i;
            
            if (empty($_SESSION["last_attachment$idx"])) {
                continue;
            }
            
            $attachment_type = "";
            $attachment_name = "";
            $attachment_origin_name = "";
            if ($this->attachment_manager->finalize_attachment($post_id, $attachment_name, $attachment_origin_name, $attachment_type, $idx)) {
                if (stripos($message, "[attachment$idx]") === false && stripos($message, "[attachment$idx=$post_id]") === false) {
                    $message .= "\n\n[attachment$idx]";
                }
                
                $message = str_ireplace("[attachment$idx]", "[attachment$idx=$post_id]", $message);
                
                $attachment_name = $dbw->quotes_or_null($attachment_name);
                $attachment_origin_name = $dbw->quotes_or_null(Emoji::Encode($attachment_origin_name));
                $attachment_type = $dbw->escape($attachment_type);
                
                $idx_db = $dbw->escape($idx);
                if (empty($idx_db)) {
                    $idx_db = 1;
                }
                
                $query = "insert into {$prfx}_attachment (post_id, nr, name, origin_name, type, user_id, last_post_id)
                  values ($post_id, $idx_db, $attachment_name, $attachment_origin_name, '$attachment_type', $uid, $post_id)";
                if (!$dbw->execute_query($query)) {
                    $dbw->rollback_transaction();
                    throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
                }
                
                $bin_str = str_repeat("0", $attachments_per_post);
                $bin_str[$attachments_per_post - $i] = "1";
                $has_attachment |= bindec($bin_str);
            } else {
                $dbw->rollback_transaction();
                throw new ForumAPIException(MessageHandler::getErrors(), ForumAPIException::ERR_CODE_PROCESSING_ERROR);
            }
        } // attachments
        
        $message = trim($message, "\r\n");

        $html_message = "";
        $has_picture = "0";
        $has_video = "0";
        $has_audio = "0";
        $has_link = "0";
        $has_code = "0";
        $has_attachment_ref = 0;
        if (!$this->format_manager->format_message($dbw, $message, $html_message, $has_picture, $has_video, $has_audio, $has_link, $has_code, $has_attachment_ref, $post_id)) {
            throw new ForumAPIException(MessageHandler::getErrors(), ForumAPIException::ERR_CODE_INVALID_REQUEST_DATA);
            $dbw->rollback_transaction();
        }
        
        $query = "update {$prfx}_post set has_attachment = $has_attachment, has_attachment_ref = $has_attachment_ref where id = $post_id";
        if (!$dbw->execute_query($query)) {
            $dbw->rollback_transaction();
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }

        $html_message = trim($html_message);
        
        $html_message_check = trim(strip_tags($html_message, "<img><audio><video><iframe>"));
        if (empty($html_message_check) && (string)$html_message_check !== "0") {
            $dbw->rollback_transaction();
            throw new ForumAPIException(text("ErrMessageEmpty"), ForumAPIException::ERR_CODE_MISSING_REQUEST_DATA);
        }
        
        $message = Emoji::Encode($message);
        $html_message = Emoji::Encode($html_message);
        
        $citated_posts = array();
        
        if (!empty($request_data["citated_posts"])) {
            $citated_posts = explode(",", trim($request_data["citated_posts"], ", "));
        }
        
        // take possible pasted citations of the top level only into consideration
        
        $tmp_html = $html_message;
        remove_nested_quotes($tmp_html, $tmp_html, 1);
        if (preg_match_all("/data-cmid=\"(\d+)\"/", $tmp_html, $matches)) {
            $citated_posts = array_merge($citated_posts, $matches[1]);
        }
        
        $plain_text = preg_replace("/[ \t]+/", " ", trim(strip_tags($html_message)));
        $plain_text = preg_replace("/[\n\r]+/", "\r\n", $plain_text);
        
        $short_message = $message;
        
        $message = $dbw->quotes_or_null($message);
        $html_message = $dbw->quotes_or_null($html_message);
        $plain_text = $dbw->quotes_or_null($plain_text);

        $query = "update {$prfx}_post set
              text_content = $message,
              html_content = $html_message,
              searchable_content = $plain_text,
              has_picture = '$has_picture',
              has_video = '$has_video',
              has_audio = '$has_audio',
              has_link = '$has_link',
              has_code = '$has_code'
              where id = $post_id";
        
        if (!$dbw->execute_query($query)) {
            $dbw->rollback_transaction();
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        // post hierarchy
        
        if (!empty($citated_posts)) {
            $in_list = $dbw->escape(implode(", ", $citated_posts));
            
            $query = "insert into {$prfx}_post_hierarchy
              (parent_post_id, reply_post_id)
              select id, $post_id from  {$prfx}_post
              where id in ($in_list)";
            if (!$dbw->execute_query($query)) {
                $dbw->rollback_transaction();
                throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
            }
        }
        
        // statistics
        
        $query = "update {$prfx}_topic_statistics set
              post_count = post_count + 1,
              post_count_total = post_count_total + 1,
              last_message_date = '$now',
              last_message_id = $post_id
              where topic_id = $tid";
        if (!$dbw->execute_query($query)) {
            $dbw->rollback_transaction();
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        if ($uid != "NULL" && !$is_private && !$forced_guest_posting) {
            $query = "update {$prfx}_user_statistics set
                post_count = post_count + 1
                where user_id = $uid";
            if (!$dbw->execute_query($query)) {
                $dbw->rollback_transaction();
                throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
            }
            
            $query = "update {$prfx}_user set
                last_post_date = '$now'
                where id = $uid";
            if (!$dbw->execute_query($query)) {
                $dbw->rollback_transaction();
                throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
            }
        }
        
        $dtnow = $dbw->format_datetime(mktime(0, 0, 0, date("n"), date("j"), date("Y")));
        
        if ($uid != "NULL") {
            $query = "insert into {$prfx}_daily_statistics (dt, user_id, forum_id)
                select '$dtnow', $uid, $fid
                from {$prfx}_dual
                where
                not exists (select 1 from {$prfx}_daily_statistics where dt = '$dtnow' and user_id = $uid and forum_id = $fid);
               ";
            if (!$dbw->execute_query($query)) {
                $dbw->rollback_transaction();
                throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
            }
            
            if (!$is_private) {
                $query = "update {$prfx}_daily_statistics set
                    post_count = post_count + 1
                    where
                    dt = '$dtnow' and user_id = $uid and forum_id = $fid;
                   ";
                if (!$dbw->execute_query($query)) {
                    $dbw->rollback_transaction();
                    throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
                }
            }
        } else {
            $query = "insert into {$prfx}_daily_statistics (dt, user_id, forum_id)
                select '$dtnow', NULL, $fid
                from {$prfx}_dual
                where
                not exists (select 1 from {$prfx}_daily_statistics where dt = '$dtnow' and user_id is NULL and forum_id = $fid);
               ";
            if (!$dbw->execute_query($query)) {
                $dbw->rollback_transaction();
                throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
            }
            
            $query = "update {$prfx}_daily_statistics set
                post_count = post_count + 1
                where
                dt = '$dtnow' and user_id is NULL and bot is NULL and forum_id = $fid;
               ";
            if (!$dbw->execute_query($query)) {
                $dbw->rollback_transaction();
                throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
            }
        }
        
        $query = "update {$prfx}_forum_statistics set
              last_message_date = '$now',
              last_message_id = $post_id
              where forum_id = $fid";
        if (!$dbw->execute_query($query)) {
            $dbw->rollback_transaction();
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }

        $all_appealed_users = array();
        $appealed_users = array();
        if (preg_match_all("/(@|%)([^%@\r\n\t]+?)\\1/iu", $tmp_html, $matches)) {
            $in_list = "";
            
            foreach ($matches[2] as $user) {
                $in_list .= "'" . $dbw->escape($user) . "', ";
            }
            
            $in_list = trim($in_list, ", ");
            
            if (!$dbw->execute_query("select user_name from {$prfx}_user where user_name in ($in_list)")) {
                MessageHandler::setError(text("ErrQueryFailed"), $dbw->get_last_error() . "\n\n" . $dbw->get_last_query());
                $dbw->rollback_transaction();
                return false;
            }
            
            while ($dbw->fetch_row()) {
                $all_appealed_users[$dbw->field_by_name("user_name")] = $dbw->field_by_name("user_name");
            }
            
            $dbw->free_result();
            
            if (!$dbw->execute_query("select {$prfx}_user.id as user_id, {$prfx}_post.topic_id,
                                      email, user_name, last_host, send_notifications, interface_language,
                                      {$prfx}_ignored_topics.topic_id topic_ignored, ignore_guests_whitelist
                                      from {$prfx}_post
                                      inner join {$prfx}_user on ({$prfx}_user.user_name in ($in_list) and {$prfx}_user.turnoff_personal_appeals = 0)
                                      inner join {$prfx}_topic on ({$prfx}_post.topic_id = {$prfx}_topic.id)
                                      inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
                                      left join {$prfx}_ignored_topics on ({$prfx}_user.id = {$prfx}_ignored_topics.user_id and {$prfx}_ignored_topics.topic_id = $tid)
                                      where {$prfx}_post.id = $post_id and
                                      ({$prfx}_topic.publish_delay <> 1 or {$prfx}_topic.user_id = {$prfx}_user.id) and
                                      ({$prfx}_topic.is_private < 1 or {$prfx}_topic.id in (select {$prfx}_private_topics.topic_id from {$prfx}_private_topics where {$prfx}_private_topics.participant_id = {$prfx}_user.id)) and
                                      (({$prfx}_forum.restricted_access in (0, 2) and {$prfx}_forum.deleted <> 1 and {$prfx}_topic.deleted <> 1) or
                                       {$prfx}_forum.id in (select forum_id from {$prfx}_forum_moderator where user_id = {$prfx}_user.id) or
                                       ({$prfx}_forum.id in (select forum_id from {$prfx}_forum_member where user_id = {$prfx}_user.id) and {$prfx}_forum.deleted <> 1 and {$prfx}_topic.deleted <> 1)
                                      )")
            ) {
                $dbw->rollback_transaction();
                throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
            }
            
            while ($dbw->fetch_row()) {
                // This user ignores the topic, no notification
                if ($dbw->field_by_name("topic_ignored")) {
                    continue;
                }
                
                // It is a comment post, and the user does not want to see comments
                if (!empty($_SESSION["filtered_topics"][$dbw->field_by_name("topic_id")]) && !empty($is_comment)) {
                    continue;
                }
                
                $appealed_users[$dbw->field_by_name("user_id")] = array(
                    "name" => $dbw->field_by_name("user_name"),
                    "email" => $dbw->field_by_name("email"),
                    "send_notifications" => $dbw->field_by_name("send_notifications"),
                    "last_host" => $dbw->field_by_name("last_host"),
                    "interface_language" => $dbw->field_by_name("interface_language"),
                    "ignores_all_guests" => $dbw->field_by_name("ignore_guests_whitelist")
                );
            }
            
            $dbw->free_result();
            
            if (!$this->forum_manager->get_my_ignore_status_for_users($dbw, $appealed_users)) {
                $dbw->rollback_transaction();
                throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
            }
        }
        
        if (count($appealed_users) > 0 && !$this->forum_manager->is_logged_in() &&
            !$this->forum_manager->is_ip_whitelisted(System::getIPAddress(), $ip_rules, $matched_rule) &&
            $diff = $this->forum_manager->check_guest_read_marker($dbw, $READ_MARKER)) {
            $dbw->rollback_transaction();
            throw new ForumAPIException(sprintf(text("ErrMessageGuestAppealsRestricted"), format_duration($diff)), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        if (count($appealed_users) > 10) {
            $dbw->rollback_transaction();
            throw new ForumAPIException(sprintf(text("ErrTooManyAppeals"), 10), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        try {
            $this->handle_topic_ignorance_archive($dbw, $prfx, $tid);
        } catch (\Exception $ex) {
            $dbw->rollback_transaction();
            throw $ex;
        }
        
        if ($no_guests) {
            $author = $dbw->quotes_or_null($this->forum_manager->get_status_user_name());
            $query = "update {$prfx}_post set
                  last_warned_by = $author,
                  last_warning = 'MSG(MsgGuestsDisallowed)'
                  where id = $post_id";
            
            if (!$dbw->execute_query($query)) {
                $dbw->rollback_transaction();
                throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
            }
          
            $topic_data = array();
            
            if (!$dbw->execute_query("select {$prfx}_topic.id, user_id, forum_id,
                                 {$prfx}_forum.name forum_name, {$prfx}_topic.name topic_name, is_private,
                                 email, user_name, author, last_host, send_notifications, interface_language
                                 from
                                 {$prfx}_topic
                                 inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
                                 left join {$prfx}_user on ({$prfx}_topic.user_id = {$prfx}_user.id)
                                 where {$prfx}_topic.id = $tid")) {
                $dbw->rollback_transaction();
                throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
            }
            
            while ($dbw->fetch_row()) {
                $topic_data = array(
                    "topic_id" => $dbw->field_by_name("id"),
                    "topic_name" => $dbw->field_by_name("topic_name"),
                    "is_private" => $dbw->field_by_name("is_private"),
                    "forum_id" => $dbw->field_by_name("forum_id"),
                    "forum_name" => $dbw->field_by_name("forum_name"),
                    "author_id" => $dbw->field_by_name("user_id"),
                    "author_name" => $dbw->field_by_name("user_name") ? $dbw->field_by_name("user_name") : $dbw->field_by_name("author"),
                    "author_email" => $dbw->field_by_name("email"),
                    "send_notifications" => $dbw->field_by_name("send_notifications"),
                    "last_host" => $dbw->field_by_name("last_host"),
                    "interface_language" => $dbw->field_by_name("interface_language"),
                    "action" => "disallow_guests"
                );
            }
            
            $dbw->free_result();

            if (!$this->forum_manager->log_moderator_event($dbw, $prfx, $topic_data)) {
                $dbw->rollback_transaction();
                throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
            }
        } // no guests
        
        if (!$dbw->commit_transaction()) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }

        $this->forum_manager->track_hit($tid, $fid);

        $this->forum_manager->track_readmarker_activity();

        if ($new_topic && !$this->forum_manager->handle_request_moderation($tid)) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }

        if (!$this->forum_manager->do_after_post_mailing($dbw, $prfx, $fid, $tid, $post_id, $is_private, $message, $citated_posts, $short_message, $search_words_appendix, $all_appealed_users, $appealed_users)) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }

        $where = "where {$prfx}_post.id = $post_id";
        
        if (!$dbw->execute_query($this->forum_manager->get_query_topic_posts($prfx, $uid, $where, "", ""))) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $post_list = [];
        $user_ids = [];
        $this->forum_manager->collect_posts($dbw, $uid, $post_list, $user_ids);

        $dbw->free_result();
        
        if (!empty($post_list)) {
            $post = array_shift($post_list);

            unset($post["creation_date_sec"]);
            unset($post["user_marker"]);
            unset($post["read_marker"]);
            unset($post["user_agent"]);
            unset($post["aname"]);
            unset($post["ip"]);
            unset($post["topic_creation_date_sec"]);
            unset($post["topic_author_read_marker"]);
            unset($post["self_edited"]);
            unset($post["editable"]);
            unset($post["moderatable"]);
        }        
    }

    //-----------------------------------------------------------------
    function post_attachment(&$request_data, &$post)
    {
        global $settings;
        global $READ_MARKER;

        $_SESSION["api_posting"] = 1;

        if (empty($request_data["post_id"])) {
            throw new ForumAPIException(text("ErrNoPostSelected"), ForumAPIException::ERR_CODE_MISSING_REQUEST_DATA);
        }

        if (empty($request_data["file_name"])) {
            throw new ForumAPIException("Name of the attachment (file_name) is not specified!", ForumAPIException::ERR_CODE_MISSING_REQUEST_DATA);
        }

        if (empty($request_data["mime_type"])) {
            throw new ForumAPIException("Mime type of the attachment (mime_type) is not specified!", ForumAPIException::ERR_CODE_MISSING_REQUEST_DATA);
        }

        $dbw = System::getDBWorker();
        if (!$dbw) {
            throw new ForumAPIException(text("ErrDbInaccessible"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $prfx = $dbw->escape(System::getDBPrefix());

        $uid = $dbw->escape($this->forum_manager->get_user_id());
        if (empty($uid)) {
            $uid = 0;
        }

        $post_id = $dbw->escape($request_data["post_id"]);
        
        if (!$rodbw->execute_query("select topic_id from {$prfx}_post where id = $post_id")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $topic_id = 0;
        
        if ($rodbw->fetch_row()) {
            $topic_id = $rodbw->field_by_name("topic_id");
        }
        
        $rodbw->free_result();
        
        if (empty($topic_id)) {
            throw new ForumAPIException(sprintf(text("ErrPostDoesNotExist"), $request_data["post_id"]), ForumAPIException::ERR_CODE_NOT_FOUND_ERROR);
        }

        $has_attachment = 0;
        
        if (!$dbw->execute_query("select user_id, read_marker, has_attachment, text_content from {$prfx}_post where id = $post_id")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        if (!$dbw->fetch_row()) {
            $dbw->free_result();
            throw new ForumAPIException(text("ErrNoPostSelected"), ForumAPIException::ERR_CODE_MISSING_REQUEST_DATA);
        }
        
        if ($dbw->field_by_name("user_id") && $dbw->field_by_name("user_id") != $uid) {
            $dbw->free_result();
            throw new ForumAPIException("You are not the author of this message ($request_data[post_id])!", ForumAPIException::ERR_CODE_ACCESS_ERROR);
        }
        
        if (!$dbw->field_by_name("user_id") && $dbw->field_by_name("read_marker") != $READ_MARKER) {
            $dbw->free_result();
            throw new ForumAPIException("You are not the author of this message ($request_data[post_id])!", ForumAPIException::ERR_CODE_ACCESS_ERROR);
        }

        $has_attachment = $dbw->field_by_name("has_attachment");
        $message = $dbw->field_by_name("text_content");

        $dbw->free_result();

        if (!$dbw->execute_query("select count(*) cnt from {$prfx}_attachment where post_id = $post_id")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $attachment_count = 0;
        
        if ($dbw->fetch_row()) {
            $attachment_count = $dbw->field_by_name("cnt");
        }

        $dbw->free_result();
        
        $attachments_per_post = $this->forum_manager->get_attachments_per_post();
        
        if ($attachment_count == $attachments_per_post) {
            throw new ForumAPIException(sprintf(text("MaxAttachmentCount"), $attachments_per_post), ForumAPIException::ERR_CODE_INVALID_REQUEST_DATA);
        }
        
        // find next free slot
        
        $bin_str = str_pad(decbin($has_attachment), $attachments_per_post, '0', STR_PAD_LEFT);
        $bin_str = strrev($bin_str);
        
        $pos = strpos($bin_str, '0');
        if ($pos === false) {
            throw new ForumAPIException(sprintf(text("MaxAttachmentCount"), $attachments_per_post), ForumAPIException::ERR_CODE_INVALID_REQUEST_DATA);
        }
        
        $pos++;
        
        if ($pos == 1) $idx = "";
        else           $idx = $pos;
        
        $attachment_base_name = session_id();
        if (!empty($idx)) {
            $attachment_base_name .= "-" . $idx;
        }
        
        $pi = pathinfo($request_data["file_name"]);
        $attachment_extension = val_or_empty($pi['extension']);
        
        $attachment_name = $attachment_base_name;
        if (!empty($attachment_extension)) {
            $attachment_name .= "." . strtolower($attachment_extension);
        }

        if (!file_put_contents(APPLICATION_ROOT . "tmp/" . $attachment_name, file_get_contents("php://input"))) {
            throw new ForumAPIException(sprintf(text("ErrFileUpload"), $request_data["file_name"]), ForumAPIException::ERR_CODE_PROCESSING_ERROR);
        }

        $_FILES["attachment$idx"]["type"] = $request_data["mime_type"];
        $_FILES["attachment$idx"]["name"] = $request_data["file_name"];
        $_FILES["attachment$idx"]["size"] = filesize(APPLICATION_ROOT . "tmp/" . $attachment_name);
        $_FILES["attachment$idx"]["tmp_name"] = APPLICATION_ROOT . "tmp/" . $attachment_name;
      
        if (!$this->attachment_manager->handle_attachments()) {
            throw new ForumAPIException(MessageHandler::getErrors(), ForumAPIException::ERR_CODE_PROCESSING_ERROR);
        }

        if (empty($_SESSION["last_attachment$idx"])) {
            throw new ForumAPIException(sprintf(text("ErrFileUpload"), $request_data["file_name"]), ForumAPIException::ERR_CODE_PROCESSING_ERROR);
        }
        
        if (!$dbw->start_transaction()) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }

        $attachment_type = "";
        $attachment_name = "";
        $attachment_origin_name = "";
        if ($this->attachment_manager->finalize_attachment($post_id, $attachment_name, $attachment_origin_name, $attachment_type, $idx)) {
            if (stripos($message, "[attachment$idx]") === false && stripos($message, "[attachment$idx=$post_id]") === false) {
                $message .= "\n\n[attachment$idx]";
            }
            
            $message = str_ireplace("[attachment$idx]", "[attachment$idx=$post_id]", $message);
            
            $attachment_name = $dbw->quotes_or_null($attachment_name);
            $attachment_origin_name = $dbw->quotes_or_null(Emoji::Encode($attachment_origin_name));
            $attachment_type = $dbw->escape($attachment_type);
            
            $idx_db = $dbw->escape($idx);
            if (empty($idx_db)) {
                $idx_db = 1;
            }
            
            $query = "insert into {$prfx}_attachment (post_id, nr, name, origin_name, type, user_id, last_post_id)
              values ($post_id, $idx_db, $attachment_name, $attachment_origin_name, '$attachment_type', $uid, $post_id)";
            if (!$dbw->execute_query($query)) {
                $dbw->rollback_transaction();
                throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
            }
            
            $bin_str = str_repeat("0", $attachments_per_post);
            $bin_str[$attachments_per_post - $pos] = "1";
            $has_attachment |= bindec($bin_str);
        } else {
            $dbw->rollback_transaction();
            throw new ForumAPIException(MessageHandler::getErrors(), ForumAPIException::ERR_CODE_PROCESSING_ERROR);
        }

        $message = trim($message, "\n\r");
        
        $html_message = "";
        $has_picture = "0";
        $has_video = "0";
        $has_audio = "0";
        $has_link = "0";
        $has_code = "0";
        $has_attachment_ref = 0;
        if (!$this->format_manager->format_message($dbw, $message, $html_message, $has_picture, $has_video, $has_audio, $has_link, $has_code, $has_attachment_ref, $post_id)) {
            $dbw->rollback_transaction();
            return false;
        }
        
        $html_message = trim($html_message);
        
        $message = Emoji::Encode($message);
        $html_message = Emoji::Encode($html_message);
        
        $message = $dbw->quotes_or_null($message);
        $html_message = $dbw->quotes_or_null($html_message);

        $query = "update {$prfx}_post set 
                  text_content = $message,
                  html_content = $html_message,
                  has_attachment = $has_attachment, 
                  has_attachment_ref = $has_attachment_ref,
                  has_picture = $has_picture, 
                  has_video = $has_video, 
                  has_audio = $has_audio, 
                  has_link = $has_link, 
                  has_code = $has_code 
                  where id = $post_id";
        if (!$dbw->execute_query($query)) {
            $dbw->rollback_transaction();
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }

        if (!$dbw->commit_transaction()) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }

        $where = "where {$prfx}_post.id = $post_id";
        
        if (!$dbw->execute_query($this->forum_manager->get_query_topic_posts($prfx, $uid, $where, "", ""))) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $post_list = [];
        $user_ids = [];
        $this->forum_manager->collect_posts($dbw, $uid, $post_list, $user_ids);

        $dbw->free_result();
        
        if (!empty($post_list)) {
            $post = array_shift($post_list);

            unset($post["creation_date_sec"]);
            unset($post["read_marker"]);
            unset($post["user_marker"]);
            unset($post["user_agent"]);
            unset($post["aname"]);
            unset($post["ip"]);
            unset($post["topic_creation_date_sec"]);
            unset($post["topic_author_read_marker"]);
            unset($post["self_edited"]);
            unset($post["editable"]);
            unset($post["moderatable"]);
        }        
    }
    
    //-----------------------------------------------------------------
    function get_user_data(&$user_data, &$request_data)
    {
        global $settings;
        global $READ_MARKER;
        
        if (empty($request_data["user_id"])) {
            throw new ForumAPIException(sprintf(text("ErrUserDoesNotExist"), "-"), ForumAPIException::ERR_CODE_NOT_FOUND_ERROR);
        }
        
        if (!is_numeric($request_data["user_id"])) {
            throw new ForumAPIException(sprintf(text("ErrUserDoesNotExist"), $request_data["user_id"]), ForumAPIException::ERR_CODE_NOT_FOUND_ERROR);
        }

        $tmp = [];
        
        if (!$this->forum_manager->get_user_data($request_data["user_id"], $tmp)) {
            throw new ForumAPIException(MessageHandler::getErrors(), ForumAPIException::ERR_CODE_PROCESSING_ERROR);
        }
        
        $tmp["ignores"] = [];
        $tmp["ignored"] = [];
        if(!$this->forum_manager->get_user_ignore_info($request_data["user_id"], $tmp["ignores"], $tmp["ignored"])) {
            throw new ForumAPIException(MessageHandler::getErrors(), ForumAPIException::ERR_CODE_PROCESSING_ERROR);
        }
        
        $tmp["hides"] = [];
        $tmp["hidden"] = [];
        if(!$this->forum_manager->get_user_hide_info($request_data["user_id"], $tmp["hides"], $tmp["hidden"]))
        {
            throw new ForumAPIException(MessageHandler::getErrors(), ForumAPIException::ERR_CODE_PROCESSING_ERROR);
        }

        $user_data = $tmp;

        unset($user_data["user_login"]);
        unset($user_data["user_email"]);
        unset($user_data["api_active"]);
        unset($user_data["api_token"]);
        unset($user_data["my_notes"]);
        unset($user_data["my_notes_bb"]);
        unset($user_data["hide_email"]);
        unset($user_data["privileged"]);
        unset($user_data["privileged_topic_moderator"]);
        unset($user_data["global_ban_allowed"]);
        unset($user_data["hide_ignored"]);
        unset($user_data["hide_pictures"]);
        unset($user_data["donot_hide_adult_pictures"]);
        unset($user_data["hide_user_info"]);
        unset($user_data["hide_user_avatars"]);
        unset($user_data["no_private_messages"]);
        unset($user_data["turnoff_events"]);
        unset($user_data["send_notifications"]);
        unset($user_data["turnoff_personal_appeals"]);
        unset($user_data["last_host"]);
        unset($user_data["donot_notify_on_rates"]);
        unset($user_data["notify_about_new_users"]);
        unset($user_data["notify_citation"]);
        unset($user_data["notify_on_words"]);
        unset($user_data["custom_css"]);
        unset($user_data["custom_smiles"]);
        unset($user_data["skin_properties"]);
        unset($user_data["ip"]);
        unset($user_data["last_ip"]);
        unset($user_data["ip_blocked"]);
        unset($user_data["last_ip_blocked"]);
        unset($user_data["skin"]);
        unset($user_data["interface_language"]);
        unset($user_data["read_marker"]);
        unset($user_data["time_zone"]);
        unset($user_data["show_ip"]);
        unset($user_data["words_to_notify"]);
        unset($user_data["forum_access"]);
     }
     
    //-----------------------------------------------------------------
     function delete_posts(&$request_data)
     {
        global $settings;
        global $READ_MARKER;

        if (empty($request_data["posts"])) {
            throw new ForumAPIException(text("ErrNoPostSelected"), ForumAPIException::ERR_CODE_NOT_FOUND_ERROR);
        }

        $_REQUEST["posts"] = $request_data["posts"];

        if(!$this->forum_manager->delete_restore_posts("delete_post"))
        {
            throw new ForumAPIException(MessageHandler::getErrors(), ForumAPIException::ERR_CODE_PROCESSING_ERROR);
        }
     }

     //-----------------------------------------------------------------
     function restore_posts(&$request_data)
     {
        global $settings;
        global $READ_MARKER;

        if (empty($request_data["posts"])) {
            throw new ForumAPIException(text("ErrNoPostSelected"), ForumAPIException::ERR_CODE_NOT_FOUND_ERROR);
        }

        $_REQUEST["posts"] = $request_data["posts"];

        if(!$this->forum_manager->delete_restore_posts("restore_post"))
        {
            throw new ForumAPIException(MessageHandler::getErrors(), ForumAPIException::ERR_CODE_PROCESSING_ERROR);
        }
     }

     //-----------------------------------------------------------------
     function get_post(&$post, &$request_data)
     {
        global $READ_MARKER;
        
        if (empty($request_data["post_id"])) {
            throw new ForumAPIException(text("ErrNoPostSelected"), ForumAPIException::ERR_CODE_NOT_FOUND_ERROR);
        }

        $rodbw = System::getRODBWorker();
        if (!$rodbw) {
            throw new ForumAPIException(text("ErrDbInaccessible"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $prfx = $rodbw->escape(System::getDBPrefix());

        $post_id = $rodbw->escape($request_data["post_id"]);

        if (!$rodbw->execute_query("select topic_id from {$prfx}_post where id = $post_id")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $topic_id = 0;
        
        if ($rodbw->fetch_row()) {
            $topic_id = $rodbw->field_by_name("topic_id");
        }
        
        $rodbw->free_result();

        if (empty($topic_id)) {
            throw new ForumAPIException(sprintf(text("ErrPostDoesNotExist"), $request_data["post_id"]), ForumAPIException::ERR_CODE_NOT_FOUND_ERROR);
        }

        $this->check_access_to_topic($rodbw, $topic_id);

        $uid = $rodbw->escape($this->forum_manager->get_user_id());
        if (empty($uid)) {
            $uid = 0;
        }

        $where = "where {$prfx}_post.id = $post_id";
        
        if (!$rodbw->execute_query($this->forum_manager->get_query_topic_posts($prfx, $uid, $where, "", ""))) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $post_list = [];
        $user_ids = [];
        $this->forum_manager->collect_posts($rodbw, $uid, $post_list, $user_ids);

        $rodbw->free_result();
        
        if (!empty($post_list)) {
            $post = array_shift($post_list);

            unset($post["creation_date_sec"]);
            unset($post["user_marker"]);
            unset($post["read_marker"]);
            unset($post["user_agent"]);
            unset($post["aname"]);
            unset($post["ip"]);
            unset($post["topic_creation_date_sec"]);
            unset($post["topic_author_read_marker"]);
            unset($post["self_edited"]);
            unset($post["editable"]);
            unset($post["moderatable"]);
        }        
    }   

     //-----------------------------------------------------------------
     function update_message(&$post, &$request_data)
     {
        global $settings;
        global $READ_MARKER;
        global $BB_PARSER_VERSION;
        
        $_SESSION["api_posting"] = 1;
        
        if (empty($BB_PARSER_VERSION)) {
            $BB_PARSER_VERSION = 1;
        }
        
        if (empty($request_data["post_id"])) {
            throw new ForumAPIException(text("ErrNoPostSelected"), ForumAPIException::ERR_CODE_NOT_FOUND_ERROR);
        }

        $dbw = System::getDBWorker();
        if (!$dbw) {
            throw new ForumAPIException(text("ErrDbInaccessible"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $prfx = $dbw->escape(System::getDBPrefix());

        $post_id = $dbw->escape($request_data["post_id"]);

        if (!$dbw->execute_query("select topic_id, profiled_topic, {$prfx}_post.user_id, {$prfx}_post.author, html_content, last_updated, last_updated_by, 
                                  {$prfx}_topic.name, {$prfx}_post.read_marker, {$prfx}_post.creation_date, post_user.user_name,
                                  forum_id, {$prfx}_forum.name forum_name, is_comment, is_adult, is_private, 
                                  topic_user.user_name topic_user_name, 
                                  {$prfx}_topic.user_id topic_user_id,
                                  {$prfx}_topic.creation_date topic_creation_date, 
                                  {$prfx}_topic.author topic_author,
                                  {$prfx}_topic.read_marker topic_read_marker
                                  from {$prfx}_post 
                                  inner join {$prfx}_topic on ({$prfx}_post.topic_id = {$prfx}_topic.id)
                                  inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
                                  left join {$prfx}_user post_user on ({$prfx}_post.user_id = post_user.id)
                                  left join {$prfx}_user topic_user on ({$prfx}_topic.user_id = topic_user.id)
                                  where {$prfx}_post.id = $post_id")) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $old_message = "";
        $forum_id = 0;
        $forum_name = "";
        $topic_id = 0;
        $topic_private = 0;
        $profiled_topic = 0;
        $old_post_is_comment = 0;
        $old_post_is_adult = 0;
        $last_updated = 0;
        $last_updated_by = "";
        $post_user_id = "";
        $post_read_marker = "";
        $old_topic_name = "";
        $post_author = "";
        
        $topic_author_id = 0;
        $topic_author = "";
        $topic_author_read_marker = "";
        $is_first = false;
        
        if ($dbw->fetch_row()) {
            $topic_id = $dbw->field_by_name("topic_id");
            $topic_private = $dbw->field_by_name("is_private");
            $forum_id = $dbw->field_by_name("forum_id");
            $forum_name = $dbw->field_by_name("is_private") ? text("PrivateTopics") : $dbw->field_by_name("forum_name");
            $profiled_topic = $dbw->field_by_name("profiled_topic");
            $post_user_id = $dbw->field_by_name("user_id");
            $old_message = $dbw->field_by_name("html_content");
            $old_topic_name = $dbw->field_by_name("name");
            $post_read_marker = $dbw->field_by_name("read_marker");
            $post_author = $dbw->field_by_name("author");
            $old_post_is_comment = $dbw->field_by_name("is_comment");
            $old_post_is_adult = $dbw->field_by_name("is_adult");

            $topic_author_id = $dbw->field_by_name("topic_user_id");
            $topic_author = $dbw->field_by_name("topic_author");
            $topic_author_read_marker = $dbw->field_by_name("topic_read_marker");
            
            $is_first = (xstrtotime($dbw->field_by_name("creation_date")) == xstrtotime($dbw->field_by_name("topic_creation_date")));

            if ($dbw->field_by_name("last_updated")) {
                $last_updated = xstrtotime($dbw->field_by_name("last_updated"));
                $last_updated_by = $dbw->field_by_name("last_updated_by");
            } else {
                $last_updated = xstrtotime($dbw->field_by_name("creation_date"));
                $last_updated_by = $dbw->field_by_name("user_name") ? $dbw->field_by_name("user_name") : $dbw->field_by_name("author");
            }
        }
        
        $dbw->free_result();
        
        if (empty($topic_id)) {
            throw new ForumAPIException(sprintf(text("ErrPostDoesNotExist"), $request_data["post_id"]), ForumAPIException::ERR_CODE_NOT_FOUND_ERROR);
        }

        $this->check_access_to_topic($dbw, $topic_id);

        if ($post_user_id && $post_user_id != $this->forum_manager->get_user_id()) {
            $dbw->free_result();
            throw new ForumAPIException("You are not the author of this message ($request_data[post_id])!", ForumAPIException::ERR_CODE_ACCESS_ERROR);
        }
        
        if (!$post_user_id && $post_read_marker != $READ_MARKER) {
            $dbw->free_result();
            throw new ForumAPIException("You are not the author of this message ($request_data[post_id])!", ForumAPIException::ERR_CODE_ACCESS_ERROR);
        }

        if (!$post_user_id) {
            $_SESSION["guest_posting_mode"] = true;
        }
        
        if (!$this->forum_manager->may_edit_message($request_data["post_id"])) {
            throw new ForumAPIException(text("ErrEditTimeExpired"), ForumAPIException::ERR_CODE_ACCESS_ERROR);
        }

        $has_attachments = false;

        if (!empty($request_data["attachments"])) {
            if (count($request_data["attachments"]) > $this->forum_manager->get_attachments_per_post()) {
                throw new ForumAPIException(sprintf(text("MaxAttachmentCount"), $this->forum_manager->get_attachments_per_post()), ForumAPIException::ERR_CODE_INVALID_REQUEST_DATA);
            }
          
            $counter = 1;
            foreach ($request_data["attachments"] as $attachment_data) {
                if (empty($attachment_data["file_name"])) {
                    throw new ForumAPIException("Name of the attachment (file_name) is not specified!", ForumAPIException::ERR_CODE_MISSING_REQUEST_DATA);
                }

                if (empty($attachment_data["mime_type"])) {
                    throw new ForumAPIException("Mime type of the attachment (mime_type) is not specified!", ForumAPIException::ERR_CODE_MISSING_REQUEST_DATA);
                }

                if (empty($attachment_data["base64_contents"])) {
                    throw new ForumAPIException("Contents of the attachment (base64_contents) is not specified!", ForumAPIException::ERR_CODE_MISSING_REQUEST_DATA);
                }

                $idx = ($counter == 1) ? "" : $counter;
                $counter++;
                
                $attachment_base_name = session_id();
                if (!empty($idx)) {
                    $attachment_base_name .= "-" . $idx;
                }
                
                $pi = pathinfo($attachment_data["file_name"]);
                $attachment_extension = val_or_empty($pi['extension']);
                
                $attachment_name = $attachment_base_name;
                if (!empty($attachment_extension)) {
                    $attachment_name .= "." . strtolower($attachment_extension);
                }

                if (!file_put_contents(APPLICATION_ROOT . "tmp/" . $attachment_name, base64_decode($attachment_data["base64_contents"]))) {
                    throw new ForumAPIException(sprintf(text("ErrFileUpload"), $attachment_data["file_name"]), ForumAPIException::ERR_CODE_PROCESSING_ERROR);
                }

                $_FILES["attachment$idx"]["type"] = $attachment_data["mime_type"];
                $_FILES["attachment$idx"]["name"] = $attachment_data["file_name"];
                $_FILES["attachment$idx"]["size"] = filesize(APPLICATION_ROOT . "tmp/" . $attachment_name);
                $_FILES["attachment$idx"]["tmp_name"] = APPLICATION_ROOT . "tmp/" . $attachment_name;
            }
          
            if (!$this->attachment_manager->handle_attachments()) {
                throw new ForumAPIException(MessageHandler::getErrors(), ForumAPIException::ERR_CODE_PROCESSING_ERROR);
            }

            $has_attachments = true;
        }
        
        $subject = "";
        if (!empty($request_data["subject"]) || (string)$request_data["subject"] === "0") {
            $subject = trim($this->forum_manager->strip_subject($request_data["subject"]));

            $symbols = "";
            if (!$this->forum_manager->check_subject($request_data["subject"], $symbols)) {
                $error = text("ErrStringContainsInvalidSymbols");
                if (!empty($symbols)) $error .= "\n\n[" . $symbols . "]";
                
                throw new ForumAPIException($error, ForumAPIException::ERR_CODE_INVALID_REQUEST_DATA);
            }
            
            if (utf8_strlen($request_data["subject"]) > $settings["max_topic_name_symbols"]) {
                throw new ForumAPIException(sprintf(text("ErrSubjectTooLong"), $settings["max_topic_name_symbols"]), ForumAPIException::ERR_CODE_INVALID_REQUEST_DATA);
            }
        }

        $request_data["message"] = trim($request_data["message"] ?? "");

        if (empty($request_data["message"]) && !$has_attachments) {
            throw new ForumAPIException(text("ErrMessageEmpty"), ForumAPIException::ERR_CODE_MISSING_REQUEST_DATA);
        }

        $uid = $dbw->escape($this->forum_manager->get_user_id());
        if (empty($uid)) {
            $uid = "NULL";
        }
        
        $now = $dbw->format_datetime(time());
        
        $rm = $dbw->escape($READ_MARKER);
        
        $user_marker = $dbw->quotes_or_null($request_data["api_token"]);
        
        if (!$dbw->start_transaction()) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $query = "delete from {$prfx}_attachment where post_id = $post_id";
        if (!$dbw->execute_query($query)) {
            $dbw->rollback_transaction();
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }

        $message = $request_data["message"];
        
        $has_attachment = 0;
        
        // finalize attachments
        
        $message = preg_replace("/\\[attachment1\\]/", "[attachment=$post_id]", $message);
        
        $attachments_per_post = $this->forum_manager->get_attachments_per_post();
        for ($i = 1; $i <= $attachments_per_post; $i++) {
            $idx = ($i == 1) ? "" : $i;
            
            if (empty($_SESSION["last_attachment$idx"])) {
                continue;
            }
            
            $attachment_type = "";
            $attachment_name = "";
            $attachment_origin_name = "";
            if ($this->attachment_manager->finalize_attachment($post_id, $attachment_name, $attachment_origin_name, $attachment_type, $idx)) {
                if (stripos($message, "[attachment$idx]") === false && stripos($message, "[attachment$idx=$post_id]") === false) {
                    $message .= "\n\n[attachment$idx]";
                }
                
                $message = str_ireplace("[attachment$idx]", "[attachment$idx=$post_id]", $message);
                
                $attachment_name = $dbw->quotes_or_null($attachment_name);
                $attachment_origin_name = $dbw->quotes_or_null(Emoji::Encode($attachment_origin_name));
                $attachment_type = $dbw->escape($attachment_type);
                
                $idx_db = $dbw->escape($idx);
                if (empty($idx_db)) {
                    $idx_db = 1;
                }
                
                $query = "insert into {$prfx}_attachment (post_id, nr, name, origin_name, type, user_id, last_post_id)
                  values ($post_id, $idx_db, $attachment_name, $attachment_origin_name, '$attachment_type', $uid, $post_id)";
                if (!$dbw->execute_query($query)) {
                    $dbw->rollback_transaction();
                    throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
                }
                
                $bin_str = str_repeat("0", $attachments_per_post);
                $bin_str[$attachments_per_post - $i] = "1";
                $has_attachment |= bindec($bin_str);
            } else {
                $dbw->rollback_transaction();
                throw new ForumAPIException(MessageHandler::getErrors(), ForumAPIException::ERR_CODE_PROCESSING_ERROR);
            }
        } // attachments
        
        $message = trim($message, "\r\n");

        $html_message = "";
        $has_picture = "0";
        $has_video = "0";
        $has_audio = "0";
        $has_link = "0";
        $has_code = "0";
        $has_attachment_ref = 0;
        if (!$this->format_manager->format_message($dbw, $message, $html_message, $has_picture, $has_video, $has_audio, $has_link, $has_code, $has_attachment_ref, $post_id)) {
            throw new ForumAPIException(MessageHandler::getErrors(), ForumAPIException::ERR_CODE_INVALID_REQUEST_DATA);
            $dbw->rollback_transaction();
        }
        
        $query = "update {$prfx}_post set has_attachment = $has_attachment, has_attachment_ref = $has_attachment_ref where id = $post_id";
        if (!$dbw->execute_query($query)) {
            $dbw->rollback_transaction();
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }

        $html_message = trim($html_message);
        
        $html_message_check = trim(strip_tags($html_message, "<img><audio><video><iframe>"));
        if (empty($html_message_check) && (string)$html_message_check !== "0") {
            $dbw->rollback_transaction();
            throw new ForumAPIException(text("ErrMessageEmpty"), ForumAPIException::ERR_CODE_MISSING_REQUEST_DATA);
        }
        
        $message = Emoji::Encode($message);
        $html_message = Emoji::Encode($html_message);
        
        $citated_posts = array();
        
        if (!empty($request_data["citated_posts"])) {
            $citated_posts = explode(",", trim($request_data["citated_posts"], ", "));
        }
        
        // take possible pasted citations of the top level only into consideration
        
        $tmp_html = $html_message;
        remove_nested_quotes($tmp_html, $tmp_html, 1);
        if (preg_match_all("/data-cmid=\"(\d+)\"/", $tmp_html, $matches)) {
            $citated_posts = array_merge($citated_posts, $matches[1]);
        }
        
        $plain_text = preg_replace("/[ \t]+/", " ", trim(strip_tags($html_message)));
        $plain_text = preg_replace("/[\n\r]+/", "\r\n", $plain_text);
        
        $content_changed = ($old_message != $message);
        
        $message = $dbw->quotes_or_null($message);
        $html_message = $dbw->quotes_or_null($html_message);
        $plain_text = $dbw->quotes_or_null($plain_text);

        $is_comment = 0;
        if ($profiled_topic && empty($request_data["is_thematic"])) {
            $is_comment = 1;
        }
        
        $is_adult = empty($request_data["is_adult"]) ? 0 : 1;

        $last_updated = $dbw->format_datetime($last_updated);
        $last_updated_by = $dbw->quotes_or_null($last_updated_by);

        $self_edited = "1";
        
        // topic name changed
        
        $subject_changed = (!empty($subject) && $old_topic_name != $subject);
        
        $subject_editable = false;
        if ($this->forum_manager->is_admin() || $this->forum_manager->is_forum_moderator($forum_id) || $this->forum_manager->is_topic_moderator($topic_id) ||
            (!empty($is_first) && !empty($topic_author_id) && $topic_author_id == $this->forum_manager->get_user_id()) ||
            (!empty($is_first) && $topic_author_read_marker == $READ_MARKER)
        ) {
            $subject_editable = true;
        }
        
        if (!$subject_editable) {
            $subject_changed = false;
        }
        
        if ($subject_changed) {
            $subject_db = $dbw->quotes_or_null($subject);
            $query = "update {$prfx}_topic set
                name = $subject_db
                where id = $topic_id";
            if (!$dbw->execute_query($query)) {
                $dbw->rollback_transaction();
                throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
            }

            $mod_event["action"] = "change_topic";
            $mod_event["author_id"] = $topic_author_id;
            $mod_event["author_name"] = $topic_author;
            $mod_event["read_marker"] = $READ_MARKER;
            $mod_event["comment"] = "MSG(PreviousName): " . $old_topic_name; // text("PreviousName")
            $mod_event["topic_name"] = $subject;
            $mod_event["topic_id"] = $topic_id;
            $mod_event["forum_name"] = $forum_name;
            $mod_event["forum_id"] = $forum_id;
            
            if (empty($topic_private) && !$this->forum_manager->log_moderator_event($dbw, $prfx, $mod_event)) {
                $dbw->rollback_transaction();
                throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
            }
        } // topic update

        if ($content_changed) {
            $query = "insert into {$prfx}_post_history
              (post_id, dt, author, self_edited, text_content, html_content)
              select
              id, '$last_updated', $last_updated_by, self_edited, text_content, html_content
              from {$prfx}_post
              where {$prfx}_post.id = $post_id
             ";
            if (!$dbw->execute_query($query)) {
                $dbw->rollback_transaction();
                throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
            }
        }
        
        $updated_by = $dbw->quotes_or_null($post_author);

        $query = "update {$prfx}_post set
              text_content = $message,
              html_content = $html_message,
              searchable_content = $plain_text,
              has_picture = '$has_picture',
              is_comment = '$is_comment',
              is_adult = '$is_adult',
              has_video = '$has_video',
              has_audio = '$has_audio',
              has_link = '$has_link',
              has_code = '$has_code',
              self_edited = $self_edited,
              last_updated = '$now',
              last_updated_by = $last_updated_by
              where id = $post_id";
        
        if (!$dbw->execute_query($query)) {
            $dbw->rollback_transaction();
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        if (!$dbw->commit_transaction()) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }

        $this->forum_manager->track_hit($topic_id, $forum_id);

        $this->forum_manager->track_readmarker_activity();

        $where = "where {$prfx}_post.id = $post_id";
        
        if (!$dbw->execute_query($this->forum_manager->get_query_topic_posts($prfx, $uid, $where, "", ""))) {
            throw new ForumAPIException(text("ErrQueryFailed"), ForumAPIException::ERR_CODE_DATABASE_ERROR);
        }
        
        $post_list = [];
        $user_ids = [];
        $this->forum_manager->collect_posts($dbw, $uid, $post_list, $user_ids);

        $dbw->free_result();
        
        if (!empty($post_list)) {
            $post = array_shift($post_list);

            unset($post["creation_date_sec"]);
            unset($post["user_marker"]);
            unset($post["read_marker"]);
            unset($post["user_agent"]);
            unset($post["aname"]);
            unset($post["ip"]);
            unset($post["topic_creation_date_sec"]);
            unset($post["topic_author_read_marker"]);
            unset($post["self_edited"]);
            unset($post["editable"]);
            unset($post["moderatable"]);
        }        
     }
} // ForumAPIManager