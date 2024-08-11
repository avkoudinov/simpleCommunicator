<script>
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

function do_action()
{
  var form = document.getElementById('main_form');
  if(!form) return false;

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

  Forum.show_sys_progress_indicator(true);

  delay_redirect('ip_activity.php?&ip=' + form.elements['ip'].value);
  return false;
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

/ <a href="statistics.php"><?php echo_html(text("Statistics")); ?></a>

/ <a href="load_statistics.php"><?php echo_html(text("LoadStatistics")); ?></a>

/ <span class="topic_title_main"><?php echo_html($title); ?></span>

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

<form action="ip_activity.php" id="main_form" enctype="multipart/form-data" method="get" onsubmit="return do_action();">
<table class="form_table profile_table">

<tr>
<th colspan="2"><?php echo_html($title); ?></th>
</tr>

<tr>
<td>
<?php echo_html(text("IPAddress")); ?>:</td>
<td><input type="text" id="ip" name="ip" value="<?php echo_html(reqvar("ip")); ?>">
</td>
</tr>

<tr>
<td></td>
<td>
<a href="ip_moderation.php?type=ip_users&ip=<?php echo(xrawurlencode(reqvar("ip"))); ?>" onclick="return show_ip_users(this)"><?php echo_html(text("ShowMembersOfIP")); ?></a>

<br><a href="ip_moderation.php?type=moderation&ip=<?php echo(xrawurlencode(reqvar("ip"))); ?>" onclick="return moderate_ip(this)"><?php echo_html(text("ModerateIP")); ?></a>
</td>
</tr>

<tr>
<td colspan="2"><input type="hidden" name="type" value="<?php echo_html(reqvar("type")); ?>"></td>
</tr>

<tr>
<td colspan="2" class="button_area">

<div class="left_buttons">
<input type="button" class="standard_button" value="<?php echo_html(text("Back")); ?>" onclick="delay_redirect('<?php echo_html($target_url); ?>')">
</div>
<div class="right_buttons">
<input type="submit" class="standard_button send_button" value="<?php echo_html(text("Apply")); ?>">
</div>
<div class="clear_both">
</div>
</td>
</tr>

</table>
</form>

<table class="user_agent_table">
<tr>
<th><?php echo_html(text("UserAgent")); ?></th>
<th class='author_col'><?php echo_html(text("Author")); ?></th>
<th style="display:none"><?php echo_html(text("IPAddress")); ?></th>
<th><?php echo_html(text("DateTime")); ?></th>
<th>URI</th>
</tr>

<?php if(count($ip_activity_list) == 0): ?>

<tr>
<td colspan="5" class="table_message"><?php echo_html(text("UserAgentsNotFound")); ?></td>
</tr>

<?php else: ?>

<?php
foreach($ip_activity_list as $ua_data):
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
<td style="display:none">
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

<?php if(count($ip_activity_list) > 25): ?>

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

/ <a href="statistics.php"><?php echo_html(text("Statistics")); ?></a>

/ <a href="load_statistics.php"><?php echo_html(text("LoadStatistics")); ?></a>

/ <span class="topic_title_main"><?php echo_html($title); ?></span>

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
