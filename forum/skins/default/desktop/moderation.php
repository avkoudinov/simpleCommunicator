<script>
var config = {
  format: "<?php echo_js(text("DateFormat")); ?>",
  start_year: 2000,
  month_names: [
    "<?php echo_js(text("January")); ?>",
    "<?php echo_js(text("February")); ?>",
    "<?php echo_js(text("March")); ?>",
    "<?php echo_js(text("April")); ?>",
    "<?php echo_js(text("May")); ?>",
    "<?php echo_js(text("June")); ?>",
    "<?php echo_js(text("July")); ?>",
    "<?php echo_js(text("August")); ?>",
    "<?php echo_js(text("September")); ?>",
    "<?php echo_js(text("October")); ?>",
    "<?php echo_js(text("November")); ?>",
    "<?php echo_js(text("December")); ?>"
  ],
  
  weekday_names: [
    "<?php echo_js(text("MondayShort")); ?>",
    "<?php echo_js(text("TuesdayShort")); ?>",
    "<?php echo_js(text("WednesdayShort")); ?>",
    "<?php echo_js(text("ThursdayShort")); ?>",
    "<?php echo_js(text("FridayShort")); ?>",
    "<?php echo_js(text("SaturdayShort")); ?>",
    "<?php echo_js(text("SundayShort")); ?>"
  ]
};

Forum.addXEvent(window, 'load', function () {
  SimpleCalendar.assign("#start_date", config);
});

var search_users_ajax = null;

function search_users()
{
  var form = document.getElementById('main_form');
  if(!form) return false;

  var selected_users = form.elements['selected_users[]'];

  for(var i = selected_users.length - 1; i >= 0 ; i--)
  {
    selected_users.options[i] = null;
  }
  
  if(form.elements['start_date'].value == '') 
  {
    form.elements['start_date'].focus();
    return false;
  }

  var search_user_button = document.getElementById('search_user_button');
  if(search_user_button) search_user_button.classList.add("member_search_button_active");

  Forum.unselectAll(form.elements['selected_users[]']);

  if(!search_users_ajax)
  {
    search_users_ajax = new Forum.AJAX();

    search_users_ajax.timeout = TIMEOUT;

    search_users_ajax.beforestart = function() { break_check_new_messages(); };
    search_users_ajax.aftercomplete = function(error) { activate_check_new_messages(); };

    search_users_ajax.onload = function(text, xml)
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

        var selected_users = form.elements['selected_users[]'];

        for(var i = selected_users.length - 1; i >= 0 ; i--)
        {
          selected_users.options[i] = null;
        }

        if(response.found_users && !Forum.isEmptyObject(response.found_users))
        {
          for(var t in response.found_users)
          {
            var option = new Option(response.found_users[t]["uname_short"],
                                    t,
                                    true, false
                                   );
            option.title = response.found_users[t]["uname"];
            
            if (response.found_users[t]["is_user"]) {
                option.classList.add("user_option");
            }
            
            selected_users.options[selected_users.options.length] = option;
          }
        }
        
        Forum.fireEvent(selected_users, 'change');
      }
      catch(err)
      {
        Forum.handle_ajax_error(this, err.message, this.last_url, {});
      }

      if(search_user_button) search_user_button.classList.remove("member_search_button_active");
    };

    search_users_ajax.onerror = function(error, url, info)
    {
      if(search_user_button) search_user_button.classList.remove("member_search_button_active");

      Forum.handle_ajax_error(this, error, url, info);
    };
  }
  
  search_users_ajax.abort();
  search_users_ajax.resetParams();

  search_users_ajax.setPOST('search_moderated_users', "1");
  search_users_ajax.setPOST('hash', get_protection_hash());
  search_users_ajax.setPOST('user_logged', user_logged);
  search_users_ajax.setPOST('trace_sql', trace_sql);
  search_users_ajax.setPOST('forum', form.elements['forum'].value);
  search_users_ajax.setPOST('start_date', form.elements['start_date'].value);
  search_users_ajax.setPOST('hour', form.elements['hour'].value);
  search_users_ajax.setPOST('minute', form.elements['minute'].value);

  search_users_ajax.request("ajax/process.php");

  return false;
} // search_users

