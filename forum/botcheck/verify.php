<?php
// verify.php — принимает результат JS-проверки, пересчитывает всё сам
// (клиенту не доверяем — только сырые данные, вычисления повторяются на сервере)

session_set_cookie_params(0, str_replace(basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "../include/session_start_inc.php";
require_once "../include/general_inc.php";

header('Content-Type: application/json; charset=utf-8');


$cfg = require __DIR__ . '/bot_config.php';
require_once __DIR__ . '/bot_lib.php';

$body = json_decode((string) file_get_contents('php://input'), true);

if (!is_array($body)) {
    http_response_code(400);
    echo json_encode(['action' => 'error']);
    exit;
}

$data = bp_read_token($body['ticket'] ?? null, $cfg['secret_key']);

if (!$data || ($data['exp'] ?? 0) < time()) {
    http_response_code(400);
    echo json_encode(['action' => 'error']);
    exit;
}

// --- Пересчитываем proof-of-work сами, не веря присланному хэшу на слово ---
$seed       = $data['challenge_seed'] ?? '';
$difficulty = $data['difficulty'] ?? $cfg['difficulty'];
$nonce      = $body['pow_nonce'] ?? null;
$claimedHash = (string) ($body['pow_hash'] ?? '');
$powOk = false;

if ($seed !== '' && $nonce !== null && is_numeric($nonce)) {
    $realHash = hash('sha256', $seed . ':' . (int) $nonce);
    $powOk = hash_equals($realHash, $claimedHash)
        && str_starts_with($realHash, str_repeat('0', $difficulty));
}

// --- Клиентский скоринг ---
$clientScore = 100; // максимум по умолчанию, только вычитаем за красные флаги

if (!empty($body['webdriver']))                    $clientScore -= 40; // navigator.webdriver=true — почти всегда автоматизация
if (empty($body['canvas']))                         $clientScore -= 20; // canvas недоступен/заблокирован
if (!$powOk)                                        $clientScore -= 40; // PoW не сошёлся или подделан
if ((int) ($body['elapsed_ms'] ?? 0) < 5)           $clientScore -= 10; // подозрительно мгновенное решение
if (empty($body['screen']) || $body['screen'] === '0x0x0') $clientScore -= 15;

$clientScore = max(0, min(100, $clientScore));
$serverScore = (int) ($data['server_score'] ?? 100);
$totalScore  = (int) round($serverScore * 0.4 + $clientScore * 0.6);

// Отладочный лог — включить 'debug_log' => true в bot_config.php на время калибровки порога,
// затем выключить (в проде писать на диск на каждый запрос не нужно).
if (!empty($cfg['debug_log'])) {
    trace_message_to_file(sprintf(
        '[botcheck] ip=%s server=%d client=%d total=%d pow_ok=%s webdriver=%s canvas=%s elapsed_ms=%s screen=%s',
        bp_client_ip(), $serverScore, $clientScore, $totalScore,
        $powOk ? 'yes' : 'no',
        !empty($body['webdriver']) ? 'yes' : 'no',
        empty($body['canvas']) ? 'empty' : 'ok',
        $body['elapsed_ms'] ?? '?',
        $body['screen'] ?? '?'
    ), "bot_check.log");
}

if ($totalScore >= $cfg['pass_threshold']) {
    bp_issue_pass_cookie($cfg, bp_client_ip());
    echo json_encode(['action' => 'redirect', 'url' => $data['return_url'] ?? '/']);
} else {
    echo json_encode([
        'action' => 'captcha',
        'url'    => $cfg['captcha_url'] . '?ticket=' . urlencode($body['ticket']),
    ]);
}
