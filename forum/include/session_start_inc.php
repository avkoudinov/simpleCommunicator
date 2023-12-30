<?php
session_set_cookie_params(0);
session_start();

function save_session()
{
}

if (empty($_SESSION["session_start_time"])) {
    $_SESSION["session_start_time"] = time();
    $_SESSION["session_start_request_uri"] = "";
    if (!empty($_SERVER["REQUEST_URI"])) {
        $_SESSION["session_start_request_uri"] = $_SERVER["REQUEST_URI"];
    }
}
?>