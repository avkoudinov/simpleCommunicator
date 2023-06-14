<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
$fmanager->activate_account();
//------------------------------------------------------------------
header("location: profile.php");
//------------------------------------------------------------------
?>