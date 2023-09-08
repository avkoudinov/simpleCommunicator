<link rel="icon" type="image/png" href="<?php echo($view_path); ?>images/favicon.png<?php echo($cache_appendix); ?>" data-default-icon="<?php echo($view_path); ?>images/favicon.png<?php echo($cache_appendix); ?>" data-signal-icon="<?php echo($view_path); ?>images/favicon_new.png<?php echo($cache_appendix); ?>" id="fav_icon"/>

<?php if(file_exists($view_path . "images/favicon-57x57.png")): ?>
<link rel="apple-touch-icon" sizes="57x57" href="<?php echo($view_path); ?>images/favicon-57x57.png">
<?php endif; ?>
<?php if(file_exists($view_path . "images/favicon-60x60.png")): ?>
<link rel="apple-touch-icon" sizes="60x60" href="<?php echo($view_path); ?>images/favicon-60x60.png">
<?php endif; ?>
<?php if(file_exists($view_path . "images/favicon-72x72.png")): ?>
<link rel="apple-touch-icon" sizes="72x72" href="<?php echo($view_path); ?>images/favicon-72x72.png">
<?php endif; ?>
<?php if(file_exists($view_path . "images/favicon-76x76.png")): ?>
<link rel="apple-touch-icon" sizes="76x76" href="<?php echo($view_path); ?>images/favicon-76x76.png">
<?php endif; ?>
<?php if(file_exists($view_path . "images/favicon-114x114.png")): ?>
<link rel="apple-touch-icon" sizes="114x114" href="<?php echo($view_path); ?>images/favicon-114x114.png">
<?php endif; ?>
<?php if(file_exists($view_path . "images/favicon-120x120.png")): ?>
<link rel="apple-touch-icon" sizes="120x120" href="<?php echo($view_path); ?>images/favicon-120x120.png">
<?php endif; ?>
<?php if(file_exists($view_path . "images/favicon-144x144.png")): ?>
<link rel="apple-touch-icon" sizes="144x144" href="<?php echo($view_path); ?>images/favicon-144x144.png">
<?php endif; ?>
<?php if(file_exists($view_path . "images/favicon-152x152.png")): ?>
<link rel="apple-touch-icon" sizes="152x152" href="<?php echo($view_path); ?>images/favicon-152x152.png">
<?php endif; ?>
<?php if(file_exists($view_path . "images/favicon-180x180.png")): ?>
<link rel="apple-touch-icon" sizes="180x180" href="<?php echo($view_path); ?>images/favicon-180x180.png">
<?php endif; ?>
<?php if(file_exists($view_path . "images/favicon-192x192.png")): ?>
<link rel="icon" type="image/png" sizes="192x192" href="<?php echo($view_path); ?>images/favicon-192x192.png">
<?php endif; ?>
<?php if(file_exists($view_path . "images/favicon-32x32.png")): ?>
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo($view_path); ?>images/favicon-32x32.png">
<?php endif; ?>
<?php if(file_exists($view_path . "images/favicon-96x96.png")): ?>
<link rel="icon" type="image/png" sizes="96x96" href="<?php echo($view_path); ?>images/favicon-96x96.png">
<?php endif; ?>
<?php if(file_exists($view_path . "images/favicon-16x16.png")): ?>
<link rel="icon" type="image/png" sizes="16x16" href="<?php echo($view_path); ?>images/favicon-16x16.png">
<?php endif; ?>
<?php if(file_exists($view_path . "images/favicon-144x144.png")): ?>
<meta name="msapplication-TileImage" content="/favicon-144x144.png">
<?php endif; ?>
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="theme-color" content="#ffffff">



<meta property="og:type" content="website"/>

<?php if (!empty($ogtitle)): ?>
<meta property="og:title" content="<?php echo_html($ogtitle); ?>"/>
<?php endif; ?>

<?php if (!empty($ogdescription)): ?>
<meta property="og:description" content="<?php echo_html($ogdescription); ?>"/>
<?php endif; ?>

<?php if(file_exists($view_path . "images/forum_picture.png")): ?>
<meta property="og:image" content="<?php echo(get_host_address() . get_url_path() . $view_path); ?>images/forum_picture.png"/>
<meta property="og:image:secure_url" content="<?php echo(get_host_address() . get_url_path() . $view_path); ?>images/forum_picture.png"/>
<meta property="og:image:type" content="image/png"/>
<?php endif; ?>
