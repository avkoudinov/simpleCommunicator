<script>
function show_download_error(error)
{
  var mbuttons = [
    {
      caption: msg_OK,
      handler: function() { Forum.hide_user_msgbox(); }
    }
  ];

  Forum.show_user_msgbox(msg_Error, error, 'icon-error.gif', mbuttons);
}

function delete_avatar()
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
        handle_avatar_display();
        handle_photo_display();
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

  var on_hide = function () { form.elements['user_name'].focus(); };

  Forum.show_user_msgbox(msg_Confirmation, "<?php echo_js(text("MsgCancelConfirm")); ?>", 'icon-warning.gif', mbuttons, false, on_hide);

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

        if(response.success)
        {
          delay_redirect('guest_profile.php');
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
  formData.append('user_marker', user_marker);
  formData.append('save_guest_profile', "1");

  save_data_ajax.setFormData(formData);

  save_data_ajax.request("ajax/process.php");

  return false;
} // save_data

var export_import_ajax = null;

function export_import(export_import_action)
{
  var form = document.getElementById('main_form');
  if(!form) return;

  if(export_import_action == "export" && Forum.formDirty(form))
  {
    var mbuttons = [
      {
        caption: msg_OK,
        handler: function() { Forum.hide_user_msgbox(); }
      }
    ];

    Forum.show_user_msgbox(msg_Warning, "<?php echo_js(text("MsgSaveDataBeforeExport")); ?>", 'icon-warning.gif', mbuttons);

    return false;
  }
  
  Forum.show_sys_progress_indicator(true);

  if(!export_import_ajax)
  {
    export_import_ajax = new Forum.AJAX();

    export_import_ajax.timeout = TIMEOUT;

    export_import_ajax.beforestart = function() { break_check_new_messages(); };
    export_import_ajax.aftercomplete = function(error) { activate_check_new_messages(); };

    export_import_ajax.onload = function(text, xml)
    {
      try
      {
        var response = JSON.parse(text);

        Forum.handle_response_messages(response);

        if(response.success)
        {
          if(this.export_import_action == "export" && auxiliary_frame)
            auxiliary_frame.document.location.href = "ajax/download_file.php?file=profile_export";
          
          if(this.export_import_action == "import")
          {
            delay_redirect('guest_profile.php');
            return false;
          }
        }
      }
      catch(err)
      {
        Forum.handle_ajax_error(this, err.message, this.last_url, {});
      }

      Forum.show_sys_progress_indicator(false);
    };

    export_import_ajax.onerror = function(error, url, info)
    {
      Forum.show_sys_progress_indicator(false);

      Forum.handle_ajax_error(this, error, url, info);
    };
  }

  export_import_ajax.abort();
  export_import_ajax.resetParams();
  
  export_import_ajax.export_import_action = export_import_action;
  
  var formData = new FormData(form);

  formData.append('hash', get_protection_hash());
  formData.append('user_logged', user_logged);  
  formData.append('export_import_action', export_import_action);

  export_import_ajax.setFormData(formData);

  export_import_ajax.request("ajax/process.php");

  return false;
} // save_data

var scrolling_interval = "";
function start_scroll_preferred(direction)
{
  var elm = document.getElementById("preferred_forum_area");
  if(!elm) return;
  
  scrolling_interval = setInterval(function () { scroll_preferred(direction) }, 100);
}

function stop_scroll_preferred()
{
  if(scrolling_interval)
  {
    clearInterval(scrolling_interval);
    scrolling_interval = null;
  }
}

function scroll_preferred(direction)
{
  var elm = document.getElementById("preferred_forum_area");
  if(!elm) return;
  
  if(direction == 'up')
  {
    if(elm.scrollTop > 0) elm.scrollTop -= 120;
    else                  elm.scrollTop = 0;
  }
  else
  {
    elm.scrollTop += 120;
  }
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

  Forum.fireEvent(hide_pictures, 'update_view');
  Forum.fireEvent(donot_hide_adult_pictures, 'update_view');
}

function show_hide_ignore_guests_area()
{
  var ignore_guests = document.getElementById("ignore_guests");
  var ignore_guests_area = document.getElementById("ignore_guests_area");

  if(!ignore_guests || !ignore_guests_area) return;
  
  if(ignore_guests.checked)
    ignore_guests_area.style.display = "block";
  else
    ignore_guests_area.style.display = "none";
  
  var ignore_guests_except_areas = document.getElementsByClassName("ignore_guests_except_area");
  for(var i = 0; i < ignore_guests_except_areas.length; i++)
  {
    ignore_guests_except_areas[i].style.display = ignore_guests.checked ? "table-cell" : "none";
  }
}

