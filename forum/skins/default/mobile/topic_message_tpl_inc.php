<?php
$blocked = false;
$blocked_only_topic = false;
$may_rate = true;
$may_write = true;
$may_answer = true;
$post_ignored = 0;

$first_topic_post = (val_or_empty($topic_data["first_topic_pinned_message"]) == $pid || (empty($topic_data["first_topic_pinned_message"]) && val_or_empty($topic_data["absolute_first_topic_message"]) == $pid));

if (!empty($pinfo["topic_closed"])) {
    $may_write = false;
}

if ($fmanager->is_topic_moderator($pinfo["topic_id"])) {
    $may_write = true;
}

if (!empty($pinfo["forum_closed"])) {
    $may_write = false;
}

if (!empty($pinfo["me_forum_blocked"])) {
    $blocked = true;
    $may_write = false;
    $may_rate = false;
}

if (!empty($pinfo["me_forum_guest_blocked"])) {
    $blocked = true;
    $may_rate = false;
}

if (!empty($pinfo["me_topic_blocked"])) {
    if(empty($blocked)) $blocked_only_topic = true;
    
    $blocked = true;
    $may_write = false;
    $may_rate = false;
}

if (!empty($pinfo["me_topic_guest_blocked"])) {
    $blocked = true;
    $may_rate = false;
}

if ($fmanager->is_admin() || $fmanager->is_forum_moderator($pinfo["forum_id"])) {
    $blocked = false;
    $may_write = true;
    $may_rate = true;
}

if (!empty($_SESSION["blocked"])) {
    $blocked = true;
    $blocked_only_topic = false;
    $may_write = false;
    $may_rate = false;
}

if (!empty($_SESSION["rating_blocked"])) {
    $may_rate = false;
}

if ($fmanager->is_logged_in() && empty($_SESSION["approved"])) {
    $may_write = false;
    $may_rate = false;
}

if ($fmanager->is_logged_in() && empty($_SESSION["activated"])) {
    $may_write = false;
    $may_rate = false;
}

if (!$fmanager->is_logged_in() && !empty($_SESSION["ip_blocked"])) {
    $may_write = false;
}

if (!empty($user_data[$pinfo["user_id"]]["ignored"]) || !empty($pinfo["guest_ignored"])) {
    $may_rate = false;
    $may_answer = false;
    $citatable = "";
    $post_ignored = 1;
}

if (!empty($pinfo["comment_ignored"])) {
    $post_ignored = 1;
}

if (!reqvar_empty("favourite_posts") || !reqvar_empty("favourite_posts_only") || !reqvar_empty("include_ignored") ||
    (in_array(reqvar("author_mode"), array("author_liked", "author_disliked", "last_posts", "wrote_post")) && !reqvar_empty("author")) ||
    (!reqvar_empty("replies_to") && !empty($pinfo["profiled_topic"]))
) {
    $post_ignored = 0;
}

if (!empty($forum_data["disable_ignore"])) {
    $post_ignored = 0;
}

if (!empty($settings["archive_mode"]))
{
    $may_write = false;
    $may_rate = false;
}

$message_url = "topic.php?fid=" . $pinfo["forum_id_for_url"] . "&tid=" . $pinfo["topic_id"] . "&msg=" . $pid;

$deleted = "";
$pinned = "";
$pinned_appendix = "";
$current = "";
if(!empty($pinfo["deleted"])) $deleted = "deleted_post";
if(!empty($pinfo["pinned"])) 
{
  $pinned = "pinned_post";
  $pinned_appendix = "&nbsp;<span class='pinned_sign'>&nbsp;</span>";
}

$attachment_editable_class = "";
if(!empty($pinfo["editable"]))
{
  $attachment_editable_class .= " attachment_editable";
}

if(!empty($pinfo["moderatable"]) && !empty($pinfo["editable"]))
{
  $attachment_editable_class .= " attachment_moderatable";
}

$hide_moderator_names = (!$fmanager->is_moderator_log_visible() || (val_or_empty($settings["moderator_log"]) == "all_names_hidden" && !$fmanager->is_moderator()));

if(val_or_empty($_SESSION["ensure_anchor_visible"]) == $pid) $current = "current_post";

if(!reqvar_empty("search_keys"))
{
  $fmanager->highlight_found_keys($pinfo["html_content"], reqvar("search_keys"), !reqvar_empty("with_morphology"));
}

if($fmanager->demo_mode())
{
  $pinfo["ip"] = "127.0.0.1";
}
?>

<?php if(!reqvar_empty("news_digest") && val_or_empty($current_topic_name) != $pinfo["topic_name"]): ?>

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

$display = "style='visibility:hidden'";
if(!empty($remaining_new)) $display = "";
?>
<div class="other_new_messages_alertbox" data-tid="<?php echo_html($current_topic_id); ?>" <?php echo($display); ?>>
<a href="topic.php?fid=<?php echo_html($fid_for_url); ?>&tid=<?php echo_html($current_topic_id); ?>&gotonew=1" rel="nofollow" target="_blank"><?php echo_html(text("NewMessages")); ?></a> <span class="new">[<a href="topic.php?fid=<?php echo_html($fid_for_url); ?>&tid=<?php echo_html($current_topic_id); ?>&gotonew=1" rel="nofollow" target="_blank"><?php echo_html(text("new")); ?>:<span class='new_messages_count'><?php echo($remaining_new); ?></span></a>]</span>
</div>
<div class="clear_both"></div>
<?php endif; ?>

<?php
$current_topic_name = $pinfo["topic_name"];
$current_topic_id = $pinfo["topic_id"];
$current_forum_id = $pinfo["forum_id"];

$fid_for_url = $current_forum_id;
if(!empty($pinfo["topic_private"])) $fid_for_url = "private";
?>

<div class="new_digest_topic">

<?php
$not_preferred = "";
if(!empty($_SESSION["preferred_forums"]) && empty($_SESSION["preferred_forums"][$current_forum_id]) && empty($pinfo["topic_private"])) $not_preferred = "not_preferred";
?>
<a href="forum.php?fid=<?php echo_html($fid_for_url); ?>" class="<?php echo($not_preferred); ?>"><?php echo_html($pinfo["forum_name"]); ?></a> 

/ <span class="topic_title_main"><?php echo_html(postprocess_message($current_topic_name)); ?></span>
<?php if(!empty($_SESSION["ignored_topics"][$current_topic_id])): ?>
 <span class="closed">[<?php echo_html(text("ignored")); ?>]</span>
<?php endif; ?>
</div>
<div class="clear_both"></div>

<?php endif; ?>

<a id="p<?php echo_html($pid); ?>"></a>

<?php
$user_identifier_class = "";

if(empty($pinfo["user_id"]))
{
  $user_identifier_class .= " guest_post";
}
else
{
  $user_identifier_class .= " user_post";
}

$user_identifier_class .= " author_post_" . md5($pinfo["author"]);
?>

<table id="post_table_<?php echo_html($pid); ?>" class="post_table forum_<?php echo($pinfo["forum_id"]); ?> topic_<?php echo($pinfo["topic_id"]); ?> <?php echo($user_identifier_class); ?> <?php echo_html($pinned); ?> <?php echo_html($attachment_editable_class); ?> <?php if($post_ignored) echo "ignored_post"; ?> <?php if(!empty($pinfo["editable"]) && !empty($pinfo["profiled_topic"])) echo(empty($pinfo["is_comment"]) ? "thematic_post" : "comment_post"); ?> <?php if(!empty($pinfo["is_adult"])) echo "adult_post"; ?>">
<tr>

<?php if(!empty($pinfo["editable"]) && empty($pinfo["is_system"]) && (($fmanager->is_admin() && empty($pinfo["topic_private"])) || $fmanager->is_forum_moderator($pinfo["forum_id"]) || $fmanager->is_topic_moderator($pinfo["topic_id"]))): ?>
<th id="post_head_<?php echo_html($pid); ?>" class="post_head checkbox_post_selector <?php echo_html($current); ?> <?php echo_html($deleted); ?>">

<div style="position: relative">
  <div class="post_anchor <?php if(!empty($_SESSION["skin_properties"][$skin]["pin_the_menu"])) echo "post_anchor_fixed_menu"; ?>" id="post_anchor_<?php echo_html($pid); ?>"></div>
</div>

<div class="popup_moderator_menu" id="moderator_popup_menu_<?php echo_html($pid); ?>">

    <div style="position: absolute;right:2px;top:2px;cursor:pointer" onclick="hide_all_popups(); clear_selection();"><img src="<?php echo($view_path); ?>images/cross.png" alt="<?php echo_html(text("Close")); ?>"></div>

    <span style="font-weight: bold"><?php echo_html(text("MsgPostsSelected")); ?>: <span class="selected_posts_count">0</span></span><br>

    <a href="<?php echo($message_url); ?>" class="moderator_link" onclick='return select_all()'><?php echo_html(text("SelectAll")); ?></a>
    <br><a href="<?php echo($message_url); ?>" class="moderator_link" onclick='return unselect_all()'><?php echo_html(text("ResetSelection")); ?></a>

    <?php if(!empty($pinfo["profiled_topic"])): ?>
    <br><a href="<?php echo($message_url); ?>" class="moderator_link" onclick='return confirm_action("<?php echo_js(text("MsgConfirmToThematic"), true); ?>", { topic_action: "convert_to_thematic", topic: "<?php echo_js($pinfo["topic_id"]); ?>", forum: "<?php echo_js($pinfo["forum_id"]); ?>" });'><?php echo_html(text("MakePostsThematic")); ?></a>
    <br><a href="<?php echo($message_url); ?>" class="moderator_link" onclick='return confirm_action("<?php echo_js(text("MsgConfirmToComment"), true); ?>", { topic_action: "convert_to_comment", topic: "<?php echo_js($pinfo["topic_id"]); ?>", forum: "<?php echo_js($pinfo["forum_id"]); ?>" });'><?php echo_html(text("MakePostsToComments")); ?></a>
    <?php endif; ?>

    <br><a href="<?php echo($message_url); ?>" class="moderator_link" onclick='return confirm_action("<?php echo_js(text("MsgConfirmToAdult"), true); ?>", { topic_action: "convert_to_adult", topic: "<?php echo_js($pinfo["topic_id"]); ?>", forum: "<?php echo_js($pinfo["forum_id"]); ?>" });'><?php echo_html(text("MakePostAdult")); ?></a>
    <br><a href="<?php echo($message_url); ?>" class="moderator_link" onclick='return confirm_action("<?php echo_js(text("MsgConfirmToNonAdult"), true); ?>", { topic_action: "convert_to_nonadult", topic: "<?php echo_js($pinfo["topic_id"]); ?>", forum: "<?php echo_js($pinfo["forum_id"]); ?>" });'><?php echo_html(text("MakePostNonAdult")); ?></a>

    <br><a href="<?php echo($message_url); ?>" class="moderator_link" onclick='return confirm_action_with_comment("<?php echo_js(text("MsgConfirmPostsDelete"), true); ?>", { topic_action: "delete_post", topic: "<?php echo_js($pinfo["topic_id"]); ?>", forum: "<?php echo_js($pinfo["forum_id"]); ?>" });'><?php echo_html(text("DeleteMessages")); ?></a>
    <br><a href="<?php echo($message_url); ?>" class="moderator_link" onclick='return confirm_action_with_comment("<?php echo_js(text("MsgConfirmPostsRestore"), true); ?>", { topic_action: "restore_post", topic: "<?php echo_js($pinfo["topic_id"]); ?>", forum: "<?php echo_js($pinfo["forum_id"]); ?>" });'><?php echo_html(text("RestoreMessages")); ?></a>

    <?php if(empty($pinfo["topic_private"])): ?>
    <br><a href="<?php echo($message_url); ?>" class="moderator_link" onclick="return select_target_topic_for_move('move_posts')"><?php echo_html(text("MovePosts")); ?></a>
    <?php endif; ?>
</div>

<div class="topic_name post_checkbox" onclick="toggle_selection(this, '<?php echo_html($pid); ?>'); show_moderator_popup_menu('<?php echo_html($pid); ?>')" data-pid="<?php echo_html($pid); ?>"><?php echo_html(smart_date($pinfo["creation_date"])); ?><?php echo($pinned_appendix); ?></div>
<?php else: ?>
<th id="post_head_<?php echo_html($pid); ?>" class="post_head <?php echo_html($current); ?> <?php echo_html($deleted); ?>">

<div style="position: relative">
  <div class="post_anchor <?php if(!empty($_SESSION["skin_properties"][$skin]["pin_the_menu"])) echo "post_anchor_fixed_menu"; ?>" id="post_anchor_<?php echo_html($pid); ?>"></div>
</div>

<div class="topic_name"><?php echo_html(smart_date($pinfo["creation_date"])); ?><?php echo($pinned_appendix); ?></div>
<?php endif; ?>

