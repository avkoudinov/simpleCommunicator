<script>

<?php if(!empty($_SESSION["jump_to_log"])): 
unset($_SESSION["jump_to_log"]);
?>
Forum.addXEvent(window, 'load', function () {
  document.location.href = "#log";
});
<?php endif; ?>

var action_ajax = null;

function activate_ban_checkbox()
{
  var elm = document.getElementById("block_user");  
  if(elm) elm.checked = true;
}

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

function do_action()
{
  var form = document.getElementById('main_form');
  if(!form) return false;

  Forum.show_sys_progress_indicator(true);

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
          delay_redirect('user_moderation.php?uid=<?php echo_html($user_data["id"]); ?>');
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
  formData.append('moderate_user', "1");

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

/ <a href="moderation.php"><?php echo_html(text("Moderation")); ?></a> / 

<span class="topic_title_main"><?php echo_html($fmanager->is_moderator() ? text("ModerateUser") : text("ModeratorLog")); ?></span>

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

<form action="user_moderation.php" id="main_form" enctype="multipart/form-data" method="post" onsubmit="return do_action();">

<input type="hidden" id="uid" name="uid" value="<?php echo_html($user_data["id"]); ?>">

<table class="form_table profile_table">

<tr>
<th colspan="2"><?php echo_html($fmanager->is_moderator() ? text("ModerateUser") : text("ModeratorLog")); ?></th>
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
<td><a href="#log"><?php echo_html(text("ModeratorLog")); ?></a></td>
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




<?php if($fmanager->is_moderator()): ?>

<?php if(!empty($settings["rates_active"])): ?>
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
<a href="rate_moderation.php?uid=<?php echo_html($user_data["id"]); ?>#moderation" class="moderator_link"><?php echo_html(text("ModerateRates")); ?></a>
</td>
</tr>
<?php endif; ?>



<?php if($fmanager->may_see_ip()): ?>

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


<?php if($fmanager->is_moderator()): ?>

<tr>
<td colspan="2"></td>
</tr>

<?php endif; ?>



<?php if(count($moderated_restricted_forum_list) > 0): ?>

<tr>
<th colspan="2" class="subheader"><?php echo_html(text("AccessToRestrictedForums")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("Access")); ?>:</td>
<td>

   <table class="checkbox_table">
  <?php
  foreach($moderated_restricted_forum_list as $fid => $fname):
  $checked = !empty($user_data["forum_access"][$fid]) ? "checked" : "";
  ?>
   <tr>
     <td>
     <input type="checkbox" id="forum_access_<?php echo_html($fid); ?>" name="forum_access[<?php echo_html($fid); ?>]" value="1" <?php echo($checked); ?>>
     </td>
     <td>
     <label for="forum_access_<?php echo_html($fid); ?>"><?php echo_html($fname); ?></label> 
     </td>
   </tr>
  <?php endforeach; ?>
   </table>

</td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<?php endif; ?>


<?php 
if(!empty($user_data["forum_blocked"])) $user_data["forum_blocked"] = array_intersect_key($user_data["forum_blocked"], $moderated_forum_list);

if(!empty($user_data["forum_blocked"])): 
?>

<tr>
<th colspan="2" class="subheader"><?php echo_html(text("UnblockOnForums")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("Forum")); ?>:</td>
<td>

   <table class="checkbox_table">
  <?php foreach($user_data["forum_blocked"] as $fid => $forum_data): ?>
   <tr>
     <td>
  <input type="checkbox" id="unblock_user_for_forum_<?php echo_html($fid); ?>" name="unblock_user_for_forum[]" value="<?php echo_html($fid); ?>">
     </td>
     <td>
  <label for="unblock_user_for_forum_<?php echo_html($fid); ?>"><?php echo_html($forum_data["name"]); ?>
  
    <br><span class="error_text"><?php echo_html(empty($forum_data["block_expires"]) ? text("Blocked") : sprintf(text("BlockedUntil"), $forum_data["block_expires"])); ?></span>
        <?php if(!empty($forum_data["block_time_left"])): ?>
        <span style="color:gray">[<?php echo_html($forum_data["block_time_left"]); ?>]</span>
        <?php endif; ?>
    
  </label> 
     </td>
   </tr>
  <?php endforeach; ?>
   </table>

</td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<?php endif; ?>

<tr>
<th colspan="2" class="subheader"><?php echo_html(text("Moderate")); ?></th>
</tr>

<?php if($fmanager->global_ban_allowed()): ?>

<tr>
<td><?php echo_html(text("Action")); ?>*:</td>
<td>
   <table class="checkbox_table">
    <?php if(empty($user_data["hidden"])): ?>
     <tr>
       <td>
    <input type="checkbox" id="hide_profile" name="hide_profile"> 
       </td>
       <td>
    <label for="hide_profile"><?php echo_html(text("HideProfile")); ?></label>
       </td>
     </tr>
    <?php else: ?>
     <tr>
       <td>
    <input type="checkbox" id="open_profile" name="open_profile"> 
       </td>
       <td>
    <label for="open_profile"><?php echo_html(text("OpenProfile")); ?></label>
       </td>
     </tr>
    <?php endif; ?>
    
    <?php if(!empty($settings["rates_active"])): ?>
      <?php if(empty($user_data["rating_blocked"])): ?>
       <tr>
         <td>
      <input type="checkbox" id="disallow_rating" name="disallow_rating"> 
         </td>
         <td>
      <label for="disallow_rating"><?php echo_html(text("DisallowRating")); ?></label>
         </td>
       </tr>
      <?php else: ?>
       <tr>
         <td>
      <input type="checkbox" id="allow_rating" name="allow_rating"> 
         </td>
         <td>
      <label for="allow_rating"><?php echo_html(text("AllowRating")); ?></label>
         </td>
       </tr>
      <?php endif; ?>
    <?php endif; ?>
    
    
    
   </table>
</td>
</tr>

<?php endif; ?>

<?php if(!empty($user_data["blocked"])): ?>

    <?php if($fmanager->global_ban_allowed()): ?>
    <tr>
    <td></td>
    <td>
     <table class="checkbox_table">
     <tr>
       <td>
        <input type="checkbox" id="unblock_user" name="unblock_user"> 
       </td>
       <td>
        <label for="unblock_user"><?php echo_html(text("Unblock")); ?></label>
       </td>
     </tr>
     </table>
    </td>
    </tr>
    <?php else: ?>
    <tr>
    <td></td>
    <td>
    <?php echo_html(text("MsgUserBlockedGlobally")); ?>
    </td>
    </tr>
    <?php endif; ?>

<?php else: ?>

<tr>
<td></td>
<td>
   <table class="checkbox_table">
   <tr>
     <td>
     <input type="checkbox" id="block_user" name="block_user"> 
     </td>
     <td>
     <label for="block_user"><?php echo_html(text("Block")); ?></label>
     </td>
   </tr>
   </table>
</td>
</tr>

<tr>
<td><?php echo_html(text("Forum")); ?>:</td>
<td>
<select name="forum" onchange="activate_ban_checkbox()">

<?php if ($fmanager->is_admin()): ?>

<option value="-9"><?php echo_html(text("ForAllForums")); ?></option>

<?php elseif($fmanager->global_ban_allowed()): ?>

<option value="-9"><?php echo_html(text("ForAllForums")); ?></option>
<option value=""><?php echo_html(text("ForAllModeratedForums")); ?></option>

<?php else: ?>

<option value=""><?php echo_html(text("ForAllModeratedForums")); ?></option>

<?php endif ?>

<?php foreach($moderated_forum_list as $fid => $fname): ?>
<option value="<?php echo_html($fid); ?>"><?php echo_html($fname); ?></option>
<?php endforeach; ?>
</select>
</td>
</tr>

<tr>
<td><?php echo_html(text("Period")); ?>:</td>
<td>
  <table class="period_table">
  <tr>
  <td><?php echo_html(text("Days")); ?></td>
  <td><?php echo_html(text("Hours")); ?></td>
  <td><?php echo_html(text("Minutes")); ?></td>
  </tr>
  <tr>
  <td><select name="days" onchange="activate_ban_checkbox()">
  <option value="">-</option>
  <?php for($i = 1; $i <= 30; $i++): ?>
  <option value="<?php echo_html($i); ?>"><?php echo_html($i); ?></option>
  <?php endfor; ?>
  </select>
  </td>
  <td><select name="hours" onchange="activate_ban_checkbox()">
  <option value="">-</option>
  <?php for($i = 1; $i <= 24; $i++): ?>
  <option value="<?php echo_html($i); ?>"><?php echo_html($i); ?></option>
  <?php endfor; ?>
  </select>
  </td>
  <td><select name="minutes" onchange="activate_ban_checkbox()">
  <option value="">-</option>
  <?php for($i = 1; $i <= 60; $i++): ?>
  <option value="<?php echo_html($i); ?>"><?php echo_html($i); ?></option>
  <?php endfor; ?>
  </select>
  </td>
  </tr>
  </table>
</td>
</tr>
<?php endif; // blocked ?>

    <?php if(empty($user_data["blocked"]) || $fmanager->global_ban_allowed()): ?>
    <tr>
    <td><?php echo_html(text("Reason")); ?>*:</td>
    <td>
    <select id="reason" name="reason">
    <option value="">-</option>
    <?php foreach($reason_list as $rid => $rname): ?>
    <option value="<?php echo_html($rid); ?>"><?php echo_html($rname); ?></option>
    <?php endforeach; ?>
    </select>
    </td>
    </tr>

    <tr>
    <td></td>
    <td><textarea id="reason_info" name="reason_info"></textarea></td>
    </tr>
    <?php endif; ?>

<?php endif; // if moderator ?>

<tr>
<td colspan="2"></td>
</tr>

<tr>
<td colspan="2" class="button_area">
<div class="left_buttons">
<input type="button" class="standard_button" value="<?php echo_html(text("Back")); ?>" onclick="delay_redirect('<?php echo_html($target_url); ?>')">
</div>
<?php if($fmanager->is_moderator()): ?>
<div class="right_buttons">
<input type="submit" class="standard_button send_button" value="<?php echo_html(text("Apply")); ?>">
</div>
<?php endif; // if moderator ?>
<div class="clear_both">
</div>

<a id="log"></a>
</td>
</tr>

</table>

</form>

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

/ <a href="moderation_log.php"><?php echo_html(text("ModeratorLog")); ?></a>:
<a href="view_profile.php?uid=<?php echo_html($user_data["id"]); ?>"><?php echo_html($user_data["user_name"]); ?></a>

/ <?php echo(build_page_info($pagination_info, text("Pages"))); ?>
</div>

<?php if($pagination_info["page_count"] > 1): ?>
<div class="navigator_bar"><?php echo(build_page_navigator("user_moderation.php?uid=$user_data[id]&mpage=$#log", $pagination_info)); ?></div>
<?php endif; ?>

<div class="forum_action_bar">
<table>
<tr>
<td>
<form action="user_moderation.php" method="get" onsubmit="Forum.show_sys_progress_indicator(true);">
<input type="hidden" name="uid" value="<?php echo(reqvar("uid")); ?>">
<input type="hidden" name="apply_filter" value="1">
<select name="action_name" id="action_name" class="filter_field" onchange="Forum.show_sys_progress_indicator(true); this.form.submit();">
<option value=""><?php echo_html(text("Filter") . " ..."); ?></option>
<?php foreach($action_list as $aid => $aname):
$selected = (val_or_empty($_SESSION["moderator_log_filter"]["action_name"]) == $aid) ? "selected" : "";
?>
<option value="<?php echo_html($aid); ?>" <?php echo($selected); ?>><?php echo_html($aname); ?></option>
<?php endforeach; ?>
</select>
</form>
</td>
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

<table class="moderator_log_table">
<tr>
<th class="author_col"><?php echo_html(text("Moderator")); ?> / <?php echo_html(text("Author")); ?></th>
<th class="topic_name_col"><?php echo_html(text("Action")); ?></th>
<th class="topic_name_col"><?php echo_html(text("Comment")); ?></th>
<th class="author_col"><?php echo_html(text("Author") . " / " . text("IPAddress")); ?></th>
<th class="topic_name_col"><?php echo_html(text("Topic")); ?></th>
<th class="forum_col"><?php echo_html(text("Forum")); ?></th>
<th class="date_col"><?php echo_html(text("DateTime")); ?></th>
<th>#</th>
</tr>

<?php if(count($event_list) == 0): ?>

<tr>
<td colspan="8" class="table_message"><?php echo_html(text("NoEvents")); ?></td>
</tr>

<?php else: ?>

<?php foreach($event_list as $evid => $evinfo): ?>

<tr>
<td class="author_col">
<div class="smart_break">
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

echo($moderator);
?>
</div>
</td>
<td class="topic_name_col">
<?php
$action = $evinfo["action"];
if($action == "block_user" && !empty($evinfo["action_expires"])) $action = "block_user_until";
if($action == "block_user_forum" && !empty($evinfo["action_expires"])) $action = "block_user_forum_until";
if($action == "block_ip" && !empty($evinfo["action_expires"])) $action = "block_ip_until";
if($action == "block_user_marker" && !empty($evinfo["action_expires"])) $action = "block_user_marker_until";

echo_html(str_ireplace("{time}", $evinfo["action_expires"], ForumManager::get_action_txt($action)));
?>
</td>
<td><div class="smart_break"><?php echo($evinfo["comment"]); ?></div></td>
<td class="author_col">
<div class="smart_break">
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
    $author .= escape_html(preg_replace("/([0-9]+\\.)+([^\\.]+)/", "xx.xx.xx.$2", $evinfo["ip"]));
  }
}
elseif($evinfo["action"] == "block_user_marker" || $evinfo["action"] == "unblock_user_marker")
{
  if(!empty($author)) $author .= ", ";

  if(!($fmanager->is_moderator() && $fmanager->may_see_ip()))
  {
    $author .= escape_html(substr($evinfo["ip"], 0, 4) . "xxxxxxxx" . substr($evinfo["ip"], -4));
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
    $author .= escape_html($evinfo["ip"]) . "&nbsp;<a href='ip_moderation.php?type=um_moderation&ip=" . xrawurlencode($evinfo["ip"]) . "' class='moderator_link $ip_class' title='" . escape_html(text("Moderate")) . "'>$ip_sign</a>";
  }
}

