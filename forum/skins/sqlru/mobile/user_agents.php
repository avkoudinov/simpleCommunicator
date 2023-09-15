<script>
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
          var elm = document.getElementById(this.params.read_marker);
          if(elm) elm.style.display = "none";
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
</script>

<!-- BEGIN: header2 -->

<div class="header2">

<form action="user_agents.php" method="get">
<table class="aux_table" style="width:100%">
<tr>
<td>
  <input type="text" class="search_field" id="search_key" name="search_key" autocomplete="off" placeholder="<?php echo_html(text("SearchUserAgent")); ?>" value="<?php echo_html(reqvar("search_key")); ?>">
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

/ <a href="moderation.php"><?php echo_html(text("Moderation")); ?></a>

/ <a href="rm_moderation.php"><?php echo_html(text("ReadmarkerModeration")); ?></a>

/ <a href="guest_ips.php"><?php echo_html(text("GuestIPs")); ?></a>

/ <a href="tor_ips.php"><?php echo_html(text("TorIPs")); ?></a>

/ <span class="topic_title_main"><?php echo_html(text("UserAgents")); ?></span>
</div>

<!-- END: forum_bar -->

<table class="rm_table">
<tr>
<th><?php echo_html(text("UserAgent")); ?></th>

</tr>

<?php if(count($user_agent_list) == 0): ?>

<tr>
<td class="table_message"><?php echo_html(text("UserAgentsNotFound")); ?></td>
</tr>

<?php else: ?>

<?php
foreach($user_agent_list as $ua_data):
?>

<tr>
<td>

  <div class="forum_info">
  <?php echo_html(text("UserAgent")); ?>: <span class="number"><?php echo_html($ua_data["user_agent"]); ?></span><br>
  </div>

  <div class="forum_info">
  <?php echo_html(text("Author")); ?>: 

  <?php
  $author = "";
  if(!empty($ua_data["author"]))
  {
    if(!empty($ua_data["user_id"])) 
      $author = "<a href='view_profile.php?uid=$ua_data[user_id]'>" . escape_html($ua_data["author"]) . "</a>";
    elseif($ua_data["author"] == "admin")
      $author = "<a class='admin_link' href='view_guest_profile.php?guest=" . xrawurlencode($ua_data["author"]) . "' >" . escape_html(text("MasterAdministrator")) . "</a>";
    else
      $author = "<a class='guest_link' href='view_guest_profile.php?guest=" . xrawurlencode($ua_data["author"]) . "' >" . escape_html($ua_data["author"]) . "</a>";
  }

  if(empty($settings["hide_online_status"]) && (!empty($ua_data["author_online"]) || !empty($online_users["g_" . $ua_data["author"]])))
  {
    $author .= "&nbsp;<span class='online_text'>✓</span>";
  }
  
  echo($author);
  ?>
  </div>
  
  <div class="forum_info">
  <?php echo_html(text("IPAddress")); ?>: 
  
  <?php
  $ip = escape_html($ua_data["ip"]);
  if(!empty($settings["whois_server"]))
  {
    $url = str_ireplace("{ip}", $ip, $settings["whois_server"]);

    $ip = "<a href='$url' target='_blank'>" . $ip . "</a>";
  }

  $ip_sign = "✘";
  $ip_class = "ip_moderation";
  if(!empty($ua_data["ip_blocked"]))
  {
    $ip_sign = "✘";
    $ip_class = "ip_moderation ip_blocked";
  }
  $ip .= "&nbsp;<a href='ip_moderation.php?type=moderation&ip=" . xrawurlencode($ua_data["ip"]) . "' class='moderator_link $ip_class' title='" . escape_html(text("Moderate")) . "'>$ip_sign</a>";

  echo($ip);
  ?>
  </div>
  
  <div class="forum_info">
  <?php echo_html(text("DateTime")); ?>: <span class="number"><?php echo_html(smart_date($ua_data["dt"])); ?></span><br>
  </div>
  <div class="forum_info">
  <?php echo_html("URI"); ?>: <a href="<?php echo($ua_data["uri"]); ?>" target="_blank"><?php echo_html(urldecode($ua_data["uri"])); ?></a><br>
  </div>
  

  <div class="navigation_arrows_right">
  <div class="scroll_up" onclick="window.scrollTo(0, 0);"></div>
  <div class="scroll_down" onclick="window.scrollTo(0, 1000000);"></div>
  </div>

</td>

</tr>

<?php endforeach; ?>

<?php endif; ?>

</table>

<?php if(count($user_agent_list) > 2): ?>

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

/ <a href="guest_ips.php"><?php echo_html(text("GuestIPs")); ?></a>

/ <a href="tor_ips.php"><?php echo_html(text("TorIPs")); ?></a>

/ <span class="topic_title_main"><?php echo_html(text("UserAgents")); ?></span>
</div>

<!-- END: forum_bar -->

<?php endif; ?>

