<!-- BEGIN: header3 -->

<div class="header3">

<div class="user_search_bar">
<form action="user_agents.php" method="get">
<table class="aux_table">
<tr>
<td>
<input type="text" class="search_field" id="search_key" name="search_key" autocomplete="off" placeholder="<?php echo_html(text("SearchUserAgent")); ?>" value="<?php echo_html(reqvar("search_key")); ?>">
</td>
<td>
<input type="submit" class="standard_button search_button" value="<?php echo_html(text("DoSearch")); ?>"><?php if(!reqvar_empty("search_key")): ?><input type="submit" class="standard_button search_button" value="<?php echo_html(text("Reset")); ?>" onclick="this.form.elements['search_key'].value=''"><?php endif; ?>
</td>
</tr>
</table>
</form>
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

/ <a href="rm_moderation.php"><?php echo_html(text("ReadmarkerModeration")); ?></a>

/ <a href="guest_ips.php"><?php echo_html(text("GuestIPs")); ?></a>

/ <a href="tor_ips.php"><?php echo_html(text("TorIPs")); ?></a>

/ <span class="topic_title_main"><?php echo_html(text("UserAgents")); ?></span>

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

<table class="user_agent_table">
<tr>
<th><?php echo_html(text("UserAgent")); ?></th>
<th class='author_col'><?php echo_html(text("Author")); ?></th>
<th><?php echo_html(text("IPAddress")); ?></th>
<th><?php echo_html(text("DateTime")); ?></th>
<th>URI</th>
</tr>

<?php if(count($user_agent_list) == 0): ?>

<tr>
<td colspan="5" class="table_message"><?php echo_html(text("UserAgentsNotFound")); ?></td>
</tr>

<?php else: ?>

<?php
foreach($user_agent_list as $ua_data):
?>

<tr>
<td><?php echo_html($ua_data["user_agent"]); ?></td>
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
?>

<td class='author_col'><?php echo($author); ?></td>
<td>
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
</td>
<td><?php echo_html(smart_date($ua_data["dt"])); ?></td>
<td><a href="<?php echo($ua_data["uri"]); ?>" target="_blank"><?php echo_html(urldecode($ua_data["uri"])); ?></a></td>

</tr>

<?php endforeach; ?>

<?php endif; ?>

</table>

<?php if(count($user_agent_list) > 25): ?>

<!-- BEGIN: forum_bar -->

<div class="forum_bar">

<div class="forum_name_bar"><a href="forums.php"><?php echo_html(text("Forums")); ?></a>

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
