<?php
if (empty($ajax_processing)) {
    $exec_time = round(1000 * (microtime(true) - $_SESSION["trace_time_start"]));
    
    if (!empty($new_check_time)) {
        $_SESSION["execution_profiles"][] = array("action" => "check new", "time" => $new_check_time);
    }
    
    $execution_profiles = array();
    if (!empty($_SESSION["execution_profiles"])) {
        $execution_profiles = $_SESSION["execution_profiles"];
    }
    
    unset($_SESSION["trace_time_start"]);
    unset($_SESSION["execution_profiles"]);
    //debug_message("unsetting trace_time_start in final");
    
    if (!empty($_SESSION["trace_sql"])) {
        $_SESSION["trace_sql_log"] .= "Total execution time: " . $exec_time . "ms" . "\n";
        $_SESSION["trace_sql_log"] .= "----------------------------------------------------------------------" . "\n";
        
        trace_message_to_file($_SESSION["trace_sql_log"], "trace_sql.log");
        
        unset($_SESSION["trace_sql"]);
        unset($_SESSION["trace_sql_log"]);
    }
    
    $MSG_INFO_MESSAGE = MessageHandler::getInfos();
    $MSG_WARNING_MESSAGE = MessageHandler::getWarnings();
    $MSG_ERROR_MESSAGE = MessageHandler::getErrors();
    $MSG_DEBUG_MESSAGE = MessageHandler::getDebugMessages();
    
    $MSG_PROG_WARNING = "";
    if (defined('SHOW_PROGRAM_WARNINGS') && SHOW_PROGRAM_WARNINGS) {
        $MSG_PROG_WARNING = MessageHandler::getProgWarnings();
    }
    
    $MSG_AUTO_HIDE_INFO = MessageHandler::autoHideInfo();
    $MSG_ACTIVE_TAB = MessageHandler::getActiveTab();
    $MSG_FOCUS_ELEMENT = MessageHandler::getFocusElement();
    $MSG_ERROR_ELEMENT = MessageHandler::getErrorElement();
}
?>

