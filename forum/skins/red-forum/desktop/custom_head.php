<!-- write your adjustments here -->

<!-- Google Fonts for Kroleg -->
<link rel="stylesheet" href="<?php echo($view_path); ?>css/fonts_googleapis.css<?php echo($cache_appendix); ?>" type="text/css">

<!-- -->
<meta name="author" content="Programmizd 02 | Программизд 02">
<meta name="keywords" content="дедофорум, nosql.ru, sql.ru, просто трёп">
<!-- -->

<?php
$url = $_SERVER['REQUEST_SCHEME'].$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
if (strpos($url,'fid=8') == true && strpos($url,'tid=15984') == false) {
  //if (strpos($url,'tid=15984') !== true) {
    //include($_SERVER['DOCUMENT_ROOT'] . '/nosql_forum_pigeon.txt');
    echo'
    <style>
      .author_post_a5ed32b3ee70913566e2b92ef7f251c4
      {
        display: none !important;
      }
    </style>    
    ';
  //}
}
if (strpos($url,'tid=15984') == true) { 
  //include($_SERVER['DOCUMENT_ROOT'] . '/nosql_forum_pigeon.txt');
  echo'
  <style>
    .author_post_a5ed32b3ee70913566e2b92ef7f251c4 img
    {
      display: none !important;
    }
  </style>    
  ';
}
?>