function expand_statistics_list(elm)
{
  var parent_table = elm.parentNode;
  if(!parent_table) return;
  parent_table = parent_table.parentNode;
  if(!parent_table) return;
  parent_table = parent_table.parentNode;
  if(!parent_table) return;
  
  var elms = parent_table.getElementsByClassName("statistics_row_hidden");
  for(var i = elms.length-1; i >= 0; i--)
  {
    elms[i].classList.remove("statistics_row_hidden");
  }
  
  elm = elm.parentNode;
  if(elm) elm = elm.parentNode;
  if(elm) elm = elm.style.display = "none";
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
});
</script>

<!-- BEGIN: forum_bar -->

<div class="forum_bar">
<a href="forums.php"><?php echo_html(text("Forums")); ?></a>

<?php
$display = "style='display:none'";
if(!empty($topics_with_new_count)) $display = "";
?>
<span class="new topics_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span> 

/ <span class="topic_title_main"><?php echo_html(text("Profile")); ?></span>
</div>

<!-- END: forum_bar -->

<form action="guest_profile.php" id="main_form" enctype="multipart/form-data" method="post" onsubmit="return save_data();">

<input type="text" id="delete_avatar" name="delete_avatar" value="0" style="display:none">

<table class="form_table profile_table">

<tr>
<th><?php echo_html(text("Profile")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("UserName")); ?>:</td>
</tr>
<tr>
<td>
<?php
$readolny = $fmanager->is_master_admin() ? "class='read_only_field' readonly=''" : "";
?>
<input type="text" id="user_name" name="user_name" value="<?php echo_html($user_data["user_name"]); ?>" <?php echo($readolny); ?>>
<div class="field_comment"><?php echo_html(text("UserNameComment2")); ?></div>
</td>
</tr>

<?php
$aname_appendix = "";
if(!empty($user_data["aname"]))
  $aname_appendix = "&aname=" . $user_data["aname"];
?>

<tr>
<td><?php echo_html(text("Actions")); ?>:</td>
</tr>
<tr>
<td>
<a href="view_guest_profile.php?guest=<?php echo(xrawurlencode($user_data["user_name"])); ?><?php echo($aname_appendix); ?>"><?php echo_html(text("ProfilePreview")); ?></a></td>
</tr>

<?php if($fmanager->is_moderator_log_visible()): ?>
<tr>
<td><a href="moderation_log.php?user_name=<?php echo(xrawurlencode($user_data["user_name"])); ?>"><?php echo_html(text("ModeratorLog")); ?></a></td>
</tr>
<?php endif; ?>

<tr>
<td><a href="search.php?do_search=1&author=<?php echo_html(xrawurlencode($user_data["user_name"])); ?>&author_mode=created_topic"><?php echo_html(text("AllAuthorTopics")); ?></a></td>
</tr>

<tr>
<td><a href="search.php?do_search=1&author=<?php echo_html(xrawurlencode($user_data["user_name"])); ?>&author_mode=participating"><?php echo_html(text("AllTopicsWithAuthor")); ?></a></td>
</tr>

<tr>
<td><a href="search_topic.php?do_search=1&author=<?php echo_html(xrawurlencode($user_data["user_name"])); ?>&author_mode=last_posts"><?php echo_html(text("AuthorLastMessages")); ?></a></td>
</tr>

<tr>
<td><a href="search_topic.php?do_search=1&author=<?php echo_html(xrawurlencode($user_data["user_name"])); ?>&author_mode=last_topics"><?php echo_html(text("AuthorLastTopics")); ?></a></td>
</tr>

<tr>
<td><a href="search_topic.php?do_search=1&author=<?php echo_html(xrawurlencode($user_data["user_name"])); ?>&has_attachment=1&author_mode=last_posts"><?php echo_html(text("AuthorLastAttachments")); ?></a></td>
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
<div class="del_picture_button" title="<?php echo_html(text("Delete")); ?>" onclick="delete_avatar()"></div>
<img class="avatar_picture" src="<?php echo($picture); ?>" alt="<?php echo_html(text("Avatar")); ?>">
</div>

<div class="clear_both"></div>

</td>
</tr>

<tr>
<td></td>
</tr>

<tr>
<th class="subheader"><?php echo_html(text("AdditionalSettings")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("Skin")); ?>:</td>
</tr>
<tr>
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
</tr>
<tr class="skin_property skin_property_<?php echo_html($skin); ?>" style="display: <?php echo($skin == $user_data["skin"] ? "table-row" : "none"); ?>">
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
</tr>
<tr>
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
</tr>
<tr>
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
     <input type="checkbox" id="hide_ignored" name="hide_ignored" <?php echo_html(checked($user_data["hide_ignored"])); ?>> 
     </td>
     <td>
     <label for="hide_ignored"><?php echo_html(text("HideIgnored")); ?></label>
     </td>
   </tr>
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
<td></td>
</tr>

