<?php
require_once "utf8_functions_inc.php";
require_once "aux_functions_inc.php";
require_once "image_utils_inc.php";

$path = APPLICATION_ROOT;
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
require_once APPLICATION_ROOT . "Zend/Http/Client.php";

//------------------------------------------------------------------------------
function val_or_empty(&$param)
{
    return isset($param) ? $param : "";
}

//------------------------------------------------------------------------------
function quotes_or_null($param)
{
    return (string)$param === "" ? "NULL" : "'" . $param . "'";
}

//------------------------------------------------------------------------------
function encrypt($s, $key)
{
    $r = "";
    for ($i = 0; $i <= strlen($s); $i++) {
        $si = isset($s[$i]) ? $s[$i] : "";
        $r .= substr(str_shuffle(md5($key)), ($i % strlen(md5($key))), 1) . $si;
    }
    
    for ($i = 1; $i <= strlen($r); $i++) {
        $s[$i - 1] = chr(ord($r[$i - 1]) + ord(substr(md5($key), ($i % strlen(md5($key))) - 1, 1)));
    }
    return base64_encode($s);
} // encrypt
//------------------------------------------------------------------------------
function decrypt($s, $key)
{
    $s = base64_decode($s);
    for ($i = 1; $i <= strlen($s); $i++) {
        $s[$i - 1] = chr(ord($s[$i - 1]) - ord(substr(md5($key), ($i % strlen(md5($key))) - 1, 1)));
    }
    $r = "";
    for ($i = 1; $i <= strlen($s) - 2; $i = $i + 2) {
        $r .= $s[$i];
    }
    return $r;
} // decrypt
//------------------------------------------------------------------------------
function encode_array(&$arr, $src_encoding, $trg_encoding)
{
    foreach ($arr as &$val) {
        $val = @iconv($src_encoding, $trg_encoding . "//IGNORE", $val);
    }
} // encode_array
//------------------------------------------------------------------------------
function month_name($month, $short = false)
{
    $appendix = "";
    if ($short) {
        $appendix = "Short";
    }
    
    switch ($month) {
        case  1:
            return text("January" . $appendix);
        case  2:
            return text("February" . $appendix);
        case  3:
            return text("March" . $appendix);
        case  4:
            return text("April" . $appendix);
        case  5:
            return text("May" . $appendix);
        case  6:
            return text("June" . $appendix);
        case  7:
            return text("July" . $appendix);
        case  8:
            return text("August" . $appendix);
        case  9:
            return text("September" . $appendix);
        case 10:
            return text("October" . $appendix);
        case 11:
            return text("November" . $appendix);
        case 12:
            return text("December" . $appendix);
    }
    
    return $month;
} // month_name
//------------------------------------------------------------------------------
function seconds_to_string($seconds)
{
    $hours = floor($seconds / 3600);
    $seconds = $seconds - $hours * 3600;
    $minutes = floor($seconds / 60);
    $seconds = $seconds - $minutes * 60;
    
    if ($seconds <= 9) {
        $seconds = "0" . $seconds;
    }
    if ($minutes <= 9) {
        $minutes = "0" . $minutes;
    }
    if ($hours <= 9) {
        $hours = "0" . $hours;
    }
    
    return $hours . ":" . $minutes . ":" . $seconds;
} // seconds_to_string
//------------------------------------------------------------------------------
function smart_date($dt)
{
    if (empty($dt)) {
        return "";
    }
    
    $anow = adjust_timezone(time());
    
    $today = date(text("DateFormat"), $anow);
    $yesterday = date(text("DateFormat"), xstrtotime("yesterday", $anow));
    $tomorrow = date(text("DateFormat"), xstrtotime("tomorrow", $anow));

    $dt = str_replace($today, text("Today"), $dt);
    $dt = str_replace($yesterday, text("Yesterday"), $dt);
    $dt = str_replace($tomorrow, text("Tomorrow"), $dt);
    
    return $dt;
} // smart_date
//------------------------------------------------------------------------------
function smart_date2($dt)
{
    if (empty($dt)) {
        return text("Never");
    }
    
    $diff = time() - $dt;
    if ($diff < 60) {
        $dt = text("Now");
    } elseif ($diff < 3600) {
        $dt = format_duration($diff);
    } else {
        $dt = smart_date(adjust_and_format_timezone($dt, text("DateTimeFormat")));
    }
    
    return $dt;
} // smart_date2
//------------------------------------------------------------------------------
function convert_timezone($time, $source_timezone, $target_timezone)
{
    $source_timezone_gmt_offset = get_timezone_gmt_offset($source_timezone);
    $target_timezone_gmt_offset = get_timezone_gmt_offset($target_timezone);
    
    $time -= $source_timezone_gmt_offset; // gmt time
    $time += $target_timezone_gmt_offset;
    
    return $time;
} // convert_timezone
//------------------------------------------------------------------------------
function get_timezone_gmt_offset($timezone)
{
    $tz = new DateTimeZone($timezone);
    
    $gmt_tz = new DateTimeZone("GMT");
    $gmt_time = new DateTime("now", $gmt_tz);
    
    return $tz->getOffset($gmt_time);
} // get_timezone_gmt_offset
//------------------------------------------------------------------------------
function format_gmt_offset($offset)
{
    $sign = $offset < 0 ? "-" : "+";
    $offset = abs($offset);
    
    $offset = "[GMT" . $sign . sprintf("%02s", floor($offset / 3600)) . ":" . sprintf("%02s", ($offset % 3600) / 60) . "]";
    
    return $offset;
} // format_gmt_offset
//------------------------------------------------------------------------------
function xstrtotime($str)
{
	if (empty($str)) return 0;
	
	return strtotime($str);
} // xstrtotime
//------------------------------------------------------------------------------
function xrawurlencode($str)
{
	if (empty($str)) return "";
	
	return rawurlencode($str);
} // xrawurlencode
//------------------------------------------------------------------------------

function cmp_gmt_offset($z1, $z2)
{
    $zv1 = get_timezone_gmt_offset($z1);
    $zv2 = get_timezone_gmt_offset($z2);
    
    if ($zv1 == $zv2) {
        return strcmp($z1, $z2);
    }
    
    return ($zv1 < $zv2) ? -1 : 1;
} // cmp_gmt_offset
//------------------------------------------------------------------------------
function cmp_length($v1, $v2)
{
    if (utf8_strlen($v1) == utf8_strlen($v2)) {
        return 0;
    }
    
    return (utf8_strlen($v1) < utf8_strlen($v2)) ? 1 : -1; // reverse order desired
} // cmp_length
//------------------------------------------------------------------------------
function cmp_rates($v1, $v2)
{
    if ($v1["cnt"] == $v2["cnt"]) {
        return 0;
    }
    
    return ($v1["cnt"] < $v2["cnt"]) ? 1 : -1;
} // cmp_rates
//------------------------------------------------------------------------------
function adjust_timezone($time)
{
    if (empty($_SESSION["time_zone"]) || !in_array($_SESSION["time_zone"], $GLOBALS['time_zones']) || empty($time)) {
        return $time;
    }
    
    return convert_timezone($time, TIME_ZONE, $_SESSION["time_zone"]);
} // adjust_timezone
//------------------------------------------------------------------------------
function back_adjust_timezone($time)
{
    if (empty($_SESSION["time_zone"]) || !in_array($_SESSION["time_zone"], $GLOBALS['time_zones']) || empty($time)) {
        return $time;
    }
    
    return convert_timezone($time, $_SESSION["time_zone"], TIME_ZONE);
} // back_adjust_timezone
//------------------------------------------------------------------------------
function adjust_and_format_timezone($time, $format)
{
    if (empty($time)) {
        return $time;
    }
    
    return date($format, adjust_timezone($time));
} // adjust_and_format_timezone
//------------------------------------------------------------------------------
function format_number($str, $precision = 0)
{
    if ((string)$str === "") {
        return "";
    }
    
    if (!is_numeric($str) || $str == "1+") {
        return $str;
    }
    
    return number_format($str, $precision, ",", " ");
} // format_number
//------------------------------------------------------------------------------
function format_duration($duration)
{
    if (empty($duration)) {
        return "0 " . text("MinutesShort");
    }
    
    $string = "";
    
    $mins = floor($duration / 60);
    if (empty($mins)) {
        $string .= "1 " . text("MinutesShort");
        return trim($string);
    }
    
    $days = floor($duration / (24 * 3600));
    if (!empty($days)) {
        $string .= $days . " " . text("DaysShort") . " ";
    }
    
    $duration -= $days * (24 * 3600);
    
    $hours = floor($duration / 3600);
    if (!empty($hours)) {
        $string .= $hours . " " . text("HoursShort") . " ";
    }
    
    $duration -= $hours * 3600;
    
    $mins = floor($duration / 60);
    if (!empty($mins)) {
        $string .= $mins . " " . text("MinutesShort");
    }
    
    return trim($string);
}

//------------------------------------------------------------------------------
function format_date($date, $format)
{
    if (empty($date)) {
        return "";
    }
    
    return date($format, xstrtotime($date));
}

