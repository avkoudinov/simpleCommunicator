<script>
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
var in_search = 1;
var last_author = "<?php echo_js($fmanager->get_user_name()); ?>";
var first_message = "<?php echo($first_message); ?>";
var last_message = "<?php echo($last_message); ?>";
var first_new_message = last_message;
var posts_per_page = <?php echo($fmanager->get_posts_per_page()); ?>;
var loaded_message_count = <?php echo($pagination_info["loaded_message_count"]); ?>;

<?php if(!reqvar_empty("news_digest") || !reqvar_empty("rate_statistics")): ?>
var is_last_page = <?php echo($pagination_info["page"] == $pagination_info["page_count"] < 1 ? "1" : "0") ?>;
<?php else: ?>
var is_last_page = <?php echo($pagination_info["total_count"] - $pagination_info["last_message_position"] < 1 ? "1" : "0") ?>;
<?php endif; ?>

var all_page_mode = <?php echo($pagination_info["mode"] == "all" ? "1" : "0"); ?>;
var filtered_comment_mode = 0;

var has_auto_saved_message = <?php echo($fmanager->has_auto_saved_message($tid) ? 1 : 0); ?>;

var archive_mode = <?php echo(!empty($settings["archive_mode"]) ? "1" : "0"); ?>;

<?php if(!reqvar_empty("download") && $fmanager->is_logged_in()): ?>
do_not_check_new = true;
<?php endif; ?>

var user_tags = {};
<?php foreach($user_tags as $tgid => $tgname): ?> 
user_tags['#<?php echo_js($tgid); ?>'] = '<?php echo_js($tgname); ?>';
<?php endforeach; ?> 

function exec_load_new_posts(highlight_message, target_url)
{
  // no implementation in search mode
  return false;
}

function exec_reload_nav_control(ctrl, all_entry_post)
{
  // no implementation in search mode
  return false;
}

function exec_reload_online_users()
{
  // no implementation in search mode
  return false;
}
</script>

<?php
require_once "topic_post_functions_inc.php";
?>

<?php if($fmanager->is_logged_in() && reqvar_empty("news_digest") && reqvar_empty("rate_statistics")): ?>

<!-- BEGIN: header2 -->

<div class="header2">

<div id="actions" class="actions" onclick="toggle_actions()"><?php echo_html(text("Actions")); ?> ...</div>

<div id="actions_area" class="actions_area">

<a href="<?php echo($base_url . "&startmsg=" . $pagination_info["first_page_message"] . "&download=1"); ?>"><?php echo_html(text("Download")); ?></a> <br>

<?php if(!empty($_SESSION["has_forums_with_user_guest_posting"]) && $fmanager->is_logged_in() && !empty($forum_data["user_posting_as_guest"]) && !$fmanager->is_master_admin()): ?>
<?php if(empty($_SESSION["guest_posting_mode"])): ?>
<a href="<?php echo($base_url . "&startmsg=" . $pagination_info["first_page_message"]); ?>&guest_posting_on=1&hash=<?php echo_html($_SESSION["hash"]); ?>" onclick="check_actual_hash(this)" class="moderator_link"><?php echo_html(text("GuestPostingModeOn")); ?></a><br>
<?php else: ?>
<a href="<?php echo($base_url . "&startmsg=" . $pagination_info["first_page_message"]); ?>&guest_posting_off=1&hash=<?php echo_html($_SESSION["hash"]); ?>" onclick="check_actual_hash(this)" class="moderator_link"><?php echo_html(text("GuestPostingModeOff")); ?></a><br>
<?php endif; ?>
<?php endif; ?>

</div>

</div>

<!-- END: header2 -->
<?php endif; ?>

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

<?php if(!reqvar_empty("news_digest")): 
$not_preferred = "";
if(!empty($fid) && empty($_SESSION["ignored_forums"][$fid]) && !$is_private) $not_preferred = "not_preferred";
?>

  / <a class="<?php echo($not_preferred); ?>"  rel="nofollow" href="<?php echo($forum_url); ?>"><?php echo_html($forum_title); ?></a> 

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

