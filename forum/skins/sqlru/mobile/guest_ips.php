<script type='text/JavaScript'>
function save_ips()
{
  var elm = document.getElementById("white_ips");
  if(!elm) return false;
  
  var params = { save_white_ips: 1, ips: elm.value };
  
  return do_action(null, params);
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
          if(params.save_white_ips == 1)
          {
            delay_reload();
            return;
          }
          
          if(typeof response.ips != "undefined")
          {
            var white_ips = document.getElementById("white_ips");
            if(white_ips) white_ips.value = response.ips;
          }
        }
        else
        {
          if(params.whitelist_ip == 1)
          {
            if(elm) elm.checked = !elm.checked;
          }
        }
      }
      catch(err)
      {
        if(params.whitelist_ip == 1)
        {
          if(elm) elm.checked = !elm.checked;
        }

        Forum.handle_ajax_error(this, err.message, this.last_url, {});
      }

      Forum.show_sys_progress_indicator(false);
    };

    action_ajax.onerror = function(error, url, info)
    {
      if(params.whitelist_ip == 1)
      {
        if(elm) elm.checked = !elm.checked;
      }

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
</script>

<!-- BEGIN: header2 -->

<div class="header2">

<form action="guest_ips.php" method="get">
<table class="aux_table" style="width:100%">
<tr>
<td>
  <input type="text" class="search_field" id="search_key" name="search_key" autocomplete="off" placeholder="<?php echo_html(text("SearchIPAddress")); ?>" value="<?php echo_html(reqvar("search_key")); ?>"/>
</td>
<td style="width:1%; white-space: nowrap">
<input type="submit" class="standard_button search_button" value="<?php echo_html(text("DoSearch")); ?>"/><?php if(!reqvar_empty("search_key")): ?><input type="submit" class="standard_button search_button" value="<?php echo_html(text("Reset")); ?>" onclick="this.form.elements['search_key'].value=''"/><?php endif; ?>
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

/ <a href="moderation.php"><?php echo_html(text("Moderation")); ?></a>

/ <a href="rm_moderation.php"><?php echo_html(text("ReadmarkerModeration")); ?></a>

/ <a href="user_agents.php"><?php echo_html(text("UserAgents")); ?></a>

/ <a href="tor_ips.php"><?php echo_html(text("TorIPs")); ?></a>

/ <span class="topic_title_main"><?php echo_html($title); ?></span>
</div>

<!-- END: forum_bar -->

<form action="moderation.php" id="main_form" enctype="multipart/form-data" method="post" onsubmit="return save_ips()">

<table class="form_table profile_table profile_view_table moderation_table">

<tr>
<th><?php echo_html(text("WhiteListIPs")); ?></th>
</tr>

<tr>
<td class="moderation_user_list">
<textarea id="white_ips" name="white_ips"><?php echo_html($ips); ?></textarea>
<div class="field_comment"><?php echo_html(text("WhiteListIPComment")); ?></div>
</td>
</tr>

<tr>
<td></td>
</tr>

<tr>
<td class="button_area">

<div class="right_buttons">
<input type="submit" class="standard_button send_button" value="<?php echo_html(text("Apply")); ?>"/>
</div>
<div class="clear_both">
</div>
</td>
</tr>

</table>

</form>



<table class="ip_table guest_ip_table">
<tr>
<th><?php echo_html(text("IPAddress")); ?></th>
</tr>

<?php if(empty($guest_ips)): ?>

<tr>
<td class="table_message"><?php echo_html(text("NoData")); ?></td>
</tr>

<?php else: ?>

<?php foreach($guest_ips as $ipfno): ?>

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
<?php echo_html(text("Authors")); ?>:<br> 

  <?php 
  sort($ipfno["authors"]); 
  $first = true;
  foreach($ipfno["authors"] as $author_name)
  {
    if(!$first) echo ", ";
    else        $first = false;
    
    if($author_name == "admin")
      $author = "<a class='admin_link' href='view_guest_profile.php?guest=" . xrawurlencode($author_name) . "' >" . escape_html(text("MasterAdministrator")) . "</a>";
    else
      $author = "<a class='guest_link' href='view_guest_profile.php?guest=" . xrawurlencode($author_name) . "' >" . escape_html($author_name) . "</a>";

    if(empty($settings["hide_online_status"]) && !empty($online_users["g_" . $author_name]))
    {
      $author .= "&nbsp;<span class='online_text'>✓</span>";
    }
    
    echo($author); 
  }
  ?>
  
  <br>

  <?php
  $checked = !empty($ipfno["guest_ip_whitelisted"]) ? "checked" : "";
  ?>
  <table class="checkbox_table">
   <tr>
     <td>
  <input type="checkbox" name="block_unblock" <?php echo($checked); ?> onchange='do_action(this, { whitelist_ip: 1, ip: "<?php echo_js($ipfno["matched_rule"]); ?>", state: this.checked ? 1 : 0 })'>
     </td>
     <td>
  <label for="<?php echo_html(md5($ipfno["ip"])); ?>"><?php echo_html(text("PermissionAllow")); ?></label>
     </td>
   </tr>
  </table> 

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

<?php if(count($guest_ips) > 2): ?>

<!-- BEGIN: forum_bar -->

<div class="forum_bar">
<a href="forums.php"><?php echo_html(text("Forums")); ?></a>

<?php
$display = "style='display:none'";
if(!empty($topics_with_new_count)) $display = "";
?>
<span class="new topics_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span> 

/ <a href="moderation.php"><?php echo_html(text("Moderation")); ?></a>

/ <a href="rm_moderation.php"><?php echo_html(text("ReadmarkerModeration")); ?></a>

/ <a href="user_agents.php"><?php echo_html(text("UserAgents")); ?></a>

/ <a href="tor_ips.php"><?php echo_html(text("TorIPs")); ?></a>

/ <span class="topic_title_main"><?php echo_html($title); ?></span>
</div>

<!-- END: forum_bar -->

<?php endif; ?>

