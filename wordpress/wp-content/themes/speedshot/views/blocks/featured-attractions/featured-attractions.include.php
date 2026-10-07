<?php

use Timber\Timber;
use Speedshot\Theme\Blocks\SectionSettings;

$context = Timber::context();
$fields  = get_fields() ?: [];

$context['fields']          = $fields;
$context['block']           = $block ?? [];
$context['is_preview']      = $is_preview ?? false;
$context['section_classes'] = SectionSettings::build_classes(
    $fields,
    ['block-featured-attractions', !empty($block['align']) ? 'align' . $block['align'] : '']
);
$context['section_id']    = SectionSettings::section_id($fields);
$context['section_style'] = SectionSettings::inline_style($fields);

// Domyślne atrakcje (fallback gdy ACF puste)
$defaults = [
    [
        'number'      => '01',
        'title'       => 'Laserowy Paintball',
        'description' => 'Dynamiczna rywalizacja drużynowa z laserowymi karabinkami. Bez bólu, bez plam — czysta taktyka i emocje dla każdego wieku. Idealna atrakcja na festyny, pikniki i eventy firmowe.',
        'tags'        => ['Dla dorosłych', 'Dla dzieci', 'Plener'],
        'badge'       => '',
        'url'         => '/laser-tag/',
        'icon'        => 'crosshair',
        'image'       => '',
    ],
    [
        'number'      => '02',
        'title'       => 'Wyścigi Autek Zdalnie Sterowanych',
        'description' => 'Modele RC klasy 1:10 z napędem 4×4 i tor pełen adrenaliny. Szybka, efektowna jazda i rywalizacja, która porywa dzieci i dorosłych.',
        'tags'        => ['Dla dorosłych', 'Dla dzieci', 'Plener'],
        'badge'       => '',
        'url'         => '/wyscigi-aut-zdalnie-sterowanych/',
        'icon'        => 'car',
        'image'       => '',
    ],
    [
        'number'      => '03',
        'title'       => 'Bitwy Wodne',
        'description' => 'Pistolety na wodę wspomagane baterią, dmuchane przeszkody i zbiorniki z wodą. Świetna, orzeźwiająca atrakcja na letnie dni.',
        'tags'        => ['Dla dzieci', 'Plener', 'Lato'],
        'badge'       => '',
        'url'         => '/bitwy-na-pistolety-wodne/',
        'icon'        => 'waves',
        'image'       => '',
    ],
    [
        'number'      => '04',
        'title'       => 'Mini Festiwal Dmuchańców',
        'description' => 'Zabawa na 5 dmuchanych zamkach dostosowanych do każdego wieku. Kolorowa atrakcja, która od razu przyciąga dzieci.',
        'tags'        => ['Dla dzieci', 'Plener', 'Rodzinne'],
        'badge'       => '',
        'url'         => '/dmuchance-na-wynajem/',
        'icon'        => 'castle',
        'image'       => '',
    ],
    [
        'number'      => '05',
        'title'       => 'Kule Wodne',
        'description' => 'Chodzenie po wodzie w wielkich, bezpiecznych kulach na specjalnym basenie. Mnóstwo frajdy dla dzieci, młodzieży i dorosłych.',
        'tags'        => ['Dla dzieci', 'Plener', 'Lato'],
        'badge'       => '',
        'url'         => '/kule-wodne-waterball/',
        'icon'        => 'waves',
        'image'       => '',
    ],
    [
        'number'      => '06',
        'title'       => 'Bitwy Nerf',
        'description' => 'Bezpieczne starcia na piankowe strzałki, dmuchane przeszkody i scenariusze pełne emocji. Dużo ruchu i rywalizacji dla dzieci i młodzieży.',
        'tags'        => ['Dla dzieci', 'Plener', 'Drużynowe'],
        'badge'       => '',
        'url'         => '/bitwy-nerf/',
        'icon'        => 'crosshair',
        'image'       => '',
    ],
    [
        'number'      => '07',
        'title'       => 'Archer Tag',
        'description' => 'Dynamiczna gra zespołowa z łukami i miękkimi, piankowymi strzałami. Bezbolesna, bezpieczna i emocjonująca — łączy łucznictwo z „dwoma ogniami”.',
        'tags'        => ['Dla dorosłych', 'Dla dzieci', 'Plener'],
        'badge'       => '',
        'url'         => '/archery-tag/',
        'icon'        => 'target',
        'image'       => '',
    ],
    [
        'number'      => '08',
        'title'       => 'Mobilny Tor Wyścigowy Carrera',
        'description' => 'Widowiskowy tor z komputerowym pomiarem czasu i modelami w skali 1:32. Rywalizacja na czas i kibice przy bandach — hit każdego eventu.',
        'tags'        => ['Dla dorosłych', 'Dla dzieci', 'Plener'],
        'badge'       => 'Nowość',
        'url'         => '/mobilny-tor-wyscigowy-carrera/',
        'icon'        => 'car',
        'image'       => '',
    ],
    [
        'number'      => '09',
        'title'       => 'Mini Golf',
        'description' => 'Przenośny tor minigolfa — spokojna, rodzinna zabawa dostępna dosłownie wszędzie. Świetna jako atrakcja towarzysząca większym eventom lub samodzielny punkt programu.',
        'tags'        => ['Dla rodzin', 'Plener', 'Łatwy transport'],
        'badge'       => '',
        'url'         => '/mobilny-minigolf/',
        'icon'        => 'flag',
        'image'       => '',
    ],
];

