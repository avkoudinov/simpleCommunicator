<!-- BEGIN: object for comment dialog -->

<div id="post_comment_area" class="post_comment_area">

<form action="topic.php" id="post_comment_form" method="post">

   <table>
   <tr>
   <td style="width: 1%; white-space: nowrap">
   <?php echo_html(text("Author")); ?>:
   </td>
   <td id="comment_author" style="padding-left:0px"></td>
   </tr>
   </table>

   <table>
   <tr>
   <td style="padding-top:0px">
   <textarea autocomplete="off" id="post_comment" name="post_comment" onkeypress="return handle_post_comment_enter(event)"></textarea>
   </td>
   </tr>
   </table>

</form>

</div>

<!-- END: object for comment dialog -->

<!-- BEGIN: object for citation dialog -->

<div id="citation_dialog" class="moderator_post_more_actions citation_dialog">

    <div style="position: absolute;right:2px;top:2px;cursor:pointer" onclick="hide_all_popups(); clear_selection();"><img src="skins/default/desktop/images/cross.png" alt="Закрыть"></div>

    <a href="<?php echo($base_url); ?>" id="citation_action" onclick="clear_selection(); return citate_text(this.getAttribute('data-parent-pid'), this.getAttribute('data-pid'), this.getAttribute('data-author'), this.getAttribute('data-tid'), this.getAttribute('data-subject'), this.getAttribute('data-profiled_topic'), this.getAttribute('data-stringent_rules'), this.getAttribute('data-text'));"><?php echo_html(text("Citate")); ?></a>

</div>

<!-- END: object for citation dialog -->

<!-- BEGIN: object for posting dialog -->

<div id="form_container" style="display:none">

<form action="topic.php" id="post_form" method="post" enctype="multipart/form-data" onsubmit="return post_message('post_message');">

<input type="hidden" id="tid" name="tid" value="<?php echo_html($tid); ?>">
<input type="hidden" id="edit_mode" name="edit_mode" value="0">
<input type="hidden" id="citated_post" name="citated_post" value="">
<input type="hidden" id="return_post" name="return_post" value="">
<input type="hidden" id="edited_post" name="edited_post" value="">
<input type="hidden" id="special_case" name="special_case" value="">
<input type="hidden" id="profiled_topic" name="profiled_topic" value="">
<input type="hidden" id="stringent_rules" name="stringent_rules" value="">
<input type="hidden" id="login_active" name="login_active" value="">

<table id="post_message_table" class="form_table post_message_table">

<tr>
<th colspan="2" id="post_message_caption"><?php echo_html(text("NewMessage")); ?></th>
</tr>

<tr id="author_row">
<td><?php echo_html(text("Author")); ?>*:</td>
<td>
<?php
$author = $fmanager->get_last_posted_user_name();
$read_only = '';
if($fmanager->is_logged_in() && !(!empty($forum_data["user_posting_as_guest"]) && !empty($_SESSION["guest_posting_mode"])))
{
  $author = $fmanager->get_user_name();
  $read_only = ' class="read_only_field" readonly';
}
?>
<input type="text" id="author" name="author" value="<?php echo_html($fmanager->get_display_name($author)); ?>" <?php echo($read_only); ?> autocomplete="off" onkeypress="return handle_enter(event)">
</td>
</tr>

<?php if(!$fmanager->is_logged_in()): ?>
<tr id="enter_password_row">
<td>&nbsp;</td>
<td><div class="enter_password"><a href="<?php echo($base_url); ?>" onclick="return show_author_password()"><?php echo_html(text("EnterPassword")); ?></a></div></td>
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

<tr>
<td><?php echo_html(text("Subject")); ?>*:</td>
<td><input type="text" id="subject" name="subject" value="" class="read_only_field" readonly autocomplete="off" onkeypress="return handle_enter(event)"></td>
</tr>

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
    <div onclick="insert_tag('[radikal]','[/radikal]', 0)">RADIKAL</div>
    <div onclick="insert_tag('[reddit]','[/reddit]', 0)">REDDIT</div>
    <div onclick="insert_tag('[tiktok]','[/tiktok]', 0)">TIKTOK</div>
    <div onclick="insert_tag('[anim]','[/anim]', 0)">ANIM</div>
    <div onclick="insert_tag('[ascii-art]','[/ascii-art]', 0)">ASCII ART</div>
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

