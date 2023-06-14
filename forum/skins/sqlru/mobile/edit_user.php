
<script type='text/JavaScript'>
function do_delete_avatar()
{
  var f = document.getElementById("delete_avatar");
  if(!f) return;

  f.value = 1;

  handle_avatar_display();
}

function handle_avatar_display()
{
  var f = document.getElementById("delete_avatar");
  if(!f) return;

  var a1 = document.getElementById("avatar1");
  var a2 = document.getElementById("avatar2");

  if(!a1 || !a2) return;

  if(f.value == 1)
  {
    a2.style.display = 'none';
    a1.style.display = 'block';
  }
  else
  {
    a1.style.display = 'none';
    a2.style.display = 'block';
  }
}

function delete_photo()
{
  var f = document.getElementById("delete_photo");
  if(!f) return;

  f.value = 1;

  handle_photo_display();
}

function handle_photo_display()
{
  var f = document.getElementById("delete_photo");
  if(!f) return;

  var photo = document.getElementById("photo_container");
  if(!photo) return;

  if(f.value == 1)
  {
    photo.style.display = 'none';
  }
  else
  {
    photo.style.display = 'inline-block';
  }
}

function confirm_delete()
{
  var form = document.getElementById('main_form');
  if(!form) return false;

  var mbuttons = [
    {
      caption: msg_Yes,
      handler: function() {
        Forum.hide_user_msgbox();

        save_data('delete_user');
      }
    },
    {
      caption: msg_No,
      handler: function() { Forum.hide_user_msgbox(); }
    }
  ];

  Forum.show_user_msgbox(msg_Confirmation, "<?php echo_js(text("MsgUserDeleteConfirm")); ?>", 'icon-warning.gif', mbuttons, false);

  return false;
}


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
        handle_avatar_display();
        handle_photo_display();
        update_form_check_boxes(form);
      }
    },
    {
      caption: msg_No,
      handler: function() { Forum.hide_user_msgbox(); }
    }
  ];

  Forum.show_user_msgbox(msg_Confirmation, "<?php echo_js(text("MsgResetConfirm")); ?>", 'icon-question.gif', mbuttons, false);

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

  Forum.show_user_msgbox(msg_Confirmation, "<?php echo_js(text("MsgCancelConfirm")); ?>", 'icon-warning.gif', mbuttons, false);

  return false;
}

var save_data_ajax = null;

function save_data(action)
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

        if(response.ERROR_ELEMENT == 'password')
        {
          form.elements['password'].value = "";
          form.elements['password2'].value = "";
        }

        if(response.success && response.target_url)
        {
          delay_redirect(response.target_url);
          return;
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
  formData.append(action, "1");

  save_data_ajax.setFormData(formData);

  save_data_ajax.request("ajax/process.php");

  return false;
} // save_data
</script>

<!-- BEGIN: forum_bar -->

<div class="forum_bar">
<a href="forums.php"><?php echo_html(text("Forums")); ?></a> 

<?php
$display = "style='display:none'";
if(!empty($topics_with_new_count)) $display = "";
?>
<span class="new topics_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span> 

/ <a href="users.php"><?php echo_html(text("Users")); ?></a> / <span class="topic_title_main"><?php echo_html(text("EditUser")); ?></span>
</div>

<!-- END: forum_bar -->

<form action="edit_user.php" id="main_form" enctype="multipart/form-data" method="post" onsubmit="return save_data('save_user');">

<input type="hidden" id="uid" name="uid" value="<?php echo_html($user_data["id"]); ?>">

<input type="text" id="delete_avatar" name="delete_avatar" value="0" style="display:none">
<input type="text" id="delete_photo" name="delete_photo" value="0" style="display:none">

<table class="form_table profile_table">

<tr>
<th><?php echo_html(text("EditUser")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("UserName")); ?>*:</td>
</tr>
<tr>
<td><input type="text" id="user_name" name="user_name" value="<?php echo_html($user_data["user_name"]); ?>">
<div class="field_comment"><?php echo_html(text("UserNameComment2")); ?></div>
</td>
</tr>

<tr>
<td><?php echo_html(text("UserLogin")); ?>*:</td>
</tr>
<tr>
<td>
<input type="text" id="user_login" name="user_login" value="<?php echo_html($user_data["user_login"]); ?>">
<div class="field_comment"><?php echo_html(text("UserLoginComment2")); ?></div>
</td>
</tr>

<tr>
<td><?php echo_html(text("Email")); ?>*:</td>
</tr>
<tr>

<?php
if($fmanager->demo_mode()) $user_data["user_email"] = text("hidden");
?>

<td><input type="email" id="user_email" name="user_email" value="<?php echo_html($user_data["user_email"]); ?>"/></td>
</tr>

