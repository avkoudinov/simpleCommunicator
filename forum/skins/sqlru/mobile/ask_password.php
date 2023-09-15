
<script>
var verify_password_ajax = null;

function verify_password()
{
  var form = document.getElementById('main_form');
  if(!form) return;

  Forum.show_sys_progress_indicator(true);
  
  if(!verify_password_ajax)
  {
    verify_password_ajax = new Forum.AJAX();

    verify_password_ajax.timeout = TIMEOUT;

    verify_password_ajax.beforestart = function() { break_check_new_messages(); };
    verify_password_ajax.aftercomplete = function(error) { activate_check_new_messages(); };

    verify_password_ajax.onload = function(text, xml)
    {
      try
      {
        var response = JSON.parse(text);

        Forum.handle_response_messages(response);

        if(response.success && response.target_url)
        {
          delay_redirect(response.target_url);
          return;
        }
        else
        {
          form.elements['password'].value = "";
        }
      }
      catch(err)
      {
        Forum.handle_ajax_error(this, err.message, this.last_url, {});
      }

      Forum.show_sys_progress_indicator(false);
    };

    verify_password_ajax.onerror = function(error, url, info)
    {
      Forum.show_sys_progress_indicator(false);

      Forum.handle_ajax_error(this, error, url, info);
    };
  }

  verify_password_ajax.abort();
  verify_password_ajax.resetParams();
  
  var formData = new FormData(form);

  formData.append('hash', get_protection_hash());
  formData.append('user_logged', user_logged);  
  formData.append('verify_password', "1");

  verify_password_ajax.setFormData(formData);

  verify_password_ajax.request("ajax/process.php");

  return false;
}
</script>
<form action="ask_password.php" id="main_form" method="get" onsubmit="return verify_password();">

<input type="hidden" id="fid" name="fid" value="<?php echo_html(val_or_empty($forum_data["id"])); ?>">
<table class="form_table login_table">

<tr>
<th colspan="2"><?php echo_html($subtitle); ?></th>
</tr>

<tr>
<td colspan="2">
<?php echo_html($entrance_warning); ?>
</td>
</tr>

<tr>
<td><?php echo_html(text("Password")); ?>*:</td>
<td><input type="password" id="password" name="password" class="user_profile_field"></td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<tr>
<td colspan="2" class="button_area">
<div class="left_buttons">
<input type="button" class="standard_button" value="<?php echo_html(text("Back")); ?>" onclick="history.back()">
</div>
<div class="right_buttons">
<input type="submit" class="standard_button" value="<?php echo_html(text("Enter")); ?>">
</div>
</td>
</tr>

</table>

</form>
