<?php
$comma = "";
if(!empty($pagination_info["ignored_count"]) || !empty($pagination_info["ignored_comment_count"])) $comma = ",";
?>
<span class="message_count_bar"><?php echo(build_message_info_bar($base_url, $pagination_info, $all_entry_post)); ?><?php echo($comma); ?> 

<?php
$comma = "";
if(!empty($pagination_info["ignored_count"])) $comma = ",";
?>

<?php if(!empty($pagination_info["ignored_comment_count"])): ?>
<a href="<?php echo($base_url . "&force_comments=1" . $startmsg_appendix); ?>" class="not_preferred"><?php echo_html(text("hidden_comments")); ?>: <?php echo_html(format_number($pagination_info["ignored_comment_count"])); ?></a><?php echo($comma); ?>
<?php endif; ?>

<?php if(!empty($pagination_info["ignored_count"])): ?>
<span class="not_preferred"><?php echo_html(text("ignored")); ?>: <?php echo_html(format_number($pagination_info["ignored_count"])); ?></span>
<?php endif; ?>

</span>
