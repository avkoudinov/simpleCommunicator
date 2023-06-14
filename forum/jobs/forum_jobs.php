<?php
//-----------------------------------------------------------------------
require_once "../include/session_start_inc.php";
require_once "../include/general_inc.php";
//-----------------------------------------------------------------------
$fmanager->execute_forum_jobs(false);
//-----------------------------------------------------------------------
if (MessageHandler::errorsExist()) {
    echo "\n";
    echo MessageHandler::getErrors();
}

if (MessageHandler::warningsExist()) {
    echo "\n";
    echo MessageHandler::getWarnings();
}
?>