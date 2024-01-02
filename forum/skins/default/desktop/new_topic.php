<script src='<?php echo($view_path); ?>topic.js<?php echo($cache_appendix); ?>'></script>
<script src='skins/<?php echo($skin); ?>/js/attachment_gallery.js<?php echo($cache_appendix); ?>'></script>
<script src='skins/<?php echo($skin); ?>/js/attachment_posting.js<?php echo($cache_appendix); ?>'></script>
<script src='skins/<?php echo($skin); ?>/js/attachment_drag_drop.js<?php echo($cache_appendix); ?>'></script>
<script src='skins/<?php echo($skin); ?>/js/caret.js<?php echo($cache_appendix); ?>'></script>
<script src='skins/<?php echo($skin); ?>/js/field_lookup.js<?php echo($cache_appendix); ?>'></script>

<!--
<script defer src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
-->

<?php
if(empty($_SESSION["current_language"]) || empty($GLOBALS['LANGUAGE_MAPPINGS'][$_SESSION["current_language"]]))
{
  $locale = "en";
}
else
{
  $locale = $GLOBALS['LANGUAGE_MAPPINGS'][$_SESSION["current_language"]];
}
?>

<script>
var in_search = 0;
var topic_id = '';
var final_url = '<?php echo_js($final_url); ?>';
var ensure_anchor_visible = '';

function confirm_post(form)
{
  var poll_elm = document.getElementById('poll');
  if(!poll_elm || !poll_elm.checked) return post_message('post_message');
  
  var mbuttons = [
    {
      caption: msg_Yes,
      handler: function() {
        Forum.hide_user_msgbox();

        post_message('post_message');
      }
    },
    {
      caption: msg_No,
      handler: function() {
        Forum.hide_user_msgbox();
      }
    }
  ];

  Forum.show_user_msgbox(msg_Warning, "<?php echo_js(text("PollConfirmation")); ?>", 'icon-warning.gif', mbuttons, false);

  return false;
}

function confirm_reset(form)
{
  var elm;

  if(!Forum.formDirty(form))
  {
    form.reset();
    reset_attachment_fields_and_slots();
    show_hide_poll(form['poll']);

    elm = document.getElementById('subject');
    if(!elm) return;

    elm.focus();
    elm.setSelectionRange(elm.value.length, elm.value.length);

    return;
  }

  var mbuttons = [
    {
      caption: msg_Yes,
      handler: function() {
        Forum.hide_user_msgbox();

        form.reset();
        reset_attachment_fields_and_slots();
        show_hide_poll(form['poll']);

        elm = document.getElementById('message');
        if(!elm) return;

        elm.focus();
        elm.setSelectionRange(elm.value.length, elm.value.length);
      }
    },
    {
      caption: msg_No,
      handler: function() {
        Forum.hide_user_msgbox();

        elm = document.getElementById('message');
        if(!elm) return;

        elm.focus();
        elm.setSelectionRange(elm.value.length, elm.value.length);
      }
    }
  ];

  Forum.show_user_msgbox(msg_Confirmation, "<?php echo_js(text("MsgConfirmPostCancel")); ?>", 'icon-question.gif', mbuttons, false);

  return false;
}

function show_hide_poll(chkb)
{
  var elm = document.getElementById('poll_area');
  if(!elm) return;

  if(chkb.checked) elm.style.display = "table-row";
  else             elm.style.display = "none";

  elms = document.getElementsByClassName('poll_multiselect_area');
  if(!elms.length) return;
  
  for(var i = 0; i < elms.length; i++)
  {
    if(chkb.checked) elms[i].style.display = "table-row";
    else             elms[i].style.display = "none";
  }
}

function convert_action_link(target, params)
{
  var elm;

  if(target == "add_attachment_to_favourites")
  {
    elm = document.getElementById("favourite_attachment_link_" + params.id);
    if(elm)
    {
      elm.title = "<?php echo_js(text("AddToFavourites")); ?>";
      elm.classList.remove('post_in_favourites');
      elm.classList.add('post_not_in_favourites');
      elm.onclick = function (event) { return do_action({ topic_action: "add_attachment_to_favourites", id: params.id }); }
    }
  }

  if(target == "remove_attachment_from_favourites")
  {
    elm = document.getElementById("favourite_attachment_link_" + params.id);
    if(elm)
    {
      elm.title = "<?php echo_js(text("RemoveFromFavourites")); ?>";
      elm.classList.remove('post_not_in_favourites');
      elm.classList.add('post_in_favourites');
      elm.onclick = function (event) { return do_action({ topic_action: "remove_attachment_from_favourites", id: params.id }); }
    }
  }
}

