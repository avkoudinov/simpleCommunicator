<script>
var config = {
  format: "<?php echo_js(text("DateFormat")); ?>",
  start_year: 2000,
  month_names: [
    "<?php echo_js(text("January")); ?>",
    "<?php echo_js(text("February")); ?>",
    "<?php echo_js(text("March")); ?>",
    "<?php echo_js(text("April")); ?>",
    "<?php echo_js(text("May")); ?>",
    "<?php echo_js(text("June")); ?>",
    "<?php echo_js(text("July")); ?>",
    "<?php echo_js(text("August")); ?>",
    "<?php echo_js(text("September")); ?>",
    "<?php echo_js(text("October")); ?>",
    "<?php echo_js(text("November")); ?>",
    "<?php echo_js(text("December")); ?>"
  ],
  
  weekday_names: [
    "<?php echo_js(text("MondayShort")); ?>",
    "<?php echo_js(text("TuesdayShort")); ?>",
    "<?php echo_js(text("WednesdayShort")); ?>",
    "<?php echo_js(text("ThursdayShort")); ?>",
    "<?php echo_js(text("FridayShort")); ?>",
    "<?php echo_js(text("SaturdayShort")); ?>",
    "<?php echo_js(text("SundayShort")); ?>"
  ]
};

Forum.addXEvent(window, 'load', function () {
  SimpleCalendar.assign("#start_date", config);
  SimpleCalendar.assign("#end_date", config);
});

var current_displayed_event_id_info = null;
function toggle_id_info_actions(evid)
{
  elm = document.getElementById("event_id_info_" + evid);
  if(!elm) return false;

  var need_show = (elm.style.display == "none");

  if(current_displayed_event_id_info)
  {
    current_displayed_event_id_info.style.display = "none";
    current_displayed_event_id_info = null;
  }

  if(need_show)
  {
    elm.style.display = "block";
    if(document.getElementById("evid_link_" + evid)) focus_field("evid_link_" + evid);
    
    current_displayed_event_id_info = elm;
  }

  return false;
}

function user_esc_handler()
{
  var elm = document.getElementById("moderator_name_lookup");
  if(elm)
  {
    elm.parentNode.style.display = "none";

    for(var i = elm.length - 1; i >= 0 ; i--)
    {
      elm.options[i] = null;
    }
  }

  elm = document.getElementById("user_name_lookup");
  if(elm)
  {
    elm.parentNode.style.display = "none";

    for(var i = elm.length - 1; i >= 0 ; i--)
    {
      elm.options[i] = null;
    }
  }
  
  elm = document.getElementsByClassName("event_id_info_actions");
  for(var i = elm.length - 1; i >= 0; i--)
  {
    elm[i].style.display = "none";
  }
}
</script>

<!-- BEGIN: header2 -->

<div class="header2 moderation_log_filter">

<form action="moderation_log.php" method="get" onsubmit="Forum.show_sys_progress_indicator(true);">
<input type="hidden" name="apply_filter" value="1">

<table>
<tr>
<td>
<?php echo_html(text("Moderator")); ?> / <?php echo_html(text("Author")); ?>:
</td>
<td>
  <input type="text" class="filter_field" id="moderator_name" name="moderator_name" autocomplete="off" value="<?php echo_html(val_or_empty($_SESSION["moderator_log_filter"]["moderator_name"])); ?>" onkeypress="return lookup_handle_enter(this.id, event)" onkeyup="return lookup_entries('search_authors', this, event)" onblur="lookup_delayed_hide(this.id)">
  <div style="position: relative">
  <div class="field_lookup_area" style="display:none">
  <select id="moderator_name_lookup" size="10" 
           onclick="if(!mustAdjustMultiSelect()) { lookup_apply_selection('moderator_name') }" 
           onchange="if(mustAdjustMultiSelect()) { lookup_apply_selection_if_active('moderator_name') }" 

           onkeypress="return lookup_handle_enter('moderator_name', event)" onblur="user_esc_handler()"
           >
  </select>
  </div>
  </div>
