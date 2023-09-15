<script>
var load_user_rates_ajax = null;

function load_user_rates(load_user_rates_button)
{
  var params = { uid: "<?php echo_js(reqvar("uid")); ?>", author: "<?php echo_js($user_data["user_name"]); ?>" };

  if(load_user_rates_button) load_user_rates_button.classList.add("member_search_button_active");

  if(!load_user_rates_ajax)
  {
    load_user_rates_ajax = new Forum.AJAX();

    load_user_rates_ajax.timeout = TIMEOUT;

    load_user_rates_ajax.beforestart = function() { break_check_new_messages(); };
    load_user_rates_ajax.aftercomplete = function(error) { 
      activate_check_new_messages(); 
      check_new_messages();
    };

    load_user_rates_ajax.onload = function(text, xml)
    {
      if(text.trim() == '') 
      {
        if(load_user_rates_button) load_user_rates_button.classList.remove("member_search_button_active");
        return;
      }

      try
      {
        var navs = document.getElementsByClassName('user_rates_area');
        for(var i = 0; i < navs.length; i++)
        {
          navs[i].innerHTML = text;
        }
      }
      catch(err)
      {
      }

      if(load_user_rates_button) load_user_rates_button.classList.remove("member_search_button_active");
    };

    load_user_rates_ajax.onerror = function(error, url, info)
    {
      if(load_user_rates_button) load_user_rates_button.classList.remove("member_search_button_active");
    };
  } // init ajax

  load_user_rates_ajax.abort();
  load_user_rates_ajax.resetParams();

  for(var p in params)
  {
    if(!Object.prototype.hasOwnProperty.call(params, p)) continue;

    load_user_rates_ajax.setPOST(p, params[p]);
  }

  load_user_rates_ajax.setPOST('hash', get_protection_hash());
  load_user_rates_ajax.setPOST('user_logged', user_logged);

  load_user_rates_ajax.request("ajax/load_user_rates.php");

  return false;
}

function reload_daily_activity_image(period)
{
  var img = document.getElementById("user_daily_activity_image");
  if(!img) return;

  img.style.opacity = "0.2";

  img.src = "ajax/user_activity_diagram.php?type=daily&uid=<?php echo_html(reqvar("uid")); ?>&period=" + period + "&rnd=" + Math.random();
}

function reload_hourly_activity_image(time_zone)
{
  var img = document.getElementById("user_hourly_activity_image");
  if(!img) return;

  img.style.opacity = "0.2";

  img.src = "ajax/user_activity_diagram.php?type=hourly&uid=<?php echo_html(reqvar("uid")); ?>&time_zone=" + time_zone + "&rnd=" + Math.random();
}

var show_user_email_ajax = null;

function show_user_email()
{
  var elm = document.getElementById("user_email");
  if(!elm) return;

  var show_email_button = document.getElementById('show_email_button');
  if(show_email_button) show_email_button.classList.add("member_search_button_active");

  if(!show_user_email_ajax)
  {
    show_user_email_ajax = new Forum.AJAX();

    show_user_email_ajax.timeout = TIMEOUT;

    show_user_email_ajax.beforestart = function() { break_check_new_messages(); };
    show_user_email_ajax.aftercomplete = function(error) { activate_check_new_messages(); };

    show_user_email_ajax.onload = function(text, xml)
    {
      try
      {
        var response = JSON.parse(text);

        Forum.handle_response_messages(response);

        if(response.user_email)
        {
          elm.innerHTML = response.user_email;
        }
      }
      catch(err)
      {
        Forum.handle_ajax_error(this, err.message, this.last_url, {});
      }

      if(show_email_button) show_email_button.classList.remove("member_search_button_active");
    };

    show_user_email_ajax.onerror = function(error, url, info)
    {
      if(show_email_button) show_email_button.classList.remove("member_search_button_active");

      Forum.handle_ajax_error(this, error, url, info);
    };
  }

  show_user_email_ajax.abort();
  show_user_email_ajax.resetParams();

  show_user_email_ajax.setPOST('show_user_email', "1");
  show_user_email_ajax.setPOST('uid', "<?php echo_js(reqvar("uid")); ?>");
  show_user_email_ajax.setPOST('hash', get_protection_hash());
  show_user_email_ajax.setPOST('user_logged', user_logged);

  show_user_email_ajax.request("ajax/process.php");

  return false;
} // show_user_email

function confirm_action(msg, params)
{
  var mbuttons = [
    {
      caption: msg_Yes,
      handler: function() {
        Forum.hide_user_msgbox();
        do_action(params);
      }
    },
    {
      caption: msg_No,
      handler: function() {
        Forum.hide_user_msgbox();
      }
    }
  ];

  Forum.show_user_msgbox(msg_Confirmation, msg, 'icon-question.gif', mbuttons);

  return false;
}

function confirm_action_with_comment(msg, params)
{
  var mbuttons = [
    {
      caption: msg_Yes,
      handler: function() {
        Forum.hide_user_msgbox();
        
        var elm = document.getElementById("sys_user_textarea");
        if(elm) params["comment"] = elm.value;
        
        do_action(params);
      }
    },
    {
      caption: msg_No,
      handler: function() {
        Forum.hide_user_msgbox();
      }
    }
  ];

  Forum.show_user_textarea(msg_Confirmation, msg, '', 'icon-question.gif', mbuttons, 250);

  return false;
}

var action_ajax = null;