function show_attachment_gallery()
{
  var errbuttons = [
    {
      caption: msg_OK,
      handler: function() { Forum.hide_user_msgbox(); }
    }
  ];
  
  var buttons = [
    {
      caption: "<?php echo_js(text("Cancel")); ?>",
      handler: function() { Forum.hide_sys_lightbox(); }
    },
    {
      caption: "<?php echo_js(text("Apply")); ?>",
      addClass: "send_button",
      handler: function() { 
        if(paste_gallery_attachment_placeholders()) 
          Forum.hide_sys_lightbox(); 
        else
          Forum.show_user_msgbox(msg_Error, "<?php echo_js(text("ErrNoAttachmentSelected")); ?>", 'icon-error.gif', errbuttons);
      }
    }
  ];

  Forum.show_attachment_gallery("<?php echo_js(text("AttachmentGallery")); ?>", buttons);
  
  var sys_lightbox_body = document.getElementById("sys_lightbox_body");
  if(!sys_lightbox_body) return false;
  
  var attachment_gallery_area = sys_lightbox_body.lastChild;
  if(attachment_gallery_area)
  {
    Forum.addXEvent(attachment_gallery_area, 'scroll', function (ev) {
      var gap_to_end = this.scrollHeight - this.offsetHeight - this.scrollTop;
      if(gap_to_end < 200)
      {
        load_next_gallery_attachments("<?php echo_js(text("AddToFavourites")); ?>", "<?php echo_js(text("RemoveFromFavourites")); ?>", last_loaded_att_post_id, last_loaded_att_id);
      }
    });
  }
  
  load_next_gallery_attachments("<?php echo_js(text("AddToFavourites")); ?>", "<?php echo_js(text("RemoveFromFavourites")); ?>", 0, 0);

  return false;
}

function goto_topic(tid, subject)
{
  if(!tid) return;
  
  var form = document.getElementById('post_form');
  if(!form) return;

  var goto_url = 'topic.php?fid=<?php echo_html($fid); ?>&tid={tid}&do_write=first_message';
  user_esc_handler();
  
  form.elements['tid'].value = tid;
  form.elements['subject'].value = subject;
  store_unposted_message();
  Forum.show_sys_progress_indicator(true);
  delay_redirect(goto_url.replace(/{tid}/, tid));
}

function subject_lookup_handle_enter(eid, ev) 
{
  if(ev.keyCode == 13) 
  {
    var lst = document.getElementById(eid + "_lookup");
    if (!lst || !lst.value) return true;

    subject_lookup_apply_selection(eid);
    return false;
  }
}

function subject_lookup_apply_selection_if_active(eid)
{
  var lst = document.getElementById(eid + "_lookup");

  if(!lst) return;

  if(document.activeElement != lst) return;
  
  goto_topic(lst.value, Forum.selectedText(lst));
}

function subject_lookup_apply_selection(eid)
{
  var lst = document.getElementById(eid + "_lookup");

  if(!lst) return;

  goto_topic(lst.value, Forum.selectedText(lst));
}

function user_esc_handler() {
  elm = document.getElementById("subject_lookup");
  if (elm) {
    elm.parentNode.style.display = "none";

    for (var i = elm.length - 1; i >= 0; i--) {
      elm.options[i] = null;
    }
  }    
}

var post_message_ajax = null;

function post_message(action)
{
  var form = document.getElementById('post_form');
  if(!form) return;

  Forum.show_sys_progress_indicator(true);

  if(!post_message_ajax)
  {
    post_message_ajax = new Forum.AJAX();

    post_message_ajax.timeout = TIMEOUT;

    post_message_ajax.onload = function(text, xml)
    {
      try
      {
        var response = JSON.parse(text);

        Forum.handle_response_messages(response);

        if(response.ERROR_ELEMENT == "user_password")
        {
          if(form.elements["user_password"]) form.elements["user_password"].value = "";
        }

        if(form.elements["captcha_field"] && response.ERROR_ELEMENT == "captcha_field")
        {
          form.elements["captcha_field"].value = "";
          show_hide_captcha(true);
        }

        if(response.success)
        {
          if(response.double_post && response.last_post_topic_url)
          {
            writing_message = false;

            delay_redirect(response.last_post_topic_url);
            return;
          }

          if(response.target_url)
          {
            writing_message = false;

            delay_redirect(response.target_url);
            return;
          }

          if(response.html)
          {
            var buttons = [
              {
                caption: "<?php echo_js(text("Back")); ?>",
                handler: function() { Forum.hide_sys_lightbox(); }
              },
              {
                caption: "<?php echo_js(text("Send")); ?>",
                addClass: "send_button",
                handler: function() {
                  Forum.hide_sys_lightbox();
                  post_message('post_message');
                }
              }
            ];

            Forum.show_post_preview("<?php echo_js(text("Preview")); ?>", response.html, buttons);
          }
        }
        else
        {
        }
      }
      catch(err)
      {
        Forum.handle_ajax_error(this, err.message, this.last_url, {});
      }

      Forum.show_sys_progress_indicator(false);
      
      // We halted the auto save while submission of the post.
      // Post was not submitted, so activate auto save again
      activate_auto_save();
    };

    post_message_ajax.onerror = function(error, url, info)
    {
      Forum.show_sys_progress_indicator(false);

      Forum.handle_ajax_error(this, error, url, info);

      // We halted the auto save while submission of the post.
      // Post was not submitted, so activate auto save again
      activate_auto_save();
    };
  }
  else
  {
  }

  post_message_ajax.abort();
  post_message_ajax.resetParams();

  break_auto_save();
  break_check_new_messages();

  var formData = new FormData(form);

  formData.append('hash', get_protection_hash());
  formData.append('user_logged', user_logged);  
  formData.append('user_marker', user_marker);
  formData.append(action, "1");
  
  for(var i = 1; i <= ATTACHMENTS_PER_POST; i++)
  {
    index = i;
    if(index == 1) index = '';
    
    if(attachment_buffer["del_attachment" + index] == 1)
    {
      formData.append("del_attachment" + index, 1);
    }
    
    // if pasted image exists, replace the file field
    if(attachment_buffer["attachment" + index] && formData.delete)
    {
      formData.delete("attachment" + index);
      formData.append("attachment" + index, attachment_buffer["attachment" + index].file, attachment_buffer["attachment" + index].file_name);
    }
  }

  post_message_ajax.setFormData(formData);

  // if writing a message takes long time,
  // posting of the message over AJAX may loose the session,
  // although the session of the main script still live.
  post_message_ajax.request("ajax/process.php");

  return false;
} // post_message

