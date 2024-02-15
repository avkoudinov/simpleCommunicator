<script>
var topic_id = '<?php echo_js(val_or_empty($tid)); ?>';
var final_url = '<?php echo_js($final_url); ?>';
var ensure_anchor_visible = '<?php echo_js(val_or_empty($_SESSION["ensure_anchor_visible"])); ?>';
</script>

<script src='skins/<?php echo($skin); ?>/js/bbutils.js<?php echo($cache_appendix); ?>'></script>
<script src='<?php echo($view_path); ?>topic.js<?php echo($cache_appendix); ?>'></script>
<script src='skins/<?php echo($skin); ?>/js/attachment_gallery.js<?php echo($cache_appendix); ?>'></script>
<script src='skins/<?php echo($skin); ?>/js/attachment_posting.js<?php echo($cache_appendix); ?>'></script>
<script src='skins/<?php echo($skin); ?>/js/attachment_drag_drop.js<?php echo($cache_appendix); ?>'></script>
<script src='skins/<?php echo($skin); ?>/js/caret.js<?php echo($cache_appendix); ?>'></script>

<!--
<div id="fb-root"></div>
<script async defer src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.2"></script>
-->

<!--
<script defer src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
-->

<script async src="https://vp.rambler.ru/player/sdk.js"></script>

<?php
unset($_SESSION["ensure_anchor_visible"]);

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
var last_author = "<?php echo_js($fmanager->get_display_name($fmanager->get_user_name())); ?>";
var first_message = "<?php echo($first_message); ?>";
var last_message = "<?php echo($last_message); ?>";
var first_new_message = last_message;
var posts_per_page = <?php echo($fmanager->get_posts_per_page() + $pagination_info["pinned_message_count"]); ?>;
var loaded_message_count = <?php echo($pagination_info["loaded_message_count"]); ?>;
var is_last_page = <?php echo($pagination_info["last_page_message"] == $pagination_info["last_topic_message"] || $pagination_info["loaded_message_count"] == 0 ? "1" : "0") ?>;
var all_page_mode = <?php echo($pagination_info["mode"] == "all" ? "1" : "0"); ?>;
var filtered_comment_mode = <?php echo(!empty($topic_data["thematic_only"]) ? "1" : "0"); ?>;
var has_auto_saved_message = <?php echo($fmanager->has_auto_saved_message($tid) ? 1 : 0); ?>;

var archive_mode = <?php echo(!empty($settings["archive_mode"]) ? "1" : "0"); ?>;

<?php if(!reqvar_empty("leave_unread") || (!reqvar_empty("download") && $fmanager->is_logged_in())): ?>
do_not_check_new = true;
<?php endif; ?>

var user_tags = {};
<?php foreach($user_tags as $tgid => $tgname): ?> 
user_tags['#<?php echo_js($tgid); ?>'] = '<?php echo_js($tgname); ?>';
<?php endforeach; ?> 

<?php
$gotomsg_appendix = "";
$startmsg_appendix = "";
if ($pagination_info["first_page_message"] != $pagination_info["first_topic_message"]) {
  $gotomsg_appendix = "&msg=" . $pagination_info["first_page_message"];
  $startmsg_appendix = "&startmsg=" . $pagination_info["first_page_message"];
}
?>

function exec_load_new_posts(highlight_message, target_url)
{
  return load_new_posts('<?php echo_html($tid); ?>', '<?php echo_html($fid); ?>', highlight_message, target_url);
}

function exec_reload_nav_control(ctrl, all_entry_post)
{
  var params = { 
    topic: '<?php echo_js($tid); ?>', 
    forum: '<?php echo_html($fid); ?>', 
    ctrl: ctrl, 
    base_url: '<?php echo_js($base_url); ?>', 
    mode: '<?php echo_js($pagination_info["mode"]); ?>', 
    startmsg: '<?php echo_js($pagination_info["startmsg"]); ?>', 
    msg: '<?php echo_js($pagination_info["msg"]); ?>', 
    pinned_message_count: '<?php echo_js($pagination_info["pinned_message_count"]); ?>', 

    first_page_message: first_message, 
    last_page_message: last_message, 
    loaded_message_count: loaded_message_count, 
    posts_per_page: posts_per_page, 

    all_entry_post: all_entry_post 
  };
    
  return reload_nav_control(params);
}

function exec_reload_online_users()
{
  return reload_online_users('<?php echo_js($tid); ?>', '<?php echo_js($fid); ?>');
}

function search_on_enter(form, event)
{
  if(event.keyCode != 13) return true;

  search_user(form);

  return false;
}

var search_user_ajax = null;

function search_user(form)
{
  if(!form) return false;

  if(form.elements['user_to_search'].value == '')
  {
    form.elements['topic_to_search'].focus();
    return false;
  }

  if(form.elements['search_user_button']) form.elements['search_user_button'].classList.add("member_search_button_active");

  Forum.unselectAll(form.elements['found_users[]']);
  Forum.unselectAll(form.elements['selected_users[]']);

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
          if(this.search_form.elements['search_user_button']) this.search_form.elements['search_user_button'].classList.remove("member_search_button_active");
          return;
        }

        // remove old entries

        var found_users = this.search_form.elements['found_users[]'];

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

      if(this.search_form.elements['search_user_button']) this.search_form.elements['search_user_button'].classList.remove("member_search_button_active");
    };

    search_user_ajax.onerror = function(error, url, info)
    {
      if(this.search_form.elements['search_user_button']) this.search_form.elements['search_user_button'].classList.remove("member_search_button_active");

      Forum.handle_ajax_error(this, error, url, info);
    };
  } // init ajax

  search_user_ajax.abort();
  search_user_ajax.resetParams();

  search_user_ajax.search_form = form;

  search_user_ajax.setPOST('search_users', "1");
  search_user_ajax.setPOST('hash', get_protection_hash());
  search_user_ajax.setPOST('user_logged', user_logged);
  search_user_ajax.setPOST('trace_sql', trace_sql);
  search_user_ajax.setPOST('lookup_string', form.elements['user_to_search'].value);

  search_user_ajax.request("ajax/process.php");

  return false;
} // search_user

var topic_choose_apply_func = null;

function merge_topic()
{
  var elm = document.getElementById("found_topics");
  if(!elm) return false;

  if(!elm.value)
  {
    var mbuttons = [
      {
        caption: msg_OK,
        handler: function() { Forum.hide_user_msgbox(); }
      }
    ];

    Forum.show_user_msgbox(msg_Error, "<?php echo_js(text("ErrNoTopicSelected")); ?>", 'icon-error.gif', mbuttons);

    return;
  }

  do_action({ topic_action: "merge_topic", forum: "<?php echo_js($fid); ?>", topic: "<?php echo_js($tid); ?>", target_topic: elm.value });
}

function select_target_topic_for_merge()
{
  hide_all_popups();

  topic_choose_apply_func = merge_topic;
  topic_choose_apply_func.action = 'merge_topic';

  var buttons = [
    {
      caption: "<?php echo_js(text("Cancel")); ?>",
      handler: function() { Forum.hide_sys_lightbox(); }
    },
    {
      caption: "<?php echo_js(text("Apply")); ?>",
      addClass: "send_button",
      handler: function() { topic_choose_apply_func(topic_choose_apply_func.action); }
    }
  ];

  Forum.show_topic_selector("<?php echo_js(text("MergeTopic")); ?>", buttons, false, true, 600);

  return false;
}