<div class="post_id_info">

    <?php if(empty($settings["archive_mode"]) && $fmanager->is_logged_in() && !$fmanager->is_master_admin()): ?>
    <?php if(!empty($pinfo["subscribed"])): ?>
    <a href="<?php echo($message_url); ?>" id="subscribe_post_link_<?php echo_html($pid); ?>" class="post_subscribe_action post_subscribed" onmouseout="this.blur()" onclick='return do_action({ topic_action: "unsubscribe_from_post", post: "<?php echo_js($pid); ?>" })' title="<?php echo_html(text("Unsubscribe")); ?>">&nbsp;&nbsp;</a>&nbsp;
    <?php else: ?>
    <a href="<?php echo($message_url); ?>" id="subscribe_post_link_<?php echo_html($pid); ?>" class="post_subscribe_action post_not_subscribed" onmouseout="this.blur()" onclick='return confirm_action("<?php echo_js(text("MsgConfirmSubscribeToPost"), true); ?>", { topic_action: "subscribe_to_post", post: "<?php echo_js($pid); ?>" })' title="<?php echo_html(text("Subscribe")); ?>">&nbsp;&nbsp;</a>&nbsp;
    <?php endif; ?>
    <?php endif; ?>

    <?php if(!empty($pinfo["in_favourites"])): ?>
    <a href="<?php echo($message_url); ?>" id="favourite_post_link_<?php echo_html($pid); ?>" class="post_favourite_action post_in_favourites" onclick='return do_action({ topic_action: "remove_post_from_favourites", post: "<?php echo_js($pid); ?>" })' title="<?php echo_html(text("RemoveFromFavourites")); ?>">&nbsp;&nbsp;</a>&nbsp;
    <?php else: ?>
    <a href="<?php echo($message_url); ?>" id="favourite_post_link_<?php echo_html($pid); ?>" class="post_favourite_action post_not_in_favourites" onclick='return confirm_action("<?php echo_js(text("MsgConfrimAddPostToFavourites"), true); ?>", { topic_action: "add_post_to_favourites", post: "<?php echo_js($pid); ?>" })' title="<?php echo_html(text("AddToFavourites")); ?>">&nbsp;&nbsp;</a>&nbsp;
    <?php endif; ?>

    <a href="<?php echo($message_url); ?>" onclick="return toggle_id_info_actions('<?php echo_html($pid); ?>')" title="<?php echo_html(text("ShowShareLinks")); ?>">#<?php echo_html($pid); ?></a>

    <div id="post_id_info_<?php echo_html($pid); ?>" class="post_id_info_actions" style="display:none">

    <div style="position: absolute;right:2px;top:2px;cursor:pointer" onclick="toggle_id_info_actions('<?php echo_html($pid); ?>')"><img src="<?php echo($view_path); ?>images/cross.png" alt="<?php echo_html(text("Close")); ?>"></div>

    <?php if(empty($pinfo["topic_private"])): ?>
    <div class="inner_label"><?php echo_html(text("Link")); ?>:</div>
    <table class="aux_table">
    <tr>
    <td><input type="text" id="pid_link_<?php echo_html($pid); ?>" value="<?php echo_html(get_host_address() . get_url_path() . $message_url); ?>" onfocus="select_text_in_field('pid_link_<?php echo_html($pid); ?>')"/></td>
    <td>&nbsp;</td>
    <td><input type="button" class="standard_button" value="&nbsp;" onclick="focus_field('pid_link_<?php echo_html($pid); ?>')" title="<?php echo_html(text("MarkForCopy")); ?>"></td>
    </tr>
    </table>

    <div class="inner_label"><?php echo_html(text("LinkToMessage")); ?>:</div>
    <table class="aux_table">
    <tr>
    <td><input type="text" id="pid_lmsg_<?php echo_html($pid); ?>" value="[mid=<?php echo_html($pid); ?>]" onfocus="select_text_in_field('pid_lmsg_<?php echo_html($pid); ?>')"/></td>
    <td>&nbsp;</td>
    <td><input type="button" class="standard_button" value="&nbsp;" onclick="focus_field('pid_lmsg_<?php echo_html($pid); ?>')" title="<?php echo_html(text("MarkForCopy")); ?>"></td>
    </tr>
    </table>

    <div class="inner_label"><?php echo_html(text("LinkWithTopicName")); ?>:</div>
    <table class="aux_table">
    <tr>
    <td><input type="text" id="pid_linktn_<?php echo_html($pid); ?>" value="[url=<?php echo_html(get_host_address() . get_url_path() . $message_url); ?>]<?php echo_html(postprocess_message($pinfo["topic_name"])); ?>[/url]" onfocus="select_text_in_field('pid_linktn_<?php echo_html($pid); ?>')"/></td>
    <td>&nbsp;</td>
    <td><input type="button" class="standard_button" value="&nbsp;" onclick="focus_field('pid_linktn_<?php echo_html($pid); ?>')" title="<?php echo_html(text("MarkForCopy")); ?>"></td>
    </tr>
    </table>
    <?php else: ?>
    <div class="inner_label"><?php echo_html(text("LinkToMessage")); ?>:</div>
    <table class="aux_table">
    <tr>
    <td><input type="text" id="pid_lmsg_<?php echo_html($pid); ?>" value="[mid=<?php echo_html($pid); ?>]" onfocus="select_text_in_field('pid_lmsg_<?php echo_html($pid); ?>')"/></td>
    <td>&nbsp;</td>
    <td><input type="button" class="standard_button" value="&nbsp;" onclick="focus_field('pid_lmsg_<?php echo_html($pid); ?>')" title="<?php echo_html(text("MarkForCopy")); ?>"></td>
    </tr>
    </table>
    <?php endif; ?>

    <?php if(!empty($pinfo["user_id"])): ?>
    <div class="inner_label"><?php echo_html(text("UserProfileLink")); ?>:</div>
    <table class="aux_table">
    <tr>
    <td><input type="text" id="pid_linkusr_<?php echo_html($pid); ?>" value="[uid=<?php echo_html($pinfo["user_id"]); ?>]" onfocus="select_text_in_field('pid_linkusr_<?php echo_html($pid); ?>')"/></td>
    <td>&nbsp;</td>
    <td><input type="button" class="standard_button" value="&nbsp;" onclick="focus_field('pid_linkusr_<?php echo_html($pid); ?>')" title="<?php echo_html(text("MarkForCopy")); ?>"></td>
    </tr>
    </table>
    <?php endif; ?>

    <?php 
    $attachments_per_post = $fmanager->get_attachments_per_post();
    for($i = 1; $i <= $attachments_per_post; $i++): ?>
    <?php
    $idx = $i;
    $appendix = " " . $i;
    if($idx == 1) 
    {
      $idx = "";
      $appendix = "";
    }
    
    $bin_str = str_repeat("0", $attachments_per_post);
    $bin_str[$attachments_per_post - $i] = 1;
    ?>
    <?php if(!empty($pinfo["has_attachment"]) && ($pinfo["has_attachment"] & bindec($bin_str)) && empty($pinfo["topic_private"])): ?>
    <div class="inner_label"><?php echo_html(text("AttachmentLink") . $appendix); ?>:</div>
    <table class="aux_table">
    <tr>
    <td><input type="text" id="pid_linkatt<?php echo($idx); ?>_<?php echo_html($pid); ?>" value="[attachment<?php echo($idx); ?>=<?php echo_html($pid); ?>]" onfocus="select_text_in_field('pid_linkatt<?php echo($idx); ?>_<?php echo_html($pid); ?>')"/></td>
    <td>&nbsp;</td>
    <td><input type="button" class="standard_button" value="&nbsp;" onclick="focus_field('pid_linkatt<?php echo($idx); ?>_<?php echo_html($pid); ?>')" title="<?php echo_html(text("MarkForCopy")); ?>"></td>
    </tr>
    </table>
    <?php endif; ?>
    <?php endfor; ?>    

    <?php if($fmanager->is_admin()): ?>
    <div class="inner_label"><?php echo_html(text("ReadMarker")); ?>:</div>
    <table class="aux_table">
    <tr>
    <td><input type="text" id="pid_readmarker_<?php echo_html($pid); ?>" value="<?php echo_html($pinfo["read_marker"]); ?>" onfocus="select_text_in_field('pid_readmarker_<?php echo_html($pid); ?>')"/></td>
    <td>&nbsp;</td>
    <td><input type="button" class="standard_button" value="&nbsp;" onclick="focus_field('pid_readmarker_<?php echo_html($pid); ?>')" title="<?php echo_html(text("MarkForCopy")); ?>"></td>
    </tr>
    </table>
    
    <div class="inner_label"><?php echo_html(text("FingerPrint")); ?>:</div>
    <table class="aux_table">
    <tr>
    <td><input type="text" id="pid_usermarker_<?php echo_html($pid); ?>" value="<?php echo_html($pinfo["user_marker"]); ?>" onfocus="select_text_in_field('pid_usermarker_<?php echo_html($pid); ?>')"/></td>
    <td>&nbsp;</td>
    <td><input type="button" class="standard_button" value="&nbsp;" onclick="focus_field('pid_usermarker_<?php echo_html($pid); ?>')" title="<?php echo_html(text("MarkForCopy")); ?>"></td>
    </tr>
    </table>

    <div class="inner_label"><?php echo_html(text("UserAgent")); ?>:</div>
    <table class="aux_table">
    <tr>
    <td><input type="text" id="pid_useragent_<?php echo_html($pid); ?>" value="<?php echo_html($pinfo["user_agent"]); ?>" onfocus="select_text_in_field('pid_useragent_<?php echo_html($pid); ?>')"/></td>
    <td>&nbsp;</td>
    <td><input type="button" class="standard_button" value="&nbsp;" onclick="focus_field('pid_useragent_<?php echo_html($pid); ?>')" title="<?php echo_html(text("MarkForCopy")); ?>"></td>
    </tr>
    </table>
    <?php endif; ?>

    </div>

</div>

<div class="clear_both">
</div>
</th>

</tr>

