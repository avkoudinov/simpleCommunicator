
<?php if($installed): ?>

<!-- BEGIN: header2 -->

<div class="header2" id="<?php echo($main_menu_id); ?>">

<?php 
$search_appendix = "";
if(!empty($is_private))
{
  $search_appendix = "&" . xrawurlencode("forums[]") . "=private";
}
elseif(basename($_SERVER["PHP_SELF"]) == "favourites.php" || !reqvar_empty("favourites_only") || !reqvar_empty("favourite_posts") || !reqvar_empty("favourite_posts_only"))
{
  $search_appendix = "&favourites_only=1";
}
elseif(reqvar("fid") == "my_topics" || 
       ($fmanager->is_logged_in() && reqvar("author") == $fmanager->get_user_name() && reqvar("author_mode") == "created_topic")
) {
  $search_appendix = "&author=" . xrawurlencode($fmanager->get_user_name()) . "&author_mode=created_topic&new_search=1";
}
elseif(reqvar("fid") == "my_part_topics" || 
       ($fmanager->is_logged_in() && reqvar("author") == $fmanager->get_user_name() && reqvar("author_mode") == "participating")
) {
  $search_appendix = "&author=" . xrawurlencode($fmanager->get_user_name()) . "&author_mode=participating&new_search=1";
}
elseif((basename($_SERVER["PHP_SELF"]) == "forum.php" || basename($_SERVER["PHP_SELF"]) == "topic.php" || basename($_SERVER["PHP_SELF"]) == "new_topic.php") && !reqvar_empty("fid"))
{
  $search_appendix = "&" . xrawurlencode("forums[]") . "=" . reqvar("fid");
}
?>

<?php if($fmanager->is_logged_in()): ?>

<div class="member_info_bar">
<?php
if($fmanager->get_user_name() == "admin")
  $member_link = "<a class='member_nick' href='view_guest_profile.php?guest=" . xrawurlencode($fmanager->get_user_name()) . "'>" . escape_html($fmanager->get_status_user_name()) . "</a>";
else
  $member_link = "<a class='member_nick' href='view_profile.php?uid=" . $fmanager->get_user_id() . "'>" . escape_html($fmanager->get_status_user_name()) . "</a>";

echo($member_link); 
?>
</div>

<div class="member_action_bar">
<a href="logout.php?hash=<?php echo_html($_SESSION["hash"]); ?>" onclick="return confirm_logout()"><?php echo_html(text("Logout")); ?></a>  

  <?php 
  if(!$fmanager->is_master_admin()): 
  ?>
  | <a href="profile.php"><?php echo_html(text("Profile")); ?></a> 
  <?php
  $display = "style='display:none'";
  if(!empty($private_topics_with_new_count)) $display = "";
  ?>
  | <a href="forum.php?fid=private"><?php echo_html(text("PrivateTopicsMiddle")); ?></a><span class="new private_topics_with_new_indicator" <?php echo($display); ?>>&nbsp;[<a rel="nofollow" href="new_messages.php?fid=private"><?php echo_html(text("new")); ?>:<span class='private_topics_with_new_count'><?php echo($private_topics_with_new_count); ?></span></a>]</span>
  
     <?php if(empty($_SESSION["turnoff_events"])): 
     $display = "style='display:none'";
     if(!empty($new_events_count)) $display = "";
     ?>
     | <a href="events.php"><?php echo_html(text("Events")); ?></a><span class="new new_events_indicator" <?php echo($display); ?>>&nbsp;[<a href="events.php?event_type=new_events"><?php echo_html(text("new")); ?>:<span class='new_events_count'><?php echo($new_events_count); ?></span></a>]</span>
     <?php endif; ?>
  
  <?php else: ?>
  | <a href="guest_profile.php"><?php echo_html(text("Profile")); ?></a> 
  | <a href="password_change.php"><?php echo_html(text("PasswordChange")); ?></a> 
  <?php endif; ?>

</div>

