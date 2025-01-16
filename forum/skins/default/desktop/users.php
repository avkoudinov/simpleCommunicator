<script>
function user_esc_handler()
{
  var elm = document.getElementById("user_name_lookup");
  if(elm)
  {
    elm.parentNode.style.display = "none";
    
    for(var i = elm.length - 1; i >= 0 ; i--)
    {
      elm.options[i] = null;
    }
  }
}
</script>

<!-- BEGIN: header3 -->

<div class="header3">

<div class="user_search_bar">
<form action="users.php" method="get">
<table class="aux_table">
<tr>
<td>
<input type="text" class="search_field" id="user_name" name="user_name" autocomplete="off" placeholder="<?php echo_html(text("SearchUser")); ?>" value="<?php echo_html(reqvar("user_name")); ?>" onkeypress="return lookup_handle_enter(this.id, event)" onkeyup="return lookup_entries('search_users', this, event)" onblur="lookup_delayed_hide(this.id)">
  <div class="field_lookup_area" style="display:none">
  <select id="user_name_lookup" size="10" 
           onclick="if(!mustAdjustMultiSelect()) { lookup_apply_selection('user_name') }" 
           onchange="if(mustAdjustMultiSelect()) { lookup_apply_selection_if_active('user_name') }" 

           onkeypress="return lookup_handle_enter('user_name', event)" onblur="user_esc_handler()"
           >
  </select>
  </div>
</td>
<td>
<input type="submit" class="standard_button search_button" value="<?php echo_html(text("DoSearch")); ?>"><?php if(!reqvar_empty("user_name")): ?><input type="submit" class="standard_button search_button" value="<?php echo_html(text("Reset")); ?>" onclick="this.form.elements['user_name'].value=''"><?php endif; ?>
</td>
</tr>
</table>
</form>
</div>


<div class="right_action_panel">
<select class='sort_selector' onchange='if(this.value) document.location.href=this.value'>

<option value="users.php?sort=new_members" <?php echo(reqvar("sort") == "new_members" ? "selected" : ""); ?>><?php echo_html(text("Sort")); ?>: <?php echo_html(text("NewUsers")); ?></option>

<?php if(empty($settings["hide_online_status"])): ?>
<option value="users.php?sort=last_activity" <?php echo(reqvar("sort") == "last_activity" ? "selected" : ""); ?>><?php echo_html(text("Sort")); ?>: <?php echo_html(text("LastActivity")); ?></option>
<?php endif; ?>

<option value="users.php?sort=blocked_users" <?php echo(reqvar("sort") == "blocked_users" ? "selected" : ""); ?>><?php echo_html(text("Sort")); ?>: <?php echo_html(text("BlockedUsers")); ?></option>
<option value="users.php?sort=not_activated_users" <?php echo(reqvar("sort") == "not_activated_users" ? "selected" : ""); ?>><?php echo_html(text("Sort")); ?>: <?php echo_html(text("NotActivatedUsers")); ?></option>
<option value="users.php?sort=left_users" <?php echo(reqvar("sort") == "left_users" ? "selected" : ""); ?>><?php echo_html(text("Sort")); ?>: <?php echo_html(text("LeftUsers")); ?></option>
<option value="users.php?sort=privileged_users" <?php echo(reqvar("sort") == "privileged_users" ? "selected" : ""); ?>><?php echo_html(text("Sort")); ?>: <?php echo_html(text("PrivilegedMembers")); ?></option>
<option value="users.php?sort=moderators" <?php echo(reqvar("sort") == "moderators" ? "selected" : ""); ?>><?php echo_html(text("Sort")); ?>: <?php echo_html(text("ForumModerators")); ?></option>
<option value="users.php?sort=administrators" <?php echo(reqvar("sort") == "administrators" ? "selected" : ""); ?>><?php echo_html(text("Sort")); ?>: <?php echo_html(text("Administrators")); ?></option>

<option value="users.php?sort=max_count" <?php echo(reqvar("sort") == "max_count" ? "selected" : ""); ?>><?php echo_html(text("Sort")); ?>: <?php echo_html(text("MaxMessagesCount")); ?></option>
<option value="users.php?sort=active_writers" <?php echo(reqvar("sort") == "active_writers" ? "selected" : ""); ?>><?php echo_html(text("Sort")); ?>: <?php echo_html(text("ActiveWriters")); ?></option>
<option value="users.php?sort=active_readers" <?php echo(reqvar("sort") == "active_readers" ? "selected" : ""); ?>><?php echo_html(text("Sort")); ?>: <?php echo_html(text("ActiveReaders")); ?></option>
<option value="users.php?sort=regulars" <?php echo(reqvar("sort") == "regulars" ? "selected" : ""); ?>><?php echo_html(text("Sort")); ?>: <?php echo_html(text("Regulars")); ?></option>

