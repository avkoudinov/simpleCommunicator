<?php
// captcha.php — финальный шаг проверки антибот-системы.
// Использует УЖЕ ИМЕЮЩУЮСЯ капчу проекта (папка captcha/, библиотека SimpleCaptcha),
// без Cloudflare и без какой-либо самодельной генерации картинок.
//
// ВАЖНО: этот файл — не то же самое, что captcha/captcha.php (это обёртка-страница
// с формой; captcha/captcha.php — сам генератор картинки, он вызывается из <img>).

session_set_cookie_params(0, str_replace(basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "../include/session_start_inc.php";
require_once "../include/general_inc.php";

header('Cache-Control: no-store');
header('Pragma: no-cache');
header('Expires: 0');

$cache_appendix = "?v=" . $skin_version;

$cfg = require __DIR__ . '/bot_config.php';
require_once __DIR__ . '/bot_lib.php';

// Подключаем тот же самый инклюд, что стартует сессию на остальных страницах сайта,
// чтобы кука сессии была ОБЩЕЙ с captcha/captcha.php (он использует $_SESSION).
require_once "../include/session_start_inc.php";

$ticket = $_GET['ticket'] ?? ($_POST['ticket'] ?? '');
$data = bp_read_token($ticket, $cfg['secret_key']);

$sessionVar = $cfg['captcha_session_var'];
$link_expired = false;
$error = '';

if (!$data || ($data['exp'] ?? 0) < time()) {
    $link_expired = true;
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userInput = trim((string)($_POST['captcha_field'] ?? ''));
        $realCode = $_SESSION[$sessionVar] ?? null;

        if ($realCode !== null && $userInput !== '' && strcasecmp($userInput, $realCode) === 0) {
            unset($_SESSION[$sessionVar]); // код одноразовый — использован, больше не годится
            bp_issue_pass_cookie($cfg, bp_client_ip());
            header('Location: ' . ($data['return_url'] ?? '/'));
            exit;
        }

        $error = escape_html(text("BrowserCheckCaptchaError"));
    }
}
?><!DOCTYPE html>
<html lang="<?php echo(current_language()); ?>">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/style.css<?php echo($cache_appendix); ?>" type="text/css">
    <title><?php echo_html(text("BrowserCheck")); ?></title>

    <script>
        function showField() {
            let field = document.getElementById('captcha_field');
            field.scrollIntoView({block: 'center', behavior: 'smooth'});
        }

        document.addEventListener('load', function () {
            setTimeout(showField, 300);
        });

        // Клавиатура появилась или скрылась: пересчитываем положение
        if (window.visualViewport) {
            window.visualViewport.addEventListener('resize', showField);
        }
    </script>
</head>
<body>
<div class="box">
    <?php if ($link_expired): ?>

        <p class='error'><?php echo_html(text("BrowserCheckExpired")); ?></p>

        <div class='warning'></div>

        <p>
            <a href="<?php echo($data['return_url'] ?? '/'); ?>"><?php echo_html(text("BrowserCheckCaptchaTryAgain")); ?></a>
        </p>

    <?php else: ?>

        <img class="captcha_picture" id="captcha_picture"
             src="../captcha/captcha.php?rnd=<?= rand(1000, 9999) ?>&session_var=<?= urlencode($sessionVar) ?>"
             alt="Captcha"
             onclick="reloadCaptcha()">

        <p><?php echo_html(text("BrowserCheckCaptchaEnterText")); ?></p>

        <form method="post">
            <input type="hidden" name="ticket" value="<?= htmlspecialchars($ticket) ?>">
            <input type="text" id="captcha_field" name="captcha_field" autocomplete="off" autofocus required>
            <br>
            <?php if ($error): ?><p class="error captcha_error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
            <button type="submit"><?php echo_html(text("Send")); ?></button>
        </form>

    <?php endif; ?>
</div>
<script>
    function reloadCaptcha() {
        var img = document.getElementById('captcha_picture');
        img.src = '../captcha/captcha.php?rnd=' + Math.round(Math.random() * 1000)
            + '&force_new=1&session_var=<?= urlencode($sessionVar) ?>';
        var field = document.getElementById('captcha_field');
        field.value = '';
        field.focus();
    }
</script>
</body>
</html>
