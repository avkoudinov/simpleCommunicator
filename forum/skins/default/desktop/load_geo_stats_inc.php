<h3 class="profile_caption"><?php echo_html(text("GeoStatistics")); ?></h2>

<div class="forum_activity_image_wrapper">
<?php
$query_string = "?report1=country_stats&title=" . urlencode(text("Countries"));
?>
<img class="forum_activity_image" title="<?php echo_text("Countries"); ?>" alt="<?php echo_text("Countries"); ?>" src="ajax/pie_diagram.php<?php echo($query_string); ?>&rnd=<?php echo(rand(1000, 9000)); ?>" onload="this.style.opacity = '1';">
</div>

<div class="browser_stat_wrapper">
<table class="general_statistics_table">
<tr>
<th><?php echo_html(text("Countries")); ?></th>
<th><?php echo_html(text("Value")); ?></th>
</tr>

<?php foreach($_SESSION["country_stats"] as $country => $val): ?>
<tr>
<td><?php echo_html($country); ?></td>
<td><?php echo_html(number_format($val, 1, ',', '') . " %"); ?></td>
</tr>
<?php endforeach; ?>

</table>
</div>


<?php foreach($_SESSION["city_stats"] as $country => $_country_city_stats): ?>

<h3 class="profile_caption"><?php echo_html($country); ?></h2>

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


<h3 class="profile_caption"><?php echo_html(text("Bots")); ?></h2>

<div class="forum_activity_image_wrapper">
<?php
$query_string = "?report1=country_bot_stats&title=" . urlencode(text("Bots"));
?>
<img class="forum_activity_image" title="<?php echo_text("Bots"); ?>" alt="<?php echo_text("Bots"); ?>" src="ajax/pie_diagram.php<?php echo($query_string); ?>&rnd=<?php echo(rand(1000, 9000)); ?>" onload="this.style.opacity = '1';">
</div>

<div class="browser_stat_wrapper">
<table class="general_statistics_table">
<tr>
<th><?php echo_html(text("Countries")); ?></th>
<th><?php echo_html(text("Value")); ?></th>
</tr>

<?php foreach($_SESSION["country_bot_stats"] as $country => $val): ?>
<tr>
<td><?php echo_html($country); ?></td>
<td><?php echo_html(number_format($val, 1, ',', '') . " %"); ?></td>
</tr>
<?php endforeach; ?>

</table>
</div>



<h3 class="profile_caption"><?php echo_html(text("Proxys")); ?></h2>

<div class="forum_activity_image_wrapper">
<?php
$query_string = "?report1=proxy_stats&title=" . urlencode(text("Proxys"));
?>
<img class="forum_activity_image" title="<?php echo_text("Proxys"); ?>" alt="<?php echo_text("Proxys"); ?>" src="ajax/pie_diagram.php<?php echo($query_string); ?>&rnd=<?php echo(rand(1000, 9000)); ?>" onload="this.style.opacity = '1';">
</div>


<div class="browser_stat_wrapper">
<table class="general_statistics_table">
<tr>
<th><?php echo_html(text("Proxys")); ?></th>
<th><?php echo_html(text("Value")); ?></th>
</tr>

<?php foreach($_SESSION["proxy_stats"] as $access_type => $val): ?>
<tr>
<td><?php echo_html($access_type); ?></td>
<td><?php echo_html(number_format($val, 1, ',', '') . " %"); ?></td>
</tr>
<?php endforeach; ?>

</table>
</div>


<h3 class="profile_caption"><?php echo_html(text("IPTypes")); ?></h2>

<div class="forum_activity_image_wrapper">
<?php
$query_string = "?report1=ip_type_stats&title=" . urlencode(text("IPTypes"));
?>
<img class="forum_activity_image" title="<?php echo_text("IPTypes"); ?>" alt="<?php echo_text("IPTypes"); ?>" src="ajax/pie_diagram.php<?php echo($query_string); ?>&rnd=<?php echo(rand(1000, 9000)); ?>" onload="this.style.opacity = '1';">
</div>

<div class="browser_stat_wrapper">
<table class="general_statistics_table">
<tr>
<th><?php echo_html(text("IPTypes")); ?></th>
<th><?php echo_html(text("Value")); ?></th>
</tr>

<?php foreach($_SESSION["ip_type_stats"] as $ip_type => $val): ?>
<tr>
<td><?php echo_html($ip_type); ?></td>
<td><?php echo_html(number_format($val, 1, ',', '') . " %"); ?></td>
</tr>
<?php endforeach; ?>

</table>
</div>
