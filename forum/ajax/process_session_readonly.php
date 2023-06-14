<?php
//-----------------------------------------------------------------------
session_set_cookie_params(0, str_replace("ajax/" . basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "../include/session_start_readonly_inc.php";

$ajax_processing = true;
require_once "../include/general_inc.php";
//-----------------------------------------------------------------------
$response = array();
$response['success'] = false;
$show_messages = true;
//-----------------------------------------------------------------------
if (!empty($maintenance_until) && empty($_SESSION["admdebug"])) {
    MessageHandler::addMessagesToResponse($response);
    System::sendJSON($response);
    exit;
}
//-----------------------------------------------------------------------
elseif (!reqvar_empty("user_logged") && !$fmanager->is_logged_in()) {
    MessageHandler::setError(text("ErrSessionExpired"));
    
    MessageHandler::addMessagesToResponse($response);
    System::sendJSON($response);
    exit;
} //---------------------------------------------------------------------
elseif (!$fmanager->check_hash()) {
    MessageHandler::setError(text("ErrWrongHashCode"));

    MessageHandler::addMessagesToResponse($response);
    System::sendJSON($response);
    exit;
}
//-----------------------------------------------------------------------

//-----------------------------------------------------------------------
elseif (!reqvar_empty("auto_save")) {
    $response['success'] = $fmanager->auto_save_message(reqvar("topic"), reqvar("message"));
}
//-----------------------------------------------------------------------

//-----------------------------------------------------------------------
if ($show_messages) {
    MessageHandler::addMessagesToResponse($response);
}
System::sendJSON($response);
//-----------------------------------------------------------------------
require_once "../include/final_inc.php";
//-----------------------------------------------------------------------
?>