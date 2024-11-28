<h3 class="profile_caption"><?php echo_html(text("Browsers")); ?></h2>

<div class="forum_activity_image_wrapper">
<?php
$query_string = "?report1=browser_stats&title=" . urlencode(text("Browsers"));
?>
<img class="forum_activity_image" title="<?php echo_text("Browsers"); ?>" alt="<?php echo_text("Browsers"); ?>" src="ajax/pie_diagram.php<?php echo($query_string); ?>&rnd=<?php echo(rand(1000, 9000)); ?>" onload="this.style.opacity = '1';">
</div>


<div class="browser_stat_wrapper">
<table class="general_statistics_table">
<tr>
<th><?php echo_html(text("Browsers")); ?></th>
<th><?php echo_html(text("Value")); ?></th>
</tr>

<?php foreach($_SESSION["browser_stats"] as $browser => $val): ?>
<tr>
<td><?php echo_html($browser); ?></td>
<td><?php echo_html(number_format($val, 1, ',', '') . " %"); ?></td>
</tr>
<?php endforeach; ?>

</table>
</div>

<h3 class="profile_caption"><?php echo_html(text("OperatingSystems")); ?></h2>

<div class="forum_activity_image_wrapper">
<?php
$query_string = "?report1=os_stats&title=" . urlencode(text("OperatingSystems"));
?>
<img class="forum_activity_image" title="<?php echo_text("OperatingSystems"); ?>" alt="<?php echo_text("OperatingSystems"); ?>" src="ajax/pie_diagram.php<?php echo($query_string); ?>&rnd=<?php echo(rand(1000, 9000)); ?>" onload="this.style.opacity = '1';">
</div>

<div class="browser_stat_wrapper">
<table class="general_statistics_table">
<tr>
<th><?php echo_html(text("OperatingSystems")); ?></th>
<th><?php echo_html(text("Value")); ?></th>
</tr>

<?php foreach($_SESSION["os_stats"] as $os => $val): ?>
<tr>
<td><?php echo_html($os); ?></td>
<td><?php echo_html(number_format($val, 1, ',', '') . " %"); ?></td>
</tr>
<?php endforeach; ?>

</table>
</div>

<h3 class="profile_caption"><?php echo_html(text("Bots")); ?></h2>

<div class="forum_activity_image_wrapper">
<?php
$query_string = "?report1=bot_stats&title=" . urlencode(text("Bots"));
?>
<img class="forum_activity_image" title="<?php echo_text("Bots"); ?>" alt="<?php echo_text("Bots"); ?>" src="ajax/pie_diagram.php<?php echo($query_string); ?>&rnd=<?php echo(rand(1000, 9000)); ?>" onload="this.style.opacity = '1';">
</div>


<div class="browser_stat_wrapper">
<table class="general_statistics_table">
<tr>
<th><?php echo_html(text("Bots")); ?></th>
<th><?php echo_html(text("Value")); ?></th>
</tr>

<?php foreach($_SESSION["bot_stats"] as $bot => $val): ?>
<tr>
<td><a class="guest_link" href="view_bot_profile.php?bot=<?php echo(xrawurlencode($bot)); ?>"><?php echo_html($bot); ?></a></td>
<td><?php echo_html(number_format($val, 1, ',', '') . " %"); ?></td>
</tr>
<?php endforeach; ?>

</table>
</div>