</td>
</tr>
<tr>
<td>
<?php echo_html(text("Action")); ?>:
</td>
<td>
<select name="action_name" id="action_name" class="filter_field" onchange="Forum.show_sys_progress_indicator(true); this.form.submit();">
<option value="">-</option>
<?php foreach($action_list as $aid => $aname):
$selected = (val_or_empty($_SESSION["moderator_log_filter"]["action_name"]) == $aid) ? "selected" : "";
?>
<option value="<?php echo_html($aid); ?>" <?php echo($selected); ?>><?php echo_html($aname); ?></option>
<?php endforeach; ?>
</select>
</td>
</tr>
<tr>
<td>
<?php echo_html(text("Author") . " / " . text("IPAddress")); ?>:
</td>
<td>
  <input type="text" class="filter_field" id="user_name" name="user_name" autocomplete="off" value="<?php echo_html(val_or_empty($_SESSION["moderator_log_filter"]["user_name"])); ?>" onkeypress="return lookup_handle_enter(this.id, event)" onkeyup="return lookup_entries('search_authors', this, event)" onblur="lookup_delayed_hide(this.id)">
  <div style="position: relative">
  <div class="field_lookup_area" style="display:none">
  <select id="user_name_lookup" size="10" 
           onclick="if(!mustAdjustMultiSelect()) { lookup_apply_selection('user_name') }" 
           onchange="if(mustAdjustMultiSelect()) { lookup_apply_selection_if_active('user_name') }" 

           onkeypress="return lookup_handle_enter('user_name', event)" onblur="user_esc_handler()"
           >
  </select>
  </div>
  </div>
</td>
</tr>
<tr>
<td>
<?php echo_html(text("Topic")); ?>:
</td>
<td>
<input type="text" class="filter_field" id="topic_name" name="topic_name" value="<?php echo_html(val_or_empty($_SESSION["moderator_log_filter"]["topic_name"])); ?>">
</td>
</tr>
<tr>
<td>
<?php echo_html(text("Forum")); ?>:
</td>
<td>
<select name="forum" id="forum" class="filter_field" onchange="Forum.show_sys_progress_indicator(true); this.form.submit();">
<option value="">-</option>

<?php if($fmanager->is_logged_in() && !$fmanager->is_master_admin()): ?>
<?php
$selected = (val_or_empty($_SESSION["moderator_log_filter"]["forum"]) == "private") ? "selected" : "";
?>
<option value="private" <?php echo($selected); ?>><?php echo_html(text("PrivateTopics")); ?></option>
<?php endif; ?>

<?php foreach($forum_list as $fid => $fdata):
$selected = (val_or_empty($_SESSION["moderator_log_filter"]["forum"]) == $fid) ? "selected" : "";
?>
<option value="<?php echo_html($fid); ?>" <?php echo($selected); ?>><?php echo_html($fdata["name"]); ?></option>
<?php endforeach; ?>


</select>
</td>
</tr>
<tr>
<td>
<?php echo_html(text("DateRange")); ?>:
</td>
<td>
  <table class="date_block">
      <tr>
      <td><input type="text" class="filter_field" autocomplete="off" id="start_date" name="start_date" value="<?php echo_html(val_or_empty($_SESSION["moderator_log_filter"]["start_date"])); ?>"></td>
      <td style="text-align:center; width: 10px"></td>
      <td><input type="text" class="filter_field" autocomplete="off" id="end_date" name="end_date" value="<?php echo_html(val_or_empty($_SESSION["moderator_log_filter"]["end_date"])); ?>"></td>
      </tr>
  </table>
</td>
</tr>
<tr>
<td colspan="2" style="text-align: center">
<input type="submit" class="standard_button" value="<?php echo_html(text("Search")); ?>">
<input type="submit" class="standard_button" value="<?php echo_html(text("Reset")); ?>" onclick="this.form.elements['apply_filter'].value='-1'">
</td>
</tr>
</table>

</form>

</div>

<!-- END: header2 -->

<!-- BEGIN: forum_bar -->

<div class="forum_bar">
<a href="forums.php"><?php echo_html(text("Forums")); ?></a>

<?php
$display = "style='display:none'";
if(!empty($topics_with_new_count)) $display = "";
?>
<span class="new topics_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span>

<?php if(!$fmanager->is_logged_in() && !empty($_SESSION["ip_blocked"])): ?>
<span class="closed">[<?php echo_html(empty($_SESSION["ip_block_time_left"]) ? text("ip_blocked") : sprintf(text("ip_blocked_until"), $_SESSION["ip_block_time_left"])); ?>]</span>
<?php elseif($fmanager->is_logged_in() && empty($_SESSION["activated"])): ?>
<span class="closed">[<?php echo_html(text("notActivated")); ?>]</span>
<?php elseif($fmanager->is_logged_in() && empty($_SESSION["approved"])): ?>
<span class="closed">[<?php echo_html(text("notApproved")); ?>]</span>
<?php elseif(!empty($_SESSION["blocked"])): 
$self_blocked_class = "";
if(val_or_empty($_SESSION["self_blocked"]) == 1) $self_blocked_class = "self_blocked";
elseif(val_or_empty($_SESSION["self_blocked"]) == 2) $self_blocked_class = "author_dead";
?>
<span class="closed <?php echo($self_blocked_class); ?>">[<?php echo_html(empty($_SESSION["block_time_left"]) ? text("blocked") : sprintf(text("blocked_until"), $_SESSION["block_time_left"])); ?>]</span>
<?php endif; ?>

