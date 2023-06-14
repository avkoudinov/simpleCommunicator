<?php
//------------------------------------------------------------------
session_set_cookie_params(0, "");
require_once "include/session_start_inc.php";
require_once "include/general_inc.php";
//------------------------------------------------------------------
$title = text("Installation");
MessageHandler::setFocusElement("forum_name");
//------------------------------------------------------------------
$_SESSION["install_lang"] = reqvar("lang");

if(!empty($_SESSION["install_lang"]) && !in_array($_SESSION["install_lang"], $ACTIVE_LANGUAGES))
{
  $_SESSION["install_lang"] = "en";
}
//------------------------------------------------------------------
require_once "include/final_inc.php";
//------------------------------------------------------------------
$view = "installation.php";
require_once $view_path . "carcass.php";
//------------------------------------------------------------------
?>