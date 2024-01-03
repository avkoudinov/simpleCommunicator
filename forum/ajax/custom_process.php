<?php
//-----------------------------------------------------------------------
session_set_cookie_params(0, str_replace("ajax/" . basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "../include/session_start_inc.php";

$ajax_processing = true;
require_once "../include/general_inc.php";
//-----------------------------------------------------------------------
if (detect_bot(val_or_empty($_SERVER["HTTP_USER_AGENT"])) != "") {
    exit;
}
//-----------------------------------------------------------------------
$response = array();
$response['success'] = false;
$show_messages = true;
//-----------------------------------------------------------------------
if (!empty($maintenance_until) && empty($_SESSION["admdebug"])) {
    MessageHandler::addMessagesToResponse($response);
    System::sendJSON($response);
    exit;
} //---------------------------------------------------------------------
elseif (!reqvar_empty("user_logged") && !$fmanager->is_logged_in()) {
    $report_id = time() . "-" . rand(1000, 9999);
    if (!empty($_GET) || !empty($_POST)) {
        dump_request($report_id);
        $response['send_empty_hash_report'] = $report_id;
    }

    MessageHandler::setError(text("ErrSessionExpired"));
    
    MessageHandler::addMessagesToResponse($response);
    System::sendJSON($response);
    exit;
} //---------------------------------------------------------------------
elseif (!$fmanager->check_hash()) {
    $report_id = time() . "-" . rand(1000, 9999);
    if (!empty($_GET) || !empty($_POST)) {
        dump_request($report_id);
        $response['send_empty_hash_report'] = $report_id;
    }
    
    MessageHandler::setError(text("ErrWrongHashCode"));
    
    MessageHandler::addMessagesToResponse($response);
    System::sendJSON($response);
    exit;
} //---------------------------------------------------------------------
elseif (!reqvar_empty("selfban")) {
    $response['success'] = $fmanager->selfban();
} //---------------------------------------------------------------------

//-----------------------------------------------------------------------
if (empty($response['success']) && !MessageHandler::errorsExist() && !MessageHandler::warningsExist())
{
    MessageHandler::setError(text("ErrNoValidCommand"));
}

if ($show_messages) {
    MessageHandler::addMessagesToResponse($response);
}
System::sendJSON($response);
//-----------------------------------------------------------------------
require_once "../include/final_inc.php";
//-----------------------------------------------------------------------
?>