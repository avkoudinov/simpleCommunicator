<?php
//-----------------------------------------------------------------------
session_set_cookie_params(0, str_replace("ajax/" . basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "../include/session_start_readonly_inc.php";

$ajax_processing = true;
require_once "../include/general_inc.php";
//-----------------------------------------------------------------------
if(detect_bot(val_or_empty($_SERVER["HTTP_USER_AGENT"])))
{
    exit;
}
//------------------------------------------------------------------
if (!$fmanager->check_hash()) {
    exit;
}

$response = array();
$response['success'] = false;

if (!$fmanager->is_logged_in()) {
    MessageHandler::setError(text("ErrActionNotAllowed"));
} elseif ($fmanager->is_master_admin()) {
    MessageHandler::setWarning(text("MsgMasterAdminWarning"));
} else {
    $response['success'] = $fmanager->load_attachments($response, reqvar("last_att_post_id"), reqvar("last_att_id"));
}

//-----------------------------------------------------------------------
System::sendJSON($response);
//-----------------------------------------------------------------------
require_once "../include/final_inc.php";
//-----------------------------------------------------------------------
?>
