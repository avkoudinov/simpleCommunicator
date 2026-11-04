<?php
// challenge.php — страница, на которой выполняется клиентская JS-проверка

session_set_cookie_params(0, str_replace("botcheck/" . basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "../include/session_start_inc.php";
require_once "../include/general_inc.php";

header('Cache-Control: no-store');
header('Pragma: no-cache');
header('Expires: 0');

$cache_appendix = "?v=" . $skin_version;

$cfg = require __DIR__ . '/bot_config.php';
require_once __DIR__ . '/bot_lib.php';

$token_error = "";
$incomingTicket = $_GET['ticket'] ?? '';
$data = bp_read_token($incomingTicket, $cfg['secret_key'], $token_error);

$link_expired = false;
if (!$data || ($data['exp'] ?? 0) < time()) {
    $link_expired = true;
}

// Добавляем в тикет параметры proof-of-work — verify.php потом пересчитает хэш сам,
// а не будет доверять тому, что скажет клиент.
$difficulty = $cfg['difficulty'];
$challengeSeed = bin2hex(random_bytes(8));

$richTicket = bp_make_token(array_merge($data, [
        'challenge_seed' => $challengeSeed,
        'difficulty' => $difficulty,
]), $cfg['secret_key']);
?><!DOCTYPE html>
<html lang="<?php echo(current_language()); ?>">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/style.css<?php echo($cache_appendix); ?>" type="text/css">
    <title><?php echo_html(text("BrowserCheck")); ?></title>


</head>
<body>

<?php if ($link_expired): ?>

    <div class="box">
        <p class='error'><?php echo_html(text("BrowserCheckExpired")); ?></p>

        <div class='warning'></div>

        <p>
            <a href="<?php echo($data['return_url'] ?? '/'); ?>"><?php echo_html(text("BrowserCheckCaptchaTryAgain")); ?></a>
        </p>
    </div>

<?php else: ?>

    <div id="box" class="box">
        <p><?php echo_html(text("BrowserCheckChecking")); ?></p>
        <noscript><?php echo_html(text("BrowserCheckJS")); ?></noscript>
        <div class="spinner"></div>
        <?php if (!empty($cfg['debug_log'])): ?>
        <br>
        <p style="font-size: 90%; color: maroon"><?php echo_html($_SESSION["bot_check_challenge_reason"]); ?></p>
        <?php endif; ?>
    </div>

    <script>
        const TICKET = <?= json_encode($richTicket) ?>;
        const SEED = <?= json_encode($challengeSeed) ?>;
        const DIFFICULTY = <?= json_encode($difficulty) ?>;
        const VERIFY_URL = <?= json_encode($cfg['verify_url']) ?>;

        // crypto.subtle доступен только в secure context (https или localhost).
        // На обычном http:// (например, локальный *.local домен) его нет,
        // поэтому даём чистую JS-реализацию SHA-256 как fallback.
        async function sha256hex(str) {
            if (window.isSecureContext && window.crypto && window.crypto.subtle) {
                const buf = await crypto.subtle.digest('SHA-256', new TextEncoder().encode(str));
                return Array.from(new Uint8Array(buf)).map(b => b.toString(16).padStart(2, '0')).join('');
            }
            return sha256hexPure(str);
        }

        // Компактная чистая JS-реализация SHA-256 (без зависимостей).
        function sha256hexPure(ascii) {
            function rightRotate(v, n) {
                return (v >>> n) | (v << (32 - n));
            }

            const maxWord = Math.pow(2, 32);
            const words = [];
            let asciiBitLength = ascii.length * 8;

            let hash = sha256hexPure.h || (sha256hexPure.h = []);
            let k = sha256hexPure.k || (sha256hexPure.k = []);
            let primeCounter = k.length;

            const isComposite = {};
            for (let candidate = 2; primeCounter < 64; candidate++) {
                if (!isComposite[candidate]) {
                    for (let i = 0; i < 313; i += candidate) isComposite[i] = candidate;
                    hash[primeCounter] = (Math.pow(candidate, 0.5) * maxWord) | 0;
                    k[primeCounter++] = (Math.pow(candidate, 1 / 3) * maxWord) | 0;
                }
            }

            ascii += '\x80';
            while ((ascii.length % 64) - 56) ascii += '\x00';

            for (let i = 0; i < ascii.length; i++) {
                const j = ascii.charCodeAt(i);
                if (j >> 8) return ''; // не ASCII — в нашем случае данные всегда hex/ascii
                words[i >> 2] |= j << (((3 - i) % 4) * 8);
            }
            words[words.length] = (asciiBitLength / maxWord) | 0;
            words[words.length] = asciiBitLength;

            for (let j = 0; j < words.length;) {
                const w = words.slice(j, (j += 16));
                const oldHash = hash.slice(0);
                hash = hash.slice(0, 8);

                for (let i = 0; i < 64; i++) {
                    const w15 = w[i - 15], w2 = w[i - 2];
                    const a = hash[0], e = hash[4];
                    const temp1 = hash[7]
                        + (rightRotate(e, 6) ^ rightRotate(e, 11) ^ rightRotate(e, 25))
                        + ((e & hash[5]) ^ (~e & hash[6]))
                        + k[i]
                        + (w[i] = (i < 16) ? w[i] : (
                            w[i - 16]
                            + (rightRotate(w15, 7) ^ rightRotate(w15, 18) ^ (w15 >>> 3))
                            + w[i - 7]
                            + (rightRotate(w2, 17) ^ rightRotate(w2, 19) ^ (w2 >>> 10))
                        ) | 0);
                    const temp2 = (rightRotate(a, 2) ^ rightRotate(a, 13) ^ rightRotate(a, 22))
                        + ((a & hash[1]) ^ (a & hash[2]) ^ (hash[1] & hash[2]));

                    hash = [(temp1 + temp2) | 0].concat(hash);
                    hash[4] = (hash[4] + temp1) | 0;
                }

                for (let i = 0; i < 8; i++) {
                    hash[i] = (hash[i] + oldHash[i]) | 0;
                }
            }

            let result = '';
            for (let i = 0; i < 8; i++) {
                for (let j = 3; j >= 0; j--) {
                    const b = (hash[i] >> (j * 8)) & 255;
                    result += ((b < 16) ? '0' : '') + b.toString(16);
                }
            }
            return result;
        }

        async function solveProofOfWork(seed, difficulty) {
            const prefix = '0'.repeat(difficulty);
            let nonce = 0, hash = '';
            const start = performance.now();
            while (true) {
                hash = await sha256hex(seed + ':' + nonce);
                if (hash.startsWith(prefix)) break;
                nonce++;
                if (nonce > 2_000_000) break; // защита от зависания вкладки
            }
            return {nonce, hash, elapsed_ms: Math.round(performance.now() - start)};
        }

        function canvasFingerprint() {
            try {
                const c = document.createElement('canvas');
                const ctx = c.getContext('2d');
                ctx.textBaseline = 'top';
                ctx.font = '14px Arial';
                ctx.fillText('bp-fingerprint-\u2603-0123', 2, 2);
                return c.toDataURL();
            } catch (e) {
                return null;
            }
        }

        (async () => {
            const pow = await solveProofOfWork(SEED, DIFFICULTY);

            let box = document.getElementById('box');
            if (!box) {
                box = document.body;
            }

            const payload = {
                ticket: TICKET,
                pow_nonce: pow.nonce,
                pow_hash: pow.hash,
                elapsed_ms: pow.elapsed_ms,
                webdriver: !!navigator.webdriver,
                canvas: canvasFingerprint(),
                screen: `${screen.width}x${screen.height}x${screen.colorDepth}`,
                lang: navigator.language,
                tz_offset: new Date().getTimezoneOffset(),
                has_plugins: navigator.plugins ? navigator.plugins.length : 0,
            };

            try {
                const res = await fetch(VERIFY_URL, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(payload),
                });
                const json = await res.json();
                if (json.action === 'redirect' || json.action === 'captcha') {
                    window.location.replace(json.url);
                } else {
                    box.innerHTML = "<p class='error'><?php echo_js(text("BrowserCheckError")); ?></p><div class='warning'></div>";
                }
            } catch (e) {
                box.innerHTML = "<p class='error'><?php echo_js(text("BrowserCheckError")); ?></p><div class='warning'></div>";
            }
        })();
    </script>
<?php endif; ?>

</body>
</html>
