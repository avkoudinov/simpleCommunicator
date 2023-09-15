<!DOCTYPE html>
<html lang="<?php echo(current_language()); ?>">
<head>

<meta name="format-detection" content="telephone=no">

<title><?php echo_html($title); ?></title>

<?php include($_SERVER['DOCUMENT_ROOT'] . '/Google.Analytics.php') ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/Meta.Tags_nosql_forum.php') ?>
<link rel="stylesheet" href="<?php echo($view_path); ?>css/fonts_googleapis.css<?php echo($cache_appendix); ?>" type="text/css">

<script>
var timerStart = Date.now();
var NEW_CHECK_FREQUENCY = "<?php echo_js(defined('NEW_CHECK_FREQUENCY') ? NEW_CHECK_FREQUENCY*1000 : 30*1000); ?>";
var ATTACHMENTS_PER_POST = "<?php echo_js(defined('ATTACHMENTS_PER_POST') ? ATTACHMENTS_PER_POST : 3); ?>";
var DEBUG_MODE = <?php echo_js(defined('DEVELOPER_MODE') && DEVELOPER_MODE ? "true" : "false"); ?>;
var DEBUG_CONTEXT = 'none';

var pin_the_menu = <?php echo(!empty($_SESSION["skin_properties"][$skin]["pin_the_menu"]) ? 1 : 0); ?>;
var no_success_report = <?php echo(!empty($_SESSION["skin_properties"][$skin]["no_success_report"]) ? 1 : 0); ?>;
var no_confirmation_of_uncritical_actions = <?php echo(!empty($_SESSION["skin_properties"][$skin]["no_confirmation_of_uncritical_actions"]) ? 1 : 0); ?>;
var no_confirmation_of_any_actions = <?php echo(!empty($_SESSION["skin_properties"][$skin]["no_confirmation_of_any_actions"]) ? 1 : 0); ?>;
var no_confirmation_of_dislikes = <?php echo(!empty($_SESSION["skin_properties"][$skin]["no_confirmation_of_dislikes"]) ? 1 : 0); ?>;
</script>

<?php
$cache_appendix = "?v=" . $skin_version;
?>

<meta http-equiv="content-type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=1024"> 

<link rel="stylesheet" href="calendar/calendar.css<?php echo($cache_appendix); ?>" type="text/css">

<link rel="stylesheet" href="highlight.old/styles/vs.css<?php echo($cache_appendix); ?>" type="text/css">

<link rel="stylesheet" href="<?php echo($view_path); ?>css/multiselect.css<?php echo($cache_appendix); ?>" type="text/css">
<link rel="stylesheet" href="<?php echo($view_path); ?>css/file_input.css<?php echo($cache_appendix); ?>" type="text/css">
<link rel="stylesheet" href="<?php echo($view_path); ?>css/styles.css<?php echo($cache_appendix); ?>" type="text/css">

<link rel="stylesheet" href="<?php echo($view_path); ?>css/debug_console.css<?php echo($cache_appendix); ?>" type="text/css">

<?php if($view_mode == "tablet"): ?>
<link rel="stylesheet" href="<?php echo($view_path); ?>css/styles_horizontal.css<?php echo($cache_appendix); ?>" type="text/css">
<?php endif; ?>

<link rel="stylesheet" href="<?php echo($view_path); ?>css/adjustments.css<?php echo($cache_appendix); ?>" type="text/css">
<style>
.dummy
{
}

<?php
$settings["rates_active"] = false;
$settings["hide_online_status"] = true;
?>

<?php if(!empty($_SESSION["hide_user_info"])): ?>
.author_cell .avatar_container img
{
  display: none !important;
}

.author_cell .author_wrapper .aux_table td:first-child
{
  display: none;
}
<?php endif; ?>

<?php 
if(!empty($_SESSION["custom_css"]))
{
  echo($_SESSION["custom_css"]); 
}
?>
</style>

<?php require_once $view_path . "seo_inc.php"; ?>

<script>
Forum = {};
</script>

<script src='skins/<?php echo($skin); ?>/js/css_device_selector.js<?php echo($cache_appendix); ?>'></script>
<script src='skins/<?php echo($skin); ?>/js/css_browser_selector.js<?php echo($cache_appendix); ?>'></script>
<script src='skins/<?php echo($skin); ?>/js/swipe-events.js<?php echo($cache_appendix); ?>'></script>

<script src='skins/<?php echo($skin); ?>/js/debug_console.js<?php echo($cache_appendix); ?>'></script>
<script src='skins/<?php echo($skin); ?>/js/xevent.js<?php echo($cache_appendix); ?>'></script>
<script src='skins/<?php echo($skin); ?>/js/utils.js<?php echo($cache_appendix); ?>'></script>
<script src='skins/<?php echo($skin); ?>/js/md5.js<?php echo($cache_appendix); ?>'></script>
<script src='skins/<?php echo($skin); ?>/js/ajax.js<?php echo($cache_appendix); ?>'></script>
<script src='skins/<?php echo($skin); ?>/js/multiselect.js<?php echo($cache_appendix); ?>'></script>
<script src='skins/<?php echo($skin); ?>/js/file_input.js<?php echo($cache_appendix); ?>'></script>