<tr>
<td>
   <table class="checkbox_table">
   <tr>
     <td>
     <input type="checkbox" id="activated" name="activated" <?php echo_html(checked($user_data["activated"])); ?>> 
     </td>
     <td>
     <label for="activated"><?php echo_html(text("Activated")); ?></label>
     </td>
   </tr>
   <tr>
     <td>
     <input type="checkbox" id="approved" name="approved" <?php echo_html(checked($user_data["approved"])); ?>> 
     </td>
     <td>
     <label for="approved"><?php echo_html(text("Approved")); ?></label>
     </td>
   </tr>
   <?php if($fmanager->is_master_admin()): ?>
   <tr>
     <td>
     <input type="checkbox" id="is_admin" name="is_admin" <?php echo_html(checked($user_data["is_admin"])); ?>> 
     </td>
     <td>
     <label for="is_admin"><?php echo_html(text("Administrator")); ?></label>
     </td>
   </tr>
   <?php endif; ?>
   <tr>
     <td>
     <input type="checkbox" id="privileged" name="privileged" <?php echo_html(checked($user_data["privileged"])); ?>> 
     </td>
     <td>
     <label for="privileged"><?php echo_html(text("PrivilegedMember")); ?></label>
     </td>
   </tr>
   <tr>
     <td>
     <input type="checkbox" id="privileged_topic_moderator" name="privileged_topic_moderator" <?php echo_html(checked($user_data["privileged_topic_moderator"])); ?>> 
     </td>
     <td>
     <label for="privileged_topic_moderator"><?php echo_html(text("PrivilegedTopicModerator")); ?></label>
     </td>
   </tr>
   </table>

<?php if(!empty($user_data["moderator"]) && empty($user_data["is_admin"])): ?>
</td>
</tr>
<tr>
<td><?php echo_html(text("Moderator")); ?>:</td>
</tr>
<tr>
<td>
   <table class="checkbox_table">
   <tr>
     <td>
     <input type="checkbox" id="global_ban_allowed" name="global_ban_allowed" <?php echo_html(checked($user_data["global_ban_allowed"])); ?>> 
     </td>
     <td>
     <label for="global_ban_allowed"><?php echo_html(text("GlobalBlockAllowed")); ?></label>
     </td>
   </tr>
   <tr>
     <td>
     <input type="checkbox" id="show_ip" name="show_ip" <?php echo_html(checked($user_data["show_ip"])); ?>> 
     </td>
     <td>
     <label for="show_ip"><?php echo_html(text("ShowIP")); ?></label>
     </td>
   </tr>
   </table>
<?php endif; ?>

</td>
</tr>

<tr>
<td><span class="number"><?php echo_html(text("Actions")); ?>:</span></td>
</tr>

<tr>
<td>
<a href="view_profile.php?uid=<?php echo_html($user_data["id"]); ?>"><?php echo_html(text("ProfilePreview")); ?></a>
</td>
</tr>

<?php if($fmanager->is_moderator_log_visible() || $fmanager->get_user_id() == reqvar("uid")): ?>
<tr>
<td><a href="user_moderation.php?uid=<?php echo_html($user_data["id"]); ?>#log"><?php echo_html(text("ModeratorLog")); ?></a></td>
</tr>
<?php endif; ?>


<tr>
<td>
<a class="moderator_link" href="user_moderation.php?uid=<?php echo_html($user_data["id"]); ?>"><?php echo_html(text("ModerateUser")); ?></a>
</td>
</tr>

<?php if(!empty($settings["rates_active"])): ?>
<tr>
<td>
<a class="moderator_link" href="rate_moderation.php?uid=<?php echo_html($user_data["id"]); ?>#moderation"><?php echo_html(text("ModerateRates")); ?></a>
</td>
</tr>
<?php endif; ?>

<tr>
<td>
<a href="ip_moderation.php?type=user_ips&user=<?php echo(xrawurlencode($user_data["user_name"])); ?>" class="moderator_link"><?php echo_html(text("ShowAuthorIPs")); ?></a>
</td>
</tr>

<tr>
<td>
<a href="ip_moderation.php?type=other_users&user=<?php echo(xrawurlencode($user_data["user_name"])); ?>" class="moderator_link"><?php echo_html(text("ShowMembersOfAuthorIPs")); ?></a>
</td>
</tr>

<tr>
<td></td>
</tr>

<tr>
<th class="subheader"><?php echo_html(text("Password")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("NewPassword")); ?>:</td>
</tr>
<tr>
<td><input type="password" id="password" name="password"></td>
</tr>
<tr>
<td><?php echo_html(text("PasswordConfirmation")); ?>:</td>
</tr>
<tr>
<td><input type="password" id="password2" name="password2"></td>
</tr>

<tr>
<td></td>
</tr>

<tr>
<th class="subheader"><?php echo_html(text("AdditionalInformation")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("Avatar")); ?>:</td>
</tr>
<tr>
<td><input type="file" placeholder="<?php echo_html(text("SelectFile")); ?>" id="avatar" name="avatar"/>

<div class="inner_label"><?php echo_html(text("AvatarComment")); ?></div>

