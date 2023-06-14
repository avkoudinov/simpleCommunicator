<?php
//-----------------------------------------------------------------------
session_set_cookie_params(0, str_replace("ajax/" . basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "../include/session_start_readonly_inc.php";

$ajax_processing = true;
require_once "../include/general_inc.php";
//-----------------------------------------------------------------------
if(!reqvar_empty("animated_gif")) $_REQUEST["animated"] = 1;

$thumb = reqvar("thumb");

if (empty($thumb) || $thumb == "not_found") {
    $thumb = "../" . $view_path . "images/noimage.png";
} elseif ($thumb == "image_placeholder") {
    $thumb = "../" . $view_path . "images/image_placeholder.png";
} elseif (!reqvar_empty("animated")) {
    $outfile = base64_encode(System::generateHash($thumb, SALT_KEY)) . ".jpg";
    
    $thumb = str_ireplace("{{base_url}}", get_host_address() . get_url_path(), $thumb);

    if (preg_match("/^ajax\/.+/", $thumb)) {
        $thumb = get_host_address() . get_url_path() . $thumb;
    }

    // The reason why the attachment thumb path differs from the url thumb path is
    // that the attachments can be protected from seeing by everyone.

    if (file_exists(APPLICATION_ROOT . "user_data/thumbs/" . $outfile)) {
        $thumb = "../user_data/thumbs/" . $outfile;
    } elseif (@create_animation_thumb($thumb,
        APPLICATION_ROOT . "user_data/thumbs/" . $outfile,
        APPLICATION_ROOT . "user_data/images/play_animation.png",
        true)) {
        $thumb = "../user_data/thumbs/" . $outfile;
    } else {
        $thumb = "../" . $view_path . "images/gif.png";
    }
}

//-----------------------------------------------------------------------
header("Location: $thumb");
//-----------------------------------------------------------------------
?>