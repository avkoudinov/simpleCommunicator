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
   !empty($_SESSION["preferred_forums"]) && 
   empty($_SESSION["preferred_forums"][$sfid]) &&
   !$fmanager->is_forum_moderator($sfid)) continue;
?>
<option value="forum.php?fid=<?php echo_html($sfid); ?>"><?php echo_html($fdata["name"]); ?></option>
<?php endforeach; ?>
</select>
