<script>
function confirm_action(elm, msg, params)
{
  var mbuttons = [
    {
      caption: msg_Yes,
      handler: function() {
        Forum.hide_user_msgbox();
        do_action(elm, params);
      }
    },
    {
      caption: msg_No,
      handler: function() {
        Forum.hide_user_msgbox();
        elm.checked = false;
      }
    }
  ];

  Forum.show_user_msgbox(msg_Confirmation, msg, 'icon-question.gif', mbuttons);

  return false;
}

var action_ajax = null;

function do_action(elm, params)
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
          if(params.tor_ip_action == 'change_block_level')
          {
            elm.setAttribute("data-old-value", elm.value);
          }
          
          if(params.tor_ip_action == 'block_all' || params.tor_ip_action == 'unblock_all')
          {
            delay_reload();
            return;
          }
        }
        else
        {
          if(params.tor_ip_action == 'block_all' || params.tor_ip_action == 'unblock_all')
          {
            elm.checked = !elm.checked;
          }
          if(params.tor_ip_action == 'change_block_level')
          {
            elm.value = elm.getAttribute("data-old-value");
          }
        }
      }
      catch(err)
      {
        Forum.handle_ajax_error(this, err.message, this.last_url, {});
        if(params.tor_ip_action == 'block_all' || params.tor_ip_action == 'unblock_all')
        {
          elm.checked = !elm.checked;
        }
        if(params.tor_ip_action == 'change_block_level')
        {
          elm.value = elm.getAttribute("data-old-value");
        }
      }

      Forum.show_sys_progress_indicator(false);
    };

    action_ajax.onerror = function(error, url, info)
    {
      Forum.show_sys_progress_indicator(false);
      if(params.tor_ip_action == 'block_all' || params.tor_ip_action == 'unblock_all')
      {
        elm.checked = !elm.checked;
      }
      if(params.tor_ip_action == 'change_block_level')
      {
        elm.value = elm.getAttribute("data-old-value");
      }

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
  action_ajax.setPOST('trace_sql', trace_sql);

  action_ajax.request("ajax/process.php");

  return false;
}
</script>

<!-- BEGIN: header2 -->

<div class="header2">

<form action="tor_ips.php" method="get">
<table class="aux_table" style="width:100%">
<tr>
<td>
  <input type="text" class="search_field" id="search_key" name="search_key" autocomplete="off" placeholder="<?php echo_html(text("SearchIPAddress")); ?>" value="<?php echo_html(reqvar("search_key")); ?>">
</td>
<td style="width:1%; white-space: nowrap">
<input type="submit" class="standard_button search_button" value="<?php echo_html(text("DoSearch")); ?>"><?php if(!reqvar_empty("search_key")): ?><input type="submit" class="standard_button search_button" value="<?php echo_html(text("Reset")); ?>" onclick="this.form.elements['search_key'].value=''"><?php endif; ?>
</td>
</tr>
</table>
</form>

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

/ <a href="moderation.php"><?php echo_html(text("Moderation")); ?></a>

/ <a href="rm_moderation.php"><?php echo_html(text("ReadmarkerModeration")); ?></a>

/ <a href="user_agents.php"><?php echo_html(text("UserAgents")); ?></a>

/ <a href="guest_ips.php"><?php echo_html(text("GuestIPs")); ?></a>

/ <span class="topic_title_main"><?php echo_html(text("TorIPs")); ?></span>
</div>

<!-- END: forum_bar -->


<table class="form_table profile_table profile_view_table">

<tr>
<th><?php echo_html(text("TorIPs")); ?></th>
</tr>

<tr>
<td style="padding-top:0px">
   <table class="checkbox_table">
  <?php if(empty($settings["block_tor_ips"])): ?>
   <tr>
     <td>
  <input type="checkbox" id="block_unblock" name="block_unblock" onchange='confirm_action(this, "<?php echo_js(text("MsgConfirmBlockTorWrite"), true); ?>", { tor_ip_action: "block_all" })'> 
     </td>
     <td>
  <label for="block_unblock"><?php echo_html(text("Block")); ?></label>
     </td>
   </tr>
  <?php else: ?>
   <tr>
     <td>
  <input type="checkbox" id="block_unblock" name="block_unblock" onchange="return do_action(this, { tor_ip_action: 'unblock_all' })"> 
     </td>
     <td>
  <label for="block_unblock"><?php echo_html(text("Unblock")); ?></label>
     </td>
   </tr>
  <?php endif; ?>
   </table>
