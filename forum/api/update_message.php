<?php
$ajax_processing = true;
require_once "../include/general_inc.php";

$api = new ForumAPIHandler();
$api->handleRequest(basename(__FILE__, ".php"));