function do_action(params)
{
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
          if(response.target_url)
          {
            delay_redirect(response.target_url);
            return;
          }
          
          if(response.convert_action_link) convert_action_link(response.convert_action_link, this.params);
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

  action_ajax.params = params;

  for(var p in params)
  {
    if(!Object.prototype.hasOwnProperty.call(params, p)) continue;
    
    action_ajax.setPOST(p, params[p]);
  }

  action_ajax.setPOST('hash', get_protection_hash());
  action_ajax.setPOST('user_logged', user_logged);

  action_ajax.request("ajax/process.php");

  return false;
}

function convert_action_link(target, params)
{
  var elm;

  if(target == "put_to_ignore_list")
  {
    elm = document.getElementById("ignore_action");
    if(elm)
    {
      elm.innerHTML = "<?php echo_js(text("PutToIgnoreList")); ?>";
      elm.onclick = function (event) { return confirm_action_with_comment("<?php echo_js(sprintf(text("MsgConfirmUserIgnore"), $user_data["user_name"])); ?>", { ignore_action: "put_to_ignore_list", uid: "<?php echo_js(reqvar("uid")); ?>" }); }
    }
  }

  if(target == "remove_from_ignore_list")
  {
    elm = document.getElementById("ignore_action");
    if(elm)
    {
      elm.innerHTML = "<?php echo_js(text("RemoveFromIgnoreList")); ?>";
      elm.onclick = function (event) { return do_action({ ignore_action: "remove_from_ignore_list", uid: "<?php echo_js(reqvar("uid")); ?>" }); }
    }
  }
  
  if(target == "hide_user_profile")
  {
    elm = document.getElementById("profile_hide_action");
    if(elm)
    {
      elm.innerHTML = "<?php echo_js(text("HideProfile")); ?>";
      elm.onclick = function (event) { return confirm_action("<?php echo_js(sprintf(text("MsgConfirmProfileHide"), $user_data["user_name"])); ?>", { profile_hide_action: "hide_user_profile", uid: "<?php echo_js(reqvar("uid")); ?>" }); }
    }
  }

  if(target == "open_user_profile")
  {
    elm = document.getElementById("profile_hide_action");
    if(elm)
    {
      elm.innerHTML = "<?php echo_js(text("OpenProfile")); ?>";
      elm.onclick = function (event) { return do_action({ profile_hide_action: "open_user_profile", uid: "<?php echo_js(reqvar("uid")); ?>" }); }
    }
  }

  if(target == "subscribe_to_user")
  {
    elm = document.getElementById("subscribe_action");
    if(elm)
    {
      elm.innerHTML = "<?php echo_js(text("SubscribeToUser")); ?>";
      elm.onclick = function (event) { return do_action({ subscribe_action: "subscribe_to_user", uid: "<?php echo_js(reqvar("uid")); ?>", user_name: "<?php echo_js($user_data["user_name"]); ?>" }); }
    }
  }

  if(target == "unsubscribe_from_user")
  {
    elm = document.getElementById("subscribe_action");
    if(elm)
    {
      elm.innerHTML = "<?php echo_js(text("UnsubscribeFromUser")); ?>";
      elm.onclick = function (event) { return do_action({ subscribe_action: "unsubscribe_from_user", uid: "<?php echo_js(reqvar("uid")); ?>", user_name: "<?php echo_js($user_data["user_name"]); ?>" }); }
    }
  }
}

function expand_statistics_list(elm)
{
  var parent_table = elm.parentNode;
  if(!parent_table) return;
  parent_table = parent_table.parentNode;
  if(!parent_table) return;
  parent_table = parent_table.parentNode;
  if(!parent_table) return;
  
  var elms = parent_table.getElementsByClassName("statistics_row_hidden");
  for(var i = elms.length-1; i >= 0; i--)
  {
    elms[i].classList.remove("statistics_row_hidden");
  }
  
  elm = elm.parentNode;
  if(elm) elm = elm.parentNode;
  if(elm) elm = elm.style.display = "none";
}
</script>

<!-- BEGIN: forum_bar -->

<div class="forum_bar">
<a href="forums.php"><?php echo_html(text("Forums")); ?></a> 

<?php
$display = "style='display:none'";
if(!empty($topics_with_new_count)) $display = "";
?>
<span class="new topics_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span> 

/ <a href="users.php"><?php echo_html(text("Users")); ?></a> 

<?php if(reqvar("uid") == $fmanager->get_user_id()): ?>
/ <a href="profile.php"><?php echo_html(text("ProfileSettings")); ?></a> 
<?php endif; ?>

/ <span class="topic_title_main"><?php echo_html(text("UserProfile")); ?><span>
</div>

<!-- END: forum_bar -->

<table class="form_table profile_table profile_view_table" style="margin-bottom: 0px">

<tr>
<th><?php echo_html(text("UserProfile")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("UserName")); ?>:</td>
</tr>
<tr>
  <?php
  $online_status = "";
  if(empty($settings["hide_online_status"]) && !empty($user_data["online"]))
  {
    $online_status = "&nbsp;<span class='online_text'>✓</span>";
  }
  ?>
<td><div class="smart_break"><span class="number"><?php echo_html($user_data["user_name"]); ?></span><?php echo($online_status); ?></div></td>
</tr>

<tr>
<td></td>
</tr>

<tr>
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
<td></td>
</tr>

<tr>
<td><span class="number"><?php echo_html(text("Email")); ?>:</span></td>
</tr>
<tr>
<td id="user_email">
<div class="smart_break">
<?php if($fmanager->demo_mode()): ?>
<?php echo_html(text("hidden")); ?>
<?php elseif($fmanager->is_admin()): ?>
<?php echo_html($user_data["user_email"]); ?>
<?php elseif(!empty($user_data["hide_email"])): ?>
<?php echo_html(text("hidden")); ?>
<?php else: ?>
<input type="button" id="show_email_button" class="standard_button member_search_button" value="<?php echo_html(text("Show")); ?>" onclick="show_user_email()">
<?php endif; ?>
</div>
</td>
</tr>

