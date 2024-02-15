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
  var elm = document.getElementById("block_ip");  
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

  if(form.elements['type'].value == "ip_users")
  {
    if(form.elements['ip'].value == "") 
    {
      var mbuttons = [
        {
          caption: msg_OK,
          handler: function() { Forum.hide_user_msgbox(); }
        }
      ];

      Forum.show_user_msgbox(msg_Error, "<?php echo_js(text("ErrIPAddressEmpty")); ?>", 'icon-error.gif', mbuttons, false, function () { form.elements['ip'].focus(); });

      return false;
    }
    
    delay_redirect('ip_moderation.php?type=ip_users&ip=' + form.elements['ip'].value);
    return false;
  }

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
          var author_appendix = "";
          var author = document.getElementById("author");
          if(author && author.value) author_appendix = "&author=" + encodeURIComponent(author.value);
          
          delay_redirect('ip_moderation.php?type=<?php echo(reqvar("type")); ?>&ip=' + encodeURIComponent(form.elements['ip'].value) + author_appendix);
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
  formData.append('moderate_ip', "1");

  action_ajax.setFormData(formData);

  action_ajax.request("ajax/process.php");

  return false;
} // do_action

function show_guest_ips(link)
{
  var elm = document.getElementById("ip");
  if(!elm || elm.value == "") 
  {
    var mbuttons = [
      {
        caption: msg_OK,
        handler: function() { Forum.hide_user_msgbox(); }
      }
    ];

    Forum.show_user_msgbox(msg_Error, "<?php echo_js(text("ErrIPAddressEmpty")); ?>", 'icon-error.gif', mbuttons, false, function () { elm.focus(); });

    return false;
  }

  link.href = "guest_ips.php?search_key=" + encodeURIComponent(elm.value);

  return true;
}

function show_ip_users(link)
{
  var elm = document.getElementById("ip");
  if(!elm || elm.value == "") 
  {
    var mbuttons = [
      {
        caption: msg_OK,
        handler: function() { Forum.hide_user_msgbox(); }
      }
    ];

    Forum.show_user_msgbox(msg_Error, "<?php echo_js(text("ErrIPAddressEmpty")); ?>", 'icon-error.gif', mbuttons, false, function () { elm.focus(); });

    return false;
  }

  var author_appendix = "";
  var author = document.getElementById("author");
  if(author && author.value) author_appendix = "&author=" + encodeURIComponent(author.value);

  link.href = "ip_moderation.php?type=ip_users&ip=" + encodeURIComponent(elm.value) + author_appendix;

  return true;
}

function moderate_ip(link)
{
  var elm = document.getElementById("ip");
  if(!elm || elm.value == "") 
  {
    var mbuttons = [
      {
        caption: msg_OK,
        handler: function() { Forum.hide_user_msgbox(); }
      }
    ];

    Forum.show_user_msgbox(msg_Error, "<?php echo_js(text("ErrIPAddressEmpty")); ?>", 'icon-error.gif', mbuttons, false, function () { elm.focus(); });

    return false;
  }

  var author_appendix = "";
  var author = document.getElementById("author");
  if(author && author.value) author_appendix = "&author=" + encodeURIComponent(author.value);

  link.href = "ip_moderation.php?type=moderation&ip=" + elm.value + author_appendix;

  return true;
}

function show_um_users(link)
{
  var elm = document.getElementById("ip");
  if(!elm || elm.value == "") 
  {
    var mbuttons = [
      {
        caption: msg_OK,
        handler: function() { Forum.hide_user_msgbox(); }
      }
    ];

    Forum.show_user_msgbox(msg_Error, "<?php echo_js(text("ErrFingerPrintEmpty")); ?>", 'icon-error.gif', mbuttons, false, function () { elm.focus(); });

    return false;
  }

  var author_appendix = "";
  var author = document.getElementById("author");
  if(author && author.value) author_appendix = "&author=" + encodeURIComponent(author.value);

  link.href = "ip_moderation.php?type=um_users&ip=" + elm.value + author_appendix;

  return true;
}

function moderate_um(link)
{
  var elm = document.getElementById("ip");
  if(!elm || elm.value == "") 
  {
    var mbuttons = [
      {
        caption: msg_OK,
        handler: function() { Forum.hide_user_msgbox(); }
      }
    ];

    Forum.show_user_msgbox(msg_Error, "<?php echo_js(text("ErrFingerPrintEmpty")); ?>", 'icon-error.gif', mbuttons, false, function () { elm.focus(); });

    return false;
  }

  var author_appendix = "";
  var author = document.getElementById("author");
  if(author && author.value) author_appendix = "&author=" + encodeURIComponent(author.value);

  link.href = "ip_moderation.php?type=um_moderation&ip=" + elm.value + author_appendix;

  return true;
}
</script>

