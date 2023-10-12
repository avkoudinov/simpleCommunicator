<?php
// defining the application root
require_once dirname(__FILE__) . "/application_root_inc.php";

// version
require_once APPLICATION_ROOT . "include/version_inc.php";

// config
require_once APPLICATION_ROOT . "include/config_inc.php";
require_once APPLICATION_ROOT . "include/admin_config_inc.php";
require_once APPLICATION_ROOT . "include/maintenance_inc.php";

// class autoload policy
require_once APPLICATION_ROOT . "include/define_autoload_inc.php";

// common utility functions
require_once APPLICATION_ROOT . "include/utility_functions_inc.php";

// nbbc BB code parser
require_once APPLICATION_ROOT . "include/bb_parse_functions_inc.php";

// error handler
require_once APPLICATION_ROOT . "include/error_handler_inc.php";

// language definitions
require_once APPLICATION_ROOT . "lang/lang_inc.php";

// time zones
require_once APPLICATION_ROOT . "include/timezones.php";

// captcha utils
require_once APPLICATION_ROOT . "captcha/SimpleCaptcha.php";

// captcha utils
require_once APPLICATION_ROOT . "emoji/Emoji.php";

if (!defined('TIME_ZONE') || !@date_default_timezone_set(TIME_ZONE)) {
    @date_default_timezone_set('Universal');
}

header("Content-type: text/html; charset=utf-8");

$script_name = basename(val_or_empty($_SERVER["PHP_SELF"]));

$fmanager = ForumManager::instance();

$installed = (defined('ADMIN_PASSWORD') && ADMIN_PASSWORD != "");

function is_maintenance() {
    global $maintenance_until;
    
    return (!empty($maintenance_until) && empty($_SESSION["admdebug"]));
}

if($installed && !is_maintenance())
{
  $fmanager->check_ip(val_or_empty($_SERVER["REMOTE_ADDR"]), val_or_empty($_SERVER["HTTP_USER_AGENT"]));
}

// Generate a random hash to protect against unwanted actions
// through action URLs in the pictures or links.

if (empty($_SESSION["hash"])) {
    $_SESSION["hash_generation_request_uri"] = val_or_empty($_SERVER["REQUEST_URI"]);
    if (!reqvar_empty("hash")) {
        $_SESSION["hash"] = reqvar("hash");
        $_SESSION["hash_generation"] = "recreated from client hash";
    } else {
        $_SESSION["hash"] = System::generateSessionHashCode();
        $_SESSION["hash_generation"] = "newly created";
    }
}

// set read marker
$READ_MARKER = get_cookie("q_read_marker");
if (empty($READ_MARKER)) {
    $READ_MARKER = System::generateReadmarker();
}

// 90 days
set_cookie("q_read_marker", $READ_MARKER, time() + 90 * 24 * 3600);

$target_url = val_or_empty($_SESSION["last_url"]);
if (empty($target_url) || $target_url == val_or_empty($_SERVER["REQUEST_URI"])) {
    $target_url = "forums.php";
}

if ($fmanager->check_hash()) {
    $uri = val_or_empty($_SERVER["REQUEST_URI"]);
    if (empty($uri)) {
        $uri = $target_url;
    }
    
    $uri = preg_replace("/show_deleted=\\d&?/", "", $uri);
    $uri = preg_replace("/hide_deleted=\\d&?/", "", $uri);
    $uri = preg_replace("/guest_posting_on=\\d&?/", "", $uri);
    $uri = preg_replace("/guest_posting_off=\\d&?/", "", $uri);
    $uri = preg_replace("/hash=.+&?/", "", $uri);
    $uri = rtrim($uri, "&?");
    
    if (!reqvar_empty("show_deleted")) {
        $_SESSION["show_deleted"] = 1;
        header("Location: " . $uri);
        exit;
    }
    if (!reqvar_empty("hide_deleted")) {
        $_SESSION["show_deleted"] = 0;
        header("Location: " . $uri);
        exit;
    }
    if (!reqvar_empty("guest_posting_on")) {
        $_SESSION["guest_posting_mode"] = 1;
        header("Location: " . $uri);
        exit;
    }
    if (!reqvar_empty("guest_posting_off")) {
        $_SESSION["guest_posting_mode"] = 0;
        header("Location: " . $uri);
        exit;
    }
}

