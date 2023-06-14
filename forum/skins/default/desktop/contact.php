
<script type='text/JavaScript'>
function confirm_reset()
{
  var form = document.getElementById("main_form");
  if(!form) return;

  if(!Forum.formDirty(form))
  {
    form.elements['email'].focus();
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

  var on_hide = function () { form.elements['email'].focus(); };

  Forum.show_user_msgbox(msg_Confirmation, "<?php echo_js(text("MsgResetConfirm")); ?>", 'icon-question.gif', mbuttons, false, on_hide);

  return false;
}

function confirm_back()
{
  var form = document.getElementById("main_form");
  if(!form) return;

  if(!Forum.formDirty(form))
  {
    delay_redirect('<?php echo_html($target_url); ?>');
    return;
  }

  var mbuttons = [
    {
      caption: msg_Yes,
      handler: function() {
        Forum.hide_user_msgbox();

        delay_redirect('<?php echo_html($target_url); ?>');
      }
    },
    {
      caption: msg_No,
      handler: function() { Forum.hide_user_msgbox(); }
    }
  ];

  var on_hide = function () { form.elements['email'].focus(); };

  Forum.show_user_msgbox(msg_Confirmation, "<?php echo_js(text("MsgCancelConfirm")); ?>", 'icon-warning.gif', mbuttons, true, on_hide);

  return false;
}

var send_message_ajax = null;

function send_message()
{
  var form = document.getElementById('main_form');
  if(!form) return;

  Forum.show_sys_progress_indicator(true);

  if(!send_message_ajax)
  {
    send_message_ajax = new Forum.AJAX();

    send_message_ajax.timeout = TIMEOUT;

    send_message_ajax.beforestart = function() { break_check_new_messages(); };
    send_message_ajax.aftercomplete = function(error) { activate_check_new_messages(); };

    send_message_ajax.onload = function(text, xml)
    {
      try
      {
        var response = JSON.parse(text);

        Forum.handle_response_messages(response);

        if(form.elements["captcha_field"] &&
           response.ERROR_ELEMENT == "captcha_field")
        {
          form.elements["captcha_field"].value = "";
          show_hide_captcha(true);
        }

        if(response.success)
        {
          form.elements["subject"].value = "";
          form.elements["message"].value = "";
          form.elements["captcha_field"].value = "";
          form.elements["rules_agreemnt"].checked = false;
          
          show_hide_captcha(true);
        }
      }
      catch(err)
      {
        Forum.handle_ajax_error(this, err.message, this.last_url, {});
      }
      
      Forum.show_sys_progress_indicator(false);
    };

    send_message_ajax.onerror = function(error, url, info)
    {
      Forum.show_sys_progress_indicator(false);

      Forum.handle_ajax_error(this, error, url, info);
    };
  }
  
  send_message_ajax.abort();
  send_message_ajax.resetParams();

  var formData = new FormData(form);

  formData.append('hash', get_protection_hash());
  formData.append('user_logged', user_logged);  
  formData.append('user_marker', user_marker);
  formData.append('send_message', "1");

  send_message_ajax.setFormData(formData);

  send_message_ajax.request("ajax/process.php");

  return false;
} // send_message

function handle_enter(ev)
{
  if(ev.ctrlKey && (ev.keyCode == 13 || ev.keyCode == 10))
  {
    send_message();
  }
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

/ <span class="topic_title_main"><?php echo_html(text("Contact")); ?></span>
</div>

<div class="forum_action_bar">
<table>
<tr>
<td>
<?php
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

<form action="contact.php" id="main_form" method="post" onsubmit="return send_message();">

<table class="form_table contact_table">

<tr>
<th colspan="2"><?php echo_html(text("Contact")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("Email")); ?>*:</td>
<td><input type="email" id="email" name="email" value="<?php echo_html($sender_email); ?>"/></td>
</tr>

<tr>
<td><?php echo_html(text("Subject")); ?>*:</td>
<td><input type="text" id="subject" name="subject" value=""/></td>
</tr>

<tr>
<td style="vertical-align:top"><?php echo_html(text("Message")); ?>*:</td>
<td><textarea id="message" name="message" onkeypress="return handle_enter(event)"></textarea></td>
</tr>

<tr class="captcha_area">
<td style="vertical-align:top"></td>
<td style="vertical-align:top">
   <div class="captcha_comment"><?php echo_html(text("MsgSpamProtect")); ?></div>

   <table class="captcha_table">
   <tr>
     <td>
   <img class='captcha_picture' src='captcha/captcha.php?rnd=<?php echo(rand(1000, 9999)); ?>&session_var=captcha' id='captcha_picture' alt='Captcha' onclick='Forum.reload_captcha("captcha_picture", "captcha", "captcha_field")'/>
     </td>
     <td>
     </td>
     <td>
   <input type="text" id="captcha_field" name="captcha_field" class="captcha_field" value="" autocomplete="off" onkeypress="return handle_enter(event)"/>
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
     <label for="rules_agreemnt"><?php echo(text("PostRulesAgreement")); ?></label>
     </td>
   </tr>
   </table>

</td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<tr>
<td colspan="2" class="button_area">
<div class="left_buttons">
<input type="button" class="standard_button" value="<?php echo_html(text("Reset")); ?>" onclick="confirm_reset()"/>
<input type="button" class="standard_button" value="<?php echo_html(text("Back")); ?>" onclick="confirm_back()"/>
</div>
<div class="right_buttons">
<input type="submit" class="standard_button send_button" value="<?php echo_html(text("Send")); ?>"/>
</div>
<div class="clear_both">
</div>
</td>
</tr>

</table>

</form>

</div>