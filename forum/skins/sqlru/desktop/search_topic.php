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
<script src='skins/<?php echo($skin); ?>/js/field_lookup.js<?php echo($cache_appendix); ?>'></script>

<!--
<script defer src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
-->

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
var has_auto_saved_message = <?php echo($fmanager->has_auto_saved_message($tid) ? 1 : 0); ?>;

var archive_mode = <?php echo(!empty($settings["archive_mode"]) ? "1" : "0"); ?>;
var thematic_per_default = <?php echo(!empty($_SESSION["thematic_per_default"]) ? "1" : "0"); ?>;

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
<!-- BEGIN: header3 -->

<div class="header3">

<div class="left_action_panel">
<?php if(!empty($_SESSION["has_forums_with_user_guest_posting"]) && $fmanager->is_logged_in() && !empty($forum_data["user_posting_as_guest"])): ?>
<?php if(empty($_SESSION["guest_posting_mode"])): ?>
<a href="<?php echo($base_url . "&startmsg=" . $pagination_info["first_page_message"]); ?>&guest_posting_on=1&hash=<?php echo_html($_SESSION["hash"]); ?>" onclick="check_actual_hash(this)" class="moderator_link"><?php echo_html(text("GuestPostingModeOn")); ?></a>
<?php else: ?>
<a href="<?php echo($base_url . "&startmsg=" . $pagination_info["first_page_message"]); ?>&guest_posting_off=1&hash=<?php echo_html($_SESSION["hash"]); ?>" onclick="check_actual_hash(this)" class="moderator_link"><?php echo_html(text("GuestPostingModeOff")); ?></a>
<?php endif; ?>
<?php endif; ?>
</div>

<div class="right_action_panel">
<a href="<?php echo($base_url . "&startmsg=" . $pagination_info["first_page_message"] . "&download=1"); ?>"><?php echo_html(text("Download")); ?></a> 
</div>

<div class="clear_both"></div>
</div>

<!-- END: header3 -->
<?php endif; ?>

<div class="content_area">

<!-- BEGIN: forum_bar -->

<div class="forum_bar">

<div class="wide_bar"><a href="forums.php"><?php echo_html(text("Forums")); ?></a>

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

<?php if(!reqvar_empty("news_digest")): 
$not_preferred = "";
if(!empty($_SESSION["preferred_forums"]) && !empty($fid) && empty($_SESSION["preferred_forums"][$fid]) && !$is_private) $not_preferred = "not_preferred";
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
  if(!empty($_SESSION["preferred_forums"]) && !empty($fid) && empty($_SESSION["preferred_forums"][$fid]) && !$is_private) $not_preferred = "not_preferred";
  ?>
  / <a href="forum.php?fid=<?php echo_html($fid_for_url); ?>" class="<?php echo($not_preferred); ?>"><?php echo_html($forum_title); ?></a>

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

  <?php
  $display = "style='display:none'";
  if(!empty($forum_data["topics_with_new_count"])) $display = "";
  ?>
  <span class="new forum_with_new_indicator" data-fid="<?php echo_html($fid_for_url); ?>" <?php echo($display); ?>>[<a href="<?php echo("new_messages.php?fid=" . $fid_for_url); ?>"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($forum_data["topics_with_new_count"]); ?></span></a>]</span>

<?php endif; ?>

/ <span class="topic_title_main"><?php echo_html($search_title); ?></span>

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

<?php
$all_entry_post = $first_message;
?>

<?php if(!reqvar_empty("news_digest")): ?>
    <div class="message_info_bar"><?php echo_html(text("Messages")); ?>: <span class='count_number'><?php echo_html(format_number(val_or_empty($pagination_info["total_count"]))); ?></span>, 
    <?php echo(build_page_info($pagination_info, $pagination_info["mode"] == "all" ? text("pages_all") : text("pages"))); ?>
    </div>

    <?php if($pagination_info["page_count"] > 1): ?>
    <div class="navigator_bar"><?php echo(build_page_navigator($base_url . "&tpage=$", $pagination_info, $all_entry_post)); ?></div>
    <?php endif; ?>
<?php elseif(!reqvar_empty("rate_statistics")): ?>
    <div class="message_info_bar"><?php echo_html(text("Messages")); ?>: <span class='count_number'><?php echo_html(format_number(val_or_empty($pagination_info["total_count"]))); ?></span>, 
    <?php echo(build_page_info($pagination_info, $pagination_info["mode"] == "all" ? text("pages_all") : text("pages"))); ?>
    </div>

    <?php if($pagination_info["page_count"] > 1): ?>
    <div class="navigator_bar"><?php echo(build_page_navigator($base_url . "&tpage=$", $pagination_info, $all_entry_post)); ?></div>
    <?php endif; ?>
<?php else: ?>
    <div class="message_info_bar">
    <?php require "message_info_bar_inc.php"; ?>
    </div>

    <div class="navigator_bar">
    <?php require "navigator_bar_inc.php"; ?>
    </div>