<?php
$moderator_display = "";
if(!$fmanager->is_moderator_log_visible() || (val_or_empty($settings["moderator_log"]) == "all_names_hidden" && !$fmanager->is_moderator()))
{
  $moderator_display = "display:none";
}
?>

<div class="content_area">

<form action="ip_moderation.php" id="main_form" enctype="multipart/form-data" method="get" onsubmit="return do_action();">

<input type="hidden" id="type" name="type" value="<?php echo_html(reqvar("type")); ?>">

<!-- BEGIN: forum_bar -->

<div class="forum_bar">

<div class="forum_name_bar"><a href="forums.php"><?php echo_html(text("Forums")); ?></a>

<?php
$display = "style='display:none'";
if(!empty($topics_with_new_count)) $display = "";
?>
<span class="new topics_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span> 

/ <a href="moderation.php"><?php echo_html(text("Moderation")); ?></a> / <span class="topic_title_main"><?php echo_html($subtitle); ?></span>
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

<table class="form_table profile_table">

<tr>
<th colspan="2"><?php echo_html($subtitle); ?></th>
</tr>

<?php 
$aname_appendix = "";
if(!empty($user_data["aname"]) && $user_data["aname"] != "admin")
  $aname_appendix .= "&aname=" . $user_data["aname"];

if(reqvar("ignored") == 2)
  $aname_appendix .= "&ignored=2";

if(!empty($user_data["user_name"])): ?>

<tr>
<td><?php echo_html(text("UserName")); ?>:</td>
  <?php
  $online_status = "";
  if(empty($settings["hide_online_status"]) && (!empty($user_data["online"]) || !empty($online_users["g_" . $user_data["user_name"]])))
  {
    $online_status = "&nbsp;<span class='online_text'>✓</span>";
  }

  $protected_guest = "";
  if(!empty($settings["protected_guest_list"][$user_data["user_name"]]) && $fmanager->is_admin() && empty($user_data["id"]))
  {
    $protected_guest = "<div class='protected_guest'></div>";
  }
  ?>
<td><span class="number"><?php echo_html($fmanager->get_display_name($user_data["user_name"])); ?></span><?php echo($protected_guest . $online_status); ?></td>
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

<?php if(empty($user_data["id"])): // if guest ?>

<tr>
<td><?php echo_html(text("Status")); ?>:</td>
<td>
    <?php if($user_data["user_name"] == "admin"): ?>
    <?php echo_html(text("MasterAdministrator")); ?><br>
    <?php else: ?>
    <?php echo_html(text("Guest")); ?><br>
    <?php endif; ?>

    <?php if(!empty($user_data["hidden_by_me"])): ?>
    <br><span class="error_text"><?php echo_html(text("HiddenByMe")); ?></span>
    <?php endif; ?>

    <?php if(reqvar("ignored") == 2): ?>
    <br><span class="error_text"><?php echo_html(text("Ignored")); ?>*</span>
    <?php elseif(!empty($user_data["guest_ignored"])): ?>
    <br><span class="error_text"><?php echo_html(text("Ignored")); ?></span>
    <?php endif; ?>

</td>
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

<?php if($fmanager->is_admin()): ?>

<tr>
<td><?php echo_html(text("LastIPAddress")); ?>:</td>
<td>

<?php
if($fmanager->demo_mode())
{
  $user_data["last_ip"] = "127.0.0.1";
}

