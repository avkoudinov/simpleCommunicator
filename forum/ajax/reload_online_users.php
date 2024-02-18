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
if (reqvar_empty("topic")) {
    exit;
}

if (!$fmanager->check_hash()) {
    exit;
}

$hide_from_robots = 0;
$fid = "";
$fmanager->get_topic_forum_id(reqvar("topic"), $fid, $hide_from_robots);
$fid_for_url = $fid;

if (!empty($hide_from_robots) && detect_bot(val_or_empty($_SERVER["HTTP_USER_AGENT"])) != "") {
    exit;
}

$is_private = false;
if ($fid == $fmanager->get_private_forum_id()) {
    $is_private = true;
    $fid_for_url = "private";
    
    if (!$fmanager->is_logged_in()) {
        exit;
    }
    
    if ($fmanager->is_master_admin()) {
        MessageHandler::setWarning(text("MsgMasterAdminWarning"));
        exit;
    }
}

if (!$fmanager->has_access_to_topic(reqvar("topic"), true)) {
    exit;
}

if ($fmanager->need_forum_password(reqvar("topic"), $fid)) {
    exit;
}

$topic_data = array();
if (!$fmanager->get_topic_data(reqvar("topic"), $topic_data)) {
    header("location: " . $target_url);
    exit;
}

if (!empty($topic_data["merge_target_topic"])) {
    exit;
}

$online_users = array();
$forum_readers = array();
$topic_readers = array();
$topic_ignorers = array();
$topic_blocked_users = array();
$fmanager->get_online_users($online_users, $forum_readers, $topic_readers, $topic_ignorers, $topic_blocked_users, $fid, reqvar("topic"));

debug_message(print_r($topic_blocked_users, true));

$treaders = "";
$freaders = "";

if (!empty($topic_data["is_private"])) {
    $rcnt = empty($topic_data["participants"]) ? 0 : count($topic_data["participants"]);
    
    $treaders = escape_html(text("Members")) . " ($rcnt): ";
    
    if (!empty($topic_data["participants"])) {
        foreach ($topic_data["participants"] as $pid => $pdata) {
            $appendix = "<span class='last_visit_info'>&nbsp;" . escape_html($pdata["last_visit"]) . "</span>";
            
            $online_status = "";
            if (empty($settings["hide_online_status"]) && !empty($pdata["online"])) {
                $online_status = "&nbsp;<span class='online_text'>✓</span>";
            }
            
            $treaders .= "<a href='view_profile.php?uid=$pid' >" . escape_html($pdata["user"]) . "</a>$online_status$appendix, ";
        }
        
        $treaders = trim($treaders, ", ");
    }
} else {
    $rcnt = count($topic_readers);
    if(!empty($topic_readers["g_#anonyms#"]["count"])) $rcnt += ($topic_readers["g_#anonyms#"]["count"] - 1);
    
    $treaders = escape_html(text("ReadingTopic")) . " ($rcnt): ";
    
    foreach ($topic_readers as $ouid => $uinfo) {
        $appendix = "";
        if ($uinfo["time_ago"] != text("Now")) {
            $appendix = "<span class='last_visit_info'>&nbsp;" . escape_html($uinfo["time_ago"]) . "</span>";
        }
        
        if (!empty($uinfo["id"])) {
            $treaders .= "<span style='white-space: nowrap'><a href='view_profile.php?uid=$uinfo[id]'>" . escape_html($uinfo["name"]) . "</a>$appendix</span>, ";
        } elseif (!empty($uinfo["bot"])) {
            $treaders .= "<span style='white-space: nowrap'><a class='bot_link' href='view_bot_profile.php?bot=" . xrawurlencode($uinfo["name"]) . "'>" . escape_html($uinfo["name"]) . "</a>$appendix</span>, ";
        } elseif($ouid == "g_#anonyms#") {
            $treaders .= "<span style='white-space: nowrap'><i>" . escape_html($uinfo["name"]) . "</i>$appendix</span>, ";
        } elseif ($uinfo["name"] == "admin") {
            $treaders .= "<span style='white-space: nowrap'><a class='admin_link' href='view_guest_profile.php?guest=" . xrawurlencode($uinfo["name"]) . "'>" . escape_html(text("MasterAdministrator")) . "</a>$appendix</span>, ";
        } else {
            $treaders .= "<span style='white-space: nowrap'><a class='guest_link' href='view_guest_profile.php?guest=" . xrawurlencode($uinfo["name"]) . "'>" . escape_html($uinfo["name"]) . "</a>$appendix</span>, ";
        }
    }
    
    $treaders = trim($treaders, ", ");
    
    $rcnt = count($forum_readers);
    if(!empty($forum_readers["g_#anonyms#"]["count"])) $rcnt += ($forum_readers["g_#anonyms#"]["count"] - 1);
    
    $freaders = escape_html(text("ReadingForum")) . " ($rcnt): ";
    
    foreach ($forum_readers as $ouid => $uinfo) {
        $appendix = "";
        if ($uinfo["time_ago"] != text("Now")) {
            $appendix = "<span class='last_visit_info'>&nbsp;" . escape_html($uinfo["time_ago"]) . "</span>";
        }
        
        if (!empty($uinfo["id"])) {
            $freaders .= "<span style='white-space: nowrap'><a href='view_profile.php?uid=$uinfo[id]'>" . escape_html($uinfo["name"]) . "</a>$appendix</span>, ";
        } elseif (!empty($uinfo["bot"])) {
            $freaders .= "<span style='white-space: nowrap'><a class='bot_link' href='view_bot_profile.php?bot=" . xrawurlencode($uinfo["name"]) . "'>" . escape_html($uinfo["name"]) . "</a>$appendix</span>, ";
        } elseif($ouid == "g_#anonyms#") {
            $freaders .= "<span style='white-space: nowrap'><i>" . escape_html($uinfo["name"]) . "</i>$appendix</span>, ";
        } elseif ($uinfo["name"] == "admin") {
            $freaders .= "<span style='white-space: nowrap'><a class='admin_link' href='view_guest_profile.php?guest=" . xrawurlencode($uinfo["name"]) . "'>" . escape_html(text("MasterAdministrator")) . "</a>$appendix</span>, ";
        } else {
            $freaders .= "<span style='white-space: nowrap'><a class='guest_link' href='view_guest_profile.php?guest=" . xrawurlencode($uinfo["name"]) . "'>" . escape_html($uinfo["name"]) . "</a>$appendix</span>, ";
        }
    }
    
    $freaders = trim($freaders, ", ");
}

