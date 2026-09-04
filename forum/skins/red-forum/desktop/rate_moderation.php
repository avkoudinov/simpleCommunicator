<script>
var action_ajax = null;

function do_action()
{
  var form = document.getElementById('main_form');
  if(!form) return false;

  Forum.show_sys_progress_indicator(true);
  document.location.href = "#moderation";

  if(!action_ajax)
  {
    action_ajax = new Forum.AJAX();

    action_ajax.timeout = TIMEOUT;

    action_ajax.beforestart = function() { break_check_new_messages(); };
    action_ajax.aftercomplete = function(error) { activate_check_new_messages(); };

    action_ajax.onload = function(text, xml)
    {
      try
      {
        var response = JSON.parse(text);

        Forum.handle_response_messages(response);

        if(response.success)
        {
          delay_reload();
          return;
        }
      }
      catch(err)
      {
        Forum.handle_ajax_error(this, err.message, this.last_url, {});
      }
      
      Forum.show_sys_progress_indicator(false);
    };

    action_ajax.onerror = function(error, url, info)
    {
      Forum.show_sys_progress_indicator(false);

      Forum.handle_ajax_error(this, error, url, info);
    };
  }

  action_ajax.abort();
  action_ajax.resetParams();

  var formData = new FormData(form);

  formData.append('hash', get_protection_hash());
  formData.append('user_logged', user_logged);  
  formData.append('delete_user_rates', "1");

  action_ajax.setFormData(formData);

  action_ajax.request("ajax/process.php");

  return false;
} // do_action
</script>

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

/ <a href="moderation.php"><?php echo_html(text("Moderation")); ?></a> / <span class="topic_title_main"><?php echo_html(text("ModerateRates")); ?></span>

</div>

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

<form action="rate_moderation.php" id="main_form" enctype="multipart/form-data" method="post" onsubmit="return do_action();">

<input type="hidden" id="uid" name="uid" value="<?php echo_html($user_data["id"]); ?>">

<table class="form_table profile_table">

<tr>
<th colspan="2"><?php echo_html(text("ModerateRates")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("UserName")); ?>:</td>
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
<td><span class="number"><?php echo_html($user_data["user_name"]); ?></span><?php echo($online_status); ?></td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<tr>
<td></td>
<td>

<?php
$rnd = rand(1000, 9000);
$picture = $view_path . "images/guest.jpg";
if(!empty($user_data["avatar"]))
{
  $appendix = "?rnd=$rnd";
  if(!empty($user_data["avatar_ctime"])) $appendix = "?ctime=" . $user_data["avatar_ctime"];

  $picture = escape_html($user_data["avatar"]) . $appendix;
}
?>

<img class="avatar_picture" src="<?php echo($picture); ?>" alt="<?php echo_html(text("Avatar")); ?>"><?php if(val_or_empty($user_data["self_blocked"]) == 2): ?><img class="mourning_band" src="<?php echo($view_path . "images/mourning_band.png"); ?>" alt="<?php echo_html(text("Avatar")); ?>"><?php endif; ?>

