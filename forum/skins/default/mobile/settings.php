
<script>
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
      }
    },
    {
      caption: msg_No,
      handler: function() { Forum.hide_user_msgbox(); }
    }
  ];

  Forum.show_user_msgbox(msg_Confirmation, "<?php echo_js(text("MsgResetConfirm")); ?>", 'icon-question.gif', mbuttons);

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

  Forum.show_user_msgbox(msg_Confirmation, "<?php echo_js(text("MsgCancelConfirm")); ?>", 'icon-warning.gif', mbuttons);

  return false;
}

function handle_special_mode_checkboxes()
{
  var celebration_active = document.getElementById('celebration_active');
  var mourning_active = document.getElementById('mourning_active');
  var snow_effect = document.getElementById('snow_effect');

  if(!celebration_active || !mourning_active || !snow_effect) return;
  
  if(celebration_active.checked)
  {
    snow_effect.disabled = false;
  }
  else
  {
    snow_effect.checked = false;
    snow_effect.disabled = true;
  }

  Forum.fireEvent(celebration_active, 'update_view');
  Forum.fireEvent(mourning_active, 'update_view');
  Forum.fireEvent(snow_effect, 'update_view');
}

function handle_likes_checkboxes()
{
  var rates_active = document.getElementById('rates_active');
  var dislikes_active = document.getElementById('dislikes_active');
  var dislikes_anonym = document.getElementById('dislikes_anonym');
  
  if(!rates_active || !dislikes_active || !dislikes_anonym) return;
  
  if(rates_active.checked)
  {
    dislikes_active.disabled = false;
  }
  else
  {
    dislikes_active.checked = false;
    dislikes_anonym.checked = false;
    dislikes_active.disabled = true;
    dislikes_anonym.disabled = true;
  }

  if(dislikes_active.checked)
  {
    dislikes_anonym.disabled = false;
  }
  else
  {
    dislikes_anonym.checked = false;
    dislikes_anonym.disabled = true;
  }
  
  Forum.fireEvent(rates_active, 'update_view');
  Forum.fireEvent(dislikes_active, 'update_view');
  Forum.fireEvent(dislikes_anonym, 'update_view');
}

function handle_reg_checkboxes()
{
  var approval_required = document.getElementById('approval_required');
  var delayed_reg_mailing = document.getElementById('delayed_reg_mailing');
  
  if(!approval_required || !delayed_reg_mailing) return;
  
  if(approval_required.checked)
  {
    delayed_reg_mailing.disabled = false;
  }
  else
  {
    delayed_reg_mailing.checked = false;
    delayed_reg_mailing.disabled = true;
  }
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
  formData.append('save_settings', "1");

  save_data_ajax.setFormData(formData);

  save_data_ajax.request("ajax/process.php");

  return false;
} // save_data

Forum.addXEvent(window, 'load', function () { handle_likes_checkboxes(); handle_reg_checkboxes(); handle_special_mode_checkboxes(); });
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

/ <span class="topic_title_main"><?php echo_html(text("Settings")); ?></span>
</div>

<!-- END: forum_bar -->

<form action="settings.php" id="main_form" method="post" onsubmit="return save_data();">

<table class="form_table settings_table">

<tr>
<th><?php echo_html(text("Settings")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("Skin")); ?>*:</td>
</tr>
<tr>
<td>
<select name="skin" id="skin">
<?php foreach($skin_list as $skin => $skin_title): ?>
<option value="<?php echo_html($skin); ?>" <?php echo(selected($settings["skin"], $skin)); ?>><?php echo_html($skin_title); ?></option>
<?php endforeach; ?>
</select>
</td>
</tr>

<tr>
<td><?php echo_html(text("ModeratorLog")); ?>*:</td>
</tr>
<tr>
<td>
<select name="moderator_log" id="moderator_log">
<option value="moderators" <?php echo(selected($settings["moderator_log"], "moderators")); ?>><?php echo_html(text("VisibleForModeratorsOnly")); ?></option>
<option value="admins" <?php echo(selected($settings["moderator_log"], "admins")); ?>><?php echo_html(text("VisibleForAdministratorsOnly")); ?></option>
<option value="all" <?php echo(selected($settings["moderator_log"], "all")); ?>><?php echo_html(text("VisibleForAll")); ?></option>
<option value="all_names_hidden" <?php echo(selected($settings["moderator_log"], "all_names_hidden")); ?>><?php echo_html(text("VisibleForAllNamesHidden")); ?></option>
</select>
</td>
</tr>