//------------------------------------------------------------------------------
function invert_dates(&$start_date, &$end_date, $format)
{
    $start_date_iso = iso_date($start_date, $format);
    $end_date_iso = iso_date($end_date, $format);
    
    if (empty($start_date_iso) || $start_date_iso == "error" ||
        empty($end_date_iso) || $end_date_iso == "error"
    ) {
        return true;
    }
    
    if (xstrtotime($start_date_iso) > xstrtotime($end_date_iso)) {
        $tmp = $end_date;
        $end_date = $start_date;
        $start_date = $tmp;
    }
    
    return true;
} // invert_dates
//------------------------------------------------------------------------------
function get_param($name)
{
    if (!empty($_GET[$name])) {
        return $_GET[$name];
    }
    
    if (!empty($_POST[$name])) {
        return $_POST[$name];
    }
    
    if (isset($_REQUEST["params"])) {
        $matches = array();
        if (preg_match_all("/([^\\/:]+):([^\\/:]*)/i", $_REQUEST["params"], $matches, PREG_SET_ORDER) != 0) {
            foreach ($matches as $match) {
                if ($match[1] == $name) {
                    return $match[2];
                }
            }
        }
    }
    
    return "";
} // get_param
//------------------------------------------------------------------------------
function shrink_spaces(&$text, $omit_colon = false)
{
    if (empty($text)) $text = "";
	
	$text = str_replace("\x0", "\x20", $text);
    $text = str_replace("\xc2\xa0", "\x20", $text);
    $text = preg_replace("/\\s+/u", " ", $text);
    $text = trim($text);
    
    $chars = ";,.-_";
    if (empty($omit_colon)) {
        $chars .= ":";
    }
    
    $text = trim($text, $chars);
} // shrink_spaces
//------------------------------------------------------------------------------
function reqvar_empty($name, $strict = false)
{
    if (!isset($_REQUEST[$name])) {
        return true;
    }
    
    $val = str_replace("\x0", "\x20", $_REQUEST[$name]);
    $val = str_replace("\xc2\xa0", "\x20", $_REQUEST[$name]);
    
    $val = utf8_trim($val, "\x20");
    $val = utf8_trim($val);
    
    return ($val === "" || ($strict ? (string)$val === "" : empty($val)));
} // reqvar_empty
//------------------------------------------------------------------------------
function reqvar($name, $trim = true)
{
    if (isset($_REQUEST[$name])) {
        if (is_array($_REQUEST[$name])) {
            return "error(array passed)";
        }
        
        $val = str_replace("\x0", "\x20", $_REQUEST[$name]);
        $val = str_replace("\xc2\xa0", "\x20", $_REQUEST[$name]);
        
        if ($trim) {
            $val = utf8_trim($val, "\x20");
            $val = utf8_trim($val);
        }
        
        return $val;
    } else {
        return "";
    }
} // reqvar
//------------------------------------------------------------------------------
function reqvar_checked($name)
{
    return checked(reqvar($name));
} // reqvar_checked
//------------------------------------------------------------------------------
function reqvar_selected($name, $val)
{
    if (!empty($_REQUEST[$name]) && is_array($_REQUEST[$name])) {
        return in_array($val, $_REQUEST[$name]) ? "selected" : "";
    }
    
    return selected(reqvar($name), $val);
} // reqvar_selected
//------------------------------------------------------------------------------
function reqvar_radio_selected($name, $val)
{
    return radio_selected(reqvar($name), $val);
} // reqvar_radio_selected
//------------------------------------------------------------------------------
function checked($val)
{
    return empty($val) ? "" : "checked";
} // checked
//------------------------------------------------------------------------------
function selected($option, $val)
{
    return $option != $val ? "" : "selected";
} // selected
//------------------------------------------------------------------------------
function radio_selected($option, $val)
{
    return $option != $val ? "" : "checked";
} // radio_selected
//------------------------------------------------------------------------------
function echo_reqvar($name, $qmode = ENT_QUOTES)
{
    echo htmlspecialchars(reqvar($name), $qmode);
} // echo_reqvar
//------------------------------------------------------------------------------
function echo_html($val, $qmode = ENT_QUOTES)
{
    echo htmlspecialchars($val ?? "", $qmode);
} // echo_html
//------------------------------------------------------------------------------
function escape_html($val, $qmode = ENT_QUOTES)
{
    return htmlspecialchars($val ?? "", $qmode);
} // escape_html
//------------------------------------------------------------------------------
function escape_html_array(&$arr, $qmode = ENT_QUOTES)
{
    foreach ($arr as &$val) {
        if (is_array($val)) {
            escape_html_array($val);
        } else {
            $val = htmlspecialchars($val, $qmode);
        }
    }
} // escape_html_array
//------------------------------------------------------------------------------
function escape_js($str, $escape_single_quotes = false)
{
    if ($str === null) return null;
	
	$str = str_replace("\\", "\\\\", $str);
    $str = str_replace("\n", "\\n", $str);
    $str = str_replace("\r", "\\r", $str);
    $str = str_replace("\t", "\\t", $str);
    
    $str = str_replace("/", "\\/", $str);
    $str = str_replace("\"", "\\\"", $str);
    
    if ($escape_single_quotes) {
        $str = str_replace("'", "&apos;", $str);
    }
    
    return $str;
} // escape_js
//------------------------------------------------------------------------------
function escape_php($str)
{
    return preg_replace("/[']/", "\\'", $str);
} // escape_php
//------------------------------------------------------------------------------
function preg_r_escape($pttr)
{
    return preg_replace("/[\\\\\\$]/", "\\\\$0", $pttr);
} // preg_escape
//------------------------------------------------------------------------------
function preg_p_escape($pttr)
{
    return preg_replace("/[\\\\\\[\\]\\+\\?\\-\\^\\$\\(\\)\\/\\.\\|\\{\\}\\|]/", "\\\\$0", $pttr);
} // preg_escape
//------------------------------------------------------------------------------
function echo_js($str, $escape_single_quotes = false)
{
    echo escape_js($str, $escape_single_quotes);
} // echo_js
//------------------------------------------------------------------------------
function spec_cut($str, $ln)
{
    if (utf8_strlen($str) <= ($ln + 4)) {
        return $str;
    }
    
    return utf8_trim(utf8_substr($str, 0, $ln), ". ") . " ...";
} // spec_cut
//------------------------------------------------------------------------------
function iso_date($date, $format, $empty_on_error = false)
{
    if (empty($date)) {
        return "";
    }
    
    $err_status = "error";
    if ($empty_on_error) {
        $err_status = "";
    }
    
    $pattern = preg_replace(array("/Y/", "/m/", "/d/", "/H/", "/i/", "/s/"), array("([0-9]{4})", "([0-9]{1,2})", "([0-9]{1,2})", "([0-9]{1,2})", "([0-9]{1,2})", "([0-9]{1,2})"), preg_quote($format));
    
    $units = array();
    
    if (!preg_match("/" . $pattern . "/", $date, $units)) {
        return $err_status;
    }
    
    array_shift($units);
    
    //return implode("|", $units);
    
    $order = preg_replace("/[^YmdHis]/", "", $format);
    
    $date_part = "";
    $result = "";
    $pos_Y = strpos($order, "Y");
    $pos_m = strpos($order, "m");
    $pos_d = strpos($order, "d");
    if (!($pos_Y === false || $pos_m === false || $pos_d === false)) {
        if (!checkdate($units[$pos_m], $units[$pos_d], $units[$pos_Y])) {
            return $err_status;
        }
        
        $date_part = $units[$pos_Y] . "-" . $units[$pos_m] . "-" . $units[$pos_d];
    }
    
    $time_part = "";
    $pos_H = strpos($order, "H");
    $pos_i = strpos($order, "i");
    $pos_s = strpos($order, "s");
    if (!($pos_H === false || $pos_i === false)) {
        if (!is_numeric($units[$pos_H]) || $units[$pos_H] < 0 || $units[$pos_H] > 23) {
            return $err_status;
        }
        if (!is_numeric($units[$pos_i]) || $units[$pos_i] < 0 || $units[$pos_i] > 59) {
            return $err_status;
        }
        
        $time_part = $units[$pos_H] . ":" . $units[$pos_i];
        
        if (!($pos_s === false)) {
            if (!is_numeric($units[$pos_s]) || $units[$pos_s] < 0 || $units[$pos_s] > 59) {
                return $err_status;
            }
            $time_part .= ":" . $units[$pos_s];
        }
    }
    
    return trim($date_part . " " . $time_part);
} // iso_date
//-----------------------------------------------------------------
function is_associative(&$array)
{
    if (!is_array($array) || empty($array)) {
        return false;
    }
    
    $keys = array_keys($array);
    
    return array_keys($keys) !== $keys;
} // is_associative
//-----------------------------------------------------------------
function array_to_json(&$array)
{
    // PHP json_encode escapes forward slash and the ExtJS says JSON parse error
    
    //$result = json_encode($array);
    //return $result;
    
    $result = "";
    if (is_associative($array)) {
        foreach ($array as $key => $val) {
            $result .= "\"" . escape_js($key) . "\": ";
            
            if (is_array($val)) {
                $result .= array_to_json($val);
            } elseif (is_string($val)) {
                $result .= "\"" . escape_js($val) . "\"";
            } elseif (is_bool($val)) {
                $result .= $val ? "true" : "false";
            } elseif (is_int($val) || is_long($val) || is_float($val)) {
                $result .= escape_js($val);
            } else {
                $result .= "\"\"";
            }
            
            $result .= ", ";
        }
        
        $result = "{ " . trim($result, ", ") . " }";
    } else {
        foreach ($array as $val) {
            if (is_array($val)) {
                $result .= array_to_json($val);
            } elseif (is_bool($val)) {
                $result .= $val ? "true" : "false";
            } elseif (is_numeric($val)) {
                $result .= "\"" . escape_js($val) . "\"";
            } elseif (is_string($val)) {
                $result .= "\"" . escape_js($val) . "\"";
            } else {
                $result .= "null";
            }
            
            $result .= ", ";
        }
        
        $result = "[" . trim($result, ", ") . "]";
    }
    
    return $result;
} // array_to_json
//-----------------------------------------------------------------
function validate_internal_name($str, $strong = false)
{
    $add = $strong ? "" : "\\-\\.";
    return (preg_match("/[^a-z0-9_$add]/i", $str) == 0);
} // validate_internal_name
//------------------------------------------------------------------------------
function validate_login($str)
{
    return (preg_match("/[^a-z0-9_\\-@\\.]/i", $str) == 0);
} // validate_login
//------------------------------------------------------------------------------
function make_request_url()
{
    //------------------------------------------------
    function make_request_from_array($var, $arr)
    {
        $url = "";
        
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $url .= make_request_from_array($var . "[" . $key . "]", $val);
            } else {
                $url .= xrawurlencode($var) . "[" . xrawurlencode($key) . "]=" . xrawurlencode($val) . "&";
            }
        }
        
        return $url;
    } // make_request_from_array
    //------------------------------------------------
    
    $url = $_SERVER["REQUEST_URI"] . "?";
    
    foreach ($_POST as $var => $val) {
        if (is_array($val)) {
            $url .= make_request_from_array($var, $val);
        } else {
            $url .= xrawurlencode($var) . "=" . xrawurlencode($val) . "&";
        }
    }
    
    return trim($url, "&?");
} // make_request_url
//------------------------------------------------------------------------------
function make_param_string(&$parameters, $use_mrewrite)
{
    $param_string = "";
    
    foreach ($parameters as $name => $value) {
        if ($use_mrewrite) {
            $param_string .= xrawurlencode($name) . ":" . xrawurlencode($value) . "/";
        } else {
            $param_string .= "&amp;" . xrawurlencode($name) . "=" . xrawurlencode($value);
        }
    }
    
    return $param_string;
} // make_param_string
//-----------------------------------------------------------------
function sort_array_locale(&$arr)
{
    asort($arr, SORT_LOCALE_STRING);
} // sort_array_locale
//------------------------------------------------------------------------------
function rsort_array_locale(&$arr)
{
    arsort($arr, SORT_LOCALE_STRING);
} // rsort_array_locale
//------------------------------------------------------------------------------
function get_site_name($lang)
{
    $site_name = "";
    
    if (file_exists(APPLICATION_ROOT . "lang/" . $lang . "/site_name.txt")) {
        $site_name = trim(file_get_contents(APPLICATION_ROOT . "lang/" . $lang . "/site_name.txt"));
    }
    
    if (empty($site_name)) {
        $site_name = try_translate("Forum", $lang);
    }
    
    return $site_name;
} // get_site_name
//------------------------------------------------------------------------------
function get_site_description($lang)
{
    $site_description = "";
    
    if (file_exists(APPLICATION_ROOT . "lang/" . $lang . "/site_description.txt")) {
        $site_description = trim(file_get_contents(APPLICATION_ROOT . "lang/" . $lang . "/site_description.txt"));
    }
    
    return $site_description;
} // get_site_description
//------------------------------------------------------------------------------
function is_https()
{
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        return true;
    } elseif ($_SERVER['SERVER_PORT'] == 443) {
        return true;
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        return true;
    } 
    
    return false;
}
//------------------------------------------------------------------------------
function get_host_name()
{
    $host = val_or_empty($_SERVER["HTTP_HOST"]);
    if (empty($host)) {
        $host = val_or_empty($_SERVER["SERVER_NAME"]);
    }
    if (empty($host)) {
        $host = val_or_empty($_SERVER["SERVER_ADDR"]);
    }
    
    if (empty($host)) {
        $host = "undefined";
    }
    
    return $host;
}
//------------------------------------------------------------------------------
function get_host_address($use_host = "")
{
    if (!empty($use_host)) {
        return $use_host;
    }
    
    $protocol = "http://";
    if (is_https()) {
        $protocol = "https://";
    }
    
    $host = rtrim(get_host_name(), "/");
    
    $port = val_or_empty($_SERVER["SERVER_PORT"]);
    if ($port == "80" || $port == "443") {
        $port = "";
    }
    if (!empty($port)) {
        $port = ":" . $port;
    }
    
    return $protocol . $host . $port;
} // get_host_address
//------------------------------------------------------
function get_url_path()
{
    $path = "/";
    
    if (defined('HOME_DIRECTORY')) {
        $path = HOME_DIRECTORY;
    }
    
    if (empty($path)) {
        $path = "/";
    }
    
    return $path;
} // get_url_path
//------------------------------------------------------
function get_request_url()
{
    $url = val_or_empty($_SERVER["SCRIPT_NAME"]);
    
    $params = "?" . session_name() . "=" . session_id();
    
    if (isset($_GET)) {
        foreach ($_GET as $key => $value) {
            $params .= "&" . $key . "=" . xrawurlencode($value);
        }
    }
    
    if (isset($_POST)) {
        foreach ($_POST as $key => $value) {
            $params .= "&" . $key . "=" . xrawurlencode($value);
        }
    }
    
    return $url . $params;
} // get_request_url
//------------------------------------------------------
function set_smtp_params($force_smtp_host = "")
{
    $SMTP_HOST = defined('SMTP_HOST') ? SMTP_HOST : "localhost";
    
    if (!empty($force_smtp_host)) {
        $SMTP_HOST = $force_smtp_host;
    }
    
    $smtp_info = explode(":", $SMTP_HOST);
    
    if (!empty($smtp_info[0])) {
        $SMTP_HOST = $smtp_info[0];
    } else {
        $SMTP_HOST = "localhost";
    }
    
    if (!empty($smtp_info[1])) {
        $SMTP_PORT = $smtp_info[1];
    } else {
        $SMTP_PORT = "25";
    }
    
    ini_set("SMTP", $SMTP_HOST);
    ini_set("smtp_port", $SMTP_PORT);
} // set_smtp_params
//------------------------------------------------------
function check_sender(&$sender)
{
    if (empty($sender) && defined('DEFAULT_SENDER')) {
        $sender = DEFAULT_SENDER;
    }
    if (empty($sender)) {
        if (!empty($_SERVER["HTTP_HOST"])) {
            $sender = "webadmin@" . str_ireplace("www.", "", $_SERVER["HTTP_HOST"]);
        } else {
            $sender = "webadmin@localhost";
        }
    }
} // check_sender
//------------------------------------------------------
function dir_size($dir)
{
    if (!is_dir($dir) && !is_file($dir)) {
        return 0;
    }
    
    if (is_file($dir)) {
        return filesize($dir);
    }
    
    $total_size = 0;
    
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file == "." || $file == "..") {
            continue;
        }
        
        if (is_dir($dir . DIRECTORY_SEPARATOR . $file)) {
            $total_size += dir_size($dir . DIRECTORY_SEPARATOR . $file);
        } else {
            if (is_file($dir . DIRECTORY_SEPARATOR . $file)) {
                $total_size += filesize($dir . DIRECTORY_SEPARATOR . $file);
            }
        }
    }
    
    return $total_size;
} // dir_size
//------------------------------------------------------
function del_dir($dir)
{
    if (!$dh = @opendir($dir)) {
        return false;
    }
    
    while ($obj = readdir($dh)) {
        if ($obj == '.' || $obj == '..') {
            continue;
        }
        
        if (is_dir($dir . '/' . $obj)) {
            if (!del_dir($dir . '/' . $obj)) {
                @closedir($dh);
                return false;
            }
        } else {
            if (!@unlink($dir . '/' . $obj)) {
                @closedir($dh);
                return false;
            }
        }
    } // while
    
    @closedir($dh);
    
    if (!@rmdir($dir)) {
        return false;
    }
    
    return true;
} // del_dir
//---------------------------------------------------------------
function clear_dir($dir)
{
    if (!$dh = @opendir($dir)) {
        return false;
    }
    
    while ($obj = readdir($dh)) {
        if ($obj == '.' || $obj == '..') {
            continue;
        }
        
        if (is_dir($dir . '/' . $obj)) {
            if (!del_dir($dir . '/' . $obj)) {
                @closedir($dh);
                return false;
            }
        } else {
            if (!@unlink($dir . '/' . $obj)) {
                @closedir($dh);
                return false;
            }
        }
    } // while
    
    @closedir($dh);
    
    return true;
} // clear_dir
//---------------------------------------------------------------
function copy_dir_contents($source_dir, $target_dir)
{
    if (!file_exists($target_dir) || !is_dir($target_dir)) {
        return false;
    }
    
    if (!$dh = @opendir($source_dir)) {
        return false;
    }
    
    while ($obj = readdir($dh)) {
        if ($obj == '.' || $obj == '..') {
            continue;
        }
        
        if (is_dir($source_dir . '/' . $obj)) {
            if (!file_exists($target_dir . '/' . $obj) &&
                !@mkdir($target_dir . '/' . $obj)) {
                @closedir($dh);
                return false;
            }
            
            if (!copy_dir_contents($source_dir . '/' . $obj, $target_dir . '/' . $obj)) {
                @closedir($dh);
                return false;
            }
        } else {
            if (!copy($source_dir . '/' . $obj, $target_dir . '/' . $obj)) {
                return false;
            }
        }
    } // while
    
    @closedir($dh);
    
    return true;
} // copy_dir_contents
//-----------------------------------------------------------------
function wrap_as_html($body)
{
    $body =
        "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\">
  <html>
  <head>
  <title></title>
  <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>
  <style type=\"text/css\">
  body
  {
    font-size: 12px;
    font-family: verdana;
  }
  </style>
  </head>
  <body>
  " . $body . "
  </body>
  </html>
  ";
    
    return $body;
} // wrap_as_html
//------------------------------------------------------
function filename_from_utf8($name)
{
    if (!defined("FILE_NAME_ENCODING")) {
        return $name;
    }
    
    return iconv("UTF-8", FILE_NAME_ENCODING . "//IGNORE", $name);
} // filename_from_utf8
//------------------------------------------------------
function filename_to_utf8($name)
{
    if (!defined("FILE_NAME_ENCODING")) {
        return $name;
    }
    
    return iconv(FILE_NAME_ENCODING, "UTF-8//IGNORE", $name);
} // filename_to_utf8
//------------------------------------------------------
function send_mail($to, $subject, $message, $additional_headers)
{
    $err = null;
    
    if (!@mail($to, $subject, $message, $additional_headers)) {
        $err_info = error_get_last();
        
        if (!empty($err_info)) {
            $err = $err_info["message"];
        } else {
            $err = "Sending e-mail failed for unknown reason.";
        }
    }
    
    return $err;
} // send_mail
//------------------------------------------------------
function array_to_dom(&$xmldoc, &$node, &$arr)
{
    foreach ($arr as $key => &$val) {
        $child = $xmldoc->createElement("item");
        $child->setAttribute("name", $key);
        
        if (is_array($val)) {
            array_to_dom($xmldoc, $child, $val);
        } else {
            $txtnode = $xmldoc->createTextNode($val ?? "");
            $child->appendChild($txtnode);
        }
        
        $node->appendChild($child);
    }
} // array_to_dom
//------------------------------------------------------
function dom_to_array(&$xmldoc, &$node, &$arr)
{
    if (!$node->hasChildNodes()) {
        return;
    }
    
    foreach ($node->childNodes as $child) {
        if ($child->nodeType == XML_TEXT_NODE) {
            continue;
        }
        
        $name = $child->nodeName;
        if ($child->nodeName == "item") {
            $name = $child->getAttribute("name");
        }
        
        // has a single text node
        
        if ($child->hasChildNodes() &&
            $child->childNodes->length == 1 &&
            $child->childNodes->item(0)->nodeType == XML_TEXT_NODE
        ) {
            $arr[$name] = $child->childNodes->item(0)->nodeValue;
            continue;
        }
        
        // has a collection
        
        if ($child->hasChildNodes()) {
            if (!isset($arr[$name])) {
                $arr[$name] = array();
            }
            dom_to_array($xmldoc, $child, $arr[$name]);
            continue;
        }
        
        $arr[$name] = $child->nodeValue;
    }
} // dom_to_array
//------------------------------------------------------
function serialize_array(&$arr, &$str)
{
    $xmldoc = new DOMDocument("1.0", "UTF-8");
    
    $root = $xmldoc->createElement("array");
    $root = $xmldoc->appendChild($root);
    
    if (!empty($arr)) {
        array_to_dom($xmldoc, $root, $arr);
    }
    
    $str = $xmldoc->saveXML();
} // serialize_array
//------------------------------------------------------
function unserialize_array(&$arr, &$str)
{
    if (empty($str)) {
        return;
    }
    
    $xmldoc = new DOMDocument();
    
    if (!@$xmldoc->loadXML($str)) {
        return;
    }
    
    dom_to_array($xmldoc, $xmldoc->documentElement, $arr);
} // unserialize_array
//------------------------------------------------------
function trim_walker(&$item_val, $key)
{
    $item_val = trim($item_val);
} // trim_walker
//------------------------------------------------------
function escape_backslash_walker(&$item_val, $key)
{
    $item_val = str_replace("\\", "\\\\", $item_val);
} // escape_backslash_walker
//------------------------------------------------------
// The standard function fputcsv does not escape
// the backslash if it is a normal character
//------------------------------------------------------
function fputcsvx($handle, $fields, $delimiter = ',', $enclosure = '"')
{
    array_walk($fields, 'escape_backslash_walker');
    
    return fputcsv($handle, $fields, $delimiter, $enclosure);
} // fputcsvx
//------------------------------------------------------
function def_js_message($msg)
{
    echo "var msg_$msg = \"" . escape_js(text($msg)) . "\";\n";
} // def_js_message
//------------------------------------------------------
function build_page_info(&$pagination_info, $text)
{
    $bar = escape_html($text);
    
    $bar = str_replace("{current_page}", "<span class='count_number'>" . format_number($pagination_info["page"]) . "</span>", $bar);
    $bar = str_replace("{page_count}", "<span class='count_number'>" . format_number($pagination_info["page_count"]) . "</span>", $bar);
    
    return $bar;
} // build_page_info
//------------------------------------------------------
function build_page_navigator($base_url, &$pagination_info, $all_entry_post = null)
{
    $js_code = "";

    $navigator = "";

    $navigator .= "<div style='position: relative;'>";
    $navigator .= "<div class='page_jumper' style='display: none'>";
    $navigator .= "<form action='$base_url' onsubmit='return goto_page(this)'>";
    $navigator .= "<input type='hidden' name='current_page' value='" . escape_html($pagination_info["page"]) . "'>";
    $navigator .= "<input type='hidden' name='last_page' value='" . escape_html($pagination_info["page_count"]) . "'>";
    $navigator .= "<table>";
    $navigator .= "<tr>";
    $navigator .= "<td><input type='text' name='page' value='" . escape_html($pagination_info["page"]) . "' autocomplete='off'></td>";
    $navigator .= "<td><button class='jump_button'>»</button></td>";
    
    if ($all_entry_post !== null && val_or_empty($pagination_info["mode"]) != "all" && $pagination_info["total_count"] <= 500) {
        if ($pagination_info["page"] == 1) {
            $all_entry_post = "";
        }

        $url = str_replace("tpage=$", "all=1", $base_url);
        if (!empty($all_entry_post)) {
            $url .= "&msg=" . $all_entry_post;
        }
        
        $navigator .= "<td><a href='$url' class='navigation_button all_button' onclick=\"return prepare_for_navigation(this)\">" . escape_html(text("all")) . "</a></td>";
    }

    $navigator .= "</tr>";
    $navigator .= "</table>";
    $navigator .= "</form>";
    $navigator .= "</div>";
    $navigator .= "</div>";

    $navigator .= "<table><tr>";
    
    $onclick = "return prepare_for_navigation(this)";
    $disabled_class = "";
    if ($pagination_info["page"] == 1 && val_or_empty($pagination_info["mode"]) != "all") {
        $onclick = "return false";
        $disabled_class = "disabled_button";
    }
    
    $url = str_replace("$", 1, $base_url);
    $navigator .= "<td><a href='$url' class='navigation_button first_button $disabled_class' onclick=\"$onclick\">&nbsp;</a></td>";
    
    $page = $pagination_info["page"] - 1;
    if ($page < 1) {
        $page = 1;
    }

    if (val_or_empty($pagination_info["mode"]) == "all") {
        $url = str_replace("$", 1, $base_url);
    } else {
        $url = str_replace("$", $page, $base_url);
    }
    $navigator .= "<td><a href='$url' class='navigation_button previous_button $disabled_class' onclick=\"$onclick\">&nbsp;</a></td>";
    $js_code .= "previous_page_url = '$url';\r\n";
    
    $url = str_replace("$", $pagination_info["page"], $base_url);
    if (val_or_empty($pagination_info["mode"]) == "all") {
        $caption = text("all");
        
        $url = str_replace("tpage=$", "all=1", $base_url);
        if (!empty($pagination_info["msg"])) {
            $url .= "&msg=" . $pagination_info["msg"];
        }
    } else {
        $caption = $pagination_info["page"];
        $url = str_replace("$", $pagination_info["page"], $base_url);
    }
    $navigator .= "<td><a href='$url' class='navigation_button current_page_button' onclick=\"return toogle_adjacent_page_jumper(this)\">" . escape_html($caption) . "</a></td>";
    
    $onclick = "return prepare_for_navigation(this)";
    $disabled_class = "";
    if ($pagination_info["page"] == $pagination_info["page_count"] && $pagination_info["mode"] != "all") {
        $onclick = "return false";
        $disabled_class = "disabled_button";
    }
    
    $page = $pagination_info["page"] + 1;
    if ($page > $pagination_info["page_count"]) {
        $page = $pagination_info["page_count"];
    }

    if (val_or_empty($pagination_info["mode"]) == "all") {
        $url = str_replace("$", $pagination_info["page_count"], $base_url);
    } else {
        $url = str_replace("$", $page, $base_url);
    }
    $navigator .= "<td><a href='$url' class='navigation_button next_button $disabled_class' onclick=\"$onclick\">&nbsp;</a></td>";
    $js_code .= "next_page_url = '$url';\r\n";

    $url = str_replace("$", $pagination_info["page_count"], $base_url);
    $navigator .= "<td><a href='$url' class='navigation_button last_button $disabled_class' onclick=\"$onclick\">&nbsp;</a></td>";
    
    if ($all_entry_post !== null && val_or_empty($pagination_info["mode"]) != "all" && $pagination_info["total_count"] <= 500) {
        if ($pagination_info["page"] == 1) {
            $all_entry_post = "";
        }

        $url = str_replace("tpage=$", "all=1", $base_url);
        if (!empty($all_entry_post)) {
            $url .= "&msg=" . $all_entry_post;
        }
        
        $navigator .= "<td><a href='$url' class='navigation_button all_button additional_all_button' onclick=\"return prepare_for_navigation(this)\">" . escape_html(text("all")) . "</a></td>";
    }

    $navigator .= "</tr></table>";
    
    if (!empty($js_code)) {
        $navigator .= "\r\n<script>\r\n";
        $navigator .= $js_code;
        $navigator .= "\r\n</script>\r\n";
    }
    
    return $navigator;
} // build_page_navigator
//------------------------------------------------------
function build_post_page_navigator($base_url, &$pagination_info, $all_entry_post)
{
    $pinned_position_correction = 0;
    
    if ($pagination_info["pinned_message_count"] > 0) {
        $pinned_position_correction = $pagination_info["pinned_message_count"];
    }
    
    $page_count = ceil(($pagination_info["total_count"] - $pinned_position_correction) / $pagination_info["posts_per_page"]);
    if ($page_count < 1) {
        $page_count = 1;
    }

    $current_page_first = ($pagination_info["first_message_position"] / $pagination_info["posts_per_page"]);
    if ($current_page_first < 1) {
        $current_page_first = 1;
    }
    
    $current_page_last = ($pagination_info["last_message_position"] / $pagination_info["posts_per_page"]);
    if ($current_page_last < 1) {
        $current_page_last = 1;
    }
    
    $current_page = ceil($current_page_first);

    $js_code = "";
    
    $total_count = $pagination_info["total_count"];
    if (!empty($pagination_info["ignored_hidden"]) && is_numeric($pagination_info["ignored_count"])) {
        $total_count -= $pagination_info["ignored_count"];
    }
    
    if ($total_count <= $pagination_info["posts_per_page"]) {
        return "";
    }
    
    // if there are pinned messages, the number of posts on the page is $pagination_info["posts_per_page"] + $pagination_info["pinned_message_count"]
    if (($pagination_info["first_page_message"] == $pagination_info["first_topic_message"] ||
            $pagination_info["first_page_message"] == $pagination_info["first_topic_pinned_message"]) &&
        $total_count <= $pagination_info["posts_per_page"] + $pagination_info["pinned_message_count"]
    ) {
        return "";
    }
    
    $navigator = "";

    $navigator .= "<div style='position: relative;'>";
    $navigator .= "<div class='page_jumper' style='display: none'>";

    $url = $base_url;
    $navigator .= "<form action='$base_url' onsubmit='return goto_post_page(this)'>";
    $navigator .= "<input type='hidden' name='current_page' value='" . escape_html($current_page) . "'>";
    $navigator .= "<input type='hidden' name='last_page' value='" . escape_html($page_count) . "'>";
    $navigator .= "<table>";
    $navigator .= "<tr>";
    $navigator .= "<td><input type='text' name='page' value='" . escape_html($current_page) . "' autocomplete='off'></td>";
    $navigator .= "<td><button class='jump_button'>»</button></td>";

    if (($all_entry_post == $pagination_info["first_topic_message"] ||
        $all_entry_post == $pagination_info["first_topic_pinned_message"])
    ) {
        $all_entry_post = "";
    }
    
    if ($pagination_info["mode"] != "all" && $pagination_info["total_count"] <= 500) {
        $url = $base_url . "&all=1";
        if (!empty($all_entry_post)) {
            $url .= "&msg=" . $all_entry_post;
        }
        
        $navigator .= "<td><a href='$url' class='navigation_button all_button' onclick=\"return prepare_post_for_navigation(this)\">" . escape_html(text("all")) . "</a></td>";
    }
    
    $navigator .= "</tr>";
    $navigator .= "</table>";
    $navigator .= "</form>";
    $navigator .= "</div>";
    $navigator .= "</div>";

    $navigator .= "<table><tr>";
    
    $is_first_page = false;
    $onclick = "return prepare_post_for_navigation(this)";
    $disabled_class = "";
    if (($pagination_info["first_page_message"] == $pagination_info["first_topic_message"] ||
            $pagination_info["first_page_message"] == $pagination_info["first_topic_pinned_message"]) &&
        $pagination_info["mode"] != "all" && $pagination_info["mode"] != "download"
    ) {
        $is_first_page = true;
        $onclick = "return false";
        $disabled_class = "disabled_button";
    }
    
    // left arrows
    
    $url = $base_url;
    $navigator .= "<td><a href='$url' class='navigation_button first_button $disabled_class' onclick=\"$onclick\">&nbsp;</a></td>";
    
    if ($is_first_page) {
        $url = $base_url;
    } elseif ($pagination_info["mode"] == "all" || $pagination_info["mode"] == "download") {
        if (empty($pagination_info["startmsg"])) {
            $url = $base_url;
        } else {
            $url = $base_url . "&startmsg=" . $pagination_info["startmsg"] . "&offset=-1";
        }
    } else {
        $url = $base_url . "&startmsg=" . $pagination_info["first_page_message"] . "&offset=-1";
    }
    $navigator .= "<td><a href='$url' class='navigation_button previous_button $disabled_class' onclick=\"$onclick\">&nbsp;</a></td>";
    
    $js_code .= "previous_page_url = '$url';\r\n";
    
    if ($pagination_info["mode"] == "all") {
        $caption = text("all");
        $url = $base_url . "&all=1";
        if (!empty($pagination_info["msg"])) {
            $url .= "&msg=" . $pagination_info["msg"];
        }
    } else {
        $caption = $current_page;
        $url = $base_url . "&startmsg=" . $pagination_info["first_page_message"];
    }
    $navigator .= "<td><a href='$url' class='navigation_button current_page_button' onclick=\"return toogle_adjacent_page_jumper(this)\">" . escape_html($caption) . "</a></td>";

    // right arrows
    
    $is_last_page = false;
    $onclick = "return prepare_post_for_navigation(this)";
    $disabled_class = "";
    
    if ($pagination_info["last_page_message"] == $pagination_info["last_topic_message"] &&
        $pagination_info["mode"] != "all" 
    ) {
        $is_last_page = true;
        $onclick = "return false";
        $disabled_class = "disabled_button";
    }
    
    if ($pagination_info["mode"] == "all" || $is_last_page) {
        $url = $base_url . "&startmsg=last&offset=-1";
    } else {
        $url = $base_url . "&startmsg=" . $pagination_info["last_page_message"] . "&offset=1";
    }
    $navigator .= "<td><a href='$url' class='navigation_button next_button $disabled_class' onclick=\"$onclick\">&nbsp;</a></td>";
    $js_code .= "next_page_url = '$url';\r\n";
    
    if (!empty($pagination_info["page_before_last"])) {
        $url = $base_url . "&startmsg=" . $pagination_info["last_page_message"] . "&offset=1";
    } else {
        $url = $base_url . "&startmsg=last&offset=-1";
    }
    $navigator .= "<td><a href='$url' class='navigation_button last_button $disabled_class' onclick=\"$onclick\">&nbsp;</a></td>";
    
    if ($pagination_info["mode"] != "all" && $pagination_info["total_count"] <= 500) {
        $url = $base_url . "&all=1";
        if (!empty($all_entry_post)) {
            $url .= "&msg=" . $all_entry_post;
        }
        
        $navigator .= "<td><a href='$url' class='navigation_button all_button additional_all_button' onclick=\"return prepare_post_for_navigation(this)\">" . escape_html(text("all")) . "</a></td>";
    }

    $navigator .= "</tr></table>";
    
    if (!empty($js_code)) {
        $navigator .= "\r\n<script>\r\n";
        $navigator .= $js_code;
        $navigator .= "\r\n</script>\r\n";
    }
    
    return $navigator;
} // build_post_page_navigator
//------------------------------------------------------
function build_message_info_bar($base_url, &$pagination_info, $all_entry_post)
{
    $msg_count = $pagination_info["loaded_message_count"];
    
    $pinned_position_correction = 0;
    
    if ($pagination_info["pinned_message_count"] > 0) {
        $pinned_position_correction = $pagination_info["pinned_message_count"];
        
        if ($pagination_info["first_page_message"] == $pagination_info["first_topic_message"] ||
            $pagination_info["first_page_message"] == $pagination_info["first_topic_pinned_message"]
        ) {
            $msg_count += $pinned_position_correction;
        }
    }
    
    $page_count = ceil(($pagination_info["total_count"] - $pinned_position_correction) / $pagination_info["posts_per_page"]);
    if ($page_count < 1) {
        $page_count = 1;
    }

    $current_page_first = ($pagination_info["first_message_position"] / $pagination_info["posts_per_page"]);
    if ($current_page_first < 1) {
        $current_page_first = 1;
    }
    
    $current_page_last = ($pagination_info["last_message_position"] / $pagination_info["posts_per_page"]);
    if ($current_page_last < 1) {
        $current_page_last = 1;
    }
    
    $current_page = ceil($current_page_first);
    
    if ($pagination_info["mode"] == "all") {
        $bar = escape_html(text("MessagesDisplayed") . ", " . text("pages_all"));
    } elseif ($pagination_info["mode"] == "download") {
        $bar = escape_html(text("MessagesDisplayed") . ", " . text("pages_downloaded"));
    } else {
        $bar = escape_html(text("MessagesDisplayed") . ", " . text("pages"));
    }
    
    $bar = str_replace("{msg_count}", "<span class='count_number'>" . format_number($msg_count) . "</span>", $bar);
    $bar = str_replace("{total_msg}", "<span class='count_number'>" . format_number($pagination_info["total_count"]) . "</span>", $bar);
    $bar = str_replace("{current_page}", "<span class='count_number'>" . format_number($current_page) . "</span>", $bar);
    $bar = str_replace("{page_count}", "<span class='count_number'>" . format_number($page_count) . "</span>", $bar);
    
    //return $bar . "(" . $pagination_info["first_message_position"] . "/" . $pagination_info["last_message_position"] . ")";
    return $bar;
} // build_message_info_bar
//------------------------------------------------------
function build_post_pagination($base_url, $tinfo, $class)
{
    $posts_count = $tinfo["post_count"];
    if (!empty($tinfo["has_pinned_post"])) {
        $posts_count -= 1;
    }
    $page_count = ceil($posts_count / $tinfo["posts_per_page"]);
    if ($page_count < 1) {
        $page_count = 1;
    }
    
    $navigator = "";
    
    if ($page_count <= 1) {
        $navigator .= " <span class='post_pagination one_post_pagination'>(";
        
        $url = $base_url;
        $navigator .= "<span class='first_page_indicator'><a rel='nofollow' href='$url' class='$class'>1</a>";
        
        $url = $base_url . "&gotolast=1";
        $navigator .= ", </span><a rel='nofollow' href='$url' title='" . escape_html(text("ToTheLast")) . "' class='$class arrow'>»»</a>";
        $navigator .= ")</span>";
        
        return $navigator;
    } elseif ($page_count == 2) {
        $navigator .= " <span class='post_pagination'>(";
        
        $url = $base_url;
        $navigator .= "<a rel='nofollow' href='$url' class='$class'>1</a>";
        
        $url = $base_url . "&startmsg=last&offset=-1";
        $navigator .= ", <a rel='nofollow' href='$url' class='$class'>2</a>";
        
        if ($tinfo["post_count"] <= 500) {
            $url = $base_url . "&all=1";
            $navigator .= ", <a href='$url' class='$class'>" . escape_html(text("all")) . "</a>";
        }
        
        $url = $base_url . "&gotolast=1";
        $navigator .= ", <a rel='nofollow' href='$url' title='" . escape_html(text("ToTheLast")) . "' class='$class arrow'>»»</a>";
        $navigator .= ")</span>";
        
        return $navigator;
    } elseif ($page_count == 3) {
        $navigator .= " <span class='post_pagination'>(";
        
        $url = $base_url;
        $navigator .= "<a rel='nofollow' href='$url' class='$class'>1</a>";
        
        $url = $base_url . "&startmsg=first&offset=2";
        $navigator .= ", <a rel='nofollow' href='$url' class='$class'>2</a>";
        
        $url = $base_url . "&startmsg=last&offset=-1";
        $navigator .= ", <a rel='nofollow' href='$url' class='$class'>3</a>";
        
        if ($tinfo["post_count"] <= 500) {
            $url = $base_url . "&all=1";
            $navigator .= ", <a href='$url' class='$class'>" . escape_html(text("all")) . "</a>";
        }
        
        $url = $base_url . "&gotolast=1";
        $navigator .= ", <a rel='nofollow' href='$url' title='" . escape_html(text("ToTheLast")) . "' class='$class arrow'>»»</a>";
        $navigator .= ")</span>";
        
        return $navigator;
    }
    
    $navigator .= " <span class='post_pagination'>(";
    
    $url = $base_url;
    $navigator .= "<a rel='nofollow' href='$url' class='$class'>1</a>";
    
    $url = $base_url . "&startmsg=first&offset=2";
    $navigator .= ", <a rel='nofollow' href='$url' class='$class'>2</a>";
    
    if ($page_count == 4) {
        $navigator .= ", ";
    } else {
        $navigator .= " ... ";
    }
    
    $url = $base_url . "&startmsg=last&offset=-2";
    $navigator .= "<a rel='nofollow' href='$url' class='$class'>" . format_number($page_count - 1) . "</a>";
    
    $url = $base_url . "&startmsg=last&offset=-1";
    $navigator .= ", <a rel='nofollow' href='$url' class='$class'>" . format_number($page_count) . "</a>";
    
    if ($tinfo["post_count"] <= 500) {
        $url = $base_url . "&all=1";
        $navigator .= ", <a href='$url' class='$class'>" . escape_html(text("all")) . "</a>";
    }
    
    $url = $base_url . "&gotolast=1";
    $navigator .= ", <a rel='nofollow' href='$url' title='" . escape_html(text("ToTheLast")) . "' class='$class arrow'>»»</a>";
    
    $navigator .= ")</span>";
    
    return $navigator;
} // build_post_pagination
//------------------------------------------------------
function to_url($matches)
{
    return "<a href='" . $matches[0] . "' target='_blank'>" . spec_cut(urldecode($matches[0]), 80) . "</a>";
} // to_url
//------------------------------------------------------
function postprocess_message(&$content, $lang = "", $html = true, $for_email = false)
{
    if (empty($lang)) {
        $lang = current_language();
    }
    
    if (!$for_email) {
        $user_name = val_or_empty($_SESSION["user_name"]);
        
        if ($user_name == "admin") {
            $user_name = text("MasterAdministrator", $lang);
        } elseif (empty($user_name)) {
            $user_name = text("Guest", $lang);
        }
        
        if ($html) {
            $user_name = escape_html($user_name);
        }
        
        $content = str_ireplace("#user_name#", $user_name, $content ?? "");
    }
    
    $content = preg_replace_callback("/{{date: (\d+)}}/", function ($matches) use ($lang) {
        return smart_date(adjust_and_format_timezone($matches[1], text("DateTimeFormat", $lang)));
    }, $content);
    
    // obsolete
    $content = preg_replace_callback("/<span class='qdate'>(\d+)<\/span>/", function ($matches) use ($lang) {
        return "<span class='qdate'>" . smart_date(adjust_and_format_timezone($matches[1], text("DateTimeFormat", $lang))) . "</span>";
    }, $content);
    
    $content = preg_replace_callback("/MSG\\((.*?)\\)/", function ($matches) use ($html, $lang) {
        $params = explode("\t", $matches[1]);
        if (!empty($params) && !empty($params[0])) {
            $params[0] = try_translate($params[0], $lang);
            
            $txt = call_user_func_array("sprintf", $params);
        } else {
            $txt = try_translate($matches[0], $lang);
        }
        
        if ($html) {
            $txt = escape_html($txt);
        }
        
        return $txt;
    }, $content);
    
    $content = preg_replace_callback("/{{guest_ignored:(.+?)(:(.+?))?}}/", function ($matches) {
        global $fmanager;
        
        $ignored_class = "";
        if ($fmanager->is_guest_ignored($matches[1], val_or_empty($matches[3]))) {
            $ignored_class = "ignored_author";
        }
        
        $is_moderator = $fmanager->is_forum_moderator(reqvar("fid")) || $fmanager->is_topic_moderator(reqvar("tid"));

        if (!empty($ignored_class) && !empty($_SESSION["hide_ignored"]) && !$is_moderator) {
            $ignored_class = "strongly_ignored_author";
        }
          
        return $ignored_class;
    }, $content);

    $content = preg_replace_callback("/{{user_ignored:(.+?)}}/", function ($matches) {
        global $fmanager;

        $ignored_class = "";
        if ($fmanager->is_user_ignored($matches[1])) {
            $ignored_class = "ignored_author";
        }
        
        $is_moderator = $fmanager->is_forum_moderator(reqvar("fid")) || $fmanager->is_topic_moderator(reqvar("tid"));
        
        if (!empty($ignored_class) && !empty($_SESSION["hide_ignored"]) && !$is_moderator) {
            $ignored_class = "strongly_ignored_author";
        }
          
        return $ignored_class;
    }, $content);

    $replacements = array();
    
    // obsolete
    $replacements["/\{#link#}/msi"] = text("Link", $lang);
    
    $replacements["/{{admin}}/msi"] = text("MasterAdministrator", $lang);
    $replacements["/{{link}}/msi"] = text("Link", $lang);
    $replacements["/{{ignored}}/msi"] = text("ignored", $lang);
    $replacements["/{{attachment}}/msi"] = text("Attachment", $lang);
    $replacements["/{{picture}}/msi"] = text("Picture", $lang);
    $replacements["/{{animation}}/msi"] = text("Animation", $lang);
    $replacements["/{{table}}/msi"] = text("Table", $lang);
    $replacements["/{{video}}/msi"] = text("Video", $lang);
    $replacements["/{{spoiler}}/msi"] = text("Spoiler", $lang);
    $replacements["/{{gallery}}/msi"] = text("Gallery", $lang);
    $replacements["/{{formula}}/msi"] = text("MathFormula", $lang);
    $replacements["/{{citation}}/msi"] = text("Citation", $lang);
    $replacements["/{{audio}}/msi"] = text("Audio", $lang);
    $replacements["/{{code}}/msi"] = text("Code", $lang);
    $replacements["/{{maps}}/msi"] = text("Maps", $lang);
    
    if ($html) {
        foreach ($replacements as &$replacement) {
            $replacement = escape_html($replacement);
        }
    }
    
    $content = preg_replace(array_keys($replacements), $replacements, $content);
    
    return $content;
} // postprocess_message
//------------------------------------------------------
function check_idn($url)
{
  if (!function_exists("idn_to_utf8")) {
      return $url;
  }
  
  $domain = parse_url($url, PHP_URL_HOST);
  if (empty($domain)) {
      return $url;
  }
  
  $idn = idn_to_utf8($domain);
  if (empty($idn)) {
      return $url;
  }
  
  return str_replace($domain, $idn, $url);
} // check_idn
//------------------------------------------------------
function make_links($str)
{
    $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z0-9\-]{2,}(\/\S*)?/";
    $urls = array();
    $urlsToReplace = array();
    if (preg_match_all($reg_exUrl, $str, $urls)) {
        $numOfMatches = count($urls[0]);
        $numOfUrlsToReplace = 0;
        for ($i = 0; $i < $numOfMatches; $i++) {
            $alreadyAdded = false;
            $numOfUrlsToReplace = count($urlsToReplace);
            for ($j = 0; $j < $numOfUrlsToReplace; $j++) {
                if ($urlsToReplace[$j] == $urls[0][$i]) {
                    $alreadyAdded = true;
                }
            }
            if (!$alreadyAdded) {
                array_push($urlsToReplace, $urls[0][$i]);
            }
        }
        $numOfUrlsToReplace = count($urlsToReplace);
        for ($i = 0; $i < $numOfUrlsToReplace; $i++) {
            $str = str_replace($urlsToReplace[$i], "<a href=\"" . $urlsToReplace[$i] . "\" target='_blank'>" . check_idn(urldecode($urlsToReplace[$i])) . "</a> ", $str);
        }

        return $str;
    } else {
        return $str;
    }
} // make_links
//------------------------------------------------------
function detect_encoding(&$text)
{
    if (function_exists("mb_detect_encoding") &&
        function_exists("iconv") &&
        function_exists("mb_check_encoding") &&
        !mb_check_encoding($text, "UTF-8")) {
        $current_encoding = mb_detect_encoding($text, 'windows-1251,windows-1252');
        
        if (stripos($text, "www.sql.ru") !== false) {
            $current_encoding = "windows-1251";
        }
        
        $text = @iconv($current_encoding, 'UTF-8', $text);
    }
} // detect_encoding
//------------------------------------------------------
function get_allow_edit_period()
{
    if (defined('ALLOW_EDIT_PERIOD') && is_numeric(ALLOW_EDIT_PERIOD)) {
        return 60 * ALLOW_EDIT_PERIOD;
    }
    
    return 60 * 10;
} // get_allow_edit_period
//------------------------------------------------------
function get_allow_moderate_period_days()
{
    if (defined('ALLOW_MODERATE_PERIOD') && is_numeric(ALLOW_MODERATE_PERIOD)) {
        return ALLOW_MODERATE_PERIOD;
    }
    
    return 14;
} // get_allow_moderate_period_days
//------------------------------------------------------
function get_allow_moderate_period()
{
    return 24 * 3600 * get_allow_moderate_period_days();
} // get_allow_moderate_period
//------------------------------------------------------
function detect_bot($user_agent)
{
    if (empty($user_agent)) {
        return null;
    }     
    
    $bot_data = [ "name" => "", "allowed" => 0 ];
    
    // allow TelegramBot, Twitterbot, Slackbot, Mail.RU_Bot, Facebot Twitterbot, SEOkicks, Applebot, SiteCheckerBot to load links
    if (preg_match("/.*(TelegramBot|vkShare|Twitterbot|Slackbot|Mail.RU_Bot|Facebot Twitterbot|SEOkicks|Applebot|SiteCheckerBot).*/i",
        $user_agent, $matches)) {
        $bot_data["name"] = $matches[1] . " (URL parse)";
        $bot_data["allowed"] = 1;
        
        return $bot_data;
    }

    if (preg_match("/.*(AmazonBot).*/i", $user_agent)) {
        $bot_data["name"] = "Amazon Bot";

        return $bot_data;
    }

    // allow WhatsApp to load links
    if (preg_match("/.*(WhatsApp).*/i", $user_agent)) {
        $bot_data["name"] = "WhatsApp Bot" . " (URL parse)";
        $bot_data["allowed"] = 1;

        return $bot_data;
    }

    if (preg_match("/.*(SemrushBot|ImagesiftBot|Dataprovider\.com|intelx\.io_bot|VKRobotRB|AwarioBot|keys-so-bot|Bytespider|SeekportBot|GPTBot|DotBot|DataForSeoBot|HubSpot Crawler|ClaudeBot|Barkrowler|LightspeedSystemsCrawler|MegaIndex|yacybot|Translation-Search-Machine|startmebot|Adsbot|MJ12bot|TestBot|AhrefsBot|BLEXBot|James BOT|GumGum-Bot|linkdexbot|WBSearchBot|Claritybot|msnbot-media|Domain Re-Animator Bot|SiteAnalyzerbot|NetpeakCheckerBot|BananaBot|BLEXBot|Linguee Bot|openstat\\.ru|CCBot|SMTBot|Exabot|BDCbot|Netpeak|statdom.ru\\/Bot|SeznamBot|Wotbox|PiplBot|DnyzBot|LinkedInBot|SafeDNSBot|DeuSu|calculon spider|HybridBot|LinkpadBot|MauiBot|sukibot|techleadzbot|yacybot|tracemyfile|trendictionbot|Cliqzbot).*/i",
        $user_agent, $matches)) {
        $bot_data["name"] = $matches[1];
        
        return $bot_data;
    }
    
    if (preg_match("/.*(FriendlyCrawler\/Nutch|Friendly_Crawler\/Nutch).*/i", $user_agent)) {
        $bot_data["name"] = "FriendlyCrawler Nutch";

        return $bot_data;
    }

    // allow Google Favicon to load links
    if (preg_match("/.*(Google Favicon).*/i", $user_agent)) {
        $bot_data["name"] = "Google Bot" . " (URL parse)";
        $bot_data["allowed"] = 1;

        return $bot_data;
    }

    if (preg_match("/.*(Googlebot|Mediapartners-Google|Google-PageRenderer).*/i", $user_agent)) {
        $bot_data["name"] = "Google Bot";

        return $bot_data;
    }
    
    // allow Favicons, SEOdiver to load links
    if (preg_match("/.*Yandex(Favicons|SEOdiver).*/i", $user_agent)) {
        $bot_data["name"] = "Yandex Bot" . " (URL parse)";
        $bot_data["allowed"] = 1;

        return $bot_data;
    }

    if (preg_match("/.*Yandex(Antivirus|Search|Userproxy|Bot|RCA|Images|Video|Media|Blogs|Addurl|Direct|Metrika|Catalog|News|ImageResizer|MobileBot|AccessibilityBot).*/i", $user_agent)) {
        $bot_data["name"] = "Yandex Bot";

        return $bot_data;
    }
    
    // allow BingPreview to load links
    if (preg_match("/.*(BingPreview).*/i", $user_agent)) {
        $bot_data["name"] = "Bing Bot" . " (URL parse)";
        $bot_data["allowed"] = 1;

        return $bot_data;
    }

    if (preg_match("/.*(bingbot).*/i", $user_agent)) {
        $bot_data["name"] = "Bing Bot";

        return $bot_data;
    }
    
    if (preg_match("/.*statdom.*/i", $user_agent)) {
        $bot_data["name"] = "statdom.ru Bot";

        return $bot_data;
    }
    
    if (preg_match("/.*openstat.*/i", $user_agent)) {
        $bot_data["name"] = "openstat.ru Bot";

        return $bot_data;
    }
    
    if (preg_match("/.*serpstatbot.*/i", $user_agent)) {
        $bot_data["name"] = "serpstatbot Bot";

        return $bot_data;
    }
    
    // allow it to load links facebookexternalhit
    if (preg_match("/.*(facebookexternalhit).*/i", $user_agent)) {
        $bot_data["name"] = "Facebook Bot" . " (URL parse)";
        $bot_data["allowed"] = 1;

        return $bot_data;
    }
    
    if (preg_match("/.*(petalbot).*/i", $user_agent)) {
        $bot_data["name"] = "PetalBot Bot";

        return $bot_data;
    }
    
    if (preg_match("/.*(archive.org_bot|ia_archiver|Wayback Machine Live Record).*/i", $user_agent)) {
        $bot_data["name"] = "Archive.org Bot";

        return $bot_data;
    }
    
    $ip = val_or_empty($_SERVER["REMOTE_ADDR"]);
    if (in_array($ip, array(
        "207.241.226.233",
        "207.241.225.244",
        "207.241.226.230",
        "207.241.226.219",
        "207.241.226.218",
        "207.241.226.234",
        "207.241.226.247",
        "207.241.225.236"
    ))) {
        $bot_data["name"] = "Archive.org Bot";

        return $bot_data;
    }
    
    return null;
} // detect_bot
//------------------------------------------------------
function detect_browser($user_agent)
{
    if (empty($user_agent)) {
        return null;
    }     
   
    $browser_data = get_browser($user_agent, true);
    if (empty($browser_data)) {
        return null;
    }
    
    if (val_or_empty($browser_data["browser_type"]) != "Browser") {
        return null;
    }
    
    if (val_or_empty($browser_data["browser"]) == "Android") {
        return null;
    }
    
    if ($browser_data["browser"] == "Safari" && preg_match("/.*(iPhone).*/i", $user_agent)) {
        $browser_data["browser"] = "Safari Mobile";
    }
    
    $os = $browser_data["platform_description"];
    if ($os == "iPod, iPhone & iPad") {
        $os = "iOS";
    }
    
    if ($os == "Windows" && preg_match("/.*(Windows NT 11).*/i", $user_agent)) {
        $os = "Windows 11";
    }

    if (strpos($os, "Windows Phone OS") !== false) {
        $os = "Windows Phone OS";
    }
    
    if (strpos($os, "Linux") !== false) {
        $os = "Linux";
    }
    
    if (strpos($os, "Mac OS") !== false) {
        $os = "Mac OS";
    }
    
    if (strpos($os, "macOS") !== false) {
        $os = "Mac OS";
    }
    
    if (strpos($os, "unknown") !== false) {
        $os = "";
    }
    
    return array("browser" => $browser_data["browser"], "os" => $os);
} // detect_browser
//------------------------------------------------------
function detect_device($HTTP_USER_AGENT)
{
    if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',
            $HTTP_USER_AGENT) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',
            substr($HTTP_USER_AGENT, 0, 4))) {
        return "smartphone";
    }
    
    if (stripos($HTTP_USER_AGENT, "Android") !== false) {
        return "tablet";
    } // contains Android but no word "mobile". If "mobile" is in the string, the condition above returns 1.
    if (stripos($HTTP_USER_AGENT, "iPad") !== false) {
        return "tablet";
    }
    
    if (stripos($HTTP_USER_AGENT, "Windows Phone") !== false) {
        return "smartphone";
    }
    if (stripos($HTTP_USER_AGENT, "Android") !== false) {
        return "smartphone";
    }
    if (stripos($HTTP_USER_AGENT, "iPhone") !== false) {
        return "smartphone";
    }
    
    return "desktop";
} // detect_device
//------------------------------------------------------
function process_message_callback($matches)
{
    $params = explode("\t", $matches[1]);
    if (!empty($params) && !empty($params[0])) {
        $params[0] = try_translate($params[0]);
        
        $txt = call_user_func_array("sprintf", $params);
    } else {
        $txt = $matches[0];
    }
    
    return $txt;
} // process_message_callback
//------------------------------------------------------
function convert_amr_to_mp3($in, $out)
{
    $result = -1;
    //@system("sox $in $out", $result);
    @system("ffmpeg -y -i $in -ab 32k $out", $result);
    
    if (!file_exists($out)) {
        return false;
    }
    
    return $result == 0;
} // convert_amr_to_mp3
//------------------------------------------------------
function convert_aac_to_mp3($in, $out)
{
    $result = -1;
    @system("ffmpeg -y -i $in -ab 64k $out", $result);
    
    
    if (!file_exists($out)) {
        return false;
    }
    
    return $result == 0;
} // convert_aac_to_mp3
//------------------------------------------------------
function convert_m4a_to_mp3($in, $out)
{
    $result = -1;
    @system("ffmpeg -y -i $in -codec:a libmp3lame -qscale:a 5 $out", $result);
    
    if (!file_exists($out)) {
        return false;
    }
    
    return $result == 0;
} // convert_m4a_to_mp3
//------------------------------------------------------
function compress_png($in, $out, $quality = "60-90")
{
    $result = -1;
    @system("pngquant --force --quality=$quality --output=$out $in", $result);
    
    if (!file_exists($out)) {
        return false;
    }
    
    return $result == 0;
} // compress_png
//------------------------------------------------------
function convert_png_to_jpg($in, $out, $quality = "90")
{
    if (file_exists($out)) {
        @unlink($out);
    }
    
    $result = -1;
    @system("convert $in -quality $quality $out", $result);
    
    if (!file_exists($out)) {
        return false;
    }
    
    return $result == 0;
} // convert_png_to_jpg
//------------------------------------------------------
function check_email_domain_service($domain)
{
    if (!defined('BLOCK_DISPOSABLE_EMAIL_KEY')) {
        return true;
    }
    
    try {
        $url = 'http://check.block-disposable-email.com/easyapi/json/' . BLOCK_DISPOSABLE_EMAIL_KEY . '/' . $domain;
        $client = new Zend_Http_Client($url, array(
            'maxredirects' => 5,
            'timeout' => 50
        ));
        
        $request = $client->request('GET');
        $response = $request->getBody();
        
        $json = json_decode($response, true);
        if (!$json) {
            return true;
        }
        
        if (val_or_empty($json["domain_status"]) == "block") {
            return false;
        }
    } catch (Exception $ex) {
        return true;
    }
    
    return true;
} // check_email_domain_service
//------------------------------------------------------
function get_hash($str)
{
    $hash = 0;
    $ln = strlen($str);
    if ($ln == 0) {
        return $hash;
    }
    
    for ($i = 0; $i < $ln; $i++) {
        $hash += ord($str[$i]);
    }
    
    $hash = (string)$hash;
    
    $prepad_ln = 12 - strlen($hash);
    for ($i = 0; $i < $prepad_ln; $i++) {
        $hash = '0' . $hash;
    }
    
    return $hash;
} // get_hash
//------------------------------------------------------
function start_action_time_measure()
{
    global $ajax_processing;
    
    if ($ajax_processing) {
        return;
    }
    
    $_SESSION["action_start_time"] = microtime(true);
} // start_action_time_measure
//------------------------------------------------------
function start_redirection_time_measure()
{
    global $ajax_processing;
    
    if ($ajax_processing) {
        return;
    }
    
    $_SESSION["redirection_start_time"] = microtime(true);
} // start_redirection_time_measure
//------------------------------------------------------
function measure_action_time($action)
{
    global $ajax_processing;
    
    if ($ajax_processing) {
        return;
    }
    
    $_SESSION["execution_profiles"][] = array("action" => $action, "time" => round(1000 * (microtime(true) - $_SESSION["action_start_time"])));
} // start_time_measure
//------------------------------------------------------
function get_random_special_mode_picture($mode)
{
    $dir = APPLICATION_ROOT . "/user_data/$mode/";
    $files = scandir($dir);
    
    $pictures = array();
    
    foreach ($files as $file) {
        if ($file == "." || $file == ".." || is_dir($dir . $file)) {
            continue;
        }
        
        if (!preg_match("/.+\.(jpg|jpeg|gif|png|webp)$/i", $file)) {
            continue;
        }
        
        $pictures[] = "user_data/$mode/$file";
    }
    
    if (empty($pictures)) {
        return "";
    }
    
    return $pictures[array_rand($pictures)];
} // get_random_special_mode_picture
//------------------------------------------------------
function get_cookie($name)
{
    //    return val_or_empty($_COOKIE[$name]);
    // temporary fix
    return str_replace("+", " ", val_or_empty($_COOKIE[$name]));
} // get_cookie
//------------------------------------------------------
function set_cookie($name, $value = "", $expires = 0)
{
    if (empty($name)) {
        return false;
    }
    
    $params = array("httponly" => "true", "samesite" => "lax", "path" => System::getSessionCookiePath());
    
    if (version_compare(phpversion(), "7.3") < 0) {
        $path = "";
        if (!empty($params["path"])) {
            $path = $params["path"];
        }
        
        if (!empty($params["samesite"])) {
            $path = rtrim($path, "/");
            
            $path .= "/; samesite=" . $params["samesite"];
        }
        
        $_COOKIE[$name] = $value;
        return setcookie($name, $value, $expires, $path);
    }
    
    $params["expires"] = $expires;
    
    // temporary fix
    /*
    $params2 = $params;
    $params2["path"] = "/forum/";
    setcookie($name, "", $params2);
    $params2["path"] = "/";
    setcookie($name, "", $params2);
     */
    
    $_COOKIE[$name] = $value;
    return setcookie($name, $value, $params);
} // set_cookie
//------------------------------------------------------
function switcher_ru($value)
{
    $converter = array(
        'f' => 'а',
        ',' => 'б',
        'd' => 'в',
        'u' => 'г',
        'l' => 'д',
        't' => 'е',
        '`' => 'ё',
        ';' => 'ж',
        'p' => 'з',
        'b' => 'и',
        'q' => 'й',
        'r' => 'к',
        'k' => 'л',
        'v' => 'м',
        'y' => 'н',
        'j' => 'о',
        'g' => 'п',
        'h' => 'р',
        'c' => 'с',
        'n' => 'т',
        'e' => 'у',
        'a' => 'ф',
        '[' => 'х',
        'w' => 'ц',
        'x' => 'ч',
        'i' => 'ш',
        'o' => 'щ',
        'm' => 'ь',
        's' => 'ы',
        ']' => 'ъ',
        "'" => "э",
        '.' => 'ю',
        'z' => 'я',
        
        'F' => 'А',
        '<' => 'Б',
        'D' => 'В',
        'U' => 'Г',
        'L' => 'Д',
        'T' => 'Е',
        '~' => 'Ё',
        ':' => 'Ж',
        'P' => 'З',
        'B' => 'И',
        'Q' => 'Й',
        'R' => 'К',
        'K' => 'Л',
        'V' => 'М',
        'Y' => 'Н',
        'J' => 'О',
        'G' => 'П',
        'H' => 'Р',
        'C' => 'С',
        'N' => 'Т',
        'E' => 'У',
        'A' => 'Ф',
        '{' => 'Х',
        'W' => 'Ц',
        'X' => 'Ч',
        'I' => 'Ш',
        'O' => 'Щ',
        'M' => 'Ь',
        'S' => 'Ы',
        '}' => 'Ъ',
        '"' => 'Э',
        '>' => 'Ю',
        'Z' => 'Я',
        
        '@' => '"',
        '#' => '№',
        '$' => ';',
        '^' => ':',
        '&' => '?',
        '/' => '.',
        '?' => ',',
    );
    
    $value = strtr($value, $converter);
    return $value;
} // switcher_ru
//------------------------------------------------------
function switcher_en($value)
{
    $converter = array(
        'а' => 'f',
        'б' => ',',
        'в' => 'd',
        'г' => 'u',
        'д' => 'l',
        'е' => 't',
        'ё' => '`',
        'ж' => ';',
        'з' => 'p',
        'и' => 'b',
        'й' => 'q',
        'к' => 'r',
        'л' => 'k',
        'м' => 'v',
        'н' => 'y',
        'о' => 'j',
        'п' => 'g',
        'р' => 'h',
        'с' => 'c',
        'т' => 'n',
        'у' => 'e',
        'ф' => 'a',
        'х' => '[',
        'ц' => 'w',
        'ч' => 'x',
        'ш' => 'i',
        'щ' => 'o',
        'ь' => 'm',
        'ы' => 's',
        'ъ' => ']',
        'э' => "'",
        'ю' => '.',
        'я' => 'z',
        
        'А' => 'F',
        'Б' => '<',
        'В' => 'D',
        'Г' => 'U',
        'Д' => 'L',
        'Е' => 'T',
        'Ё' => '~',
        'Ж' => ':',
        'З' => 'P',
        'И' => 'B',
        'Й' => 'Q',
        'К' => 'R',
        'Л' => 'K',
        'М' => 'V',
        'Н' => 'Y',
        'О' => 'J',
        'П' => 'G',
        'Р' => 'H',
        'С' => 'C',
        'Т' => 'N',
        'У' => 'E',
        'Ф' => 'A',
        'Х' => '{',
        'Ц' => 'W',
        'Ч' => 'X',
        'Ш' => 'I',
        'Щ' => 'O',
        'Ь' => 'M',
        'Ы' => 'S',
        'Ъ' => '}',
        'Э' => '"',
        'Ю' => '>',
        'Я' => 'Z',
        
        '"' => '@',
        '№' => '#',
        ';' => '$',
        ':' => '^',
        '?' => '&',
        '.' => '/',
        ',' => '?',
    );
    
    $value = strtr($value, $converter);
    return $value;
} // switcher_en
//------------------------------------------------------
function translit($value)
{
    $converter = array(
        'а' => 'a',
        'б' => 'b',
        'в' => 'v',
        'г' => 'g',
        'д' => 'd',
        'е' => 'e',
        'ё' => 'e',
        'ж' => 'zh',
        'з' => 'z',
        'и' => 'i',
        'й' => 'y',
        'к' => 'k',
        'л' => 'l',
        'м' => 'm',
        'н' => 'n',
        'о' => 'o',
        'п' => 'p',
        'р' => 'r',
        'с' => 's',
        'т' => 't',
        'у' => 'u',
        'ф' => 'f',
        'х' => 'h',
        'ц' => 'c',
        'ч' => 'ch',
        'ш' => 'sh',
        'щ' => 'sch',
        'ь' => '',
        'ы' => 'y',
        'ъ' => '\'',
        'э' => 'e',
        'ю' => 'yu',
        'я' => 'ya',
        
        'А' => 'A',
        'Б' => 'B',
        'В' => 'V',
        'Г' => 'G',
        'Д' => 'D',
        'Е' => 'E',
        'Ё' => 'E',
        'Ж' => 'Zh',
        'З' => 'Z',
        'И' => 'I',
        'Й' => 'Y',
        'К' => 'K',
        'Л' => 'L',
        'М' => 'M',
        'Н' => 'N',
        'О' => 'O',
        'П' => 'P',
        'Р' => 'R',
        'С' => 'S',
        'Т' => 'T',
        'У' => 'U',
        'Ф' => 'F',
        'Х' => 'H',
        'Ц' => 'C',
        'Ч' => 'Ch',
        'Ш' => 'Sh',
        'Щ' => 'Sch',
        'Ь' => '',
        'Ы' => 'Y',
        'Ъ' => '\'',
        'Э' => 'E',
        'Ю' => 'Yu',
        'Я' => 'Ya',
    );
    
    $value = strtr($value, $converter);
    return $value;
}