writing_message = true;

activate_auto_save();
</script>

<div class="content_area">

<!-- BEGIN: forum_bar -->

<div class="forum_bar">

<div class="forum_name_bar"><a href="forums.php"><?php echo_html(text("Forums")); ?></a>

<?php if(!$fmanager->is_logged_in() && !empty($_SESSION["ip_blocked"])): ?>
<span class="closed">[<?php echo_html(empty($_SESSION["ip_block_time_left"]) ? text("ip_blocked") : sprintf(text("ip_blocked_until"), $_SESSION["ip_block_time_left"])); ?>]</span>
<?php elseif($fmanager->is_logged_in() && empty($_SESSION["activated"])): ?>
<span class="closed">[<?php echo_html(text("notActivated")); ?>]</span>
<?php elseif($fmanager->is_logged_in() && empty($_SESSION["approved"])): ?>
<span class="closed">[<?php echo_html(text("notApproved")); ?>]</span>
<?php elseif(!empty($_SESSION["blocked"])): ?>
<span class="closed">[<?php echo_html(empty($_SESSION["block_time_left"]) ? text("blocked") : sprintf(text("blocked_until"), $_SESSION["block_time_left"])); ?>]</span>
<?php endif; ?>

<?php
$display = "style='display:none'";
if(!empty($topics_with_new_count)) $display = "";
?>
<span class="new topics_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span> 

/

<?php
$not_preferred = "";
if(!empty($_SESSION["preferred_forums"]) && empty($_SESSION["preferred_forums"][$fid]) && !$is_private) $not_preferred = "not_preferred";
?>
<a href="forum.php?fid=<?php echo_html($fid_for_url); ?>" class="<?php echo($not_preferred); ?>"><?php echo_html($forum_title); ?></a>

<?php if(!empty($forum_data["blocked"])): ?>
<span class="closed">[<?php echo_html(empty($forum_data["block_time_left"]) ? text("forum_blocked") : sprintf(text("forum_blocked_until"), $forum_data["block_time_left"])); ?>]</span>
<?php elseif(!empty($forum_data["closed"])): ?>
<span class="closed">[<?php echo_html(text("closed")); ?>]</span>
<?php elseif(!empty($forum_data["no_guests"]) && !$fmanager->is_logged_in()): ?>
<span class="closed">[<?php echo_html(text("closed_for_guests")); ?>]</span>
<?php endif; ?>

<?php if(!empty($forum_data["deleted"])): ?>
<span class="closed">[<?php echo_html(text("deleted")); ?>]</span>
<?php endif; ?>

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

<div class="clear_both"></div>

</div>

<!-- END: forum_bar -->

<div class="new_topic_container">

<form action="new_topic.php" id="post_form" method="post" enctype="multipart/form-data" onsubmit="return confirm_post(this)">
<input type="hidden" id="fid" name="fid" value="<?php echo_html($fid); ?>">
<input type="hidden" name="new_topic" value="1">
<input type="hidden" name="tid" value="">
<input type="hidden" name="edit_mode" value="">
<input type="hidden" name="profiled_topic" value="">
<input type="hidden" name="stringent_rules" value="<?php echo(!empty($forum_data["stringent_rules"]) ? 1 : 0); ?>">
<input type="hidden" name="is_thematic" value="">
<input type="hidden" id="login_active" name="login_active" value="">

<table class="form_table post_message_table new_topic_table">

<tr>
<th colspan="2" id="post_message_caption"><?php echo_html($title); ?></th>
</tr>

<tr id="author_row">
<td><?php echo_html(text("Author")); ?>*:</td>
<td>
<?php
$author = $fmanager->get_user_name();

