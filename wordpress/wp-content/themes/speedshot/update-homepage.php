<?php
/**
 * Jednorazowy skrypt aktualizacji strony głównej.
 * 1. Wrzuć przez FTP do katalogu głównego WordPressa (obok wp-config.php)
 * 2. Odwiedź: https://twoja-domena.pl/update-homepage.php?token=speedshot2025
 * 3. USUŃ plik przez FTP natychmiast po uruchomieniu!
 */

define('SECRET_TOKEN', 'speedshot2025');

if (empty($_GET['token']) || $_GET['token'] !== SECRET_TOKEN) {
    http_response_code(403);
    die('403 Forbidden – brak lub nieprawidłowy token.');
}

// Bootstrap WordPress
require_once __DIR__ . '/wp-load.php';

if (!function_exists('wp_update_post')) {
    die('Błąd: WordPress nie załadował się poprawnie.');
}

// Pobierz ID strony głównej
$page_id = (int) get_option('page_on_front');
if (!$page_id) {
    die('Błąd: Strona główna nie jest ustawiona jako statyczna strona (Ustawienia → Czytanie).');
}

// Wczytaj content z pliku theme
$content_file = get_template_directory() . '/homepage-content.txt';
if (!file_exists($content_file)) {
    die('Błąd: Nie znaleziono pliku homepage-content.txt w katalogu theme.');
}

$content = file_get_contents($content_file);
if (!$content) {
    die('Błąd: Nie można odczytać pliku homepage-content.txt.');
}

// Podmień lokalne URLe na produkcyjne
$local_url = 'https://speedshot.localhost';
$prod_url  = rtrim(home_url(), '/');
$content   = str_replace(
    [addslashes($local_url), $local_url],
    [addslashes($prod_url),  $prod_url],
    $content
);

// Zaktualizuj post
$result = wp_update_post([
    'ID'           => $page_id,
    'post_content' => $content,
]);

if (is_wp_error($result) || !$result) {
    die('Błąd: Aktualizacja nie powiodła się. ' . (is_wp_error($result) ? $result->get_error_message() : ''));
}

echo '<style>body{font-family:sans-serif;padding:2rem;max-width:600px;margin:0 auto}</style>';
echo '<h2>✅ Strona główna zaktualizowana</h2>';
echo '<p>Post ID: <strong>' . $page_id . '</strong></p>';
echo '<p>Domeny podmienione: <code>' . esc_html($local_url) . '</code> → <code>' . esc_html($prod_url) . '</code></p>';
echo '<p style="color:red"><strong>⚠️ USUŃ ten plik przez FTP natychmiast!</strong><br>Ścieżka na serwerze: <code>' . __FILE__ . '</code></p>';
echo '<p><a href="' . home_url() . '">Przejdź na stronę główną →</a></p>';