<tr>
<td class="author_cell">

  <div class="author_wrapper">
  <?php
  $aname_appendix = "";
  if(empty($pinfo["user_id"])): 
    $rnd = rand(1000, 9000);
    $avatar = $view_path . "images/guest.jpg?rnd=$rnd";

    if(!empty($pinfo["avatar"]))
    {
      if(val_or_empty($pinfo["aname"]) != "admin") $aname_appendix .= "&aname=" . val_or_empty($pinfo["aname"]);
      
      if(empty($_SESSION["hide_user_avatars"]) && empty($_SESSION["hidden_guest_profiles"][$pinfo["aname"]]))
      {
        $avatar = escape_html($pinfo["avatar"]) . "?ctime=" . $pinfo["avatar_ctime"];
      }
    }

    if (val_or_empty($pinfo["guest_ignored"]) == 2) 
    {
      $aname_appendix .= "&ignored=2";
    }
  ?>

  <table class="aux_table" style="width:100%">
  <tr>
  <td>
  <div class="avatar_container">
  <a class="guest_link" href="view_guest_profile.php?guest=<?php echo(xrawurlencode($pinfo["author"])); ?><?php echo($aname_appendix); ?>"><img src="<?php echo($avatar); ?>" alt="<?php echo_html($fmanager->get_display_name($pinfo["author"])); ?>"></a>
  </div>

  <div class="clear_both"></div>
  </td>
  <td>
  <div class="smart_break">
  <div class="author_container">
  <?php
  $online_status = "";
  if(empty($settings["hide_online_status"]) && !empty($online_users["g_" . $pinfo["author"]]))
  {
    $online_status = "&nbsp;<span class='online_text'>✓</span>";
  }

  $protected_guest = "";
  if(!empty($settings["protected_guest_list"][$pinfo["author"]]) && $fmanager->is_admin())
  {
    $protected_guest = "<div class='protected_guest'></div>";
  }
  
  if($pinfo["author"] == "admin")
    $author = '<a class="admin_link" href="view_guest_profile.php?guest=' . xrawurlencode($pinfo["author"]) . $aname_appendix . '">' . escape_html(text("MasterAdministrator")) . '</a>';
  else
    $author = '<a class="guest_link" href="view_guest_profile.php?guest=' . xrawurlencode($pinfo["author"]) . $aname_appendix . '">' . escape_html($pinfo["author"]) . '</a>';
  
  echo($author . $protected_guest . $online_status); 
  ?>
  </div>
  </div>
  <div class="member_status">
    <?php if($pinfo["author"] != "admin"): ?>
    <?php echo_html(text("Guest")); ?><br>
    <?php endif; ?>

    <?php if(!empty($pinfo["guest_blocked"])): ?>
    <span class="error_text">[<?php echo_html(empty($pinfo["guest_block_time_left"]) ? text("blocked") : sprintf(text("blocked_until"), $pinfo["guest_block_time_left"])); ?>]</span><br>
    <?php endif; ?>

    <?php if(!empty($pinfo["hidden_by_me"])): ?>
    <span class="error_text">[<?php echo_html(text("hidden_by_me")); ?>]</span><br>
    <?php endif; ?>

    <?php if(!empty($pinfo["guest_ignored"])): ?>
    <span class="error_text">[<?php echo_html(text("ignored")); ?><?php if($pinfo["guest_ignored"] == 2) echo_html("*"); ?>]</span><br>
    <?php endif; ?>
  </div>  
  </td>
  <td class="author_actions">

  <div class="button_panel">
  <?php if(empty($settings["archive_mode"]) && $pinfo["read_marker"] != $READ_MARKER && !($fmanager->is_master_admin() && $pinfo["author"] == "admin")): ?>  
    <?php if(empty($pinfo["guest_subscribed"])): ?>
      <a class="subscribe_action_a_<?php echo_html(md5($pinfo["author"])); ?>" href="<?php echo($message_url); ?>" title="<?php echo_html(text("SubscribeToUser")); ?>" onclick='return confirm_action("<?php echo_js(text("MsgConfirmSubscribeToUser"), true); ?>".replace(/%s/, "<?php echo_js($fmanager->get_display_name($pinfo["author"]), true); ?>"), { subscribe_action: "subscribe_to_user", uid: "", user_name: "<?php echo_js($pinfo["author"]); ?>", display_user_name: "<?php echo_js($fmanager->get_display_name($pinfo["author"])); ?>", guest_id: "<?php echo_html(md5($pinfo["author"])); ?>" });'><img class="subscribe_action_img_<?php echo_html(md5($pinfo["author"])); ?>" src="<?php echo($view_path); ?>images/subscribe_to_user.png" alt="<?php echo_html(text("SubscribeToUser")); ?>"></a>
    <?php else: ?>
      <a class="subscribe_action_a_<?php echo_html(md5($pinfo["author"])); ?>" href="<?php echo($message_url); ?>" title="<?php echo_html(text("UnsubscribeFromUser")); ?>" onclick='return do_action({ subscribe_action: "unsubscribe_from_user", uid: "", user_name: "<?php echo_js($pinfo["author"]); ?>", display_user_name: "<?php echo_js($fmanager->get_display_name($pinfo["author"])); ?>", guest_id: "<?php echo_html(md5($pinfo["author"])); ?>" });'><img class="subscribe_action_img_<?php echo_html(md5($pinfo["author"])); ?>" src="<?php echo($view_path); ?>images/unsubscribe_from_user.png" alt="<?php echo_html(text("UnsubscribeFromUser")); ?>"></a>
    <?php endif; ?>
  <?php endif; ?>

  <?php if($pinfo["read_marker"] != $READ_MARKER && !($fmanager->is_master_admin() && $pinfo["author"] == "admin") && empty($_SESSION["hide_user_info"])): ?>  
    <?php if(empty($pinfo["hidden_by_me"])): ?>
      <a class="hide_profile_a_<?php echo_html($pinfo["aname"]); ?>" href="<?php echo($message_url); ?>" title="<?php echo_html(text("HideProfile")); ?>" onclick='return confirm_action("<?php echo_js(text("MsgConfirmProfileHide"), true); ?>".replace(/%s/, "<?php echo_js($fmanager->get_display_name($pinfo["author"]), true); ?>"), { profile_hide_action: "hide_guest_profile", guest_name: "<?php echo_js($pinfo["author"]); ?>", display_guest_name: "<?php echo_js($fmanager->get_display_name($pinfo["author"])); ?>", guest_id: "<?php echo_js($pinfo["aname"]); ?>" });'><img class="hide_profile_img_<?php echo_html($pinfo["aname"]); ?>" src="<?php echo($view_path); ?>images/hide_profile.png" alt="<?php echo_html(text("HideProfile")); ?>"></a>
    <?php else: ?>
      <a class="hide_profile_a_<?php echo_html($pinfo["aname"]); ?>" href="<?php echo($message_url); ?>" title="<?php echo_html(text("OpenProfile")); ?>" onclick='return confirm_action("<?php echo_js(text("MsgConfirmProfileOpen"), true); ?>".replace(/%s/, "<?php echo_js($fmanager->get_display_name($pinfo["author"]), true); ?>"), { profile_hide_action: "open_guest_profile", guest_name: "<?php echo_js($pinfo["author"]); ?>", display_guest_name: "<?php echo_js($fmanager->get_display_name($pinfo["author"])); ?>", guest_id: "<?php echo_js($pinfo["aname"]); ?>" });'><img class="hide_profile_img_<?php echo_html($pinfo["aname"]); ?>" src="<?php echo($view_path); ?>images/show_profile.png" alt="<?php echo_html(text("OpenProfile")); ?>"></a>
    <?php endif; ?>
  <?php endif; ?>
  
  <?php if($pinfo["read_marker"] != $READ_MARKER && !($fmanager->is_master_admin() && $pinfo["author"] == "admin")): ?>  
    <?php if(empty($pinfo["guest_ignored"])): ?>
    <a class="ignore_user_a_<?php echo_html(md5($pinfo["author"])); ?>" href="<?php echo($message_url); ?>" title="<?php echo_html(text("PutToIgnoreList")); ?>" onclick='return confirm_action("<?php echo_js(text("MsgConfirmUserIgnore"), true); ?>".replace(/%s/, "<?php echo_js($fmanager->get_display_name($pinfo["author"]), true); ?>"), { ignore_action: "put_guest_to_ignore_list", guest_name: "<?php echo_js($pinfo["author"]); ?>", display_guest_name: "<?php echo_js($fmanager->get_display_name($pinfo["author"])); ?>", guest_id: "<?php echo_html(md5($pinfo["author"])); ?>" });'><img class="ignore_user_img_<?php echo_html(md5($pinfo["author"])); ?>" src="<?php echo($view_path); ?>images/ignore_user.png" alt="<?php echo_html(text("PutToIgnoreList")); ?>"></a>
    <?php elseif ($pinfo["guest_ignored"] != 2): ?>
    <a class="ignore_user_a_<?php echo_html(md5($pinfo["author"])); ?>" href="<?php echo($message_url); ?>" title="<?php echo_html(text("RemoveFromIgnoreList")); ?>" onclick='return confirm_action("<?php echo_js(text("MsgConfirmUserUnignore"), true); ?>".replace(/%s/, "<?php echo_js($fmanager->get_display_name($pinfo["author"]), true); ?>"), { ignore_action: "remove_guest_from_ignore_list", guest_name: "<?php echo_js($pinfo["author"]); ?>", display_guest_name: "<?php echo_js($fmanager->get_display_name($pinfo["author"])); ?>", guest_id: "<?php echo_html(md5($pinfo["author"])); ?>" });'><img class="ignore_user_img_<?php echo_html(md5($pinfo["author"])); ?>" src="<?php echo($view_path); ?>images/unignore_user.png" alt="<?php echo_html(text("RemoveFromIgnoreList")); ?>"></a>
    <?php endif; ?>
  <?php endif; ?>

    <?php if(empty($in_search)): ?>
    <a href="search.php?do_search=1&tid=<?php echo_html($pinfo["topic_id"]); ?>&author_mode=wrote_post&author=<?php echo(xrawurlencode($pinfo["author"])); ?>&start_from=<?php echo($pid); ?>" title="<?php echo_html(text("AuthorMessagesInTopic")); ?>" ><img src="<?php echo($view_path); ?>images/filter.png" alt="<?php echo_html(text("AuthorMessagesInTopic")); ?>"></a>
    <?php endif; ?>
  </div>

  </td>
  </tr>
  </table>

  <?php else: 
    $avatar_appendix = "?rnd=" . rand(1000, 9999);
    if(!empty($user_data[$pinfo["user_id"]]["avatar_ctime"])) $avatar_appendix = "?ctime=" . $user_data[$pinfo["user_id"]]["avatar_ctime"];
  ?>

  <table class="aux_table" style="width:100%">
  <tr>
  <td>
  <div class="avatar_container">
  <a href="view_profile.php?uid=<?php echo_html($pinfo["user_id"]); ?>"><img src="<?php echo($user_data[$pinfo["user_id"]]["avatar"] . $avatar_appendix); ?>" alt="<?php echo_html($pinfo["author"]); ?>"><?php if(val_or_empty($user_data[$pinfo["user_id"]]["self_blocked"]) == 2): ?><img class="mourning_band" src="<?php echo($view_path . "images/mourning_band.png"); ?>" alt="<?php echo_html($pinfo["author"]); ?>"><?php endif; ?></a>
  </div>

  <div class="clear_both"></div>
  </td>
  <td>
    <?php
    $online_status = "";
    if(empty($settings["hide_online_status"]) && !empty($user_data[$pinfo["user_id"]]["online"]))
    {
      $online_status .= "&nbsp;<span class='online_text'>✓</span>";
    }

    if(!empty($user_data[$pinfo["user_id"]]["notes"]))
    {
    $online_status .= '&nbsp;<img class="has_notes_flag" src="' . $view_path . 'images/icon-edit.png" alt="' . escape_html($fmanager->get_display_name($pinfo["author"])) . '" title="' . escape_html(text("Notes")) . '" onclick="toggle_user_notes(' . $pid . ')">';
      $online_status .= "<div class='user_notes' id='user_notes_$pid' style='display:none'>";
      $online_status .= "<div style='position: absolute;right:2px;top:2px;cursor:pointer' onclick='toggle_user_notes($pid)'><img class='close_cross' src='{$view_path}images/cross.png' alt='" . escape_html(text('Close')) . "'></div>";
      $online_status .= $user_data[$pinfo["user_id"]]["notes"];
      $online_status .= "</div>";
    }
    ?>
    
    <div class="smart_break">
    <div class="author_container">
    <a href="view_profile.php?uid=<?php echo_html($pinfo["user_id"]); ?>"><?php echo_html($fmanager->get_display_name($pinfo["author"])); ?></a><?php echo($online_status); ?>
    </div>
    </div>

    <div class="member_status">
    <?php if(!empty($user_data[$pinfo["user_id"]]["is_admin"])): ?>
    <?php echo_html(text("Administrator")); ?>
    <?php elseif(!empty($user_data[$pinfo["user_id"]]["is_forum_moderator"])): ?>
    <?php echo_html(text("ForumModerator")); ?>
    <?php elseif(!empty($user_data[$pinfo["user_id"]]["is_topic_moderator"])): ?>
    <?php echo_html(text("TopicModerator")); ?>
    <?php else: ?>
    <?php echo_html(text("Member")); ?>
    <?php endif; ?>

    <?php if(!empty($user_data[$pinfo["user_id"]]["hidden"])): ?>
    <br><span class="error_text">[<?php echo_html(text("hidden")); ?>]</span>
    <?php endif; ?>

    <?php if(empty($user_data[$pinfo["user_id"]]["hidden"]) && !empty($user_data[$pinfo["user_id"]]["hidden_by_me"])): ?>
    <br><span class="error_text">[<?php echo_html(text("hidden_by_me")); ?>]</span>
    <?php endif; ?>

    <?php if(!empty($user_data[$pinfo["user_id"]]["ignored"])): ?>
    <br><span class="error_text">[<?php echo_html(text("ignored")); ?>]</span>
    <?php endif; ?>

    <?php if((!$fmanager->is_logged_in() || $fmanager->is_master_admin()) && !empty($user_data[$pinfo["user_id"]]["ignores_all_guests"])): ?>
    <br><span class="error_text">[<?php echo_html(text("ignoring_guests")); ?>]</span>
    <?php elseif(val_or_empty($user_data[$pinfo["user_id"]]["ignoring_me"]) == 1): ?>
    <br><span class="error_text">[<?php echo_html(text("ignoring_me")); ?>]</span>
    <?php elseif(val_or_empty($user_data[$pinfo["user_id"]]["ignoring_me"]) == 2): ?>
    <br><span class="error_text">[<?php echo_html(text("ignoring_guests_except")); ?>]</span>
    <?php elseif(val_or_empty($user_data[$pinfo["user_id"]]["ignoring_me"]) == 3): ?>
    <br><span class="error_text">[<?php echo_html(text("ignoring_new_guests")); ?>]</span>
    <?php endif; ?>

    <?php if(empty($user_data[$pinfo["user_id"]]["activated"])): ?>
    <br><span class="error_text">[<?php echo_html(text("notActivated")); ?>]</span>
    <?php endif; ?>

    <?php if(empty($user_data[$pinfo["user_id"]]["approved"])): ?>
    <br><span class="error_text">[<?php echo_html(text("notApproved")); ?>]</span>
    <?php endif; ?>

    <?php 
    if(!empty($user_data[$pinfo["user_id"]]["blocked"])): 
    $class = "";
    $death_sign = "";
    if(val_or_empty($user_data[$pinfo["user_id"]]["self_blocked"]) == 1) $class = "self_blocked";
    elseif(val_or_empty($user_data[$pinfo["user_id"]]["self_blocked"]) == 2) 
    {
      $class = "author_dead";
      $death_sign = "&nbsp;†";
    }
    ?>
    <br><span class="error_text <?php echo($class); ?>">[<?php echo_html(empty($user_data[$pinfo["user_id"]]["block_time_left"]) ? text("blocked") : sprintf(text("blocked_until"), $user_data[$pinfo["user_id"]]["block_time_left"])); ?><?php echo($death_sign); ?>]</span>
    <?php elseif(!empty($user_data[$pinfo["user_id"]]["forum_blocked"])): ?>
    <br><span class="error_text">[<?php echo_html(empty($user_data[$pinfo["user_id"]]["forum_block_time_left"]) ? text("forum_blocked") : sprintf(text("forum_blocked_until"), $user_data[$pinfo["user_id"]]["forum_block_time_left"])); ?>]</span>
    <?php elseif(!empty($user_data[$pinfo["user_id"]]["topic_blocked"])): ?>
    <br><span class="error_text">[<?php echo_html(text("topic_blocked")); ?>]</span>
    <?php endif; ?>

    </div>

    <div class="user_info">
    <?php if(!empty($user_data[$pinfo["user_id"]]["location"])): ?>
    <?php echo_html(text("Location")); ?>: <?php echo_html(val_or_empty($user_data[$pinfo["user_id"]]["location"])); ?><br>
    <?php endif; ?>

    <?php echo_html(text("Messages")); ?>: <span class="number"><?php echo_html(format_number(val_or_empty($user_data[$pinfo["user_id"]]["post_count"]))); ?></span><?php if(!empty($settings["rates_active"])): ?><span class="rating_info"><br>
    <?php echo_html(text("Rating")); ?>:
    <a href="search_topic.php?do_search=1&post_sort=desc&author_mode=author_liked&author=<?php echo(xrawurlencode($pinfo["author"])); ?>" class="carma_plus"><?php echo_html(val_or_empty($user_data[$pinfo["user_id"]]["carma_plus"])); ?></a>
    <?php if(!empty($settings["dislikes_active"])): ?>
    / <a href="search_topic.php?do_search=1&post_sort=desc&author_mode=author_disliked&author=<?php echo(xrawurlencode($pinfo["author"])); ?>" class="carma_minus"><?php echo_html(val_or_empty($user_data[$pinfo["user_id"]]["carma_minus"])); ?></a></span>
    <?php endif; ?>
    <?php endif; ?>
    </div>

  </td>
  <td class="author_actions">

  <div class="button_panel">
  <?php if(empty($settings["archive_mode"]) && $fmanager->get_user_id() != $pinfo["user_id"]): ?>  
    <?php if(empty($user_data[$pinfo["user_id"]]["subscribed"])): ?>
      <a class="subscribe_action_a_<?php echo_html($pinfo["user_id"]); ?>" href="<?php echo($message_url); ?>" title="<?php echo_html(text("SubscribeToUser")); ?>" onclick='return confirm_action("<?php echo_js(text("MsgConfirmSubscribeToUser"), true); ?>".replace(/%s/, "<?php echo_js($fmanager->get_display_name($pinfo["author"]), true); ?>"), { subscribe_action: "subscribe_to_user", uid: "<?php echo_js($pinfo["user_id"]); ?>", user_name: "<?php echo_js($pinfo["author"]); ?>", display_user_name: "<?php echo_js($fmanager->get_display_name($pinfo["author"])); ?>", guest_id: "<?php echo_html($pinfo["user_id"]); ?>" });'><img class="subscribe_action_img_<?php echo_html($pinfo["user_id"]); ?>" src="<?php echo($view_path); ?>images/subscribe_to_user.png" alt="<?php echo_html(text("SubscribeToUser")); ?>"></a>
    <?php else: ?>
      <a class="subscribe_action_a_<?php echo_html($pinfo["user_id"]); ?>" href="<?php echo($message_url); ?>" title="<?php echo_html(text("UnsubscribeFromUser")); ?>" onclick='return do_action({ subscribe_action: "unsubscribe_from_user", uid: "<?php echo_js($pinfo["user_id"]); ?>", user_name: "<?php echo_js($pinfo["author"]); ?>", display_user_name: "<?php echo_js($fmanager->get_display_name($pinfo["author"])); ?>", guest_id: "<?php echo_html($pinfo["user_id"]); ?>" });'><img class="subscribe_action_img_<?php echo_html($pinfo["user_id"]); ?>" src="<?php echo($view_path); ?>images/unsubscribe_from_user.png" alt="<?php echo_html(text("UnsubscribeFromUser")); ?>"></a>
    <?php endif; ?>
  <?php endif; ?>

  <?php if($fmanager->get_user_id() != $pinfo["user_id"] && empty($user_data[$pinfo["user_id"]]["hidden"]) && empty($_SESSION["hide_user_info"])): ?>
    <?php if(empty($user_data[$pinfo["user_id"]]["hidden_by_me"])): ?>
      <a class="hide_profile_a_<?php echo_html($pinfo["user_id"]); ?>" href="<?php echo($message_url); ?>" title="<?php echo_html(text("HideProfile")); ?>" onclick='return confirm_action("<?php echo_js(text("MsgConfirmProfileHide"), true); ?>".replace(/%s/, "<?php echo_js($fmanager->get_display_name($pinfo["author"]), true); ?>"), { profile_hide_action: "hide_user_profile", author_name: "<?php echo_js($pinfo["author"]); ?>", display_author_name: "<?php echo_js($fmanager->get_display_name($pinfo["author"])); ?>", uid: "<?php echo_js($pinfo["user_id"]); ?>" });'><img class="hide_profile_img_<?php echo_html($pinfo["user_id"]); ?>" src="<?php echo($view_path); ?>images/hide_profile.png" alt="<?php echo_html(text("HideProfile")); ?>"></a>
    <?php else: ?>
      <a class="hide_profile_a_<?php echo_html($pinfo["user_id"]); ?>" href="<?php echo($message_url); ?>" title="<?php echo_html(text("OpenProfile")); ?>" onclick='return confirm_action("<?php echo_js(text("MsgConfirmProfileOpen"), true); ?>".replace(/%s/, "<?php echo_js($fmanager->get_display_name($pinfo["author"]), true); ?>"), { profile_hide_action: "open_user_profile", author_name: "<?php echo_js($pinfo["author"]); ?>", display_author_name: "<?php echo_js($fmanager->get_display_name($pinfo["author"])); ?>", uid: "<?php echo_js($pinfo["user_id"]); ?>" });'><img class="hide_profile_img_<?php echo_html($pinfo["user_id"]); ?>" src="<?php echo($view_path); ?>images/show_profile.png" alt="<?php echo_html(text("OpenProfile")); ?>"></a>
    <?php endif; ?>
  <?php endif; ?>

  <?php if($fmanager->get_user_id() != $pinfo["user_id"]): ?>
    <?php if(empty($user_data[$pinfo["user_id"]]["ignored"])): ?>
    <a class="ignore_user_a_<?php echo_html($pinfo["user_id"]); ?>" href="<?php echo($message_url); ?>" title="<?php echo_html(text("PutToIgnoreList")); ?>" onclick='return confirm_action_with_comment("<?php echo_js(text("MsgConfirmUserIgnore"), true); ?>".replace(/%s/, "<?php echo_js($fmanager->get_display_name($pinfo["author"]), true); ?>"), { ignore_action: "put_to_ignore_list", author_name: "<?php echo_js($pinfo["author"]); ?>", display_author_name: "<?php echo_js($fmanager->get_display_name($pinfo["author"])); ?>", uid: "<?php echo_js($pinfo["user_id"]); ?>" });'><img class="ignore_user_img_<?php echo_html($pinfo["user_id"]); ?>" src="<?php echo($view_path); ?>images/ignore_user.png" alt="<?php echo_html(text("PutToIgnoreList")); ?>"></a>
    <?php else: ?>
    <a class="ignore_user_a_<?php echo_html($pinfo["user_id"]); ?>" href="<?php echo($message_url); ?>" title="<?php echo_html(text("RemoveFromIgnoreList")); ?>" onclick='return confirm_action("<?php echo_js(text("MsgConfirmUserUnignore"), true); ?>".replace(/%s/, "<?php echo_js($fmanager->get_display_name($pinfo["author"]), true); ?>"), { ignore_action: "remove_from_ignore_list", author_name: "<?php echo_js($pinfo["author"]); ?>", display_author_name: "<?php echo_js($fmanager->get_display_name($pinfo["author"])); ?>", uid: "<?php echo_js($pinfo["user_id"]); ?>" });'><img class="ignore_user_img_<?php echo_html($pinfo["user_id"]); ?>" src="<?php echo($view_path); ?>images/unignore_user.png" alt="<?php echo_html(text("RemoveFromIgnoreList")); ?>"></a>
    <?php endif; ?>
  <?php endif; ?>

  <?php if(empty($settings["archive_mode"]) && $fmanager->is_logged_in() && !$fmanager->is_master_admin() && empty($pinfo["no_private_messages"]) && $fmanager->get_user_id() != $pinfo["user_id"] && empty($user_data[$pinfo["user_id"]]["ignoring_me"])): ?>
  <a href="new_topic.php?fid=private&receiver=<?php echo_html($pinfo["user_id"]); ?>" title="<?php echo_html(text("SendPersonalMessage")); ?>"><img src="<?php echo($view_path); ?>images/send_mail.png" alt="<?php echo_html(text("SendPersonalMessage")); ?>"></a>
  <?php endif; ?>

    <?php if(empty($in_search)): ?>
    <a href="search.php?do_search=1&tid=<?php echo_html($pinfo["topic_id"]); ?>&author_mode=wrote_post&author=<?php echo(xrawurlencode($pinfo["author"])); ?>&start_from=<?php echo($pid); ?>" title="<?php echo_html(text("AuthorMessagesInTopic")); ?>" ><img src="<?php echo($view_path); ?>images/filter.png" alt="<?php echo_html(text("AuthorMessagesInTopic")); ?>"></a>
    <?php endif; ?>
  </div>

  </td>
  </tr>
  
  <?php if(!empty($user_data[$pinfo["user_id"]]["message"])): ?>
  <tr>
  <td colspan="2">
  <div class="user_message">
  <div class="smart_break"><?php echo(nl2br(escape_html(postprocess_message($user_data[$pinfo["user_id"]]["message"])))); ?></div>
  </div>
  </td>
  </tr>
  <?php endif; ?>
  
  
  </table>

  <?php endif; ?>
  </div>

