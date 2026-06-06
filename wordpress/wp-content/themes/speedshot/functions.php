<?php
require_once __DIR__ . '/vendor/autoload.php';

if (!class_exists('Speedshot\Theme\Core\Requirements')) {
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p><strong>Class Speedshot\Theme\Core\Requirements not found.</strong><br>Run: <code>composer dump-autoload -o</code></p></div>';
    });
    return;
}

Speedshot\Theme\Core\Requirements::boot();
if (Speedshot\Theme\Core\Requirements::has_blocking_errors()) {
    return;
}

if (!class_exists('Speedshot\Theme\Core\Theme')) {
    error_log('Speedshot\Theme\Core\Theme not found! Check PSR-4 path: app/Core/Theme.php');
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p><strong>Class Speedshot\Theme\Core\Theme not found!</strong><br>
        Check: <code>app/Core/Theme.php</code> exists and namespace is correct.<br>
        Run: <code>composer dump-autoload -o</code></p></div>';
    });
    return;
}

Speedshot\Theme\Core\Theme::init();