<?php elseif(!reqvar_empty("favourite_posts") || !reqvar_empty("favourite_posts_only") || !reqvar_empty("favourites_only")): ?>

  / <a href="favourites.php"><?php echo_html(text("Favourites")); ?></a> 

  <?php
  $display = "style='display:none'";
  if(!empty($favourites_with_new_count)) $display = "";
  ?>
  <span class="new favourites_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php?fid=-1"><?php echo_html(text("new")); ?>:<span class='favourites_with_new_count'><?php echo($favourites_with_new_count); ?></span></a>]</span>

<?php elseif(!empty($fid) && !empty($forum_title)): ?>

  <?php
  $not_preferred = "";
  if(!empty($fid) && !empty($_SESSION["ignored_forums"][$fid]) && !$is_private) $not_preferred = "not_preferred";
  ?>
  / <a href="forum.php?fid=<?php echo_html($fid_for_url); ?>" class="<?php echo($not_preferred); ?>"><?php echo_html($forum_title); ?></a>

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

<?php endif; ?>

/ 

<?php if(val_or_empty($topic_data["profiled_topic"]) == 1): ?>
<span class="topic_type_indicator"><?php echo_html(text("Dedicated")); ?>:</span>
<?php elseif(val_or_empty($topic_data["profiled_topic"]) == 2): ?>
<span class="topic_type_indicator"><?php echo_html(text("Blog")); ?>:</span>
<?php endif; ?>

<span class="topic_title_main"><?php echo_html($search_title); ?></span>

/

<?php
$all_entry_post = $first_message;
?>

<?php if(!reqvar_empty("news_digest")): ?>
    <span class="message_info_bar"><?php echo_html(text("Messages")); ?>: <span class='count_number'><?php echo_html(format_number(val_or_empty($pagination_info["total_count"]))); ?></span>, 
    <?php echo(build_page_info($pagination_info, $pagination_info["mode"] == "all" ? text("pages_all") : text("pages"))); ?>
    </span>
<?php elseif(!reqvar_empty("rate_statistics")): ?>
    <span class="message_info_bar"><?php echo_html(text("Messages")); ?>: <span class='count_number'><?php echo_html(format_number(val_or_empty($pagination_info["total_count"]))); ?></span>, 
    <?php echo(build_page_info($pagination_info, $pagination_info["mode"] == "all" ? text("pages_all") : text("pages"))); ?>
    </span>
<?php else: ?>
    <span class="message_info_bar">
    <?php require "message_info_bar_inc.php"; ?>
    </span>
<?php endif; ?>

<?php if(reqvar("author_mode") != "last_posts" && reqvar_empty("rate_statistics")): ?>
<?php
$sort_url = preg_replace("/&post_sort=[^&]*/", "", $base_url);

if($sort != "desc")
{
  $desc = "";
  $sort_title = text("SortDescending");
  $sort_url .= "&post_sort=desc";
}
else
{
  $desc = "desc";
  $sort_title = text("SortAscending");
  $sort_url .= "&post_sort=asc";
}
?>
<a href="<?php echo_html($sort_url); ?>" title="<?php echo_html($sort_title); ?>" class="sorter <?php echo($desc); ?>">&nbsp;</a>
<?php endif; ?>

<?php if(!reqvar_empty("download") && $fmanager->is_logged_in()): ?>
<span class="new">[<?php echo_html(text("downloaded")); ?>]</span>
<?php endif; ?>

</div>

<div class="forum_bar">
<div style="position: relative">
  <div class="post_anchor <?php if(!empty($_SESSION["skin_properties"][$skin]["pin_the_menu"])) echo "post_anchor_fixed_menu"; ?>" id="post_anchor_top_new_message"></div>
</div>

<?php if(!reqvar_empty("news_digest")): ?>
    <?php if($pagination_info["page_count"] > 1): ?>
    <div class="navigator_bar"><?php echo(build_page_navigator($base_url . "&tpage=$", $pagination_info, $all_entry_post)); ?></div>
    <?php endif; ?>
<?php elseif(!reqvar_empty("rate_statistics")): ?>
    <?php if($pagination_info["page_count"] > 1): ?>
    <div class="navigator_bar"><?php echo(build_page_navigator($base_url . "&tpage=$", $pagination_info, $all_entry_post)); ?></div>
    <?php endif; ?>
<?php else: ?>
    <div class="navigator_bar">
    <?php require "navigator_bar_inc.php"; ?>
    </div>
