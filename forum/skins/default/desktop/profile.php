<script>
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

function confirm_general_logout()
{
  if(no_confirmation_of_any_actions)
  {
    Forum.hide_user_msgbox();
    document.location.href = "logout.php?hash=" + get_protection_hash() + "&all_sessions=1";
    return false;
  }

  var mbuttons = [
    {
      caption: msg_Yes,
      handler: function() {
        Forum.hide_user_msgbox();
        document.location.href = "logout.php?hash=" + get_protection_hash() + "&all_sessions=1";
      }
    },
    {
      caption: msg_No,
      handler: function() { Forum.hide_user_msgbox(); }
    }
  ];

  Forum.show_user_msgbox(msg_Confirmation, '<?php echo_js(text("MsgConfirmLogout")); ?>', 'icon-warning.gif', mbuttons);

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

        if(response.ERROR_ELEMENT == 'password')
        {
          form.elements['password'].value = "";
          form.elements['password2'].value = "";
        }

        if(response.ERROR_ELEMENT == 'current_password')
        {
          form.elements['current_password'].value = "";
        }

        if(response.success)
        {
          delay_redirect('profile.php');
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
  formData.append('save_profile', "1");

  save_data_ajax.setFormData(formData);

  save_data_ajax.request("ajax/process.php");

  return false;
} // save_data

var request_activation_ajax = null;

function request_activation()
{
  Forum.show_sys_progress_indicator(true);
  
  if(!request_activation_ajax)
  {
    request_activation_ajax = new Forum.AJAX();

    request_activation_ajax.timeout = TIMEOUT;

    request_activation_ajax.beforestart = function() { break_check_new_messages(); };
    request_activation_ajax.aftercomplete = function(error) { activate_check_new_messages(); };

    request_activation_ajax.onload = function(text, xml)
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

    request_activation_ajax.onerror = function(error, url, info)
    {
      Forum.show_sys_progress_indicator(false);

      Forum.handle_ajax_error(this, error, url, info);
    };
  }
  
  request_activation_ajax.abort();
  request_activation_ajax.resetParams();

  request_activation_ajax.setPOST('request_activation', "1");
  request_activation_ajax.setPOST('hash', get_protection_hash());
  request_activation_ajax.setPOST('user_logged', user_logged);

  request_activation_ajax.request("ajax/process.php");

  return false;
} // request_activation

function set_thematic_per_default_checked(cb)
{
  var elm = document.getElementById("thematic_per_default");
  if(elm && cb.checked) elm.checked = true;
}

function handle_adult_checkbox()
{
  var hide_pictures = document.getElementById('hide_pictures');
  var donot_hide_adult_pictures = document.getElementById('donot_hide_adult_pictures');
  
  if(!hide_pictures || !donot_hide_adult_pictures) return;
  
  if(hide_pictures.checked)
  {
    donot_hide_adult_pictures.checked = false;
    donot_hide_adult_pictures.disabled = true;
  }
  else
  {
    donot_hide_adult_pictures.disabled = false;
  }
}

function show_hide_ignore_guests_area()
{
  var ignore_guests = document.getElementById("ignore_guests_whitelist");
  var ignore_guests_area = document.getElementById("ignore_guests_whitelist_area");

  if(!ignore_guests || !ignore_guests_area) return;
  
  if(ignore_guests.checked)
    ignore_guests_area.style.display = "block";
  else
    ignore_guests_area.style.display = "none";
  
  ignore_guests = document.getElementById("ignore_guests_blacklist");
  ignore_guests_area = document.getElementById("ignore_guests_blacklist_area");

  if(!ignore_guests || !ignore_guests_area) return;
  
  if(ignore_guests.checked)
    ignore_guests_area.style.display = "block";
  else
    ignore_guests_area.style.display = "none";
}

function show_hide_notify_on_words_area()
{
  var notify_on_words = document.getElementById("notify_on_words");
  var notify_on_words_area = document.getElementById("notify_on_words_area");

  if(!notify_on_words || !notify_on_words_area) return;
  
  if(notify_on_words.checked)
    notify_on_words_area.style.display = "block";
  else
    notify_on_words_area.style.display = "none";
}

function skin_changed(new_skin)
{
  var properties = document.getElementsByClassName("skin_property");
  for(var i = 0; i < properties.length; i++)
  {
    properties[i].style.display = (properties[i].classList.contains("skin_property_" + new_skin)) ? "table-row" : "none";
  }
}

Forum.addXEvent(window, 'DOMContentLoaded', function () { 
  handle_adult_checkbox();
  show_hide_ignore_guests_area();
  show_hide_notify_on_words_area();
});
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

/ <a href="view_profile.php?uid=<?php echo_html($user_data["id"]); ?>"><?php echo_html(text("ProfilePreview")); ?></a>

/ <span class="topic_title_main"><?php echo_html(text("ProfileSettings")); ?></span>

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

<form action="profile.php" id="main_form" enctype="multipart/form-data" method="post" onsubmit="return save_data();">

<input type="text" id="delete_avatar" name="delete_avatar" value="0" style="display:none">
<input type="text" id="delete_photo" name="delete_photo" value="0" style="display:none">

<table class="form_table profile_table" style="margin-bottom: 0px">

<tr>
<th colspan="2"><?php echo_html(text("Profile")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("UserName")); ?>:</td>
<td><input type="text" id="user_name" name="user_name" value="<?php echo_html($user_data["user_name"]); ?>" class="read_only_field" readonly>
<div class="field_comment"><?php echo_html(text("UserNameComment2")); ?></div>
</td>
</tr>

<tr>
<td><?php echo_html(text("UserLogin")); ?>:</td>
<td>
<input type="text" id="user_login" name="user_login" value="<?php echo_html($user_data["user_login"]); ?>" class="read_only_field" readonly>
<div class="field_comment"><?php echo_html(text("UserLoginComment2")); ?></div>
</td>
</tr>

<?php
if($fmanager->demo_mode()) $user_data["user_email"] = text("hidden");
?>

<tr>
<td><?php echo_html(text("Email")); ?>*:</td>
<td><input type="email" id="user_email" name="user_email" value="<?php echo_html($user_data["user_email"]); ?>">

<?php if(!empty($user_data["activated"])): ?>
<div class="field_comment"><?php echo_html(text("UserEmailComment2")); ?></div>
<?php else: ?>
<div class="field_comment error_text" style="margin-bottom: 3px"><?php echo_html(text("WarningAccountNotActivated")); ?></div>
<input type="button" class="standard_button" value="<?php echo_html(text("RequestActivationLink")); ?>" onclick="request_activation()">
<?php endif; ?>

</td>
</tr>

<tr>
<td></td>
<td>
   <table class="checkbox_table">
   <tr>
     <td>
     <input type="checkbox" id="hide_email" name="hide_email" <?php echo_html(checked($user_data["hide_email"])); ?>> 
     </td>
     <td>
     <label for="hide_email"><?php echo_html(text("HideEmail")); ?></label>
     </td>
   </tr>
   </table>
</td>
</tr>

<tr>
<td><?php echo_html(text("Actions")); ?>:</td>
<td>
<a href="view_profile.php?uid=<?php echo_html($user_data["id"]); ?>" ><?php echo_html(text("ProfilePreview")); ?></a>
</td>
</tr>

<tr>
<td></td>
<td>
<a href="user_moderation.php?uid=<?php echo_html($user_data["id"]); ?>#log" ><?php echo_html(text("ModeratorLog")); ?></a>
</td>
</tr>

<tr>
<td></td>
<td><a href="search.php?do_search=1&author=<?php echo_html(xrawurlencode($user_data["user_name"])); ?>&author_mode=created_topic"><?php echo_html(text("AllAuthorTopics")); ?></a></td>
</tr>

<tr>
<td></td>
<td><a href="search.php?do_search=1&author=<?php echo_html(xrawurlencode($user_data["user_name"])); ?>&author_mode=participating"><?php echo_html(text("AllTopicsWithAuthor")); ?></a></td>
</tr>

<tr>
<td></td>
<td><a href="search_topic.php?do_search=1&author=<?php echo_html(xrawurlencode($user_data["user_name"])); ?>&author_mode=last_posts"><?php echo_html(text("AuthorLastMessages")); ?></a></td>
</tr>

<tr>
<td></td>
<td><a href="search_topic.php?do_search=1&author=<?php echo_html(xrawurlencode($user_data["user_name"])); ?>&author_mode=last_topics"><?php echo_html(text("AuthorLastTopics")); ?></a></td>
</tr>

<tr>
<td></td>
<td><a href="search_topic.php?do_search=1&author=<?php echo_html(xrawurlencode($user_data["user_name"])); ?>&has_attachment=1&author_mode=last_posts"><?php echo_html(text("AuthorLastAttachments")); ?></a></td>
</tr>

<tr>
<td></td>
<td><a href="search.php?do_search=1&author=<?php echo_html(xrawurlencode($user_data["user_name"])); ?>&author_mode=ignoring"><?php echo_html(text("IgnoredTopics")); ?></a></td>
</tr>

<tr>
<td></td>
<td><a href="search.php?do_search=1&author=<?php echo_html(xrawurlencode($user_data["user_name"])); ?>&author_mode=moderating"><?php echo_html(text("ModeratedTopics")); ?></a></td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<tr>
<td></td>
<td>
<?php
$aname_appendix = "";
if(!empty($user_data["aname"]) && $user_data["aname"] != "admin")
  $aname_appendix .= "&aname=" . $user_data["aname"];
?>
<a href="ip_moderation.php?type=user_ips&user=<?php echo(xrawurlencode($user_data["user_name"])); ?><?php echo($aname_appendix); ?>" class="moderator_link"><?php echo_html(text("ShowAuthorIPs")); ?></a> 
</td>
</tr>

<tr>
<td></td>
<td>
<a href="logout.php?hash=<?php echo_html($_SESSION["hash"]); ?>&all_sessions=1" class="moderator_link" onclick="return confirm_general_logout()"><?php echo_html(text("LogoutOnAll")); ?></a>
</td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<tr>
<th colspan="2" class="subheader"><?php echo_html(text("Password")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("CurrentPassword")); ?>:</td>
<td><input type="password" id="current_password" name="current_password"></td>
</tr>
<tr>
<td><?php echo_html(text("NewPassword")); ?>:</td>
<td><input type="password" id="password" name="password"></td>
</tr>
<tr>
<td><?php echo_html(text("PasswordConfirmation")); ?>:</td>
<td><input type="password" id="password2" name="password2"></td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<tr>
<th colspan="2" class="subheader"><?php echo_html(text("AdditionalInformation")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("Avatar")); ?>:</td>
<td><input type="file" data-placeholder="<?php echo_html(text("SelectFile")); ?>" id="avatar" name="avatar">

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
<img class="avatar_picture" src="<?php echo($view_path); ?>images/guest.jpg" alt="<?php echo_html(text("Avatar")); ?>">
</div>

<div id="avatar2" class="avatar_profile_container" style="display:<?php echo_html($avatar_display2); ?>">
<div class="del_picture_button" title="<?php echo_html(text("Delete")); ?>" onclick="do_delete_avatar()"></div>
<img class="avatar_picture" src="<?php echo($picture); ?>" alt="<?php echo_html(text("Avatar")); ?>">
</div>

<div class="clear_both"></div>

</td>
</tr>

<tr>
<td><?php echo_html(text("Location")); ?>:</td>
<td><input type="text" id="location" name="location" value="<?php echo_html($user_data["location"]); ?>"></td>
</tr>

<tr>
<td><?php echo_html(text("Homepage")); ?>:</td>
<td><input type="text" id="homepage" name="homepage" value="<?php echo_html($user_data["homepage"]); ?>"></td>
</tr>

<tr>
<td><?php echo_html(text("StatusMessage")); ?>:</td>
<td><textarea id="message" name="message"><?php echo_html($user_data["message"]); ?></textarea>
<div class="field_comment"><?php echo_html(text("MessageComment")); ?></div></td>
</tr>

<tr>
<td><?php echo_html(text("Signature")); ?>:</td>
<td><textarea id="signature" name="signature"><?php echo_html($user_data["signature"]); ?></textarea>
<div class="field_comment"><?php echo_html(text("SignatureComment")); ?></div></td>
</tr>

<tr>
<td><?php echo_html(text("Information")); ?>:</td>
<td><textarea id="info" name="info"><?php echo_html($user_data["info"]); ?></textarea>
<div class="field_comment"><?php echo_html(text("InformationComment")); ?></div></td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<tr>
<th colspan="2" class="subheader"><?php echo_html(text("AdditionalSettings")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("Skin")); ?>:</td>
<td>
<select name="skin" id="skin" onchange="skin_changed(this.value)">
<?php foreach($skin_list as $skin => $skin_title): ?>
<option value="<?php echo_html($skin); ?>" <?php echo(selected($user_data["skin"], $skin)); ?>><?php echo_html($skin_title); ?></option>
<?php endforeach; ?>
</select>
</td>
</tr>

<?php foreach($property_list as $skin => $properties): ?>

<tr class="skin_property skin_property_<?php echo_html($skin); ?>" style="display: <?php echo($skin == $user_data["skin"] ? "table-row" : "none"); ?>">
<td><?php echo_html(text("SkinSettings")); ?>:</td>
<td>
<?php foreach($properties as $property): ?>
   <table class="checkbox_table">
   <tr>
     <td>
     <!-- this live hack is because of placeholders -->
     <input type="hidden" name="skin_properties_placeholders[<?php echo_html($skin); ?>][<?php echo_html($property["name"]); ?>]" value="">
     <input type="checkbox" id="skin_properties_<?php echo_html($skin); ?>_<?php echo_html($property["name"]); ?>" name="skin_properties[<?php echo_html($skin); ?>][<?php echo_html($property["name"]); ?>]" value="1" <?php echo_html(checked(val_or_empty($user_data["skin_properties"][$skin][$property["name"]]))); ?>>
     </td>
     <td>
     <label for="skin_properties_<?php echo_html($skin); ?>_<?php echo_html($property["name"]); ?>"><?php echo_html($property["caption"]); ?></label>
     </td>
   </tr>
   </table>
<?php endforeach; ?>
</td>
</tr>

<?php endforeach; ?>

<tr>
<td><?php echo_html(text("InterfaceLanguage")); ?>:</td>
<td>
<select name="interface_language" id="interface_language">
<?php foreach($GLOBALS['LANGUAGES'] as $lang): ?>
<option value="<?php echo_html($lang); ?>" <?php echo(selected($user_data["interface_language"], $lang)); ?>><?php echo_html(text($lang)); ?></option>
<?php endforeach; ?>
</select>
</td>
</tr>

<tr>
<td><?php echo_html(text("TimeZone")); ?>:</td>
<td>
<select name="time_zone" id="time_zone">
<?php 
uasort($time_zones, "cmp_gmt_offset");
foreach($time_zones as $time_zone => $time_zone_name)
{
  $time_zone_name = format_gmt_offset(get_timezone_gmt_offset($time_zone)) . " " . $time_zone_name;
  
  $selected = (val_or_empty($user_data["time_zone"]) == $time_zone) ? "selected" : "";
  echo "<option value=" . escape_html($time_zone) . " $selected>" . escape_html($time_zone_name) . "</option>";
}
?>
</select>
</td>
</tr>

<tr>
<td></td>
<td>

   <table class="checkbox_table">
   <tr>
     <td>
     <input type="checkbox" id="hide_user_info" name="hide_user_info" <?php echo_html(checked($user_data["hide_user_info"])); ?>> 
     </td>
     <td>
     <label for="hide_user_info"><?php echo_html(text("HideUserInfo")); ?></label>
     </td>
   </tr>
   <tr>
     <td>
     <input type="checkbox" id="hide_user_avatars" name="hide_user_avatars" <?php echo_html(checked($user_data["hide_user_avatars"])); ?>> 
     </td>
     <td>
     <label for="hide_user_avatars"><?php echo_html(text("HideUserAvatars")); ?></label>
     </td>
   </tr>
   <tr>
     <td>
     <input type="checkbox" id="hide_pictures" name="hide_pictures" <?php echo_html(checked($user_data["hide_pictures"])); ?> onchange="handle_adult_checkbox()"> 
     </td>
     <td>
     <label for="hide_pictures"><?php echo_html(text("HidePictures")); ?></label>
     </td>
   </tr>
   <tr>
     <td>
     <input type="checkbox" id="donot_hide_adult_pictures" name="donot_hide_adult_pictures" <?php echo_html(checked($user_data["donot_hide_adult_pictures"])); ?>> 
     </td>
     <td>
     <label for="donot_hide_adult_pictures"><?php echo_html(text("DontHideAdult")); ?></label>
     </td>
   </tr>
   <tr>
     <td>
     <input type="checkbox" id="hide_comments" name="hide_comments" <?php echo_html(checked($user_data["hide_comments"])); ?> onchange="set_thematic_per_default_checked(this)"> 
     </td>
     <td>
     <label for="hide_comments"><?php echo_html(text("HideComments")); ?></label>
     </td>
   </tr>
   <tr>
     <td>
     <input type="checkbox" id="thematic_per_default" name="thematic_per_default" <?php echo_html(checked($user_data["thematic_per_default"])); ?>> 
     </td>
     <td>
     <label for="thematic_per_default"><?php echo_html(text("ThematicPostPerDefault")); ?></label>
     </td>
   </tr>
   <tr>
     <td>
     <input type="checkbox" id="hide_ignored" name="hide_ignored" <?php echo_html(checked($user_data["hide_ignored"])); ?>> 
     </td>
     <td>
     <label for="hide_ignored"><?php echo_html(text("HideIgnored")); ?></label>
     </td>
   </tr>
   <tr>
     <td>
     <input type="checkbox" id="no_private_messages" name="no_private_messages" <?php echo_html(checked($user_data["no_private_messages"])); ?>> 
     </td>
     <td>
     <label for="no_private_messages"><?php echo_html(text("NoPrivateMessages")); ?></label>
     </td>
   </tr>
   <tr>
     <td>
     <input type="checkbox" id="turnoff_personal_appeals" name="turnoff_personal_appeals" <?php echo_html(checked($user_data["turnoff_personal_appeals"])); ?>> 
     </td>
     <td>
     <label for="turnoff_personal_appeals"><?php echo_html(text("TurnOffPersonalAppeals")); ?></label>
     </td>
   </tr>
   <tr>
     <td>
     <input type="checkbox" id="notify_about_new_users" name="notify_about_new_users" <?php echo_html(checked($user_data["notify_about_new_users"])); ?>> 
     </td>
     <td>
     <label for="notify_about_new_users"><?php echo_html(text("InformAboutNewUsers")); ?></label>
     </td>
   </tr>
   <tr>
     <td>
     <input type="checkbox" id="notify_citation" name="notify_citation" <?php echo_html(checked($user_data["notify_citation"])); ?>> 
     </td>
     <td>
     <label for="notify_citation"><?php echo_html(text("NotifyOnCitation")); ?></label>
     </td>
   </tr>
   <tr>
     <td>
     <input type="checkbox" id="notify_on_words" name="notify_on_words" <?php echo_html(checked($user_data["notify_on_words"])); ?> onchange="show_hide_notify_on_words_area()"> 
     </td>
     <td>
     <label for="notify_on_words"><?php echo_html(text("NotifyOnWords")); ?></label>
     </td>
   </tr>
   </table>

<div id="notify_on_words_area">
<textarea id="words_to_notify" name="words_to_notify"><?php echo_html($user_data["words_to_notify"]); ?></textarea>
<div class="field_comment"><?php echo_html(text("NotifyOnWordsComment")); ?></div>
</div>

   <table class="checkbox_table">
   <tr>
     <td>
     <input type="checkbox" id="send_notifications" name="send_notifications" <?php echo_html(checked($user_data["send_notifications"])); ?>> 
     </td>
     <td>
     <label for="send_notifications"><?php echo_html(text("SendNotifications")); ?></label>
     </td>
   </tr>
   <tr>
     <td>
     <input type="checkbox" id="turnoff_events" name="turnoff_events" <?php echo_html(checked($user_data["turnoff_events"])); ?>> 
     </td>
     <td>
     <label for="turnoff_events"><?php echo_html(text("TurnOffEvents")); ?></label>
     </td>
   </tr>
   <?php if(!empty($settings["rates_active"])): ?>
   <tr>
     <td>
     <input type="checkbox" id="donot_notify_on_rates" name="donot_notify_on_rates" <?php echo_html(checked($user_data["donot_notify_on_rates"])); ?>> 
     </td>
     <td>
     <label for="donot_notify_on_rates"><?php echo_html(text("NoNotificationsOnRates")); ?></label>
     </td>
   </tr>
   <?php endif; ?>
   </table>

   <table class="checkbox_table">
   <tr>
     <td>
     <input type="checkbox" id="ignore_guests_whitelist" name="ignore_guests_whitelist" <?php echo_html(checked($user_data["ignore_guests_whitelist"])); ?> onchange="Forum.invert_pair_checkbox(this, 'ignore_guests_blacklist'); show_hide_ignore_guests_area();"> 
     </td>
     <td>
     <label for="ignore_guests_whitelist"><?php echo_html(text("IgnoreGuestsWhitelist")); ?></label>
     </td>
   </tr>
   </table>

    <div id="ignore_guests_whitelist_area">
    <textarea id="ignored_guests_whitelist" name="ignored_guests_whitelist"><?php echo_html($user_data["ignored_guests_whitelist"]); ?></textarea>
    <div class="field_comment"><?php echo_html(text("IgnoreGuestsComment")); ?></div>
    </div>

   <table class="checkbox_table">
   <tr>
     <td>
     <input type="checkbox" id="ignore_guests_blacklist" name="ignore_guests_blacklist" <?php echo_html(checked($user_data["ignore_guests_blacklist"])); ?> onchange="Forum.invert_pair_checkbox(this, 'ignore_guests_whitelist'); show_hide_ignore_guests_area();"> 
     </td>
     <td>
     <label for="ignore_guests_blacklist"><?php echo_html(text("IgnoreGuestsBlacklist")); ?></label>
     </td>
   </tr>
   </table>

    <div id="ignore_guests_blacklist_area">
    <textarea id="ignored_guests_blacklist" name="ignored_guests_blacklist"><?php echo_html($user_data["ignored_guests_blacklist"]); ?></textarea>
<div class="field_comment"><?php echo_html(text("IgnoreGuestsComment")); ?></div>
</div>

   <table class="checkbox_table">
   <tr>
     <td>
     <input type="checkbox" id="ignore_new_guests" name="ignore_new_guests" <?php echo_html(checked($user_data["ignore_new_guests"])); ?>> 
     </td>
     <td>
     <label for="ignore_new_guests"><?php echo_html(text("IgnoreNewGuests")); ?></label>
     </td>
   </tr>
   </table>

</td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<tr>
<th colspan="2" class="subheader"><?php echo_html(text("ExtendedSettings")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("CustomCSS")); ?>:</td>
<td><textarea id="custom_css" name="custom_css" spellcheck="false"><?php echo_html($user_data["custom_css"]); ?></textarea>
<div class="field_comment"><?php echo_html(text("CustomCSSComment")); ?></div>
</td>
</tr>

<tr>
<td><?php echo_html(text("CustomSmiles")); ?>:</td>
<td><textarea id="custom_smiles" name="custom_smiles" spellcheck="false"><?php echo_html($user_data["custom_smiles"]); ?></textarea>
<div class="field_comment"><?php echo_html(text("CustomSmilesComment")); ?></div>
</td>
</tr>

<tr>
<td></td>
<td><input type="file" data-placeholder="<?php echo_html(text("UploadCustomSmiles")); ?>" id="add_custom_smiles" name="add_custom_smiles[]">
</td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<?php if(!empty($ignores)): ?>

<tr>
<th colspan="2" class="subheader"><?php echo_html(text("RemoveFromIgnoreList")); ?></th>
</tr>

<tr>
<td>
</td>
<td>

   <?php if(!empty($ignores)): ?>
   <table class="checkbox_table">
    <?php foreach($ignores as $uid => $uname): 
    $checked = "";
    if(!empty($_REQUEST["remove_from_ignore"]) && in_array($uid, $_REQUEST["remove_from_ignore"])) $checked = "checked";
    ?>
     <tr>
       <td>
        <input type="checkbox" name="remove_from_ignore[]" value="<?php echo($uid); ?>" <?php echo($checked); ?>>
       </td>
       <td>
        <a href="view_profile.php?uid=<?php echo($uid); ?>"><?php echo_html($uname); ?></a>
       </td>
     </tr>
    <?php endforeach; ?>
   </table>
   <?php endif; ?>

</td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<?php endif; ?>

<?php if(empty($user_data["blocked"])): ?>

<tr>
<th colspan="2" class="subheader"><?php echo_html(text("Block")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("Period")); ?>:</td>
<td>
  <table class="period_table">
  <tr>
  <td><?php echo_html(text("Days")); ?></td>
  <td><?php echo_html(text("Hours")); ?></td>
  <td><?php echo_html(text("Minutes")); ?></td>
  </tr>
  <tr>
  <td><select name="days">
  <option value="" selected="selected">-</option>
  <?php for($i = 1; $i <= 30; $i++): ?>
  <option value="<?php echo_html($i); ?>"><?php echo_html($i); ?></option>
  <?php endfor; ?>
  </select>
  </td>
  <td><select name="hours">
  <option value="" selected="selected">-</option>
  <?php for($i = 1; $i <= 24; $i++): ?>
  <option value="<?php echo_html($i); ?>"><?php echo_html($i); ?></option>
  <?php endfor; ?>
  </select>
  </td>
  <td><select name="minutes">
  <option value="" selected="selected">-</option>
  <?php for($i = 1; $i <= 60; $i++): ?>
  <option value="<?php echo_html($i); ?>"><?php echo_html($i); ?></option>
  <?php endfor; ?>
  </select>
  </td>
  </tr>
  </table>
</td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<?php endif; ?>

<tr>
<th colspan="2" class="subheader"><?php echo_html(text("PreferredForums")); ?></th>
</tr>

<tr>
<td colspan="2" class="preferred_forums">

  <div class="preferred_forums_wrapper">
  <div>
   <table class="checkbox_table">
  <?php
  foreach($all_forum_list as $fid => $finfo):
  $checked = !empty($user_data["preferred_forums"][$fid]) ? "checked" : "";
  ?>
   <tr>
     <td>
     <input type="checkbox" id="preferred_forum_<?php echo_html($fid); ?>" name="preferred_forums[<?php echo_html($fid); ?>]" value="<?php echo_html($fid); ?>" <?php echo($checked); ?>>
     </td>
     <td>
     <label for="preferred_forum_<?php echo_html($fid); ?>"><?php echo_html($finfo["name"]); ?></label>
     </td>
   </tr>
  <?php endforeach; ?>
   </table>
  </div>
  </div>

</td>
</tr>

<tr>
<th colspan="2" class="subheader"><?php echo_html(text("Photo")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("Photo")); ?>:</td>
<td><input type="file" data-placeholder="<?php echo_html(text("SelectFile")); ?>" id="photo" name="photo"></td>
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
<input type="submit" class="standard_button send_button" value="<?php echo_html(text("Save")); ?>">
</div>
<div class="clear_both">
</div>
</td>
</tr>

</table>

</form>

<?php
if(!empty($user_data["photo"])):

$appendix = "?rnd=$rnd";
if(!empty($user_data["photo_ctime"])) $appendix = "?ctime=" . $user_data["photo_ctime"];
  
$user_data["photo"] .= $appendix;
?>

<div style="text-align: center;margin-top:40px">
<div id="photo_container" class="photo_profile_container">
<div class="del_picture_button" title="<?php echo_html(text("Delete")); ?>" onclick="delete_photo()"></div>
<a href="<?php echo_html($user_data["photo"]); ?>" class="lightbox_image" target='_blank' title="<?php echo_html(text("Photo")); ?>: <?php echo_html($user_data["user_name"]); ?>"><img src="<?php echo_html($user_data["photo"]); ?>" alt="<?php echo_html(text("Photo")); ?>: <?php echo_html($user_data["user_name"]); ?>"></a>
</div>
</div>

<?php endif; ?>

<div style="margin-bottom: 70px"></div>

</div>
