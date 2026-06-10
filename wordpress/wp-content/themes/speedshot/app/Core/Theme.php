<?php

namespace Speedshot\Theme\Core;

use Timber\Timber;
use Speedshot\Theme\Core\Acf;
use Speedshot\Theme\Core\Assets;
use Speedshot\Theme\Core\ContactForm;
use Speedshot\Theme\Core\EventRegistration;
use Speedshot\Theme\Core\Media;
use Speedshot\Theme\Core\PostTypes;
use Speedshot\Theme\Core\Templating;
use Speedshot\Theme\Blocks\Registry as BlocksRegistry;
use Speedshot\Theme\Rest\Api as RestApi;

class Theme
{
    public static function init(): void
    {
        // Timber config
        Timber::$dirname = ['views'];
        Timber::$autoescape = false;

        add_action('after_setup_theme', [self::class, 'supports']);

        // === REJESTRACJA I ENQUEUE ASSETÓW ===
        add_action('init', [Assets::class, 'register']);
        add_action('wp_enqueue_scripts', [Assets::class, 'enqueue_front'], 20);
        add_action('admin_enqueue_scripts', [Assets::class, 'enqueue_admin'], 20);

        // Timber/Twig
        add_filter('timber/context', [Templating::class, 'add_to_context']);
        add_filter('timber/twig', [Templating::class, 'extend_twig']);

        // Media helpers / upload support
        Media::boot();

        // Contact form
        ContactForm::boot();

        // Event registrations
        EventRegistration::boot();

        // Custom post types
        PostTypes::boot();

        // ACF Local JSON
        Acf::boot();
        add_filter('acf/settings/save_json', [Acf::class, 'save_json']);
        add_filter('acf/settings/load_json', [Acf::class, 'load_json']);

        // ACF Blocks
        BlocksRegistry::boot();
        add_action('acf/init', [BlocksRegistry::class, 'register_blocks']);

        // REST API
        RestApi::boot();

    }

    

    // Speedshot\Theme\Core\Theme.php
    public static function supports(): void
    {
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
        add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
        add_theme_support('wp-block-styles');
        add_theme_support('align-wide');
        add_theme_support('responsive-embeds');

        // === REJESTRACJA MENU ===
        register_nav_menus([
            'main-menu' => __('Main Menu', 'speedshot'),     // Header
            'footer-menu' => __('Footer Menu', 'speedshot'),   // Stopka
        ]);

        // WooCommerce support
        add_theme_support('woocommerce');
        add_theme_support('wc-product-gallery-zoom');
        add_theme_support('wc-product-gallery-lightbox');
        add_theme_support('wc-product-gallery-slider');

    }


    
}