$read_only = '';
if($fmanager->is_logged_in() && !(!empty($forum_data["user_posting_as_guest"]) && !empty($_SESSION["guest_posting_mode"])))
{
  $read_only = ' class="read_only_field" readonly';
}

if($fmanager->is_logged_in() && !empty($forum_data["user_posting_as_guest"]) && !empty($_SESSION["guest_posting_mode"]))
{
  $author = $fmanager->get_last_posted_user_name();
}
?>
<input type="text" id="author" name="author" value="<?php echo_html($fmanager->get_display_name($author)); ?>" <?php echo($read_only); ?> autocomplete="off" onkeypress="return handle_enter(event)">
</td>
</tr>

<?php
if(!$fmanager->is_logged_in()):

if($is_private == 2)
{
  $url = "new_topic.php?fid=private";
}
elseif($is_private == 1)
{
  $url = "new_topic.php?fid=private&receiver=" . xrawurlencode(reqvar("receiver"));
}
else
{
  $url = "new_topic.php?fid=$fid";
}
?>
<tr id="enter_password_row">
<td>&nbsp;</td>
<td><div class="enter_password"><a href="<?php echo($url); ?>" onclick="return show_author_password()"><?php echo_html(text("EnterPassword")); ?></a></div></td>
</tr>

<tr id="login_row" style="display:none">
<td><?php echo_html(text("UserLogin")); ?>*:</td>
<td>
<input type="text" id="user_login" name="user_login" value="" autocomplete="off" onkeypress="return handle_enter(event)"></td>
</tr>

<tr id="password_row" style="display:none">
<td><?php echo_html(text("Password")); ?>*:</td>
<td>
<div style="float:left"><input type="password" id="user_password" name="user_password" value="" autocomplete="off"></div>
<div class="enter_password" style="float:right"><a href="<?php echo($url); ?>" onclick="return cancel_author_password()"><?php echo_html(text("CancelEnterPassword")); ?></a></div>
<div style="clear:both"></div>
</td>
</tr>
<?php endif; ?>

<?php if($is_private == 1): ?>
<tr>
<td><?php echo_html(text("Receiver")); ?>*:</td>
<td>
<input type="text" name="receiver_name" value="<?php echo_html($user_data["user_name"]); ?>" class="read_only_field" readonly autocomplete="off">
<input type="hidden" name="receiver" value="<?php echo_html(reqvar("receiver")); ?>">
</td>
</tr>
<?php endif; ?>

<tr>
<td><?php echo_html(text("Subject")); ?>*:</td>
<td>
<?php if($is_private > 0): ?>
<input type="text" id="subject" name="subject" value="" autocomplete="off" onkeypress="return handle_enter(event)">
<?php else: ?>
<input type="text" id="subject" name="subject" value="" autocomplete="off" onkeypress="return handle_enter(event)" onkeyup="return lookup_existing_topics(this, event, '<?php echo_html($fid); ?>');" onblur="lookup_delayed_hide('subject');">

<div style="position: relative">
<div class="field_lookup_area topic_lookup_area" style="display:none">
  <div class="topics_exists_warning">
  <?php echo_html(text("TopicsExistWarning")); ?>  
  </div>
  <select id="subject_lookup" size="10"
      onclick="if(!mustAdjustMultiSelect()) { subject_lookup_apply_selection('subject') }"
      onchange="if(mustAdjustMultiSelect()) { subject_lookup_apply_selection_if_active('subject') }"

      onkeypress="return subject_lookup_handle_enter('subject', event)" onblur="user_esc_handler()"
  >
  </select>
</div>
</div>
<?php endif; ?>
</td>
</tr>

<tr>
<td style="vertical-align: top"></td>
<td>
 <table class="checkbox_table">
 <tr>
   <td>
   <input type="checkbox" id="is_adult" name="is_adult" tabindex="-1"> 
   </td>
   <td>
   <label for="is_adult"><?php echo_html(text("MarkMessageAdult")); ?></label>
   </td>
 </tr>
 </table>
</td>
</tr>