/ <span class="topic_title_main"><?php echo_html(text("ModeratorLog")); ?></span>,

<?php echo(build_page_info($pagination_info, text("pages"))); ?>
</div>

<?php if($pagination_info["page_count"] > 1): ?>
<div class="forum_bar">
<div class="navigator_bar"><?php echo(build_page_navigator($pagination_info["base_url_pagination"], $pagination_info)); ?></div>
<div class="clear_both">
</div>
</div>
<?php endif; ?>

<!-- END: forum_bar -->

<table class="moderator_log_table">
<tr>
<th><?php echo_html(text("Action")); ?></th>
</tr>

<?php if(count($event_list) == 0): ?>

<tr>
<td class="table_message"><?php echo_html(text("NoEvents")); ?></td>
</tr>

<?php else: ?>

<?php foreach($event_list as $evid => $evinfo): ?>

<tr>
<td>
<div style="position: relative">
  <div class="copy_event_button" onclick="toggle_id_info_actions('<?php echo_html($evid); ?>')">
  </div>

  <div id="event_id_info_<?php echo_html($evid); ?>" class="event_id_info_actions" style="display:none">
  <div style="position: absolute;right:2px;top:2px;cursor:pointer" onclick="toggle_id_info_actions('<?php echo_html($evid); ?>')"><img src="<?php echo($view_path); ?>images/cross.png" alt="<?php echo_html(text("Close")); ?>"></div>
  
      <div class="inner_label"><?php echo_html(text("Link")); ?>:</div>
        <table class="aux_table">
        <tr>
        <td><input type="text" id="evid_link_<?php echo_html($evid); ?>" value="<?php echo_html(get_host_address() . get_url_path() . "moderation_log.php?event=$evid"); ?>" onfocus="select_text_in_field('evid_link_<?php echo_html($evid); ?>')"></td>
        <td>&nbsp;</td>
        <td><input type="button" class="standard_button" value="&nbsp;" onclick="focus_field('evid_link_<?php echo_html($evid); ?>')" title="<?php echo_html(text("MarkForCopy")); ?>"></td>
        </tr>
        </table>

      <div class="inner_label"><?php echo_html(text("LinkToEvent")); ?>:</div>
        <table class="aux_table">
        <tr>
        <td><input type="text" id="evid_levt_<?php echo_html($evid); ?>" value="[mevt=<?php echo_html($evid); ?>]" onfocus="select_text_in_field('evid_levt_<?php echo_html($evid); ?>')"></td>
        <td>&nbsp;</td>
        <td><input type="button" class="standard_button" value="&nbsp;" onclick="focus_field('evid_levt_<?php echo_html($evid); ?>')" title="<?php echo_html(text("MarkForCopy")); ?>"></td>
        </tr>
        </table>
  
  </div>
</div>

<div class="event_action">
<?php
$action = $evinfo["action"];
if($action == "block_user" && !empty($evinfo["action_expires"])) $action = "block_user_until";
if($action == "block_user_forum" && !empty($evinfo["action_expires"])) $action = "block_user_forum_until";
if($action == "block_ip" && !empty($evinfo["action_expires"])) $action = "block_ip_until";
if($action == "block_user_marker" && !empty($evinfo["action_expires"])) $action = "block_user_marker_until";

echo_html(str_ireplace("{time}", $evinfo["action_expires"], ForumManager::get_action_txt($action)));
?>
</div>

<?php if(!empty($evinfo["comment"])): ?>
<div class="moderator_warning"><?php echo($evinfo["comment"]); ?></div>
<?php endif; ?>

<div class="forum_info">
<?php echo_html(text("DateTime")); ?>: <span class="number"><?php echo_html($evinfo["event_time"]); ?></span>

