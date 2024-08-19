<script>
function confirm_reset()
{
  var form = document.getElementById("main_form");
  if(!form) return;

  if(!Forum.formDirty(form))
  {
    form.elements['user_name'].focus();
    return;
  }

  var mbuttons = [
    {
      caption: msg_Yes,
      handler: function() {
        Forum.hide_user_msgbox();

        form.reset();
      }
    },
    {
      caption: msg_No,
      handler: function() { Forum.hide_user_msgbox(); }
    }
  ];

  var on_hide = function () { form.elements['user_name'].focus(); };

  Forum.show_user_msgbox(msg_Confirmation, "<?php echo_js(text("MsgResetConfirm")); ?>", 'icon-question.gif', mbuttons, false, on_hide);

  return false;
}

function confirm_back()
{
  var form = document.getElementById("main_form");
  if(!form) return;

  if(!Forum.formDirty(form))
  {
    window.history.back();
    return;
  }

  var mbuttons = [
    {
      caption: msg_Yes,
      handler: function() {
        Forum.hide_user_msgbox();

        window.history.back();
      }
    },
    {
      caption: msg_No,
      handler: function() { Forum.hide_user_msgbox(); }
    }
  ];

  var on_hide = function () { form.elements['user_name'].focus(); };

  Forum.show_user_msgbox(msg_Confirmation, "<?php echo_js(text("MsgCancelConfirm")); ?>", 'icon-warning.gif', mbuttons, false, on_hide);

  return false;
}

var register_ajax = null;

function register()
{
  var form = document.getElementById('main_form');
  if(!form) return;

  Forum.show_sys_progress_indicator(true);

  if(!register_ajax)
  {
    register_ajax = new Forum.AJAX();

    register_ajax.timeout = TIMEOUT;

    register_ajax.beforestart = function() { break_check_new_messages(); };
    register_ajax.aftercomplete = function(error) { activate_check_new_messages(); };

    register_ajax.onload = function(text, xml)
    {
      try
      {
        var response = JSON.parse(text);

        Forum.handle_response_messages(response);

        if(response.success)
        {
          delay_redirect('profile.php');
          return;
        }

        form.elements["captcha_field"].value = "";
        show_hide_captcha(true);
      }
      catch(err)
      {
        Forum.handle_ajax_error(this, err.message, this.last_url, {});
      }

      Forum.show_sys_progress_indicator(false);
    };

    register_ajax.onerror = function(error, url, info)
    {
      Forum.show_sys_progress_indicator(false);

      Forum.handle_ajax_error(this, error, url, info);
    };
  }

  register_ajax.abort();
  register_ajax.resetParams();
  
  var formData = new FormData(form);

  formData.append('hash', get_protection_hash());
  formData.append('user_logged', user_logged);  
  formData.append('user_marker', user_marker);
  formData.append('register', "1");

  register_ajax.setFormData(formData);

  register_ajax.request("ajax/process.php");

  return false;
} // register
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

/ <span class="topic_title_main"><?php echo_html(text("Registration")); ?></span>
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

<form action="registration.php" id="main_form" method="post" onsubmit="return register();">

<table class="form_table registration_table">

<tr>
<th colspan="2"><?php echo_html(text("Registration")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("UserName")); ?>*:</td>
<td><input type="text" id="user_name" name="user_name" value="">
<div class="field_comment"><?php echo_html(text("UserNameComment")); ?></div>
</td>
</tr>

<tr>
<td><?php echo_html(text("Email")); ?>*:</td>
<td><input type="email" id="user_email" name="user_email" value="">
<div class="field_comment"><?php echo_html(text("UserEmailComment")); ?></div>
</td>
</tr>

<tr>
<td></td>
<td>
   <table class="checkbox_table">
   <tr>
     <td>
      <input type="checkbox" id="hide_email" name="hide_email" checked> 
     </td>
     <td>
      <label for="hide_email"><?php echo_html(text("HideEmail")); ?></label>
     </td>
   </tr>
   </table>
</td>
</tr>

<tr>
<td><?php echo_html(text("UserLogin")); ?>*:</td>
<td>
<input type="text" id="user_login" name="user_login" value="">
<div class="field_comment"><?php echo_html(text("UserLoginComment")); ?></div>
</td>
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

<tr class="captcha_area">
<td colspan="2"></td>
</tr>

<tr class="captcha_area">
<td style="vertical-align:top"></td>
<td style="vertical-align:top">
   <div class="captcha_comment"><?php echo_html(text("MsgSpamProtect")); ?></div>

   <table class="captcha_table">
   <tr>
     <td>
     <img class='captcha_picture' src='captcha/captcha.php?rnd=<?php echo(rand(1000, 9999)); ?>&session_var=captcha' id='captcha_picture' alt='Captcha' onclick='Forum.reload_captcha("captcha_picture", "captcha", "captcha_field")'>
     </td>
     <td>
     </td>
     <td>
     <input type="text" id="captcha_field" name="captcha_field" class="captcha_field" value="" autocomplete="off">
     </td>
   </tr>
   </table>

</td>
</tr>

<tr>
<td></td>
<td>

   <table class="checkbox_table">
   <tr>
     <td>
     <input type="checkbox" id="rules_agreemnt" name="rules_agreemnt">
     </td>
     <td>
     <label for="rules_agreemnt"><?php echo(text("RegistrationRulesAgreement")); ?></label>
     </td>
   </tr>
   </table>

</td>
</tr>


<tr>
<td colspan="2"></td>
</tr>

<tr>
<td colspan="2"><?php echo_html(text("RegistrationComment")); ?></td>
</tr>

<tr>
<td colspan="2"></td>
</tr>


<tr>
<td colspan="2" class="button_area">
<div class="left_buttons">
<input type="button" class="standard_button" value="<?php echo_html(text("Reset")); ?>" onclick="confirm_reset()">
<input type="button" class="standard_button" value="<?php echo_html(text("Back")); ?>" onclick="confirm_back()">
</div>
<div class="right_buttons">
<input type="submit" class="standard_button send_button" value="<?php echo_html(text("Register")); ?>">
</div>
<div class="clear_both">
</div>

</td>
</tr>

</table>

</form>

</div>