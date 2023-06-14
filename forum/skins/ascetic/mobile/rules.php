<!-- BEGIN: forum_bar -->

<div class="forum_bar">
<a href="forums.php"><?php echo_html(text("Forums")); ?></a>

<?php
$display = "style='display:none'";
if(!empty($topics_with_new_count)) $display = "";
?>
<span class="new topics_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span> 

/ <span class="topic_title_main"><?php echo_html(text("Rules")); ?></span>
</div>

<!-- END: forum_bar -->

<div class="text_content">
<?php
if(file_exists($view_path . "lang/" . current_language() . "/rules.html")) 
{
  @include $view_path . "lang/" . current_language() . "/rules.html";
}
?>
</div>

