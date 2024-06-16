<script>
var selected_topics = {};

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
  hide_all_popups();

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

function show_topic_actions_menu(tid)
{
  hide_all_popups();

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

function user_esc_handler()
{
  hide_all_popups();
}

function confirm_action(msg, params)
{
  if(no_confirmation_of_any_actions == 1 || (no_confirmation_of_uncritical_actions == 1 && params.uncritical)) 
  {
    Forum.hide_user_msgbox();
    do_action(params);
    return false;
  }

  if(!params.mark_read_action && Forum.isEmptyObject(selected_topics))
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

  Forum.show_user_msgbox(msg_Confirmation, msg, 'icon-question.gif', mbuttons);

  return false;
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

  action_ajax.request("ajax/process.php");

  return false;
}
</script>

<?php
$base_url = "favourites.php";
$fpage_appendix = "";

if(!reqvar_empty("fpage"))
{
  $fpage_appendix = "&fpage=" . reqvar("fpage");
}
?>

<!-- BEGIN: header3 -->

<div class="header3">

<?php if($fmanager->is_moderator()): ?>

<div class="left_action_panel">

<?php if(empty($_SESSION["show_deleted"])): ?>
<a href="<?php echo($base_url); ?>?show_deleted=1<?php echo($fpage_appendix); ?>&hash=<?php echo_html($_SESSION["hash"]); ?>" onclick="check_actual_hash(this)" class="moderator_link"><?php echo_html(text("DisplayDeleted")); ?></a>
<?php else: ?>
<a href="<?php echo($base_url); ?>?hide_deleted=1<?php echo($fpage_appendix); ?>&hash=<?php echo_html($_SESSION["hash"]); ?>" onclick="check_actual_hash(this)" class="moderator_link"><?php echo_html(text("HideDeleted")); ?></a>
<?php endif; ?>

</div>

<?php endif; ?>

<div class="right_action_panel">
<?php
$display = "style='display:none'";
$digest_url = "search_topic.php?news_digest=1&do_search=1&fid=favourites";
$class = "favourites_with_new_indicator";
if(!empty($favourites_with_new_count)) $display = "";
?>
<span class="<?php echo($class); ?>" <?php echo($display); ?>><a href="<?php echo($digest_url); ?>" ><?php echo_html(text("Digest")); ?></a> |</span>

<a href="<?php echo($base_url . $fpage_appendix); ?>" onclick='return confirm_action("<?php echo_js(text("MsgConfirmMarkRead"), true); ?>", { mark_read_action: "mark_favourites_read", uncritical: 1 })'><?php echo_html(text("MarkRead")); ?></a>
</div>

<div class="clear_both">
</div>

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

/ <a href="favourites.php"><?php echo_html($forum_title); ?></a>

<?php
$display = "style='display:none'";
if(!empty($favourites_with_new_count)) $display = "";
?>
<span class="new favourites_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php?fid=-1"><?php echo_html(text("new")); ?>:<span class='favourites_with_new_count'><?php echo($favourites_with_new_count); ?></span></a>]</span>

/ <?php echo_html(text("Topics")); ?>: <span class='count_number'><?php echo_html(format_number(val_or_empty($pagination_info["total_count"]))); ?></span>,

<?php echo(build_page_info($pagination_info, text("pages"))); ?>

<?php if(!empty($pagination_info["ignored_count"])): ?>
<?php if($fmanager->is_logged_in()): ?>
<a class="not_preferred" href="search.php?do_search=1&author=<?php echo(xrawurlencode($fmanager->get_user_name())); ?>&author_mode=ignoring&favourites_only=1"><?php echo_html(text("ignored")); ?>: <?php echo_html(format_number($pagination_info["ignored_count"])); ?></a>
<?php else: ?>
<span class="not_preferred"><?php echo_html(text("ignored")); ?>: <?php echo_html(format_number($pagination_info["ignored_count"])); ?></span>
<?php endif; ?>
<?php endif; ?>

</div>


<?php if($pagination_info["page_count"] > 1): ?>
<div class="navigator_bar"><?php echo(build_page_navigator("favourites.php?fpage=$", $pagination_info)); ?></div>
<?php endif; ?>

<div class="forum_action_bar">
<table>
<tr>
<td>
<?php
$forum_selector_id = 1;
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


