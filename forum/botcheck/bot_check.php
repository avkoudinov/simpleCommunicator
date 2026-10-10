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

// --- 1. Пропускная кука уже есть и валидна — ничего не делаем, страница работает как обычно ---
$cookieData = bp_read_token($_COOKIE[$cfg['cookie_name']] ?? null, $cfg['secret_key']);

if ($cookieData
    && ($cookieData['exp'] ?? 0) > time()
    && ($cookieData['ip_bucket'] ?? null) === bp_ip_bucket(bp_client_ip())
) {
    return; // всё ок, выполнение страницы продолжается как обычно
}

// --- 2. Доверенные боты (по UA) пропускаются без челленджа ---
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
foreach ($cfg['good_bots'] as $pattern) {
    if (preg_match($pattern, $ua)) {
        return;
    }
}

// --- 3. Rate limiting ---
if (bp_rate_limited(bp_client_ip(), $cfg)) {
    http_response_code(429);
    header('Retry-After: 30');
    echo 'Too many requests';
    exit;
}

// --- 4. Серверный скоринг по заголовкам запроса ---
$serverScore = bp_server_score($cfg);

// --- 5. Запоминаем, куда шёл пользователь, и формируем тикет проверки ---
$scheme    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$returnUrl = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '/');

$ticket = bp_make_token([
    'return_url'   => $returnUrl,
    'server_score' => $serverScore,
    'ip_bucket'    => bp_ip_bucket(bp_client_ip()),
    'nonce'        => bin2hex(random_bytes(8)),
    'exp'          => time() + $cfg['ticket_ttl'],
], $cfg['secret_key']);

header('Location: ' . $cfg['challenge_url'] . '?ticket=' . urlencode($ticket));
exit;
