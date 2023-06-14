<?php
//------------------------------------------------------------------------------
function handle_error($errno, $errstr, $errfile, $errline)
{
    $_SESSION["LAST_ERROR"] = $errstr;
    $_SESSION["LAST_ERROR_FILE"] = $errfile;
    $_SESSION["LAST_ERROR_LINE"] = $errline;
    
    $errortype = array(
        E_ERROR => "Error",
        E_WARNING => "Warning",
        E_PARSE => "Parsing Error",
        E_NOTICE => "Notice",
        E_CORE_ERROR => "Core Error",
        E_CORE_WARNING => "Core Warning",
        E_COMPILE_ERROR => "Compile Error",
        E_COMPILE_WARNING => "Compile Warning",
        E_USER_ERROR => "User Error",
        E_USER_WARNING => "User Warning",
        E_USER_NOTICE => "User Notice",
        2048 => "Runtime Notice",   // E_STRICT
        8192 => "Deprecated Notice" // E_DEPRECATED
    );
    
    // do not generate warnings
    if (strstr($errstr, "Illegal IFD size") ||
        strstr($errstr, "mail()") ||
        strstr($errstr, "get_headers()") ||
        strstr($errstr, "exif_read_data") ||
        strstr($errstr, "imagecreatefromstring") ||
        strstr($errstr, "imagecreatefromjpeg") ||
        strstr($errstr, "failed to open stream") ||
        strstr($errstr, "get_headers") ||
        strstr($errstr, "Detected an illegal character in input string")        
    ) {
        return;
    }
    
    if (strstr($errstr, "Lock wait timeout exceeded")) {
        MessageHandler::setError("Словили 'Lock wait timeout exceeded'!");
    }
    
    if (strstr($errstr, "Deadlock")) {
        MessageHandler::setError("Словили 'Deadlock found when trying to get lock'!");
    }
    
    if (basename($errfile) == "nbbc.php" && ($errno == E_NOTICE || $errno == E_WARNING)) {
        return;
    }
    
    force_err_details($errstr);
    
    if (empty($errortype[$errno])) {
        $etype = $errno;
    } else {
        $etype = $errortype[$errno];
    }
    
    MessageHandler::setProgWarning($etype . ": " . str_replace("<br/>", "\n", trim($errstr)) . "\n\n" .
        $errfile . "\n" .
        "line " . $errline);
    
    $dtrace = debug_backtrace();
    
    trace_error(format_backtrace($dtrace));
} // handle_error
//------------------------------------------------------------------------------
set_error_handler("handle_error");
//------------------------------------------------------------------------------
function deep_implode(&$arr)
{
    $list = "";
    
    foreach ($arr as $nm => &$val) {
        if (is_array($val)) {
            $list .= deep_implode($val) . ", ";
        } elseif (is_object($val)) {
            $list .= "obj:" . get_class($val) . ", ";
        } else {
            $list .= $nm . "=" . $val . ", ";
        }
    }
    
    return "[" . trim($list, ", ") . "]";
} // deep_implode
//------------------------------------------------------------------------------
function make_arg_list(&$args)
{
    global $force_details;
    
    if ((!defined('TRACE_ERROR_ARGS') || !TRACE_ERROR_ARGS) && empty($force_details)) {
        return "";
    }
    
    $list = "";
    
    foreach ($args as $arg) {
        if (is_array($arg)) {
            $list .= deep_implode($arg) . ", ";
        } elseif (is_object($arg)) {
            $list .= "obj:" . get_class($arg) . ", ";
        } else {
            $list .= $arg . ", ";
        }
    }
    
    return trim($list, ", ");
} // make_arg_list
//------------------------------------------------------------------------------
function force_err_details($err)
{
    global $force_details;
    
    $force_details = false;
    
    if (strpos($err, "Compilation failed") !== false) {
        $force_details = true;
    }
    if (strpos($err, "Header may not contain more than a single header") !== false) {
        $force_details = true;
    }
    if (strpos($err, "Parameter must be an array or an object that implements Countable") !== false) {
        $force_details = true;
    }
    if (strpos($err, "DOMDocument::loadHTML()") !== false) {
        $force_details = true;
    }
} // force_err_details
//------------------------------------------------------------------------------
function format_backtrace(&$info)
{
    global $force_details;
    
    if (!isset($info) || count($info) == 0) {
        return "backtrace empty";
    }
    
    $trace = "";
    
    foreach ($info as $nr => &$info_entry) {
        if (!empty($trace)) {
            $trace .= "\r\n------------------\r\n";
        }
        
        if ($nr == 0) {
            $trace .= $info_entry["args"][2] . "\r\n" .
                "line: " . $info_entry["args"][3] . "\r\n" .
                $info_entry["args"][1] . "\r\n";
            
            if (((defined('TRACE_ERROR_VARS') && TRACE_ERROR_VARS) || !empty($force_details)) && !empty($info_entry["args"][4])) {
                $trace .= "\r\nLocal variables:\r\n\r\n";
                
                foreach ($info_entry["args"][4] as $nm => $val) {
                    $trace .= $nm . " = ";
                    if (is_array($val)) {
                        $trace .= deep_implode($val);
                    } elseif (is_object($val)) {
                        $trace .= "obj:" . get_class($val);
                    } else {
                        $trace .= $val;
                    }
                    $trace .= "\r\n";
                }
            }
            
            if (defined('TRACE_STACK') && TRACE_STACK) {
                $trace .= "\r\nCall stack:\r\n\r\n" . extract_call_stack($info);
            }
            
            continue;
        }
        
        $args = (isset($info_entry["args"])) ? $info_entry["args"] : array();
        $trace .= val_or_empty($info_entry["file"]) . "\r\n" .
            "line: " . val_or_empty($info_entry["line"]) . "\r\n" .
            $info_entry["function"] . "(" . make_arg_list($args) . ")";
    }
    
    return $trace;
} // format_backtrace
//------------------------------------------------------------------------------
function trace_error($msg)
{
    if (!defined('TRACE_ERRORS') ||
        !TRACE_ERRORS ||
        !empty($GLOBALS["prog_warnings_off"])
    ) {
        return;
    }
    
    $path = __FILE__;
    $basename = basename(__FILE__);
    $path = str_replace("include\\$basename", "log/", $path);
    $path = str_replace("include/$basename", "log/", $path);
    $file = $path . "trace.log";
    
    $dt = date("d.m.Y H:i:s");
    
    if ((!file_exists($file) && is_writable($path)) || is_writable($file)) {
        file_put_contents($file, "----------------------------------------------------------\r\n", FILE_APPEND);
        file_put_contents($file, $dt . "\r\n", FILE_APPEND);
        
        if (!empty($_SESSION["user_name"])) {
            file_put_contents($file, "User: " . $_SESSION["user_name"] . "\r\n", FILE_APPEND);
        } 
        
        file_put_contents($file, "Request URI: " . val_or_empty($_SERVER["REQUEST_URI"]) . "\r\n", FILE_APPEND);

        if (!empty($_POST)) {
            file_put_contents($file, "POST:" . "\r\n", FILE_APPEND);
            file_put_contents($file, print_r($_POST, true) . "\r\n", FILE_APPEND);
        }

        file_put_contents($file, $msg . "\r\n", FILE_APPEND);
        file_put_contents($file, "----------------------------------------------------------\r\n", FILE_APPEND);
        file_put_contents($file, "\r\n\r\n", FILE_APPEND);
    }
} // trace_error
//------------------------------------------------------------------------------
function sys_get_last_error()
{
    return val_or_empty($_SESSION["LAST_ERROR"]);
} // get_last_error
//------------------------------------------------------------------------------
function debug_message($msg)
{
    $path = __FILE__;
    $basename = basename(__FILE__);
    $path = str_replace("include\\$basename", "log/", $path);
    $path = str_replace("include/$basename", "log/", $path);
    $file = $path . "debug.log";
    
    if ((!file_exists($file) && is_writable($path)) || is_writable($file)) {
        file_put_contents($file, $msg . "\r\n", FILE_APPEND);
    }
} // debug_message
//------------------------------------------------------------------------------
function dump_session()
{
    $path = __FILE__;
    $basename = basename(__FILE__);
    $path = str_replace("include\\$basename", "log/", $path);
    $path = str_replace("include/$basename", "log/", $path);
    $file = $path . "session-dump.log";
    
    $msg = "";

    $msg .= "Time: " . date("d.m.Y H:i:s") . "\n";
    $msg .= "Request URI: " . val_or_empty($_SERVER["REQUEST_URI"]) . "\n";
    $msg .= "User Name: " . val_or_empty($_SESSION["user_name"]) . "\n";
    $msg .= "User ID: " . val_or_empty($_SESSION["user_id"]) . "\n";
    $msg .= "IP: " . val_or_empty($_SERVER["REMOTE_ADDR"]) . "\n";
    $msg .= "User agent: " . val_or_empty($_SERVER["HTTP_USER_AGENT"]) . "\n";
    
    $msg .= "Session ID: " . session_id() . "\n";
    $start_time = val_or_empty($_SESSION["session_start_time"]);
    if (!empty($start_time)) {
        $start_time = date("d.m.Y H:i:s", $start_time);
    }
    $msg .= "Session Start Time: " . $start_time . "\n";
    $msg .= "Session Start Request URI: " . val_or_empty($_SESSION["session_start_request_uri"]) . "\n";
    
    $msg .= "Hash sent from client: " . reqvar("hash") . "\n";
    $msg .= "Hash in the session: " . val_or_empty($_SESSION["hash"]) . "\n";
    $msg .= "Hash generated: " . val_or_empty($_SESSION["hash_generation"]) . "\n";
    $msg .= "Generation Request URI: " . val_or_empty($_SESSION["hash_generation_request_uri"]) . "\n";
    
    $msg .= "\n";
    
    $msg .= "HEADERS\n\n";
    foreach (getallheaders() as $name => $value) {
        $msg .= "$name: $value\n";
    }
    
    $msg .= "\nGET\n\n";
    $msg .= print_r($_GET, true) . "\n";
    
    $msg .= "POST\n\n";
    $msg .= print_r($_POST, true) . "\n";
    
    $msg .= "COOKIE\n\n";
    
    $q = $_COOKIE;
    unset($q["q_ignored_topics"]);
    unset($q["q_favourite_topics"]);
    unset($q["q_favourite_posts"]);
    unset($q["q_pinned_topics"]);
    
    $msg .= print_r($q, true) . "\n";
    
    $msg .= "SESSION\n\n";

    $msg .= print_r($_SESSION, true) . "\n";

    $msg .= "-----------------------\n";
    
    if ((!file_exists($file) && is_writable($path)) || is_writable($file)) {
        file_put_contents($file, $msg . "\r\n", FILE_APPEND);
    }
} // dump_session
//------------------------------------------------------------------------------
function dump_request($report_id)
{
    $path = __FILE__;
    $basename = basename(__FILE__);
    $path = str_replace("include\\$basename", "log/", $path);
    $path = str_replace("include/$basename", "log/", $path);
    $file = $path . "session-expiration.log";
    
    $msg = "Hash Problem Report From Server\n\n";
    
    $msg .= "Report ID: " . $report_id . "\n";
    $msg .= "Time: " . date("d.m.Y H:i:s") . "\n";
    $msg .= "Request URI: " . val_or_empty($_SERVER["REQUEST_URI"]) . "\n";
    $msg .= "User Name: " . val_or_empty($_SESSION["user_name"]) . "\n";
    $msg .= "User ID: " . val_or_empty($_SESSION["user_id"]) . "\n";
    $msg .= "IP: " . val_or_empty($_SERVER["REMOTE_ADDR"]) . "\n";
    $msg .= "User agent: " . val_or_empty($_SERVER["HTTP_USER_AGENT"]) . "\n";
    
    $msg .= "Session ID: " . session_id() . "\n";
    $start_time = val_or_empty($_SESSION["session_start_time"]);
    if (!empty($start_time)) {
        $start_time = date("d.m.Y H:i:s", $start_time);
    }
    $msg .= "Session Start Time: " . $start_time . "\n";
    $msg .= "Session Start Request URI: " . val_or_empty($_SESSION["session_start_request_uri"]) . "\n";
    
    $msg .= "Hash sent from client: " . reqvar("hash") . "\n";
    $msg .= "Hash in the session: " . val_or_empty($_SESSION["hash"]) . "\n";
    $msg .= "Hash generated: " . val_or_empty($_SESSION["hash_generation"]) . "\n";
    $msg .= "Generation Request URI: " . val_or_empty($_SESSION["hash_generation_request_uri"]) . "\n";
    
    $msg .= "\n";
    
    $msg .= "HEADERS\n\n";
    foreach (getallheaders() as $name => $value) {
        $msg .= "$name: $value\n";
    }
    
    $msg .= "\nGET\n\n";
    $msg .= print_r($_GET, true) . "\n";
    
    $msg .= "POST\n\n";
    $msg .= print_r($_POST, true) . "\n";
    
    $msg .= "COOKIE\n\n";
    
    $q = $_COOKIE;
    unset($q["q_ignored_topics"]);
    unset($q["q_favourite_topics"]);
    unset($q["q_favourite_posts"]);
    unset($q["q_pinned_topics"]);
    
    $msg .= print_r($q, true) . "\n";
    
    $msg .= "-----------------------\n";
    
    if ((!file_exists($file) && is_writable($path)) || is_writable($file)) {
        file_put_contents($file, $msg . "\r\n", FILE_APPEND);
    }
} // dump_request
//------------------------------------------------------------------------------
function session_debug_message($msg)
{
    if (empty($_SESSION["user_login"])) {
        return;
    }
    
    $path = __FILE__;
    $basename = basename(__FILE__);
    $path = str_replace("include\\$basename", "log/", $path);
    $path = str_replace("include/$basename", "log/", $path);
    $file = $path . "dbg_" . $_SESSION["user_login"] . ".log";
    
    if ((!file_exists($file) && is_writable($path)) || is_writable($file)) {
        file_put_contents($file, $msg . "\r\n", FILE_APPEND);
    }
} // session_debug_message
//------------------------------------------------------------------------------
function trace_case($msg, $case)
{
    if ($case == "autologin" &&
        preg_match("/\(KHTML, like Gecko\) Chrome\/87/", val_or_empty($_SERVER["HTTP_USER_AGENT"]))) {
        $user = "";
        $dt = date("d.m.Y H:i:s");
        $ip = val_or_empty($_SERVER["REMOTE_ADDR"]);
        if (!empty($_SESSION["user_name"])) {
            $user = ", " . $_SESSION["user_name"];
        }
        
        $session_start = "-";
        if (!empty($_SESSION["session_start_time"])) {
            $session_start = date("d.m.Y H:i:s", $_SESSION["session_start_time"]);
        }
        
        $host = val_or_empty($_SERVER["SERVER_NAME"]);
        if (empty($host)) {
            $host = val_or_empty($_SERVER["SERVER_ADDR"]);
        }
        
        $msg = $dt . $user . ", " . $ip . "\n" .
            "user agent: " . val_or_empty($_SERVER["HTTP_USER_AGENT"]) . "\n" .
            "host: " . $host . "\n" .
            "session id: " . session_id() . "\n" .
            "session start: " . $session_start . "\n" .
            "---------------\n" .
            $msg .
            "\n----------------------------------";
        
        trace_message_to_file($msg, "chrom_autologin.log");
    }
    
    if ($case == "readmarker" && val_or_empty($_SERVER["HTTP_USER_AGENT"]) == "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:82.0) Gecko/20100101 Firefox/82.0") {
        $user = "";
        $dt = date("d.m.Y H:i:s");
        $ip = val_or_empty($_SERVER["REMOTE_ADDR"]);
        if (!empty($_SESSION["user_name"])) {
            $user = ", " . $_SESSION["user_name"];
        }
        
        $session_start = "-";
        if (!empty($_SESSION["session_start_time"])) {
            $session_start = date("d.m.Y H:i:s", $_SESSION["session_start_time"]);
        }
        
        $msg = $dt . $user . ", " . $ip . "\n" .
            "session id: " . session_id() . "\n" .
            "session start: " . $session_start . "\n" .
            "---------------\n" .
            $msg .
            "\n----------------------------------";
        
        trace_message_to_file($msg, "kiberluddit.log");
    }
    
    if ($case == "locks") {
        $user = "";
        $dt = date("d.m.Y H:i:s");
        $ip = val_or_empty($_SERVER["REMOTE_ADDR"]);
        if (!empty($_SESSION["user_name"])) {
            $user = ", " . $_SESSION["user_name"];
        }
        
        $session_start = "-";
        if (!empty($_SESSION["session_start_time"])) {
            $session_start = date("d.m.Y H:i:s", $_SESSION["session_start_time"]);
        }
        
        $msg = $dt . $user . ", " . $ip . "\n" .
            "session id: " . session_id() . "\n" .
            "session start: " . $session_start . "\n" .
            "---------------\n" .
            $msg .
            "\nEND LOCK DUMP" .
            "\n----------------------------------";
        
        trace_message_to_file($msg, "locks.log");
    }
} // trace_case
//------------------------------------------------------------------------------
function trace_message_to_file($msg, $file)
{
    $path = __FILE__;
    $basename = basename(__FILE__);
    $path = str_replace("include\\$basename", "log/", $path);
    $path = str_replace("include/$basename", "log/", $path);
    $file = $path . $file;
    
    if ((!file_exists($file) && is_writable($path)) || is_writable($file)) {
        file_put_contents($file, $msg . "\r\n", FILE_APPEND);
    }
} // trace_message_to_file
//------------------------------------------------------------------------------
function profile_point($msg)
{
    static $profile_time;
    
    if (empty($profile_time)) {
        $profile_time = microtime(true);
    }
    
    $path = __FILE__;
    $basename = basename(__FILE__);
    $path = str_replace("include\\$basename", "log/", $path);
    $path = str_replace("include/$basename", "log/", $path);
    $file = $path . "profile.log";
    
    $msg = $msg . ": " . number_format(microtime(true) - $profile_time, 3, ".", "") . " seconds";
    
    if ((!file_exists($file) && is_writable($path)) || is_writable($file)) {
        file_put_contents($file, $msg . "\r\n", FILE_APPEND);
    }
    
    $profile_time = microtime(true);
} // profile_point
//------------------------------------------------------------------------------
function profile_message($msg)
{
    static $profile_time;
    
    if (empty($profile_time)) {
        $profile_time = microtime(true);
    }
    
    $path = __FILE__;
    $basename = basename(__FILE__);
    $path = str_replace("include\\$basename", "log/", $path);
    $path = str_replace("include/$basename", "log/", $path);
    $file = $path . "profile.log";
    
    if ((!file_exists($file) && is_writable($path)) || is_writable($file)) {
        file_put_contents($file, $msg . "\r\n", FILE_APPEND);
    }
    
    $profile_time = microtime(true);
} // profile_message
//------------------------------------------------------------------------------
function extract_call_stack($btrace)
{
    if (empty($btrace) || !is_array($btrace)) {
        return "";
    }
    
    $trace = "";
    
    $indent = "";
    foreach ($btrace as $btrace_entry) {
        
        
        if (!empty($btrace_entry["function"]) &&
            ($btrace_entry["function"] == "handle_error" ||
                strpos($btrace_entry["function"], "{closure}") !== false ||
                $btrace_entry["function"] == "handleError" ||
                $btrace_entry["function"] == "trigger_error"
            )
        ) {
            continue;
        }
        
        if (empty($btrace_entry["file"])) {
            continue;
        }
        
        if (!empty($btrace_entry["function"])) {
            $trace .= $indent . $btrace_entry["function"] . "() ";
        }
        
        $trace .= "[";
        
        $trace .= str_replace(APPLICATION_ROOT, "", str_replace("\\", "/", $btrace_entry["file"]));
        
        $trace .= ", ";
        
        if (empty($btrace_entry["line"])) {
            $trace .= "line number undefined";
        } else {
            $trace .= $btrace_entry["line"];
        }
        
        $trace .= "]";
        
        $args = (isset($btrace_entry["args"])) ? $btrace_entry["args"] : [];
        $args_str = make_arg_list($args);
        
        if (!empty($btrace_entry["function"]) && !empty($args_str)) {
            $trace .= "  " . $btrace_entry["function"] . "(" . $args_str . ")";
        }
        
        $trace .= "\r\n";
        
        $indent .= "  ";
    }
    
    return trim($trace);
} // extract_call_stack
//------------------------------------------------------------------------------
?>