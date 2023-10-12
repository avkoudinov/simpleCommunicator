<script>
var selected_authors = {};

function hide_author_actions_menu()
{
  Forum.hide_sys_bubblebox();

  var elms = document.getElementsByClassName("popup_author_actions_menu");
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

function show_author_actions_menu(aname)
{
  hide_author_actions_menu();

  var count = Forum.objectPropertiesCount(selected_authors);

  if(count == 0) return false;

  var elms = document.getElementsByClassName("selected_authors_count");
  for(var i = 0; i < elms.length; i++)
  {
    elms[i].innerHTML = count;
  }

  var elm = document.getElementById("popup_author_actions_menu_" + aname);
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
    author = elms[i].getAttribute("data-author");
    if(!author) continue;
    
    if(!elms[i].parentNode.classList.contains('selected_row'))
    {
      elms[i].parentNode.classList.add('selected_row');
      selected_authors[author] = 1;
    }
  }
  
  var count = Forum.objectPropertiesCount(selected_authors);

  if(count == 0) return false;

  var elms = document.getElementsByClassName("selected_authors_count");
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
    author = elms[i].getAttribute("data-author");
    if(!author) continue;
    
    if(elms[i].parentNode.classList.contains('selected_row'))
    {
      elms[i].parentNode.classList.remove('selected_row');
      delete selected_authors[author];
    }
  }
  
  hide_author_actions_menu();
  
  return false;
}

function toggle_all_selection(th)
{
  var selected = false;
  var author = "";
  var first_author = "";
  
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
    author = elms[i].getAttribute("data-aname");
    if(!author) continue;
    
    if(!first_author) first_author = author;
    
    author = elms[i].getAttribute("data-author");
    if(!author) continue;

    if(selected)
    {
      elms[i].parentNode.classList.add('selected_row');
      selected_authors[author] = 1;
    }
    else
    {
      elms[i].parentNode.classList.remove('selected_row');
      delete selected_authors[author];
    }
  }
  
  if(first_author) show_author_actions_menu(first_author);
}

function toggle_selection(td, author)
{
  if(td.parentNode.classList.contains('selected_row'))
  {
    td.parentNode.classList.remove('selected_row');
    delete selected_authors[author];
  }
  else
  {
    td.parentNode.classList.add('selected_row');
    selected_authors[author] = 1;
  }
}

