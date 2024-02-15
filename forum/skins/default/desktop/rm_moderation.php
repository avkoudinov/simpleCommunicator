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
  action_ajax.setPOST('trace_sql', trace_sql);

  action_ajax.request("ajax/process.php");

  return false;
}
</script>

<!-- BEGIN: header3 -->

<div class="header3">

<div class="user_search_bar">
<form action="rm_moderation.php" method="get">
<table class="aux_table">
<tr>
<td>
<input type="text" class="search_field" id="search_key" name="search_key" autocomplete="off" placeholder="<?php echo_html(text("SearchReadMarker")); ?>" value="<?php echo_html(reqvar("search_key")); ?>">
</td>
<td>
<input type="submit" class="standard_button search_button" value="<?php echo_html(text("DoSearch")); ?>"><?php if(!reqvar_empty("search_key")): ?><input type="submit" class="standard_button search_button" value="<?php echo_html(text("Reset")); ?>" onclick="this.form.elements['search_key'].value=''"><?php endif; ?>
</td>
</tr>
</table>
</form>
</div>


<div class="right_action_panel">
<select class='sort_selector' onchange='if(this.value) document.location.href=this.value'>

<option value="rm_moderation.php?sort=last_activity" <?php echo(reqvar("sort") == "last_activity" ? "selected" : ""); ?>><?php echo_html(text("Sort")); ?>: <?php echo_html(text("LastActivity")); ?></option>
<option value="rm_moderation.php?sort=first_activity" <?php echo(reqvar("sort") == "first_activity" ? "selected" : ""); ?>><?php echo_html(text("Sort")); ?>: <?php echo_html(text("FirstActivity")); ?></option>

</select>

</div>

<div class="clear_both">
</div>


</div>

<!-- END: header3 -->

<div class="content_area">

<!-- BEGIN: forum_bar -->

<div class="forum_bar">

<div class="forum_name_bar"><a href="forums.php"><?php echo_html(text("Forums")); ?></a>

<?php
$display = "style='display:none'";
if(!empty($topics_with_new_count)) $display = "";
?>
<span class="new topics_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span> 

/ <a href="moderation.php"><?php echo_html(text("Moderation")); ?></a>

/ <a href="user_agents.php"><?php echo_html(text("UserAgents")); ?></a>

/ <a href="guest_ips.php"><?php echo_html(text("GuestIPs")); ?></a>

/ <a href="tor_ips.php"><?php echo_html(text("TorIPs")); ?></a>

/ <span class="topic_title_main"><?php echo_html(text("ReadmarkerModeration")); ?></span>,

<?php echo(build_page_info($pagination_info, text("pages"))); ?>
</div>

<?php if($pagination_info["page_count"] > 1): ?>
<div class="navigator_bar"><?php echo(build_page_navigator("rm_moderation.php?rmpage=$", $pagination_info)); ?></div>
<?php endif; ?>

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

<table class="rm_table">
<tr>
<th><?php echo_html(text("ReadMarker")); ?></th>
<th class='author_col'><?php echo_html(text("Author")); ?></th>
<th><?php echo_html(text("IPAddress")); ?></th>
<th><?php echo_html(text("UserAgent")); ?></th>
<th><?php echo_html(text("Views")); ?></th>
<th><?php echo_html(text("FirstActivity")); ?></th>
<th><?php echo_html(text("LastActivity")); ?></th>

<th class="admin_actions"><?php echo_html(text("Administrator")); ?></th>

</tr>

<?php if(count($read_marker_list) == 0): ?>

<tr>
<td colspan="8" class="table_message"><?php echo_html(text("ReadmarkersNotFound")); ?></td>
</tr>

<?php else: ?>

<?php
foreach($read_marker_list as $rm => $rm_data):
?>

