<script>
var selected_forums = {};

function confirm_action(msg, fname, params)
{
  if(no_confirmation_of_any_actions == 1 || (no_confirmation_of_uncritical_actions == 1 && params.uncritical)) 
  {
    Forum.hide_user_msgbox();
    do_action(params);
    return false;
  }

  hide_all_popups();

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
      handler: function() { Forum.hide_user_msgbox(); }
    }
  ];

  var msg = msg.replace(/%s/, fname);

  Forum.show_user_msgbox(msg_Confirmation, msg, 'icon-question.gif', mbuttons);

  return false;
}

function hide_all_popups()
{
  Forum.hide_sys_bubblebox();

  var elms = document.getElementsByClassName("popup_forum_actions_menu");
  for(var i = 0; i < elms.length; i++)
  {
    elms[i].style.display = "none";
  }
}

function show_forum_actions_menu(fid)
{
  hide_all_popups();

  var count = Forum.objectPropertiesCount(selected_forums);

  if(count == 0) return false;

  var elms = document.getElementsByClassName("selected_forums_count");
  for(var i = 0; i < elms.length; i++)
  {
    elms[i].innerHTML = count;
  }

  var elm = document.getElementById("popup_forum_actions_menu_" + fid);
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
    fid = elms[i].getAttribute("data-fid");
    if(!fid) continue;
    
    if(!elms[i].parentNode.classList.contains('selected_row'))
    {
      elms[i].parentNode.classList.add('selected_row');
      selected_forums[fid] = 1;
    }
  }
  
  var count = Forum.objectPropertiesCount(selected_forums);

  if(count == 0) return false;

  var elms = document.getElementsByClassName("selected_forums_count");
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
    fid = elms[i].getAttribute("data-fid");
    if(!fid) continue;
    
    if(elms[i].parentNode.classList.contains('selected_row'))
    {
      elms[i].parentNode.classList.remove('selected_row');
      delete selected_forums[fid];
    }
  }
  
  hide_all_popups();
  
  return false;
}

function toggle_all_selection(th)
{
  var selected = false;
  var fid = "";
  var first_fid = "";
  
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
    fid = elms[i].getAttribute("data-fid");
    if(!fid) continue;
    
    if(!first_fid) first_fid = fid;
    
    if(selected)
    {
      elms[i].parentNode.classList.add('selected_row');
      selected_forums[fid] = 1;
    }
    else
    {
      elms[i].parentNode.classList.remove('selected_row');
      delete selected_forums[fid];
    }
  }
  
  if(first_fid) show_forum_actions_menu(first_fid);
}

function toggle_selection(td, fid)
{
  if(td.parentNode.classList.contains('selected_row'))
  {
    td.parentNode.classList.remove('selected_row');
    delete selected_forums[fid];
  }
  else
  {
    td.parentNode.classList.add('selected_row');
    selected_forums[fid] = 1;
  }
}


var action_ajax = null;

function do_action(params)
{
  hide_all_popups();

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

    action_ajax.onerror = function(error, url, info)
    {
      Forum.show_sys_progress_indicator(false);

      Forum.handle_ajax_error(this, error, url, info);
    };
  }

  action_ajax.abort();
  action_ajax.resetParams();

  for(var p in params)
  {
    if(!Object.prototype.hasOwnProperty.call(params, p)) continue;

    action_ajax.setPOST(p, params[p]);
  }

  var i = 0;
  for(var f in selected_forums)
  {
    if(!Object.prototype.hasOwnProperty.call(selected_forums, f)) continue;

    action_ajax.setPOST("forums[" + (i++) + "]", f);
  }

  action_ajax.setPOST('hash', get_protection_hash());
  action_ajax.setPOST('user_logged', user_logged);
  action_ajax.setPOST('trace_sql', trace_sql);

  action_ajax.request("ajax/process.php");

  return false;
}
</script>

<!-- BEGIN: header3 -->

<div class="header3">

<div class="left_action_panel">
<?php if($fmanager->is_admin()): ?>

<?php if(empty($_SESSION["show_deleted"])): ?>
<a href="forums.php?show_deleted=1&hash=<?php echo_html($_SESSION["hash"]); ?>" onclick="check_actual_hash(this)" class="moderator_link"><?php echo_html(text("DisplayDeleted")); ?></a>
<?php else: ?>
<a href="forums.php?hide_deleted=1&hash=<?php echo_html($_SESSION["hash"]); ?>" onclick="check_actual_hash(this)" class="moderator_link"><?php echo_html(text("HideDeleted")); ?></a>
<?php endif; ?>

<?php endif; ?>

</div>

<div class="right_action_panel">
<a href="forums.php" onclick='return confirm_action("<?php echo_js(text("MsgConfirmMarkRead"), true); ?>", "", { mark_read_action: "mark_forums_read", uncritical: 1 })'><?php echo_html(text("MarkRead")); ?></a>
</div>

<div class="clear_both">
</div>


</div>

<!-- END: header3 -->

<div class="content_area">

<!-- BEGIN: forum_bar -->

