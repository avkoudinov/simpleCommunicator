<span class="message_count_bar"><?php echo(build_message_info_bar($base_url, $pagination_info, $all_entry_post)); ?> 

<?php if(!empty($pagination_info["ignored_count"])): ?>
<span class="not_preferred"><?php echo_html(text("ignored")); ?>: <?php echo_html(format_number($pagination_info["ignored_count"])); ?></span>
<?php endif; ?>

</span>