</td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<tr>
<td><?php echo_html(text("Status")); ?>:</td>
<td>
  <?php if(!empty($user_data["is_admin"])): ?>

    <?php echo_html(text("Administrator")); ?><br>

  <?php elseif(!empty($user_data["moderator"])): ?>

    <div class='moderator_of_forums'><?php echo_html(text("ModeratorOfForums")); ?>:</div>
    <?php foreach($user_data["moderator"] as $fid => $fname):
    $not_preferred = "";
    if(!empty($_SESSION["ignored_forums"][$fid])) $not_preferred = "not_preferred";
    ?>
    <a href="forum.php?fid=<?php echo_html($fid); ?>" class="<?php echo($not_preferred); ?>"><?php echo_html($fname); ?></a><br>
    <?php endforeach; ?>

  <?php else: ?>

    <?php echo_html(text("Member")); ?><br>

  <?php endif; ?>

  <?php if(!empty($user_data["my_profile"])): ?>
  <br><span class="error_text self_blocked"><?php echo_html(text("MyProfile")); ?></span>
  <?php endif; ?>

  <?php if(empty($user_data["activated"])): ?>
  <br><span class="error_text"><?php echo_html(text("NotActivated")); ?></span>
  <?php endif; ?>

  <?php if(empty($user_data["approved"])): ?>
  <br><span class="error_text"><?php echo_html(text("NotApproved")); ?></span>
  <?php endif; ?>

  <?php if(!empty($user_data["hidden"])): ?>
  <br><span class="error_text"><?php echo_html(text("Hidden")); ?></span>
  <?php endif; ?>

  <?php if(empty($user_data["hidden"]) && !empty($user_data["hidden_by_me"])): ?>
  <br><span class="error_text"><?php echo_html(text("HiddenByMe")); ?></span>
  <?php endif; ?>
  
  <?php if(!empty($user_data["hiding_me"])): ?>
  <br><span class="error_text"><?php echo_html(text("HidingMe")); ?></span>
  <?php endif; ?>  
  
  <?php if(!empty($user_data["ignored"])): ?>
  <br><span class="error_text"><?php echo_html(text("Ignored")); ?></span>

    <?php if(!empty($user_data["ignored_comment"])): ?> 
    <div class="ignore_reason">
    <?php echo($user_data["ignored_comment"]); ?>
    </div>
    <?php endif; ?>
  <?php endif; ?>

  <?php if(!$fmanager->is_logged_in() && !empty($user_data["ignores_all_guests"])): ?>
  <br><span class="error_text"><?php echo_html(text("IgnoringGuests")); ?></span>
  <?php elseif(val_or_empty($user_data["ignoring_me"]) == 1): ?>
  <br><span class="error_text"><?php echo_html(text("IgnoringMe")); ?></span>
  <?php elseif(val_or_empty($user_data["ignoring_me"]) == 2): ?>
  <br><span class="error_text"><?php echo_html(text("IgnoringGuestsExcept")); ?></span>
  <?php elseif(val_or_empty($user_data["ignoring_me"]) == 3): ?>
  <br><span class="error_text"><?php echo_html(text("IgnoringNewGuests")); ?></span>
  <?php endif; ?>
  
  <?php 
  $separator = "";
  if(!empty($user_data["blocked"])): 
  $separator = "<br>";
  $class = "";
  $death_sign = "";
  if(val_or_empty($user_data["self_blocked"]) == 1) $class = "self_blocked";
  elseif(val_or_empty($user_data["self_blocked"]) == 2) 
  {
    $class = "author_dead";
    $death_sign = "&nbsp;†";
  }
  ?>
  
  <br><span class="error_text <?php echo($class); ?>"><?php echo_html(empty($user_data["block_expires"]) ? text("Blocked") : sprintf(text("BlockedUntil"), $user_data["block_expires"])); ?><?php echo($death_sign); ?></span>
    <?php if(!empty($user_data["block_time_left"])): ?>
    <span style="color:gray">[<?php echo_html($user_data["block_time_left"]); ?>]</span>
    <?php endif; ?>

  <?php endif; ?>

  <?php if(!empty($user_data["forum_blocked"])): ?>
  <?php echo($separator); ?>
  <br><?php echo_html(text("ForumBlocking")); ?>:<br>
  <?php foreach($user_data["forum_blocked"] as $fid => $forum_data): 
    $not_preferred = "";
    if(!empty($_SESSION["ignored_forums"][$fid]) && $forum_data["fid_for_url"] != "private") $not_preferred = "not_preferred";
    ?>
    <br><a href="forum.php?fid=<?php echo_html($forum_data["fid_for_url"]); ?>" class="<?php echo($not_preferred); ?>"><?php echo_html($forum_data["name"]); ?></a>
  
    <br><span class="error_text"><?php echo_html(empty($forum_data["block_expires"]) ? text("Blocked") : sprintf(text("BlockedUntil"), $forum_data["block_expires"])); ?></span>
        <?php if(!empty($forum_data["block_time_left"])): ?>
        <span style="color:gray">[<?php echo_html($forum_data["block_time_left"]); ?>]</span>
        <?php endif; ?>
        
  <?php endforeach; ?>
  <?php endif; ?>