</td>
</tr>

<tr>
<td></td>
</tr>
 
</table>

<table class="ip_table">
<tr>
<th><?php echo_html(text("IPAddress")); ?></th>
</tr>

<?php if(empty($tor_ips)): ?>

<tr>
<td class="table_message"><?php echo_html(text("NoData")); ?></td>
</tr>

<?php else: ?>

<?php foreach($tor_ips as $ipfno): ?>

<tr>
<td>

<div class="smart_break">
<?php
$ip = escape_html($ipfno["ip"]);
if(!empty($settings["whois_server"]))
{
  $url = str_ireplace("{ip}", $ip, $settings["whois_server"]);

  $ip = "<a href='$url' target='_blank'>" . $ip . "</a>";
}

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
}

if(!empty($ipfno["tor_ip"]))
{
  $ip_class = "ip_moderation " . val_or_empty($ipfno["tor_ip_block_level"]);
  $ip_sign = "Tor";
  $ip .= "&nbsp;<a href='tor_ips.php?search_key=" . xrawurlencode($ipfno["ip"]) . "' class='moderator_link $ip_class' title='" . escape_html(text("Moderate")) . "'>$ip_sign</a>";
}

$ip .= "&nbsp;<a href='ip_moderation.php?type=ip_users&ip=" . xrawurlencode($ipfno["ip"]) . "' title='" . escape_html(text("Authors")) . "'><img src='" . $view_path . "images/users.png' alt='" . escape_html(text("Authors")) . "' class='ip_authors'></a>";

echo($ip);
?>
</div>


<div class="forum_info">
<?php echo_html(text("FirstMessage")); ?>: <span class="number"><?php echo_html($ipfno["first_message"]); ?></span><br>
<?php echo_html(text("LastMessage")); ?>: <span class="number"><?php echo_html($ipfno["last_message"]); ?></span><br>
<?php echo_html(text("Messages")); ?>: <a class="message_count" href="search.php?author_mode=wrote_post&ip=<?php echo(xrawurlencode($ipfno["ip"])); ?>&post_list=1&do_search=1&post_sort=desc"><span class="number"><?php echo_html(format_number($ipfno["cnt"])); ?></span></a><br>

  <?php echo_html(text("Action")); ?>: 
  <select class="tor_ip_action" data-old-value="<?php echo_html($ipfno["block_level"]); ?>" onchange="do_action(this, { tor_ip_action: 'change_block_level', ip: '<?php echo_js($ipfno["ip"]); ?>', level: this.value })">
  <?php $selected = val_or_empty($ipfno["block_level"]) == "0" ? "selected" : ""; ?>
  <option value="0" <?php echo($selected); ?>>-</option>
  <?php $selected = val_or_empty($ipfno["block_level"]) == "1" ? "selected" : ""; ?>
  <option value="1" <?php echo($selected); ?>><?php echo_html(text("PermissionBlockWrite")); ?></option>
  <?php $selected = val_or_empty($ipfno["block_level"]) == "2" ? "selected" : ""; ?>
  <option value="2" <?php echo($selected); ?>><?php echo_html(text("PermissionBlockRead")); ?></option>
  <?php $selected = val_or_empty($ipfno["block_level"]) == "3" ? "selected" : ""; ?>
  <option value="3" <?php echo($selected); ?>><?php echo_html(text("PermissionAllow")); ?></option>
  </select>

  <div class="navigation_arrows_right">
  <div class="scroll_up" onclick="window.scrollTo(0, 0);"></div>
  <div class="scroll_down" onclick="window.scrollTo(0, 1000000);"></div>
  </div>
</div>
</td>
</tr>

<?php endforeach; ?>

<?php endif; ?>

</table>

<?php if(count($tor_ips) > 2): ?>

<!-- BEGIN: forum_bar -->

<div class="forum_bar">
<a href="forums.php"><?php echo_html(text("Forums")); ?></a>

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

/ <a href="moderation.php"><?php echo_html(text("Moderation")); ?></a>

/ <a href="rm_moderation.php"><?php echo_html(text("ReadmarkerModeration")); ?></a>

/ <a href="user_agents.php"><?php echo_html(text("UserAgents")); ?></a>

/ <a href="guest_ips.php"><?php echo_html(text("GuestIPs")); ?></a>

/ <span class="topic_title_main"><?php echo_html(text("TorIPs")); ?></span>
</div>

<!-- END: forum_bar -->

<?php endif; ?>