function confirm_delete()
{
  var form = document.getElementById('main_form');
  if(!form) return false;

  var mbuttons = [
    {
      caption: msg_Yes,
      handler: function() {
        Forum.hide_user_msgbox();

        do_action();
      }
    },
    {
      caption: msg_No,
      handler: function() { Forum.hide_user_msgbox(); }
    }
  ];

  Forum.show_user_msgbox(msg_Confirmation, "<?php echo_js(text("MsgBulkDeleteConfirm")); ?>", 'icon-warning.gif', mbuttons, false);

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

        var selected_users = form.elements['selected_users[]'];

        for(var i = selected_users.length - 1; i >= 0 ; i--)
        {
          selected_users.options[i] = null;
        }
        
        form.reset();

        Forum.fireEvent(selected_users, 'change');
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

function delete_selected()
{
  var form = document.getElementById('main_form');
  if(!form) return false;

  var selected_users = form.elements['selected_users[]'];
  
  for(i = selected_users.options.length - 1; i >= 0 ; i--)
  {
    if(selected_users.options[i].selected)
    {
      selected_users.options[i] = null;
    }
  }
  
  Forum.fireEvent(selected_users, 'change');
}

var action_ajax = null;

function do_action()
{
  var form = document.getElementById('main_form');
  if(!form) return false;
  
  Forum.show_sys_progress_indicator(true);
  
  if(!action_ajax)
  {
    action_ajax = new Forum.AJAX();

    // may run long
    action_ajax.timeout = 5*TIMEOUT;

    action_ajax.beforestart = function() { break_check_new_messages(); };
    action_ajax.aftercomplete = function(error) { activate_check_new_messages(); };

    action_ajax.onload = function(text, xml)
    {
      try
      {
        var response = JSON.parse(text);

        if(response.success) 
        {
          delete_selected();
        }
        
        Forum.handle_response_messages(response);
      }
      catch(err)
      {
        Forum.handle_ajax_error(this, err.message, this.last_url, {});
      }
      
      Forum.show_sys_progress_indicator(false);
    };

    action_ajax.onerror = function(error, url, info)
    {
      Forum.show_sys_progress_indicator(false);

      Forum.handle_ajax_error(this, error, url, info);
    };
  }

  action_ajax.abort();
  action_ajax.resetParams();
  
  var formData = new FormData(form);

  formData.append('hash', get_protection_hash());
  formData.append('user_logged', user_logged);  
  formData.append('bulk_delete', "1");

  action_ajax.setFormData(formData);

  action_ajax.request("ajax/process.php");

  return false;
} // do_action

function show_ip_users(link)
{
  var elm = document.getElementById("ip");
  if(!elm || elm.value == "") 
  {
    var mbuttons = [
      {
        caption: msg_OK,
        handler: function() { Forum.hide_user_msgbox(); }
      }
    ];

    Forum.show_user_msgbox(msg_Error, "<?php echo_js(text("ErrIPAddressEmpty")); ?>", 'icon-error.gif', mbuttons, false, function () { elm.focus(); });

    return false;
  }

  link.href = "ip_moderation.php?type=ip_users&ip=" + elm.value;

  return true;
}

function moderate_ip(link)
{
  var elm = document.getElementById("ip");
  if(!elm || elm.value == "") 
  {
    var mbuttons = [
      {
        caption: msg_OK,
        handler: function() { Forum.hide_user_msgbox(); }
      }
    ];

    Forum.show_user_msgbox(msg_Error, "<?php echo_js(text("ErrIPAddressEmpty")); ?>", 'icon-error.gif', mbuttons, false, function () { elm.focus(); });

    return false;
  }

  link.href = "ip_moderation.php?type=moderation&ip=" + elm.value;

  return true;
}

function show_um_users(link)
{
  var elm = document.getElementById("ip");
  if(!elm || elm.value == "") 
  {
    var mbuttons = [
      {
        caption: msg_OK,
        handler: function() { Forum.hide_user_msgbox(); }
      }
    ];

    Forum.show_user_msgbox(msg_Error, "<?php echo_js(text("ErrFingerPrintEmpty")); ?>", 'icon-error.gif', mbuttons, false, function () { elm.focus(); });

    return false;
  }

  link.href = "ip_moderation.php?type=um_users&ip=" + elm.value;

  return true;
}

function moderate_um(link)
{
  var elm = document.getElementById("ip");
  if(!elm || elm.value == "") 
  {
    var mbuttons = [
      {
        caption: msg_OK,
        handler: function() { Forum.hide_user_msgbox(); }
      }
    ];

    Forum.show_user_msgbox(msg_Error, "<?php echo_js(text("ErrFingerPrintEmpty")); ?>", 'icon-error.gif', mbuttons, false, function () { elm.focus(); });

    return false;
  }

  link.href = "ip_moderation.php?type=um_moderation&ip=" + elm.value;

  return true;
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

<?php if($fmanager->is_admin()): ?>
/ <a href="rm_moderation.php"><?php echo_html(text("ReadmarkerModeration")); ?></a>

/ <a href="user_agents.php"><?php echo_html(text("UserAgents")); ?></a>

/ <a href="guest_ips.php"><?php echo_html(text("GuestIPs")); ?></a>

/ <a href="tor_ips.php"><?php echo_html(text("TorIPs")); ?></a>
<?php endif; ?>

/ <span class="topic_title_main"><?php echo_html(text("Moderation")); ?></span>

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

<?php if($fmanager->may_see_ip()): ?>

<form action="ip_moderation.php" id="ip_form" enctype="multipart/form-data" method="get">

<input type="hidden" id="type" name="type" value="moderation">

<table class="form_table profile_table">

<tr>
<th colspan="2"><?php echo_html(text("Moderation")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("IPAddressOrFingerprint")); ?>:</td>
<td><input type="text" id="ip" name="ip" value="<?php echo_html(reqvar("ip")); ?>">
</td>
</tr>

<tr>
<td></td>
<td>
<a href="ip_moderation.php?type=moderation&ip=<?php echo(xrawurlencode(reqvar("ip"))); ?>" onclick="return moderate_ip(this)"><?php echo_html(text("ModerateIP")); ?></a><br>
</td>
</tr>

<tr>
<td></td>
<td>
<a href="ip_moderation.php?type=um_users&ip=<?php echo(xrawurlencode(reqvar("ip"))); ?>"  onclick="return show_ip_users(this)"><?php echo_html(text("ShowMembersOfIP")); ?></a>
</td>
</tr>

<tr>
<td></td>
<td>
<a href="ip_moderation.php?type=um_moderation&ip=<?php echo(xrawurlencode(reqvar("ip"))); ?>" onclick="return moderate_um(this)"><?php echo_html(text("ModerateFingerPrint")); ?></a><br>
</td>
</tr>

<tr>
<td></td>
<td>
<a href="ip_moderation.php?type=ip_users&ip=<?php echo(xrawurlencode(reqvar("ip"))); ?>"  onclick="return show_um_users(this)"><?php echo_html(text("ShowMembersOfFingerPrint")); ?></a>
</td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<tr>
<td colspan="2" class="button_area">

<div class="left_buttons">
<input type="button" class="standard_button" value="<?php echo_html(text("Back")); ?>" onclick="confirm_back()">
</div>
<div class="right_buttons">
<input type="submit" class="standard_button send_button" value="<?php echo_html(text("ModerateIP")); ?>">
</div>
<div class="clear_both">
</div>
</td>
</tr>

</table>

</form>

<?php endif; ?>

<form action="moderation.php" id="main_form" enctype="multipart/form-data" method="get" onsubmit="return confirm_delete();">

<table class="form_table profile_table moderation_table">

<tr>
<th colspan="2"><?php echo_html(text("BulkDeletion")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("Forum")); ?>:</td>
<td>
<select name="forum">
<option value="" selected="selected"><?php echo_html(text("ForAllForums")); ?></option>
<?php foreach($moderated_forum_list as $fid => $fname): ?>
<option value="<?php echo_html($fid); ?>"><?php echo_html($fname); ?></option>
<?php endforeach; ?>
</select>
</td>
</tr>


<tr>
<td><?php echo_html(text("DateTime")); ?>:</td>
<td>

<table class="aux_table">
<tr>
<td><input type="text" class="filter_field" autocomplete="off" id="start_date" name="start_date" value="<?php echo_html($start_date); ?>"></td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td><select name="hour" class="filter_field">
  <?php for($i = 0; $i <= 23; $i++): ?>
  <option value="<?php echo_html($i); ?>" <?php if($i == 0) echo "selected='selected'"; ?>><?php echo_html(sprintf("%02s", $i)); ?></option>
  <?php endfor; ?>
  </select></td>
<td>:</td>
<td><select name="minute" class="filter_field">
  <?php for($i = 0; $i <= 59; $i++): ?>
  <option value="<?php echo_html($i); ?>" <?php if($i == 0) echo "selected='selected'"; ?>><?php echo_html(sprintf("%02s", $i)); ?></option>
  <?php endfor; ?>
  </select>  </td>
<td style="width:100%; text-align:right">
<input type="button" id="search_user_button" class="standard_button member_search_button" value="<?php echo_html(text("Search")); ?>" onclick="search_users()">
</td>
</tr>
</table>
  
</td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<tr>
<th colspan="2" class="subheader"><?php echo_html(text("Members")); ?></th>
</tr>

<tr>
<td colspan="2" class="moderation_user_list">
<select name="selected_users[]" id="selected_users" multiple class="multiple_choice">
</select>
<div class="field_comment"><?php echo_html(text("Member")); ?> [<?php echo_html(text("MessagesCount")); ?> / <?php echo_html(text("Registration")); ?>]</div>
</td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<?php if($fmanager->is_admin()): ?>
<tr>
<td colspan="2">
   <table class="checkbox_table">
   <tr>
     <td>
     <input type="checkbox" value="1" id="delete_physically" name="delete_physically" <?php echo_html(checked(reqvar("delete_physically"))); ?>>      
     </td>
     <td>
     <label for="delete_physically"><?php echo_html(text("PhysicalDeletion")); ?></label>
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
<td colspan="2">
<?php echo_html(text("Comment")); ?>:<br>
<textarea id="comment" name="comment"></textarea>
</td>
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
<input type="submit" class="standard_button send_button" value="<?php echo_html(text("Apply")); ?>">
</div>
<div class="clear_both">
</div>
</td>
</tr>

</table>

</form>

</div>