<tr>
<td></td>
</tr>

<?php
if($fmanager->demo_mode()) 
{
  $settings["default_sender"] = text("hidden");
  $settings["receiver"] = text("hidden");
}
?>

<tr>
<td style="vertical-align: top"><?php echo_html(text("Notifications")); ?>*:</td>
</tr>
<tr>
<td>
<div class="inner_label"><?php echo_html(text("AddressOfTheDefaultSender")); ?>:</div>
<input type="email" id="default_sender" name="default_sender" value="<?php echo_html($settings["default_sender"]); ?>"><br>
<div class="inner_label"><?php echo_html(text("AddressOfTheContactReceiver")); ?>:</div>
<input type="email" id="receiver" name="receiver" value="<?php echo_html($settings["receiver"]); ?>">
</td>
</tr>

<tr>
<td></td>
</tr>

<tr>
<td><?php echo_html(text("WhoisServer")); ?>:</td>
</tr>
<tr>
<td>
<input type="text" id="whois_server" name="whois_server" value="<?php echo_html($settings["whois_server"]); ?>"><br>

   <table class="checkbox_table">
   <tr>
     <td>
     <input type="checkbox" id="hash_ip_addresses" name="hash_ip_addresses" <?php echo_html(checked($settings["hash_ip_addresses"])); ?>> 
     </td>
     <td>
     <label for="hash_ip_addresses"><?php echo_html(text("HashIPAddresses")); ?></label>
     </td>
   </tr>
   </table>
</td>
</tr>

<tr>
<td></td>
</tr>

<tr>
<td><?php echo_html(text("SpecialMode")); ?>:</td>
</tr>
<tr>
<td>
   <table class="checkbox_table">
   <tr>
     <td>
     <input type="checkbox" id="archive_mode" name="archive_mode" <?php echo_html(checked($settings["archive_mode"])); ?>> 
     </td>
     <td>
     <label for="archive_mode"><?php echo_html(text("ArchiveMode")); ?></label>
     </td>
   </tr>
   <tr>
     <td>
     <input type="checkbox" id="celebration_active" name="celebration_active" <?php echo_html(checked($settings["celebration_active"])); ?> onchange="Forum.invert_pair_checkbox(this, 'mourning_active'); handle_special_mode_checkboxes();"> 
     </td>
     <td>
     <label for="celebration_active"><?php echo_html(text("Celebration")); ?></label>
     </td>
   </tr>
   <tr>
     <td>
     <input type="checkbox" id="snow_effect" name="snow_effect" <?php echo_html(checked($settings["snow_effect"])); ?>> 
     </td>
     <td>
     <label for="snow_effect"><?php echo_html(text("FallingSnow")); ?></label>
     </td>
   </tr>
   <tr>
     <td>
     <input type="checkbox" id="mourning_active" name="mourning_active" <?php echo_html(checked($settings["mourning_active"])); ?> onchange="Forum.invert_pair_checkbox(this, 'celebration_active'); handle_special_mode_checkboxes();"> 
     </td>
     <td>
     <label for="mourning_active"><?php echo_html(text("Mourning")); ?></label>
     </td>
   </tr>
   </table>
</td>
</tr>

<tr>
<td></td>
</tr>

<tr>
<td style="vertical-align: top"><?php echo_html(text("Registration")); ?>:</td>
</tr>
<tr>
<td>
   <table class="checkbox_table">
   <tr>
     <td>
     <input type="checkbox" id="approval_required" name="approval_required" <?php echo_html(checked($settings["approval_required"])); ?> onchange="handle_reg_checkboxes()"> 
     </td>
     <td>
     <label for="approval_required"><?php echo_html(text("AccountConfirmationRequired")); ?></label>
     </td>
   </tr>
   <tr>
     <td>
     <input type="checkbox" id="delayed_reg_mailing" name="delayed_reg_mailing" <?php echo_html(checked($settings["delayed_reg_mailing"])); ?>> 
     </td>
     <td>
     <label for="delayed_reg_mailing"><?php echo_html(text("DelayedRegistrationMailing")); ?></label>
     </td>
   </tr>
   </table>
</td>
</tr>

<tr>
<td></td>
</tr>

