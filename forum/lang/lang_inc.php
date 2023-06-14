<?php
$dir = APPLICATION_ROOT . "lang/";
$files = scandir($dir);
$GLOBALS['LANGUAGES_TOTAL'] = array();
$GLOBALS['LANGUAGES'] = array();
foreach($files as $file)
{
  if($file == "." || $file == ".." || !is_dir($dir . $file)) continue;

  $GLOBALS['LANGUAGES_TOTAL'][] = $file;

  $GLOBALS['LANGUAGES'] = array_intersect($GLOBALS['LANGUAGES_TOTAL'], $ACTIVE_LANGUAGES);
  $GLOBALS['LANGUAGES'] = array_values($GLOBALS['LANGUAGES']);
}
//-----------------------------------------------------------------
function load_lang_dictionary($lng)
{
  if(!isset($GLOBALS['DEF_TEXTS'][$lng]))
  {
    $file = APPLICATION_ROOT . "lang/" . $lng . "/texts.php";

    if(file_exists($file))
      require $file;
  }
} // load_lang_dictionary
//-----------------------------------------------------------------
function text($id, $lng = "")
{
  if(empty($lng))
  {
    $lng = current_language();
  }

  load_lang_dictionary($lng);

  if(empty($GLOBALS['DEF_TEXTS'][$lng][$id]))
  {
    trigger_error("No translation for '$id' in language [$lng]", E_USER_NOTICE);
    return $id . "(!)";
  }

  return $GLOBALS['DEF_TEXTS'][$lng][$id];
} // text
//-----------------------------------------------------------------
function try_translate($id, $lng = "")
{
  if(empty($lng))
  {
    $lng = current_language();
  }

  load_lang_dictionary($lng);

  if(empty($GLOBALS['DEF_TEXTS'][$lng][$id]))
  {
    return $id;
  }

  return $GLOBALS['DEF_TEXTS'][$lng][$id];
} // try_translate
//-----------------------------------------------------------------
function has_translation($id, $lng = "")
{
  if(empty($lng))
  {
    $lng = current_language();
  }

  load_lang_dictionary($lng);

  return (!empty($GLOBALS['DEF_TEXTS'][$lng][$id]));
} // has_translation
//-----------------------------------------------------------------
function aux_translate($id, $lang_key = "")
{
  if(has_translation($id, $lang_key))
    return try_translate($id, $lang_key);
  else
    return try_translate($id, "en") . "[translate]";
} // aux_translate
//-------------------------------------------------------------------
function echo_text($id, $lng = "")
{
  echo text($id, $lng);
} // echo_text
//-----------------------------------------------------------------
function current_language()
{
  $lng = "";

  if(!empty($_SESSION["current_language"])) $lng = $_SESSION["current_language"];

  if(empty($lng)) $lng = defined('DEFAULT_LANGUAGE') ? DEFAULT_LANGUAGE : "en";

  return $lng;
} // current_language
//-----------------------------------------------------------------
function set_language($lang)
{
  if(empty($lang)) $lang = defined('DEFAULT_LANGUAGE') ? DEFAULT_LANGUAGE : "en";

  $_SESSION["current_language"] = $lang;
  
  if(count($GLOBALS['LANGUAGES']) > 0 && !in_array($_SESSION["current_language"], $GLOBALS['LANGUAGES']))
  {
    $_SESSION["current_language"] = $GLOBALS['LANGUAGES'][0];
  }
  
  set_cookie("q_interface_language", current_language(), time() + 90 * 24 * 3600);
} // set_language
//-----------------------------------------------------------------

//-----------------------------------------------------------------
// text("en")
// text("de")
// text("ru")
// text("ua")
// text("neru")
//-----------------------------------------------------------------
if(isset($_GET["lang"]))
{
  set_language($_GET["lang"]);
}

if(empty($_SESSION["current_language"]))
{
  set_language(defined('DEFAULT_LANGUAGE') ? DEFAULT_LANGUAGE : "en");
}
//-----------------------------------------------------------------
?>