if (!reqvar_empty("admdebug") && reqvar("admdebug") == val_or_empty($adm_debug_password)) {
    $_SESSION["admdebug"] = 1;
}
if (empty($maintenance_until)) {
    unset($_SESSION["admdebug"]);
}

if (empty($ajax_processing)) {
    if (!empty($_REQUEST["trace_sql"])) {
        $_SESSION["trace_sql"] = $_REQUEST["trace_sql"];
        $_SESSION["trace_time_start"] = microtime(true);
        $_SESSION["execution_profiles"] = array();
        $_SESSION["execution_profiles"][] = array("action" => "Start - " . $_SERVER['REQUEST_URI'], "time" => 0);
        $_SESSION["trace_sql_log"] = "";
        
        $_SESSION["trace_sql_log"] .= "----------------------------------------------------------------------" . "\n";
        $_SESSION["trace_sql_log"] .= "Started: " . $_SERVER['REQUEST_URI'] . "\n";;
        $_SESSION["trace_sql_log"] .= "----------------------------------------------------------------------" . "\n";
    } elseif (!empty($_SESSION["trace_sql"])) {
        $_SESSION["trace_sql_log"] .= "Redirected to: " . $_SERVER['REQUEST_URI'] . "\n";;
        $_SESSION["trace_sql_log"] .= "----------------------------------------------------------------------" . "\n";
    } elseif (empty($_SESSION["trace_time_start"])) {
        $_SESSION["trace_time_start"] = microtime(true);
        $_SESSION["execution_profiles"] = array();
        $_SESSION["execution_profiles"][] = array("action" => "Start [" . val_or_empty($_SERVER['REQUEST_URI']) . "]", "time" => 0);
        //debug_message("setting trace_time_start:" . round(1000*$_SESSION["trace_time_start"]));
    } else {
        //debug_message("continue time counting after redirection to $_SERVER[REQUEST_URI]:" . round(1000*(microtime(true) - $_SESSION["trace_time_start"])));
        if (!empty($_SESSION["redirection_start_time"])) {
            $_SESSION["execution_profiles"][] = array("action" => "Redirection [" . $_SERVER['REQUEST_URI'] . "]", "time" => round(1000 * (microtime(true) - $_SESSION["redirection_start_time"])));
            unset($_SESSION["redirection_start_time"]);
        }
    }
} else {
    if (!empty($_REQUEST["trace_sql"])) {
        $_SESSION["ajax_trace_sql"] = $_REQUEST["trace_sql"];
        $_SESSION["ajax_trace_time_start"] = microtime(true);
        $_SESSION["ajax_execution_profiles"] = array();
        $_SESSION["ajax_execution_profiles"][] = array("action" => "Start - " . $_SERVER['REQUEST_URI'], "time" => 0);
        $_SESSION["ajax_trace_sql_log"] = "";
        
        $_SESSION["ajax_trace_sql_log"] .= "----------------------------------------------------------------------" . "\n";
        $_SESSION["ajax_trace_sql_log"] .= "Started (AJAX): " . $_SERVER['REQUEST_URI'] . "\n";;
        $_SESSION["ajax_trace_sql_log"] .= "----------------------------------------------------------------------" . "\n";
    } elseif (empty($_SESSION["ajax_trace_time_start"])) {
        $_SESSION["ajax_trace_time_start"] = microtime(true);
        $_SESSION["ajax_execution_profiles"] = array();
        $_SESSION["ajax_execution_profiles"][] = array("action" => "Start [" . val_or_empty($_SERVER['REQUEST_URI']) . "]", "time" => 0);
        //debug_message("setting trace_time_start:" . round(1000*$_SESSION["trace_time_start"]));
    }
}

$new_check_time = 0;

$topics_with_new_count = 0;
$private_topics_with_new_count = 0;
$favourites_with_new_count = 0;
$my_topics_with_new_count = 0;
$my_part_topics_with_new_count = 0;
$subscription_authors_new_messages_count = 0;
$subscription_authors_new_topics_count = 0;
$new_events_count = 0;
$new_mod_events_count = 0;