function select_private_members()
{
  hide_all_popups();

  var buttons = [
    {
      caption: "<?php echo_js(text("Cancel")); ?>",
      handler: function() { Forum.hide_sys_lightbox(); }
    },
    {
      caption: "<?php echo_js(text("Apply")); ?>",
      addClass: "send_button",
      handler: function() {
        var form = document.getElementById("search_member_area_form");
        if(!form || !form.elements['selected_users[]']) return;

        var params = { topic_action: 'add_remove_private_members', topic: '<?php echo_html($tid); ?>', forum: '<?php echo_html($fid); ?>' };

        for(var i = 0; i < form.elements['selected_users[]'].options.length; i++)
        {
          form.elements['selected_users[]'].options[i].selected = false;
          params["topic_members[" + i + "]"] = form.elements['selected_users[]'].options[i].value;
        }

        do_action(params);
      }
    }
  ];

  show_member_selector("search_member_area", "<?php echo_js(text("ManageMembers")); ?>", "<?php echo_js(text("TopicMembers")); ?>", buttons, 600);

  return false;
}

function select_blocked_users()
{
  hide_all_popups();

  var buttons = [
    {
      caption: "<?php echo_js(text("Cancel")); ?>",
      handler: function() { Forum.hide_sys_lightbox(); }
    },
    {
      caption: "<?php echo_js(text("Apply")); ?>",
      addClass: "send_button",
      handler: function() {
        var form = document.getElementById("search_blocked_user_area_form");
        if(!form || !form.elements['selected_users[]']) return;

        var params = { topic_action: 'block_unblock_topic_users', topic: '<?php echo_html($tid); ?>', forum: '<?php echo_html($fid); ?>' };

        for(var i = 0; i < form.elements['selected_users[]'].options.length; i++)
        {
          form.elements['selected_users[]'].options[i].selected = false;
          params["blocked_users[" + i + "]"] = form.elements['selected_users[]'].options[i].value;
        }

        do_action(params);
      }
    }
  ];

  show_member_selector("search_blocked_user_area", "<?php echo_js(text("ManageMembers")); ?>", "<?php echo_js(text("BlockedUsers")); ?>", buttons, 600);

  return false;
}
</script>

<?php
require_once "topic_post_functions_inc.php";
?>

<?php
$moderators = "";

if(!empty($topic_data["moderators"]))
{
  foreach($topic_data["moderators"] as $mid => $minfo)
  {
    $moderators .= "<a href='view_profile.php?uid=$mid' >" . escape_html($minfo["name"]) . "</a>";

    if(empty($settings["hide_online_status"]) && !empty($minfo["online"]))
    {
      $moderators .= "&nbsp;<span class='online_text'>✓</span>";
    }

    $moderators .= ", ";
  }

  $moderators = trim($moderators, ", ");
}
?>

<!-- BEGIN: header3 -->

<div class="header3">

<div class="left_action_panel">

<?php if(!empty($topic_data["is_private"])): ?>

  <?php 
  if($fmanager->is_topic_moderator($tid)) 
    echo_html(text("Moderator"));
  elseif($topic_data["user_id"] == $fmanager->get_user_id())
    echo_html(text("Author"));
  else  
    echo_html(text("Member"));
  ?>:

  <?php if($fmanager->is_topic_moderator($tid)): ?>
  <?php if(empty($_SESSION["show_deleted"])): ?>
  <span class="separator">|</span> <a href="<?php echo($base_url . $gotomsg_appendix); ?>&show_deleted=1&hash=<?php echo_html($_SESSION["hash"]); ?>" onclick="check_actual_hash(this)" class="moderator_link"><?php echo_html(text("DisplayDeleted")); ?></a>
  <?php else: ?>
  <span class="separator">|</span> <a href="<?php echo($base_url . $gotomsg_appendix); ?>&hide_deleted=1&hash=<?php echo_html($_SESSION["hash"]); ?>" onclick="check_actual_hash(this)" class="moderator_link"><?php echo_html(text("HideDeleted")); ?></a>
  <?php endif; ?>
  
  <?php if(empty($topic_data["deleted"])): ?>
  <span class="separator">|</span> <a href="<?php echo($base_url); ?>" class="moderator_link" onclick='return confirm_action("<?php echo_js(text("MsgConfirmTopicDelete"), true); ?>", { topic_action: "delete_topic", topic: "<?php echo_js($tid); ?>", forum: "<?php echo_js($fid); ?>" });'><?php echo_html(text("DeleteTopic")); ?></a>
  <?php else: ?>
  <span class="separator">|</span> <a href="<?php echo($base_url); ?>" class="moderator_link" onclick='return do_action({ topic_action: "restore_topic", topic: "<?php echo_js($tid); ?>", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("RestoreTopic")); ?></a>
  <?php endif; ?>
  
  <?php if(empty($topic_data["closed"])): ?>
  <span class="separator">|</span> <a href="<?php echo($base_url); ?>" class="moderator_link" onclick='return confirm_action("<?php echo_js(text("MsgConfirmTopicClose"), true); ?>", { topic_action: "close_topic", topic: "<?php echo_js($tid); ?>", forum: "<?php echo_js($fid); ?>" });'><?php echo_html(text("CloseTopic")); ?></a>
  <?php else: ?>
  <span class="separator">|</span> <a href="<?php echo($base_url); ?>" class="moderator_link" onclick='return do_action({ topic_action: "open_topic", topic: "<?php echo_js($tid); ?>", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("OpenTopic")); ?></a>
  <?php endif; ?>
  
  <?php endif; // if moderator ?>

  <?php if($topic_data["user_id"] == $fmanager->get_user_id() && $topic_data["is_private"] == 2): ?>
  <span class="separator">|</span> <a href="<?php echo($base_url); ?>" class="moderator_link" onclick='return select_private_members()'><?php echo_html(text("ManageMembers")); ?></a> 
  <?php endif; ?>
  
  <?php if($topic_data["is_private"] != 2 || $topic_data["user_id"] != $fmanager->get_user_id()): ?>
    <?php if($topic_data["user_id"] == $fmanager->get_user_id() && $topic_data["is_private"] == 2): ?>|<?php endif; ?>
  
    <span class="separator">|</span> <a href="<?php echo($base_url); ?>" class="moderator_link" onclick='return confirm_action("<?php echo_js(text("MsgConfirmLeaveTopic"), true); ?>", { topic_action: "leave_topic", topic: "<?php echo_js($tid); ?>", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("LeaveTopic")); ?></a>
  <?php endif; ?>

<?php 
elseif($fmanager->is_admin() || $fmanager->is_forum_moderator($fid) || $fmanager->is_topic_moderator($tid)): ?>

<?php if(empty($_SESSION["show_deleted"])): ?>
<span class="separator">|</span> <a href="<?php echo($base_url . $gotomsg_appendix); ?>&show_deleted=1&hash=<?php echo_html($_SESSION["hash"]); ?>" class="moderator_link"><?php echo_html(text("DisplayDeleted")); ?></a>
<?php else: ?>
<span class="separator">|</span> <a href="<?php echo($base_url . $gotomsg_appendix); ?>&hide_deleted=1&hash=<?php echo_html($_SESSION["hash"]); ?>" class="moderator_link"><?php echo_html(text("HideDeleted")); ?></a>
<?php endif; ?>

  <span class="separator">|</span> <a href="<?php echo($base_url); ?>" class="moderator_link" onclick='return select_blocked_users()'><?php echo_html(text("ManageMembers")); ?></a>

