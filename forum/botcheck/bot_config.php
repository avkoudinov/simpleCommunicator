<?php
// bot_config.php — общие настройки антибот-системы
// СГЕНЕРИРУЙ свой secret_key: php -r "echo bin2hex(random_bytes(32));"
// и вынеси его в переменную окружения / .env — не храни в файле,
// который может попасть в публичный репозиторий.

return [
    'secret_key' => SALT_KEY,
    'cookie_name' => 'q_bot55check_pass',
    'cookie_secure' => (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? null) == 443)
        || (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'),
    'cookie_ttl' => 3600 * 12,   // на сколько выдаётся пропуск после успешной проверки
    'ticket_ttl' => 120,         // сколько секунд действует тикет проверки (5 минут)
    'pass_threshold' => 70,          // итоговый балл (0-100), от которого пропускаем без капчи
    'difficulty' => 4,           // сложность proof-of-work (кол-во нулей в начале хэша)
    'debug_log' => true,

    'challenge_url' => 'botcheck/challenge.php',
    'verify_url' => 'verify.php',
    'captcha_url' => 'captcha.php',

    'rate_limit_dir' => sys_get_temp_dir() . '/botcheck_rl',
    'rate_limit_max' => 60,   // макс. запросов
    'rate_limit_window' => 60,   // за это окно в секундах

    // Интеграция с уже имеющейся капчей проекта (папка captcha/, библиотека SimpleCaptcha)
    'captcha_session_var' => 'antibot_captcha', // отдельное имя сессионной переменной — не конфликтует с капчей форума

    'bad_ua_patterns' => [
        '/curl/i', '/wget/i', '/python-requests/i', '/scrapy/i',
        '/httpclient/i', '/^$/', '/headless/i',
        '/bot(?!.*(googlebot|bingbot|yandexbot))/i',
    ],

    // Известные "хорошие" боты — пропускаем без челленджа (по UA, упрощённо).
    // Для боевого использования стоит доп. проверять обратный DNS (PTR-запись),
    // иначе злоумышленник может просто подставить такой User-Agent.
    'good_bots' => [
        '/googlebot/i', '/bingbot/i', '/yandexbot/i',
    ],
];
