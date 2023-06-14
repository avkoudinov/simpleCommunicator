
<script type='text/JavaScript'>
function confirm_reset()
{
  var form = document.getElementById("main_form");
  if(!form) return;

  if(!Forum.formDirty(form))
  {
    form.elements['forum_name'].focus();
    return;
  }

  var mbuttons = [
    {
      caption: msg_Yes,
      handler: function() {
        Forum.hide_user_msgbox();

        form.reset();
        update_form_check_boxes(form);
        
        show_hide_password();
      }
    },
    {
      caption: msg_No,
      handler: function() { Forum.hide_user_msgbox(); }
    }
  ];

  var on_hide = function () { form.elements['forum_name'].focus(); };

  Forum.show_user_msgbox(msg_Confirmation, "<?php echo_js(text("MsgResetConfirm")); ?>", 'icon-question.gif', mbuttons, false, on_hide);

  return false;
}

function confirm_back()
{
  var form = document.getElementById("main_form");
  if(!form) return;

  if(!Forum.formDirty(form))
  {
    delay_redirect('forums.php');
    return;
  }

  var mbuttons = [
    {
      caption: msg_Yes,
      handler: function() {
        Forum.hide_user_msgbox();

        delay_redirect('forums.php');
      }
    },
    {
      caption: msg_No,
      handler: function() { Forum.hide_user_msgbox(); }
    }
  ];

  var on_hide = function () { form.elements['forum_name'].focus(); };

  Forum.show_user_msgbox(msg_Confirmation, "<?php echo_js(text("MsgCancelConfirm")); ?>", 'icon-warning.gif', mbuttons, false, on_hide);

  return false;
}

function search_on_enter(event)
{
  if(event.keyCode != 13) return true;

  search_user();

  return false;
}

var search_user_ajax = null;

function search_user()
{
  var form = document.getElementById('main_form');
  if(!form) return false;

  if(form.elements['user_to_search'].value == '') 
  {
    form.elements['user_to_search'].focus();
    return false;
  }

  var search_user_button = document.getElementById('search_user_button');
  if(search_user_button) search_user_button.classList.add("member_search_button_active");

  Forum.unselectAll(form.elements['found_users[]']);
  Forum.unselectAll(form.elements['moderators[]']);

  if(!search_user_ajax)
  {
    search_user_ajax = new Forum.AJAX();

    search_user_ajax.timeout = TIMEOUT;

    search_user_ajax.beforestart = function() { break_check_new_messages(); };
    search_user_ajax.aftercomplete = function(error) { activate_check_new_messages(); };

    search_user_ajax.onload = function(text, xml)
    {
      try
      {
        var response = JSON.parse(text);

        Forum.handle_response_messages(response);

        if(!response.success) 
        {
          if(search_user_button) search_user_button.classList.remove("member_search_button_active");
          return;
        }

        // remove old entries

        var found_users = form.elements['found_users[]'];

        for(var i = found_users.length - 1; i >= 0 ; i--)
        {
          found_users.options[i] = null;
        }

        var found = false;
        if(response.found_entries && !Forum.isEmptyObject(response.found_entries))
        {
          for(var u in response.found_entries)
          {
            found = true;
            var option = new Option(response.found_entries[u],
                                    u,
                                    false, true
                                   );
            found_users.options[found_users.options.length] = option;
          }
        }

        if(!found)
        {
          var option = new Option("<?php echo_js(text("UserNotFound")); ?>",
                                  '#',
                                  false, false
                                 );
          found_users.options[found_users.options.length] = option;
        }
        
        Forum.fireEvent(found_users, 'change');
      }
      catch(err)
      {
        Forum.handle_ajax_error(this, err.message, this.last_url, {});
      }
      
      if(search_user_button) search_user_button.classList.remove("member_search_button_active");
    };

    search_user_ajax.onerror = function(error, url, info)
    {
      if(search_user_button) search_user_button.classList.remove("member_search_button_active");

      Forum.handle_ajax_error(this, error, url, info);
    };
  }
  
  search_user_ajax.abort();
  search_user_ajax.resetParams();

  search_user_ajax.setPOST('search_users', "1");
  search_user_ajax.setPOST('hash', get_protection_hash());
  search_user_ajax.setPOST('user_logged', user_logged);
  search_user_ajax.setPOST('lookup_string', form.elements['user_to_search'].value);

  search_user_ajax.request("ajax/process.php");

  return false;
} // search_user