<tr id="<?php echo_html($rm); ?>">
<td>
<div class="smart_break"><?php echo_html($rm); ?></div>
</td>
<?php
$author = "";
if(!empty($rm_data["author"]))
{
  if(!empty($rm_data["user_id"])) 
    $author = "<a href='view_profile.php?uid=$rm_data[user_id]'>" . escape_html($rm_data["author"]) . "</a>";
  elseif($rm_data["author"] == "admin")
    $author = "<a class='admin_link' href='view_guest_profile.php?guest=" . xrawurlencode($rm_data["author"]) . "' >" . escape_html(text("MasterAdministrator")) . "</a>";
  else
    $author = "<a class='guest_link' href='view_guest_profile.php?guest=" . xrawurlencode($rm_data["author"]) . "' >" . escape_html($rm_data["author"]) . "</a>";
}

if(empty($settings["hide_online_status"]) && (!empty($rm_data["author_online"]) || !empty($online_users["g_" . $rm_data["author"]])))
{
  $author .= "&nbsp;<span class='online_text'>✓</span>";
}
?>
<td class='author_col'><?php echo($author); ?></td>
<td>
<?php
$ip = escape_html($rm_data["ip"]);
if(!empty($settings["whois_server"]))
{
  $url = str_ireplace("{ip}", $ip, $settings["whois_server"]);

  $ip = "<a href='$url' target='_blank'>" . $ip . "</a>";
}

$ip_sign = "✘";
$ip_class = "ip_moderation";
if(!empty($rm_data["ip_blocked"]))
{
  $ip_sign = "✘";
  $ip_class = "ip_moderation ip_blocked";
}
$ip .= "&nbsp;<a href='ip_moderation.php?type=moderation&ip=" . xrawurlencode($rm_data["ip"]) . "' class='moderator_link $ip_class' title='" . escape_html(text("Moderate")) . "'>$ip_sign</a>";

echo($ip);
?>
</td>
<td><?php echo_html($rm_data["user_agent"]); ?></td>
<td>
<?php echo_html(format_number($rm_data["hits"])); ?><br>
<span style='color: gray'><?php echo_html(format_number($rm_data["current_name_hits"])); ?></span>
</td>

<td>
<?php echo_html($rm_data["first_activity"]); ?><br>
<span style='color: gray'><?php echo_html($rm_data["current_name_start"]); ?></span>
</td>
<td><?php echo_html($rm_data["last_activity"]); ?></td>

<td class="admin_actions">
<a href="rm_moderation.php" class="moderator_link" onclick='return confirm_action("<?php echo_js(text("MsgConfirmReadMarkerDelete"), true); ?>", { delete_read_marker: 1, read_marker: "<?php echo_js($rm); ?>" });'><?php echo_html(text("Delete")); ?></a>
</td>

</tr>

<?php endforeach; ?>

<?php endif; ?>

</table>

<?php if(count($read_marker_list) > 25): ?>

<!-- BEGIN: forum_bar -->

<div class="forum_bar">

<div class="forum_name_bar"><a href="forums.php"><?php echo_html(text("Forums")); ?></a>

<?php
$display = "style='display:none'";
if(!empty($topics_with_new_count)) $display = "";
?>
<span class="new topics_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span> 

/ <a href="moderation.php"><?php echo_html(text("Moderation")); ?></a>

/ <a href="user_agents.php"><?php echo_html(text("UserAgents")); ?></a>

/ <a href="guest_ips.php"><?php echo_html(text("GuestIPs")); ?></a>

/ <a href="tor_ips.php"><?php echo_html(text("TorIPs")); ?></a>

/ <span class="topic_title_main"><?php echo_html($title); ?></span>,

<?php echo(build_page_info($pagination_info, text("pages"))); ?>
</div>

<?php if($pagination_info["page_count"] > 1): ?>
<div class="navigator_bar"><?php echo(build_page_navigator("rm_moderation.php?rmpage=$", $pagination_info)); ?></div>
<?php endif; ?>

<div class="forum_action_bar">
<table>
<tr>
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

<?php endif; ?>

</div>