$ip = escape_html($user_data["last_ip"]);
if(!empty($ip))
{
  if(!empty($settings["whois_server"]))
  {
    $url = str_ireplace("{ip}", $ip, str_replace("&", "&", $settings["whois_server"]));
    $ip = "<a href='$url' target='_blank'>" . $ip . "</a>";
  }

  $ip_sign = "✘";
  $ip_class = "ip_moderation";
  if(!empty($user_data["last_ip_blocked"]))
  {
    $ip_sign = "✘";
    $ip_class = "ip_moderation ip_blocked";
  }
  $ip .= "&nbsp;<a href='ip_moderation.php?type=moderation&ip=" . xrawurlencode($user_data["last_ip"]) . "' class='moderator_link $ip_class' title='" . escape_html(text("Moderate")) . "'>$ip_sign</a>";

  if($fmanager->is_admin())
  {
    $ip_sign = "✓";
    $ip_class = "guest_ip";
    if(!empty($user_data["last_ip_whitelisted"]))
    {
      $ip_sign = "✓";
      $ip_class = "ip_whitelisted";
    }
    $ip .= "&nbsp;<a href='guest_ips.php?search_key=" . xrawurlencode($user_data["last_ip"]) . "' class='ip_moderation $ip_class' title='" . escape_html(text("GuestIPs")) . "'>$ip_sign</a>";
  }

  if(!empty($user_data["last_tor_ip"]))
  {
    $ip_class = "ip_moderation " . val_or_empty($user_data["last_tor_ip_block_level"]);
    $ip_sign = "Tor";
    $ip .= "&nbsp;<a href='tor_ips.php?search_key=" . xrawurlencode($user_data["last_ip"]) . "' class='moderator_link $ip_class' title='" . escape_html(text("Moderate")) . "'>$ip_sign</a>";
  }

  $ip .= "&nbsp;<a href='ip_moderation.php?type=ip_users&ip=" . xrawurlencode($user_data["last_ip"]) . "' title='" . escape_html(text("Authors")) . "'><img src='" . $view_path . "images/users.png' alt='" . escape_html(text("Authors")) . "' class='ip_authors'></a>";
}

echo $ip;
?>
</td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<?php endif; ?>

<tr>
<td></td>
<td>
<a href="view_guest_profile.php?guest=<?php echo(xrawurlencode($user_data["user_name"])); ?><?php echo($aname_appendix); ?>"><?php echo_html(text("ProfilePreview")); ?></a>
</td>
</tr>

<?php if($fmanager->is_moderator_log_visible()): ?>
<tr>
<td></td>
<td><a href="moderation_log.php?user_name=<?php echo(xrawurlencode($user_data["user_name"])); ?>"><?php echo_html(text("ModeratorLog")); ?></a></td>
</tr>
<?php endif; ?>

<?php if($fmanager->is_moderator() && $fmanager->may_see_ip()): ?>
  <?php if(reqvar("type") == "other_users"): ?>
  <tr>
  <td></td>
  <td>
  <a href="ip_moderation.php?type=user_ips&user=<?php echo(xrawurlencode($user_data["user_name"])); ?><?php echo($aname_appendix); ?>" class="moderator_link"><?php echo_html(text("ShowAuthorIPs")); ?></a>
  </td>
  </tr>
  <?php elseif(reqvar("type") == "user_ips"): ?>
  <tr>
  <td></td>
  <td>
  <a href="ip_moderation.php?type=other_users&user=<?php echo(xrawurlencode($user_data["user_name"])); ?><?php echo($aname_appendix); ?>" class="moderator_link"><?php echo_html(text("ShowMembersOfAuthorIPs")); ?></a>
  </td>
  </tr>
  <?php endif; ?>
<?php endif; ?>

<?php else: // if user ?>

<tr>
<td><?php echo_html(text("Status")); ?>:</td>
<td>
  <?php if(!empty($user_data["is_admin"])): ?>

    <?php echo_html(text("Administrator")); ?><br>

  <?php elseif(!empty($user_data["moderator"])): ?>

    <div class='moderator_of_forums'><?php echo_html(text("ModeratorOfForums")); ?>:</div>
    <?php foreach($user_data["moderator"] as $fid => $fname):
    $not_preferred = "";
    if(!empty($_SESSION["preferred_forums"]) && empty($_SESSION["preferred_forums"][$fid])) $not_preferred = "not_preferred";
    ?>
    <a href="forum.php?fid=<?php echo_html($fid); ?>" class="<?php echo($not_preferred); ?>"><?php echo_html($fname); ?></a><br>
    <?php endforeach; ?>

  <?php else: ?>

    <?php echo_html(text("RegisteredUser")); ?><br>

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

  <?php if((!$fmanager->is_logged_in() || $fmanager->is_master_admin()) && !empty($user_data["ignores_all_guests"])): ?>
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
    if(!empty($_SESSION["preferred_forums"]) && empty($_SESSION["preferred_forums"][$fid]) && $forum_data["fid_for_url"] != "private") $not_preferred = "not_preferred";
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
<td></td>
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

<?php endif; ?>