// Jeśli ACF ma dane w repeaterze — użyj ich
$acf_items = !empty($fields['featured_attractions_items']) && is_array($fields['featured_attractions_items'])
    ? $fields['featured_attractions_items']
    : [];

if (!empty($acf_items)) {
    $attractions = [];
    foreach ($acf_items as $i => $row) {
        $tags_raw = trim($row['tags_raw'] ?? '');
        $tags     = $tags_raw
            ? array_map('trim', explode(',', $tags_raw))
            : ($defaults[$i]['tags'] ?? []);

        $attractions[] = [
            'number'      => str_pad($i + 1, 2, '0', STR_PAD_LEFT),
            'title'       => $row['title']       ?? $defaults[$i]['title']       ?? '',
            'description' => $row['description'] ?? $defaults[$i]['description'] ?? '',
            'badge'       => $row['badge']        ?? '',
            'url'         => $row['url']          ?? $defaults[$i]['url']         ?? '#',
            'icon'        => $defaults[$i]['icon'] ?? 'sparkles',
            'image'       => $row['image']        ?? '',
            'image_position' => in_array($row['image_position'] ?? '', ['top', 'center', 'bottom'], true)
                ? $row['image_position']
                : 'center',
            'tags'        => $tags,
        ];
    }
    $context['attractions'] = $attractions;
} else {
    $context['attractions'] = $defaults;
}

/**
 * Zdjęcie karty: jeśli pole 'image' jest puste, pobierz zdjęcie ze strony danej
 * atrakcji (miniatura -> pierwsze zdjęcie z galerii). Gdy brak — zostaje ikona.
 * Zwraca ['image' => url, 'position' => top|center|bottom] — pozycja kadrowania
 * pochodzi z pola ACF strony atrakcji ('featured_thumb_position').
 */
$resolve_attraction_image = static function (string $url): array {
    $path = (string) wp_parse_url($url, PHP_URL_PATH);
    $slug = trim($path, '/');
    if ($slug === '') {
        return ['image' => '', 'position' => 'center'];
    }
    $page = get_page_by_path($slug);
    if (!$page) {
        return ['image' => '', 'position' => 'center'];
    }

    $position = get_field('featured_thumb_position', $page->ID);
    $position = in_array($position, ['top', 'center', 'bottom'], true) ? $position : 'center';

    $thumb_id = get_post_thumbnail_id($page->ID);
    if ($thumb_id) {
        $src = wp_get_attachment_image_url($thumb_id, 'large');
        if ($src) {
            return ['image' => $src, 'position' => $position];
        }
    }

    if (preg_match('/"photo_gallery_images":\[\s*"?(\d+)"?/', $page->post_content, $m)) {
        $src = wp_get_attachment_image_url((int) $m[1], 'large');
        if ($src) {
            return ['image' => $src, 'position' => $position];
        }
    }

    return ['image' => '', 'position' => $position];
};

foreach ($context['attractions'] as &$attraction) {
    if (empty($attraction['image']) && !empty($attraction['url'])) {
        $resolved = $resolve_attraction_image((string) $attraction['url']);
        $attraction['image'] = $resolved['image'];
        // Kadrowanie ustawione na stronie atrakcji ma pierwszeństwo dla zdjęć z automatu.
        $attraction['image_position'] = $resolved['position'];
    }
}
unset($attraction);

Timber::render('blocks/featured-attractions/featured-attractions.twig', $context);
