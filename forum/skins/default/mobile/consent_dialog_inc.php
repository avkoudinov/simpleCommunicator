<div id="consent_dialog_content" class="consent_dialog_content">

<?php 
if(file_exists($view_path . "lang/" . current_language() . "/data_consent.html")) 
{
  @include $view_path . "lang/" . current_language() . "/data_consent.html";
}
?>

</div>