<tr>
<td></td>
</tr>

<tr>
<td><span class="number"><?php echo_html(text("Status")); ?>:</span></td>
</tr>
<tr>
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
<td></td>
</tr>

<tr>
<td><?php echo_html(text("RegistrationDate")); ?>: <span class="number"><?php echo_html(smart_date($user_data["registration_date"])); ?></span></td>
</tr>

<tr>
<td></td>
</tr>

<?php if(empty($settings["hide_online_status"])): ?>
<tr>
<td><?php echo_html(text("LastActivity")); ?>: <span class="number"><?php echo_html($user_data["last_visit_date"]); ?></span></td>
</tr>

<tr>
<td></td>
</tr>
<?php endif; ?>

<tr>
<td><?php echo_html(text("MessagesCount")); ?>: <span class="number"><?php echo_html(format_number($user_data["post_count"])); ?></span></td>
</tr>

<tr>
<td></td>
</tr>

<tr>
<td><?php echo_html(text("TopicsCount")); ?>: <span class="number"><?php echo_html(format_number($user_data["topic_count"])); ?></span></td>
</tr>

<tr>
<td></td>
</tr>

<tr>
<td><?php echo_html(text("MessagesPerDay")); ?>: <span class="number"><?php echo_html(format_number($user_data["week_post_count"])); ?></span>
 <span style="color: gray" class="field_comment">(<?php echo_html(text("OverLastWeek")); ?>)</span>
</td>
</tr>

<tr>
<td></td>
</tr>

<tr>
<td><?php echo_html(text("HitsPerDay")); ?>: <span class="number"><?php echo_html(format_number($user_data["week_view_count"])); ?></span>
 <span style="color: gray" class="field_comment">(<?php echo_html(text("OverLastWeek")); ?>)</span>
</td>
</tr>

<tr>
<td></td>
</tr>

<tr>
<td><?php echo_html(text("TimeOnlinePerDay")); ?>: <span class="number"><?php echo_html(format_duration($user_data["week_time_online"])); ?></span>
 <span style="color: gray" class="field_comment">(<?php echo_html(text("OverLastWeek")); ?>)</span>
</td>
</tr>

<tr>
<td></td>
</tr>

<tr>
<td><?php echo_html(text("TimeOnlineLast24Hours")); ?>: <span class="number"><?php echo_html(format_duration($user_data["today_time_online"])); ?></span>
</td>
</tr>

<tr>
<td></td>
</tr>

<tr>
<td><?php echo_html(text("TimeOnlineTotal")); ?>: <span class="number"><?php echo_html(format_duration($user_data["time_online"])); ?></span>
</td>
</tr>

<?php if(!empty($settings["rates_active"])): ?>

<tr>
<td></td>
</tr>

<tr>
<td><?php echo_html(text("Rating")); ?>: <a href="search_topic.php?do_search=1&post_sort=desc&author_mode=author_liked&author=<?php echo(xrawurlencode($user_data["user_name"])); ?>" class="carma_plus"><?php echo_html($user_data["carma_plus"]); ?></a>
<?php if(!empty($settings["dislikes_active"])): ?>
/ <a href="search_topic.php?do_search=1&post_sort=desc&author_mode=author_disliked&author=<?php echo(xrawurlencode($user_data["user_name"])); ?>" class="carma_minus"><?php echo_html($user_data["carma_minus"]); ?></a>
<?php endif; ?>
</td>
</tr>

<tr>
<td><?php echo_html(text("Weighed")); ?>: <a href="search_topic.php?do_search=1&post_sort=desc&author_mode=author_liked&author=<?php echo(xrawurlencode($user_data["user_name"])); ?>" class="carma_plus"><?php echo_html(format_number($user_data["carma_plus_weighed"], 1)); ?></a>
<?php if(!empty($settings["dislikes_active"])): ?>
/ <a href="search_topic.php?do_search=1&post_sort=desc&author_mode=author_disliked&author=<?php echo(xrawurlencode($user_data["user_name"])); ?>" class="carma_minus"><?php echo_html(format_number($user_data["carma_minus_weighed"], 1)); ?></a>
<?php endif; ?>
</td>
</tr>

<?php endif; ?>

<tr>
<td></td>
</tr>

<?php if($fmanager->is_admin() || !empty($user_data["my_profile"])): ?>

<tr>
<td><?php echo_html(text("RegistrationIPAddress")); ?>:

<?php
if($fmanager->demo_mode())
{
  $user_data["ip"] = "127.0.0.1";
}