<tr id="profiled_topic_row" class="message_area" style="display:none">
<td colspan="2" style="padding-top: 0px">
  <?php
  $checked = "";
  if(!empty($_SESSION["thematic_per_default"]))
  {
    $checked = "checked";
  }
  ?>
   <table class="checkbox_table">
   <tr>
     <td>
      <input type="checkbox" id="is_thematic" name="is_thematic" tabindex="-1" <?php echo($checked); ?> onchange="check_thematic();"> 
     </td>
     <td>
      <label for="is_thematic"><?php echo_html(text("MessageIsThematic")); ?></label>
     </td>
   </tr>
   </table>
</td>
</tr>

<tr class="message_area">
<td colspan="2" style="padding-top: 0px">
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

<tr class="message_area">
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

<?php 
$stringent_rules_hidden = "display: none;";
if(!empty($forum_data["stringent_rules"]))
{
  $stringent_rules_hidden = "";
}
  $stringent_rules_hidden = "";
?>
<div id="stringent_rules_warning" class='guest_warning' style="<?php echo($stringent_rules_hidden); ?>"><?php echo(text("StringentRulesWarning")); ?></div>

<?php if(empty($may_write_to_topic)): ?>
<div class='guest_warning'><?php echo_html(text("WarnWritingNotPossible")); ?></div>
<?php elseif(!$fmanager->is_logged_in() && (!empty($forum_data["no_guests"]) || !empty($topic_data["no_guests"]))): ?>
<div class='guest_warning'><?php echo_html(text("WarnAuthorizationRequired")); ?></div>
<?php endif; ?>

<textarea autocomplete="off" id="message" name="message" onkeypress="return handle_enter(event)" onkeydown="return check_personal_appeal(event)"  onkeyup="return check_personal_appeal2(event)"></textarea>

<div id="load_last_version" style="visibility:hidden">
<a href="<?php echo($base_url); ?>" onclick='return confirm_load("<?php echo_js(text("MsgConfirmPostOverwrite"), true); ?>", { load_auto_saved: 1, topic: "<?php echo_js($tid); ?>", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("LoadLastSavedVersion")); ?></a>
</div>

<div class="clear_both"></div>

</td>
</tr>

<tr class="message_area">
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
<span id="paste_attachment" style="display:none"><a href="#" onclick="return paste_attachment_placeholder('', '')"><?php echo_html(text("PasteIntoMessage")); ?></a> /
<a href="#" onclick="return paste_attachment_placeholder('', 'spoiler')"><?php echo_html(text("Spoiler")); ?></a></span>
<span id="paste_attachment_gif" style="display:none">/ <a href="#" onclick="return paste_attachment_placeholder('', 'anim')"><?php echo_html(text("Animation")); ?></a></span>
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
  <span id="paste_attachment<?php echo $i; ?>" style="display:none"><a href="#" onclick="return paste_attachment_placeholder('<?php echo $i; ?>', '')"><?php echo_html(text("PasteIntoMessage")); ?></a> /
  <a href="#" onclick="return paste_attachment_placeholder('<?php echo $i; ?>', 'spoiler')"><?php echo_html(text("Spoiler")); ?></a></span>
  <span id="paste_attachment_gif<?php echo $i; ?>" style="display:none">/ <a href="#" onclick="return paste_attachment_placeholder('<?php echo $i; ?>', 'anim')"><?php echo_html(text("Animation")); ?></a></span>
  </td>
  </tr>
  </table>
  </div>
<?php endfor; ?>
<span id="paste_attachment_gallery"><a href="#" onclick="return paste_attachment_placeholder('', 'gallery')"><?php echo_html(text("PasteAsGallery")); ?></a>
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

<tr class="message_area">
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

<!-- END: object for posting dialog -->