<div class="forum_bar">

<div class="forum_name_bar" style="float:left"><a href="forums.php"><?php echo_html(text("Forums")); ?></a>

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

</div>

<div class="forum_action_bar">
<table>
<tr>
<td>
<?php
$forum_selector_id = 1;
@include "forum_selector_inc.php";
?>
</td>
<?php if($fmanager->is_admin()): ?>
<td>
<input type="button" class="standard_button" value="<?php echo_html(text("CreateForum")); ?>" onclick="delay_redirect('edit_forum.php')">
<input type="button" class="standard_button" value="<?php echo_html(text("ForumGroups")); ?>" onclick="delay_redirect('forum_groups.php')">
</td>
<?php endif; ?>
</tr>
</table>
</div>

<div class="clear_both">
</div>

</div>

<!-- END: forum_bar -->

<table class="topic_table">
<tr>
<th id="all_checkbox_selector" class="all_checkbox_selector" onclick="toggle_all_selection(this)"><div>&nbsp;</div></th>
<th class="topic_name_col"><?php echo_html(text("Name")); ?></th>
<th class="author_col"><?php echo_html(text("LastAuthor")); ?></th>
<th class="date_col"><?php echo_html(text("LastMessage")); ?></th>
<th class="number_col"><?php echo_html(text("Topics")); ?></th>

<?php if($fmanager->is_admin()): ?>
<th class="admin_actions"><?php echo_html(text("Administrator")); ?></th>
<?php endif; ?>

</tr>

<?php 
if($fmanager->is_admin()) $colspan = 6;
else                      $colspan = 5; 
?>

<?php if(count($groupped_forum_list) == 0): ?>

<tr>
<td colspan="<?php echo($colspan); ?>" class="table_message"><?php echo_html(text("NoForums")); ?></td>
</tr>

<?php else: ?>

<?php
$current_group = "";

foreach($groupped_forum_list as $fid => $finfo):
if(!empty($_SESSION["hide_ignored"]) && !empty($finfo["in_ignored"]) &&
   !$fmanager->is_forum_moderator($fid)) continue;
   
$deleted = "";
if(!empty($finfo["deleted"])) $deleted = "deleted_row";
?>

<?php
if (!empty($_SESSION["has_forum_groups"]) && $current_group != $finfo["forum_group_name"]):
$current_group = $finfo["forum_group_name"];
?>

<tr>
<th class="subheader" style="cursor: pointer" colspan="<?php echo($colspan); ?>" onclick="document.location.href = '#<?php echo_html($finfo["forum_group_id"]); ?>';"><a class="jump_to_section" id="<?php echo_html($finfo["forum_group_id"]); ?>"></a><?php echo_html(empty($current_group) ? text("OtherForums") : $current_group); ?></th>
</tr>

<?php
endif;
?>


<tr class="<?php echo_html($deleted); ?>">
<td class="checkbox_selector" data-fid="<?php echo_html($fid); ?>" onclick="toggle_selection(this, '<?php echo_html($fid); ?>'); show_forum_actions_menu('<?php echo_html($fid); ?>')"><div>&nbsp;</div></td>

<td class="topic_name_col">

  <div style="position:relative;" id="popup_container_<?php echo_html($fid); ?>">
  <div class="popup_forum_actions_menu" id="popup_forum_actions_menu_<?php echo_html($fid); ?>">

      <div style="position: absolute;right:2px;top:2px;cursor:pointer" onclick="hide_all_popups();"><img src="<?php echo($view_path); ?>images/cross.png" alt="<?php echo_html(text("Close")); ?>"></div>

      <span style="font-weight: bold"><?php echo_html(text("MsgForumsSelected")); ?>: <span class="selected_forums_count">0</span></span>
      
      <a href="forums.php" onclick='return select_all()'><?php echo_html(text("SelectAll")); ?></a>
      <a href="forums.php" onclick='return unselect_all()'><?php echo_html(text("ResetSelection")); ?></a>
      
      <a href="forums.php" onclick='return confirm_action("<?php echo_js(text("MsgConfirmForumsIgnore"), true); ?>", "", { forum_user_action: "add_to_ignored" })'><?php echo_html(text("AddForumsToIgnoredForums")); ?></a>
      <a href="forums.php" onclick='return do_action({ forum_user_action: "remove_from_ignored" })'><?php echo_html(text("RemoveForumsFromIgnoredForums")); ?></a>

      <a href="forums.php" onclick='return do_action({ mark_read_action: "mark_forums_read" })'><?php echo_html(text("MarkRead")); ?></a>

  </div>
  </div>


  <table class="forum_aux_table">
  <tr>
    <td>
      <div class="smart_break">
      
      <?php
      $topic_ignored = "";
      $not_preferred = "";
      if(!empty($finfo["in_ignored"])) 
      {
        $not_preferred = "not_preferred";
        $topic_ignored = "topic_ignored";
      }
      ?>

      <a href="forum.php?fid=<?php echo_html($fid); ?>" class="<?php echo($not_preferred); ?>"><?php echo_html($finfo["name"]); ?></a>

      <?php
      $display = "style='display:none'";
      if(!empty($finfo["topics_with_new_count"])) $display = "";
      ?>
      <span class="new forum_with_new_indicator <?php echo($topic_ignored); ?>" data-fid="<?php echo_html($fid); ?>" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php?fid=<?php echo_html($fid); ?>"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($finfo["topics_with_new_count"]); ?></span></a>]</span>
      
      <?php if(!empty($finfo["closed"])): ?>
      <span class="closed">[<?php echo_html(text("closed")); ?>]</span>
      <?php endif; ?>

      </div>
    </td>
    <?php if(!empty($finfo["moderators"])): ?>
    <td>
      <div class="smart_break" style="text-align: right">
      <?php
      $moderators = "";
      foreach($finfo["moderators"] as $mid => $minfo)
      {
        $online_status = "";
        if(empty($settings["hide_online_status"]) && !empty($minfo["online"]))
        {
          $online_status = "&nbsp;<span class='online_text'>✓</span>";
        }
        $moderators .= "<a href='view_profile.php?uid=$mid' >" . escape_html($minfo["name"]) . "</a>$online_status, ";
      }

      $moderators = trim($moderators, ", ");
      echo "<span class='topic_moderators'>[" . $moderators . "]</span>";
      ?>
      </div>
    </td>
    <?php endif; ?>
  </tr>
  </table>

  <?php if(!empty($finfo["description"])): ?>
  <span class="forum_description"><?php echo_html($finfo["description"]); ?></span>
  <?php endif; ?>