<?php
$avatar_display1 = "block";
$avatar_display2 = "none";
$rnd = rand(1000, 9000);
$picture = $view_path . "images/guest.jpg";
if(!empty($user_data["avatar"]))
{
  $avatar_display1 = "none";
  $avatar_display2 = "block";
  
  $appendix = "?rnd=$rnd";
  if(!empty($user_data["avatar_ctime"])) $appendix = "?ctime=" . $user_data["avatar_ctime"];
  
  $picture = escape_html($user_data["avatar"]) . $appendix;
}
?>

<div id="avatar1" class="avatar_profile_container" style="display:<?php echo_html($avatar_display1); ?>">
<img class="avatar_picture" src="<?php echo($view_path); ?>images/guest.jpg" alt="<?php echo_html(text("Avatar")); ?>"><?php if(val_or_empty($user_data["self_blocked"]) == 2): ?><img class="mourning_band" src="<?php echo($view_path . "images/mourning_band.png"); ?>" alt="<?php echo_html(text("Avatar")); ?>"><?php endif; ?>
</div>

<div id="avatar2" class="avatar_profile_container" style="display:<?php echo_html($avatar_display2); ?>">
<div class="del_picture_button" title="<?php echo_html(text("Delete")); ?>" onclick="do_delete_avatar()"></div>
<img class="avatar_picture" src="<?php echo($picture); ?>" alt="<?php echo_html(text("Avatar")); ?>"><?php if(val_or_empty($user_data["self_blocked"]) == 2): ?><img class="mourning_band" src="<?php echo($view_path . "images/mourning_band.png"); ?>" alt="<?php echo_html(text("Avatar")); ?>"><?php endif; ?>
</div>

<div class="clear_both"></div>

</td>
</tr>

<tr>
<td><?php echo_html(text("Location")); ?>:</td>
</tr>
<tr>
<td><input type="text" id="location" name="location" value="<?php echo_html($user_data["location"]); ?>"/></td>
</tr>

<tr>
<td><?php echo_html(text("Homepage")); ?>:</td>
</tr>
<tr>
<td><input type="text" id="homepage" name="homepage" value="<?php echo_html($user_data["homepage"]); ?>"/></td>
</tr>

<tr>
<td><?php echo_html(text("Message")); ?>:</td>
</tr>
<tr>
<td><textarea id="message" name="message"><?php echo_html($user_data["message"]); ?></textarea>
<div class="field_comment"><?php echo_html(text("MessageComment")); ?></div></td>
</tr>

<tr>
<td><?php echo_html(text("Signature")); ?>:</td>
</tr>
<tr>
<td><textarea id="signature" name="signature"><?php echo_html($user_data["signature"]); ?></textarea>
<div class="field_comment"><?php echo_html(text("SignatureComment")); ?></div></td>
</tr>

<tr>
<td><?php echo_html(text("Information")); ?>:</td>
</tr>
<tr>
<td><textarea id="info" name="info"><?php echo_html($user_data["info"]); ?></textarea>
<div class="field_comment"><?php echo_html(text("InformationComment")); ?></div></td>
</tr>

<tr>
<td></td>
</tr>

<tr>
<th class="subheader"><?php echo_html(text("Photo")); ?></th>
</tr>
<tr>
<td><input type="file" placeholder="<?php echo_html(text("SelectFile")); ?>" id="photo" name="photo"/></td>
</tr>

<tr>
<td></td>
</tr>

<tr>
<td class="button_area">
<div class="left_buttons">
<input type="button" class="standard_button delete_button" value="<?php echo_html(text("Delete")); ?>" onclick="confirm_delete()"/>
<input type="button" class="standard_button" value="<?php echo_html(text("Back")); ?>" onclick="confirm_back()"/>
</div>
<div class="right_buttons">
<input type="submit" class="standard_button send_button" value="<?php echo_html(text("Save")); ?>"/>
</div>
<div class="clear_both">
</div>
</td>
</tr>

</table>

<?php
if(!empty($user_data["photo"])):

$appendix = "?rnd=$rnd";
if(!empty($user_data["photo_ctime"])) $appendix = "?ctime=" . $user_data["photo_ctime"];
  
$user_data["photo"] .= $appendix;
?>

<div style="text-align: center">
<div id="photo_container" class="photo_profile_container">
<div class="del_picture_button" title="<?php echo_html(text("Delete")); ?>" onclick="delete_photo()"></div>
<a href="<?php echo_html($user_data["photo"]); ?>" class="lightbox_image" target='_blank' title="<?php echo_html(text("Photo")); ?>: <?php echo_html($user_data["user_name"]); ?>"><img src="<?php echo_html($user_data["photo"]); ?>" alt="<?php echo_html(text("Photo")); ?>: <?php echo_html($user_data["user_name"]); ?>"></a>
</div>
</div>


<?php endif; ?>

</form>

<div style="margin-bottom: 70px"></div>