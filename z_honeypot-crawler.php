<?php
function trapString(int $length): string
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $count = strlen($chars);
    $result = '';

    for ($i = 0; $i < $length; $i++) {
        $result .= $chars[mt_rand(0, $count - 1)];
    }

    return $result;
}

//echo trapString(16);
?>
<?php //echo $_SERVER['SERVER_NAME']; ?>

<a href="/<?php echo strstr($_SERVER['SERVER_NAME'],'.',true); ?>-news-<?php echo trapString(mt_rand(8,16)); ?>/" style="display: none;">

