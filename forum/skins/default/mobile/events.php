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
  SimpleCalendar.assign("#end_date", config);
});

function confirm_action(msg, params)
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

  Forum.show_user_msgbox(msg_Confirmation, msg, 'icon-question.gif', mbuttons);

  return false;
}

var action_ajax = null;

function do_action(params)
{
  var elm;
  
  if(params.set_event_done)
  { 
    elm = document.getElementById("indicator_loading_" + params.event);
    if(elm) elm.style.display = "inline";

    elm = document.getElementById("indicator_" + params.event);
    if(elm) elm.style.display = "none";
  }
  else
  {
    Forum.show_sys_progress_indicator(true);
  }

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

        if(params.mark_events_done && response.success)
        {
          delay_reload();
          return;
        }
        
        if(params.set_event_done && response.success)
        { 
          elm = document.getElementById("indicator_loading_" + action_ajax.event);
          if(elm) elm.style.display = "none";
        }
      }
      catch(err)
      {
        elm = document.getElementById("indicator_loading_" + action_ajax.event);
        if(elm) elm.style.display = "none";

        Forum.handle_ajax_error(this, err.message, this.last_url, {});
      }

      Forum.show_sys_progress_indicator(false);
    };

    action_ajax.onerror = function(error, url, info)
    {
      Forum.show_sys_progress_indicator(false);

      if(params.set_event_done)
      { 
        elm = document.getElementById("indicator_loading_" + action_ajax.event);
        if(elm) elm.style.display = "none";

        elm = document.getElementById("indicator_" + action_ajax.event);
        if(elm) elm.style.display = "inline";
      }

      Forum.handle_ajax_error(this, error, url, info);
    };
  }
  
  action_ajax.abort();
  action_ajax.resetParams();

  action_ajax.event = null;
  if(params.event) action_ajax.event = params.event;

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

function user_esc_handler()
{
  var elm = document.getElementById("author_name_lookup");
  if(elm)
  {
    elm.parentNode.style.display = "none";

    for(var i = elm.length - 1; i >= 0 ; i--)
    {
      elm.options[i] = null;
    }
  }
}
</script>

<!-- BEGIN: header2 -->

<div class="header2 moderation_log_filter">

<form action="events.php" method="get" onsubmit="Forum.show_sys_progress_indicator(true);">
<input type="hidden" name="apply_filter" value="1">

<table>
<tr>
<td>
<?php echo_html(text("Author")); ?>:
</td>
<td>
  <input type="text" class="filter_field" id="author_name" name="author_name" autocomplete="off" value="<?php echo_html(val_or_empty($_SESSION["event_log_filter"]["author_name"])); ?>" onkeypress="return lookup_handle_enter(this.id, event)" onkeyup="return lookup_entries('search_authors', this, event)" onblur="lookup_delayed_hide(this.id)">
  <div style="position: relative">
  <div class="field_lookup_area" style="display:none">
  <select id="author_name_lookup" size="10" 
           onclick="if(!mustAdjustMultiSelect()) { lookup_apply_selection('author_name') }" 
           onchange="if(mustAdjustMultiSelect()) { lookup_apply_selection_if_active('author_name') }" 

           onkeypress="return lookup_handle_enter('author_name', event)" onblur="user_esc_handler()"
           >
  </select>
  </div>
  </div>
</td>
</tr>
<tr>
<td>
<?php echo_html(text("Event")); ?>:
</td>
<td>
<select name="event_type" id="event_type" class="filter_field" onchange="Forum.show_sys_progress_indicator(true); this.form.submit();">
<?php foreach($filter_list as $eid => $ename):
$selected = (val_or_empty($_SESSION["event_log_filter"]["event_type"]) == $eid) ? "selected" : "";
?>
<option value="<?php echo_html($eid); ?>" <?php echo($selected); ?>><?php echo_html($ename); ?></option>
<?php endforeach; ?>
</select>
</td>
</tr>

<tr>
<td>
<?php echo_html(text("Topic")); ?>:
</td>
<td>
<input type="text" class="filter_field" id="topic_name" name="topic_name" value="<?php echo_html(val_or_empty($_SESSION["event_log_filter"]["topic_name"])); ?>">
</td>
</tr>
<tr>
<td>
<?php echo_html(text("Forum")); ?>:
</td>
<td>
<select name="forum" id="forum" class="filter_field" onchange="Forum.show_sys_progress_indicator(true); this.form.submit();">
<option value="">-</option>

<?php
$selected = (val_or_empty($_SESSION["event_log_filter"]["forum"]) == "private") ? "selected" : "";
?>
<option value="private" <?php echo($selected); ?>><?php echo_html(text("PrivateTopics")); ?></option>

<?php foreach($forum_list as $fid => $fdata):
$selected = (val_or_empty($_SESSION["event_log_filter"]["forum"]) == $fid) ? "selected" : "";
?>
<option value="<?php echo_html($fid); ?>" <?php echo($selected); ?>><?php echo_html($fdata["name"]); ?></option>
<?php endforeach; ?>

</select>
</td>
</tr>


<tr>
<td>
<?php echo_html(text("DateRange")); ?>:
</td>
<td>
  <table class="date_block">
      <tr>
      <td><input type="text" class="filter_field" autocomplete="off" id="start_date" name="start_date" value="<?php echo_html(val_or_empty($_SESSION["event_log_filter"]["start_date"])); ?>"></td>
      <td style="text-align:center; width: 10px"></td>
      <td><input type="text" class="filter_field" autocomplete="off" id="end_date" name="end_date" value="<?php echo_html(val_or_empty($_SESSION["event_log_filter"]["end_date"])); ?>"></td>
      </tr>
  </table>