</td>
</tr>


<?php if (!empty($user_data["forum_access"])): ?>

<tr>
<td colspan="2"></td>
</tr>

<tr>
<td><?php echo_html(text("AccessToRestrictedForums")); ?>:</td>
<td>

<?php foreach($user_data["forum_access"] as $fid => $fname):
$not_preferred = "";
if(!empty($_SESSION["ignored_forums"][$fid])) $not_preferred = "not_preferred";
?>
<a href="forum.php?fid=<?php echo_html($fid); ?>" class="<?php echo($not_preferred); ?>"><?php echo_html($fname); ?></a><br>
<?php endforeach; ?>

</td>
</tr>

<?php endif; ?>


<tr>
<td colspan="2"></td>
</tr>

<tr>
<td><?php echo_html(text("RegistrationDate")); ?>:</td>
<td><span class="number"><?php echo_html(smart_date($user_data["registration_date"])); ?></span></td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<?php if(empty($settings["hide_online_status"])): ?>
<tr>
<td><?php echo_html(text("LastActivity")); ?>:</td>
<td><span class="number"><?php echo_html($user_data["last_visit_date"]); ?></span></td>
</tr>

<tr>
<td colspan="2"></td>
</tr>
<?php endif; ?>

<tr>
<td><?php echo_html(text("MessagesCount")); ?>:</td>
<td><span class="number"><?php echo_html(format_number($user_data["post_count"])); ?></span></td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<tr>
<td><?php echo_html(text("TopicsCount")); ?>:</td>
<td><span class="number"><?php echo_html(format_number($user_data["topic_count"])); ?></span></td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<tr>
<td><?php echo_html(text("MessagesPerDay")); ?>:</td>
<td>
<span class="number"><?php echo_html(format_number($user_data["week_post_count"])); ?></span>
 <span style="color: gray" class="field_comment">(<?php echo_html(text("OverLastWeek")); ?>)</span>
</td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<tr>
<td><?php echo_html(text("HitsPerDay")); ?>:</td>
<td>
<span class="number"><?php echo_html(format_number($user_data["week_view_count"])); ?></span>
 <span style="color: gray" class="field_comment">(<?php echo_html(text("OverLastWeek")); ?>)</span>
</td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<tr>
<td><?php echo_html(text("TimeOnlinePerDay")); ?>:</td>
<td>
<span class="number"><?php echo_html(format_duration($user_data["week_time_online"])); ?></span>
 <span style="color: gray" class="field_comment">(<?php echo_html(text("OverLastWeek")); ?>)</span>
</td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<tr>
<td><?php echo_html(text("TimeOnlineLast24Hours")); ?>:</td>
<td>
<span class="number"><?php echo_html(format_duration($user_data["today_time_online"])); ?></span>
</td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<tr>
<td><?php echo_html(text("TimeOnlineTotal")); ?>:</td>
<td>
<span class="number"><?php echo_html(format_duration($user_data["time_online"])); ?></span>
</td>
</tr>

<?php if(!empty($settings["rates_active"])): ?>
<tr>
<td colspan="2"></td>
</tr>

