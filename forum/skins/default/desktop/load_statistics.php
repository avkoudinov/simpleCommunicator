<script>
function reload_statistics()
{
  var form = document.getElementById("statistics_filter_form");

  if(!form) return;

  Forum.show_sys_progress_indicator(true);

  form.submit();
}
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

/ <a href="statistics.php"><?php echo_html(text("Statistics")); ?></a> 

/ <span class="topic_title_main"><?php echo_html(text("LoadStatistics")); ?></span>
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

<div style="width: 940px; margin: 0 auto">

<h3 class="profile_caption"><?php echo_html(text("PeriodStatistics")); ?></h2>

<form id="statistics_filter_form" action="load_statistics.php" method="post">
<input type="hidden" name="apply_filter" value="1">

<table class="forum_activity_filter">
<tr>
<td>
  <select name="period" id="load_activity_period" class="load_activity_period_select"  onchange="reload_statistics()">
  <?php $selected = val_or_empty($_SESSION["load_activity_period"]) == "last_10_minutes" ? "selected" : ""; ?>
  <option value="last_10_minutes" <?php echo($selected); ?>><?php echo_html(text("Last10Minutes")); ?></option>
  <?php $selected = val_or_empty($_SESSION["load_activity_period"]) == "last_hour" ? "selected" : ""; ?>
  <option value="last_hour" <?php echo($selected); ?>><?php echo_html(text("LastHour")); ?></option>
  <?php $selected = val_or_empty($_SESSION["load_activity_period"]) == "last_day" ? "selected" : ""; ?>
  <option value="last_day" <?php echo($selected); ?>><?php echo_html(text("Last24Hours")); ?></option>
  <?php $selected = val_or_empty($_SESSION["load_activity_period"]) == "last_week" ? "selected" : ""; ?>
  <option value="last_week" <?php echo($selected); ?>><?php echo_html(text("LastWeek")); ?></option>
  </select>
</td>
</tr>
</table>

</form>

<h3 class="profile_caption"><?php echo_html(text("LoadStatistics")); ?></h2>

<div class="forum_activity_image_wrapper">
<img id="forum_activity_image" class="forum_activity_image" title="<?php echo_text("LoadStatistics"); ?>" alt="&nbsp;" src="ajax/load_diagram.php?rnd=<?php echo(rand(1000, 9000)); ?>" onload="this.style.opacity = '1';">
</div>

<h3 class="profile_caption"><?php echo_html(text("Members")); ?></h2>

<table class="load_statistic_table">

<tr>
<th><?php echo_html(text("Member")); ?></th>
<th colspan="3" style="width:1%"><?php echo_html(text("Views")); ?></th>
</tr>

<?php if(count($user_activity) == 0): ?>

<tr>
<td colspan="4">&nbsp;</td>
</tr>

<?php else: ?>

<?php
foreach($user_activity as $uinfo):

$pct = $uinfo["cnt"] / $total_user_hits_count;

if(!empty($uinfo["id"]))
{
  $uname = "<a href='view_profile.php?uid=$uinfo[id]'>" . escape_html($uinfo["user_name"]) . "</a>";
}
else
{
  if(!empty($uinfo["is_anonym"]))
    $uname = escape_html($uinfo["user_name"]);
  elseif($uinfo["user_name"] == "admin")
    $uname = "<a class='admin_link' href='view_guest_profile.php?guest=" . xrawurlencode($uinfo["user_name"]) . "'>" . escape_html(text("MasterAdministrator")) . "</a>";
  else  
    $uname = "<a class='guest_link' href='view_guest_profile.php?guest=" . xrawurlencode($uinfo["user_name"]) . "'>" . escape_html($uinfo["user_name"]) . "</a>";
}

if(empty($settings["hide_online_status"]) && (!empty($uinfo["online"]) || !empty($online_users["g_" . $uinfo["user_name"]])))
{
  $uname .= "&nbsp;<span class='online_text'>✓</span>";
}

$width = round(280 * $pct);
if($width == 0) $width = 1;

$width .= "px";
?>

<tr>
<td><div class="smart_break"><?php echo($uname); ?></div></td>
<td><?php echo_html($uinfo["cnt"]); ?></td>
<td><?php echo_html(format_number(100*$pct, 1)); ?> %</td>
<td style="width:1%"><div class="statistics_bar" style="width:<?php echo($width); ?>"></div><div class="clear_both"></div></td>
</tr>

<?php endforeach; ?>

<?php endif; ?>

</table>

<?php if($fmanager->is_admin()): ?>

<h3 class="profile_caption"><?php echo_html(text("UserAgents")); ?></h2>

<table class="load_statistic_table">

