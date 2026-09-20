<div id="consent_dialog" class="consent_dialog">

<?php 
if(file_exists($view_path . "lang/" . current_language() . "/data_consent.html")) 
{
  @include $view_path . "lang/" . current_language() . "/data_consent.html";
}
?>

</div>