</td>
</tr>

<?php 
$topic_name_prefix = "";
if (!empty($in_search)) {
  $topic_name_prefix = $pinfo["forum_name"] . " / ";
}

$topic_name = escape_html($topic_name_prefix . postprocess_message($pinfo["topic_name"]));
if(!reqvar_empty("search_keys"))
{
  $fmanager->highlight_found_keys($topic_name, reqvar("search_keys"), !reqvar_empty("with_morphology"));
}
?>

<tr>
<th class="subheader" data-author="<?php echo_html($pinfo["topic_author"]); ?>" data-pid="<?php echo_html($pid); ?>" data-pid="<?php echo_html($pid); ?>" data-tid="<?php echo_html($pinfo["topic_id"]); ?>" data-subject="<?php echo_html(postprocess_message($pinfo["topic_name"])); ?>" data-profiled_topic="<?php echo_html($pinfo["profiled_topic"]); ?>" data-stringent_rules="<?php echo_html($pinfo["stringent_rules"]); ?>"><div class="smart_break"><?php echo($topic_name); ?></div></th>
</tr>

<tr>
<td class="message_cell" data-author="<?php echo_html($pinfo["author"]); ?>" data-pid="<?php echo_html($pid); ?>" data-tid="<?php echo_html($pinfo["topic_id"]); ?>" data-subject="<?php echo_html(postprocess_message($pinfo["topic_name"])); ?>" data-profiled_topic="<?php echo_html($pinfo["profiled_topic"]); ?>" data-stringent_rules="<?php echo_html($pinfo["stringent_rules"]); ?>">

<div id="tags_list_<?php echo_html($pid); ?>" class="tags_list">
  
  <?php 
  $display = "none";
  if(!empty($pinfo["is_adult"]))
    $display = "block";
  ?>
  <div class="adult_tag" id="adult_tag_<?php echo_html($pid); ?>" style="display:<?php echo($display); ?>">#18+</div>
  
  <?php 
  $selected_tags = "";
  if($fmanager->is_logged_in() && !$fmanager->is_master_admin()): 
  ?>
  
      <?php if(!empty($pinfo["tags"])): ?>
      
      <?php 
      foreach($pinfo["tags"] as $tgid => $tgname): 
      if($selected_tags != "") $selected_tags .= ",";
      $selected_tags .= $tgid;
      ?>
      
      <div class="tag" title="<?php echo_html(text("AssignTags")); ?>" onclick="show_tag_selection_list('<?php echo_html($pid); ?>')">#<?php echo_html($tgname); ?></div>

      <?php endforeach; ?>
      
      <?php endif; ?>

      <div class="add_tags" title="<?php echo_html(text("AssignTags")); ?>" onclick="show_tag_selection_list('<?php echo_html($pid); ?>')">#<?php echo_html(text("tags")); ?></div>
  
  <?php endif; ?>
  
  <div class="clear_both"></div></div>