<?php endif; ?>

<div class="forum_action_bar">
<div style="position: relative">
  <div class="post_anchor <?php if(!empty($_SESSION["skin_properties"][$skin]["pin_the_menu"])) echo "post_anchor_fixed_menu"; ?>" id="post_anchor_top_new_message"></div>
</div>

<table>
<tr>
<td>
<?php
@include "forum_selector_inc.php";
?>
</td>

<?php if(reqvar_empty("news_digest") && reqvar_empty("favourite_posts") && reqvar_empty("rate_statistics") && reqvar_empty("replies_to")): ?>
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

<div id="post_area">

  <div class="navigation_arrows">
  <div class="scroll_up" onclick="window.scrollTo(0, 0);"></div>
  <div class="scroll_down" onclick="window.scrollTo(0, 1000000);"></div>
  </div>

<?php foreach($post_list as $pid => $pinfo): ?>

<div id="post_<?php echo_html($pid); ?>">
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
    <div class="message_info_bar"><?php echo_html(text("Messages")); ?>: <span class='count_number'><?php echo_html(format_number(val_or_empty($pagination_info["total_count"]))); ?></span>, 
    <?php echo(build_page_info($pagination_info, $pagination_info["mode"] == "all" ? text("pages_all") : text("pages"))); ?>
    </div>

    <?php if($pagination_info["page_count"] > 1): ?>
    <div class="navigator_bar"><?php echo(build_page_navigator($base_url . "&tpage=$", $pagination_info, $all_entry_post)); ?></div>
    <?php endif; ?>
<?php elseif(!reqvar_empty("rate_statistics")): ?>
    <div class="message_info_bar"><?php echo_html(text("Messages")); ?>: <span class='count_number'><?php echo_html(format_number(val_or_empty($pagination_info["total_count"]))); ?></span>, 
    <?php echo(build_page_info($pagination_info, $pagination_info["mode"] == "all" ? text("pages_all") : text("pages"))); ?>
    </div>

    <?php if($pagination_info["page_count"] > 1): ?>
    <div class="navigator_bar"><?php echo(build_page_navigator($base_url . "&tpage=$", $pagination_info, $all_entry_post)); ?></div>
    <?php endif; ?>
<?php else: ?>
    <div class="message_info_bar">
    <?php require "message_info_bar_inc.php"; ?>
    </div>

    <div class="navigator_bar">
    <?php require "navigator_bar_inc.php"; ?>
    </div>
<?php endif; ?>

<div class="forum_action_bar">
<table>
<tr>
<td>
<?php
@include "forum_selector_inc.php";
?>
</td>

<?php if(reqvar_empty("news_digest") && reqvar_empty("favourite_posts") && reqvar_empty("rate_statistics") && reqvar_empty("replies_to")): ?>
<td>
<input type="button" class="standard_button" value="<?php echo_html(text("NewSearch")); ?>" onclick="delay_redirect('<?php echo(empty($search_params) ? 'search.php' : 'search.php?' . $search_params . '&new_search=1'); ?>')">
</td>
<?php endif; ?>

</tr>
</table>
</div>

<div class="clear_both">
</div>

<div class="wide_bar"><a href="forums.php"><?php echo_html(text("Forums")); ?></a>

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

<?php if(!reqvar_empty("news_digest")): 
$not_preferred = "";
if(!empty($_SESSION["preferred_forums"]) && !empty($fid) && empty($_SESSION["preferred_forums"][$fid]) && !$is_private) $not_preferred = "not_preferred";
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
  if(!empty($_SESSION["preferred_forums"]) && empty($_SESSION["preferred_forums"][$fid]) && !$is_private) $not_preferred = "not_preferred";
  ?>
  / <a href="forum.php?fid=<?php echo_html($fid_for_url); ?>" class="<?php echo($not_preferred); ?>"><?php echo_html($forum_title); ?></a>

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

  <?php
  $display = "style='display:none'";
  if(!empty($forum_data["topics_with_new_count"])) $display = "";
  ?>
  <span class="new forum_with_new_indicator" data-fid="<?php echo_html($fid_for_url); ?>" <?php echo($display); ?>>[<a href="<?php echo("new_messages.php?fid=" . $fid_for_url); ?>"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($forum_data["topics_with_new_count"]); ?></span></a>]</span>

<?php endif; ?>

/ <span class="topic_title_main"><?php echo_html($search_title); ?></span>

<?php if(reqvar("author_mode") != "last_posts" && reqvar_empty("rate_statistics")): ?>
<a href="<?php echo_html($sort_url); ?>" title="<?php echo_html($sort_title); ?>" class="sorter <?php echo($desc); ?>">&nbsp;</a>
<?php endif; ?>

<?php if(!reqvar_empty("download") && $fmanager->is_logged_in()): ?>
<span class="new">[<?php echo_html(text("downloaded")); ?>]</span>
<?php endif; ?>

</div>

</div>

<!-- END: forum_bar -->

<?php
@include "online_users_inc.php";
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


