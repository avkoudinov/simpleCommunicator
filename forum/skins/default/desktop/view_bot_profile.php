<script>
function reload_daily_activity_image(period)
{
  var img = document.getElementById("bot_daily_activity_image");
  if(img)
  {
    img.style.opacity = "0.2";
    img.src = "ajax/bot_activity_diagram.php?bot=<?php echo_html(xrawurlencode(reqvar("bot"))); ?>&period=" + period + "&rnd=" + Math.random();
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

/ <?php echo_html(text("BotProfile")); ?>
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

<table class="form_table profile_table" style="margin-bottom: 0px">

<tr>
<th colspan="2"><?php echo_html(text("BotProfile")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("UserName")); ?>:</td>
  <?php
  $online_status = "";
  if(empty($settings["hide_online_status"]) && !empty($online_users["b_" . $bot_data["user_name"]]))
  {
    $online_status = "&nbsp;<span class='online_text'>✓</span>";
  }
  ?>
<td><span class="number"><?php echo_html($bot_data["user_name"]); ?></span><?php echo($online_status); ?></td>
</tr>

<tr>
<td colspan="2"></td>
</tr>


<tr>
<td><?php echo_html(text("Avatar")); ?>:</td>
<td>

<?php
$picture = $view_path . "images/bot.png";
?>

<img class="avatar_picture" src="<?php echo($picture); ?>" alt="<?php echo_html(text("Avatar")); ?>">

</td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<tr>
<td><?php echo_html(text("Status")); ?>:</td>
<td>

    <?php echo_html(text("Bot")); ?>

</td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<?php if(empty($settings["hide_online_status"])): ?>
<tr>
<td><?php echo_html(text("LastActivity")); ?>:</td>
<td><span class="number"><?php echo_html($bot_data["last_visit_date"]); ?></span></td>
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
  $bot_data["last_ip"] = "127.0.0.1";
}

$ip = escape_html($bot_data["last_ip"]);
if(!empty($ip))
{
  if(!empty($settings["whois_server"]))
  {
    $url = str_ireplace("{ip}", $ip, str_replace("&", "&", $settings["whois_server"]));
    $ip = "<a href='$url' target='_blank'>" . $ip . "</a>";
  }

  $ip_sign = "✘";
  $ip_class = "ip_moderation";
  if(!empty($bot_data["last_ip_blocked"]))
  {
    $ip_sign = "✘";
    $ip_class = "ip_moderation ip_blocked";
  }
  $ip .= "&nbsp;<a href='ip_moderation.php?type=moderation&ip=" . xrawurlencode($bot_data["last_ip"]) . "' class='moderator_link $ip_class' title='" . escape_html(text("Moderate")) . "'>$ip_sign</a>";

  if($fmanager->is_admin())
  {
    $ip_sign = "✓";
    $ip_class = "guest_ip";
    if(!empty($bot_data["last_ip_whitelisted"]))
    {
      $ip_sign = "✓";
      $ip_class = "ip_whitelisted";
    }
    $ip .= "&nbsp;<a href='guest_ips.php?search_key=" . xrawurlencode($bot_data["last_ip"]) . "' class='ip_moderation $ip_class' title='" . escape_html(text("GuestIPs")) . "'>$ip_sign</a>";
  }

  if(!empty($bot_data["last_tor_ip"]))
  {
    $ip_class = "ip_moderation " . val_or_empty($bot_data["last_tor_ip_block_level"]);
    $ip_sign = "Tor";
    $ip .= "&nbsp;<a href='tor_ips.php?search_key=" . xrawurlencode($bot_data["last_ip"]) . "' class='moderator_link $ip_class' title='" . escape_html(text("Moderate")) . "'>$ip_sign</a>";
  }

  $ip .= "&nbsp;<a href='ip_moderation.php?type=ip_users&ip=" . xrawurlencode($bot_data["last_ip"]) . "' title='" . escape_html(text("Authors")) . "'><img src='" . $view_path . "images/users.png' alt='" . escape_html(text("Authors")) . "' class='ip_authors'></a>";
}

echo $ip;
?>
</td>
</tr>

<tr>
<td colspan="2"></td>
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
if(!empty($_SESSION["ignored_forums"][$tinfo["fid"]])) $not_preferred = "not_preferred";
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

<h3 class="profile_caption"><?php echo_html(text("DailyActivity")); ?></h2>

<select name="bot_activity_period" class="user_activity_period_select" onchange="reload_daily_activity_image(this.value)" autocomplete="off">
<?php $selected = val_or_empty($_SESSION["bot_activity_period"]) == "last_month" ? "selected" : ""; ?>
<option value="last_month" <?php echo($selected); ?>><?php echo_html(text("LastMonth")); ?></option>
<?php $selected = (val_or_empty($_SESSION["bot_activity_period"]) == "last_half_year" || empty($_SESSION["bot_activity_period"])) ? "selected" : ""; ?>
<option value="last_half_year" <?php echo($selected); ?>><?php echo_html(text("LastHalfYear")); ?></option>
<?php $selected = val_or_empty($_SESSION["bot_activity_period"]) == "last_year" ? "selected" : ""; ?>
<option value="last_year" <?php echo($selected); ?>><?php echo_html(text("LastYear")); ?></option>
<?php $selected = val_or_empty($_SESSION["bot_activity_period"]) == "whole_period" ? "selected" : ""; ?>
<option value="whole_period" <?php echo($selected); ?>><?php echo_html(text("WholePeriod")); ?></option>
</select>

<div class="user_activity_image_wrapper">
<img id="bot_daily_activity_image" class="user_activity_image" title="<?php echo_text("DailyActivity"); ?>" alt="&nbsp;" src="ajax/bot_activity_diagram.php?period=<?php echo_html(val_or_empty($_SESSION["bot_activity_period"])); ?>&bot=<?php echo_html(xrawurlencode(reqvar("bot"))); ?>&rnd=<?php echo(rand(1000, 9000)); ?>" onload="this.style.opacity = '1';">
</div>


<div style="margin-bottom: 70px"></div>

</div>
