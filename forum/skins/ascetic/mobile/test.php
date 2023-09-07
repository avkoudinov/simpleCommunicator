<script type='text/JavaScript' src='<?php echo($view_path); ?>topic.js'></script>

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

<div class="post_table message_text" style="max-height:none">
<?php
echo $output;
?>
</div>

<?php if(empty($_SESSION["no_video_expand"])): ?>
<div id="fb-root"></div>
<script>(function(d, s, id) {
  
  // we have to adjust height and width, because the data-width attribute cannot
  // be changed over css  
  var adjust_facebook = d.getElementsByClassName('facebook_mobile_adjust_width');
  for(var i = 0; i < adjust_facebook.length; i++)
  {
    adjust_facebook[i].style.width = '648px';
    adjust_facebook[i].setAttribute("data-width", 648);
  }

  var adjust_facebook = d.getElementsByClassName('facebook_mobile_adjust_height');
  for(var i = 0; i < adjust_facebook.length; i++)
  {
    adjust_facebook[i].style.height = Math.floor(adjust_facebook[i].clientHeight*648/480) + 'px';
  }

  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/<?php echo($locale); ?>/sdk.js#xfbml=1&version=v2.8";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<?php endif; ?>