<?php
$moderator = "";
if(!empty($evinfo["moderator_name"]))
{
  if(!empty($evinfo["moderator_id"])) 
    $moderator = "<a href='view_profile.php?uid=" . $evinfo["moderator_id"] . "' >" . escape_html($evinfo["moderator_name"]) . "</a>";
  elseif($evinfo["moderator_name"] == "#system#")
    $moderator = escape_html(text("System"));
  elseif($evinfo["moderator_name"] == "admin")
    $moderator = "<a class='admin_link' href='view_guest_profile.php?guest=" . xrawurlencode($evinfo["moderator_name"]) . "'>" . escape_html(text("MasterAdministrator")) . "</a>";
  else
    $moderator = "<a class='guest_link' href='view_guest_profile.php?guest=" . xrawurlencode($evinfo["moderator_name"]) . "'>" . escape_html($evinfo["moderator_name"]) . "</a>";

  if(empty($settings["hide_online_status"]) && (!empty($evinfo["moderator_online"]) || !empty($online_users["g_" . $evinfo["moderator_name"]])))
  {
    $moderator .= "&nbsp;<span class='online_text'>✓</span>";
  }
}

if(($evinfo["action"] != "post_liked" && $evinfo["action"] != "like_revoked" && $evinfo["action"] != "post_disliked" && $evinfo["action"] != "dislike_revoked") &&
   (!$fmanager->is_moderator_log_visible() || (val_or_empty($settings["moderator_log"]) == "all_names_hidden" && !$fmanager->is_moderator())))
{
  $moderator = "";
}

if(!empty($settings["dislikes_anonym"]) && ($evinfo["action"] == "post_disliked" || $evinfo["action"] == "dislike_revoked"))
{
  $moderator = "";
}
?>

<?php if(!empty($moderator)): ?>
<br><?php echo_html(text("Moderator")); ?> / <?php echo_html(text("Author")); ?>: <?php echo($moderator); ?>
<?php endif; ?>

<?php
$author = "";
if(!empty($evinfo["author_name"]))
{
  if(!empty($evinfo["author_id"])) 
    $author = "<a href='view_profile.php?uid=" . $evinfo["author_id"] . "' >" . escape_html($evinfo["author_name"]) . "</a>";
  elseif($evinfo["author_name"] == "admin")
    $author = "<a class='admin_link' href='view_guest_profile.php?guest=" . xrawurlencode($evinfo["author_name"]) . "'>" . escape_html(text("MasterAdministrator")) . "</a>";
  else
    $author = "<a class='guest_link' href='view_guest_profile.php?guest=" . xrawurlencode($evinfo["author_name"]) . "'>" . escape_html($evinfo["author_name"]) . "</a>";
  
  if(empty($settings["hide_online_status"]) && (!empty($evinfo["author_online"]) || !empty($online_users["g_" . $evinfo["author_name"]])))
  {
    $author .= "&nbsp;<span class='online_text'>✓</span>";
  }
}

if($evinfo["action"] == "block_ip" || $evinfo["action"] == "unblock_ip")
{
  if(!empty($author)) $author .= ", ";

  if(($fmanager->is_moderator() && $fmanager->may_see_ip()) || $evinfo["ip"] == System::getIPAddress())
  {
    if(!empty($settings["whois_server"]))
    {
      $url = str_ireplace("{ip}", $evinfo["ip"], $settings["whois_server"]);
      $author .= "<a href='$url' target='_blank'>" . escape_html($evinfo["ip"]) . "</a>";
    }
    
    $ip_sign = "✘";
    $ip_class = "ip_moderation";
    if(!empty($evinfo["ip_blocked"]))
    {
      $ip_sign = "✘";
      $ip_class = "ip_moderation ip_blocked";
    }
    $author .= "&nbsp;<a href='ip_moderation.php?type=moderation&ip=" . xrawurlencode($evinfo["ip"]) . "' class='moderator_link $ip_class' title='" . escape_html(text("Moderate")) . "'>$ip_sign</a>";
  }
  else
  {
    $author .= '<span class="number">' . escape_html(preg_replace("/([0-9]+\\.)+([^\\.]+)/", "xx.xx.xx.$2", $evinfo["ip"])) . "<span>";
  }
}
elseif($evinfo["action"] == "block_user_marker" || $evinfo["action"] == "unblock_user_marker")
{
  if(!empty($author)) $author .= ", ";

  if(!($fmanager->is_moderator() && $fmanager->may_see_ip()))
  {
    $author .= '<span class="number">' . escape_html(substr($evinfo["ip"], 0, 4) . "xxxxxxxx" . substr($evinfo["ip"], -4)) . "</span>";
  }
  else
  {
    $ip_sign = "✘";
    $ip_class = "ip_moderation";
    if(!empty($evinfo["ip_blocked"]))
    {
      $ip_sign = "✘";
      $ip_class = "ip_moderation ip_blocked";
    }
    $author .= '<span class="number">' . escape_html($evinfo["ip"]) . "</span>&nbsp;<a href='ip_moderation.php?type=um_moderation&ip=" . xrawurlencode($evinfo["ip"]) . "' class='moderator_link $ip_class' title='" . escape_html(text("Moderate")) . "'>$ip_sign</a>";
  }
}
?>

