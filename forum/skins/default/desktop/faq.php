<div class="content_area">

<!-- BEGIN: forum_bar -->

<div class="forum_bar">

<div class="forum_name_bar"><a href="forums.php"><?php echo_html(text("Forums")); ?></a>

<?php
$display = "style='display:none'";
if(!empty($topics_with_new_count)) $display = "";
?>
<span class="new topics_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span> 

/ <span class="topic_title_main"><?php echo_html(text("FAQ")); ?></span>
</div>

<div class="forum_action_bar">
<table>
<tr>
<td>
<?php
@include "forum_selector_inc.php";
?>
</td>
</tr>
</table>
</div>

<div class="clear_both">
</div>

</div>

<!-- END: forum_bar -->
<div class="text_content">
<?php
if(file_exists($view_path . "lang/" . current_language() . "/faq.html")) 
{
  @include $view_path . "lang/" . current_language() . "/faq.html";
}
?>
</div>
</div>