<tr>
<td><?php echo_html(text("Rating")); ?>:</td>
<td>
<a href="search_topic.php?do_search=1&post_sort=desc&author_mode=author_liked&author=<?php echo(xrawurlencode($user_data["user_name"])); ?>" class="carma_plus" ><?php echo_html($user_data["carma_plus"]); ?></a>
<?php if(!empty($settings["dislikes_active"])): ?>
/ <a href="search_topic.php?do_search=1&post_sort=desc&author_mode=author_disliked&author=<?php echo(xrawurlencode($user_data["user_name"])); ?>" class="carma_minus" ><?php echo_html($user_data["carma_minus"]); ?></a>
<?php endif; ?>
</td>
</tr>

<tr>
<td><?php echo_html(text("Weighed")); ?>:</td>
<td>
<a href="search_topic.php?do_search=1&post_sort=desc&author_mode=author_liked&author=<?php echo(xrawurlencode($user_data["user_name"])); ?>" class="carma_plus" ><?php echo_html(format_number($user_data["carma_plus_weighed"], 1)); ?></a>
<?php if(!empty($settings["dislikes_active"])): ?>
/ <a href="search_topic.php?do_search=1&post_sort=desc&author_mode=author_disliked&author=<?php echo(xrawurlencode($user_data["user_name"])); ?>" class="carma_minus" ><?php echo_html(format_number($user_data["carma_minus_weighed"], 1)); ?></a>
<?php endif; ?>
</td>
</tr>
<?php endif; ?>

<tr>
<td colspan="2"></td>
</tr>

<tr>
<td><?php echo_html(text("Actions")); ?>:</td>
<td>
<a href="view_profile.php?uid=<?php echo_html($user_data["id"]); ?>" ><?php echo_html(text("ProfilePreview")); ?></a>
</td>
</tr>

<?php if($fmanager->is_moderator_log_visible() || $fmanager->get_user_id() == reqvar("uid")): ?>
<tr>
<td></td>
<td><a href="user_moderation.php?uid=<?php echo_html($user_data["id"]); ?>#log"><?php echo_html(text("ModeratorLog")); ?></a></td>
</tr>
<?php endif; ?>


<?php if(!empty($user_data["my_profile"])): ?>

<tr>
<td colspan="2"></td>
</tr>

<tr>
<td></td>
<td>
<a href="profile.php" class="moderator_link"><?php echo_html(text("ProfileSettings")); ?></a> 
</td>
</tr>

<tr>
<td></td>
<td>
<?php
$aname_appendix = "";
if(!empty($user_data["aname"]) && $user_data["aname"] != "admin")
  $aname_appendix .= "&aname=" . $user_data["aname"];
?>
<a href="ip_moderation.php?type=user_ips&user=<?php echo(xrawurlencode($user_data["user_name"])); ?><?php echo($aname_appendix); ?>" class="moderator_link"><?php echo_html(text("ShowAuthorIPs")); ?></a> 
</td>
</tr>

<?php endif; ?>



<?php if($fmanager->is_moderator()): 
$moderator_caption = $fmanager->is_admin() ? text("Administrator") : text("Moderator");
?>

<tr>
<td colspan="2"></td>
</tr>

<tr>
<td>
<?php 
if(!empty($moderator_caption)) 
{
  echo_html($moderator_caption . ": "); 
  $moderator_caption = "";
}
?>
<td>
<a href="user_moderation.php?uid=<?php echo_html($user_data["id"]); ?>" class="moderator_link"><?php echo_html(text("ModerateUser")); ?></a>
</td>
</tr>

<?php endif; ?>

<?php if($fmanager->is_admin()): ?>

<tr>
<td>
<?php 
if(!empty($moderator_caption)) 
{
  echo_html($moderator_caption . ": "); 
  $moderator_caption = "";
}
?>
</td>
<td>
<a href="edit_user.php?uid=<?php echo_html($user_data["id"]); ?>" class="moderator_link"><?php echo_html(text("EditUser")); ?></a>
</td>
</tr>

<?php endif; ?>

<?php if($fmanager->is_moderator() && $fmanager->may_see_ip()): ?>