<?php if(empty($is_private)): ?>

  <?php 
  $display_request = "display:none";
  if($fmanager->is_logged_in() && !$fmanager->is_admin() && !$fmanager->is_forum_moderator($fid))
  {
    $display_request = "";
  }
  ?>
  <tr id="request_moderation_row" style="<?php echo($display_request); ?>">
  <td style="vertical-align: top;"></td>
  <td>
   <table class="checkbox_table">
   <tr>
     <td>
     <input type="checkbox" id="request_moderation" name="request_moderation" tabindex="-1"> 
     </td>
     <td>
     <label for="request_moderation"><?php echo_html(text("RequestModeration")); ?></label>
     </td>
   </tr>
   </table>
  </td>
  </tr>

  <?php 
  $display_no_guests = "display:none";
  if($fmanager->is_logged_in() && empty($forum_data["no_guests"]))
  {
    $display_no_guests = "";
  }
  
  if(empty($forum_data["no_guests"])):
  ?>
  <tr id="no_guests_row" style="<?php echo($display_no_guests); ?>">
  <td style="vertical-align: top;"></td>
  <td>
   <table class="checkbox_table">
   <tr>
     <td>
     <input type="checkbox" id="no_guests" name="no_guests" tabindex="-1"> 
     </td>
     <td>
     <label for="no_guests"><?php echo_html(text("DisallowGuests")); ?></label>
     </td>
   </tr>
   </table>
  </td>
  </tr>
  <?php endif; ?>

  <?php if($fmanager->is_logged_in() && !$fmanager->is_master_admin()): ?>
  <tr>
  <td style="vertical-align: top"></td>
  <td>
   <table class="checkbox_table">
   <tr>
     <td>
     <input type="checkbox" id="publish_delay" name="publish_delay" tabindex="-1"> 
     </td>
     <td>
     <label for="publish_delay"><?php echo_html(text("DelayPublishing")); ?></label>
     </td>
   </tr>
   </table>
  </td>
  </tr>
  <?php endif; ?>

  <tr>
  <td style="vertical-align: top"></td>
  <td>
   <table class="checkbox_table">
   <tr>
     <td>
     <input type="checkbox" id="blog" name="blog" tabindex="-1"> 
     </td>
     <td>
     <label for="blog"><?php echo_html(text("CreateBlog")); ?></label>
     </td>
   </tr>
   </table>
  </td>
  </tr>

<tr>
<td style="vertical-align: top"></td>
<td>

   <table class="checkbox_table">
   <tr>
     <td>
     <input type="checkbox" id="poll" name="poll" onchange="show_hide_poll(this)" tabindex="-1"> 
     </td>
     <td>
     <label for="poll"><?php echo_html(text("CreatePoll")); ?></label>
     </td>
   </tr>
   <tr class="poll_multiselect_area" style="display:none">
     <td>
     <input type="checkbox" id="poll_multiselect" name="poll_multiselect"> 
     </td>
     <td>
     <label for="poll_multiselect"><?php echo_html(text("PollMultiselect")); ?></label>
     </td>
   </tr>
   <tr class="poll_multiselect_area" style="display:none">
     <td>
     <input type="checkbox" id="poll_open" name="poll_open"> 
     </td>
     <td>
     <label for="poll_open"><?php echo_html(text("OpenPoll")); ?></label><br>
     </td>
   </tr>
   <tr class="poll_multiselect_area" style="display:none">
     <td>
     <input type="checkbox" id="poll_results_delayed" name="poll_results_delayed"> 
     </td>
     <td>
     <label for="poll_results_delayed"><?php echo_html(text("PollResultsDelayed")); ?></label>
     </td>
   </tr>
   </table>

</td>
</tr>

<tr id="poll_area" style="display:none">
<td></td>
<td>
<div style="color: maroon"><br><?php echo_html(text("PollComment2")); ?></div>
<br>
<?php echo_html(text("PollOptions")); ?>*:<br>
<textarea id="poll_options" name="poll_options"></textarea>
<div class="field_comment"><?php echo_html(text("PollComment")); ?></div>
<br>
<?php echo_html(text("Comment")); ?>:<br>
<textarea id="poll_comment" name="poll_comment"></textarea>
</td>
</tr>

<?php endif; ?>

<tr>
<td colspan="2"></td>
</tr>

<tr>
<th colspan="2" class="subheader"><?php echo_html(text("Message")); ?></th>
</tr>

<tr id="editor_toolbar">
<td colspan="2" class="toolbar">
<div class="toolbar_button_wrapper"><button class="toolbar_button" type="button" onclick="return insert_tag('[b]','[/b]', 0)" tabindex="-1"><b>B</b></button></div>
<div class="toolbar_button_wrapper"><button class="toolbar_button" type="button" onclick="return insert_tag('[i]','[/i]', 0)" tabindex="-1"><i>I</i></button></div>
<div class="toolbar_button_wrapper"><button class="toolbar_button" type="button" onclick="return insert_tag('[u]','[/u]', 0)" tabindex="-1"><u>U</u></button></div>
<div class="toolbar_button_wrapper"><button class="toolbar_button" type="button" onclick="return insert_tag('[s]','[/s]', 0)" tabindex="-1"><span style="text-decoration: line-through">S</span></button></div>
<div class="toolbar_button_wrapper"><button class="toolbar_button" type="button" onclick="return insert_tag('[hidden]','[/hidden]', 0)" tabindex="-1">***</button></div>
<div class="toolbar_button_wrapper"><button class="toolbar_button" type="button" onclick="return insert_tag('[sup]','[/sup]', 0)" tabindex="-1">X<sup>2</sup></button></div>
<div class="toolbar_button_wrapper" style="margin-right: 9px;"><button class="toolbar_button" type="button" onclick="return insert_tag('[sub]','[/sub]', 0)" tabindex="-1">X<sub>2</sub></button></div>

