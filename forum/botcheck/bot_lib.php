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

function bp_read_token(?string $token, string $secret): ?array {
    if (!$token || strpos($token, '.') === false) return null;
    [$body, $sig] = explode('.', $token, 2);
    $expected = bp_base64url_encode(hash_hmac('sha256', $body, $secret, true));
    if (!hash_equals($expected, $sig)) return null; // подпись не сходится — токен подделан/битый
    $data = json_decode(bp_base64url_decode($body), true);
    return is_array($data) ? $data : null;
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

function bp_ip_bucket(string $ip): string {
    // Кука привязывается не к точному IP (ломает мобильный интернет/CGNAT),
    // а к укрупнённому диапазону — /24 для IPv4.
    if (strpos($ip, '.') !== false) {
        $parts = explode('.', $ip);
        return implode('.', array_slice($parts, 0, 3));
    }
    return substr($ip, 0, 19); // грубое укрупнение для IPv6
}

// ---------- Rate limiting (файловый, без Redis) ----------

function bp_rate_limited(string $ip, array $cfg): bool {
    $dir = $cfg['rate_limit_dir'];
    if (!is_dir($dir)) @mkdir($dir, 0700, true);

    $file = $dir . '/' . md5($ip) . '.json';
    $now  = time();
    $data = ['count' => 0, 'window_start' => $now];

    if (is_file($file)) {
        $decoded = json_decode((string) @file_get_contents($file), true);
        if (is_array($decoded)) $data = $decoded;
    }

    if ($now - $data['window_start'] > $cfg['rate_limit_window']) {
        $data = ['count' => 0, 'window_start' => $now];
    }

    $data['count']++;
    @file_put_contents($file, json_encode($data));

    return $data['count'] > $cfg['rate_limit_max'];
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
        'ip_bucket' => bp_ip_bucket($ip),
    ], $cfg['secret_key']);

    setcookie($cfg['cookie_name'], $token, [
        'expires'  => time() + $cfg['cookie_ttl'],
        'path'     => '/',
        'secure'   => $cfg['cookie_secure'] ?? true, // на http:// (локальный *.local) ставь cookie_secure=false в конфиге
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}