</td>
</tr>
<tr>
<td colspan="2" style="text-align: center">
<input type="submit" class="standard_button" value="<?php echo_html(text("Search")); ?>">
<input type="submit" class="standard_button" value="<?php echo_html(text("Reset")); ?>" onclick="this.form.elements['apply_filter'].value='-1'">
</td>
</tr>
</table>

</form>

</div>

<!-- END: header2 -->

<!-- BEGIN: header2 -->

<div class="header2">

<div id="actions" class="actions" onclick="toggle_actions()"><?php echo_html(text("Actions")); ?> ...</div>

<div id="actions_area" class="actions_area">

<a href="events.php" onclick='return confirm_action("<?php echo_js(text("MsgConfirmMarkDone"), true); ?>", { mark_events_done: 1, uncritical: 1 })'><?php echo_html(text("MarkDone")); ?></a><br>

<?php if(get_cookie("q_stored_event_filter") == ""): 
$appendix = "";
if(!reqvar_empty("event_type")) $appendix = "&event_type=" . reqvar("event_type");
?>
<a href="events.php?store_event_filter=1<?php echo($appendix); ?>&hash=<?php echo_html($_SESSION["hash"]); ?>" class="moderator_link" onclick="check_actual_hash(this)"><?php echo_html(text("StoreFilter")); ?></a>
<?php else: ?>
<a href="events.php?reset_event_filter=1&hash=<?php echo_html($_SESSION["hash"]); ?>" class="moderator_link" onclick="check_actual_hash(this)"><?php echo_html(text("ResetFilter")); ?></a>
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

/ <span class="topic_title_main"><?php echo_html(text("Events")); ?></span>,

<?php echo(build_page_info($pagination_info, text("pages"))); ?>
</div>

<?php if($pagination_info["page_count"] > 1): ?>
<div class="forum_bar">
<div class="navigator_bar"><?php echo(build_page_navigator($pagination_info["base_url_pagination"], $pagination_info)); ?></div>
<div class="clear_both">
</div>
</div>
<?php endif; ?>

<!-- END: forum_bar -->

<table class="event_table">
<tr>
<th><?php echo_html(text("Event")); ?></th>
</tr>

<?php if(count($event_list) == 0): ?>

<tr>
<td class="table_message"><?php echo_html(text("NoEvents")); ?></td>
</tr>

<?php else: ?>

<?php foreach($event_list as $evid => $evinfo): 
$fmanager->build_event_html($evinfo, false);

$event_class = empty($evinfo["moderator_event"]) ? "" : "moderator_event"; 
if(!empty($evinfo["attention_event"]))
  $event_class = "attention_event"; 
if(!empty($evinfo["negative_event"]))
  $event_class = "negative_event"; 
?>

<tr>
<td class="<?php echo $event_class; ?>">

<div class="forum_info">
<span id="indicator_loading_<?php echo($evid); ?>" class="mark_done_loading" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;</span>

<?php if(!empty($evinfo["is_new"])): ?>
<span id="indicator_<?php echo($evid); ?>" class="new" title="<?php echo_html(text("MarkDone")); ?>" style="cursor: pointer" onclick="do_action({ set_event_done: 1, event: <?php echo($evid); ?> })">[<?php echo_html(text("new")); ?>]</span>
<?php elseif(!empty($evinfo["todo"])): ?>
<span id="indicator_<?php echo($evid); ?>" class="new" title="<?php echo_html(text("MarkDone")); ?>" style="cursor: pointer" onclick="do_action({ set_event_done: 1, event: <?php echo($evid); ?> })">✷</span>
<?php endif; ?>

<?php echo_html(text("DateTime")); ?>: <span class="number"><?php echo_html($evinfo["event_time"]); ?></span>
</div>

<?php
$online_status = "";
if(empty($settings["hide_online_status"]) && !empty($evinfo["author"]) && (!empty($evinfo["online"]) || !empty($online_users["g_" . $evinfo["author_name"]])))
{
  $online_status = "&nbsp;<span class='online_text'>✓</span>";
}
?>
<div class="forum_info"><?php echo_html(text("Author")); ?>: <?php echo($evinfo["author"]); ?><?php echo($online_status); ?></div>

<div class="forum_description"><?php echo($evinfo["event"]); ?></div>

<div class="navigation_arrows_right">
  <div class="scroll_up" onclick="window.scrollTo(0, 0);"></div>
  <div class="scroll_down" onclick="window.scrollTo(0, 1000000);"></div>
</div>

<?php endforeach; ?>

<?php endif; ?>

</table>

<?php if(count($event_list) > 2): ?>

<!-- BEGIN: forum_bar -->

<?php if($pagination_info["page_count"] > 1): ?>
<div class="forum_bar">
<div class="navigator_bar"><?php echo(build_page_navigator($pagination_info["base_url_pagination"], $pagination_info)); ?></div>
<div class="clear_both">
</div>
</div>
<?php endif; ?>

<div class="forum_bar">
<a href="forums.php"><?php echo_html(text("Forums")); ?></a>

<?php
$display = "style='display:none'";
if(!empty($topics_with_new_count)) $display = "";
?>
<span class="new topics_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span> 

/ <span class="topic_title_main"><?php echo_html(text("Events")); ?></span>,

<?php echo(build_page_info($pagination_info, text("pages"))); ?>
</div>

<!-- END: forum_bar -->

<?php endif; ?>

