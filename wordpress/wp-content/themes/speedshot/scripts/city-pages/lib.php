<?php
/**
 * Logika tworzenia podstron „Imprezy firmowe [Miasto]” (SPEEDSHOT-3).
 * Używana przez seed.php (CLI) i jednorazowy skrypt produkcyjny.
 */

defined('ABSPATH') || exit;

const SPEEDSHOT_COMPANY_PAGE_SLUG = 'imprezy-integracyjne-dla-firm';
const SPEEDSHOT_CITY_LINKS_MARKER = 'speedshot-city-links';

function speedshot_page_url(string $slug): string
{
    $page = get_page_by_path($slug);
    if (!$page || $page->post_status !== 'publish') {
        throw new RuntimeException("Brak opublikowanej strony o slugu: {$slug}");
    }

    return get_permalink($page);
}

function speedshot_expand_links(string $html): string
{
    return preg_replace_callback('/\[\[([a-z0-9-]+)\|([^\]]+)\]\]/u', static function ($m) {
        return sprintf('<a href="%s">%s</a>', esc_url(speedshot_page_url($m[1])), $m[2]);
    }, $html);
}

function speedshot_block_json(array $attrs): string
{
    return wp_json_encode($attrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function speedshot_core_blocks(array $items): string
{
    $out = [];

    foreach ($items as [$type, $value]) {
        if ($type === 'h2') {
            $out[] = "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">{$value}</h2>\n<!-- /wp:heading -->";
        } elseif ($type === 'p') {
            $out[] = "<!-- wp:paragraph -->\n<p>" . speedshot_expand_links($value) . "</p>\n<!-- /wp:paragraph -->";
        } elseif ($type === 'ul') {
            $lis = array_map(static function ($li) {
                return "<!-- wp:list-item -->\n<li>" . speedshot_expand_links($li) . "</li>\n<!-- /wp:list-item -->";
            }, $value);
            $out[] = "<!-- wp:list -->\n<ul class=\"wp-block-list\">" . implode("\n\n", $lis) . "</ul>\n<!-- /wp:list -->";
        }
    }

    return implode("\n\n", $out);
}

function speedshot_other_cities_blocks(array $city, array $cities): string
{
    $links = [];
    foreach ($cities as $other) {
        if ($other['slug'] === $city['slug']) {
            continue;
        }
        $links[] = "[[{$other['slug']}|Imprezy firmowe {$other['city']}]]";
    }

    return speedshot_core_blocks([
        ['h2', 'Imprezy firmowe w innych miastach'],
        ['p', 'Organizujemy imprezy firmowe w całej Polsce: ' . implode(', ', $links) . '. Więcej o naszej ofercie dla firm przeczytasz na stronie [[' . SPEEDSHOT_COMPANY_PAGE_SLUG . '|imprezy integracyjne dla firm]].'],
    ]);
}

function speedshot_gallery_block(array $city): string
{
    return '<!-- wp:acf/photo-gallery ' . speedshot_block_json([
        'id'   => 'block_' . $city['slug'] . '_gallery',
        'name' => 'acf/photo-gallery',
        'data' => [
            'photo_gallery_images'   => $city['gallery'],
            '_photo_gallery_images'  => 'field_photo_gallery_images',
            'photo_gallery_columns'  => '2',
            '_photo_gallery_columns' => 'field_photo_gallery_columns',
        ],
        'mode' => 'preview',
    ]) . ' /-->';
}

function speedshot_faq_block(array $city): string
{
    $data = [
        'faq_accordion_eyebrow'  => 'FAQ',
        '_faq_accordion_eyebrow' => 'field_faq_accordion_eyebrow',
        'faq_accordion_title'    => 'Imprezy firmowe ' . $city['locative'] . ' – najczęstsze pytania',
        '_faq_accordion_title'   => 'field_faq_accordion_title',
        'faq_accordion_items'    => count($city['faq']),
        '_faq_accordion_items'   => 'field_faq_accordion_items',
    ];

    foreach ($city['faq'] as $i => [$question, $answer]) {
        $data["faq_accordion_items_{$i}_question"]  = $question;
        $data["_faq_accordion_items_{$i}_question"] = 'field_faq_accordion_items_question';
        $data["faq_accordion_items_{$i}_answer"]    = '<p>' . $answer . '</p>';
        $data["_faq_accordion_items_{$i}_answer"]   = 'field_faq_accordion_items_answer';
    }

    return '<!-- wp:acf/faq-accordion ' . speedshot_block_json([
        'name' => 'acf/faq-accordion',
        'data' => $data,
        'mode' => 'preview',
    ]) . ' /-->';
}

function speedshot_contact_block(array $city): string
{
    return '<!-- wp:acf/contact-section ' . speedshot_block_json([
        'name' => 'acf/contact-section',
        'data' => [
            'contact_section_eyebrow'   => 'Zapytaj o wycenę',
            '_contact_section_eyebrow'  => 'field_contact_section_eyebrow',
            'contact_section_title'     => 'Zaplanujmy Twoją imprezę firmową ' . $city['locative'],
            '_contact_section_title'    => 'field_contact_section_title',
            'contact_section_content'   => 'Opisz wydarzenie – termin, miejsce ' . $city['locative'] . ' lub w okolicy i przybliżoną liczbę uczestników. Przygotujemy propozycję atrakcji dopasowaną do Twojego zespołu.',
            '_contact_section_content'  => 'field_contact_section_content',
            'block_id'                  => 'kontakt',
            '_block_id'                 => 'field_contact_section_block_id',
            'section_space_top'         => 'xl',
            '_section_space_top'        => 'field_contact_section_section_space_top',
            'section_space_bottom'      => 'xl',
            '_section_space_bottom'     => 'field_contact_section_section_space_bottom',
        ],
        'mode' => 'preview',
    ]) . ' /-->';
}

function speedshot_city_content(array $city, array $cities): string
{
    $inner = speedshot_core_blocks($city['content']) . "\n\n" . speedshot_other_cities_blocks($city, $cities);

    return "<!-- wp:group {\"layout\":{\"type\":\"constrained\",\"contentSize\":\"860px\"}} -->\n<div class=\"wp-block-group\">\n\n"
        . $inner
        . "\n\n</div>\n<!-- /wp:group -->\n\n"
        . speedshot_gallery_block($city) . "\n\n"
        . speedshot_faq_block($city) . "\n\n"
        . speedshot_contact_block($city) . "\n";
}

function speedshot_company_page_content(string $content, array $cities): string
{
    $city_links = array_map(static function ($city) {
        return "[[{$city['slug']}|Imprezy firmowe {$city['city']}]]";
    }, $cities);

    $section = '<!-- wp:group {"className":"' . SPEEDSHOT_CITY_LINKS_MARKER . '","layout":{"type":"constrained"}} -->' . "\n"
        . '<div class="wp-block-group ' . SPEEDSHOT_CITY_LINKS_MARKER . '">' . "\n\n"
        . speedshot_core_blocks([
            ['h2', 'Imprezy firmowe w Twoim mieście'],
            ['p', 'Przyjeżdżamy z atrakcjami do firm w całej Polsce. Sprawdź, jak organizujemy integracje w największych miastach:'],
            ['ul', $city_links],
        ])
        . "\n\n</div>\n<!-- /wp:group -->";

    // Sekcja nie zawiera zagnieżdżonych grup, więc pierwszy zamykający </div> po znaczniku jest jej własny.
    $pattern = '/<!-- wp:group \{"className":"' . SPEEDSHOT_CITY_LINKS_MARKER . '".*?<\/div>\n<!-- \/wp:group -->/s';

    if (preg_match($pattern, $content)) {
        return preg_replace_callback($pattern, static fn() => $section, $content);
    }

    // Wstawiamy na końcu pierwszej grupy z treścią (przed galerią).
    $group_end = "</div>\n<!-- /wp:group -->";
    $pos = strpos($content, $group_end);
    if ($pos === false) {
        throw new RuntimeException('Nie znaleziono końca grupy z treścią na stronie „Imprezy integracyjne”.');
    }

    return substr_replace($content, $section . "\n\n", $pos, 0);
}

/**
 * Sprawdza wszystko, czego potrzebują treści, zanim cokolwiek zapiszemy.
 * Zwraca listę problemów (pusta = można uruchamiać).
 */
function speedshot_validate_city_pages(array $cities): array
{
    $errors = [];

    $city_slugs = array_column($cities, 'slug');
    $slugs = [SPEEDSHOT_COMPANY_PAGE_SLUG];
    foreach ($cities as $city) {
        $texts = array_merge(...array_map(static fn($item) => (array) $item[1], $city['content']));
        preg_match_all('/\[\[([a-z0-9-]+)\|/', implode(' ', $texts), $m);
        $slugs = array_merge($slugs, $m[1]);

        foreach (array_merge([$city['image']], $city['gallery']) as $attachment_id) {
            if (get_post_type($attachment_id) !== 'attachment') {
                $errors[] = "{$city['city']}: brak zdjęcia o ID {$attachment_id}";
            }
        }
    }

    foreach (array_unique($slugs) as $slug) {
        // Strony miast mogą jeszcze nie istnieć – powstaną w pierwszym kroku.
        if (in_array($slug, $city_slugs, true)) {
            continue;
        }
        $page = get_page_by_path($slug);
        if (!$page || $page->post_status !== 'publish') {
            $errors[] = "Brak opublikowanej strony: /{$slug}/";
        }
    }

    foreach ($city_slugs as $slug) {
        $page = get_page_by_path($slug);
        if ($page && $page->post_status !== 'publish') {
            $errors[] = "Strona /{$slug}/ istnieje, ale ma status „{$page->post_status}”";
        }
    }

    if (!defined('WPSEO_VERSION')) {
        $errors[] = 'Wtyczka Yoast SEO nie jest aktywna – meta title/description nie zadziałają.';
    }

    return $errors;
}

/**
 * Tworzy / aktualizuje podstrony miast i sekcję z linkami na stronie „Imprezy integracyjne”.
 * Idempotentne – strony szuka po slugu. Zwraca log.
 */
function speedshot_seed_city_pages(array $cities, bool $dry_run = false): array
{
    $log = [];

    $errors = speedshot_validate_city_pages($cities);
    if ($errors) {
        throw new RuntimeException("Walidacja nie przeszła:\n- " . implode("\n- ", $errors));
    }

    foreach ($cities as $city) {
        $page = get_page_by_path($city['slug']);
        $log[] = ($page ? 'AKTUALIZACJA' : 'NOWA') . "  /{$city['slug']}/";
    }
    $log[] = 'AKTUALIZACJA  sekcja z miastami na /' . SPEEDSHOT_COMPANY_PAGE_SLUG . '/';

    if ($dry_run) {
        return $log;
    }

    kses_remove_filters();
    $log = [];

    // Najpierw tworzymy brakujące strony, żeby linki między miastami się rozwiązały.
    foreach ($cities as $city) {
        if (get_page_by_path($city['slug'])) {
            continue;
        }
        $id = wp_insert_post([
            'post_type'   => 'page',
            'post_status' => 'publish',
            'post_title'  => 'Imprezy firmowe ' . $city['city'],
            'post_name'   => $city['slug'],
        ], true);
        if (is_wp_error($id)) {
            throw new RuntimeException($id->get_error_message());
        }
    }

    foreach ($cities as $city) {
        $page = get_page_by_path($city['slug']);

        $id = wp_update_post(wp_slash([
            'ID'           => $page->ID,
            'post_title'   => 'Imprezy firmowe ' . $city['city'],
            'post_name'    => $city['slug'],
            'post_excerpt' => $city['excerpt'],
            'post_content' => speedshot_city_content($city, $cities),
            'meta_input'   => [
                '_yoast_wpseo_title'    => $city['seo_title'],
                '_yoast_wpseo_metadesc' => $city['metadesc'],
                '_yoast_wpseo_focuskw'  => $city['focuskw'],
            ],
        ]), true);

        if (is_wp_error($id)) {
            throw new RuntimeException($id->get_error_message());
        }

        set_post_thumbnail($id, $city['image']);

        $log[] = "OK  #{$id}  " . get_permalink($id);
    }

    $company = get_page_by_path(SPEEDSHOT_COMPANY_PAGE_SLUG);

    // Kopia oryginalnej treści – zapisywana tylko raz, przy pierwszym uruchomieniu.
    if (!get_post_meta($company->ID, '_speedshot_content_before_city_links', true)) {
        update_post_meta($company->ID, '_speedshot_content_before_city_links', wp_slash($company->post_content));
    }

    $result = wp_update_post(wp_slash([
        'ID'           => $company->ID,
        'post_content' => speedshot_company_page_content($company->post_content, $cities),
    ]), true);

    if (is_wp_error($result)) {
        throw new RuntimeException($result->get_error_message());
    }

    $log[] = "OK  #{$company->ID}  sekcja z miastami na " . get_permalink($company);

    return $log;
}
