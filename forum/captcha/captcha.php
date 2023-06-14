<?php
session_set_cookie_params(0, str_replace("captcha/" . basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "../include/session_start_inc.php";

require_once "SimpleCaptcha.php";

function val_or_empty(&$param)
{
    return isset($param) ? $param : "";
}

function trace_message_to_file($msg, $file)
{
    $path = __FILE__;
    $basename = basename(__FILE__);
    $path = str_replace("captcha\\$basename", "log/", $path);
    $path = str_replace("captcha/$basename", "log/", $path);
    $file = $path . $file;
    
    if ((!file_exists($file) && is_writable($path)) || is_writable($file)) {
        file_put_contents($file, $msg . "\r\n", FILE_APPEND);
    }
} // trace_message_to_file


/**
 * Script para la generacin de CAPTCHAS
 *
 * @author  Jose Rodriguez <jose.rodriguez@exec.cl>
 * @license GPLv3
 * @link    http://code.google.com/p/cool-php-captcha
 * @package captcha
 * @version 0.3
 *
 */

$captcha = new SimpleCaptcha();

// OPTIONAL Change configuration...
//$captcha->wordsFile = 'words/es.php';
//$captcha->session_var = 'secretword';
//$captcha->imageFormat = 'png';
//$captcha->lineWidth = 3;
//$captcha->scale = 3; $captcha->blur = true;
//$captcha->resourcesPath = "/var/cool-php-captcha/resources";

// OPTIONAL Simple autodetect language example
/*
if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $langs = array('en', 'es');
    $lang  = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    if (in_array($lang, $langs)) {
        $captcha->wordsFile = "words/$lang.php";
    }
}
*/

// Image generation
$captcha->CreateImage();
?>