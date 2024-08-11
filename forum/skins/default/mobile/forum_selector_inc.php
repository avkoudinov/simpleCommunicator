<table class="forum_selector_table" border=1>
<tr>
<td>
<input type="text" class="search_field" id="forum_selector<?php echo($forum_selector_id); ?>" autocomplete="off" placeholder="<?php echo_html(text("GotoForum")); ?>" onkeypress="return forum_lookup_handle_enter(this.id, event)" onkeyup="return filter_entries(this, event)" onfocus="reset_forum_selector(this.id); show_lookup_list(this.id);" onblur="lookup_delayed_hide(this.id); this.value = '';">

<div class="field_lookup_area field_lookup_area<?php echo($forum_selector_id); ?>" style="display:none">
<select id="forum_selector<?php echo($forum_selector_id); ?>_lookup" size="15"
   onclick="if(!mustAdjustMultiSelect()) { lookup_goto_forum('forum_selector<?php echo($forum_selector_id); ?>'); }" 
   onchange="if(mustAdjustMultiSelect()) { lookup_goto_forum_if_active('forum_selector<?php echo($forum_selector_id); ?>'); }" 

   onkeypress="return forum_lookup_handle_enter('forum_selector<?php echo($forum_selector_id); ?>', event)" onblur="lookup_delayed_hide('forum_selector<?php echo($forum_selector_id); ?>');"
>
<option value="new_messages.php"><?php echo_html(text("NewMessages")); ?></option>

<?php if($fmanager->is_logged_in() && !$fmanager->is_master_admin()): ?>
<option value="forum.php?fid=private"><?php echo_html(text("PrivateTopics")); ?></option>
<option value="favourites.php"><?php echo_html(text("Favourites")); ?></option>
<option value="events.php"><?php echo_html(text("Events")); ?></option>
<?php endif; ?>

<?php foreach($forum_list as $sfid => $fdata): 
if(!empty($_SESSION["hide_ignored"]) && 
   !empty($_SESSION["ignored_forums"][$sfid]) &&
   !$fmanager->is_forum_moderator($sfid)) continue;
?>
<option value="forum.php?fid=<?php echo_html($sfid); ?>"><?php echo_html($fdata["name"]); ?></option>
<?php endforeach; ?>
</select>
</div>
</td>
<td>
<div class="forum_selector_container">
<select class='forum_selector' onchange='if(this.value) document.location.href=this.value'>
<option value=""><?php echo_html(text("GotoForum")); ?></option>
<option value="new_messages.php"><?php echo_html(text("NewMessages")); ?></option>

<?php if($fmanager->is_logged_in() && !$fmanager->is_master_admin()): ?>
<option value="forum.php?fid=private"><?php echo_html(text("PrivateTopics")); ?></option>
<option value="favourites.php"><?php echo_html(text("Favourites")); ?></option>
<option value="events.php"><?php echo_html(text("Events")); ?></option>
<?php endif; ?>

<?php foreach($forum_list as $sfid => $fdata): 
if(!empty($_SESSION["hide_ignored"]) && 
   !empty($_SESSION["ignored_forums"][$sfid]) &&
   !$fmanager->is_forum_moderator($sfid)) continue;
?>
<option value="forum.php?fid=<?php echo_html($sfid); ?>"><?php echo_html($fdata["name"]); ?></option>
<?php endforeach; ?>
</select>
</div>
</td>
</tr>
</table>