$ip = escape_html($user_data["ip"]);
if(!empty($ip))
{
  if(!empty($settings["whois_server"]))
  {
    $url = str_ireplace("{ip}", $ip, str_replace("&", "&", $settings["whois_server"]));
    $ip = "<a href='$url' target='_blank'>" . $ip . "</a>";
  }

  if($fmanager->is_admin())
  {
    $ip_sign = "✘";
    $ip_class = "ip_moderation";
    if(!empty($user_data["ip_blocked"]))
    {
      $ip_sign = "✘";
      $ip_class = "ip_moderation ip_blocked";
    }
    $ip .= "&nbsp;<a href='ip_moderation.php?type=moderation&ip=" . xrawurlencode($user_data["ip"]) . "' class='moderator_link $ip_class' title='" . escape_html(text("Moderate")) . "'>$ip_sign</a>";

    $ip_sign = "✓";
    $ip_class = "guest_ip";
    if(!empty($user_data["guest_ip_whitelisted"]))
    {
      $ip_sign = "✓";
      $ip_class = "ip_whitelisted";
    }
    $ip .= "&nbsp;<a href='guest_ips.php?search_key=" . xrawurlencode($user_data["ip"]) . "' class='ip_moderation $ip_class' title='" . escape_html(text("GuestIPs")) . "'>$ip_sign</a>";

    if(!empty($user_data["tor_ip"]))
    {
      $ip_class = "ip_moderation " . val_or_empty($user_data["tor_ip_block_level"]);
      $ip_sign = "Tor";
      $ip .= "&nbsp;<a href='tor_ips.php?search_key=" . xrawurlencode($user_data["ip"]) . "' class='moderator_link $ip_class' title='" . escape_html(text("Moderate")) . "'>$ip_sign</a>";
    }

    $ip .= "&nbsp;<a href='ip_moderation.php?type=ip_users&ip=" . xrawurlencode($user_data["ip"]) . "' title='" . escape_html(text("Authors")) . "'><img src='" . $view_path . "images/users.png' alt='" . escape_html(text("Authors")) . "' class='ip_authors'></a>";
  } else {
    $aname_appendix = "";
    if(!empty($user_data["aname"]) && $user_data["aname"] != "admin")
      $aname_appendix .= "&aname=" . $user_data["aname"];
    
    $ip .= "&nbsp;<a href='ip_moderation.php?type=user_ips&user=" . xrawurlencode($user_data["user_name"]) . "' title='" . escape_html(text("ShowAuthorIPs")) . "'><img src='" . $view_path . "images/ips.png' alt='" . escape_html(text("ShowAuthorIPs")) . "' class='author_ips'></a>";
  }
}

echo $ip;
?>
</td>
</tr>

<tr>
<td></td>
</tr>

<tr>
<td><?php echo_html(text("LastIPAddress")); ?>:

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

  if($fmanager->is_admin())
  {
    $ip_sign = "✘";
    $ip_class = "ip_moderation";
    if(!empty($user_data["last_ip_blocked"]))
    {
      $ip_sign = "✘";
      $ip_class = "ip_moderation ip_blocked";
    }
    $ip .= "&nbsp;<a href='ip_moderation.php?type=moderation&ip=" . xrawurlencode($user_data["last_ip"]) . "' class='moderator_link $ip_class' title='" . escape_html(text("Moderate")) . "'>$ip_sign</a>";

    $ip_sign = "✓";
    $ip_class = "guest_ip";
    if(!empty($user_data["last_ip_whitelisted"]))
    {
      $ip_sign = "✓";
      $ip_class = "ip_whitelisted";
    }
    $ip .= "&nbsp;<a href='guest_ips.php?search_key=" . xrawurlencode($user_data["last_ip"]) . "' class='ip_moderation $ip_class' title='" . escape_html(text("GuestIPs")) . "'>$ip_sign</a>";

    if(!empty($user_data["last_tor_ip"]))
    {
      $ip_class = "ip_moderation " . val_or_empty($user_data["last_tor_ip_block_level"]);
      $ip_sign = "Tor";
      $ip .= "&nbsp;<a href='tor_ips.php?search_key=" . xrawurlencode($user_data["last_ip"]) . "' class='moderator_link $ip_class' title='" . escape_html(text("Moderate")) . "'>$ip_sign</a>";
    }

    $ip .= "&nbsp;<a href='ip_moderation.php?type=ip_users&ip=" . xrawurlencode($user_data["last_ip"]) . "' title='" . escape_html(text("Authors")) . "'><img src='" . $view_path . "images/users.png' alt='" . escape_html(text("Authors")) . "' class='ip_authors'></a>";
  } else {
    $aname_appendix = "";
    if(!empty($user_data["aname"]) && $user_data["aname"] != "admin")
      $aname_appendix .= "&aname=" . $user_data["aname"];
    
    $ip .= "&nbsp;<a href='ip_moderation.php?type=user_ips&user=" . xrawurlencode($user_data["user_name"]) . "' title='" . escape_html(text("ShowAuthorIPs")) . "'><img src='" . $view_path . "images/ips.png' alt='" . escape_html(text("ShowAuthorIPs")) . "' class='author_ips'></a>";
  }
}

echo $ip;
?>
</td>
</tr>

<tr>
<td></td>
</tr>

<tr>
<td><?php echo_html(text("StrongIgnore")); ?>: <span class="number"><?php echo_html(empty($user_data["hide_ignored"]) ? text("No") : text("Yes")); ?></span>
</td>
</tr>


<?php endif; ?>

<?php if(!empty($user_data["location"])): ?>
<tr>
<td></td>
</tr>

<tr>
<td><span class="number"><?php echo_html(text("Location")); ?>:</span></td>
</tr>
<tr>
<td><div class="smart_break"><?php echo_html($user_data["location"]); ?></div></td>
</tr>
<?php endif; ?>

<?php if(!empty($user_data["homepage"])): ?>
<tr>
<td></td>
</tr>

<tr>
<td><?php echo_html(text("Homepage")); ?>:</td>
</tr>
<tr>
<?php
$homepage = $user_data["homepage"];
if(!empty($homepage)) $homepage = "<a href='" . $homepage . "' target='_blank'>" . escape_html($homepage) . "</a>";
?>
<td><div class="smart_break"><?php echo($homepage); ?></div></td>
</tr>
<?php endif; ?>

<?php if(!empty($user_data["info"])): ?>
<tr>
<td></td>
</tr>

