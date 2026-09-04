<?php foreach($_SESSION["city_stats"] as $country => $_country_city_stats): ?>

<h3 class="profile_caption"><?php echo_html(text("Cities")); ?>: <?php echo_html($country); ?></h3>

<div class="forum_activity_image_wrapper">
<?php
$query_string = "?report1=city_stats&report2=" . urlencode($country) . "&title=" . urlencode($country);
?>
<img class="forum_activity_image" title="<?php echo_html($country); ?>" alt="<?php echo_html($country); ?>" src="ajax/pie_diagram.php<?php echo($query_string); ?>&rnd=<?php echo(rand(1000, 9000)); ?>" onload="this.style.opacity = '1';">
</div>

<div class="browser_stat_wrapper">
<table class="general_statistics_table">
<tr>
<th><?php echo_html(text("Cities")); ?></th>
<th><?php echo_html(text("Value")); ?></th>
</tr>

<?php foreach($_country_city_stats as $city => $val): ?>
<tr>
<td><?php echo_html($city); ?></td>
<td><?php echo_html(number_format($val, 1, ',', '') . " %"); ?></td>
</tr>
<?php endforeach; ?>

</table>
</div>

<?php endforeach; ?>