<tr>
<th class="subheader"><?php echo_html(text("ExtendedSettings")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("CustomCSS")); ?>:</td>
</tr>
<tr>
<td><textarea id="custom_css" name="custom_css" spellcheck="false"><?php echo_html($user_data["custom_css"]); ?></textarea>
<div class="field_comment"><?php echo_html(text("CustomCSSComment")); ?></div>
</td>
</tr>


<tr>
<td></td>
</tr>

<?php if(!empty($ignores)): ?>

<tr>
<th class="subheader"><?php echo_html(text("RemoveFromIgnoreList")); ?></th>
</tr>

<tr>
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
<td></td>
</tr>

<?php endif; ?>

<tr>
<th class="subheader"><?php echo_html(text("PreferredForums")); ?></th>
</tr>

<tr>
<td class="preferred_forums">

  <div style="position: relative">
    <div class="scroll_up" onmousedown="start_scroll_preferred('up')" onmouseup="stop_scroll_preferred()"></div>
    <div class="scroll_down" onmousedown="start_scroll_preferred('down')" onmouseup="stop_scroll_preferred()"></div>
    
    <div id="preferred_forum_area" class="preferred_forums_wrapper">
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
  </div>

</td>
</tr>

<tr>
<td class="button_area">
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

<h3 class="profile_caption"><?php echo_html(text("ExportImport")); ?></h2>

<table class="form_table profile_table" style="margin-bottom: 0px;margin-top: 0px">

<tr>
<th><?php echo_html(text("ExportImport")); ?></th>
</tr>

<tr>
<td><input type="file" data-placeholder="<?php echo_html(text("SelectFile")); ?>" id="profile_file" name="profile_file"></td>
</tr>

<tr>
<td></td>
</tr>

<tr>
<td class="button_area">
<div class="left_buttons">
</div>
<div class="right_buttons">
<input type="button" class="standard_button" value="<?php echo_html(text("Export")); ?>" onclick="return export_import('export');">
<input type="button" class="standard_button" value="<?php echo_html(text("Import")); ?>" onclick="return export_import('import');">
</div>
<div class="clear_both">
</div>
</td>
</tr>

</table>

</form>

<!-- Frame for dowloading -->
<iframe id="auxiliary_frame" name="auxiliary_frame" src="about:blank" style="display:none"></iframe>

<?php 
if(!empty($ignores) || !empty($ignored) || !empty($user_data["ignore_guests"])): 

$ignores_rows = array();
$ignored_rows = array();

foreach($ignores as $uid => $uname)
{
  $ignores_rows[] = "<a href='view_profile.php?uid=$uid'>" . escape_html($uname) . "</a>";
}

if(!empty($user_data["ignore_guests_blacklist"]))
{
  $ignored_guests = preg_split("/[\n\r]+/", $user_data["ignored_guests_blacklist"], -1, PREG_SPLIT_NO_EMPTY);
  foreach($ignored_guests as $guest)
  {
    $guest = $fmanager->display_name_to_name($guest);
    
    if($guest == "admin")
      $ignores_rows[] = '<a class="admin_link" href="view_guest_profile.php?guest=' . xrawurlencode($guest) . $aname_appendix . '">' . escape_html(text("MasterAdministrator")) . '</a>';
    else
      $ignores_rows[] = '<a class="guest_link" href="view_guest_profile.php?guest=' . xrawurlencode($guest) . $aname_appendix . '">' . escape_html($guest) . '</a>';
  }
}

if(!empty($user_data["ignore_guests_whitelist"]))
  {
  if(!empty($user_data["ignored_guests_whitelist"]))
  {
    $ignores_rows[] = escape_html(text("Guests") . ", " . text("IgnoreGuestsExcept") . ":");
    
    $ignored_guests = preg_split("/[\n\r]+/", $user_data["ignored_guests_whitelist"], -1, PREG_SPLIT_NO_EMPTY);
    foreach($ignored_guests as $guest)
    {
      $guest = $fmanager->display_name_to_name($guest);
      
      if($guest == "admin")
        $ignores_rows[] = '<a class="admin_link" href="view_guest_profile.php?guest=' . xrawurlencode($guest) . $aname_appendix . '">' . escape_html(text("MasterAdministrator")) . '</a>';
      else
        $ignores_rows[] = '<a class="guest_link" href="view_guest_profile.php?guest=' . xrawurlencode($guest) . $aname_appendix . '">' . escape_html($guest) . '</a>';
    }
  }
  else
  {
    $ignores_rows[] = escape_html(text("Guests"));  
  }
}

