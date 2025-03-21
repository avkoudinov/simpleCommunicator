<script>
var selected_topics = {};

function hide_all_popups()
{
  Forum.hide_sys_bubblebox();

  var elms = document.getElementsByClassName("popup_topic_actions_menu");
  for(var i = 0; i < elms.length; i++)
  {
    elms[i].style.display = "none";
  }

  elms = document.getElementsByClassName("profiling_info");
  for(var i = 0; i < elms.length; i++)
  {
    elms[i].style.display = "none";
  }
}

function show_forum_selector(tid)
{
  hide_all_popups();

  var elm = document.getElementById("forum_selection_area");
  var target_area = document.getElementById("popup_container_" + tid);

  if(!elm || !target_area) return false;

  reset_forum_selector('forum_selector_move');

  target_area.appendChild(elm);
  elm.style.display = "block";

  elm = document.getElementById("forum_selector_move");
  if (elm) elm.focus();

  return false;
}

function hide_forum_selector()
{
  var elm = document.getElementById("forum_selection_area");
  if(!elm) return false;

  elm.style.display = "none";
  document.body.appendChild(elm);  
  
  elm = document.getElementById("forum_selection_list");
  if(!elm) return false;
  
  Forum.unselectAll(elm);

  return false;
}

function show_topic_actions_menu(tid)
{
  hide_all_popups();
  hide_forum_selector();

  var count = Forum.objectPropertiesCount(selected_topics);

  if(count == 0) return false;

  var elms = document.getElementsByClassName("selected_topics_count");
  for(var i = 0; i < elms.length; i++)
  {
    elms[i].innerHTML = count;
  }

  var elm = document.getElementById("popup_topic_actions_menu_" + tid);
  if(!elm) return false;

  elm.style.display = "block";

  return false;
}

function select_all()
{
  var th = document.getElementById("all_checkbox_selector");
  if(th) th.classList.add('selected_all_checkbox_selector');
  
  var elms = document.getElementsByClassName("checkbox_selector");
  for(var i = 0; i < elms.length; i++)
  {
    tid = elms[i].getAttribute("data-tid");
    if(!tid) continue;
    
    if(!elms[i].parentNode.classList.contains('selected_row'))
    {
      elms[i].parentNode.classList.add('selected_row');
      selected_topics[tid] = 1;
    }
  }
  
  var count = Forum.objectPropertiesCount(selected_topics);

  if(count == 0) return false;

  var elms = document.getElementsByClassName("selected_topics_count");
  for(var i = 0; i < elms.length; i++)
  {
    elms[i].innerHTML = count;
  }
  
  return false;
}

function unselect_all()
{
  var th = document.getElementById("all_checkbox_selector");
  if(th) th.classList.remove('selected_all_checkbox_selector');
  
  var elms = document.getElementsByClassName("checkbox_selector");
  for(var i = 0; i < elms.length; i++)
  {
    tid = elms[i].getAttribute("data-tid");
    if(!tid) continue;
    
    if(elms[i].parentNode.classList.contains('selected_row'))
    {
      elms[i].parentNode.classList.remove('selected_row');
      delete selected_topics[tid];
    }
  }
  
  hide_all_popups();
  
  return false;
}

function toggle_all_selection(th)
{
  hide_forum_selector();

  var selected = false;
  var tid = "";
  var first_tid = "";
  
  if(th.classList.contains('selected_all_checkbox_selector'))
  {
    th.classList.remove('selected_all_checkbox_selector');
  }
  else
  {
    th.classList.add('selected_all_checkbox_selector');
    selected = true;
  }
  
  var elms = document.getElementsByClassName("checkbox_selector");
  for(var i = 0; i < elms.length; i++)
  {
    tid = elms[i].getAttribute("data-tid");
    if(!tid) continue;
    
    if(!first_tid) first_tid = tid;
    
    if(selected)
    {
      elms[i].parentNode.classList.add('selected_row');
      selected_topics[tid] = 1;
    }
    else
    {
      elms[i].parentNode.classList.remove('selected_row');
      delete selected_topics[tid];
    }
  }
  
  if(first_tid) show_topic_actions_menu(first_tid);
}

function toggle_selection(td, tid)
{
  hide_forum_selector();

  if(td.parentNode.classList.contains('selected_row'))
  {
    td.parentNode.classList.remove('selected_row');
    delete selected_topics[tid];
  }
  else
  {
    td.parentNode.classList.add('selected_row');
    selected_topics[tid] = 1;
  }
}