<?php if($fmanager->is_topic_moderator($tid)): ?>

  <?php if(empty($forum_data["no_guests"])): ?>
    <?php if(empty($topic_data["no_guests"])): ?>
    <span class="separator">|</span> <a href="<?php echo($base_url); ?>" class="moderator_link" onclick='return confirm_action("<?php echo_js(text("MsgConfirmDisallowGuests"), true); ?>", { topic_action: "disallow_guests", topic: "<?php echo_js($tid); ?>", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("DisallowGuests")); ?></a>
    <?php else: ?>
    <span class="separator">|</span> <a href="<?php echo($base_url); ?>" class="moderator_link" onclick='return do_action({ topic_action: "allow_guests", topic: "<?php echo_js($tid); ?>", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("AllowGuests")); ?></a>
    <?php endif; ?>
  <?php endif; // no guests ?>

<?php else: ?>

  <?php if(empty($topic_data["pinned"])): ?>
  <span class="separator">|</span> <a href="<?php echo($base_url); ?>" class="moderator_link" onclick='return confirm_action("<?php echo_js(text("MsgConfirmPinTopic"), true); ?>", { topic_action: "pin_topic", topic: "<?php echo_js($tid); ?>", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("PinTopic")); ?></a>
  <?php else: ?>
  <span class="separator">|</span> <a href="<?php echo($base_url); ?>" class="moderator_link" onclick='return do_action({ topic_action: "unpin_topic", topic: "<?php echo_js($tid); ?>", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("UnpinTopic")); ?></a>
  <?php endif; ?>

  <?php if(empty($topic_data["deleted"])): ?>
  <span class="separator">|</span> <a href="<?php echo($base_url); ?>" class="moderator_link" onclick='return confirm_action_with_comment("<?php echo_js(text("MsgConfirmTopicDelete"), true); ?>", { topic_action: "delete_topic", topic: "<?php echo_js($tid); ?>", forum: "<?php echo_js($fid); ?>" });'><?php echo_html(text("DeleteTopic")); ?></a>
  <?php else: ?>
  <span class="separator">|</span> <a href="<?php echo($base_url); ?>" class="moderator_link" onclick='return do_action({ topic_action: "restore_topic", topic: "<?php echo_js($tid); ?>", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("RestoreTopic")); ?></a>
  <?php endif; ?>

  <?php if(empty($topic_data["closed"])): ?>
  <span class="separator">|</span> <a href="<?php echo($base_url); ?>" class="moderator_link" onclick='return confirm_action("<?php echo_js(text("MsgConfirmTopicClose"), true); ?>", { topic_action: "close_topic", topic: "<?php echo_js($tid); ?>", forum: "<?php echo_js($fid); ?>" });'><?php echo_html(text("CloseTopic")); ?></a>
  <?php else: ?>
  <span class="separator">|</span> <a href="<?php echo($base_url); ?>" class="moderator_link" onclick='return do_action({ topic_action: "open_topic", topic: "<?php echo_js($tid); ?>", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("OpenTopic")); ?></a>
  <?php endif; ?>

<div style="display: inline-block;position: relative">
<a href="<?php echo($base_url); ?>" class="moderator_link" onclick="return toggle_forum_selection_area()"><?php echo_html(text("MoveTopic")); ?></a>
  <div id="forum_selection_area" class="forum_selection_area" style="display:none">
    <div style="position: absolute;right:2px;top:2px;cursor:pointer" onclick="toggle_forum_selection_area()"><img src="<?php echo($view_path); ?>images/cross.png" alt="<?php echo_html(text("Close")); ?>"></div>

  &nbsp;&nbsp;<?php echo_html(text("Forum")); ?>:
    <div class="select_container">
      <input type="text" class="search_field" id="forum_selector_move" autocomplete="off" placeholder="<?php echo_html(text("GotoForum")); ?>" onkeypress="return forum_move_handle_enter(this.id, event, { topic_action: 'move_topic', forum: '<?php echo_js($fid); ?>', topic: '<?php echo_js($tid); ?>' })" onkeyup="return filter_entries(this, event)" onfocus="reset_forum_selector(this.id);">

      <select id="forum_selector_move_lookup" size="15"
         onclick="if(!mustAdjustMultiSelect()) { lookup_move_to_forum('forum_selector_move', { topic_action: 'move_topic', forum: '<?php echo_js($fid); ?>', topic: '<?php echo_js($tid); ?>' }); }" 
         onchange="if(mustAdjustMultiSelect()) { lookup_move_to_forum_if_active('forum_selector_move', { topic_action: 'move_topic', forum: '<?php echo_js($fid); ?>', topic: '<?php echo_js($tid); ?>' }); }" 

         onkeypress="return forum_move_handle_enter('forum_selector_move', event, { topic_action: 'move_topic', forum: '<?php echo_js($fid); ?>', topic: '<?php echo_js($tid); ?>' })"
      >

      <?php foreach($forum_list as $sfid => $fdata): 
         if($sfid == $fid) continue;
      ?>
      <option value="<?php echo_html($sfid); ?>"><?php echo_html($fdata["name"]); ?></option>
      <?php endforeach; ?>
      </select>
    </div>

  </div>
</div>

  <span class="separator">|</span> <a href="<?php echo($base_url); ?>" class="moderator_link" onclick="return select_target_topic_for_merge()"><?php echo_html(text("MergeTopic")); ?></a>

  <?php if(empty($topic_data["profiled_topic"])): ?>
  <span class="separator">|</span> <a href="<?php echo($base_url); ?>" class="moderator_link" onclick='return confirm_action("<?php echo_js(text("MsgConfirmTurnProfiledModeOn"), true); ?>", { topic_action: "profiled_topic_on", topic: "<?php echo_js($tid); ?>", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("TurnProfiledModeOn")); ?></a>
  <span class="separator">|</span> <a href="<?php echo($base_url); ?>" class="moderator_link" onclick='return confirm_action("<?php echo_js(text("MsgConfirmTurnBlogModeOn"), true); ?>", { topic_action: "blog_topic_on", topic: "<?php echo_js($tid); ?>", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("TurnBlogModeOn")); ?></a>
  <?php elseif($topic_data["profiled_topic"] == 1): ?>
  <span class="separator">|</span> <a href="<?php echo($base_url); ?>" class="moderator_link" onclick='return do_action({ topic_action: "profiled_topic_off", topic: "<?php echo_js($tid); ?>", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("TurnProfiledModeOff")); ?></a>
  <?php elseif($topic_data["profiled_topic"] == 2): ?>
  <span class="separator">|</span> <a href="<?php echo($base_url); ?>" class="moderator_link" onclick='return do_action({ topic_action: "blog_topic_off", topic: "<?php echo_js($tid); ?>", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("TurnBlogModeOff")); ?></a>
  <?php endif; ?>

  <?php if(empty($forum_data["no_guests"])): ?>

  <?php if(empty($topic_data["no_guests"])): ?>
  <span class="separator">|</span> <a href="<?php echo($base_url); ?>" class="moderator_link" onclick='return confirm_action("<?php echo_js(text("MsgConfirmDisallowGuests"), true); ?>", { topic_action: "disallow_guests", topic: "<?php echo_js($tid); ?>", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("DisallowGuests")); ?></a>
  <?php else: ?>
  <span class="separator">|</span> <a href="<?php echo($base_url); ?>" class="moderator_link" onclick='return do_action({ topic_action: "allow_guests", topic: "<?php echo_js($tid); ?>", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("AllowGuests")); ?></a>
  <?php endif; ?>

  <?php endif; // no guests ?>