//------------------------------------------------------
function untranslit($value)
{
    $converter = array(
        'Sch' => 'Щ',
        'Yuy' => 'Юй',
        'Yay' => 'Яй',
        'Sh' => 'Ш',
        'Ch' => 'Ч',
        'Zh' => 'Ж',
        'Yu' => 'Ю',
        'Ya' => 'Я',
        'Ъ' => '\'',
        'sch' => 'щ',
        'yuy' => 'юй',
        'yay' => 'яй',
        'sh' => 'ш',
        'ch' => 'ч',
        'zh' => 'ж',
        'yu' => 'ю',
        'ya' => 'я',
        'ъ' => '\'',
        
        'Iy' => 'Ий',
        'Uy' => 'Уй',
        'Ay' => 'Ай',
        'Oy' => 'Ой',
        'Ey' => 'Ей',
        'iy' => 'ий',
        'uy' => 'уй',
        'ay' => 'ай',
        'oy' => 'ой',
        'ey' => 'ей',
        
        'a' => 'а',
        'b' => 'б',
        'v' => 'в',
        'g' => 'г',
        'd' => 'д',
        'e' => 'е',
        'z' => 'з',
        'i' => 'и',
        'y' => 'й',
        'k' => 'к',
        'l' => 'л',
        'm' => 'м',
        'n' => 'н',
        'o' => 'о',
        'p' => 'п',
        'r' => 'р',
        's' => 'с',
        't' => 'т',
        'u' => 'у',
        'f' => 'ф',
        'h' => 'х',
        'c' => 'ц',
        'y' => 'ы',
        'w' => 'в',
        
        
        'A' => 'А',
        'B' => 'Б',
        'V' => 'В',
        'G' => 'Г',
        'D' => 'Д',
        'E' => 'Е',
        'Z' => 'З',
        'I' => 'И',
        'Y' => 'Й',
        'K' => 'К',
        'L' => 'Л',
        'M' => 'М',
        'N' => 'Н',
        'O' => 'О',
        'P' => 'П',
        'R' => 'Р',
        'S' => 'С',
        'T' => 'Т',
        'U' => 'У',
        'F' => 'Ф',
        'H' => 'Х',
        'C' => 'Ц',
        'Y' => 'Ы',
        'W' => 'В'
    
    );
    
    $value = str_replace(array_keys($converter), $converter, $value);
    return $value;
}

//------------------------------------------------------
function build_trendline(&$in, &$out)
{
    $range_length = 28;
    $counter = 0;
    foreach ($in as $dt => $val) {
        $length = $range_length;
        
        if ($counter <= $length) {
            $position = $counter;
            if ($position > $length / 2) {
                $position = $counter - $length / 2;
            }
        } else {
            $position = $counter - $length;
        }
        
        $length = $range_length;
        $position = $counter - $length;
        if ($position < 0) {
            if ($counter > $length / 2) {
                $position = $counter - $length / 2;
            } else {
                $position = 0;
                $length = $range_length + $counter;
            }
        }
        
        $position = $counter - $range_length / 2;
        if ($position < 0) {
            $position = 0;
            $length = $range_length - $counter;
        }
        
        
        $avg_range = array_slice($in, $position, $length);
        $sum = array_sum($avg_range);
        $cnt = count($avg_range);
        
        if ($cnt == 0) {
            $out[$dt] = 0;
        } else {
            $out[$dt] = round($sum / $cnt);
        }
        
        $counter++;
    }
} // build_trendline
//------------------------------------------------------
?>