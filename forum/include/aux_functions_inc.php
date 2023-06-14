<?php
//------------------------------------------------------------------------------
function aux_get_encoding()
{
  if (PHP_VERSION_ID < 50600) 
  {
    return iconv_get_encoding('internal_encoding');
  } 
  else 
  {
    return ini_get('default_charset');
  }
}
//------------------------------------------------------------------------------
function aux_set_encoding()
{
  if (PHP_VERSION_ID < 50600) 
  {
    iconv_set_encoding('internal_encoding', 'UTF-8');
  } 
  else 
  {
    return ini_set('default_charset', 'UTF-8');
  }
}
//------------------------------------------------------------------------------
?>