var topic_choose_apply_func = function()
{
  var new_topic = "";

  var elm = document.getElementById("new_topic");
  if(elm) new_topic = elm.value;

  elm = document.getElementById("found_topics");
  if(!elm) return false;

  if(!elm.value && !new_topic)
  {
    var mbuttons = [
      {
        caption: msg_OK,
        handler: function() { Forum.hide_user_msgbox(); }
      }
    ];

    Forum.show_user_msgbox(msg_Error, "<?php echo_js(text("ErrNoTopicSelected")); ?>", 'icon-error.gif', mbuttons);

    return false;
  }

  return do_action({ topic_action: "merge_topics", target_topic: elm.value, new_topic: new_topic });
}

function select_target_topic()
{
  hide_all_popups();
  hide_forum_selector();

  if(Forum.isEmptyObject(selected_topics))
  {
    var mbuttons = [
      {
        caption: msg_OK,
        handler: function() { Forum.hide_user_msgbox(); }
      }
    ];

    Forum.show_user_msgbox(msg_Error, "<?php echo_js(text("ErrNoTopicSelected")); ?>", 'icon-error.gif', mbuttons);

    return false;
  }

  var buttons = [
    {
      caption: "<?php echo_js(text("Cancel")); ?>",
      handler: function() { Forum.hide_sys_lightbox(); }
    },
    {
      caption: "<?php echo_js(text("Apply")); ?>",
      addClass: "send_button",
      handler: function() { topic_choose_apply_func(); }
    }
  ];

  Forum.show_topic_selector("<?php echo_js(text("MergeTopics")); ?>", buttons, false, true, 870);

  return false;
}

function user_esc_handler()
{
  hide_all_popups();
  hide_forum_selector();
}

function confirm_delete_action(msg, params)
{
  hide_all_popups();

  var mbuttons = [
    {
      caption: msg_Yes,
      handler: function() {
        Forum.hide_user_msgbox();
        
        var elm = document.getElementById("sys_user_textarea");
        if(elm) params["comment"] = elm.value;
        
        do_action(params);
      }
    },
    {
      caption: msg_No,
      handler: function() {
        Forum.hide_user_msgbox();
      }
    }
  ];

  Forum.show_user_textarea(msg_Confirmation, msg, '', 'icon-question.gif', mbuttons, 170);

  return false;
}

function confirm_action(msg, params)
{
  if(no_confirmation_of_any_actions == 1 || (no_confirmation_of_uncritical_actions == 1 && params.uncritical)) 
  {
    Forum.hide_user_msgbox();
    do_action(params);
    return false;
  }

  hide_all_popups();
  hide_forum_selector();

  var mbuttons = [
    {
      caption: msg_Yes,
      handler: function() {
        Forum.hide_user_msgbox();
        do_action(params);
      }
    },
    {
      caption: msg_No,
      handler: function() {
        Forum.hide_user_msgbox();
      }
    }
  ];

  Forum.show_user_msgbox(msg_Confirmation, msg, 'icon-question.gif', mbuttons);

  return false;
}

var action_ajax = null;

