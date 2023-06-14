<script type='text/JavaScript'>
function confirm_reset()
{
  var form = document.getElementById("main_form");
  if(!form) return;

  if(!Forum.formDirty(form))
  {
    return;
  }

  var mbuttons = [
    {
      caption: msg_Yes,
      handler: function() {
        Forum.hide_user_msgbox();

        form.reset();
        form.elements['email'].focus();
      }
    },
    {
      caption: msg_No,
      handler: function() { Forum.hide_user_msgbox(); }
    }
  ];

  var on_hide = function () { form.elements['email'].focus(); };

  Forum.show_user_msgbox(msg_Confirmation, "<?php echo_js(text("MsgResetConfirm")); ?>", 'icon-question.gif', mbuttons, on_hide);

  return false;
}

function confirm_back()
{
  var form = document.getElementById("main_form");
  if(!form) return;

  if(!Forum.formDirty(form))
  {
    delay_redirect('installation.php');
    return;
  }

  var mbuttons = [
    {
      caption: msg_Yes,
      handler: function() {
        Forum.hide_user_msgbox();

        delay_redirect('installation.php');
      }
    },
    {
      caption: msg_No,
      handler: function() { Forum.hide_user_msgbox(); }
    }
  ];

  Forum.show_user_msgbox(msg_Confirmation, "<?php echo_js(text("MsgCancelConfirm")); ?>", 'icon-warning.gif', mbuttons);

  return false;
}

var install_ajax = null;

function install()
{
  var form = document.getElementById('main_form');
  if(!form) return;

  Forum.show_sys_progress_indicator(true);

  if(!install_ajax)
  {
    install_ajax = new Forum.AJAX();

    install_ajax.timeout = 10*TIMEOUT;

    install_ajax.beforestart = function() { break_check_new_messages(); };
    install_ajax.aftercomplete = function(error) { activate_check_new_messages(); };

    install_ajax.onload = function(text, xml)
    {
      try
      {
        var response = JSON.parse(text);

        Forum.handle_response_messages(response);

        if(response.success)
        {
          delay_redirect('settings.php');
          return;
        }
      }
      catch(err)
      {
        Forum.handle_ajax_error(this, err.message, this.last_url, {});
      }
      
      Forum.show_sys_progress_indicator(false);
    };

    install_ajax.onerror = function(error, url, info)
    {
      Forum.show_sys_progress_indicator(false);

      Forum.handle_ajax_error(this, error, url, info);
    };
  }

  install_ajax.abort();
  install_ajax.resetParams();
  
  var formData = new FormData(form);

  formData.append('hash', get_protection_hash());
  formData.append('user_logged', user_logged);  
  formData.append('install', "1");

  install_ajax.setFormData(formData);

  install_ajax.request("ajax/process.php");

  return false;
} // install
</script>


<?php if($installed): ?>

<div class="text_content">

<h1 style="margin-top: 0px"><?php echo_html(text("InstallDeniedComment1")); ?></h1>

<p><?php echo_html(text("InstallDeniedComment2")); ?><p>

<p><?php echo_html(text("InstallDeniedComment3")); ?><p>

</div>

<?php elseif(empty($_SESSION["install_lang"])): ?>

<table class="form_table settings_table install_table">

<tr>
<th><?php echo_html(text("Installation")); ?></th>
</tr>

<tr>
<td class="install_langs">

   <?php foreach($ACTIVE_LANGUAGES as $lang): ?>
   <a href="installation.php?lang=<?php echo($lang); ?>" class="install_button" style="background-image:url('<?php echo($view_path); ?>lang/<?php echo($lang); ?>/images/<?php echo($lang); ?>.gif');"><?php echo_html(text($lang)); ?></a>
   <?php endforeach; ?>

</td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

</table>

<?php else: ?>

<form action="installation.php" id="main_form" method="post" onsubmit="return install();">

<input type="hidden" id="lang" name="lang" value="<?php echo_html($_SESSION["install_lang"]); ?>">

<table class="form_table settings_table install_table">

<tr>
<th colspan="2"><?php echo_html(text("Installation")); ?></th>
</tr>

<tr>
<th colspan="2" class="subheader"><?php echo_html(text("AdminData")); ?></th>
</tr>

<tr>
<td colspan="2"><?php echo_html(text("InstallMasterAdminComment")); ?></td>
</tr>

<tr>
<td style="vertical-align: top"><?php echo_html(text("ForumName")); ?>*:</td>
<td>
<input type="text" id="forum_name" name="forum_name" value=""/>
</td>
</tr>

<tr>
<td><?php echo_html(text("UserLogin")); ?>*:</td>
<td>
<input type="text" id="user_login" name="user_login" value="admin" class="read_only_field" readonly>
</td>
</tr>

<tr>
<td style="vertical-align: top"><?php echo_html(text("Email")); ?>*:</td>
<td>
<input type="email" id="email" name="email" value=""/>
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

<tr>
<th colspan="2" class="subheader"><?php echo_html(text("DatabaseConnection")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("DatabaseType")); ?>*:</td>
<td>
<select name="db_type" id="db_type">
<?php foreach($SUPPORTED_DATABASES as $db => $dbname): ?>
<option value="<?php echo_html($db); ?>" <?php echo($db == "MySQL" ? "selected" : ""); ?>><?php echo_html($dbname); ?></option>
<?php endforeach; ?>
</select>
</td>
</tr>

<tr>
<td style="vertical-align: top"><?php echo_html(text("DatabaseServer")); ?>*:</td>
<td>
<input type="text" id="db_server" name="db_server" value="localhost"/>
</td>
</tr>

<tr>
<td style="vertical-align: top"><?php echo_html(text("DatabaseName")); ?>*:</td>
<td>
<input type="text" id="db_name" name="db_name" value=""/>
<div class="field_comment"><?php echo_html(text("DBCreationComment")); ?></div>
</td>
</tr>

<tr>
<td style="vertical-align: top"><?php echo_html(text("DatabaseUser")); ?>*:</td>
<td>
<input type="text" id="db_user" name="db_user" value=""/>
<div class="field_comment"><?php echo_html(text("DBUserComment")); ?></div>
</td>
</tr>

<tr>
<td><?php echo_html(text("DatabasePassword")); ?>*:</td>
<td><input type="password" id="db_password" name="db_password"></td>
</tr>

<tr>
<td style="vertical-align: top"><?php echo_html(text("DatabaseTablePrefix")); ?>*:</td>
<td>
<input type="text" id="db_prefix" name="db_prefix" value="v1"/>
<div class="field_comment"><?php echo_html(text("DBPrefixComment")); ?></div>
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
<input type="submit" class="standard_button send_button" value="<?php echo_html(text("Install")); ?>"/>
</div>
<div class="clear_both">
</div>
</td>
</tr>

</table>

</form>

<?php endif; ?>

