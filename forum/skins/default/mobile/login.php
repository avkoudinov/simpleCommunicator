
<script>
var login_ajax = null;

function do_login()
{
  var form = document.getElementById('main_form');
  if(!form) return;

  Forum.show_sys_progress_indicator(true);

  if(!login_ajax)
  {
    login_ajax = new Forum.AJAX();

    login_ajax.timeout = TIMEOUT;

    login_ajax.beforestart = function() { break_check_new_messages(); };
    login_ajax.aftercomplete = function(error) { activate_check_new_messages(); };

    login_ajax.onload = function(text, xml)
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
          form.elements['user_password'].value = "";
        }
      }
      catch(err)
      {
        Forum.handle_ajax_error(this, err.message, this.last_url, {});
      }

      Forum.show_sys_progress_indicator(false);
    };

    login_ajax.onerror = function(error, url, info)
    {
      Forum.show_sys_progress_indicator(false);

      Forum.handle_ajax_error(this, error, url, info);
    };
  }

  login_ajax.abort();
  login_ajax.resetParams();
  
  var formData = new FormData(form);

  formData.append('hash', get_protection_hash());
  formData.append('user_logged', user_logged);  
  formData.append('do_login', "1");

  login_ajax.setFormData(formData);

  login_ajax.request("ajax/process.php");

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

/ <span class="topic_title_main"><?php echo_html(text("Authorization")); ?></span>
</div>

<!-- END: forum_bar -->

<form action="login.php" id="main_form" method="post" onsubmit="return do_login();">

<table class="form_table login_table">

<tr>
<th><?php echo_html(text("Authorization")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("UserLogin")); ?>*:</td>
</tr>
<tr>
<td><input type="text" id="user_login" name="user_login"></td>
</tr>

<tr>
<td><?php echo_html(text("Password")); ?>*:</td>
</tr>
<tr>
<td><input type="password" id="user_password" name="user_password"></td>
</tr>

<tr>
<td>
   <table class="checkbox_table">
   <tr>
     <td>
    <input type="checkbox" id="user_autologin" name="user_autologin"> 
     </td>
     <td>
    <label for="user_autologin"><?php echo_html(text("LoginAutomatically")); ?></label>
     </td>
   </tr>
   </table>
</td>
</tr>

<tr>
<td>
<a href="registration.php" tabindex="-1"><?php echo_html(text("Registration")); ?></a></td>
</tr>

<tr>
<td>
<a href="password_restore.php" tabindex="-1"><?php echo_html(text("PasswordRestoration")); ?></a>
</td>
</tr>

<tr>
<td></td>
</tr>


<tr>
<td class="button_area"><input type="submit" class="standard_button" value="<?php echo_html(text("Login")); ?>"></td>
</tr>

</table>

</form>
