<?php
// bot_lib.php — вспомогательные функции, используются во всех файлах антибот-системы

// ---------- Подписанные токены (HMAC), без серверного хранилища ----------

function bp_base64url_encode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function bp_base64url_decode(string $data): string {
    $pad = strlen($data) % 4;
    if ($pad) $data .= str_repeat('=', 4 - $pad);
    return (string) base64_decode(strtr($data, '-_', '+/'));
}

function bp_make_token(array $payload, string $secret): string {
    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
    $body = bp_base64url_encode($json);
    $sig  = bp_base64url_encode(hash_hmac('sha256', $body, $secret, true));
    return $body . '.' . $sig;
}

function bp_get_client_data(): string
{
    $data = "";
    $data .= "User-Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\n";
    $data .= "IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
    $data .= "URL: " . ($_SERVER['REQUEST_URI'] ?? '/') . "\n";

    return $data;
}
function br_trace_checking($message, &$cfg)
{
    if (empty($cfg['debug_log'])) return;

    trace_message_to_file($message . "------------------------------------------", "bot_check.log");
}
function bp_read_token(?string $token, string $secret, &$error): ?array {
    if (!$token) {
        $error = 'Ticket empty';
        return null;
    }

    if (strpos($token, '.') === false) {
        $error = 'Ticket invalid';
        return null;
    }

    [$body, $sig] = explode('.', $token, 2);
    $expected = bp_base64url_encode(hash_hmac('sha256', $body, $secret, true));

    if (!hash_equals($expected, $sig)) {
        $error = 'Ticket manipulated';
        return null;
    } // подпись не сходится — токен подделан/битый

    $data = json_decode(bp_base64url_decode($body), true);

    if (!is_array($data)) {
        $error = 'Ticket invalid';
        return null;
    }

    return $data;
}

// ---------- IP ----------

function bp_client_ip(): string {
    // ВАЖНО: если сайт за nginx/CDN, X-Forwarded-For нужно доверять только от
    // известных доверенных прокси (иначе клиент сам подделает заголовок и обойдёт rate-limit).
    // Здесь — упрощённый вариант, доработать под свою инфраструктуру.
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($parts[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// ---------- Серверный скоринг (по заголовкам запроса) ----------

function bp_server_score(array $cfg): int {
    $score = 100; // максимум по умолчанию, только вычитаем за красные флаги
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

    foreach ($cfg['bad_ua_patterns'] as $pattern) {
        if (preg_match($pattern, $ua)) { $score -= 30; break; }
    }

    if (empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) $score -= 10;

    // Chromium-браузеры почти всегда шлют sec-ch-ua / Sec-Fetch-*
    if (empty($_SERVER['HTTP_SEC_FETCH_SITE']) && empty($_SERVER['HTTP_SEC_CH_UA'])) {
        $score -= 10;
    }

    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    if ($accept === '*/*' || $accept === '') $score -= 10;

    return max(0, min(100, $score));
}

// ---------- Выдача пропускной куки ----------

function bp_issue_pass_cookie(array $cfg, string $ip): void {
    $token = bp_make_token([
        'exp'       => time() + $cfg['cookie_ttl'],
    ], $cfg['secret_key']);
    
    setcookie($cfg['cookie_name'], $token, [
        'expires'  => time() + $cfg['cookie_ttl'],
        'path'     => str_replace("botcheck/" . basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]),
        'secure'   => $cfg['cookie_secure'] ?? true, // на http:// (локальный *.local) ставь cookie_secure=false в конфиге
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}