var save_data_ajax = null;

function save_data()
{
  var form = document.getElementById('main_form');
  if(!form) return;

  Forum.unselectAll(form.elements['found_users[]']);
  Forum.unselectAll(form.elements['moderators[]']);

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
        
        Forum.show_sys_progress_indicator(false);
      }
      catch(err)
      {
        Forum.show_sys_progress_indicator(false);
        
        Forum.handle_ajax_error(this, err.message, this.last_url, {});
      }
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
  formData.append('save_forum', "1");

  var moderators = form.elements['moderators[]'];
  for(var i = 0; i < moderators.options.length; i++)
  {
    formData.append('moderators[]', moderators.options[i].value);
  }

  save_data_ajax.setFormData(formData);

  save_data_ajax.request("ajax/process.php");

  return false;
} // save_data

function enable_disable()
{
  var registered_access = document.getElementById("registered_access");
  var restricted_access = document.getElementById("restricted_access");
  var protected_by_password = document.getElementById("protected_by_password");
  var restricted_guest_mode = document.getElementById("restricted_guest_mode");

  var no_guests = document.getElementById("no_guests");
  
  if(!registered_access || !restricted_access || !protected_by_password || !no_guests || !restricted_guest_mode) return;
  
  if(registered_access.checked || restricted_access.checked)
  {
    no_guests.checked = true;
    no_guests.setAttribute("data-is-checked", 1);
    no_guests.disabled = true;
    
    restricted_guest_mode.checked = false;
    restricted_guest_mode.setAttribute("data-is-checked", 0);
    restricted_guest_mode.disabled = true;
  }

  if(restricted_access.checked)
  {
    no_guests.checked = true;
    no_guests.setAttribute("data-is-checked", 1);
    no_guests.disabled = true;

    restricted_guest_mode.checked = false;
    restricted_guest_mode.setAttribute("data-is-checked", 0);
    restricted_guest_mode.disabled = true;

    protected_by_password.checked = false;
    protected_by_password.setAttribute("data-is-checked", 0);
    protected_by_password.disabled = true;
  }

  if(no_guests.checked)
  {
    restricted_guest_mode.checked = false;
    restricted_guest_mode.setAttribute("data-is-checked", 0);
    restricted_guest_mode.disabled = true;
    
  }
  else
  {
    restricted_guest_mode.disabled = false;
  }
  
  if(!restricted_access.checked && !registered_access.checked)
  {
    no_guests.disabled = false;
  }
  
  if(!restricted_access.checked)
  {
    protected_by_password.disabled = false;
  }  
  
  Forum.fireEvent(no_guests, 'update_view');
  Forum.fireEvent(restricted_guest_mode, 'update_view');
  Forum.fireEvent(protected_by_password, 'update_view');
  
  show_hide_password();
  show_hide_restrictions();
}

function show_hide_password()
{
  var row11 = document.getElementById("row11");
  var row12 = document.getElementById("row12");
  var row21 = document.getElementById("row21");
  var row22 = document.getElementById("row22");
  var protected_by_password = document.getElementById("protected_by_password");

  if(!row11 || !row21 || !row21 || !row22 || !protected_by_password) return;

  if(protected_by_password.checked)
  {
    row11.style.display = "table-row";
    row12.style.display = "table-row";
    row21.style.display = "table-row";
    row22.style.display = "table-row";
  }
  else
  {
    row11.style.display = "none";
    row12.style.display = "none";
    row21.style.display = "none";
    row22.style.display = "none";
  }
}

function show_hide_restrictions()
{
  var row31 = document.getElementById("row31");
  var row32 = document.getElementById("row32");
  
  var no_guests = document.getElementById("no_guests");
  var registered_access = document.getElementById("registered_access");
  var protected_by_password = document.getElementById("protected_by_password");
  var restricted_access = document.getElementById("restricted_access");

  if(!row31 || !row32 || !no_guests || !registered_access || !protected_by_password || !restricted_access) return;

  if(no_guests.checked && !restricted_access.checked && !protected_by_password.checked)
  {
    row31.style.display = "table-row";
    row32.style.display = "table-row";
  }
  else
  {
    row31.style.display = "none";
    row32.style.display = "none";
  }
}

