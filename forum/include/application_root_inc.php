<?php
// defining the application root
$aroot = __FILE__;
$basename = basename(__FILE__);
$aroot = str_replace("\\", "/", $aroot);
$aroot = str_replace("include/$basename", "", $aroot);

define('APPLICATION_ROOT', $aroot);
?>