$tignorers = "";
$rcnt = count($topic_ignorers);
if($rcnt > 0)
{
  $tignorers = escape_html(text("IgnoringTopic")) . " ($rcnt): ";

  foreach($topic_ignorers as $iuid => $uinfo)
  {
      $online_status = "";
      if(empty($settings["hide_online_status"]) && !empty($uinfo["online"]))
      {
        $online_status = "&nbsp;<span class='online_text'>✓</span>";
      }

      $active_ignorer = "";
      if (empty($uinfo["auto_ignored"])) {
          $active_ignorer = "class='active_ignorer'";
      }

      $tignorers .= "<a $active_ignorer href='view_profile.php?uid=$iuid' >" . escape_html($uinfo["name"]) . "</a>$online_status, ";
  }

  $tignorers = trim($tignorers, ", ");
}

$tblocked = "";
$rcnt = count($topic_blocked_users);
if($rcnt > 0)
{
  $tblocked = escape_html(text("BlockedInTopic")) . " ($rcnt): ";

  foreach($topic_blocked_users as $iuid => $uinfo)
  {
      $online_status = "";
      if(empty($settings["hide_online_status"]) && !empty($uinfo["online"]))
      {
          $online_status = "&nbsp;<span class='online_text'>✓</span>";
      }
      
      $active_ignorer = "";
      if (empty($uinfo["auto_ignored"])) {
          $active_ignorer = "class='active_ignorer'";
      }

      $tblocked .= "<span class='user_name'><a $active_ignorer href='view_profile.php?uid=$iuid' >" . escape_html($uinfo["name"]) . "</a>$online_status</span>, ";
  }

  $tblocked = trim($tblocked, ", ");
}

require $view_path . "topic_online_users_inc.php";

require_once "../include/final_inc.php";
?>