<?php if($fmanager->is_logged_in() && !$fmanager->is_master_admin()): ?>
<div id="manage_tags_list_<?php echo_html($pid); ?>" class="manage_tags_list" data-selected-tags="<?php echo_html($selected_tags); ?>" data-pid="<?php echo_html($pid); ?>">
<h3><?php echo_html(text("AssignTags")); ?></h3>
<div style="position: absolute;right:0px;top:0px;cursor:pointer" onclick="hide_manage_tags_list('<?php echo_html($pid); ?>')"><img src="<?php echo($view_path); ?>images/cross.png" alt="<?php echo_html(text("Close")); ?>"></div>
  
  <div class="clear_both insert_marker"></div>
    <div class="add_manage_tags">
    <input type="text" placeholder="<?php echo_html(text("NewTag")); ?>" id="new_tag_<?php echo_html($pid); ?>" autocomplete="off" onkeypress="return handle_new_tag_enter(event, '<?php echo_html($pid); ?>')">
    <input type="button" class="new_tag" id="add_new_tag_<?php echo_html($pid); ?>" onclick="add_new_tag('<?php echo_html($pid); ?>')">
    <input type="button" class="manage_tags" onclick="show_tag_manager()" title="<?php echo_html(text("ManageTags")); ?>">
    <div class="clear_both"></div>
    </div>
</div>
<?php endif; ?>

<?php if(empty($poll_rendered) && !empty($topic_data["poll_options"])): 
$poll_rendered = true;
?>

<form action="topic.php" id="poll_form" method="post" onsubmit="return vote('vote');">

<input type="hidden" name="hash" value="<?php echo_html($_SESSION["hash"]); ?>">
<input type="hidden" name="tid" value="<?php echo_html($pinfo["topic_id"]); ?>">
<input type="hidden" name="fid" value="<?php echo_html($pinfo["forum_id"]); ?>">
<input type="hidden" name="pid" value="<?php echo_html($pid); ?>">

<div class="poll_area">
  <div class="poll_header">
  <div class="smart_break"><?php echo_html($topic_data["poll_comment"]); ?></div>
  </div>

  <table class="poll_table">

  <?php
  foreach($topic_data["poll_options"] as $poid => $poinfo):
  $pct = 0;
  $pct_voters = 0;
  $width = 0;

  $factor = 1;
  if($topic_data["poll_total_votes"] > 0) $factor = $topic_data["poll_total_votes"]/$topic_data["max_votes_count"];

  if($poinfo["votes_count"] > 0 && $topic_data["poll_total_votes"] > 0)
  {
    $pct = 100 * $poinfo["votes_count"] / $topic_data["poll_total_votes"];

    $width = round(2*$factor*$pct);
  }
  
  if($poinfo["votes_count"] > 0 && $topic_data["poll_total_voters"] > 0)
  {
    $pct_voters = 100 * $poinfo["votes_count"] / $topic_data["poll_total_voters"];
  }
  
  $pct = format_number($pct, 2) . "&nbsp;%";
  $pct_voters = "&nbsp;(" . format_number($pct_voters, 2) . "&nbsp;%)";

  if(val_or_empty($topic_data["poll_results_delayed"]) == 1) 
  {
    $pct = "";
    $pct_voters = "";
  }

  if(!($topic_data["is_poll"] & 2))
  {
    $pct_voters = "";
  }

  if($width == 0) $width = 1;

  $width .= "px";

  if($topic_data["is_poll"] & 2) $control = '<input id="opt_' . $poid . '" type="checkbox" name="poll_votes[]" value="' . $poid . '">';
  else                           $control = '<input id="opt_' . $poid . '" type="radio" name="poll_votes" value="' . $poid . '">';

  if(!empty($_SESSION["blocked"]) || !empty($forum_data["blocked"]) || !empty($topic_data["blocked"]) ||
     val_or_empty($topic_data["poll_results_delayed"]) == 2 || val_or_empty($topic_data["poll_results_delayed"]) == 3 
    )
  {
    $control = "&nbsp;&nbsp;";
  }

  if(val_or_empty($topic_data["poll_results_delayed"]) == 2)
  {
    $control = "&nbsp;&nbsp;";
  }

  if(!empty($topic_data["i_have_voted"]))
  {
    $control = "&nbsp;&nbsp;";
    if(!empty($poinfo["my_vote"])) $control = "<b>✓</b>";
  }

  if(!$fmanager->is_logged_in() || $fmanager->is_master_admin()) $control = "&nbsp;&nbsp;";
  ?>

  <tr>
  <td><?php echo($control); ?></td>
  <td><div class="smart_break"><label for="opt_<?php echo($poid); ?>"><?php echo($poinfo["name"]); ?></label></div></td>
  <td><?php echo($pct); ?></td>
  <td style="position: relative">

    <?php if(val_or_empty($topic_data["poll_results_delayed"]) != 1): ?>
    <table class="aux_table">
    <tr>
    <?php
    $cursor = "";
    $onclick = "";
    if(!empty($poinfo["votes_count"]) && $topic_data["is_poll"] & 4)
    {
      $cursor = "cursor: pointer;";
      $onclick = " onclick='return toggle_voted_users(\"$poid\")'";
    }
    ?>
    <td><div class="statistics_bar" style="<?php echo($cursor); ?>width:<?php echo($width); ?>" <?php echo($onclick); ?>></div></td>
    <td>&nbsp;<?php if(!empty($poinfo["votes_count"]) && $topic_data["is_poll"] & 4) echo("<a href='" . escape_html($message_url) . "' class='view_voted' onclick='return toggle_voted_users(\"$poid\")'>" . $poinfo["votes_count"] . "</a>"); else echo_html($poinfo["votes_count"]); ?><?php echo($pct_voters); ?>
    </td>
    </tr>
    </table>

    <?php if(!empty($poinfo["votes_count"]) && $topic_data["is_poll"] & 4): ?>

      <div id="voted_users_<?php echo_html($poid); ?>" class="voted_users" style="display:none">

      <div style="position: absolute;right:2px;top:2px;cursor:pointer" onclick="toggle_voted_users('<?php echo_html($poid); ?>')"><img src="<?php echo($view_path); ?>images/cross.png" alt="<?php echo_html(text("Close")); ?>"></div>

      <?php
      $option_caption = trim(strip_tags($poinfo["name"]));
      if(!empty($option_caption)) $option_caption .= ":";
      ?>

      <div class="voted_users_caption"><?php echo($option_caption); ?></div>

      <?php foreach($poinfo["users"] as $uinfo): 
      if(!empty($uinfo["user_ignored"]))
        $voted_user = "<span class='not_preferred'>" . escape_html($uinfo["user"]) . "</span>";
      else
        $voted_user = "<a href='view_profile.php?uid=$uinfo[user_id]' onclick='hide_all_popups()'>" . escape_html($uinfo["user"]) . "</a>";
      ?>
      <p><?php echo_html($uinfo["tm"]); ?>: <?php echo($voted_user); ?></p>
      <?php endforeach; ?>

      </div>

    <?php endif; ?>
    <?php endif; // hidden before publish ?>

  </td>
  </tr>
  <?php endforeach; ?>

  </table>

  <div class="poll_footer">

    <table class="poll_action_table">
    <tr>
    <td>
    <?php if(!$fmanager->is_logged_in() || $fmanager->is_master_admin() ||
             !empty($_SESSION["blocked"]) || !empty($forum_data["blocked"]) || !empty($topic_data["blocked"])
            ): 
    ?>
        <?php if(val_or_empty($topic_data["poll_results_delayed"]) == 2 || val_or_empty($topic_data["poll_results_delayed"]) == 3): ?>
        <?php echo_html(text("MsgPollCompleted")); ?>
        <?php else: ?>
        &nbsp;
        <?php endif; ?>
    <?php elseif(val_or_empty($topic_data["poll_results_delayed"]) == 2 || val_or_empty($topic_data["poll_results_delayed"]) == 3): ?>
    <?php echo_html(text("MsgPollCompleted")); ?>
    <?php elseif(empty($topic_data["i_have_voted"])): ?>
    <input type="submit" class="standard_button" value="<?php echo_html(text("Vote")); ?>"/>
    <?php else: ?>

    <table class="aux_table">
    <tr>
    <td><?php echo_html(text("MsgAlreadyVoted")); ?></td>
    <td>&nbsp;
    <?php if($fmanager->is_logged_in() && !$fmanager->is_master_admin() && 
             !empty($topic_data["i_have_voted"]) && !empty($topic_data["may_cancel_vote"]) && val_or_empty($topic_data["poll_results_delayed"]) != 2): ?>
    <input type="button" class="standard_button" value="<?php echo_html(text("Cancel")); ?>" onclick="vote('cancel_vote')"/>
    <?php endif; ?>
    </td>
    </tr>
    </table>

    <?php endif; ?>
    </td>
    <td><?php echo_html(text("Voted")); ?>: <?php echo_html(val_or_empty($topic_data["poll_total_voters"])); ?></td>
    
    <?php if((val_or_empty($topic_data["poll_results_delayed"]) == 0 || val_or_empty($topic_data["poll_results_delayed"]) == 1) &&
             !empty($topic_data["poll_total_voters"]) &&
             ($topic_data["read_marker"] == $READ_MARKER || $fmanager->is_admin() || $fmanager->is_forum_moderator($pinfo["forum_id"]) || $fmanager->is_topic_moderator($pinfo["topic_id"]))): 
    
    $button_caption = (val_or_empty($topic_data["poll_results_delayed"]) == 1) ? text("Publish") : text("Close");
    ?>
    <td>
    <input type="button" class="standard_button" value="<?php echo_html($button_caption); ?>" onclick='return confirm_poll_action("<?php echo_js(text("MsgPollCompleteConfirm")); ?>", "close_poll");'/>
    </td>
    <?php elseif((val_or_empty($topic_data["poll_results_delayed"]) == 2 || val_or_empty($topic_data["poll_results_delayed"]) == 3) &&
             ($topic_data["read_marker"] == $READ_MARKER || $fmanager->is_admin() || $fmanager->is_forum_moderator($pinfo["forum_id"]) || $fmanager->is_topic_moderator($pinfo["topic_id"]))): ?>
    <td>
    <input type="button" class="standard_button" value="<?php echo_html(text("Open")); ?>" onclick='return confirm_poll_action("<?php echo_js(text("MsgPollOpenConfirm")); ?>", "open_poll");'/>
    </td>
    <?php endif; ?>
    
    </tr>
    </table>

  </div>
</div>

<div class="clear_both"></div>
<div class="poll_comment">
<?php if($topic_data["is_poll"] & 4): ?><div><?php echo_html(text("OpenPollComment")); ?></div><?php endif; ?>
<?php if($topic_data["poll_results_delayed"] == 1): ?><div><?php echo_html(text("DelayedPollComment")); ?></div><?php endif; ?>
</div>

</form>

<?php endif; ?>

<?php if($post_ignored): ?>
<input type="button" class="standard_button" value="<?php echo_html(text("Show")); ?>" onclick="show_ignored_post(this, '<?php echo_html($pid); ?>')">
<?php endif; ?>

<!-- must be without spaces, because of sibling getting -->
<div class="message_text" id="message_text_<?php echo_html($pid); ?>"><?php echo($pinfo["html_content"]); ?></div><div class="message_text_more_wrapper"><div class="message_text_more">...</div></div>

</td>
</tr>
<tr>
<td class="message_action_cell">

<?php if(!empty($user_data[$pinfo["user_id"]]["signature"])): ?>
<div class="message_signature"><?php echo($user_data[$pinfo["user_id"]]["signature"]); ?></div><div class="clear_both"></div>
<?php endif; ?>

<?php
$display = "display:none";
if(!empty($pinfo["last_warning"]))
{
  $display = "display:block";
}

$warned_by = $fmanager->get_display_name($pinfo["last_warned_by"]);

if(!$fmanager->is_moderator_log_visible() || (val_or_empty($settings["moderator_log"]) == "all_names_hidden" && !$fmanager->is_moderator()))
{
  $warned_by = text("Moderator");
}
?>

<div class="moderator_warning" style="<?php echo($display); ?>" id="modwarning_<?php echo_html($pid); ?>">
<div id="modwarning_moderator_<?php echo_html($pid); ?>" class="moderator_name"><?php echo_html($warned_by); ?>:</div>
<div id="modwarning_warning_<?php echo_html($pid); ?>"><?php echo($pinfo["last_warning"]); ?></div>
</div>

<!-- BEGIN: versions / rating -->
<div class="post_status_bar">

<!-- BEGIN: version container -->
<div class="version_container">

<?php if(!empty($pinfo["last_updated"])): ?>

<div class="update_info" id="update_info_<?php echo_html($pid); ?>">
<?php
  $changed_by = $fmanager->get_display_name($pinfo["last_updated_by"]);

// if the author changed his message, we show it always.
// if the moderator changed a message, we show it due to settings.
if(empty($pinfo["self_edited"]) && $hide_moderator_names)
{
  $changed_by = text("Moderator");
}

$txt = text("Modified") . ": " . smart_date($pinfo["last_updated"]) . " - " . $changed_by;
echo_html($txt);
?>
</div>
<?php endif; ?>

<?php if(!empty($pinfo["versions"]) && ($fmanager->is_admin() || $fmanager->is_forum_moderator($pinfo["forum_id"]) || $fmanager->is_topic_moderator($pinfo["topic_id"]) || (!empty($pinfo["user_id"]) && $pinfo["user_id"] == $fmanager->get_user_id()) || ($pinfo["read_marker"] == $READ_MARKER))): ?>
<div class="message_versions">