<?php if($fmanager->is_moderator() && $fmanager->may_see_ip()): ?>
  <?php if(reqvar("type") == "other_users"): ?>
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
  <a href="ip_moderation.php?type=user_ips&user=<?php echo(xrawurlencode($user_data["user_name"])); ?><?php echo($aname_appendix); ?>" class="moderator_link"><?php echo_html(text("ShowAuthorIPs")); ?></a>
  </td>
  </tr>
  <?php elseif(reqvar("type") == "user_ips"): ?>
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
  <a href="ip_moderation.php?type=other_users&user=<?php echo(xrawurlencode($user_data["user_name"])); ?><?php echo($aname_appendix); ?>" class="moderator_link"><?php echo_html(text("ShowMembersOfAuthorIPs")); ?></a>
  </td>
  </tr>
  <?php endif; ?>
<?php endif; ?>

<?php endif; // guest or registered ?>

<?php endif; // user data ?>

<?php if(reqvar("type") == "moderation" || reqvar("type") == "ip_users" ||
         reqvar("type") == "um_moderation" || reqvar("type") == "um_users"): 
?>

<tr>
<td>
<?php 
if(reqvar("type") == "um_moderation" || reqvar("type") == "um_users") 
  echo_html(text("FingerPrint")); 
else
  echo_html(text("IPAddress")); 
?>:</td>
<td><input type="text" id="ip" name="ip" value="<?php echo_html(reqvar("ip")); ?>">

<?php if(reqvar("type") != "moderation" && reqvar("type") != "um_moderation"): ?>
<input type="hidden" id="author" name="author" value="<?php echo_html(reqvar("author")); ?>">
<?php endif; ?>

</td>
</tr>

<tr>
<td></td>
<td>
<?php if(reqvar("type") == "moderation"): ?>
<a href="ip_moderation.php?type=ip_users&ip=<?php echo(xrawurlencode(reqvar("ip")) . $author_appendix); ?>" onclick="return show_ip_users(this)"><?php echo_html(text("ShowMembersOfIP")); ?></a>
  <?php if($fmanager->is_admin()): ?>
  <br><a href="guest_ips.php?search_key=<?php echo(xrawurlencode(reqvar("ip"))); ?>" onclick="return show_guest_ips(this)"><?php echo_html(text("GuestIPs")); ?></a>
  <?php endif; ?>
<?php endif; ?>
<?php if(reqvar("type") == "ip_users"): ?>
<a href="ip_moderation.php?type=moderation&ip=<?php echo(xrawurlencode(reqvar("ip")) . $author_appendix); ?>" onclick="return moderate_ip(this)"><?php echo_html(text("ModerateIP")); ?></a>
  <?php if($fmanager->is_admin()): ?>
  <br><a href="guest_ips.php?search_key=<?php echo(xrawurlencode(reqvar("ip"))); ?>" onclick="return show_guest_ips(this)"><?php echo_html(text("GuestIPs")); ?></a>
  <?php endif; ?>
<?php endif; ?>
<?php if(reqvar("type") == "um_moderation"): ?>
<a href="ip_moderation.php?type=um_users&ip=<?php echo(xrawurlencode(reqvar("ip"))); ?>"  onclick="return show_um_users(this)"><?php echo_html(text("ShowMembersOfFingerPrint")); ?></a>
<?php endif; ?>
<?php if(reqvar("type") == "um_users"): ?>
<a href="ip_moderation.php?type=um_moderation&ip=<?php echo(xrawurlencode(reqvar("ip"))); ?>" onclick="return moderate_um(this)"><?php echo_html(text("ModerateFingerPrint")); ?></a>
<?php endif; ?>
</td>
</tr>

<?php endif; // id ?>

<?php if(reqvar("type") == "moderation" || reqvar("type") == "um_moderation"): ?>

<tr>
<td colspan="2"></td>
</tr>

<tr>
<th colspan="2" class="subheader"><?php echo_html(text("Moderate")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("Author")); ?>:</td>
<td>
  <?php if(reqvar("author") == "admin"): ?>
  <input type="hidden" id="author" name="author" value="<?php echo_html(reqvar("author")); ?>">
  <input type="text" class="read_only_field" read_only="" name="display_author" value="<?php echo_html(text("MasterAdministrator")); ?>">
  <?php else: ?>
  <input type="text" id="author" name="author" value="<?php echo_html(reqvar("author")); ?>">
  <?php endif; ?>
</td>
</tr>