<tr>
<th><?php echo_html(text("UserAgent")); ?></th>
<th colspan="3" style="width:1%"><?php echo_html(text("Views")); ?></th>
</tr>

<?php if(count($agent_activity) == 0): ?>

<tr>
<td colspan="4">&nbsp;</td>
</tr>

<?php else: ?>

<?php
foreach($agent_activity as $agifno):

$pct = $agifno["cnt"] / $total_agents_hits_count;

$agent = escape_html($agifno["agent"]);

$width = round(280 * $pct);
if($width == 0) $width = 1;

$width .= "px";
?>

<tr>
<td>
<div class="smart_break">
<a href="user_agents.php?search_key=<?php echo(xrawurlencode($agifno["agent"])); ?>" target="_blank"><?php echo($agent); ?></a>
</div>
</td>
<td><?php echo_html($agifno["cnt"]); ?></td>
<td><?php echo_html(format_number(100*$pct, 1)); ?> %</td>
<td style="width:1%"><div class="statistics_bar" style="width:<?php echo($width); ?>"></div><div class="clear_both"></div></td>
</tr>

<?php endforeach; ?>

<?php endif; ?>

</table>

<h3 class="profile_caption"><?php echo_html(text("IPAddresses")); ?></h2>

<table class="load_statistic_table">

<tr>
<th><?php echo_html(text("IPAddress")); ?></th>
<th colspan="3" style="width:1%"><?php echo_html(text("Views")); ?></th>
</tr>

<?php if(count($ip_activity) == 0): ?>

<tr>
<td colspan="4">&nbsp;</td>
</tr>

<?php else: ?>

<?php
foreach($ip_activity as $ipfno):

$pct = $ipfno["cnt"] / $total_ip_hits_count;

$ip = escape_html($ipfno["ip"]);
if(!empty($settings["whois_server"]))
{
  $url = str_ireplace("{ip}", $ip, str_replace("&", "&", $settings["whois_server"]));

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

$width = round(280 * $pct);
if($width == 0) $width = 1;

$width .= "px";
?>

<tr>
<td><?php echo($ip); ?></td>
<td><?php echo_html($ipfno["cnt"]); ?></td>
<td><?php echo_html(format_number(100*$pct, 1)); ?> %</td>
<td style="width:1%"><div class="statistics_bar" style="width:<?php echo($width); ?>"></div><div class="clear_both"></div></td>
</tr>

<?php endforeach; ?>

<?php endif; ?>

</table>

<a id="banned_ips"></a>
<h3 class="profile_caption"><?php echo_html(text("BlockedIPAddresses")); ?></h2>

<table class="ip_table2" style="width:100%">
<tr>
<th><?php echo_html(text("IPAddress")); ?></th>
<th style="width:1%"><?php echo_html(text("FirstAttack")); ?></th>
<th style="width:1%"><?php echo_html(text("LastAttack")); ?></th>
<th><?php echo_html(text("Type")); ?></th>
<th style="width:1%"><?php echo_html(text("Attacks")); ?> / <?php echo_html(text("Hits")); ?></th>
</tr>

<?php if(empty($banned_ips)): ?>

<tr>
<td colspan="5" class="table_message"><?php echo_html(text("NoData")); ?></td>
</tr>

<?php else: ?>

<?php foreach($banned_ips as $ipfno): ?>

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

$ip .= "&nbsp;<a href='ip_activity.php?ip=" . xrawurlencode($ipfno["ip"]) . "' title='" . escape_html(text("IPActivity")) . "'><img src='" . $view_path . "images/activity.png' alt='" . escape_html(text("IPActivity")) . "' class='ip_activity'></a>";

echo($ip);
?>
</div>
</td>
<td style="width:1%"><?php echo_html($ipfno["first_attack"]); ?></td>
<td style="width:1%"><?php echo_html($ipfno["last_attack"]); ?></td>
<td><?php echo_html($ipfno["atype"]); ?></td>
<td style="width:1%">
<?php echo_html($ipfno["cnt"]); ?> / <?php echo_html(round($ipfno["hits"])); ?>
</td>
</tr>

<?php endforeach; ?>

<?php endif; ?>

</table>
<br>

<?php endif; ?>

</div>

<!-- BEGIN: forum_bar -->

<div class="forum_bar">

<div class="forum_name_bar"><a href="forums.php"><?php echo_html(text("Forums")); ?></a>

<?php
$display = "style='display:none'";
if(!empty($topics_with_new_count)) $display = "";
?>
<span class="new topics_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span> 

/ <a href="statistics.php"><?php echo_html(text("Statistics")); ?></a> 

/ <span class="topic_title_main"><?php echo_html(text("LoadStatistics")); ?></span>
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

</div>