<tr>
<td><?php echo_html(text("Information")); ?>:</td>
</tr>
<tr>
<td class="detailed_user_info"><div class="smart_break"><?php echo($user_data["info_formatted"]); ?></div></td>
</tr>
<?php endif; ?>

<tr>
<td><a id="notes"></td>
</tr>

<?php if($fmanager->is_logged_in() && !$fmanager->is_master_admin() && empty($user_data["my_profile"])): ?>

<tr class="my_notes">
<td><?php echo_html(text("Notes")); ?>: <div class="edit_notes" title="<?php echo_html(text("Edit")); ?>" onclick='Forum.show_notes_editor("<?php echo_js(text("Notes")); ?>", "my_notes", "<?php echo_js(reqvar("uid")); ?>", 250)'></div></td>
</tr>
<tr class="my_notes">
<td><div class="smart_break" id="my_notes" data-notes="<?php echo_html($user_data["my_notes_bb"]); ?>"><?php echo($user_data["my_notes"]); ?></div></td>
</tr>

<tr class="my_notes">
<td></td>
</tr>
<?php endif; ?>

<tr>
<td><span class="number"><?php echo_html(text("Actions")); ?>:</span></td>
</tr>

<?php if($fmanager->is_moderator_log_visible() || $fmanager->get_user_id() == reqvar("uid")): ?>
<tr>
<td><a href="user_moderation.php?uid=<?php echo_html($user_data["id"]); ?>#log"><?php echo_html(text("ModeratorLog")); ?></a></td>
</tr>
<?php endif; ?>

<tr>
<td><a href="search.php?do_search=1&author=<?php echo_html(xrawurlencode($user_data["user_name"])); ?>&author_mode=created_topic"><?php echo_html(text("AllAuthorTopics")); ?></a></td>
</tr>

<tr>
<td><a href="search.php?do_search=1&author=<?php echo_html(xrawurlencode($user_data["user_name"])); ?>&author_mode=participating"><?php echo_html(text("AllTopicsWithAuthor")); ?></a></td>
</tr>

<tr>
<td><a href="search_topic.php?do_search=1&author=<?php echo_html(xrawurlencode($user_data["user_name"])); ?>&author_mode=last_posts"><?php echo_html(text("AuthorLastMessages")); ?></a></td>
</tr>

<tr>
<td><a href="search_topic.php?do_search=1&author=<?php echo_html(xrawurlencode($user_data["user_name"])); ?>&author_mode=last_topics"><?php echo_html(text("AuthorLastTopics")); ?></a></td>
</tr>

<tr>
<td><a href="search_topic.php?do_search=1&author=<?php echo_html(xrawurlencode($user_data["user_name"])); ?>&has_attachment=1&author_mode=last_posts"><?php echo_html(text("AuthorLastAttachments")); ?></a></td>
</tr>

<tr>
<td><a href="search.php?do_search=1&author=<?php echo_html(xrawurlencode($user_data["user_name"])); ?>&author_mode=ignoring"><?php echo_html(text("IgnoredTopics")); ?></a></td>
</tr>

<tr>
<td><a href="search.php?do_search=1&author=<?php echo_html(xrawurlencode($user_data["user_name"])); ?>&author_mode=moderating"><?php echo_html(text("ModeratedTopics")); ?></a></td>
</tr>

<?php if(empty($settings["archive_mode"]) && $fmanager->is_logged_in() && !$fmanager->is_master_admin() && empty($user_data["my_profile"]) && empty($user_data["no_private_messages"]) && empty($user_data["ignoring_me"])): ?>
<tr>
<td><a href="new_topic.php?fid=private&receiver=<?php echo_html($user_data["id"]); ?>"><?php echo_html(text("SendPersonalMessage")); ?></a></td>
</tr>
<?php endif; ?>

<?php if(empty($settings["archive_mode"]) && $fmanager->is_logged_in() && !$fmanager->is_master_admin() && empty($user_data["my_profile"])): ?>
<tr>
<td>
<?php if(empty($user_data["subscribed"])): ?>
<a id="subscribe_action" href="#" onclick='return do_action({ subscribe_action: "subscribe_to_user", uid: "<?php echo_js(reqvar("uid")); ?>", user_name: "<?php echo_js($user_data["user_name"]); ?>" });'><?php echo_html(text("SubscribeToUser")); ?></a>
<?php else: ?>
<a id="subscribe_action" href="#" onclick='return do_action({ subscribe_action: "unsubscribe_from_user", uid: "<?php echo_js(reqvar("uid")); ?>", user_name: "<?php echo_js($user_data["user_name"]); ?>" });'><?php echo_html(text("UnsubscribeFromUser")); ?></a>
<?php endif; ?>
</td>
</tr>
<?php endif; ?>

<?php if(empty($user_data["my_profile"])): ?>

<tr>
<td></td>
</tr>

<tr>
<td>
<?php if(empty($user_data["ignored"])): ?>
<a id="ignore_action" href="#" class="moderator_link" onclick='return confirm_action_with_comment("<?php echo_js(text("MsgConfirmUserIgnore"), true); ?>".replace(/%s/, "<?php echo_js($user_data["user_name"], true); ?>"), { ignore_action: "put_to_ignore_list", uid: "<?php echo_js(reqvar("uid")); ?>" });'><?php echo_html(text("PutToIgnoreList")); ?></a>
<?php else: ?>
<a id="ignore_action" href="#" class="moderator_link" onclick='return do_action({ ignore_action: "remove_from_ignore_list", uid: "<?php echo_js(reqvar("uid")); ?>" });'><?php echo_html(text("RemoveFromIgnoreList")); ?></a>
<?php endif; ?>
</td>
</tr>