<tr>
<td><?php echo_html(text("Action")); ?>*:</td>
<td>
   <table class="checkbox_table">
  <?php if(empty($ip_blocked)): ?>
   <tr>
     <td>
  <input type="checkbox" id="block_ip" name="block_ip"> 
     </td>
     <td>
  <label for="block_ip"><?php echo_html(text("Block")); ?></label>
     </td>
   </tr>
  <?php else: ?>
   <tr>
     <td>
  <input type="checkbox" id="unblock_ip" name="unblock_ip"> 
     </td>
     <td>
  <label for="unblock_ip"><?php echo_html(text("Unblock")); ?></label>
     </td>
   </tr>
  <?php endif; ?>
   </table>
</td>
</tr>

<tr>
<td><?php echo_html(text("Reason")); ?>*:</td>
<td>
<select id="reason" name="reason" onchange="activate_ban_checkbox()">
<option value="">-</option>
<?php foreach($reason_list as $rid => $rname): ?>
<option value="<?php echo_html($rid); ?>"><?php echo_html($rname); ?></option>
<?php endforeach; ?>
</select>
</td>
</tr>

<tr>
<td></td>
<td><textarea id="reason_info" name="reason_info" onchange="activate_ban_checkbox()"></textarea>
</tr>

<?php if(empty($ip_blocked)): ?>
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
  <?php for($i = 1; $i <= 60; $i++): ?>
  <option value="<?php echo_html($i); ?>"><?php echo_html($i); ?></option>
  <?php endfor; ?>
  </select>
  </td>
  <td><select name="hours" onchange="activate_ban_checkbox()">
  <option value="">-</option>
  <?php for($i = 1; $i <= 60; $i++): ?>
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

<?php endif; ?>

<?php endif; // moderation ?>

<tr>
<td colspan="2"></td>
</tr>

<tr>
<td colspan="2" class="button_area">

<div class="left_buttons">
<input type="button" class="standard_button" value="<?php echo_html(text("Back")); ?>" onclick="delay_redirect('<?php echo_html($target_url); ?>')">
</div>
<div class="right_buttons">
<?php if(reqvar("type") == "moderation" || reqvar("type") == "um_moderation" || reqvar("type") == "ip_users"): ?>
<input type="submit" class="standard_button send_button" value="<?php echo_html(text("Apply")); ?>">
<?php endif; ?>
</div>
<div class="clear_both">
</div>

<a id="log"></a>
</td>
</tr>

</table>

</form>



<!-- ************************************************** -->
<!-- ************************************************** -->
<!-- ************************************************** -->



<?php if(reqvar("type") == "ip_users" || reqvar("type") == "um_users"): ?>

<table class="ip_table">
<tr>
<th><?php echo_html(text("Member")); ?></th>
<th><?php echo_html(text("FirstMessage")); ?></th>
<th><?php echo_html(text("LastMessage")); ?></th>
<th><?php echo_html(text("Messages")); ?></th>

<?php if(reqvar("type") == "ip_users"): ?>
<th><?php echo_html(text("More")); ?></th>
<?php endif; ?>

</tr>

<?php if(empty($ip_users)): ?>

<tr>
<td colspan="<?php echo(reqvar("type") == "ip_users" ? 5 : 4); ?>" class="table_message"><?php echo_html(text("NoData")); ?></td>
</tr>

<?php else: ?>

<?php foreach($ip_users as $uifno): ?>

<tr>
<td>
<div class="smart_break" style="min-width:200px">
<?php
$user = "";
if(!empty($uifno["user_name"]))
{
  if(!empty($uifno["id"]))
    $user = "<a href='view_profile.php?uid=" . $uifno["id"] . "' >" . escape_html($uifno["user_name"]) . "</a>";
  elseif($uifno["user_name"] == "admin")
    $user = "<a class='admin_link' href='view_guest_profile.php?guest=" . xrawurlencode($uifno["user_name"]) . "' >" . escape_html(text("MasterAdministrator")) . "</a>";
  else
    $user = "<a class='guest_link' href='view_guest_profile.php?guest=" . xrawurlencode($uifno["user_name"]) . "' >" . escape_html($uifno["user_name"]) . "</a>";
  
  if(empty($settings["hide_online_status"]) && (!empty($uifno["online"]) || !empty($online_users["g_" . $uifno["user_name"]])))
  {
    $user .= "&nbsp;<span class='online_text'>✓</span>";
  }
}

echo($user);
?>
</div>
</td>
<td><?php echo_html($uifno["first_message"]); ?></td>
<td><?php echo_html($uifno["last_message"]); ?></td>