<?php endif; ?>

<?php endif; ?>

<?php if(!empty($_SESSION["has_forums_with_user_guest_posting"]) && $fmanager->is_logged_in() && !$fmanager->is_master_admin() && !empty($forum_data["user_posting_as_guest"])): ?>
<?php if(empty($_SESSION["guest_posting_mode"])): ?>
<span class="separator">|</span> <a href="<?php echo($base_url . $gotomsg_appendix); ?>&guest_posting_on=1&hash=<?php echo_html($_SESSION["hash"]); ?>" onclick="check_actual_hash(this)" class="moderator_link"><?php echo_html(text("GuestPostingModeOn")); ?></a>
<?php else: ?>
<span class="separator">|</span> <a href="<?php echo($base_url . $gotomsg_appendix); ?>&guest_posting_off=1&hash=<?php echo_html($_SESSION["hash"]); ?>" onclick="check_actual_hash(this)" class="moderator_link"><?php echo_html(text("GuestPostingModeOff")); ?></a>
<?php endif; ?>
<?php endif; ?>

<?php if(!empty($topic_data["profiled_topic"])): ?>
<?php if(empty($topic_data["thematic_only"])): ?>
<span class="separator">|</span> <a href="<?php echo(preg_replace("/&force_comments=1/", "", $base_url) . $startmsg_appendix); ?>&show_thematic_messages=1&hash=<?php echo_html($_SESSION["hash"]); ?>" onclick="check_actual_hash(this)" class="moderator_link"><?php echo_html(text("ThematicOnly")); ?></a>
<?php else: ?>
<span class="separator">|</span> <a href="<?php echo(preg_replace("/&force_comments=1/", "", $base_url) . $startmsg_appendix); ?>&show_all_messages=1&hash=<?php echo_html($_SESSION["hash"]); ?>" onclick="check_actual_hash(this)" class="moderator_link"><?php echo_html(text("ShowAllMessages")); ?></a>
<?php endif; ?>
<?php endif; ?>


</div>

<div class="right_action_panel">

<?php if(!empty($topic_data["publish_delay"]) && $topic_data["user_id"] == $fmanager->get_user_id()): ?>
<span id="publish_topic"><a href="<?php echo($base_url); ?>" onclick='return confirm_action("<?php echo_js(text("MsgConfirmTopicPublish"), true); ?>", { topic_action: "publish", topic: "<?php echo_js($tid); ?>", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("Publish")); ?></a> |</span>
<?php endif; ?>

<?php if(empty($topic_data["in_favourites"])): ?>
<a id="favourites_action" href="<?php echo($base_url); ?>" onclick='return confirm_action("<?php echo_js(text("MsgConfrimAddToFavourites"), true); ?>", { topic_action: "add_to_favourites", topic: "<?php echo_js($tid); ?>", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("AddToFavourites")); ?></a> |
<?php else: ?>
<a id="favourites_action" href="<?php echo($base_url); ?>" onclick='return do_action({ topic_action: "remove_from_favourites", topic: "<?php echo_js($tid); ?>", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("RemoveFromFavourites")); ?></a> |
<?php endif; ?>

<?php if($fmanager->is_logged_in() && !$fmanager->is_master_admin()): ?>
  <?php if(empty($topic_data["subscribed"])): ?>
  <a id="subscribe_action" href="<?php echo($base_url); ?>" onclick='return confirm_action("<?php echo_js(text("MsgConfirmSubscribeToTopic"), true); ?>", { topic_action: "subscribe", topic: "<?php echo_js($tid); ?>", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("Subscribe")); ?></a> |
  <?php else: ?>
  <a id="subscribe_action" href="<?php echo($base_url); ?>" onclick='return do_action({ topic_action: "unsubscribe", topic: "<?php echo_js($tid); ?>", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("Unsubscribe")); ?></a> |
  <?php endif; ?>
<?php endif; ?>

<?php if(empty($topic_data["in_ignored"])): ?>
<a id="ignore_action" href="<?php echo($base_url); ?>" onclick='return confirm_action("<?php echo_js(text("MsgConfirmTopicIgnore"), true); ?>", { topic_action: "add_to_ignored", topic: "<?php echo_js($tid); ?>", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("AddToIgnoredTopics")); ?></a> |
<?php else: ?>
<a id="ignore_action" href="<?php echo($base_url); ?>" onclick='return do_action({ topic_action: "remove_from_ignored", topic: "<?php echo_js($tid); ?>", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("RemoveFromIgnoredTopics")); ?></a> |
<?php endif; ?>

<?php if(empty($topic_data["user_pinned"])): ?>
<a id="pin_user_action" href="<?php echo($base_url); ?>" onclick='return confirm_action("<?php echo_js(text("MsgConfirmPinTopic"), true); ?>", { topic_action: "pin_user_topic", topic: "<?php echo_js($tid); ?>", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("PinTopic")); ?></a> |
<?php else: ?>
<a id="pin_user_action" href="<?php echo($base_url); ?>" onclick='return do_action({ topic_action: "unpin_user_topic", topic: "<?php echo_js($tid); ?>", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("UnpinTopic")); ?></a> |
<?php endif; ?>

<a href="<?php echo($base_url); ?>" onclick='return confirm_action("<?php echo_js(text("MsgConfirmMarkRead"), true); ?>", { mark_read_action: "mark_topic_read", topic: "<?php echo_js($tid); ?>", uncritical: 1 })'><?php echo_html(text("MarkReadShort")); ?></a> / <a href="<?php echo($base_url); ?>" onclick='return confirm_action("<?php echo_js(text("MsgConfirmMarkUnread"), true); ?>", { mark_read_action: "mark_topic_unread", start_post: "<?php echo_js($first_message); ?>", uncritical: 1 })'><?php echo_html(text("MarkUnread")); ?></a> |

<?php if($fmanager->is_logged_in()): ?>
<a href="<?php echo($base_url . $startmsg_appendix . "&download=1"); ?>"><?php echo_html(text("Download")); ?></a> |
<?php endif; ?>