<?php if(empty($user_data["hidden"])): ?>

<tr>
<td>
<?php if(empty($user_data["hidden_by_me"])): ?>
<a id="profile_hide_action" href="#" class="moderator_link" onclick='return confirm_action("<?php echo_js(text("MsgConfirmProfileHide"), true); ?>".replace(/%s/, "<?php echo_js($user_data["user_name"], true); ?>"), { profile_hide_action: "hide_user_profile", uid: "<?php echo_js(reqvar("uid")); ?>" });'><?php echo_html(text("HideProfile")); ?></a>
<?php else: ?>
<a id="profile_hide_action" href="#" class="moderator_link" onclick='return do_action({ profile_hide_action: "open_user_profile", uid: "<?php echo_js(reqvar("uid")); ?>" });'><?php echo_html(text("OpenProfile")); ?></a>
<?php endif; ?>
</td>
</tr>
<?php endif; ?>

<?php else: ?>

<tr>
<td></td>
</tr>

<tr>
<td><a class="moderator_link" href="profile.php"><?php echo_html(text("ProfileSettings")); ?></a></td>
</tr>

<tr>
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






<?php if($fmanager->is_moderator()): ?>

<tr>
<td></td>
</tr>

<tr>
<td>
<span class="number"><?php echo_html($fmanager->is_admin() ? text("Administrator") : text("Moderator")); ?>:</span></td>
</tr>
<tr>
<td>
<a href="user_moderation.php?uid=<?php echo_html($user_data["id"]); ?>" class="moderator_link"><?php echo_html(text("ModerateUser")); ?></a>
</td>
</tr>

<?php endif; ?>

<?php if($fmanager->is_admin()): ?>

<tr>
<td>
<a href="edit_user.php?uid=<?php echo_html($user_data["id"]); ?>" class="moderator_link"><?php echo_html(text("EditUser")); ?></a>
</td>
</tr>

<?php endif; ?>

<?php if($fmanager->is_moderator()): ?>

<?php if(!empty($settings["rates_active"])): ?>
<tr>
<td>
<a href="rate_moderation.php?uid=<?php echo_html($user_data["id"]); ?>#moderation" class="moderator_link"><?php echo_html(text("ModerateRates")); ?></a>
</td>
</tr>
<?php endif; ?>

<?php endif; ?>

<?php if($fmanager->is_moderator() && $fmanager->may_see_ip()): ?>

<tr>
<td>
<a href="ip_moderation.php?type=user_ips&user=<?php echo(xrawurlencode($user_data["user_name"])); ?>" class="moderator_link"><?php echo_html(text("ShowAuthorIPs")); ?></a>
</td>
</tr>

<tr>
<td>
<a href="ip_moderation.php?type=other_users&user=<?php echo(xrawurlencode($user_data["user_name"])); ?>" class="moderator_link"><?php echo_html(text("ShowMembersOfAuthorIPs")); ?></a>
</td>
</tr>

<?php endif; ?>







<tr>
<td></td>
</tr>

</table>

<?php
if(!empty($user_data["photo"])):

$appendix = "?rnd=$rnd";
if(!empty($user_data["photo_ctime"])) $appendix = "?ctime=" . $user_data["photo_ctime"];

$user_data["photo"] .= $appendix;
?>

<h3 class="profile_caption"><?php echo_html(text("Photo")); ?></h2>

<div style="text-align: center">
<div id="photo_container" class="photo_profile_container">
<a href="<?php echo_html($user_data["photo"]); ?>" class="lightbox_image" target='_blank' title="<?php echo_html(text("Photo")); ?>: <?php echo_html($user_data["user_name"]); ?>"><img src="<?php echo_html($user_data["photo"]); ?>" alt="<?php echo_html(text("Photo")); ?>: <?php echo_html($user_data["user_name"]); ?>"></a>
</div>
</div>

<?php endif; ?>

<?php 
$rowcount = count($read_topics);
if($rowcount > 0 && empty($settings["hide_online_status"])): 
$i = 0;
$row_class = "";
?>

<h3 class="profile_caption"><?php echo_html(text("ReadTopics")); ?></h2>

<table class="topic_statistic_table">

<tr>
<th><?php echo_html(text("Topic")); ?></th>
<th><?php echo_html(text("Forum")); ?></th>
<th><?php echo_html(text("DateTime")); ?></th>
</tr>

<?php foreach($read_topics as $tinfo):
$not_preferred = "";
if(!empty($_SESSION["preferred_forums"]) && empty($_SESSION["preferred_forums"][$tinfo["fid"]])) $not_preferred = "not_preferred";
?>

<tr class="<?php echo($row_class); ?>">
<td><div class="smart_break"><a href="topic.php?fid=<?php echo_html($tinfo["fid"]); ?>&tid=<?php echo_html($tinfo["tid"]); ?>&gotonew=1" rel="nofollow"><?php echo_html(postprocess_message($tinfo["name"])); ?></a></div></td>
<td><a href="forum.php?fid=<?php echo_html($tinfo["fid"]); ?>" class="<?php echo($not_preferred); ?>"><?php echo_html($tinfo["forum_name"]); ?></a></td>
<td class="date_col"><?php echo_html($tinfo["dt"]); ?></td>
</tr>

<?php if($rowcount > 25 && $i == 20): 
$row_class = "statistics_row_hidden";
?>
<tr>
<td><div class="statistics_list_expander" onclick="expand_statistics_list(this)">...</div></td>
<td></td>
<td></td>
</tr>
<?php endif; ?>