function confirm_action(msg, params)
{
  hide_author_actions_menu();

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
  hide_author_actions_menu();
  
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
  for(var t in selected_authors)
  {
    if(!Object.prototype.hasOwnProperty.call(selected_authors, t)) continue;

    action_ajax.setPOST("authors[" + (i++) + "]", t);
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
<a href="subscription.php" onclick='return confirm_action("<?php echo_js(text("MsgConfirmMarkAuthorsRead"), true); ?>", { mark_read_action: "mark_subscriptions_read" });'><?php echo_html(text("MarkRead")); ?></a>
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

/ <span class="topic_title_main"><?php echo_html(text("Subscription")); ?></span>
</div>

<!-- END: forum_bar -->

<table class="topic_table">
<tr>
<th id="all_checkbox_selector" class="all_checkbox_selector" onclick="toggle_all_selection(this)"><div>&nbsp;</div></th>

<th><?php echo_html(text("Subscription")); ?></th>
</tr>

<?php if(empty($subscribed_authors)): ?>

<tr>
<td colspan="2" class="table_message"><?php echo_html(text("UsersNotFound")); ?></td>
</tr>

<?php else: ?>

<tr>
<td></td>
<td>
  <div class="smart_break">
    <?php echo_html(text("AllAuthors")); ?>
  </div>
  <div class="forum_info">
  <?php
  $display = "style='display:none'";
  if(!empty($subscription_authors_new_messages_count)) $display = "";
  ?>
  <a href="search.php?do_search=1&author=<?php echo(xrawurlencode(text("Subscription"))); ?>&author_mode=last_posts&mark_read=1"><?php echo_html(text("LastMessages")); ?></a><span class="new subscription_new_indicator" <?php echo($display); ?>>&nbsp;[<a href="search.php?do_search=1&author=<?php echo(xrawurlencode(text("Subscription"))); ?>&author_mode=last_posts&unseen=1&mark_read=1"><?php echo_html(text("new")); ?>:<span class='subscription_authors_new_messages_count'><?php echo($subscription_authors_new_messages_count); ?></span></a>]</span><br>

  <?php
  $display = "style='display:none'";
  if(!empty($subscription_authors_new_topics_count)) $display = "";
  ?>
  <a href="search.php?do_search=1&author=<?php echo(xrawurlencode(text("Subscription"))); ?>&author_mode=last_topics&mark_read=1"><?php echo_html(text("LastTopics")); ?></a><span class="new subscription_topics_new_indicator" <?php echo($display); ?>>&nbsp;[<a href="search.php?do_search=1&author=<?php echo(xrawurlencode(text("Subscription"))); ?>&author_mode=last_topics&unseen=1&mark_read=1"><?php echo_html(text("new")); ?>:<span class='subscription_authors_new_topics_count'><?php echo($subscription_authors_new_topics_count); ?></span></a>]</span>
  </div>

  <div class="navigation_arrows_right">
  <div class="scroll_up" onclick="window.scrollTo(0, 0);"></div>
  <div class="scroll_down" onclick="window.scrollTo(0, 1000000);"></div>
  </div>
</td>
</tr>

<?php foreach($subscribed_authors as $user_data): 
  $aname = md5($user_data["user_name"]);
  ?>

<tr>
<td class="checkbox_selector" data-aname="<?php echo_html($aname); ?>" data-author="<?php echo_html($user_data["user_name"]); ?>" onclick="toggle_selection(this, '<?php echo_html($user_data["user_name"]); ?>'); show_author_actions_menu('<?php echo_html($aname); ?>')"></td>
<td>
  <div style="position:relative;" id="popup_container_<?php echo_html($aname); ?>">
  <div class="popup_author_actions_menu" id="popup_author_actions_menu_<?php echo_html($aname); ?>">

      <div style="position: absolute;right:2px;top:2px;cursor:pointer" onclick="hide_author_actions_menu();"><img src="<?php echo($view_path); ?>images/cross.png" alt="<?php echo_html(text("Close")); ?>"></div>

      <span style="font-weight: bold"><?php echo_html(text("MsgTopicsSelected")); ?>: <span class="selected_authors_count">0</span></span>
      
      <br> <a href="subscription.php" onclick='return select_all()'><?php echo_html(text("SelectAll")); ?></a>
      <br> <a href="subscription.php" onclick='return unselect_all()'><?php echo_html(text("ResetSelection")); ?></a>
      
      <br> <a href="subscription.php" onclick='return confirm_action("<?php echo_js(text("MsgConfirmUnubscribeFromUsers"), true); ?>", { unsubscribe_from_authors: 1 })'><?php echo_html(text("UnsubscribeFromUser")); ?></a>
      
      <br> <a href="subscription.php" onclick='return do_action({ mark_read_action: "mark_subscriptions_read" })'><?php echo_html(text("MarkRead")); ?></a>

  </div>
  </div>

  <div class="smart_break">
    <?php if(empty($user_data["uid"])): ?>

    <?php
    $online_status = "";
    if(empty($settings["hide_online_status"]) && !empty($online_users["g_" . $user_data["user_name"]]))
    {
      $online_status = "&nbsp;<span class='online_text'>✓</span>";
    }

    if($user_data["user_name"] == "admin")
      $author_string = "<a class='admin_link' href='view_guest_profile.php?guest=" . xrawurlencode($user_data["user_name"]) . "'>" . escape_html(text("MasterAdministrator")) . "</a>";
    else  
      $author_string = "<a class='guest_link' href='view_guest_profile.php?guest=" . xrawurlencode($user_data["user_name"]) . "'>" . escape_html($user_data["user_name"]) . "</a>";
    ?>
    <span><?php echo($author_string); ?><?php echo($online_status); ?></span>
    <?php else: ?>

    <?php
    $online_status = "";
    if(empty($settings["hide_online_status"]) && !empty($user_data["online"]))
    {
      $online_status = "&nbsp;<span class='online_text'>✓</span>";
    }
    ?>
    <a href="view_profile.php?uid=<?php echo_html($user_data["uid"]); ?>" target="_blank"><?php echo_html($user_data["user_name"]); ?></a><?php echo($online_status); ?>
    <?php endif; ?>
  </div>
  <div class="forum_info">
  <?php echo_html(text("SubscriptionDate")); ?>: 
  <span class="number"><?php echo_html($user_data["tm"]); ?></span>
  <br>
  
  <?php echo_html(text("LastMessage")); ?>: 
  <span class="number"><?php echo_html($user_data["last_post_date"]); ?></span>
  <br>

  <?php echo_html(text("ViewDate")); ?>: 
  <span class="number"><?php echo_html($user_data["last_view"]); ?></span>
  <br>
  <?php
  $display = "style='display:none'";
  if(!empty($user_data["new_messages"])) $display = "";
  ?>
  <a href="search.php?do_search=1&author=<?php echo(xrawurlencode($user_data["user_name"])); ?>&author_mode=last_posts&mark_read=1"><?php echo_html(text("LastMessages")); ?></a><span class="new subscription_author_new_messages" data-author="<?php echo_html($user_data["user_name"]); ?>" <?php echo($display); ?>>&nbsp;[<a href="search.php?do_search=1&author=<?php echo(xrawurlencode($user_data["user_name"])); ?>&author_mode=last_posts&unseen=1&mark_read=1"><?php echo_html(text("new")); ?>:<span class='new_messages_count'><?php echo(val_or_empty($user_data["new_messages"])); ?></span></a>]</span><br>
  <?php
  $display = "style='display:none'";
  if(!empty($user_data["new_topics"])) $display = "";
  ?>
  <a href="search.php?do_search=1&author=<?php echo(xrawurlencode($user_data["user_name"])); ?>&author_mode=last_topics&mark_read=1"><?php echo_html(text("LastTopics")); ?></a><span class="new subscription_author_new_topics" data-author="<?php echo_html($user_data["user_name"]); ?>" <?php echo($display); ?>>&nbsp;[<a href="search.php?do_search=1&author=<?php echo(xrawurlencode($user_data["user_name"])); ?>&author_mode=last_topics&unseen=1&mark_read=1"><?php echo_html(text("new")); ?>:<span class='new_messages_count'><?php echo(val_or_empty($user_data["new_topics"])); ?></span></a>]</span>
  </div>

  <div class="navigation_arrows_right">
  <div class="scroll_up" onclick="window.scrollTo(0, 0);"></div>
  <div class="scroll_down" onclick="window.scrollTo(0, 1000000);"></div>
  </div>
</td>
</tr>

<?php endforeach; ?>

<?php endif; ?>

</table>

<?php if(!empty($subscribed_authors) && count($subscribed_authors) > 1): ?>

<!-- BEGIN: forum_bar -->

<div class="forum_bar">
<a href="forums.php"><?php echo_html(text("Forums")); ?></a>

<?php
$display = "style='display:none'";
if(!empty($topics_with_new_count)) $display = "";
?>
<span class="new topics_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span> 

/ <span class="topic_title_main"><?php echo_html(text("Subscription")); ?></span>
</div>

<!-- END: forum_bar -->

<?php endif; ?>