<?php if(!empty($settings["rates_active"])): ?>
<option value="users.php?sort=best_rating" <?php echo(reqvar("sort") == "best_rating" ? "selected" : ""); ?>><?php echo_html(text("Sort")); ?>: <?php echo_html(text("BestRating")); ?></option>
<option value="users.php?sort=best_rating_weighed" <?php echo(reqvar("sort") == "best_rating_weighed" ? "selected" : ""); ?>><?php echo_html(text("Sort")); ?>: <?php echo_html(text("BestRating")); ?> (<?php echo_html(text("Weighed")); ?>)</option>
<?php if(!empty($settings["dislikes_active"])): ?>
<option value="users.php?sort=worst_rating" <?php echo(reqvar("sort") == "worst_rating" ? "selected" : ""); ?>><?php echo_html(text("Sort")); ?>: <?php echo_html(text("WorstRating")); ?></option>
<option value="users.php?sort=worst_rating_weighed" <?php echo(reqvar("sort") == "worst_rating_weighed" ? "selected" : ""); ?>><?php echo_html(text("Sort")); ?>: <?php echo_html(text("WorstRating")); ?> (<?php echo_html(text("Weighed")); ?>)</option>
<?php endif; ?>
<?php endif; ?>

</select>
</div>

<div class="clear_both">
</div>


</div>

<!-- END: header3 -->

<div class="content_area">

<!-- BEGIN: forum_bar -->

<div class="forum_bar">

<div class="forum_name_bar"><a href="forums.php"><?php echo_html(text("Forums")); ?></a>

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

/ <a href="users.php"><?php echo_html(text("Users")); ?></a> /
<?php echo_html($fmanager->get_user_sort_name()); ?>: <span class='count_number'><?php echo_html(format_number(val_or_empty($pagination_info["total_count"]))); ?></span>,

<?php echo(build_page_info($pagination_info, text("pages"))); ?>
</div>

<?php 
if($pagination_info["page_count"] > 1): 
$url = "users.php?upage=$";
if (!reqvar_empty("sort")) $url .= "&sort=" . urlencode(reqvar("sort"));
if (!reqvar_empty("user_name")) $url .= "&user_name=" . urlencode(reqvar("user_name"));
?>
<div class="navigator_bar"><?php echo(build_page_navigator($url, $pagination_info)); ?></div>
<?php endif; ?>

<div class="forum_action_bar">
<table>
<tr>
<td>
<?php
$forum_selector_id = 1;
@include "forum_selector_inc.php";
?>
</td>
</tr>
</table>
</div>

<div class="clear_both">
</div>

</div>

<!-- END: forum_bar -->

<?php
if(empty($settings["hide_online_status"]))
{
  $activity_display = "";
}
else
{
  $activity_display = "style='display:none'";
}

$cols_cnt = 12;
if($fmanager->is_moderator()) $cols_cnt = 13;
if(empty($settings["rates_active"])) $cols_cnt--;
?>

<table class="user_table">
<tr>
<th></th>
<th><?php echo_html(text("User")); ?></th>
<th><?php echo_html(text("RegistrationDate")); ?></th>
<th <?php echo($activity_display); ?>><?php echo_html(text("LastActivity")); ?></th>
<th><?php echo_html(text("Messages")); ?></th>
<th><?php echo_html(text("Topics")); ?></th>
<th><?php echo_html(text("MessagesPerDay")); ?></th>
<th><?php echo_html(text("HitsPerDay")); ?></th>
<th><?php echo_html(text("TimeOnlinePerDay")); ?></th>
<th><?php echo_html(text("TimeOnlineTotal")); ?></th>
<?php if(!empty($settings["rates_active"])): ?>
<th><?php echo_html(text("Rating")); ?></th>
<th><?php echo_html(text("Weighed")); ?></th>
<?php endif; ?>

<?php if($fmanager->is_moderator()): ?>
<th class="admin_actions"><?php echo_html($fmanager->is_admin() ? text("Administrator") : text("Moderator")); ?></th>
<?php endif; ?>

</tr>

<?php if(count($user_list) == 0): ?>

<tr>
<td colspan="<?php echo($cols_cnt); ?>" class="table_message"><?php echo_html(text("UsersNotFound")); ?></td>
</tr>

<?php else: ?>

<?php
$cnt = 1 + ($pagination_info["page"] - 1)*$pagination_info["rows_per_page"];
foreach($user_list as $uid => $user_data):
?>

<tr>
<td><?php echo_html($cnt++); ?></td>
<td>

