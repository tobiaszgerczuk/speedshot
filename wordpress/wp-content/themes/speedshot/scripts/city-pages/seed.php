<?php
/**
 * Tworzy / aktualizuje podstrony „Imprezy firmowe [Miasto]” (SPEEDSHOT-3) – wersja CLI.
 * Na produkcji bez SSH użyj speedshot-city-pages.php (instrukcja w pliku).
 *
 * Lokalnie:
 *   docker compose exec wordpress php /var/www/html/wp-content/themes/speedshot/scripts/city-pages/seed.php [--dry-run]
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}

require dirname(__DIR__, 5) . '/wp-load.php';
require __DIR__ . '/lib.php';

try {
    $log = speedshot_seed_city_pages(require __DIR__ . '/cities.php', in_array('--dry-run', $argv, true));
} catch (RuntimeException $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}

echo implode("\n", $log) . "\n";