<div class="toolbar_button_wrapper"><button class="toolbar_button" type="button" onclick="return insert_tag('[size=1]','[/size]', 0)" tabindex="-1">1</button></div>
<div class="toolbar_button_wrapper"><button class="toolbar_button" type="button" onclick="return insert_tag('[size=2]','[/size]', 0)" tabindex="-1">2</button></div>
<div class="toolbar_button_wrapper"><button class="toolbar_button" type="button" onclick="return insert_tag('[size=3]','[/size]', 0)" tabindex="-1">3</button></div>
<div class="toolbar_button_wrapper"><button class="toolbar_button" type="button" onclick="return insert_tag('[size=4]','[/size]', 0)" tabindex="-1">4</button></div>
<div class="toolbar_button_wrapper" style="margin-right: 9px;"><button class="toolbar_button" type="button" onclick="return insert_tag('[size=5]','[/size]', 0)" tabindex="-1">5</button></div>

<div class="toolbar_button_wrapper">
<button class="toolbar_button" type="button" style="background: transparent url('<?php echo($view_path); ?>images/color.png') no-repeat center center" onclick="return toggle_color_selection_area()" tabindex="-1">&nbsp;</button>
  <div id="color_selection_area" class="color_selection_area" style="display:none">

  <script>
  document.write(render_color_picker());
  </script>

  </div>
  <div class="clear_both"></div>
</div>

<div class="toolbar_button_wrapper">
<button class="toolbar_button" type="button" style="background: transparent url('<?php echo($view_path); ?>images/smile.png') no-repeat center center" onclick="return toggle_smile_selection_area()" tabindex="-1">&nbsp;</button>
</div>

<?php if($fmanager->is_logged_in() && !$fmanager->is_master_admin()): ?>
<div class="toolbar_button_wrapper"><button class="toolbar_button" type="button" style="background: transparent url('<?php echo($view_path); ?>images/gallery.png') no-repeat center center" onclick="return show_attachment_gallery()" tabindex="-1">&nbsp;</button></div>
<?php endif; ?>

<div class="toolbar_button_wrapper" style="float:right;margin-right: 0px;"><button class="toolbar_button" type="button" onclick="window.open('help.php#message_formatting'); return false;" tabindex="-1"><?php echo_html(text("Help")); ?></button></div>

<div class="clear_both"></div>

</td>
</tr>

<tr>
<td colspan="2" class="toolbar">
<div class="toolbar_button_wrapper"><button class="toolbar_button" type="button" onclick="return insert_tag('[img]','[/img]', 0)" tabindex="-1">IMG</button></div>
<div class="toolbar_button_wrapper"><button class="toolbar_button" type="button" onclick="return insert_tag('[anim]','[/anim]', 0)" tabindex="-1">ANIM</button></div>
<div class="toolbar_button_wrapper"><button class="toolbar_button" type="button" onclick="return insert_tag('[url=]','[/url]', 0)" tabindex="-1">URL</button></div>

<div class="toolbar_button_wrapper"><button class="toolbar_button" type="button" onclick="return insert_tag('[quote=]','[/quote]', 0)" tabindex="-1">QUOTE</button></div>
<div class="toolbar_button_wrapper"><button class="toolbar_button" type="button" onclick="return insert_tag('[spoiler]','[/spoiler]', 0)" tabindex="-1">SPOILER</button></div>

<div class="toolbar_button_wrapper">
<button class="toolbar_button" type="button" onclick="return toggle_code_selection_area()" tabindex="-1">CODE</button>
  <div id="code_selection_area" class="code_selection_area" style="display:none">

    <?php
    echo $fmanager->build_codes_table();
    ?>

  </div>
  <div class="clear_both"></div>
</div>

<div class="toolbar_button_wrapper"><button class="toolbar_button" type="button" onclick="return insert_tag('[fixed]','[/fixed]', 0)" tabindex="-1">FIX</button></div>
<div class="toolbar_button_wrapper"><button class="toolbar_button" type="button" onclick="return insert_tag('[poem]','[/poem]', 0)" tabindex="-1">POEM</button></div>
<div class="toolbar_button_wrapper"><button class="toolbar_button" type="button" onclick="return insert_tag('[hr]','', 0)" tabindex="-1">HR</button></div>
<div class="toolbar_button_wrapper"><button class="toolbar_button" type="button" style="background: transparent url('<?php echo($view_path); ?>images/list.png') no-repeat center center" onclick="return insert_tag('[list]','[/list]', 0)" tabindex="-1">&nbsp;</button></div>
<div class="toolbar_button_wrapper"><button class="toolbar_button" type="button" style="background: transparent url('<?php echo($view_path); ?>images/nlist.png') no-repeat center center" onclick="return insert_tag('[nlist]','[/nlist]', 0)" tabindex="-1">&nbsp;</button></div>
<div class="toolbar_button_wrapper"><button class="toolbar_button" type="button" onclick="return insert_tag('[table]','[/table]', 0)" tabindex="-1">TABLE</button></div>