<tr>
<td>
<?php 
if(!empty($moderator_caption)) 
{
  echo_html($moderator_caption . ": "); 
  $moderator_caption = "";
}
?>
</td>
<td>
<a href="ip_moderation.php?type=user_ips&user=<?php echo(xrawurlencode($user_data["user_name"])); ?>" class="moderator_link"><?php echo_html(text("ShowAuthorIPs")); ?></a>
</td>
</tr>

<tr>
<td>
<?php 
if(!empty($moderator_caption)) 
{
  echo_html($moderator_caption . ": "); 
  $moderator_caption = "";
}
?>
</td>
<td>
<a href="ip_moderation.php?type=other_users&user=<?php echo(xrawurlencode($user_data["user_name"])); ?>" class="moderator_link"><?php echo_html(text("ShowMembersOfAuthorIPs")); ?></a>
</td>
</tr>

<?php endif; ?>




<tr>
<td colspan="2"></td>
</tr>

<tr>
<td colspan="2" class="button_area">
<div class="left_buttons">
<input type="button" class="standard_button" value="<?php echo_html(text("Back")); ?>" onclick="delay_redirect('<?php echo_html($target_url); ?>')">
</div>
<div class="clear_both">
</div>
</td>
</tr>

</table>


<a id="moderation"></a>
<table class="rate_table">


<tr>
<th><?php echo_html(text("Member")); ?></th>
<th colspan="2"><?php echo_html(text("Likes")); ?></th>
<?php if(!empty($settings["dislikes_active"])): ?>
<th colspan="2"><?php echo_html(text("Dislikes")); ?></th>
<?php endif; ?>
</tr>

<?php if(!empty($rate_info)): ?>
<?php foreach($rate_info as $uid => $udata): ?>
<tr>
<td><div class="smart_break"><a href="view_profile.php?uid=<?php echo_html($uid); ?>"><?php echo_html($udata["user_name"]); ?></a></div></td>
<td>
<?php
$likes = 0;
if(!empty($udata["likes"])) $likes = $udata["likes"];
?>
<a href="search_topic.php?do_search=1&post_sort=desc&author_mode=author_liked&author=<?php echo(xrawurlencode($udata["user_name"])); ?>&rated_by=<?php echo(xrawurlencode($user_data["user_name"])); ?>" class="carma_plus" ><?php echo_html($likes); ?></a>
</td>
<td>
<input type="text" name="to_delete[<?php echo_html($uid); ?>][likes]" placeholder="<?php echo_html(text("Delete")); ?>">
</td>
<?php if(!empty($settings["dislikes_active"])): ?>
<td>
<?php
$dislikes = 0;
if(!empty($udata["dislikes"])) $dislikes = $udata["dislikes"];
?>
<a href="search_topic.php?do_search=1&post_sort=desc&author_mode=author_disliked&author=<?php echo(xrawurlencode($udata["user_name"])); ?>&rated_by=<?php echo(xrawurlencode($user_data["user_name"])); ?>" class="carma_minus" ><?php echo_html($dislikes); ?></a>
</td>
<td>
<input type="text" name="to_delete[<?php echo_html($uid); ?>][dislikes]" placeholder="<?php echo_html(text("Delete")); ?>">
</td>
<?php endif; ?>
</tr>
<?php endforeach; ?>

<tr>
<td colspan="5" class="button_area">
<div class="left_buttons">
<input type="button" class="standard_button" value="<?php echo_html(text("Back")); ?>" onclick="delay_redirect('<?php echo_html($target_url); ?>')">
</div>
<div class="right_buttons">
<input type="submit" class="standard_button send_button" value="<?php echo_html(text("Apply")); ?>">
</div>
<div class="clear_both">
</div>
</td>
</tr>


<?php else: ?>

<tr>
<td colspan="5" style="text-align: center"><?php echo_html(text("MsgUserNasNoRates")); ?></td>
</tr>

<?php endif; ?>

</table>

</form>

</div>