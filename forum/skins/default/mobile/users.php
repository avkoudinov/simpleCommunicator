<script src='skins/<?php echo($skin); ?>/js/field_lookup.js<?php echo($cache_appendix); ?>'></script>

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

<!-- BEGIN: header2 -->

<div class="header2">

<form action="users.php" method="get">
<table class="aux_table" style="width:100%">
<tr>
<td style="position: relative">
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
<td style="width:1%; white-space: nowrap">
<input type="submit" class="standard_button search_button" value="<?php echo_html(text("DoSearch")); ?>"><?php if(!reqvar_empty("user_name")): ?><input type="submit" class="standard_button search_button" value="<?php echo_html(text("Reset")); ?>" onclick="this.form.elements['user_name'].value=''"><?php endif; ?>
</td>
</tr>
</table>
</form>

</div>

<!-- END: header2 -->

<!-- BEGIN: header2 -->

<div class="header2">
<select class='sort_selector' onchange='if(this.value) document.location.href=this.value'>

<option value="users.php?sort=new_members" <?php echo(reqvar("sort") == "new_members" ? "selected" : ""); ?>><?php echo_html(text("Sort")); ?>: <?php echo_html(text("NewUsers")); ?></option>

<?php if(empty($settings["hide_online_status"])): ?>
<option value="users.php?sort=last_activity" <?php echo(reqvar("sort") == "last_activity" ? "selected" : ""); ?>><?php echo_html(text("Sort")); ?>: <?php echo_html(text("LastActivity")); ?></option>
<?php endif; ?>

<option value="users.php?sort=blocked_users" <?php echo(reqvar("sort") == "blocked_users" ? "selected" : ""); ?>><?php echo_html(text("Sort")); ?>: <?php echo_html(text("BlockedUsers")); ?></option>
<option value="users.php?sort=left_users" <?php echo(reqvar("sort") == "left_users" ? "selected" : ""); ?>><?php echo_html(text("Sort")); ?>: <?php echo_html(text("LeftUsers")); ?></option>
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

<!-- END: header2 -->

<!-- BEGIN: forum_bar -->

<div class="forum_bar">
<a href="forums.php"><?php echo_html(text("Forums")); ?></a>

<?php
$display = "style='display:none'";
if(!empty($topics_with_new_count)) $display = "";
?>
<span class="new topics_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span> 

/ <a href="users.php"><?php echo_html(text("Users")); ?></a> /
<?php echo_html($fmanager->get_user_sort_name()); ?>: <span class='count_number'><?php echo_html(format_number(val_or_empty($pagination_info["total_count"]))); ?></span>,

<?php echo(build_page_info($pagination_info, text("pages"))); ?>
</div>

<?php if($pagination_info["page_count"] > 1): ?>
<div class="forum_bar">
<div class="navigator_bar"><?php echo(build_page_navigator("users.php?upage=$", $pagination_info)); ?></div>
<div class="clear_both">
</div>
</div>
<?php endif; ?>

<!-- END: forum_bar -->

<table class="user_table">
<tr>
<th></th>
<th><?php echo_html(text("User")); ?></th>

</tr>

<?php if(count($user_list) == 0): ?>

<tr>
<td colspan="2" class="table_message"><?php echo_html(text("UsersNotFound")); ?></td>
</tr>

<?php else: ?>

<?php
$cnt = 1 + ($pagination_info["page"] - 1)*$pagination_info["rows_per_page"];
foreach($user_list as $uid => $user_data):
?>

