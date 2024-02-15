<!DOCTYPE html>
<html lang="<?php echo(current_language()); ?>">
<head>
<title><?php echo_html($title); ?></title>

<?php
$cache_appendix = "?v=" . $skin_version;
?>

<meta name="format-detection" content="telephone=no">

<script>
var timerStart = Date.now();
var NEW_CHECK_FREQUENCY = "<?php echo_js(defined('NEW_CHECK_FREQUENCY') ? NEW_CHECK_FREQUENCY*1000 : 30*1000); ?>";
var ATTACHMENTS_PER_POST = "<?php echo_js(defined('ATTACHMENTS_PER_POST') ? ATTACHMENTS_PER_POST : 3); ?>";
var DEBUG_MODE = <?php echo_js(defined('DEVELOPER_MODE') && DEVELOPER_MODE ? "true" : "false"); ?>;
var DEBUG_CONTEXT = '<?php echo_js(!empty($_SESSION["debug_context"]) ? $_SESSION["debug_context"] : "none"); ?>';

var pin_the_menu = <?php echo(!empty($_SESSION["skin_properties"][$skin]["pin_the_menu"]) ? 1 : 0); ?>;
var no_success_report = <?php echo(!empty($_SESSION["skin_properties"][$skin]["no_success_report"]) ? 1 : 0); ?>;
var no_confirmation_of_uncritical_actions = <?php echo(!empty($_SESSION["skin_properties"][$skin]["no_confirmation_of_uncritical_actions"]) ? 1 : 0); ?>;
var no_confirmation_of_any_actions = <?php echo(!empty($_SESSION["skin_properties"][$skin]["no_confirmation_of_any_actions"]) ? 1 : 0); ?>;
var no_confirmation_of_dislikes = <?php echo(!empty($_SESSION["skin_properties"][$skin]["no_confirmation_of_dislikes"]) ? 1 : 0); ?>;
</script>
        
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=1200">

<link rel="stylesheet" href="calendar/calendar.css<?php echo($cache_appendix); ?>" type="text/css">

<link rel="stylesheet" href="highlight.old/styles/vs.css<?php echo($cache_appendix); ?>" type="text/css">

<link rel="stylesheet" href="<?php echo($view_path); ?>css/multiselect.css<?php echo($cache_appendix); ?>" type="text/css">
<link rel="stylesheet" href="<?php echo($view_path); ?>css/file_input.css<?php echo($cache_appendix); ?>" type="text/css">
<link rel="stylesheet" href="<?php echo($view_path); ?>css/styles.css<?php echo($cache_appendix); ?>" type="text/css">
<link rel="stylesheet" href="<?php echo($view_path); ?>css/debug_console.css<?php echo($cache_appendix); ?>" type="text/css">

<link rel="stylesheet" href="<?php echo($view_path); ?>css/custom.css<?php echo($cache_appendix); ?>" type="text/css"/>

<style>
.dummy
{
}

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
<script src='skins/<?php echo($skin); ?>/js/field_lookup.js<?php echo($cache_appendix); ?>'></script>

<script src='skins/<?php echo($skin); ?>/js/snow.js<?php echo($cache_appendix); ?>'></script>

<script src='<?php echo($view_path); ?>common.js<?php echo($cache_appendix); ?>'></script>

<script src='calendar/calendar.js<?php echo($cache_appendix); ?>'></script>

<script src='highlight.old/highlight.pack.js<?php echo($cache_appendix); ?>'></script>
<script>
hljs.initHighlightingOnLoad();
</script>

<script>
var protection_hash = "";
set_protection_hash('<?php echo_js(val_or_empty($_SESSION["hash"])); ?>');
var user_logged = "<?php echo_js(val_or_empty($_SESSION["logged_in"])); ?>";
var trace_sql = "<?php echo_js(val_or_empty($_REQUEST["trace_sql"])); ?>";
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
if(!empty($_SESSION["session_start_time"])) {
  $start_time = $start_time = date("d.m.Y H:i:s", $_SESSION["session_start_time"]);
} else {
  $start_time = "";
}
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

<?php
require_once "custom_head.php";
?>

</head>

<?php
$body_class = "";
if(!empty($settings["celebration_active"])) $body_class .= " celebration_active";
if(!empty($settings["mourning_active"])) $body_class .= " mourning_active";

if(!empty($_SESSION["hide_pictures"])) $body_class .= " hide_picture_mode";
if(empty($_SESSION["donot_hide_adult_pictures"])) $body_class .= " hide_adult_picture_mode";
?>
<body class="desktop <?php echo($body_class); ?>">

<div class="container">

<div class="content_wrap">

<!-- BEGIN: header1 -->

<div class="header1" id="main_header">

<div style="float:left">
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
<a href="../"><?php echo($ftitle); ?></a>
</div>

