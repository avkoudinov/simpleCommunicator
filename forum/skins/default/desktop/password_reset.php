
<script>
var save_data_ajax = null;

function save_data()
{
  var form = document.getElementById('main_form');
  if(!form) return;

  Forum.show_sys_progress_indicator(true);

  if(!save_data_ajax)
  {
    save_data_ajax = new Forum.AJAX();

    save_data_ajax.timeout = TIMEOUT;

    save_data_ajax.beforestart = function() { break_check_new_messages(); };
    save_data_ajax.aftercomplete = function(error) { activate_check_new_messages(); };

    save_data_ajax.onload = function(text, xml)
    {
      try
      {
        var response = JSON.parse(text);

        Forum.handle_response_messages(response);

        if(response.success)
        {
          delay_redirect('login.php');
          return;
        }
        else
        {
          form.elements["password"].value = "";
          form.elements["password2"].value = "";
        }
      }
      catch(err)
      {
        Forum.handle_ajax_error(this, err.message, this.last_url, {});
      }

      Forum.show_sys_progress_indicator(false);
    };

    save_data_ajax.onerror = function(error, url, info)
    {
      Forum.show_sys_progress_indicator(false);

      Forum.handle_ajax_error(this, error, url, info);
    };
  }
  
  save_data_ajax.abort();
  save_data_ajax.resetParams();

  var formData = new FormData(form);

  formData.append('hash', get_protection_hash());
  formData.append('user_logged', user_logged);  
  formData.append('reset_password', "1");

  save_data_ajax.setFormData(formData);

  save_data_ajax.request("ajax/process.php");

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

/ <span class="topic_title_main"><?php echo_html(text("PasswordReset")); ?></span>
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

<form action="password_reset.php" id="main_form" method="post" onsubmit="return save_data();">

<table class="form_table pwd_reset_table">

<tr>
<th colspan="2"><?php echo_html(text("PasswordReset")); ?></th>
</tr>

<tr>
<td colspan="2"><?php echo_html(text("PasswordResetComment")); ?></td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<tr>
<td><?php echo_html(text("UserName")); ?>:</td>
<td><input type="text" id="user_name" name="user_name" class="read_only_field" value="<?php echo_html($user_data["user_name"]); ?>" readonly></td>
</tr>

<tr>
<td><?php echo_html(text("UserLogin")); ?>:</td>
<td><input type="text" id="user_login" name="user_login" class="read_only_field" value="<?php echo_html($user_data["user_login"]); ?>" readonly></td>
</tr>

<tr>
<td><?php echo_html(text("Password")); ?>*:</td>
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
<td colspan="2" class="button_area">

<div class="left_buttons">
<input type="button" class="standard_button" value="<?php echo_html(text("Reset")); ?>" onclick="this.form.reset(); this.form.elements['password'].focus();">
</div>
<div class="right_buttons">
<input type="submit" class="standard_button send_button" value="<?php echo_html(text("Save")); ?>">
</div>
<div class="clear_both">
</div>

</td>
</tr>

</table>

</form>

</div>