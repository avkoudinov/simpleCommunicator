<script type='text/JavaScript'>
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
  
  if(target == "put_guest_to_ignore_list")
  {
    elm = document.getElementById("ignore_action");
    if(elm)
    {
      elm.innerHTML = "<?php echo_js(text("PutToIgnoreList")); ?>";
      elm.onclick = function (event) { return confirm_action("<?php echo_js(sprintf(text("MsgConfirmUserIgnore"), $fmanager->get_display_name($guest_data["user_name"]))); ?>", { ignore_action: "put_guest_to_ignore_list", guest_name: "<?php echo_js($guest_data["user_name"]); ?>", guest_id: "<?php echo_html(md5($guest_data["user_name"])); ?>" }); }
    }
  }

  if(target == "remove_guest_from_ignore_list")
  {
    elm = document.getElementById("ignore_action");
    if(elm)
    {
      elm.innerHTML = "<?php echo_js(text("RemoveFromIgnoreList")); ?>";
      elm.onclick = function (event) { return do_action({ ignore_action: "remove_guest_from_ignore_list", guest_name: "<?php echo_js($guest_data["user_name"]); ?>", guest_id: "<?php echo_html(md5($guest_data["user_name"])); ?>" }); }
    }
  }
  
  if(target == "hide_guest_profile")
  {
    elm = document.getElementById("profile_hide_action");
    if(elm)
    {
      elm.innerHTML = "<?php echo_js(text("HideProfile")); ?>";
      elm.onclick = function (event) { return confirm_action("<?php echo_js(sprintf(text("MsgConfirmProfileHide"), $fmanager->get_display_name($guest_data["user_name"]))); ?>", { profile_hide_action: "hide_guest_profile", guest_name: "<?php echo_js($guest_data["user_name"]); ?>", guest_id: "<?php echo_html($guest_data["aname"]); ?>" }); }
    }
  }

  if(target == "open_guest_profile")
  {
    elm = document.getElementById("profile_hide_action");
    if(elm)
    {
      elm.innerHTML = "<?php echo_js(text("OpenProfile")); ?>";
      elm.onclick = function (event) { return do_action({ profile_hide_action: "open_guest_profile", guest_name: "<?php echo_js($guest_data["user_name"]); ?>", guest_id: "<?php echo_html($guest_data["aname"]); ?>" }); }
    }
  }

  if(target == "subscribe_to_user")
  {
    elm = document.getElementById("subscribe_action");
    if(elm)
    {
      elm.innerHTML = "<?php echo_js(text("SubscribeToUser")); ?>";
      elm.onclick = function (event) { return do_action({ subscribe_action: "subscribe_to_user", uid: "", user_name: "<?php echo_js($guest_data["user_name"]); ?>", guest_id: "<?php echo_js(md5($guest_data["user_name"])); ?>" }); }
    }
  }

  if(target == "unsubscribe_from_user")
  {
    elm = document.getElementById("subscribe_action");
    if(elm)
    {
      elm.innerHTML = "<?php echo_js(text("UnsubscribeFromUser")); ?>";
      elm.onclick = function (event) { return do_action({ subscribe_action: "unsubscribe_from_user", uid: "", user_name: "<?php echo_js($guest_data["user_name"]); ?>", guest_id: "<?php echo_js(md5($guest_data["user_name"])); ?>" }); }
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

<?php if(!empty($guest_data["my_profile"])): ?>
/ <a href="guest_profile.php"><?php echo_html(text("ProfileSettings")); ?></a> 
<?php endif; ?>

<?php
$display = "style='display:none'";
if(!empty($topics_with_new_count)) $display = "";
?>
<span class="new topics_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span> 

/ <span class="topic_title_main"><?php echo_html(text("GuestProfile")); ?></span>
</div>

<!-- END: forum_bar -->

<table class="form_table profile_table profile_view_table" style="margin-bottom: 0px">

<tr>
<th><?php echo_html(text("GuestProfile")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("UserName")); ?>:</td>
</tr>
<tr>
  <?php
  $online_status = "";
  if(empty($settings["hide_online_status"]) && !empty($online_users["g_" . $guest_data["user_name"]]))
  {
    $online_status = "&nbsp;<span class='online_text'>✓</span>";
  }

  $protected_guest = "";
  if(!empty($settings["protected_guest_list"][$guest_data["user_name"]]) && $fmanager->is_admin())
  {
    $protected_guest = "<div class='protected_guest'></div>";
  }
  ?>
<td><span class="number"><?php echo_html($fmanager->get_display_name($guest_data["user_name"])); ?></span><?php echo($protected_guest . $online_status); ?></td>
</tr>

<tr>
<td></td>
</tr>

<tr>
<td>

<?php
$rnd = rand(1000, 9000);
$picture = $view_path . "images/guest.jpg";
if(!empty($guest_data["avatar"]))
{
  $appendix = "?rnd=$rnd";
  if(!empty($guest_data["avatar_ctime"])) $appendix = "?ctime=" . $guest_data["avatar_ctime"];

  $picture = escape_html($guest_data["avatar"]) . $appendix;
}
?>

<img class="avatar_picture" src="<?php echo($picture); ?>" alt="<?php echo_html(text("Avatar")); ?>">

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
    <?php if($guest_data["user_name"] == "admin"): ?>
    <?php echo_html(text("MasterAdministrator")); ?><br>
    <?php else: ?>
    <?php echo_html(text("Guest")); ?><br>
    <?php endif; ?>

    <?php if(!empty($guest_data["hidden_by_me"])): ?>
    <br><span class="error_text"><?php echo_html(text("HiddenByMe")); ?></span>
    <?php endif; ?>

    <?php if(!empty($guest_data["my_profile"])): ?>
    <br><span class="error_text self_blocked"><?php echo_html(text("MyProfile")); ?></span>
    <?php elseif(reqvar("ignored") == 2): ?>
    <br><span class="error_text"><?php echo_html(text("Ignored")); ?>*</span>
    <?php elseif(!empty($guest_data["guest_ignored"])): ?>
    <br><span class="error_text"><?php echo_html(text("Ignored")); ?></span>
    <?php endif; ?>

</td>
</tr>

<tr>
<td></td>
</tr>

<?php if(empty($settings["hide_online_status"])): ?>
<tr>
<td><?php echo_html(text("LastActivity")); ?>: <span class="number"><?php echo_html($guest_data["last_visit_date"]); ?></span></td>
</tr>

<tr>
<td></td>
</tr>
<?php endif; ?>

<?php if($fmanager->is_admin()): ?>

<tr>
<td><?php echo_html(text("LastIPAddress")); ?>:

<?php
if($fmanager->demo_mode())
{
  $guest_data["last_ip"] = "127.0.0.1";
}

$ip = escape_html($guest_data["last_ip"]);
if(!empty($ip))
{
  if(!empty($settings["whois_server"]))
  {
    $url = str_ireplace("{ip}", $ip, str_replace("&", "&", $settings["whois_server"]));
    $ip = "<a href='$url' target='_blank'>" . $ip . "</a>";
  }

  $ip_sign = "✘";
  $ip_class = "ip_moderation";
  if(!empty($guest_data["last_ip_blocked"]))
  {
    $ip_sign = "✘";
    $ip_class = "ip_moderation ip_blocked";
  }
  $ip .= "&nbsp;<a href='ip_moderation.php?type=moderation&ip=" . xrawurlencode($guest_data["last_ip"]) . "' class='moderator_link $ip_class' title='" . escape_html(text("Moderate")) . "'>$ip_sign</a>";

  if($fmanager->is_admin())
  {
    $ip_sign = "✓";
    $ip_class = "guest_ip";
    if(!empty($guest_data["last_ip_whitelisted"]))
    {
      $ip_sign = "✓";
      $ip_class = "ip_whitelisted";
    }
    $ip .= "&nbsp;<a href='guest_ips.php?search_key=" . xrawurlencode($guest_data["last_ip"]) . "' class='ip_moderation $ip_class' title='" . escape_html(text("GuestIPs")) . "'>$ip_sign</a>";
  }

  if(!empty($guest_data["last_tor_ip"]))
  {
    $ip_class = "ip_moderation " . val_or_empty($guest_data["last_tor_ip_block_level"]);
    $ip_sign = "Tor";
    $ip .= "&nbsp;<a href='tor_ips.php?search_key=" . xrawurlencode($guest_data["last_ip"]) . "' class='moderator_link $ip_class' title='" . escape_html(text("Moderate")) . "'>$ip_sign</a>";
  }

  $ip .= "&nbsp;<a href='ip_moderation.php?type=ip_users&ip=" . xrawurlencode($guest_data["last_ip"]) . "' title='" . escape_html(text("Authors")) . "'><img src='" . $view_path . "images/users.png' alt='" . escape_html(text("Authors")) . "' class='ip_authors'></a>";
}

echo $ip;
?>
</td>
</tr>

<tr>
<td></td>
</tr>
<?php endif; ?>

<tr>
<td><span class="number"><?php echo_html(text("Actions")); ?>:</span></td>
</tr>

<?php if($fmanager->is_moderator_log_visible()): ?>
<tr>
<td><a href="moderation_log.php?user_name=<?php echo(xrawurlencode($guest_data["user_name"])); ?>"><?php echo_html(text("ModeratorLog")); ?></a></td>
</tr>
<?php endif; ?>

<tr>
<td><a href="search.php?do_search=1&author=<?php echo_html(xrawurlencode($guest_data["user_name"])); ?>&author_mode=created_topic"><?php echo_html(text("AllAuthorTopics")); ?></a></td>
</tr>

<tr>
<td><a href="search.php?do_search=1&author=<?php echo_html(xrawurlencode($guest_data["user_name"])); ?>&author_mode=participating"><?php echo_html(text("AllTopicsWithAuthor")); ?></a></td>
</tr>

<tr>
<td><a href="search_topic.php?do_search=1&author=<?php echo_html(xrawurlencode($guest_data["user_name"])); ?>&author_mode=last_posts"><?php echo_html(text("AuthorLastMessages")); ?></a></td>
</tr>

<tr>
<td><a href="search_topic.php?do_search=1&author=<?php echo_html(xrawurlencode($guest_data["user_name"])); ?>&author_mode=last_topics"><?php echo_html(text("AuthorLastTopics")); ?></a></td>
</tr>

<tr>
<td><a href="search_topic.php?do_search=1&author=<?php echo_html(xrawurlencode($guest_data["user_name"])); ?>&has_attachment=1&author_mode=last_posts"><?php echo_html(text("AuthorLastAttachments")); ?></a></td>
</tr>

<?php if($fmanager->is_logged_in() && !$fmanager->is_master_admin()): ?>
<tr>
<td>
<?php if(empty($guest_data["guest_subscribed"])): ?>
<a id="subscribe_action" href="#" onclick='return do_action({ subscribe_action: "subscribe_to_user", uid: "", user_name: "<?php echo_js($guest_data["user_name"]); ?>", guest_id: "<?php echo_html(md5($guest_data["user_name"])); ?>" });'><?php echo_html(text("SubscribeToUser")); ?></a>
<?php else: ?>
<a id="subscribe_action" href="#" onclick='return do_action({ subscribe_action: "unsubscribe_from_user", uid: "", user_name: "<?php echo_js($guest_data["user_name"]); ?>", guest_id: "<?php echo_html(md5($guest_data["user_name"])); ?>" });'><?php echo_html(text("UnsubscribeFromUser")); ?></a>
<?php endif; ?>
</td>
</tr>
<?php endif; ?>



<?php if(empty($guest_data["my_profile"])): ?>
<tr>
<td></td>
</tr>

<?php if(reqvar("ignored") != 2): ?>
<tr>
<td>
<?php if(empty($guest_data["guest_ignored"])): ?>
<a id="ignore_action" href="#" class="moderator_link" onclick='return confirm_action("<?php echo_js(text("MsgConfirmUserIgnore"), true); ?>".replace(/%s/, "<?php echo_js($fmanager->get_display_name($guest_data["user_name"]), true); ?>"), { ignore_action: "put_guest_to_ignore_list", guest_name: "<?php echo_js($guest_data["user_name"]); ?>", guest_id: "<?php echo_html(md5($guest_data["user_name"])); ?>" });'><?php echo_html(text("PutToIgnoreList")); ?></a>
<?php else: ?>
<a id="ignore_action" href="#" class="moderator_link" onclick='return do_action({ ignore_action: "remove_guest_from_ignore_list", guest_name: "<?php echo_js($guest_data["user_name"]); ?>", guest_id: "<?php echo_html(md5($guest_data["user_name"])); ?>" });'><?php echo_html(text("RemoveFromIgnoreList")); ?></a>
<?php endif; ?>
</td>
</tr>
<?php endif; ?>

<?php if(empty($_SESSION["hide_user_info"]) && !empty($guest_data["aname"]) && empty($guest_data["my_profile"])): ?>  
<tr>
<td>
<?php if(empty($guest_data["hidden_by_me"])): ?>
<a id="profile_hide_action" href="#" class="moderator_link" onclick='return confirm_action("<?php echo_js(text("MsgConfirmProfileHide"), true); ?>".replace(/%s/, "<?php echo_js($fmanager->get_display_name($guest_data["user_name"]), true); ?>"), { profile_hide_action: "hide_guest_profile", guest_name: "<?php echo_js($guest_data["user_name"]); ?>", guest_id: "<?php echo_html($guest_data["aname"]); ?>" });'><?php echo_html(text("HideProfile")); ?></a>
<?php else: ?>
<a id="profile_hide_action" href="#" class="moderator_link" onclick='return do_action({ profile_hide_action: "open_guest_profile", guest_name: "<?php echo_js($guest_data["user_name"]); ?>", guest_id: "<?php echo_html($guest_data["aname"]); ?>" });'><?php echo_html(text("OpenProfile")); ?></a>
<?php endif; ?>
</td>
</tr>
<?php endif; ?>

<?php else: ?>

<tr>
<td></td>
</tr>

<tr>
<td>
<a class="moderator_link" href="profile.php"><?php echo_html(text("ProfileSettings")); ?></a>
</td>
</tr>

<?php endif; ?>

<tr>
<td></td>
</tr>

<?php
$separator_necessary = false;
$moderator_caption = $fmanager->is_admin() ? text("Administrator") : text("Moderator");
?>

<?php if(($fmanager->is_admin() || $fmanager->global_ban_allowed()) && !empty($guest_data["aname"])): 
$separator_necessary = true;
?>

<?php if(!empty($moderator_caption)): ?>
<tr>
<td>
<span class="number"><?php echo_html($moderator_caption); ?>:</span>
</td>
</tr>
<?php 
$moderator_caption = "";
endif; 
?>

<tr>
<td>
<a href="#" class="moderator_link" onclick='return confirm_action("<?php echo_js(text("MsgConfirmAvatarDelete"), true); ?>".replace(/%s/, "<?php echo_js($fmanager->get_display_name($guest_data["user_name"]), true); ?>"), { profile_hide_action: "delete_avatar", guest_name: "<?php echo_js($guest_data["user_name"]); ?>", guest_id: "<?php echo_html($guest_data["aname"]); ?>" });'><?php echo_html(text("DeleteTheAvatar")); ?></a>
</td>
</tr>

<?php endif; ?>

<?php if($fmanager->is_moderator() && $fmanager->may_see_ip()): 
$aname_appendix = "";
if(!empty($guest_data["aname"]) && $guest_data["aname"] != "admin")
  $aname_appendix .= "&aname=" . $guest_data["aname"];

if(reqvar("ignored") == 2)
  $aname_appendix .= "&ignored=2";

$separator_necessary = true;
?>

<?php if(!empty($moderator_caption)): ?>
<tr>
<td>
<span class="number"><?php echo_html($moderator_caption); ?>:</span>
</td>
</tr>
<?php 
$moderator_caption = "";
endif; 
?>

<tr>
<td>
<a href="ip_moderation.php?type=user_ips&user=<?php echo(xrawurlencode($guest_data["user_name"])); ?><?php echo($aname_appendix); ?>" class="moderator_link"><?php echo_html(text("ShowAuthorIPs")); ?></a>
</td>
</tr>

<tr>
<td>
<a href="ip_moderation.php?type=other_users&user=<?php echo(xrawurlencode($guest_data["user_name"])); ?><?php echo($aname_appendix); ?>" class="moderator_link"><?php echo_html(text("ShowMembersOfAuthorIPs")); ?></a>
</td>
</tr>

<?php endif; ?>

<?php if(!empty($separator_necessary)): ?>
<tr>
<td></td>
</tr>
<?php endif; ?>

</table>

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


<?php if(!empty($ignored) || !empty($hidden)): 

$ignored_rows = array();
$hidden_rows = array();

foreach($ignored as $uid => $uname)
{
  $ignored_rows[] = "<a href='view_profile.php?uid=$uid'>" . escape_html($uname) . "</a>";
}

foreach($hidden as $uid => $uname)
{
  $hidden_rows[] = "<a href='view_profile.php?uid=$uid'>" . escape_html($uname) . "</a>";
}

$rowcount = max(count($ignored_rows), count($hidden_rows));
$row_class = "";
?>

<h3 class="profile_caption"><?php echo_html(text("IgnoreList")); ?> / <?php echo_html(text("HideList")); ?></h2>

<table class="ignore_table">
<tr>
<th style="width:50%"><?php echo_html(text("MemberIgnored")); ?></th>
<th style="width:50%"><?php echo_html(text("MemberHidden")); ?></th>
</tr>

<?php for($i = 0; $i < $rowcount; $i++): ?>
<tr class="<?php echo($row_class); ?>">
<td><div class="overflow_div wide_column"><?php if(empty($ignored_rows[$i])) echo "&nbsp;"; else echo $ignored_rows[$i]; ?></div></td>
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


<div style="margin-bottom: 70px"></div>