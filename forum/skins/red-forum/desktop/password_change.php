
<script>
var password_change_ajax = null;

function password_change()
{
  var form = document.getElementById('main_form');
  if(!form) return;

  Forum.show_sys_progress_indicator(true);

  if(!password_change_ajax)
  {
    password_change_ajax = new Forum.AJAX();

    password_change_ajax.timeout = TIMEOUT;

    password_change_ajax.beforestart = function() { break_check_new_messages(); };
    password_change_ajax.aftercomplete = function(error) { activate_check_new_messages(); };

    password_change_ajax.onload = function(text, xml)
    {
      try
      {
        var response = JSON.parse(text);

        Forum.handle_response_messages(response);

        if(response.success)
        {
          form.elements['current_password'].value = "";
          form.elements['password'].value = "";
          form.elements['password2'].value = "";
        }
        else if(response.FOCUS_ELEMENT == 'current_password')
        {
          form.elements['current_password'].value = "";
        }
        else if(response.FOCUS_ELEMENT == 'password')
        {
          form.elements['password'].value = "";
          form.elements['password2'].value = "";
        }
      }
      catch(err)
      {
        Forum.handle_ajax_error(this, err.message, this.last_url, {});
      }

      Forum.show_sys_progress_indicator(false);
    };

    password_change_ajax.onerror = function(error, url, info)
    {
      Forum.show_sys_progress_indicator(false);

      Forum.handle_ajax_error(this, error, url, info);
    };
  }

  password_change_ajax.abort();
  password_change_ajax.resetParams();
  
  var formData = new FormData(form);

  formData.append('hash', get_protection_hash());
  formData.append('user_logged', user_logged);  
  formData.append('change_password', "1");

  password_change_ajax.setFormData(formData);

  password_change_ajax.request("ajax/process.php");

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

/ <span class="topic_title_main"><?php echo_html(text("PasswordChange")); ?></span>
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

<form action="password_change.php" id="main_form" method="post" onsubmit="return password_change();">

<table class="form_table login_table">

<tr>
<th colspan="2"><?php echo_html(text("PasswordChange")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("UserName")); ?>:</td>
<td><input type="text" name="user_name" value="<?php echo_html(text("Administrator")); ?>" disabled="disabled"></td>
</tr>

<tr>
<td><?php echo_html(text("CurrentPassword")); ?>*:</td>
<td><input type="password" id="current_password" name="current_password"></td>
</tr>

<tr>
<td><?php echo_html(text("NewPassword")); ?>*:</td>
<td><input type="password" id="password" name="password"></td>
</tr>

<tr>
<td><?php echo_html(text("PasswordConfirmation")); ?>*:</td>
<td><input type="password" id="password2" name="password2"></td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<tr>
<td colspan="2" class="button_area"><input type="submit" class="standard_button" value="<?php echo_html(text("Apply")); ?>"></td>
</tr>

</table>

</form>

</div>