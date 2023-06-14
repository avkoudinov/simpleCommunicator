<?php
//-----------------------------------------------------------------------
@error_reporting(0);
//-----------------------------------------------------------------------
session_set_cookie_params(0, str_replace("ajax/" . basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "../include/session_start_readonly_inc.php";

$ajax_processing = true;
require_once "../include/general_inc.php";
//-----------------------------------------------------------------------
if(!reqvar_empty("animated_gif")) $_REQUEST["animated"] = 1;

$attachment_data = array();

$fmanager->get_attachment_data(reqvar("aid"), reqvar("nr"), $attachment_data);

// Attachment moderation button
if (!reqvar_empty("attachment_button")) {
    $attachment_data["name"] = empty($attachment_data["deleted"]) ? "delete_attachment.png" : "restore_attachment.png";
    if (!empty($attachment_data["indirect_deleted"]) || empty($attachment_data["path"])) {
        $attachment_data["name"] = "attachment_warning.png";
    }
    
    if (empty($attachment_data["editable"])) {
        $attachment_data["name"] = "attachment_warning.png";
    }
    
    $attachment_data["path"] = APPLICATION_ROOT . $view_path . "images/" . $attachment_data["name"];
    $attachment_data["type"] = "image/png";
    $attachment_data["indicator"] = true;
    
    $fmanager->send_attachment_and_exit($attachment_data);
}

// Attachment status indicator
if (!reqvar_empty("attachment_del_indicator")) {
    if (empty($attachment_data["path"])) {
        $attachment_data["name"] = "removed_indicator.png";
        $attachment_data["type"] = "image/png";
    } elseif (!empty($attachment_data["no_access"])) {
        $attachment_data["name"] = "no_access_indicator.png";
        $attachment_data["type"] = "image/png";
    } elseif (!file_exists($attachment_data["path"])) {
        $attachment_data["name"] = "not_found.jpg";
        $attachment_data["type"] = "image/jpg";
    } elseif (!empty($attachment_data["deleted"])) {
        $attachment_data["name"] = "del_indicator.png";
        $attachment_data["type"] = "image/png";
    } else {
        $attachment_data["name"] = "blank.gif";
        $attachment_data["type"] = "image/gif";
    }
    
    $attachment_data["path"] = APPLICATION_ROOT . $view_path . "images/" . $attachment_data["name"];
    $attachment_data["indicator"] = true;
    
    $fmanager->send_attachment_and_exit($attachment_data);
}

// If the attachment is deleted, we check the cases when the deleted attachments should be however visible.
// If the user explicitly deletes an image (doing_delete not empty), it should disappear to signalize the successful deletion.
if (!empty($attachment_data["deleted"]) && reqvar_empty("doing_delete")) {
    // Admin or moderator may see the deleted images in some cases
    if ($fmanager->is_admin() ||
        $fmanager->is_forum_moderator($attachment_data["forum_id"]) ||
        $fmanager->is_topic_moderator($attachment_data["topic_id"])
    ) {
        $is_in_search = (strpos(val_or_empty($_SERVER['HTTP_REFERER']), "search_topic.php") !== false ||
            strpos(val_or_empty($_SERVER['HTTP_REFERER']), "from_search") !== false
        );
        
        // User may see own deleted attachments by direct jump to the post or in preview
        $direct_jump_to_post = false;
        if (preg_match("/&(goto)?msg=(\d+)/", val_or_empty($_SERVER['HTTP_REFERER']), $matches) && reqvar("aid") == $matches[2]) {
            $direct_jump_to_post = true;
        }
        
        if ((reqvar_empty("thumb") && reqvar_empty("embedded")) || // in preview
            !empty($_SESSION["show_deleted"]) || // in deleted view mode but not while deleting
            $is_in_search || // in search
            $direct_jump_to_post || // by direct going to a post
            !empty($attachment_data["indirect_deleted"]) // by viewing deleted topic or forum
        ) {
            $attachment_data["deleted"] = false;
        }
        // Simple user may see own deleted attachments by direct jump to the post or in preview
    } elseif (($fmanager->get_user_id() != "" && $attachment_data["post_user_id"] == $fmanager->get_user_id()) || $attachment_data["post_read_marker"] == $READ_MARKER) {
        $direct_jump_to_post = false;
        if (preg_match("/&(goto)?msg=(\d+)/", val_or_empty($_SERVER['HTTP_REFERER']), $matches) && reqvar("aid") == $matches[2]) {
            $direct_jump_to_post = true;
        }
        
        if ((reqvar_empty("thumb") && reqvar_empty("embedded")) || // in preview
            $direct_jump_to_post // by direct jump to the post
        ) {
            $attachment_data["deleted"] = false;
        }
    }
}

$no_image_icon = !reqvar_empty("thumb") ? "noimage.png" : "noimage_big.png";

// not exists
if (empty($attachment_data["path"]) && !reqvar_empty("picture")) {
    $attachment_data["path"] = APPLICATION_ROOT . $view_path . "images/" . $no_image_icon;
    $attachment_data["name"] = $no_image_icon;
    $attachment_data["type"] = "image/png";
    $attachment_data["indicator"] = true;
    
    $fmanager->send_attachment_and_exit($attachment_data);
}

// No access to the attachment or deleted or the attachment does not exist
if (!empty($attachment_data["no_access"]) || !empty($attachment_data["deleted"]) ||
    empty($attachment_data["path"]) || !file_exists($attachment_data["path"])) {
    if (!reqvar_empty("animated")) {
        $attachment_data["path"] = APPLICATION_ROOT . $view_path . "images/noimage.png";
        $attachment_data["name"] = "gif.png";
        $attachment_data["type"] = "image/png";
        $attachment_data["indicator"] = true;
        
        $fmanager->send_attachment_and_exit($attachment_data);
    } elseif (preg_match("/image.+/", val_or_empty($attachment_data["origin_type"]))) {
        // A picture is not found or not accessible, send a noimage picture
        $attachment_data["path"] = APPLICATION_ROOT . $view_path . "images/" . $no_image_icon;
        $attachment_data["name"] = $no_image_icon;
        $attachment_data["type"] = "image/png";
        $attachment_data["indicator"] = true;
        
        $fmanager->send_attachment_and_exit($attachment_data);
    } elseif (!reqvar_empty("thumb")) {
        // An attachment is not found or not accessible, just send the doctype indicator
        // The status will be shown over the special picture
        $fmanager->get_doctype_thumb(APPLICATION_ROOT . $view_path . "images/doctypes/", $attachment_data);
        
        $fmanager->send_attachment_and_exit($attachment_data);
    } else {
        // Just send it, HTTP status will be shown
        $fmanager->send_attachment_and_exit($attachment_data);
    }
}

// It is a gif animation
if (!reqvar_empty("animated")) {
    $outfile = "att-" . reqvar("aid");
    if (!reqvar_empty("nr")) {
        $outfile .= "-" . reqvar("nr");
    }
    
    $outfile .= ".jpg";
    
    if (preg_match("/ajax\/.+/", $attachment_data["path"])) {
        $attachment_data["path"] = get_host_address() . get_url_path() . $attachment_data["path"];
    }
    
    // The reason why the attachment thumb path differs from the url thumb path is
    // that the attachments can be protected from seeing by everyone.
    
    if (file_exists(APPLICATION_ROOT . "user_data/attachments/thumbs/" . $outfile) &&
        filemtime($attachment_data["path"]) == filemtime(APPLICATION_ROOT . "user_data/attachments/thumbs/" . $outfile)) {
        $attachment_data["path"] = APPLICATION_ROOT . "user_data/attachments/thumbs/" . $outfile;
        $attachment_data["name"] = $outfile;
        $attachment_data["type"] = "image/jpeg";
    } elseif (create_animation_thumb($attachment_data["path"],
        APPLICATION_ROOT . "user_data/attachments/thumbs/" . $outfile,
        APPLICATION_ROOT . "user_data/images/play_animation.png",
        false)) {
        $attachment_data["path"] = APPLICATION_ROOT . "user_data/attachments/thumbs/" . $outfile;
        $attachment_data["name"] = $outfile;
        $attachment_data["type"] = "image/jpeg";
    } else {
        $attachment_data["path"] = APPLICATION_ROOT . $view_path . "images/gif.png";
        $attachment_data["name"] = "gif.png";
        $attachment_data["type"] = "image/png";
        $attachment_data["indicator"] = true;
    }
    
    $fmanager->send_attachment_and_exit($attachment_data);
}

if (!reqvar_empty("thumb")) {
    // It is not a picture, show attachment doctype thumb
    if (!preg_match("/image.+/", val_or_empty($attachment_data["origin_type"]))) {
        $fmanager->get_doctype_thumb(APPLICATION_ROOT . $view_path . "images/doctypes/", $attachment_data);
        // The user wants to hide the pictures and view them only in preview,
        // then show a placeholder in the thumb.
    }
}

$fmanager->send_attachment_and_exit($attachment_data);
?>