<a href="<?php echo($base_url); ?>" onclick='return toggle_filter_actions()'><?php echo_html(text("Filter")); ?></a> 

  <div id="filter_actions" class="filter_actions" style="display:none">
    <div style="position: absolute;right:2px;top:2px;cursor:pointer" onclick="return toggle_filter_actions()"><img src="<?php echo($view_path); ?>images/cross.png" alt="<?php echo_html(text("Close")); ?>"></div>

    <span style="font-weight: bold"><?php echo_html(text("Filter")); ?>:</span><br>
    
    <?php if($fmanager->get_user_name() != ""): ?>
    <a href="search.php?do_search=1&tid=<?php echo_html($tid); ?>&author_mode=wrote_post&author=<?php echo(xrawurlencode($fmanager->get_user_name())); ?>&start_from=<?php echo($first_message); ?>"><?php echo_html(text("MyMessages")); ?></a> 
    <?php endif; ?>

    <a href="search.php?do_search=1&tid=<?php echo_html($tid); ?>&author_mode=wrote_post&author=<?php echo(xrawurlencode($topic_data["author"])); ?>&start_from=<?php echo($first_message); ?>"><?php echo_html(text("TopicAuthorMessages")); ?></a> 

    <?php if(empty($_SESSION["hide_ignored"]) && $fmanager->has_ignored()): ?>
    <a href="search.php?do_search=1&tid=<?php echo_html($tid); ?>&non_ignored_by_author=1&start_from=<?php echo($first_message); ?>"><?php echo_html(text("TopicNotIgnoredMessages")); ?></a> 
    <?php endif; ?>

    <a href="search.php?do_search=1&tid=<?php echo_html($tid); ?>&has_attachment=1&start_from=<?php echo($first_message); ?>"><?php echo_html(text("SearchAttachmentsOnly")); ?></a> 

    <a href="search.php?do_search=1&tid=<?php echo_html($tid); ?>&has_picture=1&start_from=<?php echo($first_message); ?>"><?php echo_html(text("SearchPicturesOnly")); ?></a> 

    <a href="search.php?do_search=1&tid=<?php echo_html($tid); ?>&has_video=1&start_from=<?php echo($first_message); ?>"><?php echo_html(text("SearchVideosOnly")); ?></a> 

    <a href="search.php?do_search=1&tid=<?php echo_html($tid); ?>&has_audio=1&start_from=<?php echo($first_message); ?>"><?php echo_html(text("SearchAudioOnly")); ?></a> 

    <a href="search.php?do_search=1&tid=<?php echo_html($tid); ?>&has_adult=1&start_from=<?php echo($first_message); ?>"><?php echo_html(text("SearchAdultOnly")); ?></a> 
  </div>

</div>

<div class="clear_both"></div>


</div>

<!-- END: header3 -->

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
<?php elseif(!empty($_SESSION["blocked"])): 
$self_blocked_class = "";
if(val_or_empty($_SESSION["self_blocked"]) == 1) $self_blocked_class = "self_blocked";
elseif(val_or_empty($_SESSION["self_blocked"]) == 2) $self_blocked_class = "author_dead";
?>
<span class="closed <?php echo($self_blocked_class); ?>">[<?php echo_html(empty($_SESSION["block_time_left"]) ? text("blocked") : sprintf(text("blocked_until"), $_SESSION["block_time_left"])); ?>]</span>
<?php endif; ?>

<?php
$display = "style='display:none'";
if(!empty($topics_with_new_count)) $display = "";
?>
<span class="new topics_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span>

<?php
$not_preferred = "";
if(!empty($_SESSION["preferred_forums"]) && empty($_SESSION["preferred_forums"][$fid]) && empty($topic_data["is_private"])) $not_preferred = "not_preferred";
?>
/ <a href="forum.php?fid=<?php echo_html($fid_for_url); ?><?php echo($fpage_appendix); ?>" class="<?php echo($not_preferred); ?>"><?php echo_html($forum_title); ?></a>

<?php if(!empty($forum_data["disable_ignore"])): ?>
<span class="ignore_off">[<?php echo_html(text("ignore_off")); ?>]</span>
<?php endif; ?>

<?php if(!empty($forum_data["deleted"])): ?>
<span class="closed">[<?php echo_html(text("deleted")); ?>]</span>
<?php endif; ?>

<?php if(!empty($forum_data["closed"])): ?>
<span class="closed">[<?php echo_html(text("closed")); ?>]</span>
<?php endif; ?>

<?php if(!empty($forum_data["blocked"])): ?>
<span class="closed">[<?php echo_html(empty($forum_data["block_time_left"]) ? text("forum_blocked") : sprintf(text("forum_blocked_until"), $forum_data["block_time_left"])); ?>]</span>
<?php elseif(!empty($forum_data["no_guests"]) && !$fmanager->is_logged_in()): ?>
<span class="closed">[<?php echo_html(text("closed_for_guests")); ?>]</span>
<?php endif; ?>

<?php if(!empty($topic_data["is_private"])):
$display = "style='display:none'";
if(!empty($private_topics_with_new_count)) $display = "";
?>
<span class="new private_topics_with_new_indicator" <?php echo($display); ?>>[<a href="<?php echo("new_messages.php?fid=private"); ?>"><?php echo_html(text("new")); ?>:<span class='private_topics_with_new_count'><?php echo($private_topics_with_new_count); ?></span></a>]</span>
<?php else:
$display = "style='display:none'";
if(!empty($forum_data["topics_with_new_count"])) $display = "";
?>
<span class="new forum_with_new_indicator" data-fid="<?php echo_html($fid_for_url); ?>" <?php echo($display); ?>>[<a href="<?php echo("new_messages.php?fid=" . $fid); ?>"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($forum_data["topics_with_new_count"]); ?></span></a>]</span>
<?php endif; ?>

/ 

<?php if(val_or_empty($topic_data["profiled_topic"]) == 1): ?>
<span class="topic_type_indicator"><?php echo_html(text("Dedicated")); ?>:</span>
<?php elseif(val_or_empty($topic_data["profiled_topic"]) == 2): ?>
<span class="topic_type_indicator"><?php echo_html(text("Blog")); ?>:</span>
<?php endif; ?>

<span class="topic_title_main"><?php echo_html($topic_title); ?></span>

<?php if(!empty($topic_data["in_ignored"])): ?>
<span class="<?php echo(empty($forum_data["disable_ignore"]) ? "closed" : "ignore_off"); ?>">[<?php echo_html(text("ignored")); ?>]</span>
<?php endif; ?>

<?php if(!empty($topic_data["deleted"])): ?>
<span class="closed">[<?php echo_html(text("deleted")); ?>]</span>
<?php endif; ?>

<?php if(!empty($topic_data["closed"])): ?>
<span class="closed">[<?php echo_html(text("closed")); ?>]</span>
<?php endif; ?>

<?php if(!empty($topic_data["publish_delay"])): ?>
<span class="closed not_published">[<?php echo_html(text("not_published")); ?>]</span>
<?php endif; ?>

<?php if(!empty($topic_data["blocked"])): ?>
<span class="closed">[<?php echo_html(text("topic_blocked")); ?>]</span>
<?php elseif(empty($forum_data["no_guests"]) && !empty($topic_data["no_guests"]) && !$fmanager->is_logged_in()): ?>
<span class="closed">[<?php echo_html(text("closed_for_guests")); ?>]</span>
<?php endif; ?>

<?php if(!reqvar_empty("download") && $fmanager->is_logged_in()): ?>
<span class="new">[<?php echo_html(text("downloaded")); ?>]</span>
<?php endif; ?>

<?php
$display = "style='display:none'";
if(!empty($topic_data["new_messages_count"])) $display = "";
?>
<span class="new new_messages_indicator" <?php echo($display); ?>>[<a href="<?php echo($base_url); ?>&gotonew=1" rel="nofollow" onclick="return exec_load_new_posts(-1, this.href)"><?php echo_html(text("new")); ?>:<span class='new_messages_count'><?php echo(val_or_empty($topic_data["new_messages_count"])); ?></span></a>]</span>

</div>

<?php if(!empty($moderators)): ?>
  <div class="forum_moderator_bar"><?php echo(escape_html(text("Moderators")) . ": " . $moderators); ?></div>
<?php endif; ?>

<div class="clear_both">
</div>

<?php
$all_entry_post = $first_message;
?>

<div class="message_info_bar">
<?php require "message_info_bar_inc.php"; ?>
</div>

<div class="navigator_bar">
<?php require "navigator_bar_inc.php"; ?>
</div>

