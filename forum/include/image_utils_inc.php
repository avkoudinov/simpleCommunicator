<?php
//-------------------------------------------------------------------
ini_set('gd.jpeg_ignore_warning', 1);
//-------------------------------------------------------------------
function toUint32($n){
  $ar = unpack("C*", pack("L", $n));
  return $ar;
}
//-------------------------------------------------------------------
function toUint24($n){
  $ar = unpack("C*", pack("L", $n));
  array_pop($ar);
  return $ar;
}
//-------------------------------------------------------------------
function toUint16($n){
  $ar = unpack("C*", pack("S", $n));
  return $ar;
}
//-------------------------------------------------------------------
function bytesToString($bytes){
  return implode(array_map("chr", $bytes));
}
//-------------------------------------------------------------------
function binaryToBytes($bits){
  $octets = explode(' ', $bits);
  return array_map("bindec", $octets);
}
//-------------------------------------------------------------------
function zero_to_one($v)
{
    return $v < 1 ? 1 : $v;
} // zero_to_one
//-------------------------------------------------------------------
function check_image($path)
{
    if (!file_exists($path)) {
        return false;
    }
    
    $info = getimagesize($path);
    if (empty($info) || empty($info["mime"])) return false;
    
    if (strpos($info["mime"], "image") !== 0) return false;
    
    if (!preg_match('/width="(\d+)" height="(\d+)"/', val_or_empty($info["3"]), $matches)) {
        return false;
    }
    
    if ($matches[1] > 10000 || $matches[2] > 10000) {
        return false;
    }
    
    return true;
} // check_image
//-------------------------------------------------------------------
function image_load($path)
{
    if (!file_exists($path)) {
        return false;
    }
    
    $path_parts = pathinfo($path);
    switch (strtolower(val_or_empty($path_parts['extension']))) {
        case 'jpg' :
        case 'jpeg':
            $im = @imagecreatefromjpeg($path);
            break;
        
        case 'gif' :
            {
                $im = @imagecreatefromgif($path);
            }
            break;
            
        case 'webp' :
            {
                $im = @imagecreatefromwebp($path);
            }
            break;
        
        case 'png' :
            {
                $im = @imagecreatefrompng($path);
                if ($im) {
                    imageSaveAlpha($im, true);
                    imageAlphaBlending($im, false);
                }
            }
            break;
        
        default    :
            return false;
    }
    
    return $im;
} // image_load
//-------------------------------------------------------------------
function image_save($img, $path)
{
    if (!function_exists("imagecreatefromjpeg")) {
        return false;
    }
    
    $path_parts = pathinfo($path);
    $res = false;
    switch (strtolower($path_parts['extension'])) {
        case 'jpg' :
        case 'jpeg':
            $res = imagejpeg($img, $path, 100);
            break;
        case 'gif' :
            $res = imagegif($img, $path);
            break;
        case 'webp' :
            $res = imagewebp($img, $path, 100);
            break;
        case 'png' :
            $res = imagepng($img, $path, 0, PNG_ALL_FILTERS);
            break;
        default    : //dbg
            $res = false;
    }
    
    return $res;
} // image_save
//-------------------------------------------------------------------
function image_out($img, $ext)
{
    if (!function_exists("imagecreatefromjpeg")) {
        return false;
    }
    
    header("Content-type: image/" . $ext);
    
    $res = false;
    switch ($ext) {
        case 'jpg' :
        case 'jpeg':
            $res = imagejpeg($img, null, 100);
            break;
        case 'gif' :
            $res = imagegif($img);
            break;
        case 'png' :
            $res = imagepng($img, null, 0, PNG_ALL_FILTERS);
            break;
        default    : //dbg
            $res = false;
    }
    
    return $res;
} // image_out
//-------------------------------------------------------------------
function compress_image($src, $target, $quality)
{
    if (!function_exists("imagecreatefromjpeg")) {
        return false;
    }
    
    $info = getimagesize($src);
    
    if ($info['mime'] == 'image/jpeg') {
        $image = imagecreatefromjpeg($src);
        return imagejpeg($image, $target, $quality);
    } elseif ($info['mime'] == 'image/png') {
        $pngquality = 10 - ceil($quality / 10);
        $image = imagecreatefrompng($src);
        return imagepng($image, $target, $pngquality, PNG_ALL_FILTERS);
    }
    
    return false;
} // compress_image
//-------------------------------------------------------------------
function check_image_rotation($src, $target)
{
    $pi = pathinfo($src);
    
    $orientation = "";

    if ((strtolower($pi['extension']) == "jpg" || strtolower($pi['extension']) == "jpeg") &&
        function_exists("exif_read_data")) {
        $exif = @exif_read_data($src);
        
        if (!empty($exif["Orientation"])) {
            $orientation = $exif["Orientation"];
        }
        if (empty($orientation) && !empty($exif["COMPUTED"]["Orientation"])) {
            $orientation = $exif["COMPUTED"]["Orientation"];
        }
        if (empty($orientation) && !empty($exif["IFD0"]["Orientation"])) {
            $orientation = $exif["IFD0"]["Orientation"];
        }
    }

    if (empty($orientation) || $orientation == 1) {
        return true;
    }
    
    $result = false;
    
    switch ($orientation) {
        case 3: // 180 rotate left
            $result = rotate_image($src, $target, 180);
            break;
        
        case 6: // 90 rotate right
            $result = rotate_image($src, $target, 90);
            break;
        
        case 8:    // 90 rotate left
            $result = rotate_image($src, $target, -90);
            break;
        
        default:
            trigger_error("rotate_image: Unknown orientation " . $orientation, E_USER_ERROR);
    }
    
    return $result;
} // check_attachment_rotation
//-------------------------------------------------------------------
function scale_image($src, $target, $max_x = false, $max_y = false)
{
    if (!function_exists("imagecreatefromjpeg")) {
        return false;
    }
    
    $ret = false;
    
    $src_img = image_load($src);
    if ($src_img) {
        $x = imagesx($src_img);
        $y = imagesy($src_img);
        
        // calculate new size
        if ($max_x && (!$max_y)) {
            $max_y = ceil($max_x * $y / $x);
        } elseif ($max_y && (!$max_x)) {
            $max_x = ceil($max_y * $x / $y);
        } elseif ($max_x && $max_y) {
        } else {
            $max_x = $x;
            $max_y = $y;
        }
        
        // the image is already smaller
        if ($x <= $max_x && $y <= $max_y) {
            imagedestroy($src_img);
            $ret = rename($src, $target);
            
            //$ret = image_save($src_img, $target);
            
            return $ret;
        }
        
        if (($max_x / $max_y) < ($x / $y)) {
            $target_img = imagecreatetruecolor((int)($x / ($x / $max_x)), (int)($y / ($x / $max_x)));
        } else {
            $target_img = imagecreatetruecolor((int)($x / ($y / $max_y)), (int)($y / ($y / $max_y)));
        }
        
        $target_x = imagesx($target_img);
        $target_y = imagesy($target_img);
        
        $path_parts = pathinfo($target);
        if (strtolower($path_parts['extension']) == "png" ||
            strtolower($path_parts['extension']) == "webp") {
            imagealphablending($target_img, false);
            imagesavealpha($target_img, true);
            $transparent = imagecolorallocatealpha($target_img, 255, 255, 255, 127);
            imagefilledrectangle($target_img, 0, 0, $target_x, $target_y, $transparent);
        } elseif (strtolower($path_parts['extension']) == "gif") {
            $transparent_index = imagecolortransparent($src_img);
            if ($transparent_index >= 0) {
                imagepalettecopy($src_img, $target_img);
                imagefill($target_img, 0, 0, $transparent_index);
                imagecolortransparent($target_img, $transparent_index);
                imagetruecolortopalette($target_img, true, 256);
            }
        }
        
        imagecopyresampled($target_img, $src_img, 0, 0, 0, 0, $target_x, $target_y, $x, $y);
        
        $ret = image_save($target_img, $target);
        
        imagedestroy($src_img);
        imagedestroy($target_img);
    } // if($im)
    
    return $ret;
} // scale_image
//-------------------------------------------------------------------
function rotate_image($src, $target, $degree)
{
    if (!function_exists("imagecreatefromjpeg")) {
        return false;
    }
    
    $ret = false;
    
    $src_img = image_load($src);
    if ($src_img) {
        $old_x = imagesx($src_img);
        $old_y = imagesy($src_img);
        
        if ($degree == "90" || $degree == "-90") {
            $new_x = $old_y;
            $new_y = $old_x;
        } else {
            $new_x = $old_x;
            $new_y = $old_y;
        }
        
        $target_img = imagecreatetruecolor($new_x, $new_y);
        
        $path_parts = pathinfo($target);
        if (strtolower($path_parts['extension']) == "png") {
            imagealphablending($target_img, false);
            imagesavealpha($target_img, true);
            $transparent = imagecolorallocatealpha($target_img, 255, 255, 255, 127);
            imagefilledrectangle($target_img, 0, 0, $new_x, $new_y, $transparent);
        } elseif (strtolower($path_parts['extension']) == "gif") {
            $transparent_index = imagecolortransparent($src_img);
            if ($transparent_index >= 0) {
                imagepalettecopy($src_img, $target_img);
                imagefill($target_img, 0, 0, $transparent_index);
                imagecolortransparent($target_img, $transparent_index);
                imagetruecolortopalette($target_img, true, 256);
            }
        }
        
        for ($x = 0; $x < $old_x; $x++) {
            for ($y = 0; $y < $old_y; $y++) {
                if ($degree == "90") {
                    $nx = $old_y - $y - 1;
                    $ny = $x;
                } elseif ($degree == "-90") {
                    $nx = $y;
                    $ny = $old_x - $x - 1;
                } elseif ($degree == "180") {
                    $nx = $old_x - $x - 1;
                    $ny = $old_y - $y - 1;
                } else {
                    $nx = $x;
                    $ny = $y;
                }
                
                imagecopy($target_img, $src_img, $nx, $ny, $x, $y, 1, 1);
            }
        }
        
        $ret = image_save($target_img, $target);
        
        imagedestroy($src_img);
        imagedestroy($target_img);
    } // if($im)
    
    return $ret;
} // rotate_image
//-------------------------------------------------------------------
function get_image_info($img_path, &$img_info)
{
    if (!function_exists("imagecreatefromjpeg")) {
        return false;
    }
    
    if (!file_exists($img_path)) {
        return false;
    }
    
    $img_info["size"] = filesize($img_path);
    
    if (!$img = image_load($img_path)) {
        return false;
    }
    
    $img_info["width"] = imagesx($img);
    $img_info["height"] = imagesy($img);
    
    imagedestroy($img);
    
    return true;
} // get_image_info
//-------------------------------------------------------------------
function resize_image($src, $target, $new_x, $new_y)
{
    if (!function_exists("imagecreatefromjpeg")) {
        return false;
    }
    
    $ret = false;
    
    $src_img = image_load($src);
    if ($src_img) {
        $x = imagesx($src_img);
        $y = imagesy($src_img);
        
        $target_img = imagecreatetruecolor($new_x, $new_y);
        
        $path_parts = pathinfo($target);
        if (strtolower($path_parts['extension']) == "png") {
            imagealphablending($target_img, false);
            imagesavealpha($target_img, true);
            $transparent = imagecolorallocatealpha($target_img, 255, 255, 255, 127);
            imagefilledrectangle($target_img, 0, 0, $new_x, $new_y, $transparent);
        } elseif (strtolower($path_parts['extension']) == "gif") {
            $transparent_index = imagecolortransparent($src_img);
            if ($transparent_index >= 0) {
                imagepalettecopy($src_img, $target_img);
                imagefill($target_img, 0, 0, $transparent_index);
                imagecolortransparent($target_img, $transparent_index);
                imagetruecolortopalette($target_img, true, 256);
            }
        }
        
        imagecopyresampled($target_img, $src_img, 0, 0, 0, 0, $new_x, $new_y, $x, $y);
        
        $ret = image_save($target_img, $target);
        
        imagedestroy($src_img);
        imagedestroy($target_img);
    } // if($im)
    
    return $ret;
} // resize_image
//-------------------------------------------------------------------
function is_gif_animated($filename)
{
    if (!($fh = @fopen($filename, 'rb'))) {
        return false;
    }
    
    $count = 0;
    
    //an animated gif contains multiple "frames", with each frame having a
    //header made up of:
    // * a static 4-byte sequence (\x00\x21\xF9\x04)
    // * 4 variable bytes
    // * a static 2-byte sequence (\x00\x2C) (some variants may use \x00\x21 ?)
    
    // We read through the file til we reach the end of the file, or we've found
    // at least 2 frame headers
    
    $chunk = false;
    while (!feof($fh) && $count < 2) {
        //add the last 20 characters from the previous string, to make sure the searched pattern is not split.
        $chunk = ($chunk ? substr($chunk, -20) : "") . fread($fh, 1024 * 100); //read 100kb at a time
        $count += preg_match_all('#\x00\x21\xF9\x04.{4}\x00(\x2C|\x21)#s', $chunk, $matches);
    }
    
    fclose($fh);
    
    return $count > 1;
} // is_gif_animated
//-------------------------------------------------------------------
function create_animation_thumb($filename, $outfile, $playimg_file, $is_url)
{
    if (!function_exists("imagecreatefromstring") ||
        !function_exists("imagegif")
    ) {
        return false;
    }
    
    $ctx = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ]);
    
    if (!($fh = @fopen($filename, 'rb', false, $ctx))) {
        return false;
    }
    
    //an animated gif contains multiple "frames", with each frame having a
    //header made up of:
    // * a static 4-byte sequence (\x00\x21\xF9\x04)
    // * 4 variable bytes
    // * a static 2-byte sequence (\x00\x2C) (some variants may use \x00\x21 ?)
    
    // We read through the file til we reach the end of the file, or we've found
    // at least 2 frame headers
    
    $contents = "";
    $img_type = "";
    
    $count = 0;
    $chunk = false;
    while (!feof($fh) && $count < 2) {
        $part = fread($fh, 1024 * 100);
        
        if (empty($img_type)) {
            if (strpos($part, "GIF") === 0) {
                $img_type = "gif";
            } elseif (strpos($part, "RIFF") === 0) {
                $img_type = "webp";
            }
        }
        
        $contents .= $part;
        
        if ($img_type == "gif") {
            //add the last 20 characters from the previous string, to make sure the searched pattern is not split.
            $chunk = ($chunk ? substr($chunk, -20) : "") . $part; //read 100kb at a time
            
            $count += preg_match_all('#\x00\x21\xF9\x04.{4}\x00(\x2C|\x21)#s', $chunk, $matches);
        } elseif ($img_type == "webp") {
            if (preg_match("#ANMF(.+?)ANMF#s", $contents, $matches)) {
                $contents = substr($matches[1], 20);
                $fileSize = bytesToString(toUint32(strlen($contents)+4));
                $fileHeader = "RIFF".$fileSize."WEBP";
                $contents = $fileHeader.$contents;
                break;
            }
        }
    }
    
    fclose($fh);

    if ($img_type != "gif" && $img_type != "webp") {
        return false;
    }
    
    $img = @imagecreatefromstring($contents);
    if (!$img) {
        return false;
    }
    
    $playimg = @image_load($playimg_file);
    if (!$playimg) {
        return false;
    }
    
    // put play button if img large enough
    if (imagesx($img) > 2 * imagesx($playimg) &&
        imagesy($img) > 2 * imagesy($playimg)
    ) {
        if (!imagecopy($img, $playimg, (imagesx($img) / 2) - (imagesx($playimg) / 2), (imagesy($img) / 2) - (imagesy($playimg) / 2), 0, 0, imagesx($playimg), imagesy($playimg))) {
            return false;
        }
    }
    
    $result = @imagejpeg($img, $outfile, 100);
    
    if ($result) {
        touch($outfile, is_file($filename) ? filemtime($filename) : time());
    }
    
    return $result;
} // create_animation_thumb
//-------------------------------------------------------------------
function create_latex_png($formula, $density, $out_file)
{
    $hash = sha1(serialize(array('formula' => $formula, 'density' => $density)));
    
    $temp_dir = APPLICATION_ROOT . "tmp/";
    //$packages = array('amssymb,amsmath', 'color', 'amsfonts', 'amssymb', 'pst-plot');
    $packages = array('amssymb,amsmath', 'color', 'amsfonts', 'amssymb');
    
    $tex = '\documentclass[12pt]{article}' . "\n";
    
    $tex .= '\usepackage[utf8]{inputenc}' . "\n";
    
    // Packages
    foreach ($packages as $package) {
        $tex .= '\usepackage{' . $package . "}\n";
    }
    
    $tex .= '\begin{document}' . "\n";
    $tex .= '\pagestyle{empty}' . "\n";
    $tex .= '\begin{displaymath}' . "\n";
    
    $tex .= $formula . "\n";
    
    $tex .= '\end{displaymath}' . "\n";
    $tex .= '\end{document}' . "\n";
    
    if (file_put_contents($temp_dir . $hash . ".tex", $tex) === false) {
        throw new \Exception('Failed to create tex file!');
    }
    
    $command = 'cd ' . $temp_dir . '; ' . 'export HOME=/home/apache; /usr/bin/latex' . ' ' . $hash . '.tex < /dev/null | grep ^! | grep -v Emergency > ' . $temp_dir . $hash . '.err > /dev/null 2>&1';

    if (shell_exec($command) === null) {
        //throw new \Exception('Unable to compile LaTeX formula!');
    }
    
    if (!file_exists($temp_dir . $hash . '.dvi')) {
        throw new \Exception('Unable to compile LaTeX formula!');
    }
    
    $command = '/usr/bin/dvipng' . " -q -T tight -D " . $density . ' -o ' . $out_file . ' ' . $temp_dir . $hash . '.dvi 2>&1';
    
    if (shell_exec($command) === null) {
        throw new \Exception('Unable to convert the DVI file to PNG!');
    }

    if (!file_exists($out_file)) {
        throw new \Exception('Unable to convert the DVI file to PNG!');
    }
    
    @shell_exec('rm -f ' . $temp_dir . $hash . '.* 2>&1');
} // create_latex_png
//-------------------------------------------------------------------
?>