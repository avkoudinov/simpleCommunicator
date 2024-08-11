
<script>
var send_request_ajax = null;

function send_request()
{
  var form = document.getElementById('main_form');
  if(!form) return;

  Forum.show_sys_progress_indicator(true);

  if(!send_request_ajax)
  {
    send_request_ajax = new Forum.AJAX();

    send_request_ajax.timeout = TIMEOUT;

    send_request_ajax.beforestart = function() { break_check_new_messages(); };
    send_request_ajax.aftercomplete = function(error) { activate_check_new_messages(); };

    send_request_ajax.onload = function(text, xml)
    {
      try
      {
        var response = JSON.parse(text);

        Forum.handle_response_messages(response);
      }
      catch(err)
      {
        Forum.handle_ajax_error(this, err.message, this.last_url, {});
      }

      Forum.show_sys_progress_indicator(false);
    };

    send_request_ajax.onerror = function(error, url, info)
    {
      Forum.show_sys_progress_indicator(false);

      Forum.handle_ajax_error(this, error, url, info);
    };
  }

  send_request_ajax.abort();
  send_request_ajax.resetParams();
  
  var formData = new FormData(form);

  formData.append('hash', get_protection_hash());
  formData.append('user_logged', user_logged);  
  formData.append('restore_password', "1");

  send_request_ajax.setFormData(formData);

  send_request_ajax.request("ajax/process.php");

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

/ <span class="topic_title_main"><?php echo_html(text("PasswordRestoration")); ?></span>
</div>

<!-- END: forum_bar -->

<form action="password_restore.php" id="main_form" method="post" onsubmit="return send_request();">

<table class="form_table pwd_restore_table">

<tr>
<th><?php echo_html(text("PasswordRestoration")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("PasswordRestoreComment")); ?></td>
</tr>

<tr>
<td></td>
</tr>

<tr>
<td><?php echo_html(text("Email")); ?>*:</td>
</tr>
<tr>
<td><input type="email" id="user_email" name="user_email"></td>
</tr>

<tr>
<td></td>
</tr>

<tr>
<td class="button_area">

<div class="left_buttons">
<input type="button" class="standard_button" value="<?php echo_html(text("Reset")); ?>" onclick="this.form.reset(); this.form.elements['user_email'].focus();">
<input type="button" class="standard_button" value="<?php echo_html(text("Back")); ?>" onclick="Forum.show_sys_progress_indicator(true); window.history.back();">
</div>
<div class="right_buttons">
<input type="submit" class="standard_button send_button" value="<?php echo_html(text("Send")); ?>">
</div>
<div class="clear_both">
</div>

</td>
</tr>

</table>

</form>