<div class="forum_action_bar">
<div style="position: relative">
  <div class="post_anchor <?php if(!empty($_SESSION["skin_properties"][$skin]["pin_the_menu"])) echo "post_anchor_fixed_menu"; ?>" id="post_anchor_top_new_message"></div>
</div>

<table>
<tr>
<td>
<?php
$forum_selector_id = 1;
@include "forum_selector_inc.php";
?>
</td>

<?php if($may_write_to_topic): ?>
<td>
<input type="button" class="standard_button" value="<?php echo_html(text("NewMessage")); ?>" onclick='new_message("first_post_container", first_message, "<?php echo_js($tid, true); ?>", "<?php echo_js($topic_title, true); ?>", "<?php echo($topic_data["profiled_topic_final"]); ?>", <?php echo(!empty($forum_data["stringent_rules"]) ? 1 : 0); ?>)'>
</td>
<?php endif; ?>

</tr>
</table>
</div>

<div class="clear_both">
</div>

</div>

<!-- END: forum_bar -->

<a id="anchor_first_post_container"></a>
<div id="first_post_container" class="post_container"></div>

<div id="post_area">

  <div class="navigation_arrows">
  <div class="scroll_up" onclick="window.scrollTo(0, 0);"></div>
  <div class="scroll_down" onclick="window.scrollTo(0, 1000000);"></div>
  </div>

<?php foreach($post_list as $pid => $pinfo): ?>

<?php
if(!empty($pinfo["warn_year_interval"]))
{
  echo "<div class='year_period_warning'>" . escape_html(text("OverYearInerval")) . "</div>";
}
?>

<div class="message_container" id="post_<?php echo_html($pid); ?>">
<?php
require "topic_message_tpl_inc.php";
?>
</div>

<?php endforeach; ?>

</div> <!-- foreach post -->

<?php if(count($post_list) == 0): ?>
<table id="no_posts_message" class="topic_table">
<tr>
<td class="table_message"><?php echo_html(text("NoPosts")); ?></td>
</tr>
</table>
<?php endif; ?>

<a id="anchor_last_post_container"></a>
<div id="last_post_container" class="post_container"></div>

<?php
if($pagination_info["mode"] == "all" || $pagination_info["last_page_message"] == $pagination_info["last_topic_message"]):
$display = "style='display:none'";
if(!empty($topic_data["new_messages_count"])) $display = "";
?>
<div id="new_messages_alertbox" class="new_messages_alertbox" <?php echo($display); ?>>
<a href="<?php echo($base_url); ?>&gotonew=1" rel="nofollow" onclick="return exec_load_new_posts(-1, this.href)"><?php echo_html(text("NewMessages")); ?></a> <span class="new new_messages_indicator">[<a href="<?php echo($base_url); ?>&gotonew=1" rel="nofollow" onclick="return exec_load_new_posts(-1, this.href)"><?php echo_html(text("new")); ?>:<span class='new_messages_count'><?php echo(val_or_empty($topic_data["new_messages_count"])); ?></span></a>]</span>
</div>
<div class="clear_both"></div>
<?php
endif;
?>

<!-- BEGIN: forum_bar -->

<div class="forum_bar">

<?php
$all_entry_post = $last_message;
?>

<div class="message_info_bar">
<?php require "message_info_bar_inc.php"; ?>
</div>

<div class="navigator_bar">
<?php require "navigator_bar_inc.php"; ?>
</div>

<div class="forum_action_bar">
<table>
<tr>
<td>
<?php
$forum_selector_id = 2;
@include "forum_selector_inc.php";
?>
</td>

<?php if($may_write_to_topic): ?>
<td id='bottom_new_message'>
<input type="button" class="standard_button" value="<?php echo_html(text("NewMessage")); ?>" onclick='new_message("last_post_container", last_message, "<?php echo_js($tid, true); ?>", "<?php echo_js($topic_title, true); ?>", "<?php echo($topic_data["profiled_topic_final"]); ?>", <?php echo(!empty($forum_data["stringent_rules"]) ? 1 : 0); ?>)'>
</td>
<?php endif; ?>

</tr>
</table>
</div>

<div class="clear_both"></div>

<div class="forum_name_bar"><a href="forums.php"><?php echo_html(text("Forums")); ?></a>

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

<?php
$display = "style='display:none'";
if(!empty($topics_with_new_count)) $display = "";
?>
<span class="new topics_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span>

<?php
$not_preferred = "";
if(!empty($_SESSION["preferred_forums"]) && empty($_SESSION["preferred_forums"][$fid]) && empty($topic_data["is_private"])) $not_preferred = "not_preferred";
?>
/ <a href="forum.php?fid=<?php echo_html($fid_for_url); ?><?php echo($fpage_appendix); ?>" class="<?php echo($not_preferred); ?>"><?php echo_html($forum_title); ?></a>

<?php if(!empty($forum_data["disable_ignore"])): ?>
<span class="ignore_off">[<?php echo_html(text("ignore_off")); ?>]</span>
<?php endif; ?>

<?php if(!empty($forum_data["deleted"])): ?>
<span class="closed">[<?php echo_html(text("deleted")); ?>]</span>
<?php endif; ?>

<?php if(!empty($forum_data["closed"])): ?>
<span class="closed">[<?php echo_html(text("closed")); ?>]</span>
<?php endif; ?>

<?php if(!empty($forum_data["blocked"])): ?>
<span class="closed">[<?php echo_html(empty($forum_data["block_time_left"]) ? text("forum_blocked") : sprintf(text("forum_blocked_until"), $forum_data["block_time_left"])); ?>]</span>
<?php elseif(!empty($forum_data["no_guests"]) && !$fmanager->is_logged_in()): ?>
<span class="closed">[<?php echo_html(text("closed_for_guests")); ?>]</span>
<?php endif; ?>

<?php if(!empty($topic_data["is_private"])):
$display = "style='display:none'";
if(!empty($private_topics_with_new_count)) $display = "";
?>
<span class="new private_topics_with_new_indicator" <?php echo($display); ?>>[<a href="<?php echo("new_messages.php?fid=private"); ?>"><?php echo_html(text("new")); ?>:<span class='private_topics_with_new_count'><?php echo($private_topics_with_new_count); ?></span></a>]</span>
<?php else:
$display = "style='display:none'";
if(!empty($forum_data["topics_with_new_count"])) $display = "";
?>
<span class="new forum_with_new_indicator" data-fid="<?php echo_html($fid_for_url); ?>" <?php echo($display); ?>>[<a href="<?php echo("new_messages.php?fid=" . $fid); ?>"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($forum_data["topics_with_new_count"]); ?></span></a>]</span>
<?php endif; ?>

/ 

<?php if(val_or_empty($topic_data["profiled_topic"]) == 1): ?>
<span class="topic_type_indicator"><?php echo_html(text("Dedicated")); ?>:</span>
<?php elseif(val_or_empty($topic_data["profiled_topic"]) == 2): ?>
<span class="topic_type_indicator"><?php echo_html(text("Blog")); ?>:</span>
<?php endif; ?>

<span class="topic_title_main"><?php echo_html($topic_title); ?></span>

<?php if(!empty($topic_data["in_ignored"])): ?>
<span class="<?php echo(empty($forum_data["disable_ignore"]) ? "closed" : "ignore_off"); ?>">[<?php echo_html(text("ignored")); ?>]</span>
<?php endif; ?>

<?php if(!empty($topic_data["deleted"])): ?>
<span class="closed">[<?php echo_html(text("deleted")); ?>]</span>
<?php endif; ?>

