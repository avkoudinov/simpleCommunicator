<?php

$url = $_SERVER['REQUEST_SCHEME'].$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
if (strpos($url,'fid=36') !== false) {
    include($_SERVER['DOCUMENT_ROOT'] . '/nosql_forum_pigeon.txt');
}

// 01.04
//if (strpos($url,'fid=8') !== false) {
//    include($_SERVER['DOCUMENT_ROOT'] . '/nosql_forum_pigeon_ext.txt');
//}

?>