<div class="toolbar_button_wrapper">
<button class="toolbar_button" type="button" onclick="return toggle_media_selection_area()" tabindex="-1">MEDIA</button>
  <div id="media_selection_area" class="media_selection_area" style="display:none">

    <div onclick="insert_tag('[youtube]','[/youtube]', 0)">YOUTUBE</div>
    <div onclick="insert_tag('[rutube]','[/rutube]', 0)">RUTUBE</div>
    <div onclick="insert_tag('[vimeo]','[/vimeo]', 0)">VIMEO</div>
    <div onclick="insert_tag('[coub]','[/coub]', 0)">COUB</div>
    <div onclick="insert_tag('[vkvideo]','[/vkvideo]', 0)">VK</div>
    <div onclick="insert_tag('[twitter]','[/twitter]', 0)">TWITTER</div>
    <div onclick="insert_tag('[telegram]','[/telegram]', 0)">TELEGRAM</div>
    <div onclick="insert_tag('[instagram]','[/instagram]', 0)">INSTAGRAM</div>
    <div onclick="insert_tag('[reddit]','[/reddit]', 0)">REDDIT</div>
    <div onclick="insert_tag('[dzen]','[/dzen]', 0)">YANDEX DZEN</div>
    <div onclick="insert_tag('[rambler]','[/rambler]', 0)">RAMBLER</div>
    <div onclick="insert_tag('[tiktok]','[/tiktok]', 0)">TIKTOK</div>
    <div onclick="insert_tag('[anim]','[/anim]', 0)">ANIM</div>
    <div onclick="insert_tag('[gallery]','[/gallery]', 0)">GALLERY</div>
    <div onclick="insert_tag('[video]','[/video]', 0)">VIDEO</div>
    <div onclick="insert_tag('[audio]','[/audio]', 0)">AUDIO</div>
    <?php if(defined("SUPPORT_LATEX") && SUPPORT_LATEX): ?>
    <div onclick="insert_tag('[latex]','[/latex]', 0)">LATEX</div>
    <?php endif; ?>
    <div onclick="insert_tag('[gmap]','[/gmap]', 0)">GOOGLE MAPS</div>

  </div>
</div>
<div class="toolbar_button_wrapper"><button class="toolbar_button" type="button" style="background: transparent url('<?php echo($view_path); ?>images/paste.png') no-repeat center center" onclick="return paste_text()" tabindex="-1">&nbsp;</button></div>

<div class="clear_both"></div>
</td>
</tr>

<tr id="smile_selection_area" style="display:none">
<td colspan="2" class="smile_toolbar">

    <?php
    echo($fmanager->build_smile_table());
    ?>

</td>
</tr>

<tr>
<td colspan="2">

<div style="position: relative">
<div class="appeal_author_selection_area" style="position: absolute;">
  <div style="position: absolute;right:2px;top:2px;cursor:pointer" onclick="hide_appeal_authors_lookup()"><img src="<?php echo($view_path); ?>images/cross.png" alt="<?php echo_html(text("Close")); ?>"></div>
&nbsp;&nbsp;<?php echo_html(text("Author")); ?>:
  <div class="select_container">
    <select id="author_lookup" size="10"
    onclick="if(!mustAdjustMultiSelect()) { insert_appeal_author(false) }"
    onchange="if(mustAdjustMultiSelect()) { insert_appeal_author(true) }"

    onkeypress="return handle_appeal_author_enter(event)"
    >
    </select>
  </div>
</div>
</div>

<?php if(!empty($forum_data["stringent_rules"])): ?>
<div class='guest_warning'><?php echo(text("StringentRulesWarning")); ?></div>
<?php endif; ?>

<?php if(empty($may_write_to_forum)): ?>
<div class='guest_warning'><?php echo_html(text("WarnWritingNotPossible")); ?></div>
<?php elseif(!$fmanager->is_logged_in() && (!empty($forum_data["no_guests"]) || !empty($topic_data["no_guests"]))): ?>
<div class='guest_warning'><?php echo_html(text("WarnAuthorizationRequired")); ?></div>
<?php endif; ?>

<textarea id="message" name="message" onkeypress="return handle_enter(event)" onkeydown="return check_personal_appeal(event)"  onkeyup="return check_personal_appeal2(event)"></textarea>

<?php
$visiblty = "hidden";
if($fmanager->has_auto_saved_message('')) $visiblty = "visible";
?>
<div id="load_last_version" style="visibility:<?php echo($visiblty); ?>">
<a href="new_topic.php?fid=<?php echo_html($fid_for_url); ?>" onclick='return confirm_load("<?php echo_js(text("MsgConfirmPostOverwrite"), true); ?>", { load_auto_saved: 1, topic: 0, forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("LoadLastSavedVersion")); ?></a>
</div>
</td>
</tr>

<tr>
<td style="vertical-align: top"><?php echo_html(text("Attachment")); ?>:</td>
<td style="padding-right: 100px;height:95px;vertical-align:top;">

<div style="position: relative">
<div id="drag_drop_zone" contenteditable="true" title="<?php echo_html(text("MsgPasteOrDropImage")); ?>"></div>
</div>