<?php
$author = $uifno["user_name"];
if(empty($uifno["id"]) && $author != "admin") $author = ":" . $author;
?>
<td><a class="message_count" href="search.php?author=<?php echo(xrawurlencode($author)); ?>&author_mode=wrote_post&start_date=<?php echo($start_date); ?>&include_deleted=1&ip=<?php echo(xrawurlencode(reqvar("ip"))); ?>&post_list=1&do_search=1&post_sort=desc" ><?php echo_html(format_number($uifno["cnt"])); ?></a></td>

<?php if(reqvar("type") == "ip_users"): ?>
<td class="ip_action">
  <a href="ip_moderation.php?type=user_ips&user=<?php echo(xrawurlencode($uifno["user_name"])); ?>" class="moderator_link"><?php echo_html(text("IPAddresses")); ?></a> |
  <a href="ip_moderation.php?type=other_users&user=<?php echo(xrawurlencode($uifno["user_name"])); ?>" class="moderator_link"><?php echo_html(text("OtherAuthors")); ?></a>
</td>
<?php endif; ?>
</tr>

<?php endforeach; ?>

<?php endif; ?>

</table>

<?php endif; // ip_users ?>



<!-- ************************************************** -->
<!-- ************************************************** -->
<!-- ************************************************** -->



<?php if(reqvar("type") == "user_ips"): ?>

<table class="ip_table">
<tr>
<th><?php echo_html(text("IPAddress")); ?></th>
<th><?php echo_html(text("FirstMessage")); ?></th>
<th><?php echo_html(text("LastMessage")); ?></th>
<th><?php echo_html(text("Messages")); ?></th>
</tr>

<?php if(empty($user_ips)): ?>

<tr>
<td colspan="4" class="table_message"><?php echo_html(text("NoData")); ?></td>
</tr>

<?php else: ?>

<?php foreach($user_ips as $ipfno): ?>

<tr>
<td>
<div class="smart_break" style="min-width:200px">
<?php
$ip = escape_html($ipfno["ip"]);
if(!empty($settings["whois_server"]))
{
  $url = str_ireplace("{ip}", $ip, $settings["whois_server"]);

  $ip = "<a href='$url' target='_blank'>" . $ip . "</a>";
}

if ($fmanager->is_moderator())
{
  $ip_sign = "✘";
  $ip_class = "ip_moderation";
  if(!empty($ipfno["ip_blocked"]))
  {
    $ip_sign = "✘";
    $ip_class = "ip_moderation ip_blocked";
  }
  $ip .= "&nbsp;<a href='ip_moderation.php?type=moderation&ip=" . xrawurlencode($ipfno["ip"]) . "' class='moderator_link $ip_class' title='" . escape_html(text("Moderate")) . "'>$ip_sign</a>";

  if($fmanager->is_admin())
  {
    $ip_sign = "✓";
    $ip_class = "guest_ip";
    if(!empty($ipfno["guest_ip_whitelisted"]))
    {
      $ip_sign = "✓";
      $ip_class = "ip_whitelisted";
    }
    $ip .= "&nbsp;<a href='guest_ips.php?search_key=" . xrawurlencode($ipfno["ip"]) . "' class='ip_moderation $ip_class' title='" . escape_html(text("GuestIPs")) . "'>$ip_sign</a>";

    if(!empty($ipfno["tor_ip"]))
    {
      $ip_class = "ip_moderation " . val_or_empty($ipfno["tor_ip_block_level"]);
      $ip_sign = "Tor";
      $ip .= "&nbsp;<a href='tor_ips.php?search_key=" . xrawurlencode($ipfno["ip"]) . "' class='moderator_link $ip_class' title='" . escape_html(text("Moderate")) . "'>$ip_sign</a>";
    }
  }

  $ip .= "&nbsp;<a href='ip_moderation.php?type=ip_users&ip=" . xrawurlencode($ipfno["ip"]) . "' title='" . escape_html(text("Authors")) . "'><img src='" . $view_path . "images/users.png' alt='" . escape_html(text("Authors")) . "' class='ip_authors'></a>";
}

echo($ip);
?>
</div>
</td>
<td><?php echo_html($ipfno["first_message"]); ?></td>
<td><?php echo_html($ipfno["last_message"]); ?></td>

<?php
$author = $user_data["user_name"];
if(empty($user_data["id"]) && $author != "admin") $author = ":" . $author;
?>
<td><a class="message_count" href="search.php?author=<?php echo(xrawurlencode($author)); ?>&author_mode=wrote_post&start_date=<?php echo($start_date); ?>&include_deleted=1&ip=<?php echo(xrawurlencode($ipfno["ip"])); ?>&post_list=1&do_search=1&post_sort=desc" ><?php echo_html(format_number($ipfno["cnt"])); ?></a></td>
</tr>

