<?php
spl_autoload_register(function ($class_name) {
  global $SUPPORTED_DATABASES;

  $file = APPLICATION_ROOT . "include/_generic/" . $class_name . ".class.php";
  if(file_exists($file) && @include_once($file)) return;

  foreach($SUPPORTED_DATABASES as $dbkey => $dbname)
  {
    $file = APPLICATION_ROOT . "include/" . $dbkey . "/" . $class_name . ".class.php";
    if(file_exists($file) && @include_once($file)) return;
  }
}); // __autoload
?>