<?php 
$i++;
endforeach; 
?>

</table>

<?php endif; ?>

<h3 class="profile_caption"><?php echo_html(text("ForumActivity")); ?></h2>

<table class="forum_statistic_table">

<tr>
<th><?php echo_html(text("Forum")); ?></th>
<th colspan="3"><?php echo_html(text("Messages")); ?></th>
</tr>

<?php if($total_post_count == 0): ?>

<tr>
<td colspan="4">&nbsp;</td>
</tr>

<?php else: ?>

<?php
foreach($activity_data as $fid => $info):
if($info["name"] == "#hidden_forums") $info["name"] = text("HiddenForums");
$pct = $info["cnt"] / $total_post_count;

if(!empty($fid))
{
  $not_preferred = "";
  if(!empty($_SESSION["preferred_forums"]) && empty($_SESSION["preferred_forums"][$fid])) $not_preferred = "not_preferred";
  $fname = "<a href='forum.php?fid=$fid' class='$not_preferred'>" . escape_html($info["name"]) . "</a>";
}
else
{
  $fname = escape_html($info["name"]);
}

$width = round(140 * $pct);
if($width == 0) $width = 1;

$width .= "px";
?>

<tr>
<td><?php echo($fname); ?></td>
<td><?php echo_html($info["cnt"]); ?></td>
<td><?php echo_html(format_number(100*$pct, 1)); ?> %</td>
<td><div class="statistics_bar" style="width:<?php echo($width); ?>"></div><div class="clear_both"></div></td>
</tr>

<?php endforeach; ?>

<?php endif; ?>

</table>


<h3 class="profile_caption"><?php echo_html(text("DailyActivity")); ?></h2>

<select name="user_activity_period" class="user_activity_period_select" onchange="reload_daily_activity_image(this.value)" autocomplete="off">
<?php $selected = val_or_empty($_SESSION["user_activity_period"]) == "last_month" ? "selected" : ""; ?>
<option value="last_month" <?php echo($selected); ?>><?php echo_html(text("LastMonth")); ?></option>
<?php $selected = (val_or_empty($_SESSION["user_activity_period"]) == "last_half_year" || empty($_SESSION["user_activity_period"])) ? "selected" : ""; ?>
<option value="last_half_year" <?php echo($selected); ?>><?php echo_html(text("LastHalfYear")); ?></option>
<?php $selected = val_or_empty($_SESSION["user_activity_period"]) == "last_year" ? "selected" : ""; ?>
<option value="last_year" <?php echo($selected); ?>><?php echo_html(text("LastYear")); ?></option>
<?php $selected = val_or_empty($_SESSION["user_activity_period"]) == "whole_period" ? "selected" : ""; ?>
<option value="whole_period" <?php echo($selected); ?>><?php echo_html(text("WholePeriod")); ?></option>
</select>

<div class="user_activity_image_wrapper">
<img id="user_daily_activity_image" class="user_activity_image" title="<?php echo_text("DailyActivity"); ?>" alt="&nbsp;" src="ajax/user_activity_diagram.php?type=daily&period=<?php echo_html(val_or_empty($_SESSION["user_activity_period"])); ?>&uid=<?php echo_html(reqvar("uid")); ?>&rnd=<?php echo(rand(1000, 9000)); ?>" onload="this.style.opacity = '1';">
</div>

<h3 class="profile_caption"><?php echo_html(text("WeekdayActivity")); ?></h2>

<div class="user_activity_image_wrapper">
<img id="user_weekday_activity_image" class="user_activity_image" title="<?php echo_text("WeekdayActivity"); ?>" alt="&nbsp;" src="ajax/user_activity_diagram.php?type=weekday&period=<?php echo_html(val_or_empty($_SESSION["user_activity_period"])); ?>&uid=<?php echo_html(reqvar("uid")); ?>&rnd=<?php echo(rand(1000, 9000)); ?>" onload="this.style.opacity = '1';">
</div>

<h3 class="profile_caption"><?php echo_html(sprintf(text("HourlyActivity"), 30)); ?></h2>

<select name="user_activity_time_zone" class="user_activity_period_select" onchange="reload_hourly_activity_image(this.value)">
<?php $selected = (val_or_empty($_SESSION["user_activity_time_zone"]) == "my_time_zone" || empty($_SESSION["user_activity_time_zone"])) ? "selected" : ""; ?>
<option value="my_time_zone" <?php echo($selected); ?>><?php echo_html(text("MyTimeZone")); ?></option>
<?php $selected = val_or_empty($_SESSION["user_activity_time_zone"]) == "user_time_zone" ? "selected" : ""; ?>
<option value="user_time_zone" <?php echo($selected); ?>><?php echo_html(text("AuthorTimeZone")); ?></option>
</select>

<div class="user_activity_image_wrapper">
<img id="user_hourly_activity_image" class="user_activity_image" title="<?php echo_html(sprintf(text("HourlyActivity"), 30)); ?>" alt="&nbsp;" src="ajax/user_activity_diagram.php?type=hourly&period=<?php echo_html(val_or_empty($_SESSION["user_activity_period"])); ?>&uid=<?php echo_html(reqvar("uid")); ?>&rnd=<?php echo(rand(1000, 9000)); ?>" onload="this.style.opacity = '1';">
</div>

<?php 
if(!empty($ignores) || !empty($ignored) || !empty($user_data["ignore_guests"])): 

$ignores_rows = array();
$ignored_rows = array();

foreach($ignores as $uid => $uname)
{
  $ignores_rows[] = "<a href='view_profile.php?uid=$uid'>" . escape_html($uname) . "</a>";
}

