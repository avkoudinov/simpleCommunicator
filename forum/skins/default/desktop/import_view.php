<script type='text/JavaScript' src='<?php echo($view_path); ?>topic.js'></script>

<style>
.post_compare
{
  border-collapse: collapse;
  width: 100%;
  margin-bottom: 20px;
  background-color: #eeeeee;
}

.post_compare td
{
  padding: 5px;
  border: 1px solid gray;
}
</style>

<?php
if(empty($_SESSION["current_language"]) || empty($GLOBALS['LANGUAGE_MAPPINGS'][$_SESSION["current_language"]]))
{
  $locale = "en";
}
else
{
  $locale = $GLOBALS['LANGUAGE_MAPPINGS'][$_SESSION["current_language"]];
}
?>

<?php if(empty($_SESSION["no_video_expand"])): ?>
<div id="fb-root"></div>
<!--
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/<?php echo($locale); ?>/sdk.js#xfbml=1&version=v2.8";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
-->
<?php endif; ?>

<?php
echo $output;
?>