<select onchange="do_action({topic_action: 'load_version', post: '<?php echo_html($pid); ?>', version: this.value, version_list: this })">
<option value=""><?php echo_html(smart_date($pinfo["last_updated"])); ?> - <?php echo_html($changed_by); ?></option>
<?php foreach($pinfo["versions"] as $vid => $vinfo): 
$changed_by = $fmanager->get_display_name($vinfo["author"]);
if(empty($vinfo["self_edited"]) && $hide_moderator_names) $changed_by = text("Moderator");
?>
<option value="<?php echo_html($vid); ?>"><?php echo_html(smart_date($vinfo["date"])); ?> - <?php echo_html($changed_by); ?></option>
<?php endforeach; ?>
</select>

[<span><?php echo_html(text("Versions")); ?></span>]

<div id="loading_version_<?php echo_html($pid); ?>" class="loading_version">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>

</div>
<?php endif; ?>

<div class="clear_both"></div>

</div> 
<!-- END: version container -->

<?php if(!empty($settings["rates_active"])): ?>

<?php
$liked_display = "display:none";
if(!empty($pinfo["liked_users"])) $liked_display = "display:block";
$disliked_display = "display:none";
if(!empty($pinfo["disliked_users"])) $disliked_display = "display:block";
?>

<div id="post_rating_<?php echo_html($pid); ?>" class="post_rating">
<table>
<tr>

<td><?php echo_html(text("Rating")); ?>:</td>

<?php if(!empty($may_rate) && $fmanager->is_logged_in() && !$fmanager->is_master_admin() && $pinfo["user_id"] != $fmanager->get_user_id()):
$display = empty($pinfo["already_rated"]) ? "" : "style='display:none'";
?>
<td class="arrow plus" id="post_rating_up_<?php echo_html($pid); ?>" <?php echo($display); ?> onclick='do_action({ topic_action: "rate_post", post: "<?php echo_js($pid); ?>", rating: 1 })'></td>
<?php endif; ?>

<td>
      <span class="carma_plus" id="post_rating_plus_<?php echo_html($pid); ?>"><?php echo($pinfo["carma_plus"]); ?></span>
      <?php if(!empty($settings["dislikes_active"])): ?>
      / <span class="carma_minus" id="post_rating_minus_<?php echo_html($pid); ?>"><?php echo($pinfo["carma_minus"]); ?></span> 
      <?php endif; ?>
</td>

<?php if(!empty($may_rate) && $fmanager->is_logged_in() && !$fmanager->is_master_admin() && $pinfo["user_id"] != $fmanager->get_user_id() && !empty($settings["dislikes_active"])):
$display = empty($pinfo["already_rated"]) ? "" : "style='display:none'";
?>
  <td class="arrow minus" id="post_rating_down_<?php echo_html($pid); ?>" <?php echo($display); ?> onclick='confirm_action("<?php echo_js(text("MsgConfirmDislike"), true); ?>".replace(/%s/, "<?php echo_js($fmanager->get_display_name($pinfo["author"]), true); ?>"), { topic_action: "rate_post", post: "<?php echo_js($pid); ?>", rating: -1 })'></td>
<?php endif; ?>

<?php if(!empty($may_rate) && $fmanager->is_logged_in() && !$fmanager->is_master_admin() && $pinfo["user_id"] != $fmanager->get_user_id()):
$display = !empty($pinfo["may_reset_rating"]) && !empty($pinfo["already_rated"]) ? "" : "style='display:none'";
?>
<td class="arrow reset" title="<?php echo_html(text("ResetMyRating")); ?>" id="post_rating_del_<?php echo_html($pid); ?>" <?php echo($display); ?> onclick='do_action({ topic_action: "reset_rating", post: "<?php echo_js($pid); ?>" })'></td>
<?php endif; ?>

<td id="post_rating_loading_<?php echo_html($pid); ?>" class="post_rating_loading" style="display:none">
</td>

</tr>
</table>
</div> <!-- post_rating -->

  <div class="rating_users_cotainer">
  <div class="liking_users" style="<?php echo($liked_display); ?>"><?php echo_html(text("Liked")); ?>:
  <?php
  if(!empty($pinfo["liked_users"])):
  $count = min(count($pinfo["liked_users"]), 4);
  $i = 1;
  foreach($pinfo["liked_users"] as $uid => $uname):
  if($i > 3) break;

  if($i < $count) $comma = ", ";
  else            $comma = "";

  $i++;
  ?>
  <a href="view_profile.php?uid=<?php echo_html($uid); ?>"><?php echo_html($uname); ?></a><?php echo($comma); ?>
  <?php
  endforeach;
  ?>

    <?php if(count($pinfo["liked_users"]) > 3): ?>

    <a class="more_users" href="<?php echo($message_url); ?>" onclick="return toggle_liked_users('<?php echo_html($pid); ?>')"><?php echo_html(text("MoreUsers")); ?></a>

      <div id="liked_users_<?php echo_html($pid); ?>" class="liked_users" style="display:none">

      <div style="position: absolute;right:2px;top:2px;cursor:pointer" onclick="toggle_liked_users('<?php echo_html($pid); ?>')"><img src="<?php echo($view_path); ?>images/cross.png" alt="<?php echo_html(text("Close")); ?>"></div>

      <span style="font-weight: bold"><?php echo_html(text("Liked")); ?>:</span><br>

      <?php foreach($pinfo["liked_users"] as $uid => $uname): ?>
      <a href="view_profile.php?uid=<?php echo_html($uid); ?>" onclick="hide_all_popups()"><?php echo_html($uname); ?></a>
      <?php endforeach; ?>

      </div>

    <?php endif; ?>

  <?php
  endif;
  ?>
  </div> <!-- liking_users -->

  <div class="disliking_users" style="<?php echo($disliked_display); ?>"><?php echo_html(text("Disliked")); ?>:
  <?php
  if(!empty($pinfo["disliked_users"])):
  $count = min(count($pinfo["disliked_users"]), 4);
  $i = 1;
  foreach($pinfo["disliked_users"] as $uid => $uname):
  if($i > 3) break;

  if($i < $count) $comma = ", ";
  else            $comma = "";

  $i++;
  ?>
  <a href="view_profile.php?uid=<?php echo_html($uid); ?>" ><?php echo_html($uname); ?></a><?php echo($comma); ?>
  <?php
  endforeach;
  ?>

    <?php if(count($pinfo["disliked_users"]) > 3): ?>

    <a class="more_users" href="<?php echo($message_url); ?>" onclick="return toggle_disliked_users('<?php echo_html($pid); ?>')"><?php echo_html(text("MoreUsers")); ?></a>

      <div id="disliked_users_<?php echo_html($pid); ?>" class="liked_users" style="display:none">

      <div style="position: absolute;right:2px;top:2px;cursor:pointer" onclick="toggle_disliked_users('<?php echo_html($pid); ?>')"><img src="<?php echo($view_path); ?>images/cross.png" alt="<?php echo_html(text("Close")); ?>"></div>

      <span style="font-weight: bold"><?php echo_html(text("Disliked")); ?>:</span><br>

      <?php foreach($pinfo["disliked_users"] as $uid => $uname): ?>
      <a href="view_profile.php?uid=<?php echo_html($uid); ?>"  onclick="hide_all_popups()"><?php echo_html($uname); ?></a>
      <?php endforeach; ?>

      </div>

    <?php endif; ?>

  <?php
  endif;
  ?>
  </div> <!-- disliking_users -->

  <div class="clear_both"></div>
  </div> <!-- rating_users_cotainer -->

<?php endif; ?>

<div class="clear_both"></div>
</div>
<!-- END: versions / rating -->

