<a href="search.php?do_search=1&rate_statistics=top_likes<?php echo($forum_appendix . $period_appendix); ?>" class="carma_plus" ><?php echo_html(format_number($total_likes)); ?></a>
<?php if(!empty($settings["dislikes_active"])): ?>
/ <a href="search.php?do_search=1&rate_statistics=top_dislikes<?php echo($forum_appendix . $period_appendix); ?>" class="carma_minus" ><?php echo_html(format_number($total_dislikes)); ?></a>

/ <a href="search.php?do_search=1&rate_statistics=top_rates<?php echo($forum_appendix . $period_appendix); ?>" class="carma_both" ><?php echo_html(format_number($total_rates)); ?></a>
<?php endif; ?>
