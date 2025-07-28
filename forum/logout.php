<?php
//------------------------------------------------------------------
session_set_cookie_params(0, str_replace(basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
if (!$fmanager->is_logged_in()) {
    // ok
} elseif ($fmanager->check_hash()) {
    $fmanager->logout(!reqvar_empty("all_sessions"));
} else {
    MessageHandler::setError(text("ErrWrongHashCode"));
}
//------------------------------------------------------------------
header("location: " . $target_url);
//------------------------------------------------------------------
?>