<?php endforeach; ?>

<?php endif; ?>

</table>

<?php endif; // user_ips ?>


<!-- ************************************************** -->
<!-- ************************************************** -->
<!-- ************************************************** -->


<?php if(reqvar("type") == "other_users"): ?>

<table class="ip_table">
<tr>
<th><?php echo_html(text("Member")); ?></th>
<th><?php echo_html(text("FirstMessage")); ?></th>
<th><?php echo_html(text("LastMessage")); ?></th>
<th><?php echo_html(text("Messages")); ?></th>
<th><?php echo_html(text("More")); ?></th>
</tr>

<?php if(empty($other_users)): ?>

<tr>
<td colspan="5" class="table_message"><?php echo_html(text("NoData")); ?></td>
</tr>

<?php else: ?>

<?php foreach($other_users as $uifno): ?>

<tr>
<td>
<div class="smart_break" style="min-width:200px">
<?php
$user = "";
if(!empty($uifno["user_name"]))
{
  if(!empty($uifno["id"]))
    $user = "<a href='view_profile.php?uid=" . $uifno["id"] . "' >" . escape_html($uifno["user_name"]) . "</a>";
  elseif($uifno["user_name"] == "admin")
    $user = "<a class='admin_link' href='view_guest_profile.php?guest=" . xrawurlencode($uifno["user_name"]) . "' >" . escape_html(text("MasterAdministrator")) . "</a>";
  else
    $user = "<a class='guest_link' href='view_guest_profile.php?guest=" . xrawurlencode($uifno["user_name"]) . "' >" . escape_html($uifno["user_name"]) . "</a>";
  
  if(empty($settings["hide_online_status"]) && (!empty($uifno["online"]) || !empty($online_users["g_" . $uifno["user_name"]])))
  {
    $user .= "&nbsp;<span class='online_text'>✓</span>";
  }
}

echo($user);
?>
</div>
</td>
<td><?php echo_html($uifno["first_message"]); ?></td>
<td><?php echo_html($uifno["last_message"]); ?></td>
<td><?php echo_html(format_number($uifno["cnt"])); ?></td>
<td>
  <a href="ip_moderation.php?type=user_ips&user=<?php echo(xrawurlencode($uifno["user_name"])); ?>" class="moderator_link"><?php echo_html(text("IPAddresses")); ?></a> |
  <a href="ip_moderation.php?type=other_users&user=<?php echo(xrawurlencode($uifno["user_name"])); ?>" class="moderator_link"><?php echo_html(text("OtherAuthors")); ?></a>
</td>
</tr>

<?php endforeach; ?>

<?php endif; ?>

</table>

<?php endif; // other_users ?>


<!-- ************************************************** -->
<!-- ************************************************** -->
<!-- ************************************************** -->


<?php if((reqvar("type") == "moderation" || reqvar("type") == "um_moderation") && trim(reqvar("ip")) != ""): ?>

<!-- BEGIN: forum_bar -->

<div class="forum_bar">

<div class="forum_name_bar"><a href="forums.php"><?php echo_html(text("Forums")); ?></a>

<?php
$display = "style='display:none'";
if(!empty($topics_with_new_count)) $display = "";
?>
<span class="new topics_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span> 

/ <a href="moderation_log.php"><?php echo_html(text("ModeratorLog")); ?></a>:
<?php
$ip = escape_html(reqvar("ip"));
if(!empty($settings["whois_server"]) && reqvar("type") == "moderation")
{
  $url = str_ireplace("{ip}", $ip, $settings["whois_server"]);
  $ip = "<a href='$url' target='_blank'>" . $ip . "</a>";
}

$ip_sign = "✘";
$ip_class = "ip_moderation";
if(!empty($ip_blocked))
{
  $ip_sign = "✘";
  $ip_class = "ip_moderation ip_blocked";
}

if(reqvar("type") == "um_moderation")
  $ip .= "&nbsp;<a href='ip_moderation.php?type=um_moderation&ip=" . xrawurlencode(reqvar("ip")) . $author_appendix . "' class='moderator_link $ip_class' title='" . escape_html(text("Moderate")) . "'>$ip_sign</a>";