<tr>
<td><?php echo_html($cnt++); ?></td>
<td>
  <?php
  $online_status = "";
  if(empty($settings["hide_online_status"]) && !empty($user_data["online"]))
  {
    $online_status = "&nbsp;<span class='online_text'>✓</span>";
  }
  ?>
  <div class="smart_break">
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

  <div class="forum_info">
  <?php echo_html(text("RegistrationDate")); ?>: <span class="number"><?php echo_html($user_data["registration_date"]); ?></span><br>

  <?php if(empty($settings["hide_online_status"])): ?>
  <?php echo_html(text("LastActivity")); ?>: <span class="number"><?php echo_html($user_data["last_visit_date"]); ?></span><br>
  <?php endif; ?>

  <?php echo_html(text("Messages")); ?>: <span class="number"><?php echo_html(format_number($user_data["post_count"])); ?></span><br>
  <?php echo_html(text("Topics")); ?>: <span class="number"><?php echo_html(format_number($user_data["topic_count"])); ?></span><br>
  <?php echo_html(text("MessagesPerDay")); ?>: <span class="number"><?php echo_html(format_number($user_data["week_post_count"])); ?></span> <span style="color: gray" class="field_comment">(<?php echo_html(text("OverLastWeek")); ?>)</span><br>
  <?php echo_html(text("HitsPerDay")); ?>: <span class="number"><?php echo_html(format_number($user_data["week_view_count"])); ?></span> <span style="color: gray" class="field_comment">(<?php echo_html(text("OverLastWeek")); ?>)</span><br>
  <?php echo_html(text("TimeOnlinePerDay")); ?>: <span class="number"><?php echo_html(format_duration($user_data["week_time_online"])); ?></span> <span style="color: gray" class="field_comment">(<?php echo_html(text("OverLastWeek")); ?>)</span><br>
  <?php echo_html(text("TimeOnlineTotal")); ?>: <span class="number"><?php echo_html(format_duration($user_data["time_online"])); ?></span> 

  <?php if(!empty($settings["rates_active"])): ?>
  <br><?php echo_html(text("Rating")); ?>: <a href="search_topic.php?do_search=1&post_sort=desc&author_mode=author_liked&author=<?php echo(xrawurlencode($user_data["user_name"])); ?>"  class="carma_plus"><?php echo_html($user_data["carma_plus"]); ?></a>
  <?php if(!empty($settings["dislikes_active"])): ?>
  / <a href="search_topic.php?do_search=1&post_sort=desc&author_mode=author_disliked&author=<?php echo(xrawurlencode($user_data["user_name"])); ?>" class="carma_minus"><?php echo_html($user_data["carma_minus"]); ?></a>
  <?php endif; ?>
  
  <br><?php echo_html(text("Weighed")); ?>: <a href="search_topic.php?do_search=1&post_sort=desc&author_mode=author_liked&author=<?php echo(xrawurlencode($user_data["user_name"])); ?>"  class="carma_plus"><?php echo_html(format_number($user_data["carma_plus_weighed"], 1)); ?></a>
  <?php if(!empty($settings["dislikes_active"])): ?>
  / <a href="search_topic.php?do_search=1&post_sort=desc&author_mode=author_disliked&author=<?php echo(xrawurlencode($user_data["user_name"])); ?>" class="carma_minus"><?php echo_html(format_number($user_data["carma_minus_weighed"], 1)); ?></a>
  <?php endif; ?>
  <?php endif; ?>
  </div>

<?php if($fmanager->is_moderator()): ?>
  <div class="forum_info">
   <?php echo_html($fmanager->is_admin() ? text("Administrator") : text("Moderator")); ?>:
    <a href="user_moderation.php?uid=<?php echo_html($uid); ?>" class="moderator_link"><?php echo_html(text("Moderate")); ?></a>

    <?php if($fmanager->is_admin()): ?>
    | <a href="edit_user.php?uid=<?php echo_html($uid); ?>" class="moderator_link"><?php echo_html(text("Edit")); ?></a>
    <?php endif; ?>
  </div>
<?php endif; ?>

  <div class="navigation_arrows_right">
  <div class="scroll_up" onclick="window.scrollTo(0, 0);"></div>
  <div class="scroll_down" onclick="window.scrollTo(0, 1000000);"></div>
  </div>

</td>

</tr>

<?php endforeach; ?>

<?php endif; ?>

</table>

<?php if(count($user_list) > 2): ?>

<!-- BEGIN: forum_bar -->

<div class="forum_bar">

<?php if($pagination_info["page_count"] > 1): ?>
<div class="forum_bar">
<div class="navigator_bar"><?php echo(build_page_navigator("users.php?upage=$", $pagination_info)); ?></div>
<div class="clear_both">
</div>
</div>
<?php endif; ?>

<a href="forums.php"><?php echo_html(text("Forums")); ?></a>

<?php
$display = "style='display:none'";
if(!empty($topics_with_new_count)) $display = "";
?>
<span class="new topics_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span> 

/ <a href="users.php"><?php echo_html(text("Users")); ?></a> /
<?php echo_html($fmanager->get_user_sort_name()); ?>: <span class='count_number'><?php echo_html(format_number(val_or_empty($pagination_info["total_count"]))); ?></span>,

<?php echo(build_page_info($pagination_info, text("pages"))); ?>
</div>

<!-- END: forum_bar -->

<?php endif; ?>