<?php if(!empty($topic_data["closed"])): ?>
<span class="closed">[<?php echo_html(text("closed")); ?>]</span>
<?php endif; ?>

<?php if(!empty($topic_data["publish_delay"])): ?>
<span class="closed not_published">[<?php echo_html(text("not_published")); ?>]</span>
<?php endif; ?>

<?php if(!empty($topic_data["blocked"])): ?>
<span class="closed">[<?php echo_html(text("topic_blocked")); ?>]</span>
<?php elseif(empty($forum_data["no_guests"]) && !empty($topic_data["no_guests"]) && !$fmanager->is_logged_in()): ?>
<span class="closed">[<?php echo_html(text("closed_for_guests")); ?>]</span>
<?php endif; ?>

<?php if(!reqvar_empty("download") && $fmanager->is_logged_in()): ?>
<span class="new">[<?php echo_html(text("downloaded")); ?>]</span>
<?php endif; ?>

<?php
$display = "style='display:none'";
if(!empty($topic_data["new_messages_count"])) $display = "";
?>
<span class="new new_messages_indicator" <?php echo($display); ?>>[<a href="<?php echo($base_url); ?>&gotonew=1" rel="nofollow" onclick="return exec_load_new_posts(-1, this.href)"><?php echo_html(text("new")); ?>:<span class='new_messages_count'><?php echo(val_or_empty($topic_data["new_messages_count"])); ?></span></a>]</span>

</div>

<?php if(!empty($moderators)): ?>
  <div class="forum_moderator_bar"><?php echo(escape_html(text("Moderators")) . ": " . $moderators); ?></div>
<?php endif; ?>

<div class="clear_both">
</div>

</div>

<!-- END: forum_bar -->

<?php
require_once "topic_lookup_inc.php";
require_once "tag_editor_inc.php";
require_once "topic_post_objects_inc.php";
?>

<div id="search_member_area" class="search_member_area">

<form action="topic.php" id="search_member_area_form" method="post">

   <table class="aux_table search_member_area_table" style="width:100%">
   <tr>
   <td ><input type="text" name="user_to_search" autocomplete="off" value="" placeholder="<?php echo_html(text("SearchUser")); ?>" onkeypress="return search_on_enter(this.form, event)"></td>
   <td style="text-align: right; width: 1%;">
   <input type="button" name="search_user_button" class="standard_button member_search_button" value="<?php echo_html(text("Search")); ?>" onclick="search_user(this.form)">
   </td>
   </tr>
   </table>

   <table class="list_group" style="width:100%">
   <tr>
   <th><?php echo_html(text("FoundUsers")); ?></th>
   <th></th>
   <th class="selected_users_caption">...</th>
   </tr>
   <tr>
   <td>
   <select multiple class="multiple_choice" name="found_users[]" onDblClick="Forum.moveSelectedItems(this.form.elements['found_users[]'], this.form.elements['selected_users[]'])">
   </select>
   </td>
   <td>
   <input type="button" class="standard_button" value="&gt;&gt;" onclick="Forum.moveSelectedItems(this.form.elements['found_users[]'], this.form.elements['selected_users[]']); this.form.elements['user_to_search'].value = '';">
   <input type="button" class="standard_button" value="&lt;&lt;" onclick="Forum.moveSelectedItems(this.form.elements['selected_users[]'], this.form.elements['found_users[]'])">
   </td>
   <td>
   <select multiple class="multiple_choice" name="selected_users[]" onDblClick="Forum.moveSelectedItems(this.form.elements['selected_users[]'], this.form.elements['found_users[]'])">
   <?php foreach($topic_members as $mid => $name): ?>
   <option value="<?php echo_html($mid); ?>"><?php echo_html($name); ?></option>
   <?php endforeach; ?>
   </select>
   </td>
   </tr>
   </table>

</form>

</div>

<div id="search_blocked_user_area" class="search_member_area">

<form action="topic.php" id="search_blocked_user_area_form" method="post">

   <?php 
   $caption = text("UnblockUsers");
   if($fmanager->is_admin() || $fmanager->is_forum_moderator($fid) || !empty($_SESSION["privileged_topic_moderator"])): 
   $caption = text("FoundUsers");
   ?>
   <table class="aux_table search_member_area_table" style="width:100%">
   <tr>
   <td ><input type="text" name="user_to_search" autocomplete="off" value="" placeholder="<?php echo_html(text("SearchUser")); ?>" onkeypress="return search_on_enter(this.form, event)"></td>
   <td style="text-align: right; width: 1%;">
   <input type="button" name="search_user_button" class="standard_button member_search_button" value="<?php echo_html(text("Search")); ?>" onclick="search_user(this.form)">
   </td>
   </tr>
   </table>
   <?php endif; ?>

   <table class="list_group" style="width:100%">
   <tr>
   <th><?php echo_html($caption); ?></th>
   <th></th>
   <th class="selected_users_caption">...</th>
   </tr>
   <tr>
   <td>
   <select class="multiple_choice" multiple name="found_users[]" onDblClick="Forum.moveSelectedItems(this.form.elements['found_users[]'], this.form.elements['selected_users[]'])">
   </select>
   </td>
   <td>
   <input type="button" class="standard_button" value="&gt;&gt;" onclick="Forum.moveSelectedItems(this.form.elements['found_users[]'], this.form.elements['selected_users[]']); this.form.elements['user_to_search'].value = '';">
   <input type="button" class="standard_button" value="&lt;&lt;" onclick="Forum.moveSelectedItems(this.form.elements['selected_users[]'], this.form.elements['found_users[]'])">
   </td>
   <td>
   <select multiple class="multiple_choice" name="selected_users[]" onDblClick="Forum.moveSelectedItems(this.form.elements['selected_users[]'], this.form.elements['found_users[]'])">
   <?php
   foreach($blocked_users as $mid => $name): ?>
   <option value="<?php echo_html($mid); ?>"><?php echo_html($name); ?></option>
   <?php endforeach; ?>
   </select>
   </td>
   </tr>
   </table>

</form>

</div>

<?php
$treaders = "";
$freaders = "";

