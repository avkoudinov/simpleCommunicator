<?php
// bot_check.php
//
// Подключать первой строкой в каждой точке входа:
//     require_once __DIR__ . '/bot_check.php';
//
// Либо (лучше — не забудешь ни один файл) прописать как auto_prepend_file
// в php.ini или в конфиге виртуального хоста:
//     php_admin_value auto_prepend_file /path/to/bot_check.php

$cfg = require __DIR__ . '/bot_config.php';
require_once __DIR__ . '/bot_lib.php';

$_SESSION["bot_check_client_data"] = bp_get_client_data();

if (file_exists(APPLICATION_ROOT . "user_data/config/ip_white_list.txt")) {
    $ip_white_list = file_get_contents(APPLICATION_ROOT . "user_data/config/ip_white_list.txt");

    $ip_white_list = preg_split("/[\n\r]+/", $ip_white_list, -1, PREG_SPLIT_NO_EMPTY);

    $ip = bp_client_ip();
    foreach ($ip_white_list as $ip_sample) {
        if (strpos($ip, $ip_sample) === 0) {
            br_trace_checking($_SESSION["bot_check_client_data"] . "Check passed: IP '" . $ip . "' is whitelisted\n", $cfg);
            return;
        }
    }
}

// --- 1. Пропускная кука уже есть и валидна — ничего не делаем, страница работает как обычно ---
$token_error = "";
$cookieData = bp_read_token($_COOKIE[$cfg['cookie_name']] ?? null, $cfg['secret_key'], $token_error);

if ($cookieData && ($cookieData['exp'] ?? 0) > time()) {
    return; // всё ок, выполнение страницы продолжается как обычно
}

// --- 2. Доверенные боты (по UA) пропускаются без челленджа ---
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
foreach ($cfg['good_bots'] as $pattern) {
    if (preg_match($pattern, $ua)) {
        br_trace_checking($_SESSION["bot_check_client_data"] . "Check passed: Allowed bot\n", $cfg);
        return;
    }
}

// Check not passed, find out the reason

$_SESSION["bot_check_challenge_reason"] = "Verification required: reason unknown\n";

if (empty($_COOKIE[$cfg['cookie_name']])) {
    $_SESSION["bot_check_challenge_reason"] = "Verification required: no ticket cookie, cookie live time: " . ($cfg["cookie_ttl"] / 3600)  . " hours\n";
} elseif (!empty($token_error)) {
    $_SESSION["bot_check_challenge_reason"] = "Verification required: $token_error\n";
} elseif ($cookieData && ($cookieData['exp'] ?? 0) < time()) {
    $_SESSION["bot_check_challenge_reason"] = "Verification required: Ticket expired on " . date("d.m.Y H:i:s", ($cookieData['exp'] ?? 0)) . "\n";
}

$_SESSION["bot_check_client_data"] .= $_SESSION["bot_check_challenge_reason"];

// --- 3. Серверный скоринг по заголовкам запроса ---
$serverScore = bp_server_score($cfg);

// --- 5. Запоминаем, куда шёл пользователь, и формируем тикет проверки ---
$scheme    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$returnUrl = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '/');

$ticket = bp_make_token([
    'return_url'   => $returnUrl,
    'server_score' => $serverScore,
    'nonce'        => bin2hex(random_bytes(8)),
    'exp'          => time() + $cfg['ticket_ttl'],
], $cfg['secret_key']);

header('Location: ' . $cfg['challenge_url'] . '?ticket=' . urlencode($ticket));
exit;