<?php endif; ?>

<?php if(reqvar_empty("news_digest") && reqvar_empty("favourite_posts") && reqvar_empty("rate_statistics") && reqvar_empty("replies_to")): ?>
<div class="forum_action_bar">
<input type="button" class="standard_button" value="<?php echo_html(text("NewSearch")); ?>" onclick="delay_redirect('<?php echo(empty($search_params) ? 'search.php' : 'search.php?' . $search_params . '&new_search=1'); ?>')">
</div>
<?php endif; ?>

<div class="clear_both">
</div>

</div>

<!-- END: forum_bar -->

<div id="post_area">

  <div class="navigation_arrows">
  <div class="scroll_up" onclick="window.scrollTo(0, 0);"></div>
  <div class="scroll_down" onclick="window.scrollTo(0, 1000000);"></div>
  </div>

<?php foreach($post_list as $pid => $pinfo): ?>

<div class="message_container" id="post_<?php echo_html($pid); ?>">
<?php
require "topic_message_tpl_inc.php";
?>
</div>

<?php endforeach; ?>

<?php if(!empty($current_topic_id)): 
$remaining_new = 0;
if(!empty($_SESSION["new_messages_info_cache"]["data"]["topics"][$current_topic_id])) 
{
  $remaining_new = $_SESSION["new_messages_info_cache"]["data"]["topics"][$current_topic_id];
}
elseif(!empty($_SESSION["new_messages_info_cache"]["data"]["private_topics"][$current_topic_id])) 
{
  $remaining_new = $_SESSION["new_messages_info_cache"]["data"]["private_topics"][$current_topic_id];
} 

$display = "style='display:none'";
if(!empty($remaining_new)) $display = "";
?>
<div class="other_new_messages_alertbox" data-tid="<?php echo_html($current_topic_id); ?>" <?php echo($display); ?>>
<a href="topic.php?fid=<?php echo_html($current_forum_id); ?>&tid=<?php echo_html($current_topic_id); ?>&gotonew=1" rel="nofollow" target="_blank"><?php echo_html(text("NewMessages")); ?></a> <span class="new">[<a href="topic.php?fid=<?php echo_html($current_forum_id); ?>&tid=<?php echo_html($current_topic_id); ?>&gotonew=1" rel="nofollow" target="_blank"><?php echo_html(text("new")); ?>:<span class='new_messages_count'><?php echo($remaining_new); ?></span></a>]</span>
</div>
<div class="clear_both"></div>
<?php endif; ?>

</div>

<!-- BEGIN: forum_bar -->

<div class="forum_bar">

<?php
$all_entry_post = $last_message;
?>

<?php if(!reqvar_empty("news_digest")): ?>
    <?php if($pagination_info["page_count"] > 1): ?>
    <div class="navigator_bar"><?php echo(build_page_navigator($base_url . "&tpage=$", $pagination_info, $all_entry_post)); ?></div>
    <?php endif; ?>
<?php elseif(!reqvar_empty("rate_statistics")): ?>
    <?php if($pagination_info["page_count"] > 1): ?>
    <div class="navigator_bar"><?php echo(build_page_navigator($base_url . "&tpage=$", $pagination_info, $all_entry_post)); ?></div>
    <?php endif; ?>
<?php else: ?>
    <div class="navigator_bar">
    <?php require "navigator_bar_inc.php"; ?>
    </div>
<?php endif; ?>

<?php if(reqvar_empty("news_digest") && reqvar_empty("favourite_posts") && reqvar_empty("rate_statistics") && reqvar_empty("replies_to")): ?>
<div class="forum_action_bar">
<input type="button" class="standard_button" value="<?php echo_html(text("NewSearch")); ?>" onclick="delay_redirect('<?php echo(empty($search_params) ? 'search.php' : 'search.php?' . $search_params . '&new_search=1'); ?>')">
</div>
<?php endif; ?>

<div class="clear_both">
</div>

</div>

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

<?php if(!reqvar_empty("news_digest")): 
$not_preferred = "";
if(!empty($fid) && !empty($_SESSION["ignored_forums"][$fid]) && !$is_private) $not_preferred = "not_preferred";
?>

  / <a class="<?php echo($not_preferred); ?>"  rel="nofollow" href="<?php echo($forum_url); ?>"><?php echo_html($forum_title); ?></a> 

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

