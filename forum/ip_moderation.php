<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
if (reqvar("type") == "user_ips" && $fmanager->is_logged_in() && reqvar("user") == $fmanager->get_user_name()) {
  // Allow viewing own IPs
} elseif (!$fmanager->is_moderator() || !$fmanager->may_see_ip()) {
    MessageHandler::setError(text("ErrActionNotAllowed"));
    header("Location: " . $target_url);
    exit;
}

if ($fmanager->demo_mode()) {
    MessageHandler::setWarning(text("MsgDemoMode"));
    header("Location: " . $target_url);
    exit;
}

$query_string = "";
$fmanager->apply_mlog_filter(reqvar("apply_filter"), $query_string);
if (!reqvar_empty("apply_filter")) {
    $_SESSION["jump_to_log"] = 1;
    header("Location: ip_moderation.php" . $query_string);
    exit;
}

$action_list = array();
$fmanager->build_action_list($action_list);

$user_data = array();

if (!reqvar_empty("user")) {
    $ogtype = "profile";
    
    $uid = $fmanager->user_name_to_id(reqvar("user"));
    if (!empty($uid)) {
        if (!$fmanager->get_user_data($uid, $user_data)) {
            header("location: " . $target_url);
            exit;
        }
    } else {
        $aname = reqvar("aname");
        if (reqvar("user") == "admin") {
            $aname = "admin";
        }

        if (!$fmanager->get_guest_data_for_view(reqvar("user"), $aname, $user_data)) {
            header("location: " . $target_url);
            exit;
        }
    }
}

$author_appendix = "";
if(!reqvar_empty("author"))
{
  $author_appendix = "&author=" . xrawurlencode(reqvar("author"));
}
//------------------------------------------------------------------
$title = text("ModerateIP");
$subtitle = text("ModerateIP");
//------------------------------------------------------------------
$ip_blocked = 1;

switch (reqvar("type")) {
    case "user_ips":
        $title = text("ShowAuthorIPs");
        $subtitle = text("ShowAuthorIPs");
        if (!empty($user_data["user_name"])) {
            $title .= ": " . $fmanager->get_display_name($user_data["user_name"]);;
        }

        $user_ips = array();
        $fmanager->get_user_ips(reqvar("user"), $user_ips);
        break;
    case "other_users":
        $title = text("ShowMembersOfAuthorIPs");
        $subtitle = text("ShowMembersOfAuthorIPs");
        if (!empty($user_data["user_name"])) {
            $title .= ": " . $fmanager->get_display_name($user_data["user_name"]);
        }

        $other_users = array();
        $fmanager->get_other_users(reqvar("user"), $other_users);
        break;
    
    case "ip_users":
        $title = text("ShowMembersOfIP");
        $subtitle = text("ShowMembersOfIP");
        MessageHandler::setFocusElement("ip");
        
        $ip_blocked = $fmanager->is_ip_blocked(reqvar("ip"));
        
        $ip_users = array();
        $fmanager->get_ip_users(reqvar("ip"), $ip_users);
        break;
    
    case "um_users":
        $title = text("ShowMembersOfFingerPrint");
        $subtitle = text("ShowMembersOfFingerPrint");
        MessageHandler::setFocusElement("ip");
        
        $ip_blocked = $fmanager->is_um_blocked(reqvar("ip"));
        
        $ip_users = array();
        $fmanager->get_um_users(reqvar("ip"), $ip_users);
        break;
    
    case "moderation":
        $title = text("ModerateIP");
        $subtitle = text("ModerateIP");
        MessageHandler::setFocusElement("ip");
        $event_list = array();
        $pagination_info = array();
        $pagination_info["page_count"] = 1;
        $pagination_info["page"] = reqvar_empty("mpage") ? 1 : reqvar("mpage");
        $pagination_info["base_url"] = "ip_moderation.php?type=moderation&ip=" . xrawurlencode(reqvar("ip")) . $author_appendix . "#log";
        $pagination_info["base_url_pagination"] = "ip_moderation.php?type=moderation&ip=" . xrawurlencode(reqvar("ip")) . $author_appendix . "&mpage=$#log";
        
        $ip_blocked = $fmanager->is_ip_blocked(reqvar("ip"));
        
        $fmanager->get_moderator_events($event_list, $pagination_info);
        break;
    
    case "um_moderation":
        $title = text("ModerateFingerPrint");
        $subtitle = text("ModerateFingerPrint");
        MessageHandler::setFocusElement("ip");
        $event_list = array();
        $pagination_info = array();
        $pagination_info["page_count"] = 1;
        $pagination_info["page"] = reqvar_empty("mpage") ? 1 : reqvar("mpage");
        $pagination_info["base_url"] = "ip_moderation.php?type=moderation&ip=" . xrawurlencode(reqvar("ip")) . $author_appendix . "#log";
        $pagination_info["base_url_pagination"] = "ip_moderation.php?type=moderation&ip=" . xrawurlencode(reqvar("ip")) . $author_appendix . "&mpage=$#log";
        
        $ip_blocked = $fmanager->is_um_blocked(reqvar("ip"));
        
        $fmanager->get_moderator_events($event_list, $pagination_info);
        break;
}

$title .= " - " . get_site_name(current_language());
$ogtitle = $title;

if (!empty($user_data["photo"])) {
  $ogimage = $user_data["photo"];
}  
elseif (!empty($user_data["avatar"])) {
  $ogimage = $user_data["avatar"];
}  

//------------------------------------------------------------------
$reason_list = array();

$reason_list["roughness"] = text("Roughness");
$reason_list["relatives_insult"] = text("RelativesInsult");
$reason_list["flood"] = text("Flood");
$reason_list["spam"] = text("Spam");
$reason_list["author_wish"] = text("AuthorWish");
$reason_list["illegal_content"] = text("IllegalContent");
$reason_list["unlawful_statement"] = text("UnlawfulStatement");
$reason_list["ethnic_hatred"] = text("EthnicHatred");
$reason_list["extremism"] = text("Extremism");

$reason_list["other_reason"] = text("OtherReason");
//------------------------------------------------------------------
$fmanager->track_hit("", "");

$online_users = array();
$forum_readers = array();
$topic_readers = array();
$topic_ignorers = array();
$topic_blocked_users = array();
$fmanager->get_online_users($online_users, $forum_readers, $topic_readers, $topic_ignorers, $topic_blocked_users, -1, -1);
//------------------------------------------------------------------
$start_date = date(text("DateFormat"), time() - 60 * 24 * 3600);
//------------------------------------------------------------------
$_SESSION["last_url"] = val_or_empty($_SERVER["REQUEST_URI"]);
//------------------------------------------------------------------
require_once "include/check_new_inc.php";
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "ip_moderation.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>