<table class="topic_table">
<tr>
<th id="all_checkbox_selector" class="all_checkbox_selector" onclick="toggle_all_selection(this)"><div>&nbsp;</div></th>
<th class="topic_name_col"><?php echo_html(text("Topic")); ?></th>
<th class="forum_col"><?php echo_html(text("Forum")); ?></th>
<th class="author_col"><?php echo_html(text("Author")); ?></th>
<th class="author_col"><?php echo_html(text("LastAuthor")); ?></th>
<th class="date_col"><?php echo_html(text("LastMessage")); ?></th>
<th class="number_col"><?php echo_html(text("Messages")); ?></th>
<th class="number_col"><?php echo_html(text("Views")); ?></th>
</tr>

<?php if(count($topic_list) == 0): ?>

<tr>
<td colspan="8" class="table_message"><?php echo_html(text("NoTopics")); ?></td>
</tr>

<?php else: ?>

<?php
foreach($topic_list as $tid => $tinfo):
$deleted = "";
if(!empty($tinfo["deleted"])) $deleted = "deleted_row";

if(!empty($_SESSION["topic_moderator"][$tid])) $deleted .= " moderated_topic_row";
?>

<tr class="<?php echo_html($deleted); ?>">

<?php if($tid == -1): ?>
<td></td>
<?php else: ?>
<td class="checkbox_selector" data-tid="<?php echo_html($tid); ?>" onclick="toggle_selection(this, '<?php echo_html($tid); ?>'); show_topic_actions_menu('<?php echo_html($tid); ?>')"></td>
<?php endif; ?>

<td class="topic_name_col">
  <div style="position:relative;" id="popup_container_<?php echo_html($tid); ?>">
  <div class="popup_topic_actions_menu" id="popup_topic_actions_menu_<?php echo_html($tid); ?>">

      <div style="position: absolute;right:2px;top:2px;cursor:pointer" onclick="hide_all_popups();"><img src="<?php echo($view_path); ?>images/cross.png" alt="<?php echo_html(text("Close")); ?>"></div>

      <span style="font-weight: bold"><?php echo_html(text("MsgTopicsSelected")); ?>: <span class="selected_topics_count">0</span></span>

      <a href="<?php echo($base_url . $fpage_appendix); ?>" onclick='return select_all()'><?php echo_html(text("SelectAll")); ?></a>
      <a href="<?php echo($base_url . $fpage_appendix); ?>" onclick='return unselect_all()'><?php echo_html(text("ResetSelection")); ?></a>
      
      <a href="<?php echo($base_url . $fpage_appendix); ?>" onclick='return confirm_action("<?php echo_js(text("MsgConfirmRemoveFromFavourites"), true); ?>", { topic_action: "bulk_remove_from_favourites" });'><?php echo_html(text("RemoveFromFavourites")); ?></a>
      
      <a href="<?php echo($base_url . $fpage_appendix); ?>" onclick='return confirm_action("<?php echo_js(text("MsgConfirmTopicsIgnore"), true); ?>", { topic_action: "bulk_add_to_ignored" })'><?php echo_html(text("AddTopicsToIgnoredTopics")); ?></a>
      <a href="<?php echo($base_url . $fpage_appendix); ?>" onclick='return do_action({ topic_action: "bulk_remove_from_ignored" })'><?php echo_html(text("RemoveTopicsFromIgnoredTopics")); ?></a>
      
      <a href="<?php echo($base_url . $fpage_appendix); ?>" onclick='return do_action({ mark_read_action: "mark_favourites_read" })'><?php echo_html(text("MarkRead")); ?></a>

      <?php if($fmanager->is_admin()): ?>
      <div class="mod_actions_separator"><?php echo_html($fmanager->is_admin() ? text("Administrator") : text("Moderator")); ?>:</div>

      <a href="<?php echo($base_url . $fpage_appendix); ?>" class="moderator_link" onclick='return confirm_action("<?php echo_js(text("MsgConfirmTopicsDelete"), true); ?>", { topic_action: "delete_topics" });'><?php echo_html(text("DeleteTopics")); ?></a>
      <?php if(!empty($_SESSION["show_deleted"])): ?>
      <a href="<?php echo($base_url . $fpage_appendix); ?>" class="moderator_link" onclick='return do_action({ topic_action: "restore_topics" })'><?php echo_html(text("RestoreTopics")); ?></a>
      <?php endif; ?>
      <?php endif; ?>

  </div>
  </div>

  <table class="topic_aux_table">
  <tr>
    <td>
      <div class="smart_break">
      <?php
      $topic_status = "";
      if(!empty($tinfo["pinned"]) && empty($tinfo["is_poll"]) && empty($tinfo["profiled_topic"])) 
        $topic_status .= text("Important") . ": ";
      if(!empty($tinfo["profiled_topic"]) && empty($tinfo["is_poll"]))
        $topic_status .= text("Dedicated") . ": ";
      if(!empty($tinfo["is_poll"]))
        $topic_status .= text("Poll") . ": ";
      if(!empty($tinfo["publish_delay"]))
        $topic_status .= text("NotPublished") . ": ";
      
      if(!empty($topic_status)) 
        echo('<span class="topic_status">' . escape_html($topic_status) . '</span>');
      ?>
      
      <?php
      $topic_base_url = "topic.php?fid=" . $tinfo["forum_id"] . "&tid=" . $tid;
      if($tid == -1)
      {
        $topic_base_url = "search_topic.php?favourite_posts=1";
      }
      
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
    </td>
    <?php if(!empty($tinfo["moderators"])): ?>
    <td>
      <div class="smart_break" style="text-align: right">
      <?php
      $moderators = "";
      foreach($tinfo["moderators"] as $mid => $minfo)
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