Forum.addXEvent(window, 'load', function () { enable_disable(); });
</script>

<form action="edit_forum.php" id="main_form" method="post" onsubmit="return save_data();">

<input type="hidden" id="fid" name="fid" value="<?php echo_html(val_or_empty($forum_data["id"])); ?>">
<table class="form_table forum_edit_table">

<tr>
<th><?php echo_html(reqvar_empty("fid") ? text("CreateForum") : text("EditForum")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("Name")); ?>*:</td>
</tr>
<tr>
<td>
<input type="text" id="forum_name" name="forum_name" value="<?php echo_html(val_or_empty($forum_data["forum_name"])); ?>" autocomplete="off"/>
</td>
</tr>

<tr>
<td><?php echo_html(text("Description")); ?>:</td>
</tr>
<tr>
<td>
<textarea id="forum_description" name="forum_description"><?php echo_html(val_or_empty($forum_data["forum_description"])); ?></textarea>
</td>
</tr>

<tr>
<td><?php echo_html(text("Sorting")); ?>:</td>
</tr>
<tr>
<td>
<input type="text" id="sort_order" name="sort_order" class="small_field" value="<?php echo_html(val_or_empty($forum_data["sort_order"])); ?>" autocomplete="off"/>
</td>
</tr>

<tr>
<td style="vertical-align: top"><?php echo_html(text("Access")); ?>:</td>
</tr>
<tr>
<td>
   <table class="checkbox_table">
   <tr>
     <td>
     <input type="checkbox" id="hide_from_robots" name="hide_from_robots" <?php echo_html(checked(val_or_empty($forum_data["hide_from_robots"]))); ?>> 
     </td>
     <td>
     <label for="hide_from_robots"><?php echo_html(text("HideFromSearchRobots")); ?></label>
     </td>
   </tr>
   <tr>
     <td>
     <input type="checkbox" id="allow_edit" name="allow_edit" <?php echo_html(checked(val_or_empty($forum_data["allow_edit"]))); ?>> 
     </td>
     <td>
     <label for="allow_edit"><?php echo_html(text("MessageEditingAllowed")); ?></label>
     </td>
   </tr>
   <tr>
     <td>
     <input type="checkbox" id="no_guests" name="no_guests" <?php echo_html(checked(val_or_empty($forum_data["no_guests"]))); ?> onchange="enable_disable()"> 
     </td>
     <td>
     <label for="no_guests"><?php echo_html(text("NotWritableForGuests")); ?></label>
     </td>
   </tr>
   <tr>
     <td>
     <input type="checkbox" id="restricted_guest_mode" name="restricted_guest_mode" <?php echo_html(checked(val_or_empty($forum_data["restricted_guest_mode"]))); ?>> 
     </td>
     <td>
     <label for="restricted_guest_mode"><?php echo_html(text("RestrictedGuestMode")); ?></label>
     </td>
   </tr>
   <tr>
     <td>
     <input type="checkbox" id="user_posting_as_guest" name="user_posting_as_guest" <?php echo_html(checked(val_or_empty($forum_data["user_posting_as_guest"]))); ?>> 
     </td>
     <td>
     <label for="user_posting_as_guest"><?php echo_html(text("UserPostingAsGuest")); ?></label>
     </td>
   </tr>
   <tr>
     <td>
     <input type="checkbox" id="registered_access" name="registered_access" <?php echo_html(checked(val_or_empty($forum_data["registered_access"]))); ?> onchange="Forum.invert_pair_checkbox(this, 'restricted_access'); enable_disable();"> 
     </td>
     <td>
     <label for="registered_access"><?php echo_html(text("RegisteredRestricted")); ?></label>
     </td>
   </tr>
   <tr>
     <td>
     <input type="checkbox" id="stringent_rules" name="stringent_rules" <?php echo_html(checked(val_or_empty($forum_data["stringent_rules"]))); ?>> 
     </td>
     <td>
     <label for="stringent_rules"><?php echo_html(text("StringentRules")); ?></label>
     </td>
   </tr>
   <tr>
     <td>
     <input type="checkbox" id="restricted_access" name="restricted_access" <?php echo_html(checked(val_or_empty($forum_data["restricted_access"]))); ?> onchange="Forum.invert_pair_checkbox(this, 'registered_access'); enable_disable();"> 
     </td>
     <td>
     <label for="restricted_access"><?php echo_html(text("AccessRestricted")); ?></label>
     </td>
   </tr>
   <tr>
     <td>
     <input type="checkbox" id="protected_by_password" name="protected_by_password" <?php echo_html(checked(val_or_empty($forum_data["protected_by_password"]))); ?> onchange="enable_disable()"> 
     </td>
     <td>
     <label for="protected_by_password"><?php echo_html(text("PasswordProtected")); ?></label>
     </td>
   </tr>
   </table>
