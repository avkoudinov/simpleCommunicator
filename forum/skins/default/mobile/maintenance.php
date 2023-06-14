<!DOCTYPE html>
<html lang="<?php echo(current_language()); ?>">
<head>
<title><?php echo_html(text("Maintenance")); ?></title>
        
<?php
$cache_appendix = "?v=" . $skin_version;
?>

<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
<meta name="viewport" content="width=1024"/>

<link rel="stylesheet" href="<?php echo($view_path); ?>css/styles.css<?php echo($cache_appendix); ?>" type="text/css"/>
<?php if($view_mode == "tablet"): ?>
<link rel="stylesheet" href="<?php echo($view_path); ?>css/styles_horizontal.css<?php echo($cache_appendix); ?>" type="text/css"/>
<?php endif; ?>

<link rel="icon" type="image/png" href="<?php echo($view_path); ?>images/favicon.png" />

</head>

<body>

<div class="maintenance">
<?php echo_html(sprintf(text("MaintenanceComment"), $maintenance_until, $time_zone_name)); ?>

    <div class="maintenance_comment">
    <?php
    if (!empty($maintenance_comment_lang[current_language()])) $maintenance_comment = $maintenance_comment_lang[current_language()];
    
    echo(nl2br(escape_html($maintenance_comment)));
    ?>
    </div>
</div>

<img class="maintenance" alt="<?php echo_html(text("Maintenance")); ?>" src="<?php echo($view_path); ?>images/maintenance.jpg<?php echo($cache_appendix); ?>">

</body>

</html>