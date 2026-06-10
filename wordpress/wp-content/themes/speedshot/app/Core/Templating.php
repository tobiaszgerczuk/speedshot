<?php

namespace Speedshot\Theme\Core;

use Timber\Timber;
use Twig\TwigFunction;

class Templating
{
    private static ?array $manifest = null;

    public static function add_to_context($context)
    {
        // 1. URL do szablonu
        $context['theme_url'] = get_template_directory_uri();

        // 2. URL do assetów (public)
        $context['assets_url'] = get_template_directory_uri() . '/public';

        $manifest = self::manifest();
        $context['mix'] = function ($path) use ($manifest) {
            $path = ltrim($path, '/');
            return get_template_directory_uri() . '/public' . ($manifest[$path] ?? $path);
        };

        $context['woo_active'] = class_exists('WooCommerce');
        $context['cart'] = null;
        $context['shop_url'] = null;
        $context['cart_url'] = null;

        if ($context['woo_active']) {
            $context['cart'] = function_exists('WC') ? WC()->cart : null;
            $context['shop_url'] = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : null;
            $context['cart_url'] = function_exists('wc_get_cart_url') ? wc_get_cart_url() : null;
        }

        $context['breadcrumbs'] = self::build_breadcrumbs();

        $globalSettings = Acf::get_global_settings();
        $context['global_settings'] = $globalSettings;
        $context['acf'] = array_merge($context['acf'] ?? [], [
            'logo' => $globalSettings['logo'] ?? null,
        ]);

        return $context;
    }

    public static function extend_twig($twig)
    {
        // Rejestruj funkcję Twig: {{ template_url('images/logo.png') }}
        $twig->addFunction(new TwigFunction('template_url', function ($path = '') {
            return get_template_directory_uri() . '/' . ltrim($path, '/');
        }));

        // Rejestruj mix() – na bazie zcache'owanego manifestu
        $manifest = self::manifest();
        $twig->addFunction(new TwigFunction('mix', function ($path) use ($manifest) {
            $path = ltrim($path, '/');
            $asset_path = $manifest[$path] ?? $path;
            return get_template_directory_uri() . '/public' . $asset_path;
        }));

        $twig->addFunction(new \Twig\TwigFunction('do_shortcode', function ($content) {
            return do_shortcode($content);
        }, ['is_safe' => ['html']]));

        $twig->addFunction(new TwigFunction('wco_image', [Media::class, 'image'], ['is_safe' => ['html']]));
        

        return $twig;
    }

    private static function build_breadcrumbs(): array
    {
        $crumbs = [[
            'label' => 'Strona główna',
            'url'   => home_url('/'),
        ]];

        if (is_singular()) {
            $post = get_post();

            if ($post && $post->post_parent) {
                $ancestors = array_reverse(get_post_ancestors($post->ID));
                foreach ($ancestors as $ancestor_id) {
                    $crumbs[] = [
                        'label' => get_the_title($ancestor_id),
                        'url'   => get_permalink($ancestor_id),
                    ];
                }
            } elseif ($post && get_post_type($post) !== 'page') {
                $post_type = get_post_type_object(get_post_type($post));
                $archive_link = get_post_type_archive_link(get_post_type($post));
                if ($post_type && $archive_link) {
                    $crumbs[] = [
                        'label' => $post_type->labels->name ?? $post_type->label,
                        'url'   => $archive_link,
                    ];
                }
            }

            $crumbs[] = [
                'label' => get_the_title(),
                'url'   => '',
            ];
        } elseif (is_archive()) {
            $post_type = get_post_type_object(get_queried_object()->name ?? '');
            $crumbs[] = [
                'label' => $post_type ? ($post_type->labels->name ?? $post_type->label) : get_the_archive_title(),
                'url'   => '',
            ];
        } elseif (is_search()) {
            $crumbs[] = [
                'label' => 'Wyniki wyszukiwania: ' . get_search_query(),
                'url'   => '',
            ];
        } elseif (is_404()) {
            $crumbs[] = [
                'label' => 'Strona nie znaleziona',
                'url'   => '',
            ];
        }

        return $crumbs;
    }

    private static function manifest(): array
    {
        if (self::$manifest !== null) {
            return self::$manifest;
        }

        self::$manifest = Cache::remember('assets_manifest', HOUR_IN_SECONDS, static function (): array {
            $manifestPath = get_template_directory() . '/public/mix-manifest.json';
            if (!file_exists($manifestPath)) {
                return [];
            }

            $manifest = json_decode((string) file_get_contents($manifestPath), true);
            return is_array($manifest) ? $manifest : [];
        });

        return self::$manifest;
    }
}
