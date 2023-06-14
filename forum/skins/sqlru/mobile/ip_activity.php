<script type='text/JavaScript'>
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

<!-- BEGIN: forum_bar -->

<div class="forum_bar">
<a href="forums.php"><?php echo_html(text("Forums")); ?></a>

<?php
$display = "style='display:none'";
if(!empty($topics_with_new_count)) $display = "";
?>
<span class="new topics_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span> 

/ <a href="statistics.php"><?php echo_html(text("Statistics")); ?></a>

/ <a href="load_statistics.php"><?php echo_html(text("LoadStatistics")); ?></a>

/ <span class="topic_title_main"><?php echo_html($title); ?></span>
</div>

<!-- END: forum_bar -->

<form action="ip_moderation.php" id="main_form" enctype="multipart/form-data" method="get" onsubmit="return do_action();">

<input type="hidden" id="type" name="type" value="<?php echo_html(reqvar("type")); ?>">

<table class="form_table profile_table profile_view_table">

<tr>
<th><?php echo_html($title); ?></th>
</tr>

<tr>
<td>
<?php echo_html(text("IPAddress")); ?>:</td>
</tr>
<tr>
<td>
<input type="text" id="ip" name="ip" value="<?php echo_html(reqvar("ip")); ?>">
</td>
</tr>

<tr>
<td>
<a href="ip_moderation.php?type=ip_users&ip=<?php echo(xrawurlencode(reqvar("ip"))); ?>" onclick="return show_ip_users(this)"><?php echo_html(text("ShowMembersOfIP")); ?></a>

<br><a href="ip_moderation.php?type=moderation&ip=<?php echo(xrawurlencode(reqvar("ip"))); ?>" onclick="return moderate_ip(this)"><?php echo_html(text("ModerateIP")); ?></a>
</td>
</tr>

<tr>
<td></td>
</tr>

<tr>
<td class="button_area">

<div class="left_buttons">
<input type="button" class="standard_button" value="<?php echo_html(text("Back")); ?>" onclick="delay_redirect('<?php echo_html($target_url); ?>')"/>
</div>
<div class="right_buttons">
<input type="submit" class="standard_button send_button" value="<?php echo_html(text("Apply")); ?>"/>
</div>
<div class="clear_both">
</div>
</td>
</tr>

</table>
</form>

<table class="rm_table">
<tr>
<th><?php echo_html(text("IPActivity")); ?></th>

</tr>

<?php if(count($ip_activity_list) == 0): ?>

<tr>
<td class="table_message"><?php echo_html(text("UserAgentsNotFound")); ?></td>
</tr>

<?php else: ?>

<?php
foreach($ip_activity_list as $ua_data):
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

<?php if(count($ip_activity_list) > 2): ?>

<!-- BEGIN: forum_bar -->

<div class="forum_bar">
<a href="forums.php"><?php echo_html(text("Forums")); ?></a>

<?php
$display = "style='display:none'";
if(!empty($topics_with_new_count)) $display = "";
?>
<span class="new topics_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span> 

/ <a href="statistics.php"><?php echo_html(text("Statistics")); ?></a>

/ <a href="load_statistics.php"><?php echo_html(text("LoadStatistics")); ?></a>

/ <span class="topic_title_main"><?php echo_html($title); ?></span>

</div>

<!-- END: forum_bar -->

<?php endif; ?>