else
  $ip .= "&nbsp;<a href='ip_moderation.php?type=moderation&ip=" . xrawurlencode(reqvar("ip")) . $author_appendix . "' class='moderator_link $ip_class' title='" . escape_html(text("Moderate")) . "'>$ip_sign</a>";
  
echo($ip);
?>

/ <?php echo(build_page_info($pagination_info, text("Pages"))); ?>
</div>

<?php if($pagination_info["page_count"] > 1): ?>
<div class="navigator_bar"><?php echo(build_page_navigator("ip_moderation.php?type=moderation&ip=" . xrawurlencode(reqvar("ip")) . $author_appendix . "&mpage=$#log", $pagination_info)); ?></div>
<?php endif; ?>

<div class="forum_action_bar">
<table>
<tr>
<td>
<form action="ip_moderation.php" method="get" onsubmit="Forum.show_sys_progress_indicator(true);">
<input type="hidden" name="type" value="<?php echo(reqvar("type")); ?>">
<input type="hidden" name="author" value="<?php echo(reqvar("author")); ?>">
<input type="hidden" name="ip" value="<?php echo(reqvar("ip")); ?>">
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
$moderator = escape_html($evinfo["moderator_name"]);
if(!empty($moderator))
{
  if(!empty($evinfo["moderator_id"])) $moderator = "<a href='view_profile.php?uid=" . $evinfo["moderator_id"] . "' >" . $moderator . "</a>";

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
    $author = escape_html(preg_replace("/([0-9]+\\.)+([^\\.]+)/", "xx.xx.xx.$2", $evinfo["ip"]));
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
<?php
$forum = escape_html($evinfo["forum_name"]);
if(!empty($forum) && !empty($evinfo["forum_id"]))
{
  $not_preferred = "";
  if(!empty($_SESSION["preferred_forums"]) && empty($_SESSION["preferred_forums"][$evinfo["forum_id"]])) $not_preferred = "not_preferred";
  $forum = "<a href='forum.php?fid=" . $evinfo["forum_id"] . "' class='$not_preferred'>" . $forum . "</a>";
}
echo($forum);
?>
</td>
<td class="date_col"><?php echo_html($evinfo["event_time"]); ?></td>
<td class="copy_event_ref">
    <div style="position: relative">
      <div id="event_id_info_<?php echo_html($evid); ?>" class="event_id_info_actions" style="display:none">
      <div style="position: absolute;right:2px;top:2px;cursor:pointer" onclick="toggle_id_info_actions('<?php echo_html($evid); ?>')"><img src="<?php echo($view_path); ?>images/cross.png" alt="<?php echo_html(text("Close")); ?>"></div>
      
      <div class="inner_label"><?php echo_html(text("LinkToEvent")); ?>:</div>
        <table class="aux_table">
        <tr>
        <td><input type="text" id="evid_link_<?php echo_html($evid); ?>" value="<?php echo_html(get_host_address() . get_url_path() . "moderation_log.php?event=$evid"); ?>" onfocus="select_text_in_field('evid_link_<?php echo_html($evid); ?>')"></td>
        <td>&nbsp;</td>
        <td><input type="button" class="standard_button" value="&nbsp;" onclick="focus_field('evid_link_<?php echo_html($evid); ?>')" title="<?php echo_html(text("MarkForCopy")); ?>"></td>
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
<?php
$ip = escape_html(reqvar("ip"));
if(!empty($settings["whois_server"]))
{
  $url = str_ireplace("{ip}", $ip, $settings["whois_server"]);

  $ip = "<a href='$url' target='_blank'>" . $ip . "</a>";
}

$ip_sign = "✘";
$ip_class = "ip_moderation";
if(!empty($ip_blocked))
{
  $ip_sign = "✘";
  $ip_class = "ip_moderation ip_blocked";
}
$ip .= "&nbsp;<a href='ip_moderation.php?type=moderation&ip=" . xrawurlencode(reqvar("ip")) . $author_appendix . "' class='moderator_link $ip_class' title='" . escape_html(text("Moderate")) . "'>$ip_sign</a>";

echo($ip);
?>

/ <?php echo(build_page_info($pagination_info, text("Pages"))); ?>
</div>

<?php if($pagination_info["page_count"] > 1): ?>
<div class="navigator_bar"><?php echo(build_page_navigator("ip_moderation.php?type=moderation&ip=" . xrawurlencode(reqvar("ip")) . $author_appendix . "&mpage=$#log", $pagination_info)); ?></div>
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


<?php endif; // ip moderation ?>

</div>

