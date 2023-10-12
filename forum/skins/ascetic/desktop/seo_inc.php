<?php if(defined("CANONICAL_DOMAIN") && !empty(CANONICAL_DOMAIN) && CANONICAL_DOMAIN != get_host_name()): ?>
<link rel="canonical" href="<?php echo((is_https() ? "https://" : "http://") . CANONICAL_DOMAIN . $_SERVER["REQUEST_URI"]); ?>">
<?php endif; ?>

<link rel="icon" type="image/png" href="<?php echo($view_path); ?>images/favicon.png<?php echo($cache_appendix); ?>" data-default-icon="<?php echo($view_path); ?>images/favicon.png<?php echo($cache_appendix); ?>" data-signal-icon="<?php echo($view_path); ?>images/favicon_new.png<?php echo($cache_appendix); ?>" id="fav_icon">

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
<meta name="msapplication-TileImage" content="<?php echo($view_path); ?>images/favicon-144x144.png">
<?php endif; ?>

<?php if(file_exists($view_path . "images/favicon.svg")): ?>
<link rel="mask-icon" href="<?php echo($view_path); ?>images/favicon.svg">
<?php endif; ?>

<meta name="msapplication-TileColor" content="#ffffff">
<meta name="theme-color" content="#ffffff">

<meta property="og:type" content="<?php echo_html($ogtype); ?>">
<meta property="og:site_name" content="<?php echo_html(get_site_name(current_language())); ?>">

<?php if (!empty($ogtitle)): ?>
<meta property="og:title" content="<?php echo_html($ogtitle); ?>">
<meta name="twitter:title" content="<?php echo_html($ogtitle); ?>">
<meta name="title" content="<?php echo_html($ogtitle); ?>">
<?php endif; ?>

<?php if (!empty($ogdescription)): ?>
<meta name="description" content="<?php echo_html($ogdescription); ?>">
<meta property="og:description" content="<?php echo_html($ogdescription); ?>">
<meta name="twitter:description" content="<?php echo_html($ogdescription); ?>">
<?php endif; ?>

<?php if(!empty($ogimage)): ?>
<meta property="og:image" content="<?php echo(get_host_address() . get_url_path() . $ogimage); ?>">

<?php if(is_https()): ?>
<meta property="og:image:secure_url" content="<?php echo(get_host_address() . get_url_path() . $ogimage); ?>">
<?php endif; ?>

<meta name="twitter:image" content="<?php echo(get_host_address() . get_url_path() . $ogimage); ?>">
<?php endif; ?>

<?php if (!empty($pagination_info["page_count"]) && $pagination_info["page_count"] > 1): ?>

    <?php if ($pagination_info["page"] > 1): ?>
    <link rel="first" href="<?php echo($pagination_info["base_url"]); ?>">

      <?php if ($pagination_info["page"] == 2): ?>
      <link rel="prev" href="<?php echo($pagination_info["base_url"]); ?>">
      <?php else: ?>
      <link rel="prev" href="<?php echo(str_replace("$", $pagination_info["page"] - 1, $pagination_info["base_url_pagination"])); ?>">
      <?php endif; ?>
    <?php endif; ?>

    <?php if ($pagination_info["page"] < $pagination_info["page_count"]): ?>
    <link rel="next" href="<?php echo(str_replace("$", $pagination_info["page"] + 1, $pagination_info["base_url_pagination"])); ?>">

    <link rel="last" href="<?php echo(str_replace("$", $pagination_info["page_count"], $pagination_info["base_url_pagination"])); ?>">
    <?php endif; ?>

<?php endif; ?>

<?php if (!empty($pagination_info["mode"])): ?>

<?php
$is_first_page = false;
if (($pagination_info["first_page_message"] == $pagination_info["first_topic_message"] ||
        $pagination_info["first_page_message"] == $pagination_info["first_topic_pinned_message"]) &&
    $pagination_info["mode"] != "all" && $pagination_info["mode"] != "download"
) {
    $is_first_page = true;
}


$is_last_page = false;
if ($pagination_info["last_page_message"] == $pagination_info["last_topic_message"] &&
    $pagination_info["mode"] != "all" 
) {
    $is_last_page = true;
}
?>

  <?php if(!($is_first_page && $is_last_page)): ?>

      <?php if(!$is_first_page): ?>
      
      <link rel="first" href="<?php echo($pagination_info["base_url"]); ?>">
      
      <?php
      if ($pagination_info["mode"] == "all" || $pagination_info["mode"] == "download") {
        if (empty($pagination_info["startmsg"])) {
            $url = $pagination_info["base_url"];
        } else {
            $url = $pagination_info["base_url"] . "&startmsg=" . $pagination_info["startmsg"] . "&offset=-1";
        }
      } else {
          $url = $pagination_info["base_url"] . "&startmsg=" . $pagination_info["first_page_message"] . "&offset=-1";
      }
      ?>
      <link rel="prev" href="<?php echo($url); ?>">
      
      <?php endif; ?>

      <?php if(!$is_last_page): ?>
      
      <?php
      if ($pagination_info["mode"] == "all") {
          $url = $pagination_info["base_url"] . "&startmsg=last&offset=-1";
      } else {
          $url = $pagination_info["base_url"] . "&startmsg=" . $pagination_info["last_page_message"] . "&offset=1";
      }
      ?>
      <link rel="next" href="<?php echo($url); ?>">
      
      <?php
      if (!empty($pagination_info["page_before_last"])) {
          $url = $pagination_info["base_url"] . "&startmsg=" . $pagination_info["last_page_message"] . "&offset=1";
      } else {
          $url = $pagination_info["base_url"] . "&startmsg=last&offset=-1";
      }
      ?>
      <link rel="last" href="<?php echo($url); ?>">
      
      <?php endif; ?>

  <?php endif; ?>

<?php endif; ?>
