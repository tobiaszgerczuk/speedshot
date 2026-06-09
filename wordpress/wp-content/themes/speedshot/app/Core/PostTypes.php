<?php

namespace Speedshot\Theme\Core;

class PostTypes
{
    public static function boot(): void
    {
        add_action('init', [self::class, 'register']);
    }

    public static function register(): void
    {
        register_post_type('inwestycja', [
            'labels' => [
                'name' => 'Inwestycje',
                'singular_name' => 'Inwestycja',
            ],
            'public' => true,
            'menu_icon' => 'dashicons-location-alt',
            'supports' => ['title', 'editor', 'thumbnail'],
            'show_in_rest' => true,
        ]);

        register_post_type('event', [
            'labels' => [
                'name' => 'Wydarzenia',
                'singular_name' => 'Wydarzenie',
                'add_new_item' => 'Dodaj wydarzenie',
                'edit_item' => 'Edytuj wydarzenie',
                'new_item' => 'Nowe wydarzenie',
                'view_item' => 'Zobacz wydarzenie',
                'search_items' => 'Szukaj wydarzeń',
                'not_found' => 'Nie znaleziono wydarzeń',
                'menu_name' => 'Wydarzenia',
            ],
            'public' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => 'wydarzenia'],
            'menu_icon' => 'dashicons-calendar-alt',
            'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
            'show_in_rest' => true,
        ]);
    }
}