$time_zone = val_or_empty($_SESSION["time_zone"]);
if (empty($time_zone)) {
    $time_zone = TIME_ZONE;
}
$time_zone_name = format_gmt_offset(get_timezone_gmt_offset($time_zone)) . " " . $time_zones[$time_zone];

$skin = "default";
$view_mode = "desktop";
$view_path = "skins/default/desktop/";
$skin_version = "1.0.0";

$forum_list = array();

if (!$installed) {
    $fmanager->define_view_path($skin, $view_path, $view_mode, $skin_version);
    
    // if not installed redirect to the installation,
    // but not in the case of about, help, and ajax processing
    if (!in_array($script_name, array("installation.php", "about.php", "help.php")) && empty($ajax_processing)) {
        header("Location: installation.php");
        exit;
    }
} elseif (is_maintenance()) {
    $maintenance_until = adjust_and_format_timezone(xstrtotime($maintenance_until), text("DateTimeFormat"));
    if (empty($ajax_processing)) {
        $fmanager->read_user_cookies();
        
        $fmanager->define_view_path($skin, $view_path, $view_mode, $skin_version);
        
        if ($script_name != "maintenance.php") {
            header("Location: maintenance.php");
            exit;
        }
    }
} else {
    $settings = array();
    $fmanager->get_settings($settings);
    
    $fmanager->read_user_cookies();
    
    $fmanager->try_auto_login();
    
    $fmanager->update_user_status();
    
    $fmanager->define_view_path($skin, $view_path, $view_mode, $skin_version);
    
    $fmanager->get_forum_list($forum_list);

    if (empty($ajax_processing)) {
        if (!empty($maintenance_start) && !empty($maintenance_end) && 
            time() <= xstrtotime($maintenance_end) &&
            empty($_SESSION["maintenance_notified"])          
        ) {
            $maintenance_start = adjust_and_format_timezone(xstrtotime($maintenance_start), text("DateTimeFormat"));
            $maintenance_end = adjust_and_format_timezone(xstrtotime($maintenance_end), text("DateTimeFormat"));
            
            $message = sprintf(text("MaintenanceNotification"), $maintenance_start, $maintenance_end, $time_zone_name);
            
            if (!empty($maintenance_comment_lang[current_language()])) $maintenance_comment = $maintenance_comment_lang[current_language()];
            if (!empty($maintenance_link[current_language()])) $maintenance_link = $maintenance_link[current_language()];
            
            if (!empty($maintenance_comment)) {
                $message .= "\n\n" . $maintenance_comment;
            }

            if (!empty($maintenance_link)) {
                $message .= "\n\n" . text("MaintenanceLink") . ": [html]<a href='$maintenance_link' target='_blank'>" . escape_html($maintenance_link) . "</a>[/html]";
            }

            MessageHandler::setWarning($message);
        
            $_SESSION["maintenance_notified"] = true;
        }
        
        if (!empty($backup_start) && !empty($backup_end) && !empty($backup_days) && in_array(date("N"), $backup_days) && 
            time() >= xstrtotime(date("Y-m-d") . " " . $backup_start) && time() <= xstrtotime(date("Y-m-d") . " " . $backup_end) &&
            empty($_SESSION["backup_notified"])
        ) {
            $backup_start = adjust_and_format_timezone(xstrtotime(date("Y-m-d") . " " . $backup_start), text("DateTimeFormat"));
            $backup_end = adjust_and_format_timezone(xstrtotime(date("Y-m-d") . " " . $backup_end), text("DateTimeFormat"));
            MessageHandler::setWarning(sprintf(text("BackupNotification"), $backup_start, $backup_end, $time_zone_name));
        
            $_SESSION["backup_notified"] = true;
        }
    }
}

$ogtype = "website";
$ogdescription = get_site_description(current_language());
$ogimage = "";
if(file_exists($view_path . "images/forum_picture.png"))
{
  $ogimage = $view_path . "images/forum_picture.png";
}
?>