</td>

<td class="author_col">
<div class="smart_break">
  <?php if(empty($finfo["last_author_id"])): ?>
  
  <?php
  $online_status = "";
  if(empty($settings["hide_online_status"]) && empty($finfo["last_author_ignored"]) && !empty($online_users["g_" . $finfo["last_author"]]))
  {
    $online_status = "&nbsp;<span class='online_text'>✓</span>";
  }
  
  if(empty($finfo["last_author_ignored"]))
  {
    if($finfo["last_author"] == "admin")
      $author_string = "<a class='admin_link' href='view_guest_profile.php?guest=" . xrawurlencode($finfo["last_author"]) . "'>" . escape_html(text("MasterAdministrator")) . "</a>";
    else  
      $author_string = "<a class='guest_link' href='view_guest_profile.php?guest=" . xrawurlencode($finfo["last_author"]) . "'>" . escape_html($finfo["last_author"]) . "</a>";
  }
  else
  {
    $author_string = escape_html($finfo["last_author"]);
  }
  ?>
  <span class="<?php if(!empty($finfo["last_author_ignored"])) echo("not_preferred"); ?>"><?php echo($author_string); ?><?php echo($online_status); ?></span>
  <?php else: ?>

  <?php
  $online_status = "";
  if(empty($settings["hide_online_status"]) && !empty($finfo["last_author_online"]))
  {
    $online_status = "&nbsp;<span class='online_text'>✓</span>";
  }
  ?>
  <a href="view_profile.php?uid=<?php echo_html($finfo["last_author_id"]); ?>" ><?php echo_html($finfo["last_author"]); ?></a><?php echo($online_status); ?>
  <?php endif; ?>
</div>  
</td>
<td class="date_col"><?php echo_html($finfo["last_message_date"]); ?></td>
<td class="number_col"><?php echo_html(format_number($finfo["topic_count"])); ?></td>

<?php if($fmanager->is_admin()): ?>
<td class="admin_actions">

<?php if(empty($finfo["closed"])): ?>
<a href="forums.php" class="moderator_link" onclick='return confirm_action("<?php echo_js(text("MsgConfirmForumClose"), true); ?>", "<?php echo_js($finfo["name"], true); ?>", { forum_action : "close", forum: "<?php echo_js($fid); ?>" });'><?php echo_html(text("Close")); ?></a> |
<?php else: ?>
<a href="forums.php" class="moderator_link" onclick='return do_action({ forum_action: "open", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("Open")); ?></a> |
<?php endif; ?>

<?php if(empty($finfo["deleted"])): ?>
<a href="forums.php" class="moderator_link" onclick='return confirm_action("<?php echo_js(text("MsgConfirmForumDelete"), true); ?>", "<?php echo_js($finfo["name"], true); ?>", { forum_action : "delete", forum: "<?php echo_js($fid); ?>" });'><?php echo_html(text("Delete")); ?></a> |
<?php else: ?>
<a href="forums.php" class="moderator_link" onclick='return do_action({ forum_action: "restore", forum: "<?php echo_js($fid); ?>" })'><?php echo_html(text("Restore")); ?></a> |
<?php endif; ?>

<a href="edit_forum.php?fid=<?php echo_html($fid); ?>" class="moderator_link"><?php echo_html(text("Edit")); ?></a>
</td>
<?php endif; ?>

</tr>

<?php endforeach; ?>

<?php endif; ?>

</table>

<?php
@include "online_users_inc.php";
?>

</div>