<script src='skins/<?php echo($skin); ?>/js/snow.js<?php echo($cache_appendix); ?>'></script>

<script src='<?php echo($view_path); ?>common.js<?php echo($cache_appendix); ?>'></script>

<script src='calendar/calendar.js<?php echo($cache_appendix); ?>'></script>

<script src='highlight.old/highlight.pack.js<?php echo($cache_appendix); ?>'></script>
<script>
hljs.initHighlightingOnLoad();
</script>

<script>
<?php if($view_mode == "tablet"): ?>
zoom_preview_factor = 1.2;
<?php else: ?>
zoom_preview_factor = 2;
<?php endif; ?>

var protection_hash = "";
set_protection_hash('<?php echo_js(val_or_empty($_SESSION["hash"])); ?>');
var user_logged = "<?php echo_js(val_or_empty($_SESSION["logged_in"])); ?>";
var current_url = "<?php echo_js(val_or_empty($_SERVER["REQUEST_URI"])); ?>";
var user_marker = get_agent_hash();

var fpage = "<?php echo_js(reqvar("fpage")); ?>";
var previous_page_url = '';
var next_page_url = '';

var TIMEOUT = "<?php echo_js(defined('TIMEOUT') ? TIMEOUT : 10000); ?>";

var VIEW_PATH = "<?php echo_js($view_path); ?>";

<?php
def_js_message("OK");
def_js_message("Cancel");
def_js_message("Yes");
def_js_message("No");
def_js_message("Save");
def_js_message("Error");
def_js_message("Information");
def_js_message("Warning");
def_js_message("Confirmation");
def_js_message("Question");
def_js_message("MsgConfirmPostCancel");
def_js_message("MsgSubmitOrCancelCurrentMessage");
def_js_message("Modified");
def_js_message("Version");
def_js_message("AssignTags");
def_js_message("tags");
def_js_message("Favourites");

def_js_message("ignored");
def_js_message("DeleteCurrentAttachment");

def_js_message("ErrTimeout");
def_js_message("ErrNoServerResponse");
def_js_message("ErrRequestError");

def_js_message("MaxAttachmentCount");
def_js_message("ErrNoImagesInClipboard");
?>

<?php
$start_time = val_or_empty($_SESSION["session_start_time"]);
if(!empty($start_time)) $start_time = date("d.m.Y H:i:s", $start_time);
?>

var user_name = "<?php echo_js($fmanager->get_user_name()); ?>";
var session_id = "<?php echo_js(session_id()); ?>";
var session_start_time = "<?php echo_js($start_time); ?>";
var session_cookie = "<?php echo_js(get_cookie(session_name())); ?>";

var previous_session_id = localStorage.getItem('session_id'); 
var previous_session_start_time = localStorage.getItem('session_start_time'); 
var previous_session_cookie = localStorage.getItem('session_cookie'); 

localStorage.setItem('session_id', session_id);
localStorage.setItem('session_start_time', session_start_time);
localStorage.setItem('session_cookie', session_cookie);

function confirm_logout()
{
  if(no_confirmation_of_any_actions)
  {
    Forum.hide_user_msgbox();
    document.location.href = "logout.php?hash=" + get_protection_hash();
    return false;
  }

  var mbuttons = [
    {
      caption: msg_Yes,
      handler: function() {
        Forum.hide_user_msgbox();
        document.location.href = "logout.php?hash=" + get_protection_hash();
      }
    },
    {
      caption: msg_No,
      handler: function() { Forum.hide_user_msgbox(); }
    }
  ];

  Forum.show_user_msgbox(msg_Confirmation, '<?php echo_js(text("MsgConfirmLogout")); ?>', 'icon-warning.gif', mbuttons);

  return false;
}

function confirm_clear_profile_data()
{
  if(no_confirmation_of_any_actions)
  {
    Forum.hide_user_msgbox();
    clear_profile_data();
    return false;
  }

  var mbuttons = [
    {
      caption: msg_Yes,
      handler: function() {
        Forum.hide_user_msgbox();
        clear_profile_data();
      }
    },
    {
      caption: msg_No,
      handler: function() { Forum.hide_user_msgbox(); }
    }
  ];

  Forum.show_user_msgbox(msg_Confirmation, '<?php echo_js(text("MsgConfirmClearData")); ?>', 'icon-question.gif', mbuttons);

  return false;
}
</script>

</head>

<?php
$body_class = "";
if($view_mode == "tablet") $body_class .= " tablet";

