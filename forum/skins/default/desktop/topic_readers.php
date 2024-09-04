<script>
function expand_statistics_list(elm)
{
  var parent_table = elm.parentNode;
  if(!parent_table) return;
  parent_table = parent_table.parentNode;
  if(!parent_table) return;
  parent_table = parent_table.parentNode;
  if(!parent_table) return;
  
  var elms = parent_table.getElementsByClassName("statistics_row_hidden");
  for(var i = elms.length-1; i >= 0; i--)
  {
    elms[i].classList.remove("statistics_row_hidden");
  }
  
  elm = elm.parentNode;
  if(elm) elm = elm.parentNode;
  if(elm) elm = elm.style.display = "none";
}
</script>

<div class="content_area">

<!-- BEGIN: forum_bar -->

<div class="forum_bar">

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

<?php
$not_preferred = "";
if(!empty($_SESSION["ignored_forums"][$fid]) && empty($topic_data["is_private"])) $not_preferred = "not_preferred";
?>
/ <a href="forum.php?fid=<?php echo_html($fid_for_url); ?>" class="<?php echo($not_preferred); ?>"><?php echo_html($forum_title); ?></a>

<?php if(!empty($topic_data["is_private"])):
$display = "style='display:none'";
if(!empty($private_topics_with_new_count)) $display = "";
?>
<span class="new private_topics_with_new_indicator" <?php echo($display); ?>>[<a href="<?php echo("new_messages.php?fid=private"); ?>"><?php echo_html(text("new")); ?>:<span class='private_topics_with_new_count'><?php echo($private_topics_with_new_count); ?></span></a>]</span>
<?php else:
$display = "style='display:none'";
if(!empty($forum_data["topics_with_new_count"])) $display = "";
?>
<span class="new forum_with_new_indicator <?php echo($not_preferred); ?>" data-fid="<?php echo_html($fid_for_url); ?>" <?php echo($display); ?>>[<a href="<?php echo("new_messages.php?fid=" . $fid); ?>"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($forum_data["topics_with_new_count"]); ?></span></a>]</span>
<?php endif; ?>

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

/ 

<?php if(val_or_empty($topic_data["profiled_topic"]) == 1): ?>
<span class="topic_type_indicator"><?php echo_html(text("Dedicated")); ?>:</span>
<?php elseif(val_or_empty($topic_data["profiled_topic"]) == 2): ?>
<span class="topic_type_indicator"><?php echo_html(text("Blog")); ?>:</span>
<?php endif; ?>

<a href="<?php echo($base_url); ?>&gotonew=1"><?php echo_html($topic_title); ?></a>

<?php
$display = "style='display:none'";
if(!empty($topic_data["new_messages_count"])) $display = "";
?>
<span class="new new_messages_indicator" <?php echo($display); ?>>[<a href="<?php echo($base_url); ?>&gotonew=1" rel="nofollow" onclick="return exec_load_new_posts(-1, this.href)"><?php echo_html(text("new")); ?>:<span class='new_messages_count'><?php echo(val_or_empty($topic_data["new_messages_count"])); ?></span></a>]</span>

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

/ <span class="topic_title_main"><?php echo_html(text("ReadingTopic")); ?></span>

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

</tr>
</table>
</div>

<div class="clear_both">
</div>

</div>

<!-- END: forum_bar -->

<h3 class="profile_caption"><?php echo_html(text("ReadingTopic") . ": " . $topic_title); ?></h2>

<table class="topic_statistic_table">

<tr>
<th><?php echo_html(text("Member")); ?></th>
<th><?php echo_html(text("DateTime")); ?></th>
</tr>

<?php 
$rowcount = count($all_topic_readers);
$i = 0;
$row_class = "";
?>

<?php if($rowcount == 0 || !empty($settings["hide_online_status"])): ?> 

<tr>
<td colspan="2">&nbsp;</td>
</tr>

<?php else: ?>

<?php foreach($all_topic_readers as $uinfo): 

if(!empty($uinfo["id"]))
{
  $uname = "<a href='view_profile.php?uid=$uinfo[id]'>" . escape_html($uinfo["name"]) . "</a>";
}
else
{
  if(!empty($uinfo["bot"]))
    $uname = "<a class='bot_link' href='view_bot_profile.php?bot=" . xrawurlencode($uinfo["name"]) . "'>" . escape_html($uinfo["name"]) . "</a>";
  elseif(!empty($uinfo["is_anonym"]))
    $uname = "<i><a class='guest_link' href='view_anonym_activity.php'>" . escape_html($uinfo["name"]) . "</a></i>";
  elseif($uinfo["name"] == "admin")
    $uname = "<a class='admin_link' href='view_guest_profile.php?guest=" . xrawurlencode($uinfo["name"]) . "'>" . escape_html(text("MasterAdministrator")) . "</a>";
  else  
    $uname = "<a class='guest_link' href='view_guest_profile.php?guest=" . xrawurlencode($uinfo["name"]) . "'>" . escape_html($uinfo["name"]) . "</a>";
}