</td>
<td class="forum_col">
<div class="smart_break">
<?php
$fid = $tinfo["forum_id"];
$forum_url = "forum.php?fid=$fid";
if($fid == "-1")
{
  $forum_url = "favourites.php";
}

$not_preferred = "";
if(!empty($_SESSION["preferred_forums"]) && empty($_SESSION["preferred_forums"][$fid]) && $fid != "-1" && $fid != "private") $not_preferred = "not_preferred";
?>
<a href="<?php echo($forum_url); ?>" class="<?php echo($not_preferred); ?>"><?php echo_html($tinfo["forum_name"]); ?></a>
</div>
</td>
<td class="author_col">
<div class="smart_break">
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
  <span class="<?php if(!empty($tinfo["author_ignored"])) echo("not_preferred"); ?>"><?php echo($author_string); ?><?php echo($online_status); ?></span>
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
</div>  
</td>
<td class="author_col">
<div class="smart_break">
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
  <span class="<?php if(!empty($tinfo["last_author_ignored"])) echo("not_preferred"); ?>"><?php echo($author_string); ?><?php echo($online_status); ?></span>
  <?php else: ?>

  <?php
  $online_status = "";
  if(empty($settings["hide_online_status"]) && !empty($tinfo["last_author_online"]))
  {
    $online_status = "&nbsp;<span class='online_text'>✓</span>";
  }
  ?>
  <a href="view_profile.php?uid=<?php echo_html($tinfo["last_author_id"]); ?>" ><?php echo_html($tinfo["last_author"]); ?></a><?php echo($online_status); ?>
  <?php endif; ?>
</div>  
</td>
<td class="date_col"><?php echo_html($tinfo["last_message_date"]); ?></td>
<td class="number_col"><?php echo_html(format_number($tinfo["post_count"])); ?></td>
<td class="number_col"><?php echo_html(format_number($tinfo["hits_count"])); ?><?php if(!empty($tinfo["bot_hits_count"])) echo_html(" / " . format_number($tinfo["bot_hits_count"])); ?></td>
</tr>

<?php endforeach; ?>

<?php endif; ?>

</table>

<?php if(count($topic_list) > 0): ?>

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

/ <a href="favourites.php"><?php echo_html($forum_title); ?></a>

<?php
$display = "style='display:none'";
if(!empty($favourites_with_new_count)) $display = "";
?>
<span class="new favourites_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php?fid=-1"><?php echo_html(text("new")); ?>:<span class='favourites_with_new_count'><?php echo($favourites_with_new_count); ?></span></a>]</span>

/ <?php echo_html(text("Topics")); ?>: <span class='count_number'><?php echo_html(format_number(val_or_empty($pagination_info["total_count"]))); ?></span>,

<?php echo(build_page_info($pagination_info, text("pages"))); ?>

<?php if(!empty($pagination_info["ignored_count"])): ?>
<?php if($fmanager->is_logged_in()): ?>
<a class="not_preferred" href="search.php?do_search=1&author=<?php echo(xrawurlencode($fmanager->get_user_name())); ?>&author_mode=ignoring&favourites_only=1"><?php echo_html(text("ignored")); ?>: <?php echo_html(format_number($pagination_info["ignored_count"])); ?></a>
<?php else: ?>
<span class="not_preferred"><?php echo_html(text("ignored")); ?>: <?php echo_html(format_number($pagination_info["ignored_count"])); ?></span>
<?php endif; ?>
<?php endif; ?>

</div>

<?php if($pagination_info["page_count"] > 1): ?>
<div class="navigator_bar"><?php echo(build_page_navigator("favourites.php?fpage=$", $pagination_info)); ?></div>
<?php endif; ?>

<div class="forum_action_bar">
<table>
<tr>
<td>
<?php
$forum_selector_id = 2;
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

<?php endif; ?>

<?php
@include "online_users_inc.php";
?>

</div>
