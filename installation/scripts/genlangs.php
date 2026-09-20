<?php

require_once "../forum/include/application_root_inc.php";

// common utility functions
require_once APPLICATION_ROOT . "include/utility_functions_inc.php";

$LANG_DOUBLETS = array();
$COUNTRY_DOUBLETS = array();
$TEXT_DOUBLETS = array();

$FOUND_LANGS = array();
$FOUND_COUNTRIES = array();
$FOUND_TEXTS = array();

//--------------------------------------------------------------------------
function convert_file($src_dir, $key)
{
  global $LANUAGES;

  global $LANG_DOUBLETS;
  global $COUNTRY_DOUBLETS;
  global $TEXT_DOUBLETS;

  global $FOUND_LANGS;
  global $FOUND_COUNTRIES;
  global $FOUND_TEXTS;

  echo "creating file texts.$key.php\n";

  $xmldoc = new DOMDocument();
  if(!$xmldoc->load($src_dir . "texts.xml"))
  {
    echo "failed to open the source file\n";
    exit;
  }

  $xsdpath = new DOMXPath($xmldoc);

  $document = $xmldoc->documentElement;

  $content = "<?php\n";

  $auxkey = $key;
  if($auxkey == "neru") $auxkey = "ru";
    
  $nodes = $xsdpath->evaluate("/document/texts/text/$auxkey");
  foreach($nodes as $node)
  {
    $pnode = $node->parentNode;

    $id = $pnode->getAttribute("id");

    if(!empty($FOUND_TEXTS[$key]) && in_array($id, $FOUND_TEXTS[$key]) && !preg_match("/dep_mod_.+/", $id))
    {
      $TEXT_DOUBLETS[$id] = $id;
    }
    else
    {
      $FOUND_TEXTS[$key][$id] = $id;
    }

    $text = $node->nodeValue;
    
    if($id == "DateTimeFormat" && $key == "neru") $text = str_replace("d.m.Y, H:i", "d.m.Y, H:i:s", $text);
    
    $content .= "\$GLOBALS['DEF_TEXTS']['" . $key . "']['" . $id . "'] = '" . escape_php($text) . "';\n";
  }

  $content .= "?>";

  if(!file_exists($src_dir . $key)) mkdir($src_dir . $key);

  $out = fopen($src_dir . $key . "/texts.php", "w");
  if(!$out)
  {
    echo "failed to open the target file\n";
    exit;
  }

  fwrite($out, $content);

  @fclose($out);
} // convert_file
//--------------------------------------------------------------------------

$xmldoc = new DOMDocument();
if(@$xmldoc->load(APPLICATION_ROOT . "lang/texts.xml"))
{
  $xsdpath = new DOMXPath($xmldoc);

  $nodes = $xsdpath->evaluate("/document/interface_languages/language");

  foreach($nodes as $node)
  {
    $LANUAGES[] = $node->getAttribute("id");
  }

  echo "Main application:\n\n";

  foreach($LANUAGES as $lang)
  {
    convert_file(APPLICATION_ROOT . "lang/", $lang);
  }

  if(count($TEXT_DOUBLETS) > 0)
  {
    echo "Text doublets:\n\n";
    foreach($TEXT_DOUBLETS as $ctr)
    {
      echo $ctr . "\n";
    }
  }
}
else
{
  echo "Loading failed\n";
  exit;
}

?>