<div class="title_appendix">
<span class="title_appendix_text" title="Debug Console" onclick='show_debug_console(true)'>&nbsp; <?php if(!empty($_SESSION["admdebug"]) || !empty($_SESSION["debug_context"])) echo "[debug]"; else echo "&nbsp;&nbsp;&nbsp;"; ?></span>
</div>

<div class="clear_both"></div>

<div class="powered_by">powered by <a href="about.php" >simpleCommunicator</a>&nbsp;-&nbsp;<?php echo(VERSION); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;© <?php echo(date("Y")); ?> Programmizd 02</div>
</div>

<?php if($installed): ?>

<div style="float:right;padding-top:35px;">
<div class="powered_by">
<?php
$url = val_or_empty($_SERVER["REQUEST_URI"]);
if(empty($url))$url = "forums.php";

$anchor = "";
if(preg_match("/.*(#.*)$/", $url, $matches))
{
  $anchor = $matches[1];
  $url = str_replace($anchor, "", $url);
}

$url = str_replace("mobile=1", "", $url);
$url = str_replace("desktop=1", "", $url);
$url = rtrim($url, "&?");
if(strpos($url, "?") === false) $url .= "?mobile=1" . $anchor;
else                            $url .= "&mobile=1" . $anchor;
?>
<a href="<?php echo($url); ?>" onclick="return switch_skin('mobile')"><?php echo_html(text("MobileVersion")); ?></a>&nbsp;&nbsp;&nbsp;

<a href="contact.php"><?php echo_html(text("Contact")); ?></a>&nbsp;&nbsp;&nbsp;

<a href="rules.php"><?php echo_html(text("Rules")); ?></a>&nbsp;&nbsp;&nbsp;

<a href="faq.php"><?php echo_html(text("FAQ")); ?></a>&nbsp;&nbsp;&nbsp;

<a href="help.php"><?php echo_html(text("Help")); ?></a>
</div>
</div>

<?php endif; ?>

<div class="clear_both">

<?php
$celebration_active = "";
$mourning_active = "";
if(!empty($settings["celebration_active"])) $celebration_active = " celebration_active";
if(!empty($settings["mourning_active"])) $mourning_active = " mourning_active";
?>

<a class="decoration <?php echo($celebration_active); ?> lightbox_image" target="_blank" href="<?php echo(get_random_special_mode_picture("celebration")); ?>" onclick="<?php if(!empty($settings["snow_effect"])) echo "snowStorm.start(false)"; ?>"></a>
<a class="decoration <?php echo($mourning_active); ?> lightbox_image" target="_blank" href="<?php echo(get_random_special_mode_picture("mourning")); ?>"></a>

</div>

</div>

<!-- END: header1 -->

<div id="float_header_container">
<?php
$main_menu_id = "main_menu";
@include "main_menu_inc.php";
?>
</div>

<?php if(defined('REVOLVERMAPS_KEY') && !empty(REVOLVERMAPS_KEY)): ?>
<div style="display:none">
<img src="//ra.revolvermaps.com/h/m/a/0/ff0000/128/0/<?php echo(REVOLVERMAPS_KEY); ?>.png?t=<?php echo(time()); ?>" alt="Map">
</div>
<?php endif; ?>

<?php require_once $view_path . $view; ?>

</div> <!-- content_wrap -->

</div> <!-- container -->

<div class="footer_container">
<div class="footer_wrap">

<?php
$main_menu_id = "main_menu_bottom";
@include "main_menu_inc.php";
?>

  <div class="footer">
  
    <div style="position:absolute;top:5px;left:5px;color:white;font-size:10px"><a href="user_agreement.php"><?php echo_html(text("UserAgreement")); ?></a></div>
    
    <?php 
    $footer = "";
    if(file_exists($view_path . "lang/" . current_language() . "/footer.html")) 
    {
      $footer = str_ireplace("{title}", $ftitle, file_get_contents($view_path . "lang/" . current_language() . "/footer.html"));
      $footer = str_ireplace("{user_name}", xrawurlencode($fmanager->get_user_name()), $footer);
    }

    echo($footer); 
    ?>
    
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
    
    <div style="position:absolute;top:6px;right:5px;color:white;font-size:10px;cursor:pointer" onclick="Forum.show_profiling_info()"><?php echo_html(text("CreationLoading")); ?>: <?php echo_html($exec_time); ?>ms<?php if(!empty($new_check_time)) echo " (нв.: {$new_check_time}ms)"; ?><span id="load_time"></span></div>
    
  </div>
</div>
</div>

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
    <a id="sys_image_preview_open_new" href="<?php echo($view_path); ?>images/blank.gif" target="_blank" title="<?php echo_html(text("OpenInFullSize")); ?>"></a>
    <img id="sys_preview_image" src="<?php echo($view_path); ?>images/blank.gif" alt="<?php echo_html(text("Preview")); ?>">
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

<?php
require_once "custom_body.php";
?>

</body>

</html>