<?php elseif(!reqvar_empty("favourite_posts") || !reqvar_empty("favourite_posts_only") || !reqvar_empty("favourites_only")): ?>

  / <a href="favourites.php"><?php echo_html(text("Favourites")); ?></a> 

  <?php
  $display = "style='display:none'";
  if(!empty($favourites_with_new_count)) $display = "";
  ?>
  <span class="new favourites_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php?fid=-1"><?php echo_html(text("new")); ?>:<span class='favourites_with_new_count'><?php echo($favourites_with_new_count); ?></span></a>]</span>

<?php elseif(!empty($fid) && !empty($forum_title)): ?>

  <?php
  $not_preferred = "";
  if(!empty($fid) && !empty($_SESSION["ignored_forums"][$fid]) && !$is_private) $not_preferred = "not_preferred";
  ?>
  / <a href="forum.php?fid=<?php echo_html($fid_for_url); ?>" class="<?php echo($not_preferred); ?>"><?php echo_html($forum_title); ?></a>

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

<?php endif; ?>

/ 

<?php if(val_or_empty($topic_data["profiled_topic"]) == 1): ?>
<span class="topic_type_indicator"><?php echo_html(text("Dedicated")); ?>:</span>
<?php elseif(val_or_empty($topic_data["profiled_topic"]) == 2): ?>
<span class="topic_type_indicator"><?php echo_html(text("Blog")); ?>:</span>
<?php endif; ?>

<span class="topic_title_main"><?php echo_html($search_title); ?></span>

/ 

<?php if(!reqvar_empty("news_digest")): ?>
    <span class="message_info_bar"><?php echo_html(text("Messages")); ?>: <span class='count_number'><?php echo_html(format_number(val_or_empty($pagination_info["total_count"]))); ?></span>, 
    <?php echo(build_page_info($pagination_info, $pagination_info["mode"] == "all" ? text("pages_all") : text("pages"))); ?>
    </span>
<?php elseif(!reqvar_empty("rate_statistics")): ?>
    <span class="message_info_bar"><?php echo_html(text("Messages")); ?>: <span class='count_number'><?php echo_html(format_number(val_or_empty($pagination_info["total_count"]))); ?></span>, 
    <?php echo(build_page_info($pagination_info, $pagination_info["mode"] == "all" ? text("pages_all") : text("pages"))); ?>
    </span>
<?php else: ?>
    <span class="message_info_bar">
    <?php require "message_info_bar_inc.php"; ?>
    </span>
<?php endif; ?>

<?php if(reqvar("author_mode") != "last_posts" && reqvar_empty("rate_statistics")): ?>
<a href="<?php echo_html($sort_url); ?>" title="<?php echo_html($sort_title); ?>" class="sorter <?php echo($desc); ?>">&nbsp;</a>
<?php endif; ?>

<?php if(!reqvar_empty("download") && $fmanager->is_logged_in()): ?>
<span class="new">[<?php echo_html(text("downloaded")); ?>]</span>
<?php endif; ?>

</div>

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

  $treaders = "";
  
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

  if (!empty($treaders)) {
    $treaders = escape_html(text("ReadingTopic")) . " ($rcnt): " . $treaders;
  }

  $rcnt = count($forum_readers);
  if(!empty($forum_readers["g_#anonyms#"]["count"])) $rcnt += ($forum_readers["g_#anonyms#"]["count"] - 1);

  $bcnt = 0;
  foreach($forum_readers as $ouid => $uinfo)
  {
    if(!empty($uinfo["bot"])) $bcnt++;
  }
  if (!empty($rcnt)) $rcnt = ($rcnt - $bcnt);
  
  if (!empty($bcnt)) $rcnt .= "/" . $bcnt;

  $freaders = "";

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

  if (!empty($freaders)) {
    $freaders = escape_html(text("ReadingForum")) . " ($rcnt): " . $freaders;
  }
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

<?php
unset($_SESSION["ensure_anchor_visible"]);
?>

<?php
require_once "topic_lookup_inc.php";
require_once "tag_editor_inc.php";
require_once "topic_post_objects_inc.php";
?>