</td>
</tr>
<tr>
<td class="post_footer">

  <div class="user_post_actions">

  <?php if($may_write && $may_answer): ?>
  <span class="separator">|</span> <a href="<?php echo($message_url . "&do_answer=" . $pid . "&answer_author=" . xrawurlencode($pinfo["author"])); ?>" class="answer" onclick='return answer_to_author("<?php echo_html($pid); ?>", "<?php echo_js($pinfo["author"], true); ?>", "<?php echo_js($pinfo["topic_id"], true); ?>", "<?php echo_js(postprocess_message($pinfo["topic_name"]), true); ?>", <?php echo(!empty($pinfo["profiled_topic"]) ? 1 : 0); ?>, <?php echo(!empty($pinfo["stringent_rules"]) ? 1 : 0); ?>);'><?php echo_html(empty($pinfo["ip"]) ? text("Answer") : text("AnswerShort")); ?></a>
  <?php endif; ?>

  <?php if($may_answer): 
  $citate_caption = empty($pinfo["ip"]) ? text("Citate") : text("CitateShort");
  if(empty($may_write)) $citate_caption = text("CitateForCopy");
  ?>
  <span class="separator">|</span> <a href="<?php echo($message_url . "&do_citate=" . $pid); ?>" onclick='return citate_post("<?php echo_html($pid); ?>", "<?php echo_js($pinfo["topic_id"], true); ?>", "<?php echo_js(postprocess_message($pinfo["topic_name"]), true); ?>", <?php echo(!empty($pinfo["profiled_topic"]) ? 1 : 0); ?>, <?php echo(!empty($pinfo["stringent_rules"]) ? 1 : 0); ?>);'><?php echo_html($citate_caption); ?></a> 
  <?php endif; // may_answer ?>

  <?php if($may_write): ?>
  <span class="separator">|</span> <a href="<?php echo($message_url . "&do_write=" . $pid); ?>" onclick='return new_message("", "<?php echo_html($pid); ?>", "<?php echo_js($pinfo["topic_id"], true); ?>", "<?php echo_js(postprocess_message($pinfo["topic_name"]), true); ?>", <?php echo(!empty($pinfo["profiled_topic"]) ? 1 : 0); ?>, <?php echo(!empty($pinfo["stringent_rules"]) ? 1 : 0); ?>);'><?php echo_html(empty($pinfo["ip"]) ? text("NewMessage") : text("NewMessageShort")); ?></a> 
  <?php endif; // may write ?>

  <?php
  if($fmanager->is_logged_in() && 
      empty($topic_data["publish_delay"]) &&  
      (empty($blocked) || !empty($blocked_only_topic)) &&
     (val_or_empty($pinfo["topic_private"]) == 0 || (val_or_empty($pinfo["topic_private"]) == 2 && !$fmanager->is_topic_moderator($pinfo["topic_id"])))
    ):
  ?>
  <span class="separator">|</span> <a href="<?php echo($message_url); ?>" onclick='return show_post_comment("<?php echo_js(text("Complain"), true); ?>", "<?php echo_js($fmanager->get_display_name($pinfo["author"]), true); ?>", "<?php echo_js($pid); ?>", "complain")'><?php echo_html(empty($pinfo["ip"]) ? text("Complain") : text("ComplainShort")); ?></a>
  <?php endif; ?>
  
  <?php if(!empty($pinfo["replies"])): ?>
  <span class="separator">|</span> <a href="search.php?do_search=1&replies_to=<?php echo_html($pid); ?>&author_mode=wrote_post" target="_blank"><?php echo_html(text("AnswersShort")); ?></a>
  <?php endif; ?>
  
  <?php if(!empty($in_search)): ?>
  <?php
  $search_keys_appendix = "";
  if(!reqvar_empty("search_keys"))
  {
    $search_keys_appendix .= "&search_keys=" . xrawurlencode(reqvar("search_keys"));
    if(!reqvar_empty("with_morphology")) $search_keys_appendix .= "&with_morphology=1";
  }
  ?>
  <span class="separator">|</span> <a href="topic.php?fid=<?php echo_html($pinfo["forum_id_for_url"]); ?>&tid=<?php echo_html($pinfo["topic_id"]); ?>&from_search=1<?php echo($search_keys_appendix); ?>&msg=<?php echo_html($pid); ?>" ><?php echo_html(empty($pinfo["ip"]) ? text("GotoTopic") : text("GotoTopicShort")); ?></a> 
  <?php endif; ?>

  &nbsp;

  </div>

  <?php if(!empty($pinfo["ip"])): ?>
  <div class="post_ip_info">
  <?php
  $ip = escape_html($pinfo["ip"]);
  if(!empty($settings["whois_server"]))
  {
    $ip_url = str_ireplace("{ip}", $ip, str_replace("&", "&", $settings["whois_server"]));

    $ip = "<a href='$ip_url' target='_blank'>" . $ip . "</a>";
  }

  if ($fmanager->is_moderator()) {
    $ip_sign = "✘";
    $ip_class = "ip_moderation";
    if(!empty($pinfo["ip_blocked"]))
    {
      $ip_sign = "✘";
      $ip_class = "ip_moderation ip_blocked";
    }
    $ip .= "&nbsp;<a href='ip_moderation.php?type=moderation&ip=" . xrawurlencode($pinfo["ip"]) . "&author=" . xrawurlencode($pinfo["author"]) . "' class='moderator_link $ip_class' title='" . escape_html(text("Moderate")) . "'>$ip_sign</a>";

    if($fmanager->is_admin())
    {
      $ip_sign = "✓";
      $ip_class = "guest_ip";
      if(!empty($pinfo["guest_ip_whitelisted"]))
      {
        $ip_sign = "✓";
        $ip_class = "ip_whitelisted";
      }
      $ip .= "&nbsp;<a href='guest_ips.php?search_key=" . xrawurlencode($pinfo["ip"]) . "' class='ip_moderation $ip_class' title='" . escape_html(text("GuestIPs")) . "'>$ip_sign</a>";

      if(!empty($pinfo["tor_ip"]))
      {
        $ip_class = "ip_moderation " . val_or_empty($pinfo["tor_ip_block_level"]);
        $ip_sign = "Tor";
        $ip .= "&nbsp;<a href='tor_ips.php?search_key=" . xrawurlencode($pinfo["ip"]) . "' class='moderator_link $ip_class' title='" . escape_html(text("Moderate")) . "'>$ip_sign</a>";
      }
    }

    $ip .= "&nbsp;<a href='ip_moderation.php?type=ip_users&ip=" . xrawurlencode($pinfo["ip"]) . "&author=" . xrawurlencode($pinfo["author"]) . "' title='" . escape_html(text("Authors")) . "'><img src='" . $view_path . "images/users.png' alt='" . escape_html(text("Authors")) . "' class='ip_authors'></a>";
  } elseif($fmanager->is_logged_in()) {
    $ip .= "&nbsp;<a href='ip_moderation.php?type=user_ips&&user=" . xrawurlencode($pinfo["author"]) . "' title='" . escape_html(text("ShowAuthorIPs")) . "'><img src='" . $view_path . "images/ips.png' alt='" . escape_html(text("ShowAuthorIPs")) . "' class='author_ips'></a>";
  }

  ?>
  IP: <?php echo($ip); ?>
  </div>
  <?php endif; // ip visible ?>

  <div class="clear_both">
  </div>

  <?php if(empty($pinfo["is_system"]) && (!empty($pinfo["moderatable"]) || !empty($pinfo["editable"]) || $fmanager->is_admin() || ($fmanager->is_moderator() && !empty($pinfo["user_id"])))): ?>
  <div class="moderator_post_actions">
  
    <?php
    if(!empty($pinfo["moderatable"]) || !empty($pinfo["user_id"]))
    {
      if($fmanager->is_admin()) 
        echo_html(text("Administrator") . ":");
      elseif($fmanager->is_forum_moderator($pinfo["forum_id"]) || $fmanager->is_topic_moderator($pinfo["topic_id"]))
        echo_html(text("Moderator") . ":");
      elseif(!empty($pinfo["editable"])) 
        echo_html(text("Author") . ":");
    }
    elseif(!empty($pinfo["editable"]))
    {
      echo_html(text("Author") . ":");
    }
    
    $subject_editable = 0;
    if($fmanager->is_admin() || $fmanager->is_forum_moderator($pinfo["forum_id"]) || $fmanager->is_topic_moderator($pinfo["topic_id"]) ||
       (!empty($first_topic_post) && !empty($pinfo["topic_author_id"]) && $pinfo["topic_author_id"] == $fmanager->get_user_id()) || 
       (!empty($first_topic_post) && $pinfo["topic_author_read_marker"] == $READ_MARKER)
      )
    {
      $subject_editable = 1;
    }
    ?>

    <?php if(!empty($pinfo["editable"])): ?>
      <?php if(empty($pinfo["deleted"])): ?>
          <!-- do not allow the simple user delete the first post -->
          <?php if(empty($first_topic_post) || !empty($topic_data["publish_delay"]) || !empty($pinfo["moderatable"])): ?>
          <span class="separator">|</span> <a href="<?php echo($message_url); ?>" id="delete_restore_link_<?php echo_html($pid); ?>" data-pid="<?php echo_html($pid); ?>" class="moderator_link" onclick='return select_and_confirm("<?php echo_js(text("MsgConfirmPostsDelete"), true); ?>", this, { topic_action: "delete_post", topic: "<?php echo_html($pinfo["topic_id"]); ?>", forum: "<?php echo_html($pinfo["forum_id"]); ?>" })'><?php echo_html(text("Delete")); ?></a> 
          <?php endif; ?>
      <?php elseif(!empty($pinfo["moderatable"])): ?>
      <span class="separator">|</span> <a href="<?php echo($message_url); ?>" id="delete_restore_link_<?php echo_html($pid); ?>" data-pid="<?php echo_html($pid); ?>" class="moderator_link" onclick='return select_and_do(this, { topic_action: "restore_post", topic: "<?php echo_html($pinfo["topic_id"]); ?>", forum: "<?php echo_html($pinfo["forum_id"]); ?>" })'><?php echo_html(text("Restore")); ?></a> 
      <?php endif; ?>

      <span class="separator">|</span> <a href="<?php echo($message_url); ?>" class="moderator_link" onclick="return start_editing({ topic_action: 'edit_message', post: '<?php echo_html($pid); ?>', user_id: '<?php echo_html($pinfo["user_id"]); ?>', subject_editable: '<?php echo_js($subject_editable); ?>', profiled_topic: '<?php echo_js($pinfo["profiled_topic"]); ?>', stringent_rules: '<?php echo_js($pinfo["stringent_rules"]); ?>' });"><?php echo_html(text("Edit")); ?></a>
      
      <?php if(!empty($pinfo["profiled_topic"]) && empty($pinfo["pinned"])): ?>
      <?php if(empty($pinfo["is_comment"])): ?>
      <span class="separator">|</span> <a href="<?php echo($message_url); ?>" id="convert_link_<?php echo_html($pid); ?>" data-pid="<?php echo_html($pid); ?>" class="moderator_link" onclick='return select_and_do(this, { topic_action: "convert_to_comment", topic: "<?php echo_html($pinfo["topic_id"]); ?>", forum: "<?php echo_html($pinfo["forum_id"]); ?>" })'><?php echo_html(text("MakePostToComment")); ?></a> 
      <?php else: ?>
      <span class="separator">|</span> <a href="<?php echo($message_url); ?>" id="convert_link_<?php echo_html($pid); ?>" data-pid="<?php echo_html($pid); ?>" class="moderator_link" onclick='return select_and_do(this, { topic_action: "convert_to_thematic", topic: "<?php echo_html($pinfo["topic_id"]); ?>", forum: "<?php echo_html($pinfo["forum_id"]); ?>" })'><?php echo_html(text("MakePostThematic")); ?></a> 
      <?php endif; ?>
      <?php endif; ?>
      
      <?php if(empty($pinfo["is_adult"])): ?>
      <span class="separator">|</span> <a href="<?php echo($message_url); ?>" id="convert_adult_<?php echo_html($pid); ?>" data-pid="<?php echo_html($pid); ?>" class="moderator_link" onclick='return select_and_do(this, { topic_action: "convert_to_adult", topic: "<?php echo_html($pinfo["topic_id"]); ?>", forum: "<?php echo_html($pinfo["forum_id"]); ?>" })'><?php echo_html(text("MakePostAdult")); ?></a> 
      <?php else: ?>
      <span class="separator">|</span> <a href="<?php echo($message_url); ?>" id="convert_adult_<?php echo_html($pid); ?>" data-pid="<?php echo_html($pid); ?>" class="moderator_link" onclick='return select_and_do(this, { topic_action: "convert_to_nonadult", topic: "<?php echo_html($pinfo["topic_id"]); ?>", forum: "<?php echo_html($pinfo["forum_id"]); ?>" })'><?php echo_html(text("MakePostNonAdult")); ?></a> 
      <?php endif; ?>
    <?php else: // if not editable ?>
      <?php if(!empty($pinfo["moderatable"]) && (!empty($first_topic_post) || !empty($pinfo["pinned"]))): ?>
      <span class="separator">|</span> <a href="<?php echo($message_url); ?>" class="moderator_link" onclick="return start_editing({ topic_action: 'edit_message', post: '<?php echo_html($pid); ?>', user_id: '<?php echo_html($pinfo["user_id"]); ?>', subject_editable: '<?php echo_js($subject_editable); ?>', profiled_topic: '<?php echo_js($pinfo["profiled_topic"]); ?>', stringent_rules: '<?php echo_js($pinfo["stringent_rules"]); ?>' });"><?php echo_html(text("Edit")); ?></a>
      <?php endif; // if moderatable ?>
    <?php endif; // if editable ?>

    <?php if($fmanager->is_admin() || $fmanager->is_moderator() || $fmanager->is_topic_moderator($pinfo["topic_id"])): ?>
    
    <?php if(empty($settings["archive_mode"]) && val_or_empty($pinfo["topic_private"]) != 1 && !empty($pinfo["moderatable"])): ?>
    <span class="separator">|</span> <a href="<?php echo($message_url); ?>" class="moderator_link" onclick='return show_post_comment("<?php echo_js(text("Warn"), true); ?>", "<?php echo_js($fmanager->get_display_name($pinfo["author"]), true); ?>", "<?php echo_js($pid); ?>", "warn")'><?php echo_html(text("Warn")); ?></a> 
    <?php endif; // if not simple private ?>
    
    <?php if(!empty($pinfo["moderatable"]) || !empty($pinfo["user_id"]) || ($fmanager->global_ban_allowed() && empty($pinfo["user_id"]) && !empty($pinfo["avatar"]))): ?>
    <span class="separator">|</span> <a href="<?php echo($message_url); ?>" class="moderator_link" onclick="return toggle_moderator_post_more_actions('<?php echo_html($pid); ?>')"><?php echo_html(text("More")); ?></a>
    <?php endif; // if not simple private ?>
    
    <div id="moderator_post_more_<?php echo_html($pid); ?>" class="moderator_post_more_actions" style="display:none">

      <div style="position: absolute;right:2px;top:2px;cursor:pointer" onclick="toggle_moderator_post_more_actions('<?php echo_html($pid); ?>')"><img src="<?php echo($view_path); ?>images/cross.png" alt="<?php echo_html(text("Close")); ?>"></div>

      <span style="font-weight: bold"><?php echo_html($fmanager->is_admin() ? text("Administrator") : text("Moderator")); ?>:</span><br>

      <?php if(empty($in_search) && !empty($pinfo["moderatable"])): ?>
        <?php if(!empty($pinfo["pinned"])): ?>
          <a href="<?php echo($message_url); ?>" id="pin_post_link_<?php echo_html($pid); ?>" class="moderator_link" onclick='return do_action({ topic_action: "unpin_post", post: "<?php echo_js($pid); ?>", topic: "<?php echo_js($pinfo["topic_id"]); ?>", forum: "<?php echo_js($pinfo["forum_id"]); ?>" })'><?php echo_html(text("UnpinMessage")); ?></a>
        <?php else: ?>
          <a href="<?php echo($message_url); ?>" id="pin_post_link_<?php echo_html($pid); ?>" class="moderator_link" onclick='return do_action({ topic_action: "pin_post", post: "<?php echo_js($pid); ?>", topic: "<?php echo_js($pinfo["topic_id"]); ?>", forum: "<?php echo_js($pinfo["forum_id"]); ?>" })'><?php echo_html(text("PinMessage")); ?></a>
        <?php endif; ?>
      <?php endif; // may_moderate_post ?>
      
      <?php if(!empty($pinfo["editable"]) && empty($in_search) && !empty($pinfo["moderatable"]) && empty($pinfo["topic_private"])): ?>
        <a href="<?php echo($message_url); ?>" class="moderator_link" data-pid="<?php echo_html($pid); ?>" onclick="{ select_current(this); return select_target_topic_for_move('move_posts'); }"><?php echo_html(text("MovePost")); ?></a>
        <a href="<?php echo($message_url); ?>" class="moderator_link" data-pid="<?php echo_html($pid); ?>" onclick="{ select_current(this); return select_target_topic_for_move('move_posts_from'); }"><?php echo_html(text("MoveStartingFrom")); ?></a> 
      <?php endif; // if may_moderate_post ?>
      
      <?php if(!empty($pinfo["user_id"])): ?>
        
        <?php if($fmanager->is_admin() || $fmanager->is_moderator()): ?>
        <a href="user_moderation.php?uid=<?php echo_html($pinfo["user_id"]); ?>" class="moderator_link"><?php echo_html(text("ModerateUser")); ?></a>
        <?php endif; // admin or forum moderator ?>
        
        <?php if($fmanager->is_moderator()): ?>
          <?php if(!empty($settings["rates_active"])): ?>
          <a href="rate_moderation.php?uid=<?php echo_html($pinfo["user_id"]); ?>#moderation" class="moderator_link"><?php echo_html(text("ModerateUserRates")); ?></a>
          <?php endif; ?>
        <?php endif; ?>
          
        <?php if($fmanager->is_admin()): ?>
          <a href="edit_user.php?uid=<?php echo_html($pinfo["user_id"]); ?>" class="moderator_link"><?php echo_html(text("EditUser")); ?></a>
        <?php endif; ?>

        <?php if(empty($in_search) && !empty($pinfo["moderatable"]) && empty($pinfo["topic_private"])): ?>
        <?php if(empty($user_data[$pinfo["user_id"]]["topic_blocked"])): ?>
          <a href="<?php echo($message_url); ?>" id="topic_block_link_<?php echo_html($pid); ?>" class="moderator_link" onclick='return confirm_action_with_comment("<?php echo_js(text("MsgConfirmBlockUserInTopic"), true); ?>".replace(/%s/, "<?php echo_js($fmanager->get_display_name($pinfo["author"]), true); ?>"), { topic_action: "block_user_in_topic", post: "<?php echo_js($pid); ?>", topic: "<?php echo_js($pinfo["topic_id"]); ?>", forum: "<?php echo_js($pinfo["forum_id"]); ?>", user: "<?php echo_js($pinfo["user_id"]); ?>", author_name: "<?php echo_js($pinfo["author"], true); ?>", display_author_name: "<?php echo_js($fmanager->get_display_name($pinfo["author"]), true); ?>" });'><?php echo_html(text("BlockUserInTopic")); ?></a>
        <?php else: ?>
          <a href="<?php echo($message_url); ?>" id="topic_block_link_<?php echo_html($pid); ?>" class="moderator_link" onclick='return do_action({ topic_action: "unblock_user_in_topic", post: "<?php echo_js($pid); ?>", topic: "<?php echo_js($pinfo["topic_id"]); ?>", forum: "<?php echo_js($pinfo["forum_id"]); ?>", user: "<?php echo_js($pinfo["user_id"]); ?>", author_name: "<?php echo_js($pinfo["author"], true); ?>", display_author_name: "<?php echo_js($fmanager->get_display_name($pinfo["author"]), true); ?>" });'><?php echo_html(text("UnblockUserInTopic")); ?></a>
        <?php endif; ?>
        <?php endif; // may_moderate_post ?>

        <?php if(empty($in_search) && empty($pinfo["topic_private"]) && ($fmanager->is_admin() || $fmanager->is_forum_moderator($pinfo["forum_id"]))): ?>
        <?php if(!empty($user_data[$pinfo["user_id"]]["is_topic_moderator"])): ?>
          <a href="<?php echo($message_url); ?>" id="topic_moderator_link_<?php echo_html($pid); ?>" class="moderator_link" onclick='return confirm_action_with_comment("<?php echo_js(text("MsgConfirmRemoveFromTopicModerator"), true); ?>".replace(/%s/, "<?php echo_js($fmanager->get_display_name($pinfo["author"]), true); ?>"), { topic_action: "revoke_topic_moderator", post: "<?php echo_js($pid); ?>", topic: "<?php echo_js($pinfo["topic_id"]); ?>", forum: "<?php echo_js($pinfo["forum_id"]); ?>", author_name: "<?php echo_js($pinfo["author"], true); ?>", display_author_name: "<?php echo_js($fmanager->get_display_name($pinfo["author"]), true); ?>" });'><?php echo_html(text("RemoveAuthorFromTopicModerator")); ?></a>
        <?php else: ?>
          <a href="<?php echo($message_url); ?>" id="topic_moderator_link_<?php echo_html($pid); ?>" class="moderator_link" onclick='return confirm_action("<?php echo_js(text("MsgConfirmMakeTopicModerator"), true); ?>".replace(/%s/, "<?php echo_js($fmanager->get_display_name($pinfo["author"]), true); ?>"), { topic_action: "make_topic_moderator", post: "<?php echo_js($pid); ?>", topic: "<?php echo_js($pinfo["topic_id"]); ?>", forum: "<?php echo_js($pinfo["forum_id"]); ?>", author_name: "<?php echo_js($pinfo["author"], true); ?>", display_author_name: "<?php echo_js($fmanager->get_display_name($pinfo["author"]), true); ?>" });'><?php echo_html(text("MakeAuthorTopicModerator")); ?></a>
        <?php endif; ?>
        <?php endif; // admin or forum moderator ?>

      <?php else: ?>
      
        <?php if(empty($in_search) && !empty($pinfo["moderatable"])): ?>
          <?php if(empty($forum_data["no_guests"])): ?>

          <?php if(empty($topic_data["no_guests"])): ?>
          <a href="<?php echo($message_url); ?>" class="moderator_link" onclick='return do_action({ topic_action: "disallow_guests", topic: "<?php echo_js($pinfo["topic_id"]); ?>", forum: "<?php echo_js($pinfo["forum_id"]); ?>" })'><?php echo_html(text("DisallowGuests")); ?></a>
          <?php else: ?>
          <a href="<?php echo($message_url); ?>" class="moderator_link" onclick='return do_action({ topic_action: "allow_guests", topic: "<?php echo_js($pinfo["topic_id"]); ?>", forum: "<?php echo_js($pinfo["forum_id"]); ?>" })'><?php echo_html(text("AllowGuests")); ?></a>
          <?php endif; ?>

          <?php endif; // no guests ?>
        <?php endif; // may_moderate_post ?>
        
      <?php endif; // if user id ?>

      <?php if(empty($in_search) && !empty($pinfo["moderatable"])): 
      $day_appendix = "";
      if(empty($pinfo["topic_private"]) && !$fmanager->is_admin() && !$fmanager->is_privileged_topic_moderator() && $fmanager->is_topic_moderator($pinfo["topic_id"]))
        $day_appendix = " (" . sprintf(text("ForDays"), get_allow_moderate_period_days()) . ")";
      ?>
        <a href="<?php echo($message_url); ?>" class="moderator_link" onclick='return confirm_action_with_comment("<?php echo_js(text("MsgConfirmPostsInTopicDelete"), true); ?>".replace(/%s/, "<?php echo_js($fmanager->get_display_name($pinfo["author"]), true); ?>"), { topic_action: "delete_posts_in_topic", post: "<?php echo_js($pid); ?>", topic: "<?php echo_js($pinfo["topic_id"]); ?>", forum: "<?php echo_js($pinfo["forum_id"]); ?>" });'><?php echo_html(text("DeleteAuthorMessagesInTopic") . $day_appendix); ?></a>
        <a href="<?php echo($message_url); ?>" class="moderator_link" onclick='return confirm_action_with_comment("<?php echo_js(text("MsgConfirmPostsInTopicRestore"), true); ?>".replace(/%s/, "<?php echo_js($fmanager->get_display_name($pinfo["author"]), true); ?>"), { topic_action: "restore_posts_in_topic", post: "<?php echo_js($pid); ?>", topic: "<?php echo_js($pinfo["topic_id"]); ?>", forum: "<?php echo_js($pinfo["forum_id"]); ?>" });'><?php echo_html(text("RestoreAuthorMessagesInTopic") . $day_appendix); ?></a>

        <?php if(!empty($pinfo["editable"])): ?>
        <a href="<?php echo($message_url); ?>" class="moderator_link" onclick='return confirm_action_with_comment("<?php echo_js(text("MsgConfirmPostsInTopicBulkRestore"), true); ?>".replace(/%s/, "<?php echo_js($fmanager->get_display_name($pinfo["author"]), true); ?>"), { topic_action: "restore_posts_in_topic_from", post: "<?php echo_js($pid); ?>", topic: "<?php echo_js($pinfo["topic_id"]); ?>", forum: "<?php echo_js($pinfo["forum_id"]); ?>" });'><?php echo_html(text("RestoreStartingFrom")); ?></a>
        <?php endif; ?>

        <?php if(($fmanager->is_admin() || $fmanager->is_forum_moderator($pinfo["forum_id"])) && empty($pinfo["topic_private"])): ?>
        <a href="<?php echo($message_url); ?>" class="moderator_link" onclick='return confirm_action_with_comment("<?php echo_js(text("MsgConfirmPostsInForumDelete"), true); ?>".replace(/%s/, "<?php echo_js($fmanager->get_display_name($pinfo["author"]), true); ?>"), { topic_action: "delete_posts_in_forum", post: "<?php echo_js($pid); ?>", topic: "<?php echo_js($pinfo["topic_id"]); ?>", forum: "<?php echo_js($pinfo["forum_id"]); ?>" });'><?php echo_html(text("DeleteAuthorMessagesInForum")); ?></a>
        <a href="<?php echo($message_url); ?>" class="moderator_link" onclick='return confirm_action_with_comment("<?php echo_js(sprintf(text("MsgConfirmLastNPostsDelete"), $bulk_delete_count), true); ?>".replace(/{author}/, "<?php echo_js($fmanager->get_display_name($pinfo["author"]), true); ?>"), { topic_action: "delete_last_N_posts", post: "<?php echo_js($pid); ?>", topic: "<?php echo_js($pinfo["topic_id"]); ?>", forum: "<?php echo_js($pinfo["forum_id"]); ?>" });'><?php echo_html(sprintf(text("DeleteLastNAuthorMessages"), $bulk_delete_count)); ?></a>
        <?php endif; ?>
      <?php endif; // may_moderate_post ?>

      <?php if(empty($in_search) && $fmanager->is_admin() && empty($pinfo["topic_private"])): ?>
        <a href="<?php echo($message_url); ?>" class="moderator_link" onclick='return confirm_action_with_comment("<?php echo_js(text("MsgConfirmAllPostsDelete"), true); ?>".replace(/%s/, "<?php echo_js($fmanager->get_display_name($pinfo["author"]), true); ?>"), { topic_action: "delete_all_posts", post: "<?php echo_js($pid); ?>", topic: "<?php echo_js($pinfo["topic_id"]); ?>", forum: "<?php echo_js($pinfo["forum_id"]); ?>" });'><?php echo_html(text("DeleteAllAuthorMessages")); ?></a>
      <?php endif; ?>

      <?php if($fmanager->is_admin() || $fmanager->global_ban_allowed() || $fmanager->is_forum_moderator($pinfo["forum_id"])): ?>
        <?php if(empty($pinfo["user_id"]) && !empty($pinfo["avatar"])): ?>
          <a href="<?php echo($message_url); ?>" class="moderator_link" onclick='return confirm_action("<?php echo_js(text("MsgConfirmAvatarDelete"), true); ?>".replace(/%s/, "<?php echo_js($fmanager->get_display_name($pinfo["author"]), true); ?>"), { topic_action: "delete_avatar", post: "<?php echo_js($pid); ?>", topic: "<?php echo_js($pinfo["topic_id"]); ?>", forum: "<?php echo_js($pinfo["forum_id"]); ?>" });'><?php echo_html(text("DeleteTheAvatar")); ?></a>
        <?php endif; ?>
      <?php endif; ?>

      <?php if($fmanager->is_admin()): ?>
        <a href="rm_moderation.php?search_key=<?php echo(xrawurlencode($pinfo["read_marker"])); ?>" class="moderator_link" onclick="hide_all_popups()"><?php echo_html(text("ModerateTheReadMarker")); ?></a>
      <?php endif; ?>

      <?php if($fmanager->may_see_ip() && ($fmanager->is_admin() || $fmanager->is_forum_moderator($pinfo["forum_id"]))): ?>
        <a href="ip_moderation.php?type=moderation&ip=<?php echo(xrawurlencode($pinfo["ip"])); ?>&author=<?php echo(xrawurlencode($pinfo["author"])); ?>" class="moderator_link" onclick="hide_all_popups()"><?php echo_html(text("ModerateIP")); ?></a>
        <a href="ip_moderation.php?type=ip_users&ip=<?php echo(xrawurlencode($pinfo["ip"])); ?>&author=<?php echo(xrawurlencode($pinfo["author"])); ?>" class="moderator_link" onclick="hide_all_popups()"><?php echo_html(text("ShowMembersOfIP")); ?></a>
        <a href="ip_moderation.php?type=user_ips&user=<?php echo(xrawurlencode($pinfo["author"])); ?><?php echo($aname_appendix); ?>" class="moderator_link" onclick="hide_all_popups()"><?php echo_html(text("ShowAuthorIPs")); ?></a>
        <a href="ip_moderation.php?type=other_users&user=<?php echo(xrawurlencode($pinfo["author"])); ?><?php echo($aname_appendix); ?>" class="moderator_link" onclick="hide_all_popups()"><?php echo_html(text("ShowMembersOfAuthorIPs")); ?></a>
        
        <a href="ip_moderation.php?type=um_moderation&ip=<?php echo(xrawurlencode($pinfo["user_marker"])); ?>&author=<?php echo(xrawurlencode($pinfo["author"])); ?>" class="moderator_link" onclick="hide_all_popups()"><?php echo_html(text("ModerateFingerPrint")); ?></a>
        <a href="ip_moderation.php?type=um_users&ip=<?php echo(xrawurlencode($pinfo["user_marker"])); ?>&author=<?php echo(xrawurlencode($pinfo["author"])); ?>" class="moderator_link" onclick="hide_all_popups()"><?php echo_html(text("ShowMembersOfFingerPrint")); ?></a>
      <?php endif; ?>

    </div>
    
    <?php endif; ?> <!-- if more actions -->

  </div> <!-- moderator_post_actions -->
  <?php endif; ?> <!-- if not system -->

</td>
</tr>
</table>

  <div class="navigation_arrows <?php echo($user_identifier_class); ?>">
  <div class="scroll_up" onclick="window.scrollTo(0, 0);"></div>
  <div class="scroll_down" onclick="window.scrollTo(0, 1000000);"></div>
  </div>

<a id="anchor_post_container_<?php echo_html($pid); ?>"></a>
<div id="post_container_<?php echo_html($pid); ?>" class="post_container" data-has-attachment="<?php echo($pinfo["has_attachment"]); ?>"></div>