<div class="smart_break">
  <?php
  $online_status = "";
  if(empty($settings["hide_online_status"]) && !empty($user_data["online"]))
  {
    $online_status = "&nbsp;<span class='online_text'>✓</span>";
  }

  if(!empty($user_data["privileged"]))
  {
    $online_status .= "<img class='privileged_user' src='" . $view_path . "images/privilege.png' alt='" . escape_html(text("PrivilegedMember")) . "' title='" . escape_html(text("PrivilegedMember")) . "'>";
  }
  ?>

  <a href="view_profile.php?uid=<?php echo_html($uid); ?>"><?php echo_html($user_data["user_name"]); ?></a><?php echo($online_status); ?>

  <?php if(empty($user_data["activated"])): ?>
  <span class="error_text">[<?php echo_html(text("notActivated")); ?>]</span>
  <?php endif; ?>

  <?php if(empty($user_data["approved"])): ?>
  <span class="error_text">[<?php echo_html(text("notApproved")); ?>]</span>
  <?php endif; ?>

  <?php if(!empty($user_data["hidden"])): ?>
  <span class="error_text">[<?php echo_html(text("hidden")); ?>]</span>
  <?php endif; ?>

  <?php if(!empty($user_data["blocked"])): 
  $class = "";
  $death_sign = "";
  if(val_or_empty($user_data["self_blocked"]) == 1) $class = "self_blocked";
  elseif(val_or_empty($user_data["self_blocked"]) == 2) 
  {
    $class = "author_dead";
    $death_sign = "&nbsp;†";
  }
  ?>
  <span class="error_text <?php echo($class); ?>">[<?php echo_html(empty($user_data["block_time_left"]) ? text("blocked") : sprintf(text("blocked_until"), $user_data["block_time_left"])); ?><?php echo($death_sign); ?>]</span>
  <?php endif; ?>
</div>

</td>

<td><?php echo_html($user_data["registration_date"]); ?></td>

<?php
if(!empty($settings["hide_online_status"]))
{
  $user_data["last_visit_date"] = "";
}
?>
<td <?php echo($activity_display); ?>><?php echo_html($user_data["last_visit_date"]); ?></td>

<td><?php echo_html(format_number($user_data["post_count"])); ?></td>
<td><?php echo_html(format_number($user_data["topic_count"])); ?></td>
<td><?php echo_html(format_number($user_data["week_post_count"])); ?></td>
<td><?php echo_html(format_number($user_data["week_view_count"])); ?></td>
<td><?php echo_html(format_duration($user_data["week_time_online"])); ?></td>
<td><?php echo_html(format_duration($user_data["time_online"])); ?></td>

<?php if(!empty($settings["rates_active"])): ?>
<td>
<a href="search_topic.php?do_search=1&post_sort=desc&author_mode=author_liked&author=<?php echo(xrawurlencode($user_data["user_name"])); ?>" class="carma_plus" ><?php echo_html($user_data["carma_plus"]); ?></a>
<?php if(!empty($settings["dislikes_active"])): ?>
/ <a href="search_topic.php?do_search=1&post_sort=desc&author_mode=author_disliked&author=<?php echo(xrawurlencode($user_data["user_name"])); ?>" class="carma_minus" ><?php echo_html($user_data["carma_minus"]); ?></a>
<?php endif; ?>
</td>
<td>
<a href="search_topic.php?do_search=1&post_sort=desc&author_mode=author_liked&author=<?php echo(xrawurlencode($user_data["user_name"])); ?>" class="carma_plus" ><?php echo_html(format_number($user_data["carma_plus_weighed"], 1)); ?></a>
<?php if(!empty($settings["dislikes_active"])): ?>
/ <a href="search_topic.php?do_search=1&post_sort=desc&author_mode=author_disliked&author=<?php echo(xrawurlencode($user_data["user_name"])); ?>" class="carma_minus" ><?php echo_html(format_number($user_data["carma_minus_weighed"], 1)); ?></a>
<?php endif; ?>
</td>
<?php endif; ?>

<?php if($fmanager->is_moderator()): ?>
<td class="admin_actions">
<a href="user_moderation.php?uid=<?php echo_html($uid); ?>" class="moderator_link"><?php echo_html(text("Moderate")); ?></a>

<?php if($fmanager->is_admin()): ?>
| <a href="edit_user.php?uid=<?php echo_html($uid); ?>" class="moderator_link"><?php echo_html(text("Edit")); ?></a>
<?php endif; ?>
</td>
<?php endif; ?>

</tr>

<?php endforeach; ?>

<?php endif; ?>

</table>

<?php if(count($user_list) > 25): ?>

<!-- BEGIN: forum_bar -->

<div class="forum_bar">

<div class="forum_name_bar"><a href="forums.php"><?php echo_html(text("Forums")); ?></a>

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

/ <a href="users.php"><?php echo_html(text("Users")); ?></a> /
<?php echo_html($fmanager->get_user_sort_name()); ?>: <span class='count_number'><?php echo_html(format_number(val_or_empty($pagination_info["total_count"]))); ?></span>,

<?php echo(build_page_info($pagination_info, text("pages"))); ?>
</div>

<?php if($pagination_info["page_count"] > 1): ?>
<div class="navigator_bar"><?php echo(build_page_navigator("users.php?upage=$", $pagination_info)); ?></div>
<?php endif; ?>

<div class="forum_action_bar">
<table>
<tr>
<td>
<?php
$forum_selector_id = 2;
@include "forum_selector_inc.php";
?>
</td>
</tr>
</table>
</div>

<div class="clear_both">
</div>

</div>

<!-- END: forum_bar -->

<?php endif; ?>

</div>