<?php
// These parameters give the possibility to warn
// visitors about regular backups.
// If specified, a warning dialog with the sepcified
// start and end time will be shown once per session.
//
// $backup_days - array of the week days, when the backups 
// are performed.
//
// $backup_start - the start time in hours and minutes (01:00)
//
// $backup_end - the end time in hours and minutes (07:00)

$backup_days = array();
// Format: 01:00
$backup_start = "";
// Format: 07:00
$backup_end = "";

// These parameters give the possibility to warn
// visitors about planned maintenance.
// If specified, a warning dialog with the sepcified
// start and end time will be shown once per session.

// Format: 2017-07-30 15:46
$maintenance_start = "";
$maintenance_end = "";

// Setting this parameter switches the forum into
// maintenance modus with the corresponding text
// that maintenance will be until the specified time.

// Format: 2017-07-30 15:46
$maintenance_until = "";

// Detailed comment which maintenance jobs are performed
// one for all langauges or dedicated.
$maintenance_comment = "";
$maintenance_comment_lang["ru"] = "";
$maintenance_comment_lang["ua"] = "";
$maintenance_comment_lang["en"] = "";
$maintenance_comment_lang["de"] = "";

// If a password is set, the admin will be able to 
// view and test the forum in the normal modus,
// whereas for all other users, the maintenance
// modus will be still active. This function is useful 
// for checking the forum when the maintenance is
// complete and before the maintenance modus is turned off.
//
// Example: forums.php?admdebug=password
$adm_debug_password = "";
?>