<div class="member_action_bar">

  <?php
  $display = "style='display:none'";
  if(!empty($topics_with_new_count)) $display = "";
  ?>
  <a rel="nofollow" href="new_messages.php"><?php echo_html(text("NewMessagesMiddle")); ?></a><span class="new topics_with_new_indicator" <?php echo($display); ?>>&nbsp;[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span> 
  
  <span class="topics_with_new_indicator" <?php echo($display); ?>>| <a rel="nofollow" href="search.php?do_search=1&news_digest=1"><?php echo_html(text("Digest")); ?></a></span>

  | <a href="search.php?do_search=1&hot_topics=1"><?php echo_html(text("HotTopicsMiddle")); ?></a>

  <?php
  $display = "style='display:none'";
  if(!empty($favourites_with_new_count)) $display = "";
  ?>
  | <a href="favourites.php"><?php echo_html(text("Favourites")); ?></a><span class="new favourites_with_new_indicator" <?php echo($display); ?>>&nbsp;[<a rel="nofollow" href="new_messages.php?fid=favourites"><?php echo_html(text("new")); ?>:<span class='favourites_with_new_count'><?php echo($favourites_with_new_count); ?></span></a>]</span>
  
  <?php if(!$fmanager->is_master_admin()): ?>

  <?php if(!empty($_SESSION["subscribed_authors"])): ?>
  <?php
  $display = "style='display:none'";
  if(!empty($subscription_authors_new_messages_count)) $display = "";
  ?>
  | <a href="subscription.php"><?php echo_html(text("Subscription")); ?></a><span class="new subscription_new_indicator" <?php echo($display); ?>>&nbsp;[<a href="search.php?do_search=1&author=<?php echo(xrawurlencode(text("Subscription"))); ?>&author_mode=wrote_post&post_list=1&post_sort=desc&unseen=1&mark_read=1"><?php echo_html(text("new")); ?>:<span class='subscription_authors_new_messages_count'><?php echo($subscription_authors_new_messages_count); ?></span></a>]</span>
  <?php endif; ?>
  
  <?php
  $display = "style='display:none'";
  if(!empty($my_topics_with_new_count)) $display = "";
  ?>
  | <a href="search.php?do_search=1&author=<?php echo_html(xrawurlencode($fmanager->get_user_name())); ?>&author_mode=created_topic"><?php echo_html(text("MyTopics")); ?></a><span class="new my_topics_with_new_indicator" <?php echo($display); ?>>&nbsp;[<a rel="nofollow" href="new_messages.php?fid=my_topics"><?php echo_html(text("new")); ?>:<span class='my_topics_with_new_count'><?php echo($my_topics_with_new_count); ?></span></a>]</span>
  
  / <a href="search.php?do_search=1&author=<?php echo_html(xrawurlencode($fmanager->get_user_name())); ?>&author_mode=last_posts"><?php echo_html(text("MyMessagesShort")); ?></a>

  <?php if(!empty($_SESSION["skin_properties"][$skin]["show_my_part_topics"])): ?>
  <?php
  $display = "style='display:none'";
  if(!empty($my_part_topics_with_new_count)) $display = "";
  ?>
  | <a href="search.php?do_search=1&author=<?php echo_html(xrawurlencode($fmanager->get_user_name())); ?>&author_mode=participating"><?php echo_html(text("ParticipatedTopicsMiddle")); ?></a><span class="new my_part_topics_with_new_indicator" <?php echo($display); ?>>&nbsp;[<a rel="nofollow" href="new_messages.php?fid=my_part_topics"><?php echo_html(text("new")); ?>:<span class='my_part_topics_with_new_count'><?php echo($my_part_topics_with_new_count); ?></span></a>]</span>
  <?php endif; ?>
  
  <?php endif; // not master ?>
  
</div>

<?php else: // logged or not ?>

<div class="member_info_bar">
<?php
if($fmanager->get_user_name() != "")
{
  $aname_appendix = "";
  if (!$fmanager->is_master_admin()) {
      $aname_appendix = "&aname=" . System::generateHash($READ_MARKER . $fmanager->get_user_name(), SALT_KEY);
  }
  
  $guest_name = "<a class='guest_link' href='view_guest_profile.php?guest=" . xrawurlencode($fmanager->get_user_name()) . $aname_appendix . "'>" . escape_html($fmanager->get_status_user_name()) . "</a>";
}
else
{
  $guest_name = "<span class='guest_nick'>" . escape_html($fmanager->get_status_user_name()) . "</span>";
}

echo($guest_name); 
?>
</div>

<div class="member_action_bar">
<a href="login.php"><?php echo_html(text("Login")); ?></a> | 
<a href="registration.php"><?php echo_html(text("Registration")); ?></a> |
<a href="guest_profile.php"><?php echo_html(text("Profile")); ?></a> |
<a href="#" onclick="return confirm_clear_profile_data()"><?php echo_html(text("ClearData")); ?></a> 
</div>

<div class="member_action_bar">
<?php
$display = "style='display:none'";
if(!empty($topics_with_new_count)) $display = "";
?>
<a rel="nofollow" href="new_messages.php"><?php echo_html(text("NewMessages")); ?></a><span class="new topics_with_new_indicator" <?php echo($display); ?>>&nbsp;[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span>  

<span class="topics_with_new_indicator" <?php echo($display); ?>>| <a rel="nofollow" href="search.php?news_digest=1&do_search=1"><?php echo_html(text("Digest")); ?></a></span>

<?php
$display = "style='display:none'";
if(!empty($favourites_with_new_count)) $display = "";
?>
| <a href="favourites.php"><?php echo_html(text("Favourites")); ?></a><span class="new favourites_with_new_indicator" <?php echo($display); ?>>&nbsp;[<a rel="nofollow" href="new_messages.php?fid=favourites"><?php echo_html(text("new")); ?>:<span class='favourites_with_new_count'><?php echo($favourites_with_new_count); ?></span></a>]</span>

    <?php if($fmanager->get_user_name() != ""): ?>
    | <a href="search.php?do_search=1&author=<?php echo_html(xrawurlencode($fmanager->get_user_name())); ?>&author_mode=created_topic"><?php echo_html(text("MyTopics")); ?></a>

    / <a href="search.php?do_search=1&author=<?php echo_html(xrawurlencode($fmanager->get_user_name())); ?>&author_mode=last_posts"><?php echo_html(text("MyMessagesShort")); ?></a>
    <?php endif; ?>
</div>
  
<?php endif; // logged or not ?>

<div class="member_action_bar">

<a href="forums.php"><?php echo_html(text("Forums")); ?></a> | 
<a href="users.php"><?php echo_html(text("Users")); ?></a> | 
<a href="statistics.php"><?php echo_html(text("Statistics")); ?></a> | 

  <?php if($fmanager->is_moderator_log_visible()): ?>

  <?php 
  $display = "style='display:none'";
  if(empty($_SESSION["turnoff_events"]) && !empty($new_mod_events_count)) $display = "";
  ?>
  <a href="moderation_log.php"><?php echo_html(text("ModeratorLog")); ?></a><span class="new new_mod_events_indicator" <?php echo($display); ?>>&nbsp;[<a href="events.php?event_type=unprocessed_mod_events"><?php echo_html(text("new")); ?>:<span class='new_mod_events_count'><?php echo($new_mod_events_count); ?></span></a>]</span> | 
  <?php endif; ?>

<a href="search.php?with_morphology=1<?php echo($search_appendix); ?>"><?php echo_html(text("Search")); ?></a>

</div>

<?php if($fmanager->is_moderator() || $fmanager->is_admin()): ?>
  <div class="member_action_bar">

  <?php if($fmanager->is_moderator()): ?>
  <a href="moderation.php"><?php echo_html(text("Moderation")); ?></a>  
  <?php endif; ?>

  <?php if($fmanager->is_admin()): ?>
  | <a href="settings.php"><?php echo_html(text("Settings")); ?></a> 
  <?php endif; ?>

  </div>
<?php endif; // logged or not ?>

<div class="search_bar">

<form action="search.php" method="get" onsubmit="Forum.show_sys_progress_indicator(true);">
<input type="hidden" name="do_search" value="1">
<input type="hidden" name="quick_search" value="1">

<?php if(!empty($is_private)): ?>
<input type="hidden" name="forums[]" value="private">
<?php elseif(basename($_SERVER["PHP_SELF"]) == "favourites.php" || !reqvar_empty("favourites_only")): ?>
<input type="hidden" name="favourites_only" value="1">
<?php elseif(!reqvar_empty("favourite_posts") || !reqvar_empty("favourite_posts_only")): ?>
<input type="hidden" name="favourite_posts_only" value="1">
<?php elseif(reqvar("fid") == "my_topics" || 
       ($fmanager->is_logged_in() && reqvar("author") == $fmanager->get_user_name() && reqvar("author_mode") == "created_topic")): ?>
<input type="hidden" name="author" value="<?php echo_html($fmanager->get_user_name()); ?>">
<input type="hidden" name="author_mode" value="created_topic">
<?php elseif(reqvar("fid") == "my_part_topics" || 
       ($fmanager->is_logged_in() && reqvar("author") == $fmanager->get_user_name() && reqvar("author_mode") == "participating")): ?>
<input type="hidden" name="author" value="<?php echo_html($fmanager->get_user_name()); ?>">
<input type="hidden" name="author_mode" value="participating">
<?php elseif((basename($_SERVER["PHP_SELF"]) == "forum.php" || basename($_SERVER["PHP_SELF"]) == "new_topic.php" || basename($_SERVER["PHP_SELF"]) == "topic.php") && !reqvar_empty("fid")): ?>
<input type="hidden" name="forums[]" value="<?php echo_html(reqvar("fid")); ?>">
<?php endif; ?>

<?php if(!reqvar_empty("tid")): ?>
<input type="hidden" name="tid" value="<?php echo_html(reqvar("tid")); ?>">
<?php endif; ?>

<table class="aux_table">
<tr>
<td>
<input type="text" class="search_field" name="search_keys">
<input type="hidden" name="with_morphology" value="1">
</td>
<td>
<input type="submit" class="standard_button search_button" value="<?php echo_html(text("DoSearch")); ?>">
</td>
</tr>
</table>
</form>
</div>

<div class="clear_both">
</div>

</div>

<!-- END: header2 -->

<?php endif; // if installed ?>
