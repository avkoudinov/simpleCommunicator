<h3 class="profile_caption"><?php echo_html(text("Browsers")); ?></h2>

<div class="browser_stat_wrapper">
<table class="general_statistics_table">
<tr>
<th><?php echo_html(text("Browsers")); ?></th>
<th><?php echo_html(text("Value")); ?></th>
</tr>

<?php foreach($browser_stat as $browser => $val): ?>
<tr>
<td><?php echo_html($browser); ?></td>
<td><?php echo_html(number_format($val, 1, ',', '') . " %"); ?></td>
</tr>
<?php endforeach; ?>

</table>
</div>

<h3 class="profile_caption"><?php echo_html(text("OperatingSystems")); ?></h2>

<div class="browser_stat_wrapper">
<table class="general_statistics_table">
<tr>
<th><?php echo_html(text("OperatingSystems")); ?></th>
<th><?php echo_html(text("Value")); ?></th>
</tr>

<?php foreach($os_stat as $os => $val): ?>
<tr>
<td><?php echo_html($os); ?></td>
<td><?php echo_html(number_format($val, 1, ',', '') . " %"); ?></td>
</tr>
<?php endforeach; ?>

</table>
</div>

<h3 class="profile_caption"><?php echo_html(text("Bots")); ?></h2>

<div class="browser_stat_wrapper">
<table class="general_statistics_table">
<tr>
<th><?php echo_html(text("Bots")); ?></th>
<th><?php echo_html(text("Value")); ?></th>
</tr>

<?php foreach($bot_stat as $bot => $val): ?>
<tr>
<td><a class="guest_link" href="view_bot_profile.php?bot=<?php echo(xrawurlencode($bot)); ?>"><?php echo_html($bot); ?></a></td>
<td><?php echo_html(number_format($val, 1, ',', '') . " %"); ?></td>
</tr>
<?php endforeach; ?>

</table>
</div>