<?php if(!empty($author)): ?>
<br><?php echo_html(text("Author") . " / " . text("IPAddress")); ?>: <?php echo($author); ?>
<?php endif; ?>

<?php
$topic = escape_html(postprocess_message($evinfo["topic_name"]));
if(!empty($topic) && !empty($evinfo["topic_id"]) && !empty($evinfo["topic_id"]))
{
  $post_appx = "";
  if(!empty($evinfo["post_id"])) 
  {
    $post_appx = " [<a href='topic.php?fid=" . $evinfo["forum_id"] . "&tid=" . $evinfo["topic_id"] . "&msg=" . $evinfo["post_id"] . "' >#" . $evinfo["post_id"] . "</a>]";
  }
  
  $topic = "<a href='topic.php?fid=" . $evinfo["forum_id"] . "&tid=" . $evinfo["topic_id"] . "&gotonew=1' rel='nofollow' >" . $topic . "</a>" . $post_appx;
}
?>

<?php if(!empty($topic)): ?>
<br><?php echo_html(text("Topic")); ?>: <?php echo($topic); ?>
<?php endif; ?>

<?php
$forum = escape_html($evinfo["forum_name"]);
if(!empty($forum) && !empty($evinfo["forum_id"]))
{
  $not_preferred = "";
  if(!empty($_SESSION["ignored_forums"][$evinfo["forum_id"]])) $not_preferred = "not_preferred";
  $forum = "<a href='forum.php?fid=" . $evinfo["forum_id"] . "' class='$not_preferred'>" . $forum . "</a>";
}
?>

<?php if(!empty($forum)): ?>
<br><?php echo_html(text("Forum")); ?>: <?php echo($forum); ?>
<?php endif; ?>
</div>

  <div class="navigation_arrows_right">
  <div class="scroll_up" onclick="window.scrollTo(0, 0);"></div>
  <div class="scroll_down" onclick="window.scrollTo(0, 1000000);"></div>
  </div>

<?php endforeach; ?>

<?php endif; ?>

</table>

<?php if(count($event_list) > 2): ?>

<!-- BEGIN: forum_bar -->
<?php if($pagination_info["page_count"] > 1): ?>
<div class="forum_bar">
<div class="navigator_bar"><?php echo(build_page_navigator($pagination_info["base_url_pagination"], $pagination_info)); ?></div>
<div class="clear_both">
</div>
</div>
<?php endif; ?>

<div class="forum_bar">
<a href="forums.php"><?php echo_html(text("Forums")); ?></a>

<?php
$display = "style='display:none'";
if(!empty($topics_with_new_count)) $display = "";
?>
<span class="new topics_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span>

<?php if(!$fmanager->is_logged_in() && !empty($_SESSION["ip_blocked"])): ?>
<span class="closed">[<?php echo_html(empty($_SESSION["ip_block_time_left"]) ? text("ip_blocked") : sprintf(text("ip_blocked_until"), $_SESSION["ip_block_time_left"])); ?>]</span>
<?php elseif($fmanager->is_logged_in() && empty($_SESSION["activated"])): ?>
<span class="closed">[<?php echo_html(text("notActivated")); ?>]</span>
<?php elseif($fmanager->is_logged_in() && empty($_SESSION["approved"])): ?>
<span class="closed">[<?php echo_html(text("notApproved")); ?>]</span>
<?php elseif(!empty($_SESSION["blocked"])): 
$self_blocked_class = "";
if(val_or_empty($_SESSION["self_blocked"]) == 1) $self_blocked_class = "self_blocked";
elseif(val_or_empty($_SESSION["self_blocked"]) == 2) $self_blocked_class = "author_dead";
?>
<span class="closed <?php echo($self_blocked_class); ?>">[<?php echo_html(empty($_SESSION["block_time_left"]) ? text("blocked") : sprintf(text("blocked_until"), $_SESSION["block_time_left"])); ?>]</span>
<?php endif; ?>

/ <span class="topic_title_main"><?php echo_html(text("ModeratorLog")); ?></span>,

<?php echo(build_page_info($pagination_info, text("pages"))); ?>
</div>

<!-- END: forum_bar -->

<?php endif; ?>

<?php
@include "online_users_inc.php";
?>