echo($author);
?>
</div>
</td>
<td class="topic_name_col">
<div class="smart_break" style="min-width:280px;">
<?php
$topic = escape_html(postprocess_message($evinfo["topic_name"]));
if(!empty($topic) && !empty($evinfo["topic_id"]) && !empty($evinfo["topic_id"]))
{
  $post_appx = "";
  if(!empty($evinfo["post_id"])) 
  {
    $post_appx = " &nbsp;[<a href='topic.php?fid=" . $evinfo["forum_id"] . "&tid=" . $evinfo["topic_id"] . "&msg=" . $evinfo["post_id"] . "' >#" . $evinfo["post_id"] . "</a>]";
  }
  
  $topic = "<a href='topic.php?fid=" . $evinfo["forum_id"] . "&tid=" . $evinfo["topic_id"] . "&gotonew=1' rel='nofollow' >" . $topic . "</a>" . $post_appx;
}
echo($topic);
?>
</div>
</td>
<td class="forum_col">
<div class="smart_break">
<?php
$forum = escape_html($evinfo["forum_name"]);
if(!empty($forum) && !empty($evinfo["forum_id"]))
{
  $not_preferred = "";
  if(!empty($_SESSION["ignored_forums"][$evinfo["forum_id"]])) $not_preferred = "not_preferred";
  $forum = "<a href='forum.php?fid=" . $evinfo["forum_id"] . "' class='$not_preferred'>" . $forum . "</a>";
}
echo($forum);
?>
</div>
</td>
<td class="date_col"><?php echo_html($evinfo["event_time"]); ?></td>
<td class="copy_event_ref">
    <div style="position: relative">
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
    
    <div onclick="toggle_id_info_actions('<?php echo_html($evid); ?>')">
    &nbsp;
    </div>

</td>
</tr>

<?php endforeach; ?>

<?php endif; ?>

</table>

<?php if(count($event_list) > 25): ?>

<!-- BEGIN: forum_bar -->

<div class="forum_bar">

<div class="forum_name_bar"><a href="forums.php"><?php echo_html(text("Forums")); ?></a>

<?php
$display = "style='display:none'";
if(!empty($topics_with_new_count)) $display = "";
?>
<span class="new topics_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span> 

/ <a href="moderation_log.php"><?php echo_html(text("ModeratorLog")); ?></a>:
<a href="view_profile.php?uid=<?php echo_html($user_data["id"]); ?>"><?php echo_html($user_data["user_name"]); ?></a>

/ <?php echo(build_page_info($pagination_info, text("Pages"))); ?>
</div>

<?php if($pagination_info["page_count"] > 1): ?>
<div class="navigator_bar"><?php echo(build_page_navigator("user_moderation.php?uid=$user_data[id]&mpage=$#log", $pagination_info)); ?></div>
<?php endif; ?>

<div class="forum_action_bar">
<table>
<tr>
<td>
<?php
$forum_selector_id = 3;
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

<?php
@include "online_users_inc.php";
?>

</div>
