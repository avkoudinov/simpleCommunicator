<?php
//------------------------------------------------------------------------------
require_once APPLICATION_ROOT . "nbbc/nbbc.php";

//------------------------------------------------------------------------------
$BB_PARSER_VERSION = 4;
//------------------------------------------------------------------------------
function bb_word($bbcode, $action, $name, $default, $params, $content)
{
    if ($bbcode->message_mode == "email") {
        $nl = "\n\n";
        $nl2 = "\n\n";
    } else {
        $nl = "\n\n<div class='block_wrapper'>";
        $nl2 = "\n\n</div>";
    }
    
    switch ($name) {
        case "hidden":
            return "***";
            break;

        case "code":
            $appendix = "";
            if (!empty($default)) {
                $appendix = ": " . $default;
            }
            
            return "{$nl}[{{code}}$appendix]{$nl2}";
            break;
        
        case "anim":
        case "gif":
            return "{$nl}[{{animation}}]{$nl2}";
            break;

        case "img":
            return "{$nl}[{{picture}}]{$nl2}";
            break;
        
        case "table":
            return "{$nl}[{{table}}]{$nl2}";
            break;
        
        case "spoiler":
            return "{$nl}[{{spoiler}}]{$nl2}";
            break;
        
        case "audio":
            return "{$nl}[{{audio}}]{$nl2}";
            break;
        
        case "video":
            return "{$nl}[{{video}}]{$nl2}";
            break;
        
        case "gallery":
            return "{$nl}[{{gallery}}]{$nl2}";
            break;
        
        case "coub":
            return "{$nl}[{{video}}: Coub]{$nl2}";
            break;
        
        case "telegram":
            return "{$nl}[{{video}}: Telegram]{$nl2}";
            break;
        
        case "fbvideo":
            return "{$nl}[{{video}}: Facebook]{$nl2}";
            break;
        
        case "vkvideo":
            return "{$nl}[{{video}}: VK]{$nl2}";
            break;
        
        case "instagram":
            return "{$nl}[{{video}}: Instagram]{$nl2}";
            break;
        
        case "radikal":
            return "{$nl}[{{video}}: Radikal]{$nl2}";
            break;
        
        case "reddit":
            return "{$nl}[{{video}}: Reddit]{$nl2}";
            break;
        
        case "tiktok":
            return "{$nl}[{{video}}: TikTok]{$nl2}";
            break;
        
        case "rutube":
            return "{$nl}[{{video}}: RuTube]{$nl2}";
            break;
        
        case "twitter":
            return "{$nl}[{{video}}: Twitter]{$nl2}";
            break;
        
        case "vimeo":
            return "{$nl}[{{video}}: Vimeo]{$nl2}";
            break;
        
        case "youtube":
            return "{$nl}[{{video}}: YouTube]{$nl2}";
            break;
        
        case "gmap":
            return "{$nl}[{{maps}}: Google]{$nl2}";
            break;
        
        case "latex":
            return "{$nl}[{{formula}}]{$nl2}";
            break;
    }
    
    return $content;
} // bb_word
//------------------------------------------------------------------------------
function bb_process_quote_simple($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    // restore replaced quotes
    
    $default = str_replace("___:QT:___", "'", $default);
    $default = str_replace("___:DQT:___", "\"", $default);
    
    $author = trim($default);
    
    if (preg_match("/(.*)(#\\d+)/", $author, $matches)) {
        $author = $matches[1];
    }
    
    if ($bbcode->message_mode != "email") {
        $content = HtmlHelper::cut(trim($content), 250);
        
        $content = str_replace("<br>", "<br />", $content);
    } else {
        $content = spec_cut(trim($content), 250);
    }

    if (empty($author)) {
        if ($bbcode->message_mode != "email") {
            $content = "{{citation}}:<br />" . trim($content);
        } else {
            $content = "[{{citation}}:\n" . trim($content) . "]";
        }
    } else {
        $author = ($author == "admin") ? "{{admin}}" : $author;
        
        if ($bbcode->message_mode != "email") {
            $author = escape_html($author);
            $content = "{{citation}} - $author:<br />" . $content;
        } else {
            $content = "[{{citation}} - $author:\n" . $content . "]";
        }
    }
    
    if ($bbcode->message_mode == "email") {
        return "\n\n" . $content . "\n\n";
    } else {
        return "<div class='block_wrapper citation'>" . $content . "</div>";
    }
} // bb_process_quote_simple
//------------------------------------------------------------------------------
function bb_process_spoiler_simple($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    // restore replaced quotes
    
    $default = str_replace("___:QT:___", "'", $default);
    $default = str_replace("___:DQT:___", "\"", $default);
    
    $author = trim($default);
    
    if (preg_match("/(.*)(#\\d+)/", $author, $matches)) {
        $author = $matches[1];
    }
    
    if ($bbcode->message_mode != "email") {
        $content = HtmlHelper::cut(trim($content), 250);
    } else {
        $content = spec_cut(trim($content), 250);
    }

    if (empty($author)) {
        if ($bbcode->message_mode != "email") {
            $content = "{{spoiler}}:<br />" . trim($content);
        } else {
            $content = "[{{spoiler}}:\n" . trim($content) . "]";
        }
    } else {
        $author = ($author == "admin") ? "{{admin}}" : $author;
        
        if ($bbcode->message_mode != "email") {
            $author = escape_html($author);
            $content = "{{spoiler}} - $author:<br />" . $content;
        } else {
            $content = "[{{spoiler}} - $author:\n" . $content . "]";
        }
    }
    
    if ($bbcode->message_mode == "email") {
        return "\n\n" . $content . "\n\n";
    } else {
        return "<div class='block_wrapper citation'>" . $content . "</div>";
    }
} // bb_process_spoiler_simple
//------------------------------------------------------------------------------
function bb_process_email_simple($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    $receiver = "";
    if (!empty($default) && is_string($default)) {
        $receiver = (trim($default));
    }
    
    return $receiver;
} // bb_process_email_simple
//------------------------------------------------------------------------------
function bb_process_email($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    if (!empty($default) && is_string($default)) {
        $receiver = (trim($default));
        $content = trim($content);
    } else {
        $receiver = trim($content);
        $content = urldecode(trim($content));
    }
    
    if ($receiver == $content) {
        $code = "[email]{$receiver}[/email]";
    } else {
        $code = "[email={$receiver}]{$content}[/email]";
    }
    
    if (!empty($receiver)) {
        return "<a href=\"mailto:$receiver\"data-spec-link='" . escape_html($code) . "'>$content</a>";
    } else {
        return $content;
    }
} // bb_process_email
//------------------------------------------------------------------------------
function bb_process_url_simple($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    if (is_string($default)) {
        $href = (trim(strip_tags($default)));
        $content = trim($content);
    } else {
        $href = (trim(strip_tags($content)));
        $content = urldecode(trim($content));
    }
    
    if (!$bbcode->IsValidURL($href)) {
        return htmlspecialchars($params['_tag']) . $content . htmlspecialchars($params['_endtag']);
    }
    
    $href = check_relative_url($href, "{{base_url}}");
    
    detect_encoding($content);
    
    if ($content == $href) {
        return "{{url: $href}}";
    } else {
        return $content . ": {{url: $href}}";
    }
} // bb_process_url_simple
//------------------------------------------------------------------------------
function bb_process_url($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    $content = preg_replace("/^(<br \/>\s*)+/u", "", trim($content));
    $content = preg_replace("/(<br \/>\s*)+$/u", "", trim($content));
    
    if (preg_match("/.*\\[attachment(\\d*)=([^\\]]+)\\].*/i", $content, $matches) ||
        preg_match("/.*<div class='picture_wrapper'>.*/i", $content, $matches)
    ) {
        $link_appendix = "";
        if (!empty($default) && is_string($default)) {
            $href = (trim(strip_tags($default)));
            $href = str_replace("&", "&amp;", $href);
            
            $bbcode->has_link = 1;
            $final_href = check_relative_url($href);
            $href = urldecode(trim($href));
            
            $link_appendix = "<a href=\"$final_href\" target='_blank' data-spec-link='" . escape_html($href) . "'>" . escape_html($href) . "</a>";
        }
        
        return $content . $link_appendix;
    }
    
    if (!empty($default) && is_string($default)) {
        $href = (trim(strip_tags($default)));
        $href = str_replace("&", "&amp;", $href);
        $content = rawurldecode(trim($content));
    } else {
        $href = (trim(strip_tags($content)));
        $content = rawurldecode(trim($content));
    }
    
    if (!$bbcode->IsValidURL($href)) {
        return htmlspecialchars($params['_tag']) . $content . htmlspecialchars($params['_endtag']);
    }
    
    $bbcode->has_link = 1;
    
    $final_href = check_relative_url($href);
    //$final_href = xrawurlencode($final_href);
    
    detect_encoding($content);
    
    return "<a href=\"$final_href\" target='_blank'>$content</a>";
} // bb_process_url
//------------------------------------------------------------------------------
function check_hot_linking(&$url, &$alternative_code)
{
    $no_hot_linking_list = array();

    $host = parse_url($url, PHP_URL_HOST);
    if (file_exists(APPLICATION_ROOT . "user_data/config/no_hot_linking_list.txt")) {
        $no_hot_linking_list = file_get_contents(APPLICATION_ROOT . "user_data/config/no_hot_linking_list.txt");
        $no_hot_linking_list = preg_split("/[\n\r]+/", $no_hot_linking_list, -1, PREG_SPLIT_NO_EMPTY);
    }

    foreach ($no_hot_linking_list as $pattern) {
        if (stripos($host, $pattern) !== false) {
            $alternative_code = "<div class='picture_wrapper'><img class='post_image' src='user_data/images/hotlinking.jpg' alt='{{picture}}'></div>";
            return false;
        }
    }
    
    return true;
} // check_hot_linking
//------------------------------------------------------------------------------
function replace_image_url(&$url, &$large_url)
{
    //return;
    
    // yip.su
    // grabify.link
    // fintank.ru
    // fortnight.space
    
    $img_black_list = array();
    
    if (file_exists(APPLICATION_ROOT . "user_data/config/img_black_list.txt")) {
        $img_black_list = file_get_contents(APPLICATION_ROOT . "user_data/config/img_black_list.txt");
        $img_black_list = preg_split("/[\n\r]+/", $img_black_list, -1, PREG_SPLIT_NO_EMPTY);
    }

    $host = parse_url($url, PHP_URL_HOST);
    if (in_array($host, $img_black_list)) {
        $url = "user_data/images/ip_track_warning.png";
        $large_url = "user_data/images/ip_track_warning.png";
        return;
    }
    
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 5.0
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ]);
    
    if ($headers = @get_headers($url, 1, $ctx)) {
        foreach ($headers as $name => $value) {
            $headers[strtolower($name)] = $value;
        }
        
        $src_type = "";
        $src_length = 0;
        
        if (!empty($headers["content-type"]))
        {
            if (is_array($headers["content-type"])) {
                foreach ($headers["content-type"] as $i => $content_type) {
                    if (strpos($content_type, "image") === 0) {
                       $src_type = $content_type;
                       $src_length = empty($headers["content-length"][$i]) ? 0 : $headers["content-length"][$i];
                       break; 
                    }                  
                }
                
            } else {
                $src_type = $headers["content-type"];
                $src_length = empty($headers["content-length"]) ? 0 : $headers["content-length"];
            }
        }
        
        if (!empty($src_length) && $src_length > 10485760) {
            $large_url = $url;
            $url = "user_data/images/large_picture.png";
            return;
        }
      
        if (!empty($src_type) && strpos($src_type, "image") === 0) 
        {
          $info = @getimagesize($url);
          if (preg_match('/width="(\d+)" height="(\d+)"/', val_or_empty($info["3"]), $matches) &&
              ($matches[1] > 10000 || $matches[2] > 10000)) {
              $large_url = $url;
              $url = "user_data/images/large_picture.png";
              return;
          }
        }
        
        if (!empty($headers["location"])) {
            if (!is_array($headers["location"])) {
                $host = parse_url($headers["location"], PHP_URL_HOST);
                if (in_array($host, $img_black_list)) {
                    $url = "user_data/images/ip_track_warning.png";
                    $large_url = "user_data/images/ip_track_warning.png";
                    return;
                }
            } else {
                foreach ($headers["location"] as $location) {
                    $host = parse_url($location, PHP_URL_HOST);
                    if (in_array($host, $img_black_list)) {
                        $url = "user_data/images/ip_track_warning.png";
                        $large_url = "user_data/images/ip_track_warning.png";
                        return;
                    }
                }
            }
        }
        
        if (!empty($headers["referer"])) {
            if (!is_array($headers["referer"])) {
                $host = parse_url($url, PHP_URL_HOST);
                if (in_array($host, $img_black_list)) {
                    $url = "user_data/images/ip_track_warning.png";
                    $large_url = "user_data/images/ip_track_warning.png";
                    return;
                }
            } else {
                foreach ($headers["referer"] as $location) {
                    $host = parse_url($location, PHP_URL_HOST);
                    if (in_array($host, $img_black_list)) {
                        $url = "user_data/images/ip_track_warning.png";
                        $large_url = "user_data/images/ip_track_warning.png";
                        return;
                    }
                }
            }
        }
    }
} // replace_image_url
//------------------------------------------------------------------------------
function bb_process_hr($bbcode, $action, $name, $default, $params, $content)
{
    return "<hr/>";
} // bb_process_hr
//------------------------------------------------------------------------------
function bb_process_img($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    if (trim($default) != "") {
        $src = trim($default);
    } else {
        $src = trim($content);
    }
    
    if (preg_match("/\\[attachment(\\d*)=([^\\]]+)\\]/i", $src, $matches)) {
        return $content;
    }
    
    if (empty($src)) {
        return "<p>[img][/img]</p>";
    }
    
    $large_src = $src;
    
    $alternative_code = "";
    if (!check_hot_linking($src, $alternative_code)) {
        return $alternative_code;
    }
    
    replace_image_url($src, $large_src);
    
    $src = check_relative_url($src);
    $src = str_replace("&amp;", "&", $src);

    $large_src = check_relative_url($large_src);
    $large_src = str_replace("&amp;", "&", $large_src);
    
    return "<div class='picture_wrapper'><a href='$large_src' class='lightbox_image' target='_blank'><img class='post_image' src='$src' alt='{{picture}}'></a></div>";
} // bb_process_img
//------------------------------------------------------------------------------
function bb_process_latex($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    $file_name = sha1($content) . ".png";
    $src = "user_data/thumbs/" . $file_name;
    
    if (!file_exists(APPLICATION_ROOT . $src)) {
        if (!defined("SUPPORT_LATEX") || !SUPPORT_LATEX) {
            return "<pre class='fixed'>[latex]No LaTeX support![/latex]</pre>";
        }
        
        try {
            create_latex_png($content, 170, APPLICATION_ROOT . $src);
        } catch (Exception $ex) {
            return "<pre class='fixed'>[latex]" . escape_html($ex->getMessage()) . "[/latex]</pre>";
        }
    }
    
    return "<div class='picture_wrapper'><a href='$src' class='lightbox_image latex_image' target='_blank'><img class='post_image' src='$src' alt='picture'><img class='post_image image_placeholder' src='$src' alt='picture'></a></div>";
} // bb_process_latex
//------------------------------------------------------------------------------
function bb_process_anim($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    if (trim($default) != "") {
        $src = trim($default);
    } else {
        $src = trim($content);
    }
    
    $alternative_code = "";
    if (!check_hot_linking($src, $alternative_code)) {
        return $alternative_code;
    }

    $encoded_src = check_relative_url($src);
    // this url is to be passed as parameter and does not need to have entities
    $encoded_src = str_replace("&amp;", "&", $encoded_src);
    $encoded_src = xrawurlencode($encoded_src);
    
    $thumb_src = "ajax/thumb.php?thumb=$encoded_src&amp;animated=1";
    
    $src = check_relative_url($src);
    
    if (empty($src)) {
        return "<p>[anim][/anim]</p>";
    }
    
    $wrapper_begin = "";
    $wrapper_end = "";
    $att_pict_appendix = "";
    
    if (preg_match("/\\[attachment(\\d*)=([^\\]]+)\\]/i", $src, $matches)) {
        $appx = "&amp;rnd=" . time();
        
        $idx = $matches[1];
        $att = $matches[2];
        
        $src = "ajax/attachment.php?aid=" . $att . "&amp;nr=" . $idx . $appx;
        
        $thumb_src = "ajax/attachment.php?embedded=1&amp;animated=1&amp;aid=" . $att . "&amp;nr=" . $idx . "$appx";
        
        if ($matches[1] != "tmp") {
            $own_attachment_class = "";
            if (!empty($bbcode->post_id) && $bbcode->post_id == $att) {
                $own_attachment_class = "own_attachment";
            }
            
            $wrapper_begin = "<div class='attachment_wrapper gif_wrapper $own_attachment_class' data-attid='$att' data-attnr='$idx' data-attgif='1'><div class='attachment_del_indicator attachment_del_indicator_{$att}_{$idx}' style='background-image:url(ajax/attachment.php?attachment_del_indicator=1&amp;aid=$att&amp;nr=$idx$appx)'></div><div class='attachment_button attachment_button_{$att}_{$idx}' style='background-image:url(ajax/attachment.php?attachment_button=1&amp;aid=$att&amp;nr=$idx$appx)' onclick='return delete_restore_attachment(this, \"$att\", \"$idx\")'></div>";
            $wrapper_end = "</div>";
        }
        
        $att_pict_appendix = "attachment_picture_{$att}_{$idx}";
    } else {
        $wrapper_begin = "<div class='picture_wrapper'>";
        $wrapper_end = "</div>";
    }
    
    return $wrapper_begin . "<div class='gif_loading_animation'></div><img class='post_image gif_placeholder $att_pict_appendix' src='$thumb_src' data-src='$src' alt='{{picture}}' onclick='start_gif_loading(this)'>" . $wrapper_end;
} // bb_process_anim
//------------------------------------------------------------------------------
function bb_process_smile($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    if (trim($default) != "") {
        $src = trim($default);
    } else {
        $src = trim($content);
    }
    
    if (preg_match("/\\[attachment(\\d*)=([^\\]]+)\\]/i", $src, $matches)) {
      return $src;
    }
    
    return "<img class='bbcode_smiley bbcode_external_smiley' src='$src' alt='smile'>";
} // bb_process_smile
//------------------------------------------------------------------------------
function bb_process_smile_simple($bbcode, $action, $name, $default, $params, $content)
{
    return "[:smile]";
} // bb_process_smile_simple
//------------------------------------------------------------------------------
function bb_process_bold_simple($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    // cut post reference at the author's name
    return preg_replace("/#\d+$/", "", $content);
} // bb_process_bold_simple
//------------------------------------------------------------------------------
function bb_process_fixed($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    $font_class = "";
    if (!empty($params["size"]) && is_numeric($params["size"]) && $params["size"] >= 1 && $params["size"] <= 5) {
        $font_class = "size" . round($params["size"]);
    }
    
    $background_style = "";
    if (!empty($params["bg"])) {
        $background_style = "style='background-color: " . escape_html($params["bg"]) . "'";
    }
    
    $content = trim($content, "\r\n");
    $content = str_replace("\t", "  ", $content);
    
    $has_link = 0;
    $has_code = 0;
    
    if (empty($params["plain"])) {
        $content = html_entity_decode($content);
        parse_bb_code($content, $content, $has_link, $has_code, $bbcode->post_id);
        $content = str_replace("<br />", "", $content);
    }
    
    return "<div class='fixed $font_class' $background_style>$content</div>";
} // bb_process_fixed
//------------------------------------------------------------------------------
function bb_process_poem($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    $content = trim($content, "\r\n");
    $content = str_replace("\t", "  ", $content);
    
    $has_link = 0;
    $has_code = 0;
    
    $content = html_entity_decode($content);
    parse_bb_code($content, $content, $has_link, $has_code, $bbcode->post_id);
    $content = str_replace("<br />", "", $content);
    
    return "<div class='poem'>$content</div>";
} // bb_process_poem
//------------------------------------------------------------------------------
function bb_process_quote($bbcode, $action, $name, $default, $params, $content)
{
    global $fmanager;
    
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    // restore replaced quotes
    
    $default = str_replace("___:QT:___", "'", $default);
    $default = str_replace("___:DQT:___", "\"", $default);
    
    $citated = "";
    $date = "";
    $author = trim($default);
    
    $is_simple_citation = true;
    if (preg_match("/(.*)(#\\d+)((:\\d+)?)/", $author, $matches)) {
        $is_simple_citation = false;
        $author = $matches[1];
        $citated = trim($matches[2], '#');
    }
    
    $class_appendix = "";
    
    if (!$is_simple_citation) {
        $rm = $fmanager->get_post_readmarker($citated);
        if (!empty($rm)) {
            $rm = ":" . $rm;
        }

        $uid = $fmanager->user_exists($author);
        if (empty($uid)) {
            $class_appendix .= "{{guest_ignored:" . $author . "$rm}} guest_citation";
        }
        else
        {
            $class_appendix .= "{{user_ignored:$uid}} user_citation";
        }
        
        $class_appendix .= " author_" . md5($author);
    }
    
    if (empty($author)) {
        $author2 = escape_html("{{citation}}");
    } else {
        $author2 = make_links($author == "admin" ? "{{admin}}" : $author);
        $author = escape_html($author);
    }
    
    $is_adult = 0;
    $citated_txt = "";
    $citated_mid_appendix = "";
    if (!empty($citated)) {
        $citated_mid_appendix = ' data-cmid="' . $citated . '"';
        $citated_txt = "<div class='qcitated'>[cmid=" . $citated . "]</div>";
        
        $date = $fmanager->get_post_date($citated, $is_adult);
    } else {
        $class_appendix = "";
    }
    
    $date2 = "";
    if (!empty($date)) {
        $date2 = " <span class='qdate'>{{date: " . $date . "}}</span>";
    }
    
    $citation_expander = "<span class='citation_expander'>&nbsp;</span>";
    
    $adult_class = "";
    if ($is_adult) {
        $adult_class = "adult_post";
    }
    
    return "\n\n" . '<div class="quote_wrapper ' . $class_appendix . '" data-author="' . $author . '"' . $citated_mid_appendix . '><div class="quote_header"><div class="qauthor">' . $author2 . $citation_expander . $date2 . '</div><div class="qauthor_ignored">[{{ignored}}]</div>' . $citated_txt . '<div class="clear_both"></div></div><div class="quote ' . $adult_class . '" data-author="' . $author . '"' . $citated_mid_appendix . '>' . $content . '</div></div>' . "\n\n";
} // bb_process_quote
//------------------------------------------------------------------------------
function bb_remove_quotes($bbcode, $action, $name, $default, $params, $content)
{
    global $fmanager;
    
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    // restore replaced quotes
    
    $default = str_replace("___:QT:___", "'", $default);
    $default = str_replace("___:DQT:___", "\"", $default);
    
    $author = trim($default);
    if (preg_match("/(.*)(#\\d+)((:\\d+)?)/", $author, $matches)) {
        return "\n\n";
    }
    
    $result = "\n\n[" . $name;
    if (!empty($default)) $result .= "=" . $default;
    $result .= "]\n" . $content . "\n[/quote]\n\n";
    
    return $result;
} // bb_remove_quotes
//------------------------------------------------------------------------------
function bb_process_spoiler($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    // restore replaced quotes
    
    $default = str_replace("___:QT:___", "'", $default);
    $default = str_replace("___:DQT:___", "\"", $default);
    
    $author = trim($default);
    if (empty($author)) {
        $author2 = escape_html("{{spoiler}}");
    } else {
        $author2 = make_links($author);
        $author = escape_html($author);
    }    
    
    return "\n\n" . '<div class="spoiler_wrapper"><div class="spoiler_header" onclick="toggle_spoiler(this)">' . $author2 . '</div><div class="spoiler">' . $content . '</div></div>' . "\n\n";
} // bb_process_spoiler
//------------------------------------------------------------------------------
function bb_process_list($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    $content = str_replace("<br />", "", $content);
    $content = trim($content, "\r\n");
    
    $items = preg_split("/[\r\n]+/", $content, -1, PREG_SPLIT_NO_EMPTY);
    if (empty($items)) {
        return "";
    }
    
    $content = "<ul>\n";
    foreach ($items as $item) {
        $content .= "<li>" . $item . "</li>\n";
    }
    $content .= "</ul>";
    
    return $content;
} // bb_process_list
//------------------------------------------------------------------------------
function bb_process_list_simple($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    $content = str_replace("<br />", "", $content);
    $content = trim($content, "\r\n");
    
    $items = preg_split("/[\r\n]+/", $content, -1, PREG_SPLIT_NO_EMPTY);
    if (empty($items)) {
        return "";
    }
    
    $content = "\n\n";
    foreach ($items as $item) {
        $content .= "- " . $item . "\n";
    }
    $content .= "\n\n";
    
    return $content;
} // bb_process_list_simple
//------------------------------------------------------------------------------
function bb_process_nlist($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    $content = str_replace("<br />", "", $content);
    $content = trim($content, "\r\n");
    
    $items = preg_split("/[\r\n]+/", $content, -1, PREG_SPLIT_NO_EMPTY);
    if (empty($items)) {
        return "";
    }
    
    $content = "<ol>\n";
    foreach ($items as $item) {
        $content .= "<li>" . $item . "</li>\n";
    }
    $content .= "</ol>";
    
    return $content;
} // bb_process_nlist
//------------------------------------------------------------------------------
function bb_process_nlist_simple($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    $content = str_replace("<br />", "", $content);
    $content = trim($content, "\r\n");
    
    $items = preg_split("/[\r\n]+/", $content, -1, PREG_SPLIT_NO_EMPTY);
    if (empty($items)) {
        return "";
    }
    
    $content = "\n\n";
    $counter = 1;
    foreach ($items as $item) {
        $content .= "$counter. " . $item . "\n";
        
        $counter++;
    }
    $content .= "\n\n";
    
    return $content;
} // bb_process_nlist_simple
//------------------------------------------------------------------------------
function bb_process_gallery($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    return "<div class='thumb_gallery'>" . $content . "<div class='clear_both'></div></div>";
} // bb_process_gallery
//------------------------------------------------------------------------------
function bb_process_table($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    $content = html_entity_decode($content);
    
    $rows = preg_split("/[\r\n]+/", $content, -1, PREG_SPLIT_NO_EMPTY);
    if (empty($rows)) {
        return "";
    }
    
    $table = "";
    
    $delimiter = ",";
    if (!empty($default)) {
        $delimiter = $default;
    }
    
    $delimiter_orig = $delimiter;
    
    if ($delimiter == "tab") {
        $delimiter = "\t";
    }
    
    $first = true;
    foreach ($rows as $row) {
        $cells = str_getcsv($row, $delimiter);
        
        $tag = "td";
        if ($first) {
            $first = false;
            $tag = "th";
            if (val_or_empty($cells[0]) == "-") {
                continue;
            }
        }
        
        $table .= "<tr>\n";
        foreach ($cells as $cell) {
            $cell = str_replace("\\\"", "\"", trim($cell));
            
            $has_link = 0;
            $has_code = 0;
            parse_bb_code($cell, $cell, $has_link, $has_code, $bbcode->post_id);
            
            $colspan = "";
            if (preg_match("/(.*)::(\\d+)/", $cell, $matches) && $matches[2] > 1) {
                $colspan = "colspan='$matches[2]'";
                $cell = $matches[1];
            }
            
            $table .= "<$tag $colspan>" . $cell . "</$tag>\n";
        }
        $table .= "</tr>";
    }
    
    return "<div class='table_wrapper'><table class='message_table csv_table' data-delimiter='$delimiter_orig'>\n" . $table . "</table></div>";
} // bb_process_table
//------------------------------------------------------------------------------
function bb_process_audio($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    $url = $bbcode->UnHTMLEncode(strip_tags($content));
    
    $src = trim($content);
    $content = urldecode($src);
    
    $alternative_code = "";
    if (!check_hot_linking($src, $alternative_code)) {
        return $alternative_code;
    }

    if (preg_match("/\\[attachment(\\d*)=([^\\]]+)\\]/i", $src, $matches)) {
        return $content;
    }
    
    if (!$bbcode->IsValidURL($url)) {
        return htmlspecialchars($params['_tag']) . $content . htmlspecialchars($params['_endtag']);
    }
    
    return
        "\n\n<div class='emb_wrapper'><audio controls='controls' preload='metadata' autobuffer='metadata'>" .
        "<source src='$src'>" .
        "<a href='$src'>$content</a>" .
        "</audio>" .
        "<a class='attachment_link' href='$src' target='_blank'>{{link}}</a>" .
        "</div>\n\n";
} // bb_process_audio
//------------------------------------------------------------------------------
function bb_process_video($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    $url = $bbcode->UnHTMLEncode(strip_tags($content));
    
    $src = trim($content);
    $content = urldecode($src);
    
    $alternative_code = "";
    if (!check_hot_linking($src, $alternative_code)) {
        return $alternative_code;
    }

    if (preg_match("/\\[attachment(\\d*)=([^\\]]+)\\]/i", $src, $matches)) {
        return $content;
    }
    
    if (!$bbcode->IsValidURL($url)) {
        return htmlspecialchars($params['_tag']) . $content . htmlspecialchars($params['_endtag']);
    }
    
    return
        "\n\n<div class='emb_wrapper'>" .
        "<div class='short_video'>" .
        "<a class='emb_video_short_container' href='#' onclick='return show_embedded_video(this)'>" . escape_html(text("ShowEmbeddedVideo")) . "</a>" .
        "</div><div class='emb_video_container detailed_video'>" .
        "<video controls='controls' preload='metadata'>" .
        "<source src='$src'>" .
        "<a href='$src'>$content</a>" .
        "</video>" .
        "</div>" .
        "<a class='attachment_link' href='$src' target='_blank'>{{link}}</a>" .
        "</div>\n\n";
} // bb_process_video
//------------------------------------------------------------------------------
function bb_process_youtube($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    $content = trim($content);
    $code = $content;
    
    if (preg_match("/\\[attachment(\\d*)=([^\\]]+)\\]/i", $code, $matches)) {
        return $content;
    }
    
    $appendix = "";
    
    if (preg_match('/https:\\/\\/[^\\/]*youtube.com\\/shorts\\/([A-z0-9=\-]+)$/i', $content, $matches)) {
        $code = $matches[1];
    } elseif (preg_match('/v=([A-z0-9=\-]+?)(&.*)?$/i', $content, $matches)) {
        $code = $matches[1];
    } elseif (preg_match('/https:\\/\\/[^\\/]*youtu\\.be\\/([A-z0-9=\-]+?)\\?list=/i', $content, $matches)) {
        $code = $matches[1];
    } elseif (preg_match('/https:\\/\\/[^\\/]*youtube\\.com\\/shorts\\/([A-z0-9=\-]+?)\?feature=share/i', $content, $matches)) {
        $code = $matches[1];
    } elseif (preg_match('/https:\\/\\/[^\\/]*youtube.com\\/watch\\?(time_continue=\\d+)&v=([A-z0-9=\-]+)(&.*)?/i', $content, $matches)) {
        $code = $matches[1];
    } elseif (preg_match('/https:\\/\\/[^\\/]*youtu\\.be\\/([A-z0-9=\-]+?)(\\?t=(.*))?$/i', $content, $matches)) {
        $code = $matches[1];
        
        $appendix = val_or_empty($matches["2"]);
    }
    
    $apikey = "";
    if (defined('YOUTUBE_API_KEY')) {
        $apikey = YOUTUBE_API_KEY;
    }
    
    $bbcode_text = "[" . $name . "]" . $content . "[/" . $name . "]";
    
    return gen_youtube_html($code, $apikey, $appendix, $bbcode_text);
} // bb_process_youtube
//------------------------------------------------------------------------------
function check_youtube_url($url, &$content, $message_mode)
{
    $apikey = "";
    if (defined('YOUTUBE_API_KEY')) {
        $apikey = YOUTUBE_API_KEY;
    }
    
    if (preg_match('/https:\\/\\/[^\\/]*youtube.com\\/shorts\\/([A-z0-9=\-]+)$/i', $url, $matches)) {
        if ($message_mode != "message") {
            $content = "\n[{{video}}: YouTube]\n\n";
            return true;
        }
        
        $content = gen_youtube_html($matches[1], $apikey, "", $url);
        return true;
    }
    
    if (preg_match('/https:\\/\\/[^=]+youtu[^=]+v=([A-z0-9=\-]+?)(&.*)?$/i', $url, $matches)) {
        if ($message_mode != "message") {
            $content = "\n[{{video}}: YouTube]\n\n";
            return true;
        }
        
        $content = gen_youtube_html($matches[1], $apikey, "", $url);
        return true;
    }
    
    if (preg_match('/https:\\/\\/[^\\/]*youtu\\.be\\/([A-z0-9=\-]+?)\\?list=/i', $url, $matches)) {
        if ($message_mode != "message") {
            $content = "\n[{{video}}: YouTube]\n\n";
            return true;
        }
        
        $content = gen_youtube_html($matches[1], $apikey, "", $url);
        return true;
    }
    
    if (preg_match('/https:\\/\\/[^\\/]*youtube\\.com\\/shorts\\/([A-z0-9=\-]+?)\?feature=share/i', $url, $matches)) {
        if ($message_mode != "message") {
            $content = "\n[{{video}}: YouTube]\n\n";
            return true;
        }
        
        $content = gen_youtube_html($matches[1], $apikey, "", $url);
        return true;
    }
    
    if (preg_match('/https:\\/\\/[^\\/]*youtu\\.be\\/([A-z0-9=\-]+?)(\\?t=(.*))?$/i', $url, $matches)) {
        if ($message_mode != "message") {
            $content = "\n[{{video}}: YouTube]\n\n";
            return true;
        }
        
        $content = gen_youtube_html($matches[1], $apikey, val_or_empty($matches["2"]), $url);
        return true;
    }
    
    if (preg_match('/https:\\/\\/[^\\/]*youtube.com\\/watch\\?(time_continue=\\d+)&v=([A-z0-9=\-]+)(&.*)?/i', $url, $matches)) {
        if ($message_mode != "message") {
            $content = "\n[{{video}}: YouTube]\n\n";
            return true;
        }
        
        $content = gen_youtube_html($matches[2], $apikey, val_or_empty($matches["1"]), $url);
        return true;
    }
    
    return false;
} // check_youtube_url
//------------------------------------------------------------------------------
function bb_process_gmap($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    $content = trim($content);
    
    $apikey = "";
    if (defined('GMAPS_API_KEY')) {
        $apikey = GMAPS_API_KEY;
    }
    
    $coordinates = html_entity_decode($content);
    
    if (preg_match('/([^\/]+(N|S))(\+| +)([^\/]+(E|W))/i', $coordinates, $matches)) {
        $coordinates = urldecode($matches[1] . "," . $matches[4]);
    } elseif (preg_match('/@([^\/,]+),([^\/,]+)/i', $coordinates, $matches)) {
        $coordinates = urldecode($matches[1] . "," . $matches[2]);
    }
    
    $bbcode_text = "[" . $name . "]" . $content . "[/" . $name . "]";
    
    return gen_gmaps_html($coordinates, $apikey, $bbcode_text);
} // bb_process_gmap
//------------------------------------------------------------------------------
function bb_process_instagram($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    $content = trim($content);
    $code = $content;
    
    if (preg_match("/\\[attachment(\\d*)=([^\\]]+)\\]/i", $code, $matches)) {
        return $content;
    }
    
    if (preg_match('/https:\\/\\/www\\.instagram\\.com\\/([^\\/&\\?]+)\\/([^\\/&\\?]+)\\/?/i', $content, $matches)) {
        $code = $matches[2];
    } elseif (preg_match('/https:\\/\\/www\\.instagram\\.com\\/[^\\/]+\\/([^\\/&\\?]+)\\/([^\\/&\\?]+)\\/?/i', $content, $matches)) {
        $code = $matches[2];
    }
    
    $bbcode_text = "[" . $name . "]" . $content . "[/" . $name . "]";
    
    return gen_instagram_html($code, $bbcode_text);
} // bb_process_instagram
//------------------------------------------------------------------------------
function check_instagram_url($url, &$content, $message_mode)
{
    if (preg_match('/https:\\/\\/www\\.instagram\\.com\\/([^\\/&\\?]+)\\/([^\\/&\\?]+)\\/?/i', $url, $matches)) {
        if ($message_mode != "message") {
            $content = "\n[{{video}}: Instagram]\n\n";
            return true;
        }
        
        $content = gen_instagram_html($matches[2], $url);
        return true;
    } elseif (preg_match('/https:\\/\\/www\\.instagram\\.com\\/[^\\/]+\\/([^\\/&\\?]+)\\/([^\\/&\\?]+)\\/?/i', $url, $matches)) {
        if ($message_mode != "message") {
            $content = "\n[{{video}}: Instagram]\n\n";
            return true;
        }
        
        $content = gen_instagram_html($matches[2], $url);
        return true;
    }
    
    return false;
} // check_instagram_url
//------------------------------------------------------------------------------
function bb_process_reddit($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    $content = trim($content);
    $code = $content;
    
    if (preg_match("/\\[attachment(\\d*)=([^\\]]+)\\]/i", $code, $matches)) {
        return $content;
    }
    
    if (preg_match('/https:\\/\\/www\\.reddit\\.com\\/.*comments\\/([^\\/]+)\\/.*/i', $content, $matches)) {
        $code = $matches[1];
    }
    
    $bbcode_text = "[" . $name . "]" . $content . "[/" . $name . "]";
    
    return gen_reddit_html($code, $bbcode_text);
} // bb_process_reddit
//------------------------------------------------------------------------------
function check_reddit_url($url, &$content, $message_mode)
{
    if (preg_match('/https:\\/\\/www\\.reddit\\.com\\/.*comments\\/([^\\/]+)\\/.*/i', $url, $matches)) {
        if ($message_mode != "message") {
            $content = "\n[{{video}}: Reddit]\n\n";
            return true;
        }
        
        $content = gen_reddit_html($matches[1], $url);
        return true;
    }
    
    return false;
} // check_reddit_url
//------------------------------------------------------------------------------
function bb_process_tiktok($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    $content = trim($content);
    $code = $content;
    
    if (preg_match("/\\[attachment(\\d*)=([^\\]]+)\\]/i", $code, $matches)) {
        return $content;
    }
    
    if (preg_match('/https:\\/\\/www\\.tiktok\\.com\\/.*video\\/(\\d+).*/i', $content, $matches)) {
        $code = $matches[1];
    }
    
    $bbcode_text = "[" . $name . "]" . $content . "[/" . $name . "]";
    
    return gen_tiktok_html($code, $bbcode_text);
} // bb_process_tiktok
//------------------------------------------------------------------------------
function check_tiktok_url($url, &$content, $message_mode)
{
    if (preg_match('/https:\\/\\/www\\.tiktok\\.com\\/.*video\\/(\\d+).*/i', $url, $matches)) {
        if ($message_mode != "message") {
            $content = "\n[{{video}}: TikTok]\n\n";
            return true;
        }
        
        $content = gen_tiktok_html($matches[1], $url);
        return true;
    }
    
    return false;
} // check_tiktok_url
//------------------------------------------------------------------------------
function bb_process_radikal($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    $content = trim($content);
    $code = $content;
    
    if (preg_match("/\\[attachment(\\d*)=([^\\]]+)\\]/i", $code, $matches)) {
        return $content;
    }
    
    if (preg_match('/https:\\/\\/radikal\\.ru\\/video\\/([^\\/]+)\\/?/i', $content, $matches)) {
        $code = $matches[1];
    }
    
    $bbcode_text = "[" . $name . "]" . $content . "[/" . $name . "]";
    
    return gen_radikal_html($code, $bbcode_text);
} // bb_process_radikal
//------------------------------------------------------------------------------
function check_radikal_url($url, &$content, $message_mode)
{
    if (preg_match('/https:\\/\\/radikal\\.ru\\/video\\/([^\\/]+)\\/?/i', $url, $matches)) {
        if ($message_mode != "message") {
            $content = "\n[{{video}}: Radikal]\n\n";
            return true;
        }
        
        $content = gen_radikal_html($matches[1], $url);
        return true;
    }
    
    return false;
} // check_radikal_url
//------------------------------------------------------------------------------
function bb_process_vimeo($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    $content = trim($content);
    $code = $content;
    
    if (preg_match("/\\[attachment(\\d*)=([^\\]]+)\\]/i", $code, $matches)) {
        return $content;
    }
    
    $start = 0;
    
    if (preg_match('/https:\\/\\/player\\.vimeo\\.com\\/video\\/(.*)$/i', $content, $matches)) {
        $code = $matches[1];
    } elseif (preg_match('/https:\\/\\/vimeo\\.com\\/channels\\/staffpicks\\/(.*)$/i', $content, $matches)) {
        $code = $matches[1];
    } elseif (preg_match('/https:\\/\\/vimeo\\.com\\/([^#]*)(#at=(.*))?$/i', $content, $matches)) {
        $code = $matches[1];
        $start = val_or_empty($matches[3]);
    }
    
    $bbcode_text = "[" . $name . "]" . $content . "[/" . $name . "]";
    
    return gen_vimeo_html($code, $start, $bbcode_text);
} // bb_process_vimeo
//------------------------------------------------------------------------------
function check_vimeo_url($url, &$content, $message_mode)
{
    if (preg_match('/https:\\/\\/vimeo\\.com\\/([A-z0-9=\-]+?)(\\#at=(.*))?$/i', $url, $matches)) {
        if ($message_mode != "message") {
            $content = "\n[{{video}}: Vimeo]\n\n";
            return true;
        }
        
        $content = gen_vimeo_html($matches[1], val_or_empty($matches["3"]), $url);
        return true;
    }
    
    return false;
} // check_vimeo_url
//------------------------------------------------------------------------------
function bb_process_rutube($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    $content = trim($content);
    $code = $content;
    
    if (preg_match("/\\[attachment(\\d*)=([^\\]]+)\\]/i", $code, $matches)) {
        return $content;
    }
    
    $start = 0;
    
    if (preg_match('/https:\\/\\/rutube\\.ru\\/video\\/([^\\/\\?]+).*bmstart=(\\d+).*/i', $content, $matches)) {
        $code = $matches[1];
        $start = val_or_empty($matches[2]);
    } elseif (preg_match('/https:\\/\\/rutube\\.ru\\/video\\/([^\\/\\?]+).*$/i', $content, $matches)) {
        $code = $matches[1];
    }
    
    $bbcode_text = "[" . $name . "]" . $content . "[/" . $name . "]";
    
    return gen_rutube_html($code, $start, $bbcode_text, $bbcode_text);
} // bb_process_rutube
//------------------------------------------------------------------------------
function check_rutube_url($url, &$content, $message_mode)
{
    if (preg_match('/https:\\/\\/rutube\\.ru\\/video\\/([^\\/\\?]+).*bmstart=(\\d+).*/i', $url, $matches)) {
        if ($message_mode != "message") {
            $content = "\n[{{video}}: RuTube]\n\n";
            return true;
        }
        
        $content = gen_rutube_html($matches[1], val_or_empty($matches["2"]), $url);
        return true;
    }
    
    if (preg_match('/https:\\/\\/rutube\\.ru\\/video\\/([^\\/\\?]+).*$/i', $url, $matches)) {
        if ($message_mode != "message") {
            $content = "\n[{{video}}: RuTube]\n\n";
            return true;
        }
        
        $content = gen_rutube_html($matches[1], 0, $url);
        return true;
    }
    
    return false;
} // check_rutube_url
//------------------------------------------------------------------------------
function bb_process_vkvideo($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    $content = trim($content);
    $code = $content;
    
    if (preg_match("/\\[attachment(\\d*)=([^\\]]+)\\]/i", $code, $matches)) {
        return $content;
    }
    
    if (preg_match('/video(.*)$/i', $content, $matches)) {
        $code = $matches[1];
    }
    elseif (preg_match('/clip(.*)$/i', $content, $matches)) {
        $code = $matches[1];
    }
    elseif (preg_match('/https:\/\/(m\\.)*vk\.com\/video(.*)$/i', $content, $matches)) {
        $code = $matches[2];
    }
    elseif (preg_match('/https:\/\/(m\\.)*vk\.com\/clip(.*)$/i', $content, $matches)) {
        $code = $matches[2];
    }
    
    $bbcode_text = "[" . $name . "]" . $content . "[/" . $name . "]";
    
    return gen_vkvideo_html($code, $bbcode_text);
} // bb_process_vkvideo
//------------------------------------------------------------------------------
function check_vkvideo_url($url, &$content, $message_mode)
{
    if (preg_match('/https:\/\/(m\\.)*vk\.com\/video(.*)(\\?.*)?$/i', $url, $matches)) {
        if ($message_mode != "message") {
            $content = "\n[{{video}}: VK]\n\n";
            return true;
        }
        
        $content = gen_vkvideo_html($matches[2], $url);
        return true;
    }
    
    if (preg_match('/https:\/\/(m\\.)*vk\.com\/clip(.*)(\\?.*)?$/i', $url, $matches)) {
        if ($message_mode != "message") {
            $content = "\n[{{video}}: VK]\n\n";
            return true;
        }
        
        $content = gen_vkvideo_html($matches[2], $url);
        return true;
    }

    return false;
} // check_vkvideo_url
//------------------------------------------------------------------------------
function bb_process_coub($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    $content = trim($content);
    $code = $content;
    
    if (preg_match("/\\[attachment(\\d*)=([^\\]]+)\\]/i", $code, $matches)) {
        return $content;
    }
    
    if (preg_match('/http(s)?:\\/\\/coub\\.com\\/view\\/([^\\/\\?]+)$/i', $content, $matches)) {
        $code = $matches[2];
    }
    
    $bbcode_text = "[" . $name . "]" . $content . "[/" . $name . "]";
    
    return gen_coub_html($code, $bbcode_text);
} // bb_process_coub
//------------------------------------------------------------------------------
function check_coub_url($url, &$content, $message_mode)
{
    if (preg_match('/http(s)?:\\/\\/coub\\.com\\/view\\/([^\\/\\?]+)$/i', $url, $matches)) {
        if ($message_mode != "message") {
            $content = "\n[{{video}}: Coub]\n\n";
            return true;
        }
        
        $content = gen_coub_html($matches[2], $url);
        return true;
    }
    
    return false;
} // check_coub_url
//------------------------------------------------------------------------------
function bb_process_telegram($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    $content = trim($content);
    $code = $content;
    
    if (preg_match("/\\[attachment(\\d*)=([^\\]]+)\\]/i", $code, $matches)) {
        return $content;
    }
    
    if (preg_match('/(https:\\/\\/)?t\\.me\\/(.+?)(\?.*?)?$/i', $content, $matches)) {
        $code = $matches[2];
    }
    
    $bbcode_text = "[" . $name . "]" . $content . "[/" . $name . "]";
    
    return gen_telegram_html($code, $bbcode_text);
} // bb_process_telegram
//------------------------------------------------------------------------------
function check_telegram_url($url, &$content, $message_mode)
{
    if (preg_match('/https:\\/\\/t\\.me\\/(.+?\\/.+?)(\?.*?)?$/i', $url, $matches)) {
        if ($message_mode != "message") {
            $content = "\n[{{video}}: Telegram]\n\n";
            return true;
        }
        
        $content = gen_telegram_html($matches[1], $url);
        return true;
    }
    
    return false;
} // check_telegram_url
//------------------------------------------------------------------------------
function bb_process_fbvideo($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    $content = trim($content);
    $code = $content;
    
    if (preg_match("/\\[attachment(\\d*)=([^\\]]+)\\]/i", $code, $matches)) {
        return $content;
    }
    
    if (preg_match('/http(s)?:\\/\\/www\\.facebook\\.com\\/video\\.php\\?v=([^\\/\\?]+)$/i', $content, $matches)) {
        $code = $matches[2];
    } elseif (preg_match('/http(s)?:\\/\\/www\\.facebook\\.com\\/.+\\/videos\\/([^\\/\\?]+)\\/.*$/i', $content, $matches)) {
        $code = $matches[2];
    } elseif (preg_match('/http(s)?:\\/\\/www.facebook\\.com\\/watch\\/\\?v=(\d+)/i', $content, $matches)) {
        $code = $matches[2];
    }
    
    $bbcode_text = "[" . $name . "]" . $content . "[/" . $name . "]";
    
    return gen_fbvideo_html($code, $bbcode_text);
} // bb_process_fbvideo
//------------------------------------------------------------------------------
function check_fbvideo_url($url, &$content, $message_mode)
{
    if (preg_match('/http(s)?:\\/\\/www\\.facebook\\.com\\/video\\.php\\?v=([^\\/\\?]+)$/i', $url, $matches)) {
        if ($message_mode != "message") {
            $content = "\n[{{video}}: Facebook]\n\n";
            return true;
        }
        
        $content = gen_fbvideo_html($matches[2], $url);
        return true;
    } elseif (preg_match('/http(s)?:\\/\\/www\\.facebook\\.com\\/.+\\/videos\\/([^\\/\\?]+)\\/.*$/i', $url, $matches)) {
        if ($message_mode != "message") {
            $content = "\n[{{video}}: Facebook]\n\n";
            return true;
        }
        
        $content = gen_fbvideo_html($matches[2], $url);
        return true;
    } elseif (preg_match('/http(s)?:\\/\\/www.facebook\\.com\\/watch\\/\\?v=(\d+)/i', $url, $matches)) {
        if ($message_mode != "message") {
            $content = "\n[{{video}}: Facebook]\n\n";
            return true;
        }
        
        $content = gen_fbvideo_html($matches[2], $url);
        return true;
    }
    
    return false;
} // check_fbvideo_url
//------------------------------------------------------------------------------
function bb_process_twitter($bbcode, $action, $name, $default, $params, $content)
{
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    $content = trim($content);
    $code = $content;
    
    if (preg_match("/\\[attachment(\\d*)=([^\\]]+)\\]/i", $code, $matches)) {
        return $content;
    }
    
    if (preg_match('/http(s)?:\\/\\/twitter.com\\/.+\\/status\\/(\d+)(.*?)?$/i', $content, $matches)) {
        $code = $matches[2];
    }
    
    $bbcode_text = "[" . $name . "]" . $content . "[/" . $name . "]";
    
    return gen_twitter_html($code, $bbcode_text);
} // bb_process_twitter
//------------------------------------------------------------------------------
function check_video_url($url, &$content, $message_mode)
{
    if (preg_match('/.*\\.mp4$/i', $url, $matches)) {
        if ($message_mode != "message") {
            $content = "\n[{{video}}]\n\n";
            return true;
        }
        
        $src = trim($url);
        $txt = urldecode($src);
        
        $src = str_replace("&", "&amp;", $src);
        
        $content = "<div class='emb_wrapper'>";
        $content .= "<div class='short_video'>";
        $content .= "<a class='emb_video_short_container' href='#' onclick='return show_embedded_video(this)'>" . escape_html(text("ShowEmbeddedVideo")) . "</a>";
        $content .= "</div><div class='emb_video_container detailed_video'>";
        $content .= "<video controls='controls' preload='metadata'>";
        $content .= "<source src='$src'>";
        $content .= "<a href='$src'>$txt</a>";
        $content .= "</video>";
        $content .= "</div>";
        $content .= "<a class='attachment_link' href='$src' target='_blank'>{{link}}</a>";
        $content .= "</div>";
        
        return true;
    }
    
    return false;
} // check_video_url
//------------------------------------------------------------------------------
function check_audio_url($url, &$content, $message_mode)
{
    if (preg_match('/.*\\.mp3$/i', $url, $matches)) {
        if ($message_mode != "message") {
            $content = "\n[{{audio}}]\n\n";
            return true;
        }
        
        $src = trim($url);
        $txt = urldecode($src);
        
        $src = str_replace("&", "&amp;", $src);
        
        $content = "<div class='emb_wrapper'><audio controls='controls' preload='metadata' autobuffer='metadata'>";
        $content .= "<source src='$src'>";
        $content .= "<a href='$src'>$txt</a>";
        $content .= "</audio>";
        $content .= "<a class='attachment_link' href='$src' target='_blank'>{{link}}</a>";
        $content .= "</div>";
        
        return true;
    }
    
    return false;
} // check_audio_url
//------------------------------------------------------------------------------
function check_own_url($url, &$content, $message_mode)
{
    if (stripos($url, get_host_address()) === false) {
        return false;
    }
    
    if (preg_match("/view_profile\\.php\\?uid=(\\d+)$/", $url, $matches)) {
        $content = "[uid=$matches[1]]";
        return true;
    }
    
    if (preg_match("/&msg=(\\d+)$/", $url, $matches)) {
        $content = "[mid=$matches[1]]";
        return true;
    }

    if (preg_match("/\?event=(\\d+)$/", $url, $matches)) {
        $content = "[mevt=$matches[1]]";
        return true;
    }
    
    return false;
} // check_own_url
//------------------------------------------------------------------------------
function check_special_url($url, &$content, $message_mode)
{
    if (check_own_url($url, $content, $message_mode)) {
        return true;
    }
    
    if (check_audio_url($url, $content, $message_mode)) {
        return true;
    }
    
    if (check_video_url($url, $content, $message_mode)) {
        return true;
    }
    
    if (check_youtube_url($url, $content, $message_mode)) {
        return true;
    }
    
    if (check_instagram_url($url, $content, $message_mode)) {
        return true;
    }
    
    if (check_radikal_url($url, $content, $message_mode)) {
        return true;
    }
    
    /*
    if (check_reddit_url($url, $content, $message_mode)) {
        return true;
    }
    */
    
    if (check_tiktok_url($url, $content, $message_mode)) {
        return true;
    }
    
    if (check_vimeo_url($url, $content, $message_mode)) {
        return true;
    }
    
    if (check_rutube_url($url, $content, $message_mode)) {
        return true;
    }
    
    if (check_vkvideo_url($url, $content, $message_mode)) {
        return true;
    }
    
    if (check_coub_url($url, $content, $message_mode)) {
        return true;
    }
    
    if (check_telegram_url($url, $content, $message_mode)) {
        return true;
    }
    
    /*
    if (check_fbvideo_url($url, $content, $message_mode)) {
        return true;
    }
    */
    
    return false;
} // check_special_url
//------------------------------------------------------------------------------
function bb_process_code($bbcode, $action, $name, $default, $params, $content)
{
    global $SUPPORTED_CODES;
    
    $bbcode->has_code = 1;
    
    if ($action == BBCODE_CHECK) {
        return true;
    }
    
    $language = $default;
    if (empty($SUPPORTED_CODES) || !in_array($language, array_keys($SUPPORTED_CODES))) {
        $language = "plaintext";
    }
    
    $lang_name = "";
    if ($language != "plaintext" && !empty($SUPPORTED_CODES[$language])) {
        $lang_name = ": " . $SUPPORTED_CODES[$language];
    }
    
    if ($language == "text") {
        $language = "plaintext";
    }
    
    // temporary change the special placeholders
    
    $content = preg_replace("/\[uid=(\d+)\]/i", "__uid=$1__", $content);
    $content = preg_replace("/\[mid=(\d+)\]/i", "__mid=$1__", $content);
    $content = preg_replace("/\[mevt=(\d+)\]/i", "__mevt=$1__", $content);
    $content = preg_replace("/\[msg=(\d+)\]/i", "__msg=$1__", $content);
    $content = preg_replace("/\[attachment(\d+)?(=\d+)?\]/i", "__attachment$1$2__", $content);
    $content = preg_replace("/(@|%)([^%@\r\n\t]+?)\\1/iu", "__appeal$1=$2$1__", $content);
    
    $content = trim($content, "\r\n");
    
    $content = str_replace("\t", "  ", $content);
    
    $lines = explode("\n", $content);
    $cnt = count($lines);
    
    $content = "";
    
    $line_str = "";
    for ($i = 1; $i <= $cnt; $i++) {
        $line_str .= $i . ".<br />";
        
        $line = rtrim($lines[$i - 1]);
        
        $line = str_replace(" ", "&nbsp;", $line);
        
        $line = preg_replace("/==&gt;(.*)&lt;==/", "<span class='code_highlight'>\\1</span>", $line);
        
        $line = preg_replace("/==&gt;(.*)/", "<span class='code_highlight'>\\1</span>", $line);
        
        $content .= $line . "\r\n";
    }
    
    $content = trim($content, "\r\n");
    
    return '<div class="code_wrapper" data-code="' . $language . '"><div class="code_header" onclick="expand_code(this)">' . escape_html(text("Code") . $lang_name) . '</div><div class="code"><div class="line_numbers">' . $line_str . '</div><div class="code_area"><pre><code class="' . $language . '" data-code="' . $language . '">' . $content . '</code></pre></div></div></div>';
} // bb_process_code
//------------------------------------------------------------------------------
function tags_to_lowercase(&$text)
{
    $tags = array(
        "url",
        "code",
        "img",
        "latex",
        "gif",
        "fixed",
        "list",
        "nlist",
        "quote",
        "smile",
        "spoiler",
        "table",
        
        "audio",
        "video",
        "coub",
        "telegram",
        "fbvideo",
        "vkvideo",
        "instagram",
        "radikal",
        "rutube",
        "twitter",
        "vimeo",
        "youtube"
    );
    
    foreach ($tags as $tag) {
        $text = preg_replace("/\\[$tag([^\\]]*)\\]/i", "[$tag\\1]", $text);
        $text = preg_replace("/\\[\\/$tag\\]/i", "[/$tag]", $text);
    }
} // tags_to_lowercase
//------------------------------------------------------------------------------
function split_by_bracket($str, &$chunks)
{
    $chunks = array($str, "");
    
    $l = strlen($str);
    $bpos = -1;
    $bcnt = 0;
    for ($i = 0; $i < $l; $i++) {
        if ($str[$i] == "]") {
            if ($bcnt == 0) {
                $bpos = $i;
                break;
            } else {
                $bcnt--;
            }
        }
        
        if ($str[$i] == "[") {
            $bcnt++;
        }
    }
    
    if ($bpos != -1) {
        $chunks[0] = substr($str, 0, $bpos);
        $chunks[1] = substr($str, $bpos);
        return;
    }
    
    if (preg_match("~(.+?)(\\[/.*)~i", $str, $matches)) {
        $chunks = array($matches[1], $matches[2]);
        return true;
    }
} // split_by_bracket
//------------------------------------------------------------------------------
function fix_links_callback($m)
{
    $chunks = array();
    split_by_bracket($m[2], $chunks);
    
    $chunks[0] = str_replace("[", xrawurlencode("["), $chunks[0]);
    $chunks[0] = str_replace("]", xrawurlencode("]"), $chunks[0]);
    $chunks[0] = str_replace("|", xrawurlencode("|"), $chunks[0]);
    
    return val_or_empty($m[1]) . $chunks[0] . $chunks[1] . val_or_empty($m[3]);
} // fix_links_callback
//------------------------------------------------------------------------------
function fix_links(&$text)
{
    $re = "~(\()?((?:https?|ftp)+://[^<>\"[:space:]]+[^\\.,\"[:space:]])(?(1)(\)))~x";
    
    $text = preg_replace_callback($re, "fix_links_callback", $text);
} // fix_links
//------------------------------------------------------------------------------
function fix_quot(&$text)
{
    $matches = array();
    
    $open_cnt = preg_match_all("/\\[quote?(=[^\\]]+)?\\]/", $text, $matches);
    $close_cnt = preg_match_all("/\\[\\/quote\\]/", $text, $matches);
    
    $missing_cnt = $open_cnt - $close_cnt;
    
    if ($missing_cnt > 0) {
        $text .= str_repeat("\n[/quote]\n", $missing_cnt);
    }
} // fix_quot
//------------------------------------------------------------------------------
function check_relative_url($url, $replacement = "")
{
    if (stripos($url, get_host_address()) === 0) {
        $url = str_ireplace(get_host_address() . get_url_path(), $replacement, $url);
    }
    
    if (stripos($url, "http://base") === 0) {
        $url = str_ireplace("http://base" . get_url_path(), $replacement, $url);
    }
    
    return $url;
} // check_relative_url
//------------------------------------------------------------------------------
function remove_post_citations(&$input, &$output)
{
    $output = $input;
    
    $bbcode = new BBCode;
    $bbcode->ClearRules();
    $bbcode->ClearSmileys();
    $bbcode->SetDetectURLs(true);
    $bbcode->SetCheckSpecialUrl(true);
    
    $bbcode->SetEnableSmileys(false);
    
    //----------------------------------------------
    $bbcode->AddRule('quote',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_remove_quotes',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    
    tags_to_lowercase($output);
    
    $output = $bbcode->Parse($output);
} // remove_post_citations
//------------------------------------------------------------------------------
function remove_nested_quotes(&$input, &$output, $limit)
{
    $output = $input;
    
    //debug_message("-------------------");
    //debug_message($input);
    //debug_message("====");
    
    if ($limit < 1) {
        return true;
    }
    
    if (!preg_match_all('/(<div[^<>]*>|<\/div>)/i', $output, $matches, PREG_OFFSET_CAPTURE)) {
        return true;
    }
    
    //debug_message(print_r($matches, true));
    
    $nest_level = 0;
    $cut_start_position = false;
    $inner_div_count = array();
    $offset_through_removing = 0;
    
    foreach ($matches[0] as $token) {
        if (strpos($token[0], '<div') !== false) {
            if (strpos($token[0], 'class="quote ') !== false) {
                $nest_level++;
                //debug_message("nest_level increased to:" . $nest_level);
                
                // nest level riched the limit
                // define the cut start position
                if ($nest_level == $limit && $cut_start_position === false) {
                    $cut_start_position = $token[1] + strlen($token[0]) - $offset_through_removing;
                    //debug_message("cut_start_position:" . $cut_start_position);
                }
            }
            
            if (empty($inner_div_count[$nest_level])) {
                $inner_div_count[$nest_level] = 0;
            }
            
            $inner_div_count[$nest_level]++;
            //debug_message("inner_div_count[$nest_level] increased to:" . $inner_div_count[$nest_level] . " - " . $token[0]);
        }
        
        if (strpos($token[0], '</div>') !== false) {
            if (empty($inner_div_count[$nest_level])) {
                $inner_div_count[$nest_level] = 0;
            }
            
            $inner_div_count[$nest_level]--;
            //debug_message("inner_div_count[$nest_level] decreased to:" . $inner_div_count[$nest_level] . " - " . $token[0]);
            
            if ($inner_div_count[$nest_level] == 0 && $nest_level > 0) {
                // the matching closing div is found
                if ($nest_level == $limit && $cut_start_position !== false) {
                    $cut_end_position = $token[1] - $offset_through_removing;
                    //debug_message("cut_end_position:" . $cut_end_position);
                    
                    // do replacement
                    $replacement = "...";
                    $remove_length = $cut_end_position - $cut_start_position;
                    $output = substr_replace($output, $replacement, $cut_start_position, $remove_length);
                    $offset_through_removing += $remove_length - strlen($replacement);
                    
                    $cut_start_position = false;
                }
                
                $nest_level--;
                //debug_message("nest_level decreased to:" . $nest_level);
            }
        }
    } // foreach
    
    //debug_message("====");
    //debug_message($output);
    //debug_message("-------------------");
} // remove_nested_quotes
//------------------------------------------------------------------------------
function remove_nested_spoilers(&$input, &$output, $limit)
{
    $output = $input;
    
    //debug_message("-------------------");
    //debug_message($input);
    //debug_message("====");
    
    if ($limit < 1) {
        return true;
    }
    
    if (!preg_match_all('/(<div[^<>]*>|<\/div>)/i', $output, $matches, PREG_OFFSET_CAPTURE)) {
        return true;
    }
    
    //debug_message(print_r($matches, true));
    
    $nest_level = 0;
    $cut_start_position = false;
    $inner_div_count = array();
    $offset_through_removing = 0;
    
    foreach ($matches[0] as $token) {
        if (strpos($token[0], '<div') !== false) {
            if (strpos($token[0], 'class="spoiler"') !== false) {
                $nest_level++;
                //debug_message("nest_level increased to:" . $nest_level);
                
                // nest level riched the limit
                // define the cut start position
                if ($nest_level == $limit && $cut_start_position === false) {
                    $cut_start_position = $token[1] + strlen($token[0]) - $offset_through_removing;
                    //debug_message("cut_start_position:" . $cut_start_position);
                }
            }
            
            if (empty($inner_div_count[$nest_level])) {
                $inner_div_count[$nest_level] = 0;
            }
            
            $inner_div_count[$nest_level]++;
            //debug_message("inner_div_count[$nest_level] increased to:" . $inner_div_count[$nest_level] . " - " . $token[0]);
        }
        
        if (strpos($token[0], '</div>') !== false) {
            if (empty($inner_div_count[$nest_level])) {
                $inner_div_count[$nest_level] = 0;
            }
            
            $inner_div_count[$nest_level]--;
            //debug_message("inner_div_count[$nest_level] decreased to:" . $inner_div_count[$nest_level] . " - " . $token[0]);
            
            if ($inner_div_count[$nest_level] == 0 && $nest_level > 0) {
                // the matching closing div is found
                if ($nest_level == $limit && $cut_start_position !== false) {
                    $cut_end_position = $token[1] - $offset_through_removing;
                    //debug_message("cut_end_position:" . $cut_end_position);
                    
                    // do replacement
                    $replacement = "...";
                    $remove_length = $cut_end_position - $cut_start_position;
                    $output = substr_replace($output, $replacement, $cut_start_position, $remove_length);
                    $offset_through_removing += $remove_length - strlen($replacement);
                    
                    $cut_start_position = false;
                }
                
                $nest_level--;
                //debug_message("nest_level decreased to:" . $nest_level);
            }
        }
    } // foreach
    
    //debug_message("====");
    //debug_message($output);
    //debug_message("-------------------");
} // remove_nested_spoilers
//------------------------------------------------------------------------------
function remove_nested_quotes_bb(&$input, &$output, $limit)
{
    $output = $input;
    
    //debug_message("-------------------");
    //debug_message("BEFORE:");
    //debug_message($input);
    //debug_message("====");
    
    if ($limit < 1) {
        return true;
    }
    
    if (!preg_match_all('/(\[quote[^\[\]]*\]|\[\/quote\])/i', $output, $matches, PREG_OFFSET_CAPTURE)) {
        return true;
    }
    
    $nest_level = 0;
    $cut_start_position = false;
    $offset_through_removing = 0;
    
    foreach ($matches[0] as $token) {
        if (strpos($token[0], '[quote') !== false) {
            $nest_level++;
            //debug_message("nest_level increased to:" . $nest_level);
            
            // nest level riched the limit
            // define the cut start position
            if ($nest_level == $limit && $cut_start_position === false) {
                $cut_start_position = $token[1] - $offset_through_removing;
                //debug_message("cut_start_position:" . $cut_start_position);
            }
        }
        
        if (strpos($token[0], '[/quote]') !== false) {
            
            if ($nest_level > 0) {
                // the matching closing div is found
                if ($nest_level == $limit && $cut_start_position !== false) {
                    $cut_end_position = $token[1] + strlen($token[0]) - $offset_through_removing;
                    //debug_message("cut_end_position:" . $cut_end_position);
                    
                    // do replacement
                    $replacement = "...";
                    $remove_length = $cut_end_position - $cut_start_position;
                    $output = substr_replace($output, $replacement, $cut_start_position, $remove_length);
                    $offset_through_removing += $remove_length - strlen($replacement);
                    
                    $cut_start_position = false;
                }
                
                $nest_level--;
                //debug_message("nest_level decreased to:" . $nest_level);
            }
        }
    } // foreach
    
    //debug_message("====");
    //debug_message("AFTER:");
    //debug_message($output);
    //debug_message("-------------------");
} // remove_nested_quotes_bb
//------------------------------------------------------------------------------
function remove_nested_spoilers_bb(&$input, &$output, $limit)
{
    $output = $input;
    
    //debug_message("-------------------");
    //debug_message("BEFORE:");
    //debug_message($input);
    //debug_message("====");
    
    if ($limit < 1) {
        return true;
    }
    
    if (!preg_match_all('/(\[spoiler[^\[\]]*\]|\[\/spoiler\])/i', $output, $matches, PREG_OFFSET_CAPTURE)) {
        return true;
    }
    
    $nest_level = 0;
    $cut_start_position = false;
    $offset_through_removing = 0;
    
    foreach ($matches[0] as $token) {
        if (strpos($token[0], '[spoiler') !== false) {
            $nest_level++;
            //debug_message("nest_level increased to:" . $nest_level);
            
            // nest level riched the limit
            // define the cut start position
            if ($nest_level == $limit && $cut_start_position === false) {
                $cut_start_position = $token[1] - $offset_through_removing;
                //debug_message("cut_start_position:" . $cut_start_position);
            }
        }
        
        if (strpos($token[0], '[/spoiler]') !== false) {
            
            if ($nest_level > 0) {
                // the matching closing div is found
                if ($nest_level == $limit && $cut_start_position !== false) {
                    $cut_end_position = $token[1] + strlen($token[0]) - $offset_through_removing;
                    //debug_message("cut_end_position:" . $cut_end_position);
                    
                    // do replacement
                    $replacement = "...";
                    $remove_length = $cut_end_position - $cut_start_position;
                    $output = substr_replace($output, $replacement, $cut_start_position, $remove_length);
                    $offset_through_removing += $remove_length - strlen($replacement);
                    
                    $cut_start_position = false;
                }
                
                $nest_level--;
                //debug_message("nest_level decreased to:" . $nest_level);
            }
        }
    } // foreach
    
    //debug_message("====");
    //debug_message("AFTER:");
    //debug_message($output);
    //debug_message("-------------------");
} // remove_nested_spoilers_bb
//------------------------------------------------------------------------------

if (!function_exists("str_getcsv")) {
    function str_getcsv($string)
    {
        $fh = fopen('php://temp', 'r+');
        fwrite($fh, $string);
        rewind($fh);
        
        $row = fgetcsv($fh);
        
        fclose($fh);
        
        return $row;
    }
}

function gen_gmaps_html($coordinates, $apikey, $bbcode)
{
    $html = "<iframe class='gmap_iframe' data-bbcode='" . escape_html($bbcode) . "' src='https://www.google.com/maps/embed/v1/place?key=$apikey&amp;q=" . xrawurlencode($coordinates) . "' frameborder='0' allowfullscreen=''></iframe>";
    
    return $html;
} // gen_gmaps_html
//------------------------------------------------------
function gen_youtube_html($code, $apikey, $appendix, $bbcode)
{
    $title = "YouTube Video";
    $picture = "";
    $start = 0;
    
    if (preg_match("/\\?t=((\\d+)h)?((\\d+)m)?((\\d+)s)?/", $appendix, $matches)) {
        if (!empty($matches[2])) {
            $start += 3600 * $matches[2];
        }
        if (!empty($matches[4])) {
            $start += 60 * $matches[4];
        }
        if (!empty($matches[6])) {
            $start += 1 * $matches[6];
        }
    }
    
    if (preg_match("/\\?t=(\\d+)/", $appendix, $matches)) {
        if (!empty($matches[1])) {
            $start += $matches[1];
        }
    }
    
    if (preg_match("/time_continue=(\\d+)/", $appendix, $matches)) {
        if (!empty($matches[1])) {
            $start += $matches[1];
        }
    }
    
    try {
        $url = "https://www.googleapis.com/youtube/v3/videos";
        $client = new Zend_Http_Client($url, array(
            'maxredirects' => 5,
            'timeout' => 50
        ));
        
        if (isset($GLOBALS["youtube_usage_counter"])) {
            $GLOBALS["youtube_usage_counter"]++;
        }
        
        $client->setParameterGet("id", $code);
        $client->setParameterGet("key", $apikey);
        $client->setParameterGet("part", "snippet");
        
        $request = $client->request('GET');
        $response = $request->getBody();
        
        $json = json_decode($response, true);
        
        if ($json) {
            if (!empty($json["items"][0]["snippet"]["title"])) {
                $title = $json["items"][0]["snippet"]["title"];
            }
            
            if (!empty($json["items"][0]["snippet"]["thumbnails"]["high"]["url"])) {
                $picture = $json["items"][0]["snippet"]["thumbnails"]["high"]["url"];
            } else {
                if (!empty($json["items"][0]["snippet"]["thumbnails"])) {
                    $cur_width = 0;
                    $cur_picture = "";
                    foreach ($json["items"][0]["snippet"]["thumbnails"] as $tinfo) {
                        if ($tinfo["width"] > $cur_width) {
                            $cur_width = $tinfo["width"];
                            $cur_picture = $tinfo["url"];
                        }
                    }
                    
                    $picture = $cur_picture;
                }
            }
        }
        
        if ($title == "YouTube Video") {
            $GLOBALS["youtube_error"] = $response;
        }
    } catch (Exception $ex) {
        $GLOBALS["youtube_error"] = $ex->getMessage();
    }
    
    $html = "<div class='media_wrapper' data-bbcode='" . escape_html($bbcode) . "'><div class='short_video'><a class='youtube_short_container' href='https://youtu.be/" . escape_html($code . $appendix) . "' target='blank'>" . escape_html($title) . "</a></div>";
    $html .= "<div class='youtube_container detailed_video'><div class='youtube_wrapper' style='background-image:url($picture)'>";
    $html .= "<div class='youtube_header'>" . escape_html($title) . "</div>";
    $html .= "<div class='youtube_play_embedded' onclick='embed_youtube(this, \"$code\", $start)'></div>";
    $html .= "<a class='youtube_play_youtube' href='https://youtu.be/" . escape_html($code . $appendix) . "' target='blank'></a>";
    $html .= "</div></div></div>";
    
    return $html;
} // gen_youtube_html
//------------------------------------------------------
function gen_instagram_html($code, $bbcode)
{
    try {
        $url = "https://api.instagram.com/oembed?url=http://instagr.am/p/$code/";
        $client = new Zend_Http_Client($url, array(
            'maxredirects' => 5,
            'timeout' => 50
        ));
        
        $request = $client->request('GET');
        $response = $request->getBody();
        
        $height = 510;
        $json = json_decode($response, true);
        if ($json) {
            if (!empty($json["thumbnail_height"])) {
                $height = $json["thumbnail_height"];
            }
        }
    } catch (Exception $ex) {
        //$result = $ex->getMessage();
    }
    
    $html = "<div class='media_wrapper' data-bbcode='" . escape_html($bbcode) . "'>";
    $html .= "<div class='short_video'><a class='instagram_short_container' href='https://www.instagram.com/p/$code/' target='blank'>Instagram</a></div>";
    $html .= "<div class='instagram detailed_video'>";
    $html .= "<iframe src='https://www.instagram.com/p/$code/embed/?cr=1&v=12&wp=540' allowtransparency='true' allowfullscreen='true' scrolling='no' style='background: white none repeat scroll 0% 0%; max-width: 540px; width: calc(100% - 2px); border-radius: 3px; border: 1px solid rgb(219, 219, 219); box-shadow: none; display: block; margin: 0px 0px 12px; min-width: 326px; padding: 0px; height: {$height}px' frameborder='0'></iframe>";
    $html .= "</div></div>";
    
    return $html;
} // gen_instagram_html
//------------------------------------------------------
function gen_radikal_html($code, $bbcode)
{
    $html = "<div class='media_wrapper' data-bbcode='" . escape_html($bbcode) . "'>";
    $html .= "<div class='short_video'><a class='radikal_short_container' href='https://radikal.ru/video/$code' target='blank'>Radikal</a></div>";
    $html .= "<div class='radikal detailed_video'>";
    $html .= "<iframe width='640' height='360' src='https://radikal.ru/vf/$code' frameborder='0' scrolling='no' allowfullscreen></iframe>";
    $html .= "</div>
              <a class='attachment_link' href='https://radikal.ru/video/$code' target='_blank'>{{link}}</a>
              </div>";
    
    return $html;
} // gen_radikal_html
//------------------------------------------------------
function gen_reddit_html($code, $bbcode)
{
    $html = "<div class='media_wrapper' data-bbcode='" . escape_html($bbcode) . "'>";
    $html .= "<div class='short_video'><a class='reddit_short_container' href='https://www.reddit.com/$code' target='blank'>Reddit</a></div>";
    $html .= "<div class='reddit detailed_video'>";
    $html .= "<iframe width='640' height='360' src='https://old.reddit.com/mediaembed/$code' frameborder='0' scrolling='no' allowfullscreen></iframe>";
    $html .= "</div>
              <a class='attachment_link' href='https://www.reddit.com/$code' target='_blank'>{{link}}</a>
              </div>";
    
    return $html;
} // gen_reddit_html
//------------------------------------------------------
function gen_tiktok_html($code, $bbcode)
{
    $html = "<div class='media_wrapper' data-bbcode='" . escape_html($bbcode) . "'>";
    $html .= "<div class='short_video'><a class='tiktok_short_container' href='https://www.tiktok.com/@author/video/$code' target='blank'>TikTok</a></div>";
    $html .= "<div class='tiktok detailed_video'>";
    $html .= "<iframe width='500' height='660' src='https://www.tiktok.com/embed/v2/$code?lang=" . current_language() . "' frameborder='0' scrolling='no' allowfullscreen></iframe>";
    $html .= "</div>
              <a class='attachment_link' href='https://www.tiktok.com/@author/video/$code' target='_blank'>{{link}}</a>
              </div>";
    
    return $html;
} // gen_tiktok_html
//------------------------------------------------------
function gen_vimeo_html($code, $start, $bbcode)
{
    $title = "Vimeo Video";
    $picture = "";
    
    if (empty($start)) {
        $start = 0;
    }
    $appendix = "#at=" . $start;
    
    try {
        $url = "http://vimeo.com/api/v2/video/$code.json";
        $client = new Zend_Http_Client($url, array(
            'maxredirects' => 5,
            'timeout' => 50
        ));
        
        $request = $client->request('GET');
        $response = $request->getBody();
        
        $json = json_decode($response, true);
        if ($json) {
            if (!empty($json[0]["title"])) {
                $title = $json[0]["title"];
            }
            if (empty($picture) && !empty($json[0]["thumbnail_large"])) {
                $picture = $json[0]["thumbnail_large"];
            }
            if (empty($picture) && !empty($json[0]["thumbnail_medium"])) {
                $picture = $json[0]["thumbnail_medium"];
            }
            if (empty($picture) && !empty($json[0]["thumbnail_small"])) {
                $picture = $json[0]["thumbnail_small"];
            }
        }
    } catch (Exception $ex) {
        //$result = $ex->getMessage();
    }
    
    $html = "<div class='media_wrapper' data-bbcode='" . escape_html($bbcode) . "'><div class='short_video'><a class='vimeo_short_container' href='https://vimeo.com/$code$appendix' target='blank'>" . escape_html($title) . "</a></div>";
    $html .= "<div class='vimeo_container detailed_video'><div class='vimeo_wrapper' style='background-image:url($picture)'>";
    $html .= "<div class='vimeo_header'>" . escape_html($title) . "</div>";
    $html .= "<div class='vimeo_play_embedded' onclick='embed_vimeo(this, \"$code\", \"$start\")'></div>";
    $html .= "<a class='vimeo_play_vimeo' href='https://vimeo.com/$code$appendix' target='blank'></a>";
    $html .= "</div></div></div>";
    
    return $html;
} // gen_vimeo_html
//------------------------------------------------------
function gen_vkvideo_html($code, $bbcode)
{
    $title = "VK Video";
    $picture = "";
    $player = "";
    $has_error = false;
    
    try {
        $url = "https://api.vk.com/method/video.get";
        $client = new Zend_Http_Client($url, array(
            'maxredirects' => 5,
            'timeout' => 50
        ));
        
        $client_secret = "";
        if (defined('VK_CLIENT_SECRET')) {
            $client_secret = VK_CLIENT_SECRET;
        }
        
        $access_token = "";
        if (defined('VK_ACCESS_TOKEN')) {
            $access_token = VK_ACCESS_TOKEN;
        }
        
        $client->setParameterGet("v", "5.131");
        $client->setParameterGet("videos", $code);
        $client->setParameterGet("client_secret", $client_secret);
        $client->setParameterGet("access_token", $access_token);
        
        $request = $client->request('GET');
        $response = $request->getBody();
        
        $json = json_decode($response, true);
        
        if ($json) {
            if (!empty($json["response"]["items"][0]["title"])) {
                $title = $json["response"]["items"][0]["title"];
            } else {
                $has_error = true;
            }            
            
            if (!empty($json["response"]["items"][0]["player"])) {
                $player = $json["response"]["items"][0]["player"];
            }
            
            if (!empty($json["response"]["items"][0]["content_restricted"])) {
                $has_error = true;
            }
        } else {
            $has_error = true;
        }
    } catch (Exception $ex) {
        //$result = $ex->getMessage();
        $has_error = true;
    }
    
    if ($has_error) {
        $html = "<div class='media_wrapper' data-bbcode='" . escape_html($bbcode) . "'><div class='short_video'><a class='vkvideo_short_container' href='https://vk.com/video$code' target='blank'>" . escape_html($title) . "</a></div>";
        $html .= "<div class='detailed_video'>";
        $html .= "<img class='post_image' src='user_data/images/video_unaccessible.png' alt='" . escape_html($title) . "'>";
        $html .= "</div>";
        $html .= "<a class='attachment_link' href='https://vk.com/video$code' target='_blank'>{{link}}</a>";
        $html .= "</div>";
        return $html;                
    }
    
    /*
    $html = "<div class='media_wrapper' data-bbcode='" . escape_html($bbcode) . "'><div class='short_video'><a class='vkvideo_short_container' href='https://vk.com/video$code' target='blank'>" . escape_html($title) . "</a></div>";
    $html .= "<div class='vkvideo_container detailed_video'><div class='vkvideo_wrapper' style='background-image:url($picture)'>";
    $html .= "<div class='vkvideo_header'>" . escape_html($title) . "</div>";
    $html .= "<div class='vkvideo_play_embedded' onclick='embed_vkvideo(this, \"$player\")'></div>";
    $html .= "<a class='vkvideo_play_vkvideo' href='https://vk.com/video$code' target='blank'></a>";
    $html .= "</div></div></div>";
    */
    
    $html = "<div class='media_wrapper' data-bbcode='" . escape_html($bbcode) . "'><div class='short_video'><a class='vkvideo_short_container' href='https://vk.com/video$code' target='blank'>" . escape_html($title) . "</a></div>";
    $html .= "<div class='vkvideo_container detailed_video'>";
    $html .= "<iframe src='$player' width='100%' height='100%' frameborder='0' allowfullscreen></iframe>";
    $html .= "</div>";
    $html .= "<a class='attachment_link' href='https://vk.com/video$code' target='_blank'>{{link}}</a>";
    $html .= "</div>";
    
    return $html;
} // gen_vkvideo_html
//------------------------------------------------------
function gen_rutube_html($code, $start, $bbcode)
{
    $title = "Rutube Video";
    $picture = "";
    
    if (empty($start)) {
        $start = 0;
    }
    $appendix = "?bmstart=" . $start;
    
    try {
        $url = "http://rutube.ru/api/video/$code";
        $client = new Zend_Http_Client($url, array(
            'maxredirects' => 5,
            'timeout' => 50
        ));
        
        $request = $client->request('GET');
        $response = $request->getBody();
        
        $json = json_decode($response, true);
        if ($json) {
            if (!empty($json["title"])) {
                $title = $json["title"];
            }
            if (!empty($json["thumbnail_url"])) {
                $picture = $json["thumbnail_url"];
            }
        }
    } catch (Exception $ex) {
        //$result = $ex->getMessage();
    }
    
    $html = "<div class='media_wrapper' data-bbcode='" . escape_html($bbcode) . "'><div class='short_video'><a class='rutube_short_container' href='https://rutube.ru/video/$code/$appendix' target='blank'>" . escape_html($title) . "</a></div>";
    $html .= "<div class='rutube_container detailed_video'><div class='rutube_wrapper' style='background-image:url($picture)'>";
    $html .= "<div class='rutube_header'>" . escape_html($title) . "</div>";
    $html .= "<div class='rutube_play_embedded' onclick='embed_rutube(this, \"$code\", \"$start\")'></div>";
    $html .= "<a class='rutube_play_rutube' href='https://rutube.ru/video/$code/$appendix' target='blank'></a>";
    $html .= "</div></div></div>";
    
    return $html;
} // gen_rutube_html
//------------------------------------------------------
function gen_coub_html($code, $bbcode)
{
    $title = "Coub Video";
    $picture = "";
    
    try {
        $url = "https://coub.com/api/v2/coubs/$code.json";
        $client = new Zend_Http_Client($url, array(
            'maxredirects' => 5,
            'timeout' => 50
        ));
        
        $request = $client->request('GET');
        $response = $request->getBody();
        
        $json = json_decode($response, true);
        if ($json) {
            if (!empty($json["title"])) {
                $title = $json["title"];
            }
            if (!empty($json["picture"])) {
                $picture = $json["picture"];
            }
        }
    } catch (Exception $ex) {
        //$result = $ex->getMessage();
    }
    
    $html = "<div class='media_wrapper' data-bbcode='" . escape_html($bbcode) . "'><div class='short_video'><a class='coub_short_container' href='https://coub.com/view/$code' target='blank'>" . escape_html($title) . "</a></div>";
    $html .= "<div class='coub_container detailed_video'><div class='coub_wrapper' style='background-image:url($picture)'>";
    $html .= "<div class='coub_header'>" . escape_html($title) . "</div>";
    $html .= "<div class='coub_play_embedded' onclick='embed_coub(this, \"$code\")'></div>";
    $html .= "<a class='coub_play_coub' href='https://coub.com/view/$code' target='blank'></a>";
    $html .= "</div></div></div>";
    
    return $html;
} // gen_coub_html
//------------------------------------------------------
function gen_fbvideo_html($code, $bbcode)
{
    $title = "Facebook Video";
    
    $width = 500;
    $height = 300;
    
    $access_token = "";
    if (defined('FB_ACCESS_TOKEN')) {
        $access_token = FB_ACCESS_TOKEN;
    }
    
    $video_url = "https://www.facebook.com/facebook/videos/$code/";
    
    $real_html = "";
    
    try {
        $url = "https://graph.facebook.com/oembed_video?maxwidth=$width&sdklocale=" . current_language() . "&omitscript=true&access_token=$access_token&url=" . xrawurlencode($video_url);
        
        $client = new Zend_Http_Client($url, array(
            'maxredirects' => 5,
            'timeout' => 50
        ));
        
        $request = $client->request('GET');
        $response = $request->getBody();
        
        $json = json_decode($response, true);
        if ($json) {
            if (!empty($json["height"])) {
                $height = $json["height"];
            }
            
            if (!empty($json["html"])) {
                $real_html = $json["html"];
                
                if (preg_match("/.*<a href=\"https:\\/\\/www.facebook.com\\/.+\\/videos\\/[^\\/]+\\/\">([^<>]+)<\\/a>.*/", $json["html"], $macthes)) {
                    $title = $macthes[1];
                }
            }
        }
    } catch (Exception $ex) {
        //$result = $ex->getMessage();
    }
    
    /*
    $video_html = "<div class='fb-video facebook_mobile_adjust_width' data-href='$video_url' data-width='$width'>";
    $video_html .= "<blockquote cite='$video_url' class='fb-xfbml-parse-ignore'><a href='$video_url' target='blank' class='fb-xfbml-parse-ignore facebook_mobile_adjust_height' style='height:$height'>" . escape_html($title) . "</a></blockquote>";
    $video_html .= "</div>";
    
    $html = "<div class='media_wrapper' data-bbcode='" . escape_html($bbcode) . "'><div class='short_video'><a class='fbvideo_short_container' href='$video_url' target='blank'>" . escape_html($title) . "</a></div>";
    $html .= "<div class='fbvideo_container detailed_video facebook_mobile_adjust_width facebook_mobile_adjust_height' style='width:{$width}px;height:$height'>";
    $html .= $video_html;
    $html .= "</div></div>";
    
    $html = "<div class='media_wrapper' data-bbcode='" . escape_html($bbcode) . "'><div class='short_video'><a class='fbvideo_short_container' href='$video_url' target='blank'>" . escape_html($title) . "</a></div>";
    $html .= "<div class='fbvideo_container detailed_video'>";
    $html .= "<iframe allowtransparency='true' allowfullscreen='true' scrolling='no' allow='encrypted-media' style='visibility: visible; display: block' src='https://www.facebook.com/v3.3/plugins/video.php?app_id=113869198637480&href=" . xrawurlencode($video_url) . "' width='$width' height='$height' frameborder='0'></iframe>";
    $html .= "</div></div>";
    */
    
    $html = "<div class='media_wrapper' data-bbcode='" . escape_html($bbcode) . "'><div class='short_video'><a class='fbvideo_short_container' href='$video_url' target='blank'>" . escape_html($title) . "</a></div>";
    $html .= "<div class='fbvideo_container detailed_video'>";
    
    $html .= "<div class='fb-video' data-href='https://www.facebook.com/facebook/videos/$code/' data-width='$width' data-show-text='false'>";
    $html .= "<div class='fb-xfbml-parse-ignore'>";
    $html .= "<blockquote cite='https://www.facebook.com/facebook/videos/$code/'>";
    $html .= "</blockquote>";
    $html .= "</div>";
    $html .= "</div>";
    
    $html .= "</div></div>";
    
    
    /*if(!empty($real_html)) {
       $html = "<div class='media_wrapper' data-bbcode='" . escape_html($bbcode) . "'><div class='short_video'><a class='fbvideo_short_container' href='$video_url' target='blank'>" . escape_html($title) . "</a></div>";
       $html .= "<div class='fbvideo_container detailed_video' style='color:transparent'>";
       $html .= $real_html;
       $html .= "</div></div>";
    }*/
    
    return $html;
} // gen_fbvideo_html
//------------------------------------------------------
function gen_twitter_html($code, $bbcode)
{
    $title = "Twitter Video";
    
    $width = 550;
    
    $video_url = "https://twitter.com/twitter/status/$code";
    
    try {
        $url = "https://publish.twitter.com/oembed?url=" . xrawurlencode($video_url);
        $client = new Zend_Http_Client($url, array(
            'maxredirects' => 5,
            'timeout' => 50
        ));
        
        $request = $client->request('GET');
        $response = $request->getBody();
        
        $json = json_decode($response, true);
        if ($json) {
            if (!empty($json["html"])) {
                if (preg_match("/.*<p[^<>]+>(.+)<\\/p>.*/s", $json["html"], $macthes)) {
                    $title = strip_tags($macthes[1]);
                }
            }
        }
    } catch (Exception $ex) {
        //$result = $ex->getMessage();
    }
    
    $html = "<div class='media_wrapper' data-bbcode='" . escape_html($bbcode) . "'><div class='short_video'><a class='twitter_short_container' href='$video_url' target='blank'>" . escape_html($title) . "</a></div>";
    $html .= "<div class='twitter_container detailed_video'>";
    
    //$html .= "<iframe src='https://twitter.com/i/videos/tweet/" . escape_html($code) . "' width='$width' height='$height' frameborder='0' style='visibility: visible; display: block'></iframe>";
    
    $html .= '
        <blockquote class="twitter-tweet" data-conversation="none" data-lang="' . current_language() . '" data-dnt="true" data-width="' . $width . '">
        <a href="https://twitter.com/newkc14/status/' . escape_html($code) . '"></a>
        </blockquote>
        ';
    
    $html .= "</div>
              <a class='attachment_link' href='$video_url' target='_blank'>{{link}}</a>
              </div>";
    
    return $html;
} // gen_twitter_html
//------------------------------------------------------
function gen_telegram_html($code, $bbcode)
{
    global $skin;
    
    $title = "Telegram";
    
    $video_url = "https://t.me/$code";
    
    $html = "<div class='media_wrapper' data-bbcode='" . escape_html($bbcode) . "'><div class='short_video'><a class='telegram_short_container' href='$video_url' target='blank'>" . escape_html($title) . "</a></div>";
    $html .= "<div class='telegram_container detailed_video'>";
    
    $block_id = str_ireplace("/", "-", $code) . "-" . time() . "-" . rand(1000000, 9999999);
    
    $html .= '
        <script async src="skins/' . $skin . '/js/telegram-widget.js" data-telegram-post="' . escape_html($code) . '" data-block-id="' . $block_id . '" data-width="100%"></script>
        ';
    
    $html .= "</div>
              <a class='attachment_link' href='$video_url' target='_blank'>{{link}}</a>
              </div>";
    
    return $html;
} // gen_telegram_html
//------------------------------------------------------------------------------
function parse_bb_code(&$input, &$output, &$has_link, &$has_code, $post_id)
{
    $bbcode = new BBCode;
    $bbcode->ClearRules();
    $bbcode->ClearSmileys();
    $bbcode->SetCheckSpecialUrl(true);
    $bbcode->SetDetectURLs(true);
    $bbcode->SetMessageMode("message");
    
    $bbcode->post_id = $post_id;
    
    //----------------------------------------------
    $bbcode->AddRule('b',
        array(
            'simple_start' => '<strong>',
            'simple_end' => '</strong>',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('i',
        array(
            'simple_start' => '<em>',
            'simple_end' => '</em>',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('s',
        array(
            'simple_start' => '<span style="text-decoration: line-through">',
            'simple_end' => '</span>',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('u',
        array(
            'simple_start' => '<u>',
            'simple_end' => '</u>',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('sup',
        array(
            'simple_start' => '<sup>',
            'simple_end' => '</sup>',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('sub',
        array(
            'simple_start' => '<sub>',
            'simple_end' => '</sub>',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('hidden',
        array(
            'simple_start' => '<div class="hidden_phrase_expander" onclick="this.nextSibling.style.display = \'inline\'; this.style.display = \'none\';">***</div><div class="hidden_phrase" style="display: none">&nbsp;',
            'simple_end' => '&nbsp;</div>',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('hr',
        array(
            'simple_start' => '<hr/>',
            'simple_end' => '',
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('size',
        array(
            'mode' => BBCODE_MODE_ENHANCED,
            'template' => "<span class='size{\$_default/tw}'>{\$_content/v}</span>",
            'allow' => array('_default' => '/^[1-5]$/'),
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('color',
        array(
            'mode' => BBCODE_MODE_ENHANCED,
            'template' => "<span style='color:{\$_default/tw}'>{\$_content/v}</span>",
            'allow' => array('_default' => '/^#?[a-zA-Z0-9._ -]+$/'),
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('img',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'method' => 'bb_process_img',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('latex',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'method' => 'bb_process_latex',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('gif',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'method' => 'bb_process_anim',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('anim',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'method' => 'bb_process_anim',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('smile',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'method' => 'bb_process_smile',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $new_rule = array(
        'simple_start' => '<tt>',
        'simple_end' => '</tt>',
        'class' => 'inline',
        'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link'),
    );
    $bbcode->AddRule('mono', $new_rule);
    //----------------------------------------------
    $bbcode->AddRule('fixed',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'n',
            'before_endtag' => 'a',
            'method' => 'bb_process_fixed',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('poem',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_process_poem',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    /*
    $bbcode->AddRule('fixed',
        array(
            'mode' => BBCODE_MODE_SIMPLE,
            'content' => BBCODE_VERBATIM,
            'simple_start' => '<pre>',
            'simple_end' => '</pre>',
            'class' => 'fixed',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('poem',
        array(
            'mode' => BBCODE_MODE_SIMPLE,
            'content' => BBCODE_VERBATIM,
            'simple_start' => '<pre>',
            'simple_end' => '</pre>',
            'class' => 'poem',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    */
    $bbcode->AddRule('quote',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_process_quote',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('url',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            //'content' => BBCODE_VERBATIM,
            'method' => 'bb_process_url',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('email',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'method' => 'bb_process_email',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('spoiler',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_process_spoiler',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('list',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_process_list',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('nlist',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_process_nlist',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('table',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_process_table',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('gallery',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_process_gallery',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('audio',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_process_audio',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('video',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_process_video',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('youtube',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_process_youtube',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('gmap',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_process_gmap',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('instagram',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_process_instagram',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('radikal',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_process_radikal',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('reddit',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_process_reddit',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('tiktok',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_process_tiktok',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('rutube',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_process_rutube',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('vkvideo',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_process_vkvideo',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('coub',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_process_coub',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('telegram',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_process_telegram',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    /*
    $bbcode->AddRule('fbvideo',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_process_fbvideo',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    */
    //----------------------------------------------
    $bbcode->AddRule('twitter',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_process_twitter',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('vimeo',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_process_vimeo',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('code',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            //'after_tag' => 'a',
            //'before_endtag' => 'a',
            'method' => 'bb_process_code',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    
    // smiles
    
    $dir = APPLICATION_ROOT . "user_data/smileys";
    $bbcode->SetSmileyDir($dir);
    $bbcode->SetSmileyURL("user_data/smileys/");
    
    $files = scandir($dir);
    if (!$files) {
        return true;
    }
    
    $smileys = array();
    
    foreach ($files as $file) {
        if ($file == "." || $file == "..") {
            continue;
        }
        
        if (!preg_match("/.+\.(jpg|jpeg|gif|png|webp)$/i", $file)) {
            continue;
        }
        
        $pi = pathinfo($file);
        
        $smileys[$pi['filename']] = $file;
    }
    
    foreach ($smileys as $code => $file) {
        $bbcode->AddSmiley("[:$code]", $file);
    }
    
    // smile codes
    
    if (file_exists(APPLICATION_ROOT . "user_data/smileys/_codes.cnf") &&
        $contents = @file_get_contents(APPLICATION_ROOT . "user_data/smileys/_codes.cnf")) {
        $rows = preg_split("/[\r\n]+/", $contents, -1, PREG_SPLIT_NO_EMPTY);
        
        foreach ($rows as $row) {
            $codes = str_getcsv($row);
            if (empty($codes[0]) || empty($codes[1]) || empty($smileys[$codes[1]])) {
                continue;
            }
            
            $bbcode->AddSmiley($codes[0], $smileys[$codes[1]]);
        }
    }
    
    //----------------------------------------------
    
    tags_to_lowercase($input);
    
    $output = $bbcode->Parse($input);
    
    if (!empty($bbcode->has_link)) {
        $has_link = 1;
    }
    
    if (!empty($bbcode->has_code)) {
        $has_code = 1;
    }
} // parse_bb_code
//------------------------------------------------------------------------------
function parse_ascii_art(&$text)
{
    $bbcode = new BBCode;
    $bbcode->ClearRules();
    $bbcode->SetLimit(200000);
    $bbcode->SetIgnoreNewlines(true);
    $bbcode->RemoveRule('wiki');
    $bbcode->ClearSmileys();
    
    $bbcode->AddRule('color', [
        'mode' => 4,
        'allow' => ['_default' => '/^#?[a-z0-9._ -]+$/i'],
        'template' => '<span style="color:{$_default/tw}">{$_content/v}</span>',
        'class' => 'inline',
        'allow_in' => ['listitem', 'block', 'columns', 'inline', 'link']
    ]);
    
    $text = preg_replace('/\]([\[]+)\[/', ']{[', $text);
    $text = preg_replace('/\]([\]]+)\[/', ']}[', $text);
    
    $text = html_entity_decode($text);
    
    tags_to_lowercase($text);
    
    $text = $bbcode->Parse($text);
} // parse_ascii_art
//------------------------------------------------------------------------------
function parse_bb_code_simple(&$text, $mode = "email")
{
    $bbcode = new BBCode;
    $bbcode->ClearRules();
    $bbcode->ClearSmileys();
    $bbcode->SetDetectURLs(true);
    $bbcode->SetCheckSpecialUrl(true);
    $bbcode->SetMessageMode($mode);
    
    if ($mode == "email") {
        $bbcode->SetEnableSmileys(false);
    } else {
        $bbcode->SetEnableSmileys(true);
    }

    remove_nested_quotes_bb($text, $text, 2);
    remove_nested_spoilers_bb($text, $text, 2);
    
    //----------------------------------------------
    // BB tags are ignored (stripped)
    //----------------------------------------------
    $bbcode->AddRule('i',
        array(
            'simple_start' => '',
            'simple_end' => '',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('s',
        array(
            'simple_start' => '',
            'simple_end' => '',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('u',
        array(
            'simple_start' => '',
            'simple_end' => '',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('sup',
        array(
            'simple_start' => '',
            'simple_end' => '',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('sub',
        array(
            'simple_start' => '',
            'simple_end' => '',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('size',
        array(
            'simple_start' => '',
            'simple_end' => '',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('color',
        array(
            'simple_start' => '',
            'simple_end' => '',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('fixed',
        array(
            'simple_start' => '',
            'simple_end' => '',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('poem',
        array(
            'simple_start' => '',
            'simple_end' => '',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    
    //----------------------------------------------
    // BB blocks are replaced with a word
    //----------------------------------------------
    $bbcode->AddRule('gallery',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'method' => 'bb_word',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('latex',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'method' => 'bb_word',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('gmap',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'method' => 'bb_word',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('table',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_word',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('audio',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_word',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('video',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_word',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('youtube',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_word',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('rutube',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_word',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('vkvideo',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_word',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('coub',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_word',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('telegram',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_word',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('fbvideo',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_word',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('twitter',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_word',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('instagram',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_word',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('radikal',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_word',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('vimeo',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_word',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('code',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'content' => BBCODE_VERBATIM,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_word',
            'allow_in' => array('block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    
    //----------------------------------------------
    // Special tags handling
    //----------------------------------------------
    $bbcode->AddRule('hidden',
        array(
            'mode' => BBCODE_MODE_ENHANCED,
            'template' => "***",
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('quote',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_process_quote_simple',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    $bbcode->AddRule('spoiler',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'before_tag' => 'a',
            'after_endtag' => 'a',
            'after_tag' => 'a',
            'before_endtag' => 'a',
            'method' => 'bb_process_spoiler_simple',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------

    $bbcode->AddRule('b',
        array(
            'mode' => BBCODE_MODE_CALLBACK,
            'method' => 'bb_process_bold_simple',
            'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
        ));
    //----------------------------------------------
    
    //----------------------------------------------
    // Depending on the mode
    //----------------------------------------------
    if ($mode == "email") {
        //------------------------------------------
        $bbcode->AddRule('img',
            array(
                'mode' => BBCODE_MODE_CALLBACK,
                'content' => BBCODE_VERBATIM,
                'before_tag' => 'a',
                'after_endtag' => 'a',
                'method' => 'bb_word',
                'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
            ));
        //------------------------------------------
        $bbcode->AddRule('gif',
            array(
                'mode' => BBCODE_MODE_CALLBACK,
                'content' => BBCODE_VERBATIM,
                'before_tag' => 'a',
                'after_endtag' => 'a',
                'method' => 'bb_word',
                'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
            ));
        //------------------------------------------
        $bbcode->AddRule('smile',
            array(
                'mode' => BBCODE_MODE_CALLBACK,
                'content' => BBCODE_VERBATIM,
                'method' => 'bb_process_smile_simple',
                'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
            ));
        //------------------------------------------
        $bbcode->AddRule('url',
            array(
                'mode' => BBCODE_MODE_CALLBACK,
                //'content' => BBCODE_VERBATIM,
                'method' => 'bb_process_url_simple',
                'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
            ));
        //------------------------------------------
        $bbcode->AddRule('email',
            array(
                'mode' => BBCODE_MODE_CALLBACK,
                //'content' => BBCODE_VERBATIM,
                'method' => 'bb_process_email_simple',
                'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
            ));
        //------------------------------------------
        $bbcode->AddRule('list',
            array(
                'mode' => BBCODE_MODE_CALLBACK,
                'before_tag' => 'a',
                'after_endtag' => 'a',
                'after_tag' => 'a',
                'before_endtag' => 'a',
                'method' => 'bb_process_list_simple',
                'allow_in' => array('block', 'columns', 'inline', 'link')
            ));
        //------------------------------------------
        $bbcode->AddRule('nlist',
            array(
                'mode' => BBCODE_MODE_CALLBACK,
                'before_tag' => 'a',
                'after_endtag' => 'a',
                'after_tag' => 'a',
                'before_endtag' => 'a',
                'method' => 'bb_process_nlist_simple',
                'allow_in' => array('block', 'columns', 'inline', 'link')
            ));
        //------------------------------------------
    } else {
        if ($mode == "signature") {
            //--------------------------------------
            $bbcode->AddRule('img',
                array(
                    'mode' => BBCODE_MODE_CALLBACK,
                    'content' => BBCODE_VERBATIM,
                    'before_tag' => 'a',
                    'after_endtag' => 'a',
                    'method' => 'bb_process_img',
                    'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
                ));
            //--------------------------------------
            $bbcode->AddRule('gif',
                array(
                    'mode' => BBCODE_MODE_CALLBACK,
                    'content' => BBCODE_VERBATIM,
                    'before_tag' => 'a',
                    'after_endtag' => 'a',
                    'method' => 'bb_process_anim',
                    'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
                ));
            //--------------------------------------
            $bbcode->AddRule('anim',
                array(
                    'mode' => BBCODE_MODE_CALLBACK,
                    'content' => BBCODE_VERBATIM,
                    'before_tag' => 'a',
                    'after_endtag' => 'a',
                    'method' => 'bb_process_anim',
                    'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
                ));
            //--------------------------------------
        } else {
            //--------------------------------------
            $bbcode->AddRule('img',
                array(
                    'mode' => BBCODE_MODE_CALLBACK,
                    'content' => BBCODE_VERBATIM,
                    'before_tag' => 'a',
                    'after_endtag' => 'a',
                    'after_tag' => 'a',
                    'before_endtag' => 'a',
                    'method' => 'bb_word',
                    'allow_in' => array('block', 'columns', 'inline', 'link')
                ));
            //--------------------------------------
            $bbcode->AddRule('gif',
                array(
                    'mode' => BBCODE_MODE_CALLBACK,
                    'content' => BBCODE_VERBATIM,
                    'before_tag' => 'a',
                    'after_endtag' => 'a',
                    'after_tag' => 'a',
                    'before_endtag' => 'a',
                    'method' => 'bb_word',
                    'allow_in' => array('block', 'columns', 'inline', 'link')
                ));
            //--------------------------------------
            $bbcode->AddRule('anim',
                array(
                    'mode' => BBCODE_MODE_CALLBACK,
                    'content' => BBCODE_VERBATIM,
                    'before_tag' => 'a',
                    'after_endtag' => 'a',
                    'after_tag' => 'a',
                    'before_endtag' => 'a',
                    'method' => 'bb_word',
                    'allow_in' => array('block', 'columns', 'inline', 'link')
                ));
            //--------------------------------------
        }
        //------------------------------------------
        $bbcode->AddRule('smile',
            array(
                'mode' => BBCODE_MODE_CALLBACK,
                'content' => BBCODE_VERBATIM,
                'method' => 'bb_process_smile',
                'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
            ));
        //------------------------------------------
        $bbcode->AddRule('email',
            array(
                'mode' => BBCODE_MODE_CALLBACK,
                //'content' => BBCODE_VERBATIM,
                'method' => 'bb_process_email',
                'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
            ));
        //------------------------------------------
        $bbcode->AddRule('url',
            array(
                'mode' => BBCODE_MODE_CALLBACK,
                //'content' => BBCODE_VERBATIM,
                'method' => 'bb_process_url',
                'allow_in' => array('listitem', 'block', 'columns', 'inline', 'link')
            ));
        //------------------------------------------
        $bbcode->AddRule('list',
            array(
                'mode' => BBCODE_MODE_CALLBACK,
                'before_tag' => 'a',
                'after_endtag' => 'a',
                'after_tag' => 'a',
                'before_endtag' => 'a',
                'method' => 'bb_process_list',
                'allow_in' => array('block', 'columns', 'inline', 'link')
            ));
        //------------------------------------------
        $bbcode->AddRule('nlist',
            array(
                'mode' => BBCODE_MODE_CALLBACK,
                'before_tag' => 'a',
                'after_endtag' => 'a',
                'after_tag' => 'a',
                'before_endtag' => 'a',
                'method' => 'bb_process_nlist',
                'allow_in' => array('block', 'columns', 'inline', 'link')
            ));
        //------------------------------------------
        
        // smiles
        
        $dir = APPLICATION_ROOT . "user_data/smileys";
        $bbcode->SetSmileyDir($dir);
        $bbcode->SetSmileyURL("user_data/smileys/");
        
        $files = scandir($dir);
        if (!$files) {
            return true;
        }
        
        $smileys = array();
        
        foreach ($files as $file) {
            if ($file == "." || $file == "..") {
                continue;
            }
            
            if (!preg_match("/.+\.(jpg|jpeg|gif|png|webp)$/i", $file)) {
                continue;
            }
            
            $pi = pathinfo($file);
            
            $smileys[$pi['filename']] = $file;
        }
        
        foreach ($smileys as $code => $file) {
            $bbcode->AddSmiley("[:$code]", $file);
        }
        
        // smile codes
        
        if (file_exists(APPLICATION_ROOT . "user_data/smileys/_codes.cnf") &&
            $contents = @file_get_contents(APPLICATION_ROOT . "user_data/smileys/_codes.cnf")) {
            $rows = preg_split("/[\r\n]+/", $contents, -1, PREG_SPLIT_NO_EMPTY);
            
            foreach ($rows as $row) {
                $codes = str_getcsv($row);
                if (empty($codes[0]) || empty($codes[1]) || empty($smileys[$codes[1]])) {
                    continue;
                }
                
                $bbcode->AddSmiley($codes[0], $smileys[$codes[1]]);
            }
        }
    }
    //----------------------------------------------
    
    tags_to_lowercase($text);
    
    $text = $bbcode->Parse($text);
} // parse_bb_code_simple
//------------------------------------------------------------------------------
?>