foreach($ignored as $uid => $uname)
{
  $ignored_rows[] = "<a href='view_profile.php?uid=$uid'>" . escape_html($uname) . "</a>";
}

$rowcount = max(count($ignores_rows), count($ignored_rows));
$row_class = "";
?>

<h3 class="profile_caption"><?php echo_html(text("IgnoreList")); ?></h2>

<table class="ignore_table">
<tr>
<th style="width:50%"><?php echo_html(text("MemberIgnores")); ?></th>
<th style="width:50%"><?php echo_html(text("MemberIgnored")); ?></th>
</tr>

<?php for($i = 0; $i < $rowcount; $i++): ?>
<tr class="<?php echo($row_class); ?>">
<td><div class="overflow_div wide_column"><?php if(empty($ignores_rows[$i])) echo "&nbsp;"; else echo $ignores_rows[$i]; ?></div></td>
<td><div class="overflow_div wide_column"><?php if(empty($ignored_rows[$i])) echo "&nbsp;"; else echo $ignored_rows[$i]; ?></div></td>
</td>
</tr>

<?php if($rowcount > 25 && $i == 20): 
$row_class = "statistics_row_hidden";
?>
<tr>
<td><div class="statistics_list_expander" onclick="expand_statistics_list(this)">...</div></td>
<td></td>
</tr>
<?php endif; ?>

<?php endfor; ?>

</table>

<?php endif; ?>


<?php if(!empty($hides) || !empty($hidden)): 

$hides_rows = array();
$hidden_rows = array();

foreach($hides as $uid => $uname)
{
  $hides_rows[] = "<a href='view_profile.php?uid=$uid'>" . escape_html($uname) . "</a>";
}

foreach($hidden as $uid => $uname)
{
  $hidden_rows[] = "<a href='view_profile.php?uid=$uid'>" . escape_html($uname) . "</a>";
}

$rowcount = max(count($hides_rows), count($hidden_rows));
$row_class = "";
?>

<h3 class="profile_caption"><?php echo_html(text("HideList")); ?></h2>

<table class="ignore_table">
<tr>
<th style="width:50%"><?php echo_html(text("MemberHides")); ?></th>
<th style="width:50%"><?php echo_html(text("MemberHidden")); ?></th>
</tr>

<?php for($i = 0; $i < $rowcount; $i++): ?>
<tr class="<?php echo($row_class); ?>">
<td><div class="overflow_div wide_column"><?php if(empty($hides_rows[$i])) echo "&nbsp;"; else echo $hides_rows[$i]; ?></div></td>
<td><div class="overflow_div wide_column"><?php if(empty($hidden_rows[$i])) echo "&nbsp;"; else echo $hidden_rows[$i]; ?></div></td>
</td>
</tr>

<?php if($rowcount > 25 && $i == 20): 
$row_class = "statistics_row_hidden";
?>
<tr>
<td><div class="statistics_list_expander" onclick="expand_statistics_list(this)">...</div></td>
<td></td>
</tr>
<?php endif; ?>

<?php endfor; ?>

</table>

<?php endif; ?>


<?php if(!empty($ignored_topics)): ?>

<h3 class="profile_caption"><?php echo_html(text("IgnoredTopics")); ?></h2>

<table class="topic_statistic_table">

<tr>
<th><?php echo_html(text("Topic")); ?></th>
<th><?php echo_html(text("Forum")); ?></th>
</tr>

<?php foreach($ignored_topics as $tinfo):
$not_preferred = "";
if(!empty($_SESSION["preferred_forums"]) && empty($_SESSION["preferred_forums"][$tinfo["fid"]])) $not_preferred = "not_preferred";
?>

<tr>
<td><div class="smart_break"><a href="topic.php?fid=<?php echo_html($tinfo["fid"]); ?>&tid=<?php echo_html($tinfo["tid"]); ?>&gotonew=1" rel="nofollow"><?php echo_html(postprocess_message($tinfo["name"])); ?></a></div></td>
<td><a href="forum.php?fid=<?php echo_html($tinfo["fid"]); ?>" class="<?php echo($not_preferred); ?>"><?php echo_html($tinfo["forum_name"]); ?></a></td>
</tr>

<?php endforeach; ?>

</table>

<?php endif; ?>

<div style="margin-bottom: 70px"></div>