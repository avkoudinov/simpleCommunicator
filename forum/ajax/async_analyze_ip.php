<?php
//-----------------------------------------------------------------------
//ignore_user_abort(true);
//set_time_limit(60);

session_set_cookie_params(0, str_replace("ajax/" . basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "../include/session_start_readonly_inc.php";

$ajax_processing = true;
require_once "../include/general_inc.php";

$geoip_manager = System::getClassInstance("GeoIPManager");
function finish_response()
{
    if (function_exists("fastcgi_finish_request")) {
        echo "ok";
        fastcgi_finish_request();
        return;
    }

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    ob_start();
    echo "ok";
    header("Connection: close");
    header("Content-Length: " . ob_get_length());
    ob_end_flush();
    flush();
}

$ip    = $_POST["ip"] ?? "";
$token = $_POST["token"] ?? "";
$bot = $_POST["bot"] ?? "";
$dtnow = $_POST["dtnow"] ?? "";

if (!defined("SALT_KEY") ||
    !hash_equals(hash_hmac("sha256", $ip, SALT_KEY), $token)
) {
    http_response_code(403);
    exit;
}

finish_response();

$geoip_manager->analyze_ip($ip, $bot, $dtnow);
?>