<tr>
<td><?php echo_html(text("SearchRobots")); ?>:</td>
</tr>
<tr>
<td>
   <table class="checkbox_table">
   <tr>
     <td>
     <input type="checkbox" id="hide_users_from_robots" name="hide_users_from_robots" <?php echo_html(checked($settings["hide_users_from_robots"])); ?>> 
     </td>
     <td>
     <label for="hide_users_from_robots"><?php echo_html(text("HideUsersFromSearchRobots")); ?></label>
     </td>
   </tr>
   </table>
</td>
</tr>

<tr>
<td></td>
</tr>

<tr>
<td><?php echo_html(text("OnlineStatus")); ?>:</td>
</tr>
<tr>
<td>
   <table class="checkbox_table">
   <tr>
     <td>
     <input type="checkbox" id="hide_online_status" name="hide_online_status" <?php echo_html(checked($settings["hide_online_status"])); ?>> 
     </td>
     <td>
     <label for="hide_online_status"><?php echo_html(text("HideOnlineStatus")); ?></label>
     </td>
   </tr>
   </table>
</td>
</tr>

<tr>
<td></td>
</tr>

<tr>
<td style="vertical-align: top"><?php echo_html(text("Rates")); ?>:</td>
</tr>
<tr>
<td>
   <table class="checkbox_table">
   <tr>
     <td>
     <input type="checkbox" id="rates_active" name="rates_active" <?php echo_html(checked($settings["rates_active"])); ?> onchange="handle_likes_checkboxes()"> 
     </td>
     <td>
     <label for="rates_active"><?php echo_html(text("ActivateRates")); ?></label>
     </td>
   </tr>
   <tr>
     <td>
     <input type="checkbox" id="dislikes_active" name="dislikes_active" <?php echo_html(checked($settings["dislikes_active"])); ?> onchange="handle_likes_checkboxes()"> 
     </td>
     <td>
     <label for="dislikes_active"><?php echo_html(text("ActivateDislikes")); ?></label>
     </td>
   </tr>
   <tr>
     <td>
     <input type="checkbox" id="dislikes_anonym" name="dislikes_anonym" <?php echo_html(checked($settings["dislikes_anonym"])); ?> onchange="handle_likes_checkboxes()"> 
     </td>
     <td>
     <label for="dislikes_anonym"><?php echo_html(text("DislikesAnonymous")); ?></label>
     </td>
   </tr>
   </table>
</td>
</tr>

<tr>
<td></td>
</tr>