if(!empty($settings["celebration_active"])) $body_class .= " celebration_active";
if(!empty($settings["mourning_active"])) $body_class .= " mourning_active";

if(!empty($_SESSION["hide_pictures"])) $body_class .= " hide_picture_mode";
if(empty($_SESSION["donot_hide_adult_pictures"])) $body_class .= " hide_adult_picture_mode";
?>
<body class="mobile <?php echo($body_class); ?>">

<?php include($_SERVER['DOCUMENT_ROOT'] . '/Yandex.Metrica.php') ?>

<!--
<div id="fb-root"></div>
<script async defer src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.2"></script>
-->

<div class="container">

<div class="content_wrap">

<!-- BEGIN: Menu Panel -->

<div id="menu_panel" class="menu_panel" style="display:none">
<div class="menu_title"><?php echo_html(text("Menu")); ?></div>
<div class="menu_close" onclick="show_hide_menu()"><img src="<?php echo($view_path); ?>images/cross.png" alt="<?php echo_html(text("Close")); ?>"></div>
<div class="clear_both"></div>


<?php 
$search_appendix = "";
if(!empty($is_private))
{
  $search_appendix = "&" . xrawurlencode("forums[]") . "=private";
}
elseif(basename($_SERVER["PHP_SELF"]) == "favourites.php" || !reqvar_empty("favourites_only") || !reqvar_empty("favourite_posts") || !reqvar_empty("favourite_posts_only"))
{
  $search_appendix = "&favourites_only=1";
}
elseif(reqvar("fid") == "my_topics" || 
       ($fmanager->is_logged_in() && reqvar("author") == $fmanager->get_user_name() && reqvar("author_mode") == "created_topic")
) {
  $search_appendix = "&author=" . xrawurlencode($fmanager->get_user_name()) . "&author_mode=created_topic&new_search=1";
}
elseif(reqvar("fid") == "my_part_topics" || 
       ($fmanager->is_logged_in() && reqvar("author") == $fmanager->get_user_name() && reqvar("author_mode") == "participating")
) {
  $search_appendix = "&author=" . xrawurlencode($fmanager->get_user_name()) . "&author_mode=participating&new_search=1";
}
elseif((basename($_SERVER["PHP_SELF"]) == "forum.php" || basename($_SERVER["PHP_SELF"]) == "topic.php" || basename($_SERVER["PHP_SELF"]) == "new_topic.php") && !reqvar_empty("fid"))
{
  $search_appendix = "&" . xrawurlencode("forums[]") . "=" . reqvar("fid");
}
?>
  