<div class="paste_attachment_wrapper">
<table class="aux_table">
<tr>
<td>
<input type="file" multiple="" data-placeholder="<?php echo_html(text("AddAttachment")); ?>" id="attachment" name="attachment"> 
</td>
<td>
<div class="del_attachment_button" id="del_attachment_button" title="<?php echo_html(text("Delete")); ?>" onclick="delete_attachment_file('');"></div>
</td>
<td>
&nbsp;
<span id="paste_attachment" style="display:none"><a href="forum.php?fid=<?php echo_html($fid_for_url); ?>" onclick="return paste_attachment_placeholder('', '')"><?php echo_html(text("PasteIntoMessage")); ?></a> /
<a href="forum.php?fid=<?php echo_html($fid_for_url); ?>" onclick="return paste_attachment_placeholder('', 'spoiler')"><?php echo_html(text("Spoiler")); ?></a></span>
<span id="paste_attachment_gif" style="display:none">/ <a href="<?php echo($fid_for_url); ?>" onclick="return paste_attachment_placeholder('', 'anim')"><?php echo_html(text("Animation")); ?></a></span>
</td>
</tr>
</table>
</div>

<div id="additional_attachments_area">
<?php for($i = 2; $i <= $fmanager->get_attachments_per_post(); $i++): ?>
  <div class="paste_attachment_wrapper">
  <table class="aux_table">
  <tr>
  <td>
  <input type="file" multiple="" data-placeholder="<?php echo_html(text("AddAttachment")); ?>" id="attachment<?php echo $i; ?>" name="attachment<?php echo $i; ?>"> 
  </td>
  <td>
  <div class="del_attachment_button" id="del_attachment_button<?php echo $i; ?>" title="<?php echo_html(text("Delete")); ?>" onclick="delete_attachment_file('<?php echo $i; ?>');"></div>
  </td>
  <td>
  &nbsp;
  <span id="paste_attachment<?php echo $i; ?>" style="display:none"><a href="forum.php?fid=<?php echo_html($fid_for_url); ?>" onclick="return paste_attachment_placeholder('<?php echo $i; ?>', '')"><?php echo_html(text("PasteIntoMessage")); ?></a> /
  <a href="forum.php?fid=<?php echo_html($fid_for_url); ?>" onclick="return paste_attachment_placeholder('<?php echo $i; ?>', 'spoiler')"><?php echo_html(text("Spoiler")); ?></a></span>
  <span id="paste_attachment_gif<?php echo $i; ?>" style="display:none">/ <a href="<?php echo($fid_for_url); ?>" onclick="return paste_attachment_placeholder('<?php echo $i; ?>', 'anim')"><?php echo_html(text("Animation")); ?></a></span>
  </td>
  </tr>
  </table>
  </div>
<?php endfor; ?>
<span id="paste_attachment_gallery"><a href="forum.php?fid=<?php echo_html($fid_for_url); ?>" onclick="return paste_attachment_placeholder('', 'gallery')"><?php echo_html(text("PasteAsGallery")); ?></a>
<br><br>
</div>

<div class="field_comment"><?php echo_html(sprintf(text("MaxAttachmentSizeComment"), $max_att_size, $max_att_size_audiovideo)); ?></div>

<?php
$captcha_display = "display:none";
if(!$fmanager->is_logged_in() && !$fmanager->captcha_verified())
  $captcha_display = "display:table-row";
?>

<table class="aux_table">
<tr class="captcha_area" style="<?php echo($captcha_display); ?>">
   <td>
   <br>
   <div class="captcha_comment"><?php echo_html(text("MsgSpamProtect")); ?></div>

   <table class="captcha_table">
   <tr>
     <td>
   <img class='captcha_picture' src='captcha/captcha.php?rnd=<?php echo(rand(1000, 9999)); ?>&session_var=captcha' id='captcha_picture' alt='Captcha' onclick='Forum.reload_captcha("captcha_picture", "captcha", "captcha_field")'>
     </td>
     <td>
     </td>
     <td>
   <input type="text" id="captcha_field" name="captcha_field" class="captcha_field" value="" autocomplete="off" onkeypress="return handle_enter(event)">
     </td>
   </tr>
   </table>
   </td>
</tr>
</table>

<div style="padding-top:10px">
<?php echo(text("PostRulesAgreement")); ?>
</div>

</td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<tr>
<td colspan="2" class="button_area">
<div class="left_buttons">
<input type="button" class="standard_button" value="<?php echo_html(text("Cancel")); ?>" onclick="window.history.back()">
<input type="button" class="standard_button" value="<?php echo_html(text("Reset")); ?>" onclick="confirm_reset(this.form)">
</div>
<div class="right_buttons">
<input type="button" class="standard_button" value="<?php echo_html(text("Preview")); ?>" onclick="post_message('preview_message')">

<input type="submit" class="standard_button send_button" value="<?php echo_html(text("Send")); ?>">
</div>
<div class="clear_both">
</div>
</td>
</tr>

</table>

</form>

</div>

</div>