<tr>
<td style="vertical-align: top"><?php echo_html(text("Restrictions")); ?>:</td>
</tr>
<tr>
<td>
<div class="inner_label"><?php echo_html(text("MaxAttachmentSize")); ?>:</div>
<input type="text" id="max_att_size" name="max_att_size" class="small_field" style="text-align: right" value="<?php echo_html($settings["max_att_size"]); ?>"> <?php echo_html(text("KB")); ?>
<div class="inner_label"><?php echo_html(text("MaxAttachmentSizeAudioVideo")); ?>:</div>
<input type="text" id="max_att_size_audiovideo" name="max_att_size_audiovideo" class="small_field" style="text-align: right" value="<?php echo_html($settings["max_att_size_audiovideo"]); ?>"> <?php echo_html(text("KB")); ?>
<div class="inner_label"><?php echo_html(text("MaxMessagesPerMinute")); ?>:</div>
<input type="text" id="max_messages_minute" name="max_messages_minute" class="small_field" style="text-align: right" value="<?php echo_html($settings["max_messages_minute"]); ?>">
<div class="inner_label"><?php echo_html(text("MaxMessagesPerHour")); ?>:</div>
<input type="text" id="max_messages_hour" name="max_messages_hour" class="small_field" style="text-align: right" value="<?php echo_html($settings["max_messages_hour"]); ?>">
<div class="inner_label"><?php echo_html(text("MaxMessagesPerDay")); ?>:</div>
<input type="text" id="max_messages_day" name="max_messages_day" class="small_field" style="text-align: right" value="<?php echo_html($settings["max_messages_day"]); ?>">
<div class="inner_label"><?php echo_html(text("MaxTopicsPerDay")); ?>:</div>
<input type="text" id="max_topics_day" name="max_topics_day" class="small_field" style="text-align: right" value="<?php echo_html($settings["max_topics_day"]); ?>">
<div class="inner_label"><?php echo_html(text("MaxPinnedTopics")); ?>:</div>
<input type="text" id="max_pinned_topics" name="max_pinned_topics" class="small_field" style="text-align: right" value="<?php echo_html($settings["max_pinned_topics"]); ?>">
<div class="inner_label"><?php echo_html(text("MaxMembersInPrivateTopic")); ?>:</div>
<input type="text" id="max_private_members" name="max_private_members" class="small_field" style="text-align: right" value="<?php echo_html($settings["max_private_members"]); ?>">
<div class="inner_label"><?php echo_html(text("MaxRatesPerHour")); ?>:</div>
<input type="text" id="max_rates_hour" name="max_rates_hour" class="small_field" style="text-align: right" value="<?php echo_html($settings["max_rates_hour"]); ?>">
<div class="inner_label"><?php echo_html(text("MaxPollOptions")); ?>:</div>
<input type="text" id="max_poll_options" name="max_poll_options" class="small_field" style="text-align: right" value="<?php echo_html($settings["max_poll_options"]); ?>">
<div class="inner_label"><?php echo_html(text("MinSearchInterval")); ?>:</div>
<input type="text" id="min_search_interval" name="min_search_interval" class="small_field" style="text-align: right" value="<?php echo_html($settings["min_search_interval"]); ?>">
<div class="inner_label"><?php echo_html(text("MaxSymbolsUserName")); ?>:</div>
<input type="text" id="max_user_name_symbols" name="max_user_name_symbols" class="small_field" style="text-align: right" value="<?php echo_html($settings["max_user_name_symbols"]); ?>">
<div class="inner_label"><?php echo_html(text("MaxSymbolsTopicName")); ?>:</div>
<input type="text" id="max_topic_name_symbols" name="max_topic_name_symbols" class="small_field" style="text-align: right" value="<?php echo_html($settings["max_topic_name_symbols"]); ?>">
<div class="inner_label"><?php echo_html(text("MaxMessageLengh")); ?>:</div>
<input type="text" id="max_message_length" name="max_message_length" class="small_field" style="text-align: right" value="<?php echo_html($settings["max_message_length"]); ?>"> <?php echo_html(text("KB")); ?>
</td>
</tr>

<tr>
<td style="vertical-align: top"><?php echo_html(text("ProtectedGuestNames")); ?>:</td>
</tr>
<tr>
<td>
<textarea id="protected_guests" name="protected_guests" spellcheck="false"><?php echo_html($settings["protected_guests"]); ?></textarea>
<div class="field_comment"><?php echo_html(text("ProtectedGuestsComment")); ?></div>
</td>
</tr>

<tr>
<td style="vertical-align: top"><?php echo_html(text("BlockedEmailDomains")); ?>:</td>
</tr>
<tr>
<td>
<textarea id="blocked_email_domains" name="blocked_email_domains" spellcheck="false"><?php echo_html($settings["blocked_email_domains"]); ?></textarea>
<div class="field_comment"><?php echo_html(text("DomainListComment")); ?></div>
</td>
</tr>

<tr>
<td style="vertical-align: top"><?php echo_html(text("BlockedIPAddresses")); ?>:</td>
</tr>
<tr>
<td>
<textarea id="blocked_ip_addresses" name="blocked_ip_addresses" spellcheck="false"><?php echo_html($settings["blocked_ip_addresses"]); ?></textarea>
<div class="field_comment"><?php echo_html(text("IPListComment")); ?></div>
</td>
</tr>

<tr>
<td style="vertical-align: top"><?php echo_html(text("ImageUrlBlackList")); ?>:</td>
</tr>
<tr>
<td>
<textarea id="img_domain_blacklist" name="img_domain_blacklist" spellcheck="false"><?php echo_html($settings["img_domain_blacklist"]); ?></textarea>
<div class="field_comment"><?php echo_html(text("DomainListComment")); ?></div>
</td>
</tr>

<tr>
<td style="vertical-align: top"><?php echo_html(text("ImageUrlWhiteList")); ?>:</td>
</tr>
<tr>
<td>
<textarea id="img_domain_whitelist" name="img_domain_whitelist" spellcheck="false"><?php echo_html($settings["img_domain_whitelist"]); ?></textarea>
<div class="field_comment"><?php echo_html(text("DomainListComment")); ?></div>
</td>
</tr>

<tr>
<td></td>
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

</form>
