<script src='skins/<?php echo($skin); ?>/js/field_lookup.js<?php echo($cache_appendix); ?>'></script>

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
  
  function set_to_me() {
    var elm = document.getElementById("author");
    if (elm) elm.value = "<?php echo_js($fmanager->get_user_name()); ?>";
    return false;
  }

  function reset_search_form(form) {
    Forum.clearForm(form);

    var elm = document.getElementById("wrote_post");
    if (elm) elm.checked = true;
    
    elm = document.getElementById("search_keys");
    if(elm) elm.focus();
  }

  function user_esc_handler() {
    hide_all_popups();
  
    var elm = document.getElementById("author_lookup");
    if (elm) {
      elm.parentNode.style.display = "none";

      for (var i = elm.length - 1; i >= 0; i--) {
        elm.options[i] = null;
      }
    }
    
    elm = document.getElementById("topic_name_lookup");
    if (elm) {
      elm.parentNode.style.display = "none";

      for (var i = elm.length - 1; i >= 0; i--) {
        elm.options[i] = null;
      }
    }    
    
    reset_topic_if_not_found();
  }
  
  function reset_topic_on_search()
  {
    var tid = document.getElementById("tid");
    var topic = document.getElementById("topic_name");
    if(!tid || !topic) return;
    
    if(!topic.value.match(/^\[#(\d+)\].*/))
    {
      tid.value = "";
    }
  }
  
  function reset_topic_if_not_found()
  {
    var tid = document.getElementById("tid");
    var topic = document.getElementById("topic_name");
    
    if(!tid || !topic) return;
    
    if(!tid.value) topic.value = "";
  }

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

function confirm_action(msg, params)
{
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
          delay_reload();
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
$base_url = "search.php?" . $search_params;

$base_url_complete = $base_url;
if(!reqvar_empty("fpage"))
{
  $base_url_complete .= "&fpage=" . reqvar("fpage");
}
?>

<div class="content_area">

<!-- BEGIN: forum_bar -->

<?php
$wide_bar = "";
if(!empty($in_search)) $wide_bar = "wide_bar";
?>

<div class="forum_bar">

  <div class="forum_name_bar <?php echo($wide_bar); ?>"><a href="forums.php"><?php echo_html(text("Forums")); ?></a>
    
    <?php
    $display = "style='display:none'";
    if (!empty($topics_with_new_count)) {
      $display = "";
    }
    ?>
    <span class="new topics_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span>

    <?php if(!reqvar_empty("favourite_posts_only") || !reqvar_empty("favourites_only")): ?>

      / <a href="favourites.php"><?php echo_html(text("Favourites")); ?></a> 

      <?php
      $display = "style='display:none'";
      if(!empty($favourites_with_new_count)) $display = "";
      ?>
      <span class="new favourites_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php?fid=-1"><?php echo_html(text("new")); ?>:<span class='favourites_with_new_count'><?php echo($favourites_with_new_count); ?></span></a>]</span>

    <?php elseif(!empty($fid) && !empty($forum_title)): ?>

      <?php
      $not_preferred = "";
      if(!empty($_SESSION["preferred_forums"]) && empty($_SESSION["preferred_forums"][$fid]) && !$is_private) $not_preferred = "not_preferred";
      ?>
      / <a href="forum.php?fid=<?php echo_html($fid_for_url); ?>" class="<?php echo($not_preferred); ?>"><?php echo_html($forum_title); ?></a>

      <?php
      $display = "style='display:none'";
      if(!empty($forum_data["topics_with_new_count"])) $display = "";
      ?>
      <span class="new forum_with_new_indicator" data-fid="<?php echo_html($fid_for_url); ?>" <?php echo($display); ?>>[<a href="<?php echo("new_messages.php?fid=" . $fid_for_url); ?>"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($forum_data["topics_with_new_count"]); ?></span></a>]</span>

    <?php endif; ?>

    / <span class="topic_title_main"><?php echo_html($search_title); ?></span>

  </div>

  <?php if ($in_search): ?>
    <div class="message_info_bar"><?php echo_html(text("Topics")); ?>: <span class='count_number'><?php echo_html(format_number(val_or_empty($pagination_info["total_count"]))); ?></span>,
        
        <?php echo(build_page_info($pagination_info, text("pages"))); ?>
        
        <?php if(!empty($pagination_info["ignored_count"])): ?>
        <?php if($fmanager->is_logged_in() || $fmanager->get_user_name() != ""): ?>
        <?php $search_url = str_replace("/&(author|author_mode)=[^&]+/", "", $_SERVER["REQUEST_URI"]) . "&author=" . xrawurlencode($fmanager->get_user_name()) . "&author_mode=ignoring"; ?>
        <a class="not_preferred" href="<?php echo($search_url); ?>"><?php echo_html(text("ignored")); ?>: <?php echo_html(format_number($pagination_info["ignored_count"])); ?></a>
        <?php else: ?>
        <span class="not_preferred"><?php echo_html(text("ignored")); ?>: <?php echo_html(format_number($pagination_info["ignored_count"])); ?></span>
        <?php endif; ?>
        <?php endif; ?>
        
        </div>
    
    <?php if (empty($pagination_info["all"]) && $pagination_info["page_count"] > 1): ?>
      <div class="navigator_bar"><?php echo(build_page_navigator($base_url . "&spage=$", $pagination_info)); ?></div>
    <?php endif; ?>
  <?php endif; ?>

  <div class="forum_action_bar">
    <table>
      <tr>
        <td>
          <?php
          @include "forum_selector_inc.php";
          ?>
        </td>
        
        <?php if ($in_search): ?>
          <td>
            <input type="button" class="standard_button" value="<?php echo_html(text("NewSearch")); ?>" onclick="delay_redirect('<?php echo(empty($search_params) ? 'search.php' : 'search.php?' . $search_params . '&new_search=1'); ?>')">
          </td>
        <?php endif; ?>

      </tr>
    </table>
  </div>

  <div class="clear_both">
  </div>

</div>

<!-- END: forum_bar -->

<?php if ($in_search): ?>

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
    
    <?php if (count($topic_list) == 0): ?>

      <tr>
        <td colspan="8" class="table_message"><?php echo_html(text("NoTopicsFound")); ?></td>
      </tr>
    
    <?php else: ?>
      
      <?php
      foreach ($topic_list as $tid => $tinfo):
        $deleted = "";
        if (!empty($tinfo["deleted"])) {
          $deleted = "deleted_row";
        }

        if(!empty($_SESSION["topic_moderator"][$tid])) $deleted .= " moderated_topic_row";
        ?>

        <tr class="<?php echo_html($deleted); ?>">
          <td class="checkbox_selector" data-tid="<?php echo_html($tid); ?>" onclick="toggle_selection(this, '<?php echo_html($tid); ?>'); show_topic_actions_menu('<?php echo_html($tid); ?>')"></td>
          <td class="topic_name_col">

            <div style="position:relative;" id="popup_container_<?php echo_html($tid); ?>">
            <div class="popup_topic_actions_menu" id="popup_topic_actions_menu_<?php echo_html($tid); ?>">

                <div style="position: absolute;right:2px;top:2px;cursor:pointer" onclick="hide_all_popups();"><img src="<?php echo($view_path); ?>images/cross.png" alt="<?php echo_html(text("Close")); ?>"></div>

                <span style="font-weight: bold"><?php echo_html(text("MsgTopicsSelected")); ?>: <span class="selected_topics_count">0</span></span>

                <a href="<?php echo($base_url_complete); ?>" onclick='return select_all()'><?php echo_html(text("SelectAll")); ?></a>
                <a href="<?php echo($base_url_complete); ?>" onclick='return unselect_all()'><?php echo_html(text("ResetSelection")); ?></a>
                
                <a href="<?php echo($base_url_complete); ?>" onclick='return confirm_action("<?php echo_js(text("MsgConfirmTopicsIgnore"), true); ?>", { topic_action: "bulk_add_to_ignored" })'><?php echo_html(text("AddTopicsToIgnoredTopics")); ?></a>
                <a href="<?php echo($base_url_complete); ?>" onclick='return do_action({ topic_action: "bulk_remove_from_ignored" })'><?php echo_html(text("RemoveTopicsFromIgnoredTopics")); ?></a>

                <a href="<?php echo($base_url_complete); ?>" onclick='return do_action({ mark_read_action: "mark_search_read" })'><?php echo_html(text("MarkRead")); ?></a>

                <?php if($fmanager->is_admin()): ?>
                <div class="mod_actions_separator"><?php echo_html($fmanager->is_admin() ? text("Administrator") : text("Moderator")); ?>:</div>
          
                <a href="<?php echo($base_url_complete); ?>" class="moderator_link" onclick='return confirm_action("<?php echo_js(text("MsgConfirmTopicsDelete"), true); ?>", { topic_action: "delete_topics" });'><?php echo_html(text("DeleteTopics")); ?></a>
                <?php if(!empty($_SESSION["show_deleted"])): ?>
                <a href="<?php echo($base_url_complete); ?>" class="moderator_link" onclick='return do_action({ topic_action: "restore_topics" })'><?php echo_html(text("RestoreTopics")); ?></a>
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
                    $first_post_appendix = "";
                    $only_found_appendix = "";
                    
                    if (!empty($tinfo["first_post"])) {
                      $first_post_appendix = "&msg=" . $tinfo["first_post"];
                      
                      $only_found_appendix = "search_topic.php?" . $search_params . "&tid=" . $tid;
                    }
                    
                    $topic_base_url = "topic.php?fid=" . $tinfo["forum_id"] . "&tid=$tid&from_search=1";
                    
                    if (!reqvar_empty("search_keys")) {
                      $topic_base_url .= "&search_keys=" . xrawurlencode(reqvar("search_keys"));
                      
                      if (!reqvar_empty("with_morphology")) {
                        $topic_base_url .= "&with_morphology=1";
                      }
                    }
                    
                    $not_preferred = "";
                    if (!empty($tinfo["topic_ignored"])) {
                      $not_preferred = "not_preferred";
                    }
                    
                    if (!empty($only_found_appendix)) {
                      $only_found_appendix = " &nbsp;<span class='post_pagination'>(<a href='$only_found_appendix' class='$not_preferred'>" . text("OnlyFound") . "</a>)</span>";
                    }
                    ?>

                    <a class="<?php echo($not_preferred); ?>" href="<?php echo($topic_base_url . $first_post_appendix); ?>"><?php echo_html(postprocess_message($tinfo["name"])); ?></a>
                    
                    <?php if (!empty($tinfo["hot"])): ?><span class="hot_topic" title="<?php echo_html(text("HotTopic")); ?>">&nbsp;</span><?php endif; ?>
                    
                    <?php if (!empty($tinfo["topic_in_favourites"])): ?><span class="topic_in_favourites" title="<?php echo_html(text("TopicIsInFavourites")); ?>">&nbsp;</span><?php endif; ?>
                    
                    <?php if(!empty($tinfo["is_blocked"])): ?><span class="blocked_in_topic" title="<?php echo_html(text("Blocked")); ?>">&nbsp;</span><?php endif; ?>
                    
                    <?php echo(build_post_pagination($topic_base_url, $tinfo, $not_preferred)); ?>
                    
                    <?php echo($only_found_appendix); ?>
                    
                    <?php
                    $topic_ignored = "";
                    $display = "style='display:none'";
                    if (!empty($tinfo["new_messages_count"])) {
                      $display = "";
                    }
                    if(!empty($tinfo["new_marker_ignored"])) $topic_ignored = "topic_ignored";
                    
                    $never_visited_topic = "";
                    if (!empty($tinfo["never_visited_topic"])) {
                      $never_visited_topic = "never_visited_topic";
                    }
                    ?>
                    <span class="new <?php echo($never_visited_topic); ?> new_messages_indicator <?php echo($topic_ignored); ?>" data-tid="<?php echo_html($tid); ?>" <?php echo($display); ?>>[<a href="<?php echo($topic_base_url); ?>&gotonew=1" rel="nofollow"><?php echo_html(text("new")); ?>:<span class='new_messages_count'><?php echo($tinfo["new_messages_count"]); ?></span></a>]</span>
                    
                    <?php if (!empty($tinfo["closed"])): ?>
                      <span class="closed">[<?php echo_html(text("closed")); ?>]</span>
                    <?php endif; ?>
                  </div>
                </td>
                
                <?php if (!empty($tinfo["moderators"])): ?>
                  <td>
                    <div class="smart_break" style="text-align:right">
                      <?php
                      $moderators = "";
                      foreach ($tinfo["moderators"] as $mid => $minfo) {
                        $online_status = "";
                        if (empty($settings["hide_online_status"]) && !empty($minfo["online"])) {
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
            <?php
            $not_preferred = "";
            if (!empty($_SESSION["preferred_forums"]) && empty($_SESSION["preferred_forums"][$tinfo["forum_id"]]) && $tinfo["forum_id"] != "private") {
              $not_preferred = "not_preferred";
            }
            ?>
            <a href="forum.php?fid=<?php echo_html($tinfo["forum_id"]); ?>" class="<?php echo($not_preferred); ?>"><?php echo_html($tinfo["forum_name"]); ?></a>
          </td>
          <td class="author_col">
            <div class="smart_break">
              <?php if (empty($tinfo["user_id"])): ?>                
                
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
                <span class="<?php if (!empty($tinfo["author_ignored"])) {
                  echo("not_preferred");
                } ?>"><?php echo($author_string); ?><?php echo($online_status); ?></span>
              <?php else: ?>
                
                <?php
                $online_status = "";
                if (empty($settings["hide_online_status"]) && !empty($tinfo["online"])) {
                  $online_status = "&nbsp;<span class='online_text'>✓</span>";
                }
                ?>
                <a href="view_profile.php?uid=<?php echo_html($tinfo["user_id"]); ?>" ><?php echo_html($tinfo["author"]); ?></a><?php echo($online_status); ?>
              <?php endif; ?>
            </div>
          </td>
          <td class="author_col">
            <div class="smart_break">
              <?php if (empty($tinfo["last_author_id"])): ?>
              
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
                <span class="<?php if (!empty($tinfo["last_author_ignored"])) {
                  echo("not_preferred");
                } ?>"><?php echo($author_string); ?><?php echo($online_status); ?></span>
              <?php else: ?>
                
                <?php
                $online_status = "";
                if (empty($settings["hide_online_status"]) && !empty($tinfo["last_author_online"])) {
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
  
  <?php if (count($topic_list) > 25): ?>

    <!-- BEGIN: forum_bar -->

    <div class="forum_bar">

    <div class="message_info_bar"><?php echo_html(text("Topics")); ?>: <span class='count_number'><?php echo_html(format_number(val_or_empty($pagination_info["total_count"]))); ?></span>,

        <?php echo(build_page_info($pagination_info, text("pages"))); ?>
        
        <?php if(!empty($pagination_info["ignored_count"])): ?>
        <?php if($fmanager->is_logged_in()): ?>
        <?php $search_url = str_replace("/&(author|author_mode)=[^&]+/", "", $_SERVER["REQUEST_URI"]) . "&author=" . xrawurlencode($fmanager->get_user_name()) . "&author_mode=ignoring"; ?>
        <?php else: ?>
        <span class="not_preferred"><?php echo_html(text("ignored")); ?>: <?php echo_html(format_number($pagination_info["ignored_count"])); ?></span>
        <?php endif; ?>
        <?php endif; ?>
        
        </div>
      
      <?php if (empty($pagination_info["all"]) && $pagination_info["page_count"] > 1): ?>
        <div class="navigator_bar"><?php echo(build_page_navigator($base_url . "&spage=$", $pagination_info)); ?></div>
      <?php endif; ?>

      <div class="forum_action_bar">
        <table>
          <tr>
            <td>
              <?php
              @include "forum_selector_inc.php";
              ?>
            </td>
            
            <?php if ($in_search): ?>
              <td>
                <input type="button" class="standard_button" value="<?php echo_html(text("NewSearch")); ?>" onclick="delay_redirect('<?php echo(empty($search_params) ? 'search.php' : 'search.php?' . $search_params . '&new_search=1'); ?>')">
              </td>
            <?php endif; ?>

          </tr>
        </table>
      </div>

      <div class="clear_both">
      </div>

      <div class="forum_name_bar <?php echo($wide_bar); ?>"><a href="forums.php"><?php echo_html(text("Forums")); ?></a>
        
        <?php
        $display = "style='display:none'";
        if (!empty($topics_with_new_count)) {
          $display = "";
        }
        ?>
        <span class="new topics_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span>

        <?php if(!reqvar_empty("favourite_posts_only") || !reqvar_empty("favourites_only")): ?>

          / <a href="favourites.php"><?php echo_html(text("Favourites")); ?></a> 

          <?php
          $display = "style='display:none'";
          if(!empty($favourites_with_new_count)) $display = "";
          ?>
          <span class="new favourites_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php?fid=-1"><?php echo_html(text("new")); ?>:<span class='favourites_with_new_count'><?php echo($favourites_with_new_count); ?></span></a>]</span>

        <?php elseif(!empty($fid) && !empty($forum_title)): ?>

          <?php
          $not_preferred = "";
          if(!empty($_SESSION["preferred_forums"]) && empty($_SESSION["preferred_forums"][$fid]) && !$is_private) $not_preferred = "not_preferred";
          ?>
          / <a href="forum.php?fid=<?php echo_html($fid_for_url); ?>" class="<?php echo($not_preferred); ?>"><?php echo_html($forum_title); ?></a>

          <?php
          $display = "style='display:none'";
          if(!empty($forum_data["topics_with_new_count"])) $display = "";
          ?>
          <span class="new forum_with_new_indicator" data-fid="<?php echo_html($fid_for_url); ?>" <?php echo($display); ?>>[<a href="<?php echo("new_messages.php?fid=" . $fid_for_url); ?>"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($forum_data["topics_with_new_count"]); ?></span></a>]</span>

        <?php endif; ?>

        / <span class="topic_title_main"><?php echo_html($search_title); ?></span>

      </div>

    </div>

    <!-- END: forum_bar -->
  
  <?php endif; // forum_bar ?>

<?php endif; // in_search ?>

<a id="new_search"></a>
<form action="search.php" id="main_form" method="get" onsubmit="Forum.show_sys_progress_indicator(true);">

  <div class="search_table_separator"></div>

  <table class="form_table search_table">

    <tr>
      <th colspan="2"><?php echo_html(text("SearchCriteria")); ?></th>
    </tr>

    <tr>
      <td>
        <div class="field_caption"><?php echo_html(text("SearchKeys")); ?>:</div>
        <input type="text" id="search_keys" autocomplete="off" name="search_keys" maxlength="500" value="<?php echo_html(reqvar("search_keys")); ?>">
        <div class="field_comment"><?php echo_html(text("SearchComment")); ?></div>

        <br>

     <table class="checkbox_table">
     <tr>
       <td>
      <input type="checkbox" value="1" id="with_morphology" name="with_morphology" <?php echo_html(checked(reqvar("with_morphology"))); ?>> 
       </td>
       <td>
      <label for="with_morphology"><?php echo_html(text("SearchWithMorphology")); ?></label>
       </td>
     </tr>
     <tr>
       <td>
      <input type="checkbox" value="1" id="topics_only" name="topics_only" <?php echo_html(checked(reqvar("topics_only"))); ?> onchange="Forum.invert_pair_checkbox(this, 'posts_only');"> 
       </td>
       <td>
      <label for="topics_only"><?php echo_html(text("SearchTopicNameOnly")); ?></label>
       </td>
     </tr>
     <tr>
       <td>
      <input type="checkbox" value="1" id="posts_only" name="posts_only" <?php echo_html(checked(reqvar("posts_only"))); ?> onchange="Forum.invert_pair_checkbox(this, 'topics_only');"> 
       </td>
       <td>
      <label for="posts_only"><?php echo_html(text("SearchPostsOnly")); ?></label>
       </td>
     </tr>
     </table>
     <br>

     <table class="checkbox_table">
     <tr>
       <td>
      <input type="checkbox" value="1" id="hot_topics" name="hot_topics" <?php echo_html(checked(reqvar("hot_topics"))); ?>> 
       </td>
       <td>
      <label for="hot_topics"><?php echo_html(text("HotTopics")); ?></label>
       </td>
     </tr>
     <tr>
       <td>
      <input type="checkbox" value="1" id="polls_only" name="polls_only" <?php echo_html(checked(reqvar("polls_only"))); ?>> 
       </td>
       <td>
      <label for="polls_only"><?php echo_html(text("SearchPollsOnly")); ?></label>
       </td>
     </tr>
     <tr>
       <td>
      <input type="checkbox" value="1" id="favourites_only" name="favourites_only" <?php echo_html(checked(reqvar("favourites_only"))); ?> onchange="Forum.invert_pair_checkbox(this, 'favourite_posts_only');"> 
       </td>
       <td>
      <label for="favourites_only"><?php echo_html(text("SearchTopicFavourites")); ?></label>
       </td>
     </tr>
     <tr>
       <td>
      <input type="checkbox" value="1" id="favourite_posts_only" name="favourite_posts_only" <?php echo_html(checked(reqvar("favourite_posts_only"))); ?> onchange="Forum.invert_pair_checkbox(this, 'favourites_only');"> 
       </td>
       <td>
      <label for="favourite_posts_only"><?php echo_html(text("SearchPostFavourites")); ?></label>
       </td>
     </tr>
     <tr>
       <td>
      <input type="checkbox" value="1" id="include_ignored" name="include_ignored" <?php echo_html(checked(reqvar("include_ignored"))); ?>> 
       </td>
       <td>
      <label for="include_ignored"><?php echo_html(text("SearchIgnoredToo")); ?></label>
       </td>
     </tr>
     <tr>
       <td>
      <input type="checkbox" value="1" id="include_deleted" name="include_deleted" <?php echo_html(checked(reqvar("include_deleted"))); ?> onchange="Forum.invert_pair_checkbox(this, 'deleted_only'); Forum.invert_pair_checkbox(this, 'deleted_topics_only');"> 
       </td>
       <td>
      <label for="include_deleted"><?php echo_html(text("SearchDeletedToo")); ?></label>
       </td>
     </tr>
     <tr>
       <td>
      <input type="checkbox" value="1" id="deleted_only" name="deleted_only" <?php echo_html(checked(reqvar("deleted_only"))); ?> onchange="Forum.invert_pair_checkbox(this, 'include_deleted');"> 
       </td>
       <td>
      <label for="deleted_only"><?php echo_html(text("SearchDeletedOnly")); ?></label>
       </td>
     </tr>
     <?php if($fmanager->is_moderator()): ?>
     <tr>
       <td>
      <input type="checkbox" value="1" id="deleted_topics_only" name="deleted_topics_only" <?php echo_html(checked(reqvar("deleted_topics_only"))); ?> onchange="Forum.invert_pair_checkbox(this, 'include_deleted');"> 
       </td>
       <td>
      <label for="deleted_topics_only"><?php echo_html(text("SearchDeletedTopicsOnly")); ?></label>
       </td>
     </tr>
     <?php endif; ?>
     </table>

      <br>

     <table class="checkbox_table">
     <tr>
       <td>
      <input type="checkbox" value="1" id="has_attachment" name="has_attachment" <?php echo_html(checked(reqvar("has_attachment"))); ?>> 
       </td>
       <td>
      <label for="has_attachment"><?php echo_html(text("SearchAttachmentsOnly")); ?></label>
       </td>
     </tr>
     <tr>
       <td>
      <input type="checkbox" value="1" id="has_picture" name="has_picture" <?php echo_html(checked(reqvar("has_picture"))); ?>> 
       </td>
       <td>
      <label for="has_picture"><?php echo_html(text("SearchPicturesOnly")); ?></label>
       </td>
     </tr>
     <tr>
       <td>
      <input type="checkbox" value="1" id="has_video" name="has_video" <?php echo_html(checked(reqvar("has_video"))); ?>> 
       </td>
       <td>
      <label for="has_video"><?php echo_html(text("SearchVideosOnly")); ?></label>
       </td>
     </tr>
     <tr>
       <td>
      <input type="checkbox" value="1" id="has_audio" name="has_audio" <?php echo_html(checked(reqvar("has_audio"))); ?>> 
       </td>
       <td>
      <label for="has_audio"><?php echo_html(text("SearchAudioOnly")); ?></label>
       </td>
     </tr>
     <tr>
       <td>
      <input type="checkbox" value="1" id="has_adult" name="has_adult" <?php echo_html(checked(reqvar("has_adult"))); ?>> 
       </td>
       <td>
      <label for="has_adult"><?php echo_html(text("SearchAdultOnly")); ?></label>
       </td>
     </tr>
     <tr>
       <td>
      <input type="checkbox" value="1" id="has_link" name="has_link" <?php echo_html(checked(reqvar("has_link"))); ?>> 
       </td>
       <td>
      <label for="has_link"><?php echo_html(text("SearchLinksOnly")); ?></label>
       </td>
     </tr>
     <tr>
       <td>
      <input type="checkbox" value="1" id="has_code" name="has_code" <?php echo_html(checked(reqvar("has_code"))); ?>> 
       </td>
       <td>
      <label for="has_code"><?php echo_html(text("SearchCodesOnly")); ?></label>
       </td>
     </tr>
     </table>

      <br>
      
     <table class="checkbox_table">
     <tr>
       <td>
      <input type="radio" id="wrote_post" name="author_mode" value="wrote_post" checked> 
       </td>
       <td>
      <label for="wrote_post"><?php echo_html(text("AuthorWrotePost")); ?></label>
     </tr>
     <tr>
       <td>
      <input type="radio" id="last_posts" name="author_mode" value="last_posts" <?php echo_html(reqvar_radio_selected("author_mode", "last_posts")); ?>> 
       </td>
       <td>
      <label for="last_posts"><?php echo_html(text("SearchAuthorLastMessages")); ?></label>
     </tr>
     
     <?php if(!empty($settings["rates_active"])): ?>
     <tr>
       <td>
      <input type="radio" id="author_likes" name="author_mode" value="author_likes" <?php echo_html(reqvar_radio_selected("author_mode", "author_likes")); ?>> 
       </td>
       <td>
      <label for="author_likes"><?php echo_html(text("AuthorHasLiked")); ?></label>
       </td>
     </tr>
     <?php if(!empty($settings["dislikes_active"])): ?>
     <tr>
       <td>
      <input type="radio" id="author_dislikes" name="author_mode" value="author_dislikes" <?php echo_html(reqvar_radio_selected("author_mode", "author_dislikes")); ?>> 
       </td>
       <td>
      <label for="author_dislikes"><?php echo_html(text("AuthorHasDisliked")); ?></label>
       </td>
     </tr>
     <?php endif; ?>
     <tr>
       <td>
      <input type="radio" id="author_liked" name="author_mode" value="author_liked" <?php echo_html(reqvar_radio_selected("author_mode", "author_liked")); ?>> 
       </td>
       <td>
      <label for="author_liked"><?php echo_html(text("AuthorWasLiked")); ?></label>
       </td>
     </tr>
     <?php if(!empty($settings["dislikes_active"])): ?>
     <tr>
       <td>
      <input type="radio" id="author_disliked" name="author_mode" value="author_disliked" <?php echo_html(reqvar_radio_selected("author_mode", "author_disliked")); ?>> 
       </td>
       <td>
      <label for="author_disliked"><?php echo_html(text("AuthorWasDisliked")); ?></label>
       </td>
     </tr>
     <?php endif; ?>
     <?php endif; ?>

     </table>
     <br>
     
     <table class="checkbox_table">
     <tr>
       <td>
      <input type="radio" id="created_topic" name="author_mode" value="created_topic" <?php echo_html(reqvar_radio_selected("author_mode", "created_topic")); ?>> 
       </td>
       <td>
      <label for="created_topic"><?php echo_html(text("AuthorCreatedTopic")); ?></label>
       </td>
     </tr>
     <tr>
       <td>
      <input type="radio" id="last_topics" name="author_mode" value="last_topics" <?php echo_html(reqvar_radio_selected("author_mode", "last_topics")); ?>> 
       </td>
       <td>
      <label for="last_topics"><?php echo_html(text("SearchAuthorLastTopics")); ?></label>
       </td>
     </tr>
     <tr>
       <td>
      <input type="radio" id="participating" name="author_mode" value="participating" <?php echo_html(reqvar_radio_selected("author_mode", "participating")); ?>> 
       </td>
       <td>
      <label for="participating"><?php echo_html(text("AuthorParticipatedInTopic")); ?></label>
       </td>
     </tr>
     <tr>
       <td>
      <input type="radio" id="ignoring" name="author_mode" value="ignoring" <?php echo_html(reqvar_radio_selected("author_mode", "ignoring")); ?>> 
       </td>
       <td>
      <label for="ignoring"><?php echo_html(text("AuthorIgnoringTopic")); ?></label>
       </td>
     </tr>
     <tr>
       <td>
      <input type="radio" id="moderating" name="author_mode" value="moderating" <?php echo_html(reqvar_radio_selected("author_mode", "moderating")); ?>> 
       </td>
       <td>
      <label for="moderating"><?php echo_html(text("AuthorModeratingTopic")); ?></label>
       </td>
     </tr>
     </table>
      
      <br>

        <div class="field_caption"><?php echo_html(text("Author")); ?>:</div>
        
        <table class="aux_table">
        <tr>
        <td><input type="text" id="author" name="author" maxlength="250" value="<?php echo_html(reqvar("author")); ?>" autocomplete="off" onkeypress="return lookup_handle_enter(this.id, event)" onkeyup="return lookup_entries('search_authors', this, event)" onblur="lookup_delayed_hide('author')"></td>
        <td><button class="me_button" onclick="set_to_me();" type="button"><?php echo_html(text("Me")); ?></button></td>
        </tr>
        </table>

        <div class="field_lookup_area" style="display:none">
          <select id="author_lookup" size="10"
              onclick="if(!mustAdjustMultiSelect()) { lookup_apply_selection('author') }"
              onchange="if(mustAdjustMultiSelect()) { lookup_apply_selection_if_active('author') }"

              onkeypress="return lookup_handle_enter('author', event)" onblur="user_esc_handler()"
          >
          </select>
        </div>

        <div class="field_comment"><?php echo_html(text("SearchAuthorComment")); ?></div>

        <br>

        <div class="field_caption"><?php echo_html(text("DateRange")); ?>:</div>
        <table class="date_block">
          <tr>
            <td><input type="text" autocomplete="off" id="start_date" name="start_date" value="<?php echo_html(reqvar("start_date")); ?>"></td>
            <td></td>
            <td><input type="text" autocomplete="off" id="end_date" name="end_date" value="<?php echo_html(reqvar("end_date")); ?>"></td>
          </tr>
        </table>
        <br>
        
        <?php if ($fmanager->is_moderator()): ?>
          <div class="field_caption"><?php echo_html(text("IPAddressOrFingerprint")); ?>:</div>
          <input type="text" id="ip" name="ip" maxlength="250" value="<?php echo_html(reqvar("ip")); ?>">
          <br><br>
        <?php endif; ?>

         <table class="checkbox_table">
         <tr>
         <td>
        <input type="checkbox" value="1" id="post_list" name="post_list" <?php echo_html(checked(reqvar("post_list"))); ?>> 
         </td>
         <td>
        <label for="post_list"><?php echo_html(text("SearchResultsAsPostList")); ?></label>
         </td>
         </tr>
         </table>

      </td>
      <td class="search_forum_area">
        
        <div class="field_caption"><?php echo_html(text("Topic")); ?>:</div>
        <input type="hidden" id="tid" name="tid" value="<?php echo_html(reqvar("tid")); ?>">
        <input type="text" id="topic_name" value="<?php echo_html(val_or_empty($topic_name)); ?>" autocomplete="off" onkeypress="return lookup_handle_enter(this.id, event)" onkeyup="reset_topic_on_search(); return lookup_entries('search_topics', this, event);" onblur="reset_topic_if_not_found(); lookup_delayed_hide('topic_name');">

        <div class="field_lookup_area topic_lookup_area" style="display:none">
          <select id="topic_name_lookup" size="10"
              onclick="if(!mustAdjustMultiSelect()) { lookup_apply_selection('topic_name') }"
              onchange="if(mustAdjustMultiSelect()) { lookup_apply_selection_if_active('topic_name') }"

              onkeypress="return lookup_handle_enter('topic_name', event)" onblur="user_esc_handler()"
          >
          </select>
        </div>

        <div class="field_caption"><?php echo_html(text("Forum")); ?>:</div>
        <select class="search_forum_select multiple_choice <?php if (!empty($user_tags)) {
          echo("tags_exist");
        } ?>" multiple name="forums[]" id="forums">
          
          <?php if ($fmanager->is_logged_in() && !$fmanager->is_master_admin()): ?>
            <option value="private" <?php echo_html(reqvar_selected("forums", "private")); ?>><?php echo_html(text("PrivateTopics")); ?></option>
          <?php endif; ?>
          
          <?php foreach ($all_forum_list as $fid => $fdata): ?>
            <option value="<?php echo_html($fid); ?>" <?php echo_html(reqvar_selected("forums", $fid)); ?>><?php echo_html($fdata["name"]); ?></option>
          <?php endforeach; ?>

        </select>
        
        <?php if (!empty($user_tags)): ?>
          <div class="field_caption"><?php echo_html(text("Tags")); ?>:</div>

           <table class="checkbox_table">
           <tr>
             <td>
            <input type="checkbox" value="1" id="conjunct_tags" name="conjunct_tags" <?php echo_html(checked(reqvar("conjunct_tags"))); ?>> 
             </td>
             <td>
            <label for="conjunct_tags"><?php echo_html(text("ConjuntTags")); ?></label>
             </td>
           </tr>
           <tr>
             <td>
             </td>
             <td>
             </td>
           </tr>
           </table>

          <select class="search_tags_select multiple_choice" multiple name="tags[]" id="tags">
            
            <?php foreach ($user_tags as $tgid => $tgname): ?>
              <option value="<?php echo_html($tgid); ?>" <?php echo_html(reqvar_selected("tags", $tgid)); ?>><?php echo_html($tgname); ?></option>
            <?php endforeach; ?>

          </select>
        <?php endif; ?>
      </td>
    </tr>

    <tr>
      <td colspan="2" class="button_area">
        <input type="hidden" name="do_search" value="1">
        <div class="left_buttons">
          <input type="button" class="standard_button" value="<?php echo_html(text("Reset")); ?>" onclick="reset_search_form(this.form);">
        </div>
        <div class="right_buttons">
          <input type="submit" class="standard_button send_button" value="<?php echo_html(text("DoSearch")); ?>">
        </div>
        <div class="clear_both">
        </div>
      </td>
    </tr>

  </table>

</form>

</div>
