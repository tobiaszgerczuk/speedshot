<?php
/**
 * Jednorazowe wdrożenie podstron „Imprezy firmowe [Miasto]” (SPEEDSHOT-3) na produkcji.
 *
 * 1. Wgraj zaktualizowany motyw (views/partials/footer.twig, public/style.css, scripts/city-pages/*).
 * 2. Wgraj TEN plik przez FTP do katalogu głównego WordPressa (obok wp-config.php).
 * 3. Otwórz: https://centrumrozrywki-speedshot.pl/speedshot-city-pages.php?token=<TOKEN>
 *    – zobaczysz podgląd i wynik walidacji, nic jeszcze się nie zapisuje.
 * 4. Kliknij „Uruchom”. Skrypt spróbuje usunąć się sam – jeśli się nie uda, USUŃ go przez FTP.
 *
 * Skrypt jest idempotentny – ponowne uruchomienie aktualizuje strony zamiast je dublować.
 */

const SPEEDSHOT_TOKEN = 'bd658c8e213034c11c7d3e9e5f860503';
const SPEEDSHOT_EXPIRES = '2026-10-13';

header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');

$token = (string) ($_POST['token'] ?? $_GET['token'] ?? '');
if (!hash_equals(SPEEDSHOT_TOKEN, $token)) {
    http_response_code(404);
    exit;
}

if (date('Y-m-d') > SPEEDSHOT_EXPIRES) {
    http_response_code(410);
    die('Skrypt wygasł (' . SPEEDSHOT_EXPIRES . '). Usuń go z serwera.');
}

if (!file_exists(__DIR__ . '/wp-load.php')) {
    http_response_code(500);
    die('Wgraj ten plik do katalogu głównego WordPressa (obok wp-config.php).');
}

require __DIR__ . '/wp-load.php';

$lib = get_template_directory() . '/scripts/city-pages/lib.php';
$data = get_template_directory() . '/scripts/city-pages/cities.php';
if (!file_exists($lib) || !file_exists($data)) {
    http_response_code(500);
    die('Brak plików motywu scripts/city-pages/ – najpierw wgraj zaktualizowany motyw.');
}

require $lib;
$cities = require $data;

$run = $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run']);
$error = null;
$log = [];
$deleted = false;

try {
    $log = speedshot_seed_city_pages($cities, !$run);
    if ($run) {
        $deleted = @unlink(__FILE__);
    }
} catch (RuntimeException $e) {
    $error = $e->getMessage();
}

$footer_ok = strpos((string) file_get_contents(get_template_directory() . '/views/partials/footer.twig'), 'site-footer__cities') !== false;
$e = static fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex, nofollow">
    <title>SPEEDSHOT-3 – podstrony miast</title>
    <style>
        body { font: 15px/1.5 system-ui, sans-serif; max-width: 760px; margin: 40px auto; padding: 0 16px; color: #1a1a1a; }
        pre { background: #f4f4f4; padding: 12px 14px; border-radius: 6px; white-space: pre-wrap; }
        .ok { color: #11703a; } .err { color: #b3261e; } .warn { color: #8a5a00; }
        button { font: inherit; padding: 10px 18px; border: 0; border-radius: 6px; background: #1b3a6b; color: #fff; cursor: pointer; }
    </style>
</head>
<body>
<h1>SPEEDSHOT-3 – podstrony „Imprezy firmowe [Miasto]”</h1>

<?php if (!$footer_ok): ?>
    <p class="warn">Uwaga: footer.twig na serwerze nie zawiera linków do miast – wgraj zaktualizowany motyw.</p>
<?php endif; ?>

<?php if ($error): ?>
    <p class="err"><strong>Błąd – nic nie zostało zapisane<?= $run ? ' (lub zapis przerwał się w trakcie)' : '' ?>:</strong></p>
    <pre><?= $e($error) ?></pre>
<?php elseif (!$run): ?>
    <p class="ok">Walidacja OK. Po uruchomieniu zostaną wykonane operacje:</p>
    <pre><?= $e(implode("\n", $log)) ?></pre>
    <form method="post">
        <input type="hidden" name="token" value="<?= $e($token) ?>">
        <button type="submit" name="run" value="1">Uruchom</button>
    </form>
<?php else: ?>
    <p class="ok"><strong>Gotowe.</strong></p>
    <pre><?= $e(implode("\n", $log)) ?></pre>
    <?php if ($deleted): ?>
        <p class="ok">Skrypt usunął się z serwera.</p>
    <?php else: ?>
        <p class="err"><strong>Nie udało się usunąć skryptu – usuń speedshot-city-pages.php przez FTP.</strong></p>
    <?php endif; ?>
    <p>Jeśli na serwerze działa cache strony, wyczyść go.</p>
<?php endif; ?>
</body>
</html>