</td>
</tr>

<tr id="row11" style="display:none">
<td><?php echo_html(text("Password")); ?>:</td>
</tr>
<tr id="row12" style="display:none">
<td>
<input type="password" id="password" name="password">
</td>
</tr>

<tr id="row21" style="display:none">
<td><?php echo_html(text("PasswordConfirmation")); ?>:</td>
</tr>
<tr id="row22" style="display:none">
<td>
<input type="password" id="password2" name="password2">
</td>
</tr>

<tr id="row31" style="display:none">
<td><?php echo_html(text("Restrictions")); ?>:</td>
</tr>
<tr id="row32" style="display:none">
<td>
<div class="inner_label"><?php echo_html(text("MinDurationComment")); ?>:</div>
<input type="text" id="access_duration" name="access_duration" class="small_field" style="text-align: right" value="<?php echo_html(val_or_empty($forum_data["access_duration"])); ?>"/>
<div class="inner_label"><?php echo_html(text("MinMessageCountComment")); ?>:</div>
<input type="text" id="access_message_count" name="access_message_count" class="small_field" style="text-align: right" value="<?php echo_html(val_or_empty($forum_data["access_message_count"])); ?>"/>
</td>
</tr>

<tr>
<td></td>
</tr>

<tr>
<th><?php echo_html(text("Moderators")); ?></th>
</tr>

<tr>
<td class="search_moderator_area">

   <table class="aux_table search_member_area_table" style="width:100%">
   <tr>
   <td ><input type="text" id="user_to_search" name="user_to_search" autocomplete="off" value="" placeholder="<?php echo_html(text("SearchUser")); ?>" onkeypress="return search_on_enter(event)"/></td>
   <td style="text-align: right; width: 1%;">
   <input type="button" id="search_user_button" class="standard_button member_search_button" value="<?php echo_html(text("Search")); ?>" onclick="search_user()"/>
   </td>
   </tr>
   </table>

   <table class="list_group" style="width:100%">
   <tr>
   <th><?php echo_html(text("FoundUsers")); ?></th>
   <th></th>
   <th><?php echo_html(text("Moderators")); ?></th>
   </tr>
   <tr>
   <td>
   <select multiple class="multiple_choice" id="found_users" name="found_users[]" onDblClick="Forum.moveSelectedItems(this.form.elements['found_users[]'], this.form.elements['moderators[]'])">
   </select>
   </td>
   <td>
   <input type="button" class="standard_button" value="&gt;&gt;" onclick="Forum.moveSelectedItems(this.form.elements['found_users[]'], this.form.elements['moderators[]']); this.form.elements['user_to_search'].value = '';">
   <input type="button" class="standard_button" value="&lt;&lt;" onclick="Forum.moveSelectedItems(this.form.elements['moderators[]'], this.form.elements['found_users[]'])">
   </td>
   <td>
   <select multiple class="multiple_choice" id="moderators" name="moderators[]" onDblClick="Forum.moveSelectedItems(this.form.elements['moderators[]'], this.form.elements['found_users[]'])">
   <?php foreach($moderator_list as $mid => $name): ?>
   <option value="<?php echo_html($mid); ?>"><?php echo_html($name); ?></option>
   <?php endforeach; ?>
   </select>
   </td>
   </tr>
   </table>

</td>
</tr>

<tr>
<td class="button_area">
<div class="left_buttons">
<input type="button" class="standard_button" value="<?php echo_html(text("Reset")); ?>" onclick="confirm_reset()"/>
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

</form>