if(!empty($user_data["ignore_new_guests"]) && !(!empty($user_data["ignore_guests_whitelist"]) && empty($user_data["ignored_guests_whitelist"])))
{
    $ignores_rows[] = escape_html(text("NewGuests"));  
}

if(!empty($user_data["ignore_guests_blacklist"]))
{
  $ignored_guests = preg_split("/[\n\r]+/", $user_data["ignored_guests_blacklist"], -1, PREG_SPLIT_NO_EMPTY);
  foreach($ignored_guests as $guest)
{
    $guest = $fmanager->display_name_to_name($guest);

    if($guest == "admin")
      $ignores_rows[] = '<a class="admin_link" href="view_guest_profile.php?guest=' . xrawurlencode($guest) . '">' . escape_html(text("MasterAdministrator")) . '</a>';
    else
      $ignores_rows[] = '<a class="guest_link" href="view_guest_profile.php?guest=' . xrawurlencode($guest) . '">' . escape_html($guest) . '</a>';
  }
}

if(!empty($user_data["ignore_guests_whitelist"]))
{
  if(!empty($user_data["ignored_guests_whitelist"]))
  {
    $ignores_rows[] = escape_html(text("Guests") . ", " . text("IgnoreGuestsExcept") . ":");
    
    $ignored_guests = preg_split("/[\n\r]+/", $user_data["ignored_guests_whitelist"], -1, PREG_SPLIT_NO_EMPTY);
    foreach($ignored_guests as $guest)
    {
      $guest = $fmanager->display_name_to_name($guest);

      if($guest == "admin")
        $ignores_rows[] = '<a class="admin_link" href="view_guest_profile.php?guest=' . xrawurlencode($guest) . '">' . escape_html(text("MasterAdministrator")) . '</a>';
      else
        $ignores_rows[] = '<a class="guest_link" href="view_guest_profile.php?guest=' . xrawurlencode($guest) . '">' . escape_html($guest) . '</a>';
    }
  }
  else
  {
    $ignores_rows[] = escape_html(text("Guests"));  
  }
}

foreach($ignored as $uid => $uname)
{
  $ignored_rows[] = "<a href='view_profile.php?uid=$uid'>" . escape_html($uname) . "</a>";
}

$rowcount = max(count($ignores_rows), count($ignored_rows));
$row_class = "";
?>

<h3 class="profile_caption"><?php echo_html(text("IgnoreList")); ?></h2>

<table class="ignore_table">
<tr>
<th style="width:50%"><?php echo_html(text("MemberIgnores")); ?></th>
<th style="width:50%"><?php echo_html(text("MemberIgnored")); ?></th>
</tr>

<?php for($i = 0; $i < $rowcount; $i++): ?>
<tr class="<?php echo($row_class); ?>">
<td><div class="overflow_div wide_column"><?php if(empty($ignores_rows[$i])) echo "&nbsp;"; else echo $ignores_rows[$i]; ?></div></td>
<td><div class="overflow_div wide_column"><?php if(empty($ignored_rows[$i])) echo "&nbsp;"; else echo $ignored_rows[$i]; ?></div></td>
</td>
</tr>

<?php if($rowcount > 25 && $i == 20): 
$row_class = "statistics_row_hidden";
?>
<tr>
<td><div class="statistics_list_expander" onclick="expand_statistics_list(this)">...</div></td>
<td></td>
</tr>
<?php endif; ?>

<?php endfor; ?>

</table>

<?php endif; ?>

<?php if(!empty($hides) || !empty($hidden)): 

$hides_rows = array();
$hidden_rows = array();

foreach($hides as $uid => $uname)
{
  $hides_rows[] = "<a href='view_profile.php?uid=$uid'>" . escape_html($uname) . "</a>";
}

foreach($hidden as $uid => $uname)
{
  $hidden_rows[] = "<a href='view_profile.php?uid=$uid'>" . escape_html($uname) . "</a>";
}

$rowcount = max(count($hides_rows), count($hidden_rows));
$row_class = "";
?>

<h3 class="profile_caption"><?php echo_html(text("HideList")); ?></h2>

<table class="ignore_table">
<tr>
<th style="width:50%"><?php echo_html(text("MemberHides")); ?></th>
<th style="width:50%"><?php echo_html(text("MemberHidden")); ?></th>
</tr>

<?php for($i = 0; $i < $rowcount; $i++): ?>
<tr class="<?php echo($row_class); ?>">
<td><div class="overflow_div wide_column"><?php if(empty($hides_rows[$i])) echo "&nbsp;"; else echo $hides_rows[$i]; ?></div></td>
<td><div class="overflow_div wide_column"><?php if(empty($hidden_rows[$i])) echo "&nbsp;"; else echo $hidden_rows[$i]; ?></div></td>
</td>
</tr>

<?php if($rowcount > 25 && $i == 20): 
$row_class = "statistics_row_hidden";
?>
<tr>
<td><div class="statistics_list_expander" onclick="expand_statistics_list(this)">...</div></td>
<td></td>
</tr>
<?php endif; ?>

<?php endfor; ?>

</table>

<?php endif; ?>

<div class="user_rates_area">

<?php if(!empty($settings["rates_active"])): ?>

<h3 class="profile_caption"><?php echo_html(text("RateStatistics")); ?></h2>

<input type="button" class="standard_button load_user_rates" value="<?php echo_html(text("Show")); ?>" onclick="load_user_rates(this)">

<?php endif; // rates active ?>

</div>

<div style="margin-bottom: 70px"></div>