if(empty($settings["hide_online_status"]) && (!empty($uinfo["online"]) || !empty($online_users["g_" . $uinfo["name"]])))
{
  $uname .= "&nbsp;<span class='online_text'>✓</span>";
}
?>

<tr class="<?php echo($row_class); ?>">
<td><div class="smart_break"><?php echo($uname); ?></div></td>
<td class="date_col"><?php echo_html($uinfo["time_ago"]); ?></td>
</tr>

<?php if($rowcount > 25 && $i == 20): 
$row_class = "statistics_row_hidden";
?>
<tr>
<td><div class="statistics_list_expander" onclick="expand_statistics_list(this)">...</div></td>
<td></td>
</tr>
<?php endif; ?>

<?php 
$i++;
endforeach; 
?>

<?php endif; ?>

</table>
<br><br>

<!-- BEGIN: forum_bar -->

<?php if($rowcount > 20): ?>

<div class="forum_bar">

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

<?php
$not_preferred = "";
if(!empty($_SESSION["ignored_forums"][$fid]) && empty($topic_data["is_private"])) $not_preferred = "not_preferred";
?>
/ <a href="forum.php?fid=<?php echo_html($fid_for_url); ?>" class="<?php echo($not_preferred); ?>"><?php echo_html($forum_title); ?></a>

<?php if(!empty($topic_data["is_private"])):
$display = "style='display:none'";
if(!empty($private_topics_with_new_count)) $display = "";
?>
<span class="new private_topics_with_new_indicator" <?php echo($display); ?>>[<a href="<?php echo("new_messages.php?fid=private"); ?>"><?php echo_html(text("new")); ?>:<span class='private_topics_with_new_count'><?php echo($private_topics_with_new_count); ?></span></a>]</span>
<?php else:
$display = "style='display:none'";
if(!empty($forum_data["topics_with_new_count"])) $display = "";
?>
<span class="new forum_with_new_indicator <?php echo($not_preferred); ?>" data-fid="<?php echo_html($fid_for_url); ?>" <?php echo($display); ?>>[<a href="<?php echo("new_messages.php?fid=" . $fid); ?>"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($forum_data["topics_with_new_count"]); ?></span></a>]</span>
<?php endif; ?>

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

/ 

<?php if(val_or_empty($topic_data["profiled_topic"]) == 1): ?>
<span class="topic_type_indicator"><?php echo_html(text("Dedicated")); ?>:</span>
<?php elseif(val_or_empty($topic_data["profiled_topic"]) == 2): ?>
<span class="topic_type_indicator"><?php echo_html(text("Blog")); ?>:</span>
<?php endif; ?>

<a href="<?php echo($base_url); ?>&gotonew=1"><?php echo_html($topic_title); ?></a>

<?php
$display = "style='display:none'";
if(!empty($topic_data["new_messages_count"])) $display = "";
?>
<span class="new new_messages_indicator" <?php echo($display); ?>>[<a href="<?php echo($base_url); ?>&gotonew=1" rel="nofollow" onclick="return exec_load_new_posts(-1, this.href)"><?php echo_html(text("new")); ?>:<span class='new_messages_count'><?php echo(val_or_empty($topic_data["new_messages_count"])); ?></span></a>]</span>

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

/ <span class="topic_title_main"><?php echo_html(text("ReadingTopic")); ?></span>

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


</tr>
</table>
</div>

<div class="clear_both">
</div>

</div>

<?php endif; ?>

<!-- END: forum_bar -->

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

  $bcnt = 0;
  foreach($topic_readers as $ouid => $uinfo)
  {
    if(!empty($uinfo["bot"])) $bcnt++;
  }
  if (!empty($rcnt)) $rcnt = ($rcnt - $bcnt);

  if (!empty($bcnt)) $rcnt .= "/" . $bcnt;

  $treaders = "<a href='topic_readers.php?fid=" . $fid_for_url . "&tid=" . $tid . "' class='topic_readers'>" . escape_html(text("ReadingTopic")) . "</a> ($rcnt): ";

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
      $treaders .= "<span class='user_name'><i><a class='guest_link' href='view_anonym_activity.php'>" . escape_html($uinfo["name"]) . "</a></i>$appendix</span>, ";
    elseif($uinfo["name"] == "admin")
      $treaders .= "<span class='user_name'><a class='admin_link' href='view_guest_profile.php?guest=" . xrawurlencode($uinfo["name"]) . "'>" . escape_html(text("MasterAdministrator")) . "</a>$appendix</span>, ";
    else
      $treaders .= "<span class='user_name'><a class='guest_link' href='view_guest_profile.php?guest=" . xrawurlencode($uinfo["name"]) . "'>" . escape_html($uinfo["name"]) . "</a>$appendix</span>, ";
  }

  $treaders = trim($treaders, ", ");

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