<?php if($fmanager->is_logged_in()): ?>

  <?php 
  if(!$fmanager->is_master_admin()): 
  ?>
  <p>
  <a href="profile.php"><?php echo_html(text("ProfileSettings")); ?></a> <br>

  <a href="view_profile.php?uid=<?php echo_html($fmanager->get_user_id()); ?>"><?php echo_html(text("ProfilePreview")); ?></a> <br>

  <?php
  $display = "style='display:none'";
  if(!empty($private_topics_with_new_count)) $display = "";
  ?>
  <a href="forum.php?fid=private"><?php echo_html(text("PrivateTopics")); ?></a><span class="new private_topics_with_new_indicator" <?php echo($display); ?>>&nbsp;[<a rel="nofollow" href="new_messages.php?fid=private"><?php echo_html(text("new")); ?>:<span class='private_topics_with_new_count'><?php echo($private_topics_with_new_count); ?></span></a>]</span><br>
  
    <?php if(empty($_SESSION["turnoff_events"])): 
     $display = "style='display:none'";
     if(!empty($new_events_count)) $display = "";
    ?>
    <a href="events.php"><?php echo_html(text("Events")); ?></a><span class="new new_events_indicator" <?php echo($display); ?>>&nbsp;[<a href="events.php"><?php echo_html(text("new")); ?>:<span class='new_events_count'><?php echo($new_events_count); ?></span></a>]</span><br>
    <?php endif; ?>
    
  <?php else: ?>

  <a href="guest_profile.php"><?php echo_html(text("Profile")); ?></a> <br>
  <a href="password_change.php"><?php echo_html(text("PasswordChange")); ?></a> <br>
  
  <?php endif; ?>
  </p>

  <p>
  <?php
  $display = "style='display:none'";
  if(!empty($topics_with_new_count)) $display = "";
  ?>
  <a rel="nofollow" href="new_messages.php"><?php echo_html(text("NewMessages")); ?></a><span class="new topics_with_new_indicator" <?php echo($display); ?>>&nbsp;[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span><br>

  <span class="topics_with_new_indicator" <?php echo($display); ?>><a rel="nofollow" href="search.php?news_digest=1&do_search=1"><?php echo_html(text("Digest")); ?></a> <br></span>
  
  <a href="search.php?do_search=1&hot_topics=1"><?php echo_html(text("HotTopics")); ?></a><br>

  <?php
  $display = "style='display:none'";
  if(!empty($favourites_with_new_count)) $display = "";
  ?>
  <a href="favourites.php"><?php echo_html(text("Favourites")); ?></a><span class="new favourites_with_new_indicator" <?php echo($display); ?>>&nbsp;[<a rel="nofollow" href="new_messages.php?fid=favourites"><?php echo_html(text("new")); ?>:<span class='favourites_with_new_count'><?php echo($favourites_with_new_count); ?></span></a>]</span><br>
  
  <?php if(!$fmanager->is_master_admin()): ?>

  <?php if(!empty($_SESSION["subscribed_authors"])): ?>
  <?php
  $display = "style='display:none'";
  if(!empty($subscription_authors_new_messages_count)) $display = "";
  ?>
  <a href="subscription.php"><?php echo_html(text("Subscription")); ?></a><span class="new subscription_new_indicator" <?php echo($display); ?>>&nbsp;[<a href="search.php?do_search=1&author=<?php echo(xrawurlencode(text("Subscription"))); ?>&author_mode=wrote_post&post_list=1&post_sort=desc&unseen=1&mark_read=1"><?php echo_html(text("new")); ?>:<span class='subscription_authors_new_messages_count'><?php echo($subscription_authors_new_messages_count); ?></span></a>]</span> <br>
  <?php endif; ?>
  
  <?php
  $display = "style='display:none'";
  if(!empty($my_topics_with_new_count)) $display = "";
  ?>
  <a href="search.php?do_search=1&author=<?php echo_html(xrawurlencode($fmanager->get_user_name())); ?>&author_mode=created_topic"><?php echo_html(text("MyTopics")); ?></a><span class="new my_topics_with_new_indicator" <?php echo($display); ?>>&nbsp;[<a rel="nofollow" href="new_messages.php?fid=my_topics"><?php echo_html(text("new")); ?>:<span class='my_topics_with_new_count'><?php echo($my_topics_with_new_count); ?></span></a>]</span>

  / <a href="search.php?do_search=1&author=<?php echo_html(xrawurlencode($fmanager->get_user_name())); ?>&author_mode=last_posts"><?php echo_html(text("MyMessagesShort")); ?></a>

  <?php if(!empty($_SESSION["skin_properties"][$skin]["show_my_part_topics"])): ?>
  <br>
  <?php
  $display = "style='display:none'";
  if(!empty($my_part_topics_with_new_count)) $display = "";
  ?>
  <a href="search.php?do_search=1&author=<?php echo_html(xrawurlencode($fmanager->get_user_name())); ?>&author_mode=participating"><?php echo_html(text("ParticipatedTopicsMiddle")); ?></a><span class="new my_part_topics_with_new_indicator" <?php echo($display); ?>>&nbsp;[<a rel="nofollow" href="new_messages.php?fid=my_part_topics"><?php echo_html(text("new")); ?>:<span class='my_part_topics_with_new_count'><?php echo($my_part_topics_with_new_count); ?></span></a>]</span> <br>
  <?php endif; ?>
  
  <?php endif; // master admin ?>
  
  </p>
<p>
<a href="forums.php"><?php echo_html(text("Forums")); ?></a> <br>
<a href="users.php"><?php echo_html(text("Users")); ?></a> <br>
<a href="statistics.php"><?php echo_html(text("Statistics")); ?></a> <br>
<a href="load_statistics.php"><?php echo_html(text("LoadStatistics")); ?></a> <br>

  <?php if($fmanager->is_moderator_log_visible()): ?>
  <?php 
  $display = "style='display:none'";
  if(empty($_SESSION["turnoff_events"]) && !empty($new_mod_events_count)) $display = "";
  ?>
  <a href="moderation_log.php"><?php echo_html(text("ModeratorLog")); ?></a><span class="new new_mod_events_indicator" <?php echo($display); ?>>&nbsp;[<a href="events.php?event_type=unprocessed_mod_events"><?php echo_html(text("new")); ?>:<span class='new_mod_events_count'><?php echo($new_mod_events_count); ?></span></a>]</span> <br>
  <?php endif; ?>
  
  <a href="search.php?with_morphology=1<?php echo($search_appendix); ?>"><?php echo_html(text("Search")); ?></a><br> 
</p>
  
<p>  
  <?php if($fmanager->is_moderator()): ?>
  <a href="moderation.php"><?php echo_html(text("Moderation")); ?></a>  <br>
  <?php endif; ?>

  <?php if($fmanager->is_admin()): ?>
  <a href="rm_moderation.php"><?php echo_html(text("ReadmarkerModeration")); ?></a>  <br>
  <a href="user_agents.php"><?php echo_html(text("UserAgents")); ?></a>  <br>
  <a href="guest_ips.php"><?php echo_html(text("GuestIPs")); ?></a>  <br>
  <a href="tor_ips.php"><?php echo_html(text("TorIPs")); ?></a>  <br>

   <a href="settings.php"><?php echo_html(text("Settings")); ?></a> <br>
  <?php endif; ?>
</p>
  
<?php else: ?>

<p>
<a href="guest_profile.php"><?php echo_html(text("Profile")); ?></a> <br>
<a href="#" onclick="return confirm_clear_profile_data()"><?php echo_html(text("ClearData")); ?></a> <br>
<a href="registration.php"><?php echo_html(text("Registration")); ?></a> <br>
</p>

<p>
  <?php
  $display = "style='display:none'";
  if(!empty($topics_with_new_count)) $display = "";
  ?>
  <a rel="nofollow" href="new_messages.php"><?php echo_html(text("NewMessages")); ?></a><span class="new topics_with_new_indicator" <?php echo($display); ?>>&nbsp;[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span><br>
  
  <span class="topics_with_new_indicator" <?php echo($display); ?>><a rel="nofollow" href="search.php?news_digest=1&do_search=1"><?php echo_html(text("Digest")); ?></a> <br></span>
  
  <a href="search.php?do_search=1&hot_topics=1<?php echo($search_appendix); ?>"><?php echo_html(text("HotTopics")); ?></a><br>

  <?php
  $display = "style='display:none'";
  if(!empty($favourites_with_new_count)) $display = "";
  ?>
  <a href="favourites.php"><?php echo_html(text("Favourites")); ?></a><span class="new favourites_with_new_indicator" <?php echo($display); ?>>&nbsp;[<a rel="nofollow" href="new_messages.php?fid=favourites"><?php echo_html(text("new")); ?>:<span class='favourites_with_new_count'><?php echo($favourites_with_new_count); ?></span></a>]</span><br>
</p>

<p>
<a href="forums.php"><?php echo_html(text("Forums")); ?></a> <br> 
<a href="users.php"><?php echo_html(text("Users")); ?></a> <br>
<a href="statistics.php"><?php echo_html(text("Statistics")); ?></a> <br>

  <?php if($fmanager->is_moderator_log_visible()): ?>
  <a href="moderation_log.php"><?php echo_html(text("ModeratorLog")); ?></a> <br>
  <?php endif; ?>

  <a href="search.php"><?php echo_html(text("Search")); ?></a> 
</p>

  
<?php endif; // logged or not ?>


</div>

<!-- END: Menu Panel -->

<!-- BEGIN: header1 -->

<div class="header1" id="main_header">

<div class="title">
<?php 
$ftitle = escape_html(text("Forum"));
if(file_exists($view_path . "lang/" . current_language() . "/title.html")) 
{
  $ftitle = file_get_contents($view_path . "lang/" . current_language() . "/title.html");
  $ftitle = str_ireplace("{site_name}", get_site_name(current_language()), $ftitle);
}

if(!empty($_SESSION["skin_properties"][$skin]["show_df_logotype"]))
{
  $ftitle = "<img src='{$view_path}images/forum_logo.png' srcset='{$view_path}images/forum_logo.svg' title='" . escape_html($ftitle) . "' alt='" . escape_html($ftitle) . "'>";
}
?>
<a href="../"><?php echo($ftitle); ?></a></div>
<div class="title_appendix"><span class="title_appendix_text" title="Debug Console" onclick='show_debug_console(true)'>&nbsp;(аскет) <?php if(!empty($_SESSION["admdebug"])) echo "[debug]"; ?></span></div>

<div class="title_separator"></div>

<?php if($installed): ?>
<div class="header_links">
<?php
$url = val_or_empty($_SERVER["REQUEST_URI"]);
if(empty($url))$url = "forums.php";

$anchor = "";
if(preg_match("/.*(#.*)$/", $url, $matches))
{
  $anchor = $matches[1];
  $url = str_replace($anchor, "", $url);
}

$target = "desktop=1";
$target_caption = text("DesktopVersion");
$target_action = "return switch_skin('desktop')";
if($view_mode == "mobile") 
{
  $target = "tablet=1";
  $target_caption = text("TabletVersion");
  $target_action = "return switch_skin('tablet')";
}

$url = str_replace("mobile=1", "", $url);
$url = str_replace("desktop=1", "", $url);
$url = str_replace("tablet=1", "", $url);
$url = rtrim($url, "&?");
if(strpos($url, "?") === false) $url .= "?$target" . $anchor;
else                            $url .= "&$target" . $anchor;
?>
<a href="<?php echo($url); ?>" onclick="<?php echo($target_action); ?>"><?php echo_html($target_caption); ?></a>&nbsp;&nbsp;&nbsp;

<a href="contact.php"><?php echo_html(text("Contact")); ?></a>&nbsp;&nbsp;&nbsp;

<a href="rules.php"><?php echo_html(text("Rules")); ?></a>&nbsp;&nbsp;&nbsp;

<a href="faq.php"><?php echo_html(text("FAQ")); ?></a>&nbsp;&nbsp;&nbsp;

<a href="help.php"><?php echo_html(text("Help")); ?></a>
</div>
<div class="clear_both"></div>
<?php endif; // installed ?>

<?php
$celebration_active = "";
$mourning_active = "";
if(!empty($settings["celebration_active"])) $celebration_active = " celebration_active";
if(!empty($settings["mourning_active"])) $mourning_active = " mourning_active";
?>

<a class="decoration <?php echo($celebration_active); ?> lightbox_image" target="_blank" href="<?php echo(get_random_special_mode_picture("celebration")); ?>" onclick="<?php if(!empty($settings["snow_effect"])) echo "snowStorm.start(false)"; ?>"></a>
<a class="decoration <?php echo($mourning_active); ?> lightbox_image" target="_blank" href="<?php echo(get_random_special_mode_picture("mourning")); ?>"></a>

</div>

<!-- END: header1 -->

<?php if($installed): ?>

<!-- BEGIN: header2 -->

<div class="header2" id="second_menu">
  <div style="float:left"><button class="standard_button" onclick="show_hide_menu()"><?php echo_html(text("Menu")); ?></button></div>
  <div class="member_area">
  
  <?php if($fmanager->is_logged_in()): ?>
  
  <?php
  if($fmanager->get_user_name() == "admin")
    $member_link = "<a class='member_nick' href='view_guest_profile.php?guest=" . xrawurlencode($fmanager->get_user_name()) . "'>" . escape_html($fmanager->get_status_user_name()) . "</a>,";
  else
    $member_link = "<a class='member_nick' href='view_profile.php?uid=" . $fmanager->get_user_id() . "'>" . escape_html($fmanager->get_status_user_name()) . "</a>,";

  echo($member_link); 
  ?>
  <a href="logout.php?hash=<?php echo_html($_SESSION["hash"]); ?>" onclick="return confirm_logout()"><?php echo_html(text("Logout")); ?></a> |
  <a href="profile.php"><?php echo_html(text("Profile")); ?></a> 
  
  <?php else: ?>
  
  <?php
  if($fmanager->get_user_name() != "")
  {
    $guest_name = "<a class='guest_link' href='view_guest_profile.php?guest=" . xrawurlencode($fmanager->get_user_name()) . "'>" . escape_html($fmanager->get_status_user_name()) . "</a>,";
  }
  else
  {
    $guest_name = "<span class='guest_nick'>" . escape_html($fmanager->get_status_user_name()) . "</span>,";
  }

  echo($guest_name); 
  ?>

  <a href="login.php"><?php echo_html(text("Login")); ?></a> |
  <a href="guest_profile.php"><?php echo_html(text("Profile")); ?></a> |
  <a href="#" onclick="return confirm_clear_profile_data()"><?php echo_html(text("ClearData")); ?>
  
  <?php endif; ?>
  
  
  </div>
  <div class="clear_both"></div>
</div>

<!-- END: header2 -->
      
<div id="float_header_container">
<?php
$main_menu_id = "main_menu";
@include "quick_menu_inc.php";
?>
</div>

<!-- BEGIN: header2 -->

<div class="header2">
<?php
@include "forum_selector_inc.php";
?>
</div>
      
<!-- END: header2 -->

<?php if(in_array(basename($_SERVER["PHP_SELF"]), array("forums.php", "forum.php", "topic.php", "search.php", "search_topic.php", "favourites.php", "new_messages.php"))): ?>

<!-- BEGIN: header2 -->

<div class="header2">

<form action="search.php" method="get" onsubmit="Forum.show_sys_progress_indicator(true);">
<input type="hidden" name="do_search" value="1">
<input type="hidden" name="quick_search" value="1">

<?php if(!empty($is_private)): ?>
<input type="hidden" name="forums[]" value="private">
<?php elseif(basename($_SERVER["PHP_SELF"]) == "favourites.php" || !reqvar_empty("favourites_only")): ?>
<input type="hidden" name="favourites_only" value="1">
<?php elseif(!reqvar_empty("favourite_posts") || !reqvar_empty("favourite_posts_only")): ?>
<input type="hidden" name="favourite_posts_only" value="1">
<?php elseif(reqvar("fid") == "my_topics" || 
       ($fmanager->is_logged_in() && reqvar("author") == $fmanager->get_user_name() && reqvar("author_mode") == "created_topic")): ?>
<input type="hidden" name="author" value="<?php echo_html($fmanager->get_user_name()); ?>">
<input type="hidden" name="author_mode" value="created_topic">
<?php elseif(reqvar("fid") == "my_part_topics" || 
       ($fmanager->is_logged_in() && reqvar("author") == $fmanager->get_user_name() && reqvar("author_mode") == "participating")): ?>
<input type="hidden" name="author" value="<?php echo_html($fmanager->get_user_name()); ?>">
<input type="hidden" name="author_mode" value="participating">
<?php elseif((basename($_SERVER["PHP_SELF"]) == "forum.php" || basename($_SERVER["PHP_SELF"]) == "new_topic.php" || basename($_SERVER["PHP_SELF"]) == "topic.php") && !reqvar_empty("fid")): ?>
<input type="hidden" name="forums[]" value="<?php echo_html(reqvar("fid")); ?>">
<?php endif; ?>

<?php if(!reqvar_empty("tid")): ?>
<input type="hidden" name="tid" value="<?php echo_html(reqvar("tid")); ?>">
<?php endif; ?>

<table style="width:100%" class="aux_table">
<tr>
<td>
<input type="text" class="search_field" name="search_keys" autocomplete="off">
<input type="hidden" name="with_morphology" value="1">
</td>
<td style="width:1%; white-space: nowrap">
<input type="submit" class="standard_button search_button" value="<?php echo_html(text("DoSearch")); ?>">
</td>
</tr>
</table>

</form>

</div>

<!-- END: header2 -->

<?php endif; ?>
      
<?php endif; // installed ?>
      
<div class="content_area">

<?php if(defined('REVOLVERMAPS_KEY') && !empty(REVOLVERMAPS_KEY)): ?>
<div style="display:none">
<img src="//ra.revolvermaps.com/h/m/a/0/ff0000/128/0/<?php echo(REVOLVERMAPS_KEY); ?>.png?t=<?php echo(time()); ?>" alt="Map">
</div>
<?php endif; ?>

<?php require_once $view_path . $view; ?>

</div>

</div> <!-- end content_wrap -->

</div> <!-- end container -->

<div class="footer_container">

<div class="footer_wrap">

<?php
$main_menu_id = "main_menu_bottom";
@include "quick_menu_inc.php";
?>

<!-- BEGIN: header2 -->

<div class="header2">
<?php
@include "forum_selector_inc.php";
?>
</div>

<!-- END: header2 -->

<div class="footer">

  <div style="float: left">
  <a href="user_agreement.php"><?php echo_html(text("UserAgreement")); ?></a>
  </div>

  <div style="float: right;cursor:pointer">
  <?php echo_html(text("CreationLoading")); ?>: <?php echo_html($exec_time); ?>ms<?php if(!empty($new_check_time)) echo " (нв.: {$new_check_time}ms)"; ?><span id="load_time" onclick="Forum.show_profiling_info()"></span>
  </div>

  <div class="clear_both">
  </div>

    <?php if(!empty($execution_profiles)): 
    $others = $exec_time;
    ?>
    <div id="profiling_info" class="profiling_info">
      <div style="position: absolute;right:2px;top:2px;cursor:pointer" onclick="this.parentNode.style.display = 'none'"><img src="<?php echo($view_path); ?>images/cross.png" alt="<?php echo_html(text("Close")); ?>"></div>

    <table>
    <?php foreach($execution_profiles as $profile): 
    $others -= $profile["time"];
    ?>
    <tr>
    <td><div class="smart_break"><?php echo_html($profile["action"]); ?>:</div></td>
    <td style="text-align:right"><?php echo_html($profile["time"]); ?>ms</td>
    </tr>
    <?php endforeach; ?>
    
    <tr>
    <td>others:</td>
    <td style="text-align:right"><?php echo_html($others); ?>ms</td>
    </tr>

    <tr>
    <td></td>
    <td style="text-align:right"></td>
    </tr>
    <tr>
    <td style="font-weight:bold">total:&nbsp;</td>
    <td style="text-align:right;font-weight:bold"><?php echo_html($exec_time); ?>ms</td>
    </tr>
    </table>
    
    </div>
    <?php endif; ?>

</div>

</div> <!-- end footer_wrap -->

</div> <!-- end footer_container -->

<!-- BEGIN: user_msgbox -->

<div class="_user_msgbox" id="user_msgbox">
  <div>
    <div>
      <div class="_user_msgbox_head" id="user_msgbox_head">
        <div class="_user_msgbox_title" id="user_msgbox_title"></div>
        <div class="_user_msgbox_close" onclick="Forum.hide_user_msgbox()">x</div>
      </div>
      <div class="_user_msgbox_body" id="user_msgbox_body">

      <table>
      <tr>
      <td class="_user_msgbox_icon" id="user_msgbox_icon"></td>
      <td><div class="_user_msgbox_text" id="user_msgbox_text"></div></td>
      </tr>
      </table>

      <div class="_user_msgbox_buttons" id="user_msgbox_buttons"></div>
      </div>
    </div>
  </div>
</div>

<!-- END: user_msgbox -->

<script>
// preload message box images
var img_error = new Image();
img_error.src = VIEW_PATH + "images/icon-error.gif";

var img_warning = new Image();
img_warning.src = VIEW_PATH + "images/icon-warning.gif";

var img_info = new Image();
img_info.src = VIEW_PATH + "images/icon-info.gif";

var img_loading = new Image();
img_loading.src = VIEW_PATH + "images/loading.gif";

var img_loading_big = new Image();
img_loading_big.src = VIEW_PATH + "images/loading-big.gif";

var img_loading_bar = new Image();
img_loading_bar.src = VIEW_PATH + "images/loading-bar.gif";
</script>

<!-- BEGIN: sys_lightbox -->

<div class="_sys_lightbox" id="sys_lightbox">
  <div>
    <div>
      <div class="_sys_lightbox_head" id="sys_lightbox_head">
        <div class="_sys_lightbox_title" id="sys_lightbox_title"></div>
        <div class="_sys_lightbox_close" onclick="Forum.hide_sys_lightbox()">x</div>
      </div>
      <div class="_sys_lightbox_body" id="sys_lightbox_body"></div>
      <div class="_sys_lightbox_toolbar" id="sys_lightbox_toolbar"></div>
    </div>
  </div>
</div>

<!-- END: sys_lightbox -->

<!-- BEGIN: sys_bubblebox -->
<div id="sys_bubblebox" class="_sys_bubblebox">
  <div style="position: absolute;right:2px;top:2px;cursor:pointer" onclick="Forum.hide_sys_bubblebox()"><img src="<?php echo($view_path); ?>images/cross.png" alt="<?php echo_html(text("Close")); ?>"></div>
  
  <span style="font-weight: bold" id="sys_bubblebox_title"></span><br><br>

  <div id="sys_bubblebox_msg"></div>

</div>
<!-- END: sys_bubblebox -->

<!-- BEGIN: sys_image_preview -->

<div id="sys_image_preview" class="_sys_image_preview" onclick="Forum.hide_image_preview()">
  <div>
    <div>
    <div id="sys_image_preview_close"></div>
    <div id="sys_image_preview_open_new"></div>
    <img id="sys_preview_image" src="skins/default/mobile/images/blank.gif" alt="<?php echo_html(text("Preview")); ?>">
    </div>
  </div>
</div>

<div id="preview_navigation">
  <table>
  <tr>
  <td id="preview_navigation_previous"></td>
  <td id="preview_navigation_status">0 / 0</td>
  <td id="preview_navigation_next"></td>
  </tr>
  </table>
</div>

<!-- END: sys_image_preview -->
      
<!-- BEGIN: sys_progress_indicator -->

<div class="_sys_load_lightbox" id="sys_progress_indicator">
  <div>
    <div onclick="Forum.show_sys_progress_indicator(false)">
    </div>
  </div>
</div>

<!-- END: sys_progress_indicator -->
      
<script>
var messages = {};

messages.INFO_MESSAGE = "<?php echo_js($MSG_INFO_MESSAGE); ?>";
messages.WARNING_MESSAGE = "<?php echo_js($MSG_WARNING_MESSAGE); ?>";
messages.PROG_WARNING = "<?php echo_js($MSG_PROG_WARNING); ?>";
messages.ERROR_MESSAGE = "<?php echo_js($MSG_ERROR_MESSAGE); ?>";
messages.DEBUG_MESSAGE = "<?php echo_js($MSG_DEBUG_MESSAGE); ?>";

messages.AUTO_HIDE_INFO = "<?php echo_js($MSG_AUTO_HIDE_INFO); ?>";
messages.ACTIVE_TAB = "<?php echo_js($MSG_ACTIVE_TAB); ?>";
messages.FOCUS_ELEMENT = "<?php echo_js($MSG_FOCUS_ELEMENT); ?>";
messages.ERROR_ELEMENT = "<?php echo_js($MSG_ERROR_ELEMENT); ?>";

Forum.addXEvent(window, 'DOMContentLoaded', function () { 
  browser_class(navigator.userAgent);
  Forum.handle_response_messages(messages); 
});

var load_time = document.getElementById("load_time");
if(load_time)
{
  load_time.innerHTML = " / " + (Date.now()-timerStart) + "ms";
}
</script>      

<div id="debug_console_container">
<div id="debug_console">
    <div id="debug_console_close" onclick="show_debug_console(false)"><img src="<?php echo($view_path); ?>images/cross.png" alt="Close"></div>
<b>Debug Console</b> <span class="debug_console_select">[<a href="#" onclick=" return select_debug_console_output()">Select Text</a>]</span>
<textarea id="debug_console_output"></textarea>
</div>
</div>

</body>

</html>