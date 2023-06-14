<?php
//-----------------------------------------------------------------------
$ajax_processing = true;
require_once "../include/general_inc.php";
//-----------------------------------------------------------------------

$msg = "Hash Problem Report From Client\n\n";
$msg .= "Report ID: " . reqvar("report_id") . "\n";
$msg .= "Time: " . date("d.m.Y H:i:s") . "\n";
$msg .= "IP: " . val_or_empty($_SERVER["REMOTE_ADDR"]) . "\n";
$msg .= "User agent: " . val_or_empty($_SERVER["HTTP_USER_AGENT"]) . "\n";

$msg .= "Current client hash: " . reqvar("hash") . "\n";

$msg .= "\n";

$msg .= print_r($_GET, true) . "\n";

$msg .= "-----------------------\n";

trace_message_to_file($msg, "session-expiration.log");
?>