function do_action(params)
{
  hide_all_popups();
  hide_forum_selector();

  Forum.show_sys_progress_indicator(true);

  if(!action_ajax)
  {
    action_ajax = new Forum.AJAX();

    action_ajax.timeout = TIMEOUT;

    action_ajax.beforestart = function() { break_check_new_messages(); };
    action_ajax.aftercomplete = function(error) { activate_check_new_messages(); };

    action_ajax.onload = function(text, xml)
    {
      try
      {
        var response = JSON.parse(text);

        Forum.handle_response_messages(response);

        if(response.success)
        {
          if(response.target_url) 
          {
            delay_redirect(response.target_url);
            return;
          }
          else if(response.do_reload) 
          {
            delay_reload();
            return;
          }

          if(response.convert_action_link) convert_action_link(response.convert_action_link, this.params);
        }
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

  action_ajax.params = params;

  for(var p in params)
  {
    if(!Object.prototype.hasOwnProperty.call(params, p)) continue;

    action_ajax.setPOST(p, params[p]);
  }

  var i = 0;
  for(var t in selected_topics)
  {
    if(!Object.prototype.hasOwnProperty.call(selected_topics, t)) continue;

    action_ajax.setPOST("topics[" + (i++) + "]", t);
  }

  action_ajax.setPOST('hash', get_protection_hash());
  action_ajax.setPOST('user_logged', user_logged);
  action_ajax.setPOST('trace_sql', trace_sql);
  action_ajax.setPOST('current_url', current_url);
  action_ajax.setPOST('fpage', fpage);
  action_ajax.setPOST('return_forum', '<?php echo_js($fid_for_url); ?>');

  action_ajax.request("ajax/process.php");

  return false;
}

function convert_action_link(target, params)
{
  var elm;

  if(target == "add_to_ignored")
  {
    elm = document.getElementById("ignore_action");
    if(elm)
    {
      params.topic_action = "add_to_ignored";
      elm.innerHTML = "<?php echo_js(text("AddToIgnoredForums")); ?>";
      elm.onclick = function (event) { return confirm_action("<?php echo_js(text("MsgConfirmForumIgnore"), true); ?>", params); }
    }
  }

  if(target == "remove_from_ignored")
  {
    elm = document.getElementById("ignore_action");
    if(elm)
    {
      params.topic_action = "remove_from_ignored";
      elm.innerHTML = "<?php echo_js(text("RemoveFromIgnoredForums")); ?>";
      elm.onclick = function (event) { return do_action(params); }
    }
  }
}
</script>

<?php
$fpage_appendix = "";
if(!reqvar_empty("fpage")) $fpage_appendix = "&fpage=" . reqvar("fpage");
?>

<!-- BEGIN: header2 -->

<div class="header2">

<div id="actions" class="actions" onclick="toggle_actions()"><?php echo_html(text("Actions")); ?> ...</div>

<div id="actions_area" class="actions_area">

<?php
$display = "style='display:none'";
$digest_url = "search_topic.php?news_digest=1&do_search=1";

if(val_or_empty($fid_for_url) == "private")
{
  $class = "new_private";
  $digest_url .= "&fid=" . $fid_for_url;
  if(!empty($forum_data["topics_with_new_count"])) $display = "";
}
elseif(!empty($fid_for_url))
{
  $class = "forum_with_new_indicator";
  $digest_url .= "&fid=" . $fid_for_url;
  if(!empty($forum_data["topics_with_new_count"])) $display = "";
}
?>
<span class="<?php echo($class); ?>" <?php echo($display); ?> data-fid="<?php echo_html(val_or_empty($fid_for_url)); ?>"><a href="<?php echo($digest_url); ?>" ><?php echo_html(text("Digest")); ?></a> <br></span>

<?php if(!$is_private): ?>
<?php if(empty($forum_data["in_ignored"])): ?>
<a id="ignore_action" href="<?php echo($base_url); ?>" onclick='return confirm_action("<?php echo_js(text("MsgConfirmForumIgnore"), true); ?>", { forum_user_action: "add_to_ignored", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("AddToIgnoredForums")); ?></a> <br>
<?php else: ?>
<a id="ignore_action" href="<?php echo($base_url); ?>" onclick='return do_action({ forum_user_action: "remove_from_ignored", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("RemoveFromIgnoredForums")); ?></a> <br>
<?php endif; ?>
<?php endif; ?>

<a href="<?php echo($base_url . $fpage_appendix); ?>" onclick='return confirm_action("<?php echo_js(text("MsgConfirmMarkRead"), true); ?>", { mark_read_action: "mark_forum_read", forum: "<?php echo_js($fid); ?>", uncritical: 1 });'><?php echo_html(text("MarkRead")); ?></a><br>

<?php if(!empty($_SESSION["has_forums_with_user_guest_posting"]) && $fmanager->is_logged_in() && !$fmanager->is_master_admin() && !empty($forum_data["user_posting_as_guest"])): ?>
<?php if(empty($_SESSION["guest_posting_mode"])): ?>
<a href="<?php echo($base_url); ?>&guest_posting_on=1<?php echo($fpage_appendix); ?>&hash=<?php echo_html($_SESSION["hash"]); ?>" onclick="check_actual_hash(this)" class="moderator_link"><?php echo_html(text("GuestPostingModeOn")); ?></a><br>
<?php else: ?>
<a href="<?php echo($base_url); ?>&guest_posting_off=1<?php echo($fpage_appendix); ?>&hash=<?php echo_html($_SESSION["hash"]); ?>" onclick="check_actual_hash(this)" class="moderator_link"><?php echo_html(text("GuestPostingModeOff")); ?></a><br>
<?php endif; ?>
<?php endif; ?>


<?php if(($fmanager->is_admin() || $fmanager->is_forum_moderator($fid)) || $is_private): ?>

<?php 
if($is_private)
  echo_html(text("Author"));
else
  echo_html($fmanager->is_admin() ? text("Administrator") : text("Moderator")); 
?>:<br>

<?php if(empty($_SESSION["show_deleted"])): ?>
<a href="<?php echo($base_url); ?>&show_deleted=1<?php echo($fpage_appendix); ?>&hash=<?php echo_html($_SESSION["hash"]); ?>" onclick="check_actual_hash(this)" class="moderator_link"><?php echo_html(text("DisplayDeleted")); ?></a><br>
<?php else: ?>
<a href="<?php echo($base_url); ?>&hide_deleted=1<?php echo($fpage_appendix); ?>&hash=<?php echo_html($_SESSION["hash"]); ?>" onclick="check_actual_hash(this)" class="moderator_link"><?php echo_html(text("HideDeleted")); ?></a><br>
<?php endif; ?>

<?php if(!$is_private && ($fmanager->is_admin() || $fmanager->is_forum_moderator($fid))): 
$start_date = date(text("DateFormat"), xstrtotime("-1 day"));
?>
<a href="search_topic.php?start_date=<?php echo_html($start_date); ?>&author_mode=wrote_post&post_list=1&post_sort=desc&forums[]=<?php echo_html($fid); ?>" class="moderator_link"><?php echo_html(text("RecentMessageModeration")); ?></a>
<?php endif; ?>

<?php endif; ?>


</div>

</div>

<!-- END: header2 -->


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

<?php
$not_preferred = "";
if(!empty($_SESSION["ignored_forums"][$fid]) && !$is_private) $not_preferred = "not_preferred";
?>
/ <a href="<?php echo("forum.php?fid=" . $fid_for_url); ?>" class="<?php echo($not_preferred); ?>"><?php echo_html($forum_title); ?></a>

<?php
$display = "style='display:none'";
if(!empty($forum_data["topics_with_new_count"])) $display = "";
?>
<span class="new forum_with_new_indicator <?php echo($not_preferred); ?>" data-fid="<?php echo_html($fid_for_url); ?>" <?php echo($display); ?>>[<a href="<?php echo("new_messages.php?fid=" . $fid_for_url); ?>"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($forum_data["topics_with_new_count"]); ?></span></a>]</span>

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

/ <?php echo_html(text("Topics")); ?>:&nbsp;<span class='count_number'><?php echo_html(format_number(val_or_empty($pagination_info["total_count"]))); ?></span>,

<?php echo(build_page_info($pagination_info, text("pages"))); ?>

<?php if(!empty($pagination_info["ignored_count"])): ?>
<?php if($fmanager->is_logged_in() || $fmanager->get_user_name() != ""): ?>
<a class="not_preferred" href="search.php?do_search=1&author=<?php echo(xrawurlencode($fmanager->get_user_name())); ?>&author_mode=ignoring&forums<?php echo(xrawurlencode("[]")); ?>=<?php echo($fid_for_url); ?>"><?php echo_html(text("ignored")); ?>: <?php echo_html(format_number($pagination_info["ignored_count"])); ?></a>
<?php else: ?>
<span class="not_preferred"><?php echo_html(text("ignored")); ?>: <?php echo_html(format_number($pagination_info["ignored_count"])); ?></span>
<?php endif; ?>
<?php endif; ?>

</div>

<div class="forum_bar forum_bar_breakable">

<?php if($pagination_info["page_count"] > 1): ?>
<div class="navigator_bar"><?php echo(build_page_navigator($base_url . "&fpage=$", $pagination_info)); ?></div>
<?php endif; ?>

<div class="forum_action_bar">
<?php if($may_write): ?>
<?php
$new_topic_disabled = "";
$new_topic_title = escape_html(text("NewTopic"));
if($topic_time_to_next > 0) 
{
  $new_topic_disabled = "disabled='disabled'";
  $new_topic_title .= ": <span style='color:red'>" . format_duration($topic_time_to_next) . "</span>";
}
?>
<button class="standard_button" onclick="delay_redirect('new_topic.php?fid=<?php echo_html($fid_for_url); ?>')" <?php echo($new_topic_disabled); ?>><?php echo($new_topic_title); ?></button>
<?php endif; ?>
</div>

<div class="clear_both">
</div>

</div>

<!-- END: forum_bar -->

<?php
if(!empty($forum_data["moderators"]))
{
  $moderators = "";
  foreach($forum_data["moderators"] as $mid => $minfo)
  {
    $moderators .= "<a href='view_profile.php?uid=$mid'>" . escape_html($minfo["name"]) . "</a>";

    if(empty($settings["hide_online_status"]) && !empty($minfo["online"]))
    {
      $moderators .= "&nbsp;<span class='online_text'>✓</span>";
    }

    $moderators .= ", ";
  }

  $moderators = trim($moderators, ", ");
  echo "<div class='header2'>" . escape_html(text("Moderators")) . ": " . $moderators . "</div>";
}
?>

<table class="topic_table">
<tr>
<th id="all_checkbox_selector" class="all_checkbox_selector" onclick="toggle_all_selection(this)"><div>&nbsp;</div></th>

<th><?php echo_html(text("Topic")); ?></th>
</tr>

<?php if(count($topic_list) == 0): ?>

<tr>
<td colspan="2" class="table_message"><?php echo_html(text("NoTopics")); ?></td>
</tr>

<?php else: ?>

<?php
foreach($topic_list as $tid => $tinfo):
$deleted = "";
if(!empty($tinfo["deleted"])) $deleted = "deleted_row";

if(!empty($_SESSION["topic_moderator"][$tid])) $deleted .= " moderated_topic_row";
?>

<tr class="<?php echo_html($deleted); ?>">
<td class="checkbox_selector" data-tid="<?php echo_html($tid); ?>" onclick="toggle_selection(this, '<?php echo_html($tid); ?>'); show_topic_actions_menu('<?php echo_html($tid); ?>')"></td>
<td>

  <div style="position:relative;" id="popup_container_<?php echo_html($tid); ?>">
  <div class="popup_topic_actions_menu" id="popup_topic_actions_menu_<?php echo_html($tid); ?>">

      <div style="position: absolute;right:2px;top:2px;cursor:pointer" onclick="hide_all_popups();"><img src="<?php echo($view_path); ?>images/cross.png" alt="<?php echo_html(text("Close")); ?>"></div>

      <span style="font-weight: bold"><?php echo_html(text("MsgTopicsSelected")); ?>: <span class="selected_topics_count">0</span></span>

      <a href="<?php echo($base_url . $fpage_appendix); ?>" onclick='return select_all()'><?php echo_html(text("SelectAll")); ?></a>
      <a href="<?php echo($base_url . $fpage_appendix); ?>" onclick='return unselect_all()'><?php echo_html(text("ResetSelection")); ?></a>

      <a href="<?php echo($base_url . $fpage_appendix); ?>" onclick='return confirm_action("<?php echo_js(text("MsgConfirmTopicsIgnore"), true); ?>", { topic_action: "bulk_add_to_ignored" })'><?php echo_html(text("AddTopicsToIgnoredTopics")); ?></a>
      <a href="<?php echo($base_url . $fpage_appendix); ?>" onclick='return do_action({ topic_action: "bulk_remove_from_ignored" })'><?php echo_html(text("RemoveTopicsFromIgnoredTopics")); ?></a>
      
      <a href="<?php echo($base_url . $fpage_appendix); ?>" onclick='return do_action({ mark_read_action: "mark_forum_read", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("MarkRead")); ?></a>

      <?php if(($fmanager->is_admin() || $fmanager->is_forum_moderator($fid)) && !$is_private): ?>

      <div class="mod_actions_separator"><?php echo_html($fmanager->is_admin() ? text("Administrator") : text("Moderator")); ?>:</div>

      <a href="<?php echo($base_url . $fpage_appendix); ?>" class="moderator_link" onclick='return confirm_delete_action("<?php echo_js(text("MsgConfirmTopicsDelete"), true); ?>", { topic_action: "delete_topics" });'><?php echo_html(text("DeleteTopics")); ?></a>
      <?php if(!empty($_SESSION["show_deleted"])): ?>
      <a href="<?php echo($base_url . $fpage_appendix); ?>" class="moderator_link" onclick='return do_action({ topic_action: "restore_topics", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("RestoreTopics")); ?></a>
      <?php endif; ?>
      <a href="<?php echo($base_url . $fpage_appendix); ?>" class="moderator_link" onclick='return confirm_action("<?php echo_js(text("MsgConfirmTopicsClose"), true); ?>", { topic_action: "close_topics" });'><?php echo_html(text("CloseTopics")); ?></a>
      <a href="<?php echo($base_url . $fpage_appendix); ?>" class="moderator_link" onclick='return do_action({ topic_action: "open_topics", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("OpenTopics")); ?></a>

      <a href="<?php echo($base_url . $fpage_appendix); ?>" class="moderator_link" onclick="return show_forum_selector('<?php echo_html($tid); ?>')"><?php echo_html(text("MoveTopics")); ?></a>

      <a href="<?php echo($base_url . $fpage_appendix); ?>" class="moderator_link" onclick="return select_target_topic()"><?php echo_html(text("MergeTopics")); ?></a>
      <?php endif; ?>

  </div>
  </div>

<div class="smart_break">

<?php
$topic_status = "";
if(!empty($tinfo["pinned"]) && empty($tinfo["is_poll"]) && empty($tinfo["profiled_topic"])) 
  $topic_status .= text("Important") . ": ";
if(!empty($tinfo["profiled_topic"]) && $tinfo["profiled_topic"] == 1)
  $topic_status .= text("Dedicated") . ": ";
if(!empty($tinfo["profiled_topic"]) && $tinfo["profiled_topic"] == 2)
  $topic_status .= text("Blog") . ": ";
if(!empty($tinfo["is_poll"]))
  $topic_status .= text("Poll") . ": ";
if(!empty($tinfo["publish_delay"]))
  $topic_status .= text("NotPublished") . ": ";

if(!empty($topic_status)) 
  echo('<span class="topic_status">' . escape_html($topic_status) . '</span>');
?>
      
<?php
$topic_base_url = "topic.php?fid=" . $fid_for_url . $fpage_appendix . "&tid=" . $tid;
$not_preferred = "";
if(!empty($tinfo["topic_ignored"])) $not_preferred = "not_preferred";
?>

<a class="<?php echo($not_preferred); ?>" href="<?php echo($topic_base_url); ?>"><?php echo_html(postprocess_message($tinfo["name"])); ?></a>

<?php if(!empty($tinfo["hot"])): ?><span class="hot_topic" title="<?php echo_html(text("HotTopic")); ?>">&nbsp;</span><?php endif; ?>

<?php if(!empty($tinfo["topic_in_favourites"])): ?><span class="topic_in_favourites" title="<?php echo_html(text("TopicIsInFavourites")); ?>">&nbsp;</span><?php endif; ?>

<?php if(!empty($tinfo["is_blocked"])): ?><span class="blocked_in_topic" title="<?php echo_html(text("Blocked")); ?>">&nbsp;</span><?php endif; ?>

<?php echo(build_post_pagination($topic_base_url, $tinfo, $not_preferred)); ?>

<?php
$topic_ignored = "";
$display = "style='display:none'";
if(!empty($tinfo["new_messages_count"])) $display = "";
if(!empty($tinfo["new_marker_ignored"])) $topic_ignored = "topic_ignored";

$never_visited_topic = "";
if(!empty($tinfo["never_visited_topic"])) $never_visited_topic = "never_visited_topic";
?>
<span class="new <?php echo($never_visited_topic); ?> new_messages_indicator <?php echo($topic_ignored); ?>" data-tid="<?php echo_html($tid); ?>" <?php echo($display); ?>>[<a href="<?php echo($topic_base_url); ?>&gotonew=1" rel="nofollow"><?php echo_html(text("new")); ?>:<span class='new_messages_count'><?php echo($tinfo["new_messages_count"]); ?></span></a>]</span>

<?php if(!empty($tinfo["closed"])): ?>
<span class="closed">[<?php echo_html(text("closed")); ?>]</span>
<?php endif; ?>


</div>

<div class="forum_info">
<?php echo_html(text("Author")); ?>:

  <?php if(empty($tinfo["user_id"])): ?>

  <?php
  $online_status = "";
  if(empty($settings["hide_online_status"]) && empty($tinfo["author_ignored"]) && !empty($online_users["g_" . $tinfo["author"]]))
  {
    $online_status = "&nbsp;<span class='online_text'>✓</span>";
  }

  if(empty($tinfo["author_ignored"]))
  {
    if($tinfo["author"] == "admin")
      $author_string = "<a class='admin_link' href='view_guest_profile.php?guest=" . xrawurlencode($tinfo["author"]) . "'>" . escape_html(text("MasterAdministrator")) . "</a>";
    else  
      $author_string = "<a class='guest_link' href='view_guest_profile.php?guest=" . xrawurlencode($tinfo["author"]) . "'>" . escape_html($tinfo["author"]) . "</a>";
  }
  else
  {
    $author_string = escape_html($tinfo["author"]);
  }
  ?>
  <span class="number <?php if(!empty($tinfo["author_ignored"])) echo("not_preferred"); ?>"><?php echo($author_string); ?><?php echo($online_status); ?></span>
  <?php else: ?>
  
  <?php
  $online_status = "";
  if(empty($settings["hide_online_status"]) && !empty($tinfo["online"]))
  {
    $online_status = "&nbsp;<span class='online_text'>✓</span>";
  }
  ?>
  <a href="view_profile.php?uid=<?php echo_html($tinfo["user_id"]); ?>"><?php echo_html($tinfo["author"]); ?></a><?php echo($online_status); ?>
  <?php endif; ?>

<?php if($is_private): ?>
  <br><?php echo_html(text("Members")); ?>:
  <?php
  if(!empty($tinfo["participants"])):
  $count = count($tinfo["participants"]);
  $i = 1;
  foreach($tinfo["participants"] as $pid => $pinfo):
  if($i < $count) $comma = ", ";
  else            $comma = "";

  if($count > 6 && $i == 5) {
     echo "<span class='user_list_expander' onclick='this.nextSibling.style.display = \"inline\"; this.style.display = \"none\";'>...</span>";
     echo "<span class='hidden_user_list'>";
  }

  $i++;

  $online_status = "";
  if(empty($settings["hide_online_status"]) && !empty($pinfo["online"]))
  {
    $online_status = "&nbsp;<span class='online_text'>✓</span>";
  }
  ?>

  <a href="view_profile.php?uid=<?php echo_html($pid); ?>"><?php echo_html($pinfo["name"]); ?></a><?php echo($online_status); ?><?php echo($comma); ?>

  <?php
  endforeach;

  if($count > 6) {
     echo "</span>";
  }

  endif;
  ?>
<?php endif; ?>

  <?php
  if(!empty($tinfo["moderators"]))
  {
    $moderators = "";
    foreach($tinfo["moderators"] as $mid => $minfo)
    {
      $online_status = "";
      if(empty($settings["hide_online_status"]) && !empty($minfo["online"]))
      {
        $online_status = "&nbsp;<span class='online_text'>✓</span>";
      }
      $moderators .= "<a href='view_profile.php?uid=$mid'>" . escape_html($minfo["name"]) . "</a>$online_status, ";
    }

    $moderators = trim($moderators, ", ");

    echo "<br>" . escape_html(text("Moderators")) . ": " . $moderators;
  }
  ?>

<br> <?php echo_html(text("LastAuthor")); ?>:
  <?php if(empty($tinfo["last_author_id"])): ?>

  <?php
  $online_status = "";
  if(empty($settings["hide_online_status"]) && empty($tinfo["last_author_ignored"]) && !empty($online_users["g_" . $tinfo["last_author"]]))
  {
    $online_status = "&nbsp;<span class='online_text'>✓</span>";
  }
  
  if(empty($tinfo["last_author_ignored"]))
  {
    if($tinfo["last_author"] == "admin")
      $author_string = "<a class='admin_link' href='view_guest_profile.php?guest=" . xrawurlencode($tinfo["last_author"]) . "'>" . escape_html(text("MasterAdministrator")) . "</a>";
    else  
      $author_string = "<a class='guest_link' href='view_guest_profile.php?guest=" . xrawurlencode($tinfo["last_author"]) . "'>" . escape_html($tinfo["last_author"]) . "</a>";
  }
  else
  {
    $author_string = escape_html($tinfo["last_author"]);
  }
  ?>
  <span class="number <?php if(!empty($tinfo["last_author_ignored"])) echo("not_preferred"); ?>"><?php echo($author_string); ?><?php echo($online_status); ?></span>
  <?php else: ?>

  <?php
  $online_status = "";
  if(empty($settings["hide_online_status"]) && !empty($tinfo["last_author_online"]))
  {
    $online_status = "&nbsp;<span class='online_text'>✓</span>";
  }
  ?>
  <a href="view_profile.php?uid=<?php echo_html($tinfo["last_author_id"]); ?>"><?php echo_html($tinfo["last_author"]); ?></a><?php echo($online_status); ?>
  <?php endif; ?>
<br> <?php echo_html(text("LastMessage")); ?>: <span class="number"><?php echo_html($tinfo["last_message_date"]); ?></span>

<br> <?php echo_html(text("Messages")); ?>: <span class="number"><?php echo_html(format_number($tinfo["post_count"])); ?></span>
<br> <?php echo_html(text("Views")); ?><?php if(!empty($tinfo["bot_hits_count"])): ?> / <?php echo_html(text("Bots")); ?><?php endif; ?>:  <span class="number"><?php echo_html(format_number($tinfo["hits_count"])); ?></span><?php if(!empty($tinfo["bot_hits_count"])): ?> / <span class="number"><?php echo_html(format_number($tinfo["bot_hits_count"])); ?></span><?php endif; ?>

  <div class="navigation_arrows_right">
  <div class="scroll_up" onclick="window.scrollTo(0, 0);"></div>
  <div class="scroll_down" onclick="window.scrollTo(0, 1000000);"></div>
  </div>

</div>
</td>
</tr>

<?php endforeach; ?>

<?php endif; ?>

</table>

<?php if(count($topic_list) > 0): ?>

<!-- BEGIN: forum_bar -->

<div class="forum_bar forum_bar_breakable">

<?php if($pagination_info["page_count"] > 1): ?>
<div class="navigator_bar"><?php echo(build_page_navigator($base_url . "&fpage=$", $pagination_info)); ?></div>
<?php endif; ?>

<div class="forum_action_bar">
<?php if($may_write): ?>
<button class="standard_button" onclick="delay_redirect('new_topic.php?fid=<?php echo_html($fid_for_url); ?>')" <?php echo($new_topic_disabled); ?>><?php echo($new_topic_title); ?></button>
<?php endif; ?>
</div>

<div class="clear_both">
</div>

</div>

<!-- END: forum_bar -->

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

<?php
$not_preferred = "";
if(!empty($_SESSION["ignored_forums"][$fid]) && !$is_private) $not_preferred = "not_preferred";
?>
/ <a href="<?php echo("forum.php?fid=" . $fid_for_url); ?>" class="<?php echo($not_preferred); ?>"><?php echo_html($forum_title); ?></a>

<?php
$display = "style='display:none'";
if(!empty($forum_data["topics_with_new_count"])) $display = "";
?>
<span class="new forum_with_new_indicator <?php echo($not_preferred); ?>" data-fid="<?php echo_html($fid_for_url); ?>" <?php echo($display); ?>>[<a href="<?php echo("new_messages.php?fid=" . $fid_for_url); ?>"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($forum_data["topics_with_new_count"]); ?></span></a>]</span>

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

/ <?php echo_html(text("Topics")); ?>:&nbsp;<span class='count_number'><?php echo_html(format_number(val_or_empty($pagination_info["total_count"]))); ?></span>,

<?php echo(build_page_info($pagination_info, text("pages"))); ?>

<?php if(!empty($pagination_info["ignored_count"])): ?>
<?php if($fmanager->is_logged_in() || $fmanager->get_user_name() != ""): ?>
<a class="not_preferred" href="search.php?do_search=1&author=<?php echo(xrawurlencode($fmanager->get_user_name())); ?>&author_mode=ignoring&forums<?php echo(xrawurlencode("[]")); ?>=<?php echo($fid_for_url); ?>"><?php echo_html(text("ignored")); ?>: <?php echo_html(format_number($pagination_info["ignored_count"])); ?></a>
<?php else: ?>
<span class="not_preferred"><?php echo_html(text("ignored")); ?>: <?php echo_html(format_number($pagination_info["ignored_count"])); ?></span>
<?php endif; ?>
<?php endif; ?>

</div>

<!-- END: forum_bar -->

<?php endif; ?>

<?php
require_once "topic_lookup_inc.php";
?>

<?php
$freaders = "";

if(empty($is_private))
{
  $rcnt = count($forum_readers);
  if(!empty($forum_readers["g_#anonyms#"]["count"])) $rcnt += ($forum_readers["g_#anonyms#"]["count"] - 1);

  $bcnt = 0;
  foreach($forum_readers as $ouid => $uinfo)
  {
    if(!empty($uinfo["bot"])) $bcnt++;
  }
  if (!empty($rcnt)) $rcnt = ($rcnt - $bcnt);
  
  if (!empty($bcnt)) $rcnt .= "/" . $bcnt;

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
      $freaders .= "<span class='user_name'><i><a class='guest_link' href='view_anonym_activity.php'>" . escape_html($uinfo["name"]) . "</a></i>$appendix</span>, ";
    elseif($uinfo["name"] == "admin")
      $freaders .= "<span class='user_name'><a class='admin_link' href='view_guest_profile.php?guest=" . xrawurlencode($uinfo["name"]) . "'>" . escape_html(text("MasterAdministrator")) . "</a>$appendix</span>, ";
    else
      $freaders .= "<span class='user_name'><a class='guest_link' href='view_guest_profile.php?guest=" . xrawurlencode($uinfo["name"]) . "'>" . escape_html($uinfo["name"]) . "</a>$appendix</span>, ";
  }

  $freaders = trim($freaders, ", ");
}
?>


<?php if(empty($settings["hide_online_status"]) && empty($_SESSION["skin_properties"][$skin]["no_online_users"]) && empty($is_private)): ?>

<div class="header2 forum_readers">
<?php echo($freaders); ?>
</div>

<?php endif; ?>

<?php
@include "online_users_inc.php";
?>

<div id="forum_selection_area" class="forum_selection_area">
  <div style="position: absolute;right:2px;top:4px;cursor:pointer" onclick="hide_forum_selector()"><img src="<?php echo($view_path); ?>images/cross.png" alt="<?php echo_html(text("Close")); ?>"></div>

&nbsp;&nbsp;<?php echo_html(text("Forum")); ?>:
  <div class="select_container">
      <input type="text" class="search_field" id="forum_selector_move" autocomplete="off" placeholder="<?php echo_html(text("FindForum")); ?>" onkeypress="return forum_move_handle_enter(this.id, event, { topic_action: 'move_topics', forum: '<?php echo_js($fid); ?>' })" onkeyup="return filter_entries(this, event)" onfocus="reset_forum_selector(this.id);">

      <select id="forum_selector_move_lookup" size="15"
         onclick="if(!mustAdjustMultiSelect()) { lookup_move_to_forum('forum_selector_move', { topic_action: 'move_topics', forum: '<?php echo_js($fid); ?>' }); }" 
         onchange="if(mustAdjustMultiSelect()) { lookup_move_to_forum_if_active('forum_selector_move', { topic_action: 'move_topics', forum: '<?php echo_js($fid); ?>' }); }" 

         onkeypress="return forum_move_handle_enter('forum_selector_move', event, { topic_action: 'move_topics', forum: '<?php echo_js($fid); ?>' })"
      >

      <?php foreach($forum_list as $sfid => $fdata): 
         if($sfid == $fid) continue;
      ?>
      <option value="<?php echo_html($sfid); ?>"><?php echo_html($fdata["name"]); ?></option>
      <?php endforeach; ?>
      </select>
  </div>

</div>
