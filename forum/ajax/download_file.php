<?php
//-----------------------------------------------------------------------
@error_reporting(0);
//-----------------------------------------------------------------------
session_set_cookie_params(0, str_replace("ajax/" . basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "../include/session_start_readonly_inc.php";

$ajax_processing = true;
require_once "../include/general_inc.php";
//-----------------------------------------------------------------------

$success = false;
$error = text("ErrUnknownDownloadRequest");
$file_path = "";
$out_file_name = "";

do
{
  if(reqvar_empty("file"))
  {
    $error = text("ErrDownloadFileNotSpecified");
    break;
  }
  elseif(reqvar("file") == "profile_export")
  {
    $appendix = get_cookie(session_name());
    if(empty($appendix))
    {
      $appendix = time() . "_" . rand(10000, 99999);
    }
    
    $file_path = APPLICATION_ROOT . "tmp/profile_export_" . $appendix . ".xml";
    if(!file_exists($file_path))
    {
      $error = text("ErrDownloadFileDoesNotExists");
      break;
    }
    
    $success = true;
    $out_file_name = "profile_export.xml";
  }  
}
while(false);

if($success)
{
  $handle = fopen($file_path, "rb");
  if($handle)
  {
    header("Content-type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"$out_file_name\"");

    while(!feof($handle))
    {
      echo fread($handle, 8192);
    }

    fclose($handle);
  }
}
else
{
header("Content-type: text/html; charset=utf-8");
// it is important that there is no space before DOCTYPE
?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Download Error</title>
</head>
<body>
<script type="text/javascript">
if(typeof top.show_download_error == 'function')
{
  top.show_download_error("<?php echo_js($error); ?>");
}
else
{
  alert("<?php echo_js($error); ?>");
}
</script>
</body>
</html>
<?php  
}
?>