if(!empty($topic_data["is_private"]))
{
  $rcnt = empty($topic_data["participants"]) ? 0 : count($topic_data["participants"]);

  $treaders = escape_html(text("Members")) . " ($rcnt): ";

  if(!empty($topic_data["participants"]))
  {
    foreach($topic_data["participants"] as $pid => $pdata)
    {
      $appendix = "<span class='last_visit_info'>&nbsp;" . escape_html($pdata["last_visit"]) . "</span>";

      $online_status = "";
      if(empty($settings["hide_online_status"]) && !empty($pdata["online"]))
      {
        $online_status = "&nbsp;<span class='online_text'>✓</span>";
      }

      $treaders .= "<span class='user_name'><a href='view_profile.php?uid=$pid' >" . escape_html($pdata["user"]) . "</a>$online_status$appendix</span>, ";
    }

    $treaders = trim($treaders, ", ");
  }
}
else
{
  $rcnt = count($topic_readers);
  if(!empty($topic_readers["g_#anonyms#"]["count"])) $rcnt += ($topic_readers["g_#anonyms#"]["count"] - 1);

  $treaders = escape_html(text("ReadingTopic")) . " ($rcnt): ";

  foreach($topic_readers as $ouid => $uinfo)
  {
    $appendix = "";
    if($uinfo["time_ago"] != text("Now"))
      $appendix = "<span class='last_visit_info'>&nbsp;" . escape_html($uinfo["time_ago"]) . "</span>";

    if(!empty($uinfo["id"]))
      $treaders .= "<span class='user_name'><a href='view_profile.php?uid=$uinfo[id]'>" . escape_html($uinfo["name"]) . "</a>$appendix</span>, ";
    elseif(!empty($uinfo["bot"]))
      $treaders .= "<span class='user_name'><a class='bot_link' href='view_bot_profile.php?bot=" . xrawurlencode($uinfo["name"]) . "'>" . escape_html($uinfo["name"]) . "</a>$appendix</span>, ";
    elseif($ouid == "g_#anonyms#")
      $treaders .= "<span class='user_name'><i>" . escape_html($uinfo["name"]) . "</i>$appendix</span>, ";
    elseif($uinfo["name"] == "admin")
      $treaders .= "<span class='user_name'><a class='admin_link' href='view_guest_profile.php?guest=" . xrawurlencode($uinfo["name"]) . "'>" . escape_html(text("MasterAdministrator")) . "</a>$appendix</span>, ";
    else
      $treaders .= "<span class='user_name'><a class='guest_link' href='view_guest_profile.php?guest=" . xrawurlencode($uinfo["name"]) . "'>" . escape_html($uinfo["name"]) . "</a>$appendix</span>, ";
  }

  $treaders = trim($treaders, ", ");

  $rcnt = count($forum_readers);
  if(!empty($forum_readers["g_#anonyms#"]["count"])) $rcnt += ($forum_readers["g_#anonyms#"]["count"] - 1);

  $freaders = escape_html(text("ReadingForum")) . " ($rcnt): ";

  foreach($forum_readers as $ouid => $uinfo)
  {
    $appendix = "";
    if($uinfo["time_ago"] != text("Now"))
      $appendix = "<span class='last_visit_info'>&nbsp;" . escape_html($uinfo["time_ago"]) . "</span>";

    if(!empty($uinfo["id"]))
      $freaders .= "<span class='user_name'><a href='view_profile.php?uid=$uinfo[id]'>" . escape_html($uinfo["name"]) . "</a>$appendix</span>, ";
    elseif(!empty($uinfo["bot"]))
      $freaders .= "<span class='user_name'><a class='bot_link' href='view_bot_profile.php?bot=" . xrawurlencode($uinfo["name"]) . "'>" . escape_html($uinfo["name"]) . "</a>$appendix</span>, ";
    elseif($ouid == "g_#anonyms#")
      $freaders .= "<span class='user_name'><i>" . escape_html($uinfo["name"]) . "</i>$appendix</span>, ";
    elseif($uinfo["name"] == "admin")
      $freaders .= "<span class='user_name'><a class='admin_link' href='view_guest_profile.php?guest=" . xrawurlencode($uinfo["name"]) . "'>" . escape_html(text("MasterAdministrator")) . "</a>$appendix</span>, ";
    else
      $freaders .= "<span class='user_name'><a class='guest_link' href='view_guest_profile.php?guest=" . xrawurlencode($uinfo["name"]) . "'>" . escape_html($uinfo["name"]) . "</a>$appendix</span>, ";
  }

  $freaders = trim($freaders, ", ");
}

$tignorers = "";
$rcnt = count($topic_ignorers);
if($rcnt > 0)
{
  $tignorers = escape_html(text("IgnoringTopic")) . " ($rcnt): ";

  foreach($topic_ignorers as $iuid => $uinfo)
  {
      $online_status = "";
      if(empty($settings["hide_online_status"]) && !empty($uinfo["online"]))
      {
          $online_status = "&nbsp;<span class='online_text'>✓</span>";
      }
      
      $active_ignorer = "";
      if (empty($uinfo["auto_ignored"])) {
          $active_ignorer = "class='active_ignorer'";
      }

      $tignorers .= "<span class='user_name'><a $active_ignorer href='view_profile.php?uid=$iuid' >" . escape_html($uinfo["name"]) . "</a>$online_status</span>, ";
  }

  $tignorers = trim($tignorers, ", ");
}

$tblocked = "";
$rcnt = count($topic_blocked_users);
if($rcnt > 0)
{
  $tblocked = escape_html(text("BlockedInTopic")) . " ($rcnt): ";

  foreach($topic_blocked_users as $iuid => $uinfo)
  {
      $online_status = "";
      if(empty($settings["hide_online_status"]) && !empty($uinfo["online"]))
      {
          $online_status = "&nbsp;<span class='online_text'>✓</span>";
      }
      
      $active_ignorer = "";
      if (empty($uinfo["auto_ignored"])) {
          $active_ignorer = "class='active_ignorer'";
      }

      $tblocked .= "<span class='user_name'><a $active_ignorer href='view_profile.php?uid=$iuid' >" . escape_html($uinfo["name"]) . "</a>$online_status</span>, ";
  }

  $tblocked = trim($tblocked, ", ");
}
?>

<div class="online_users_area">

<?php
@include "topic_online_users_inc.php";
?>

</div>

</div>

<script>

function startup_action()
{
  <?php
  if(!empty($_SESSION["do_post"])):
  unset($_SESSION["do_post"]);
  ?>
  new_message('first_post_container', first_message, "<?php echo_js($tid, true); ?>", "<?php echo_js($topic_title, true); ?>", "<?php echo($topic_data["profiled_topic_final"]); ?>", <?php echo(!empty($forum_data["stringent_rules"]) ? 1 : 0); ?>);
  <?php endif; ?>

  <?php
  if(!empty($_SESSION["do_write"])):
  
  $container = "";
  if($_SESSION["do_write"] == "first_message") $container = "first_post_container";
  ?>
  new_message('<?php echo_js($container); ?>', <?php echo_js($_SESSION["do_write"]); ?>, "<?php echo_js($tid, true); ?>", "<?php echo_js($topic_title, true); ?>", "<?php echo($topic_data["profiled_topic_final"]); ?>", <?php echo(!empty($forum_data["stringent_rules"]) ? 1 : 0); ?>);
  <?php
  unset($_SESSION["do_write"]);
  endif;
  ?>

  <?php
  if(!empty($_SESSION["do_answer"])):
  ?>
  answer_to_author(<?php echo_js($_SESSION["do_answer"]); ?>, '<?php echo_js($_SESSION["answer_author"]); ?>', "<?php echo_js($tid, true); ?>", "<?php echo_js($topic_title, true); ?>", "<?php echo($topic_data["profiled_topic_final"]); ?>", <?php echo(!empty($forum_data["stringent_rules"]) ? 1 : 0); ?>);
  <?php
  unset($_SESSION["do_answer"]);
  unset($_SESSION["answer_author"]);
  endif;
  ?>

  <?php
  if(!empty($_SESSION["do_citate"])):
  ?>
  citate_post(<?php echo_js($_SESSION["do_citate"]); ?>, "<?php echo_js($tid, true); ?>", "<?php echo_js($topic_title, true); ?>", "<?php echo($topic_data["profiled_topic_final"]); ?>", <?php echo(!empty($forum_data["stringent_rules"]) ? 1 : 0); ?>);
  <?php
  unset($_SESSION["do_citate"]);
  endif;
  ?>

  restore_unposted_message();
}

<?php if(!reqvar_empty("download") && $fmanager->is_logged_in()): ?>
do_not_check_new = true;
<?php endif; ?>

</script>
