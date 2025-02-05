<script>
function confirm_action(msg, fname, params)
{
  if(no_confirmation_of_any_actions == 1 || (no_confirmation_of_uncritical_actions == 1 && params.uncritical)) 
  {
    Forum.hide_user_msgbox();
    do_action(params);
    return false;
  }

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

var action_ajax = null;

function do_action(params)
{
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

  action_ajax.setPOST('hash', get_protection_hash());
  action_ajax.setPOST('user_logged', user_logged);
  action_ajax.setPOST('trace_sql', trace_sql);

  action_ajax.request("ajax/process.php");

  return false;
}
</script>

<!-- BEGIN: header2 -->

<div class="header2">

<div id="actions" class="actions" onclick="toggle_actions()"><?php echo_html(text("Actions")); ?> ...</div>

<div id="actions_area" class="actions_area">
<a href="forums.php" onclick='return confirm_action("<?php echo_js(text("MsgConfirmMarkRead"), true); ?>", "", { mark_read_action: "mark_forums_read", uncritical: 1 })'><?php echo_html(text("MarkRead")); ?></a>

<?php if($fmanager->is_admin()): ?>

<br><?php echo_html($fmanager->is_admin() ? text("Administrator") : text("Moderator")); ?>:<br>

<?php if(empty($_SESSION["show_deleted"])): ?>
<a href="forums.php?show_deleted=1&hash=<?php echo_html($_SESSION["hash"]); ?>" onclick="check_actual_hash(this)" class="moderator_link"><?php echo_html(text("DisplayDeleted")); ?></a>
<?php else: ?>
<a href="forums.php?hide_deleted=1&hash=<?php echo_html($_SESSION["hash"]); ?>" onclick="check_actual_hash(this)" class="moderator_link"><?php echo_html(text("HideDeleted")); ?></a>
<?php endif; ?>

<?php endif; ?>

</div>

</div>

<!-- END: header2 -->


<!-- BEGIN: forum_bar -->

<div class="forum_bar forum_bar_breakable">

<div class="forum_name_bar"><a href="forums.php"><?php echo_html(text("Forums")); ?></a>

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

<?php if($fmanager->is_admin()): ?>
<div class="forum_action_bar">
<input type="button" class="standard_button" value="<?php echo_html(text("CreateForum")); ?>" onclick="delay_redirect('edit_forum.php')">
<input type="button" class="standard_button" value="<?php echo_html(text("ForumGroups")); ?>" onclick="delay_redirect('forum_groups.php')">
</div>
<?php endif; ?>

<div class="clear_both">
</div>

</div>

<!-- END: forum_bar -->


<table class="forum_table">
<tr>
<th><?php echo_html(text("Forum")); ?></th>
</tr>

<?php if(count($groupped_forum_list) == 0): ?>

<tr>
<td class="table_message"><?php echo_html(text("NoForums")); ?></td>
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
<th class="subheader" style="cursor: pointer" onclick="document.location.href = '#<?php echo_html($finfo["forum_group_id"]); ?>';"><a class="jump_to_section" id="<?php echo_html($finfo["forum_group_id"]); ?>"></a><?php echo_html(empty($current_group) ? text("OtherForums") : $current_group); ?></th>
</tr>

<?php
endif;
?>

<tr class="<?php echo_html($deleted); ?>">

<td>

<?php
$topic_ignored = "";
$not_preferred = "";
if(!empty($finfo["in_ignored"])) 
{
  $topic_ignored = "topic_ignored";
  $not_preferred = "not_preferred";
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

<?php if(!empty($finfo["description"])): ?>
<div class="forum_description"><?php echo_html($finfo["description"]); ?></div>
<?php endif; ?>

<div class="forum_info">

<?php
if(!empty($finfo["moderators"]))
{
  $moderators = "";
  foreach($finfo["moderators"] as $mid => $minfo)
  {
    $online_status = "";
    if(empty($settings["hide_online_status"]) && !empty($minfo["online"]))
    {
      $online_status = "&nbsp;<span class='online_text'>✓</span>";
    }
    $moderators .= "<a href='view_profile.php?uid=$mid'>" . escape_html($minfo["name"]) . "</a>$online_status, ";
  }

  $moderators = trim($moderators, ", ");

  echo escape_html(text("Moderators")) . ": " . $moderators . "<br>";
}
?>


<?php echo_html(text("LastAuthor")); ?>:
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
  <span class="number <?php if(!empty($finfo["last_author_ignored"])) echo("not_preferred"); ?>"><?php echo($author_string); ?><?php echo($online_status); ?></span>
  <?php else: ?>

  <?php
  $online_status = "";
  if(empty($settings["hide_online_status"]) && !empty($finfo["last_author_online"]))
  {
    $online_status = "&nbsp;<span class='online_text'>✓</span>";
  }
  ?>
  <a href="view_profile.php?uid=<?php echo_html($finfo["last_author_id"]); ?>"><?php echo_html($finfo["last_author"]); ?></a><?php echo($online_status); ?>
  <?php endif; ?>
<br>
<?php echo_html(text("LastMessage")); ?>: <span class="number"><?php echo_html($finfo["last_message_date"]); ?></span>
<br>
<?php echo_html(text("Topics")); ?>: <span class="number"><?php echo_html(format_number($finfo["topic_count"])); ?></span>


  <?php if($fmanager->is_admin()): ?>
    <div class="forum_info">
     <?php echo_html(text("Administrator")); ?>:
      
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

    </div>
  <?php endif; ?>

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

<?php
@include "online_users_inc.php";
?>

