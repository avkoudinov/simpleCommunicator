<!DOCTYPE html>
<html lang="<?php echo(current_language()); ?>">
<head>
<title><?php echo_html($title); ?></title>
        
<?php
$cache_appendix = "?v=" . $skin_version;
?>

<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
<meta name="viewport" content="width=1024"/>

<link rel="stylesheet" href="<?php echo($view_path); ?>css/styles.css<?php echo($cache_appendix); ?>" type="text/css"/>

<?php require_once $view_path . "seo_inc.php"; ?>

</head>

<body>

<div class="maintenance">
<?php echo_html(sprintf(text("MaintenanceComment"), $maintenance_until, $time_zone_name)); ?>

    <?php if (!empty($maintenance_comment) && !empty($maintenance_link)): ?>
    <div class="maintenance_comment">
    <?php
    if (!empty($maintenance_comment)) echo(nl2br(escape_html($maintenance_comment)));
    ?>
    
    <?php if(!empty($maintenance_link)): ?>
    <br><br>
    <?php echo_html(text("MaintenanceLink")); ?>: <a href="<?php echo($maintenance_link); ?>"><?php echo_html($maintenance_link); ?></a>
    <?php endif; ?>
    </div>
    <?php endif; ?>
    
</div>

<img class="maintenance" alt="<?php echo_html(text("Maintenance")); ?>" src="<?php echo($view_path); ?>images/maintenance.jpg<?php echo($cache_appendix); ?>">

</body>

</html>