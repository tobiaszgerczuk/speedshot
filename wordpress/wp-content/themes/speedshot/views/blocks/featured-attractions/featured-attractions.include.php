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
        'url'         => '/atrakcje/laser-tag/',
        'icon'        => 'crosshair',
        'image'       => '',
    ],
    [
        'number'      => '02',
        'title'       => 'Tor Wyścigowy Carrera',
        'description' => 'Profesjonalny, mobilny tor wyścigowy z autami Carrera. Rywalizacja na czas, turnieje i kibice przy bandach — hit każdego eventu plenerowego.',
        'tags'        => ['Dla dorosłych', 'Dla dzieci', 'Plener'],
        'badge'       => 'Nowość',
        'url'         => '/atrakcje/',
        'icon'        => 'car',
        'image'       => '',
    ],
    [
        'number'      => '03',
        'title'       => 'Mobilny Minigolf',
        'description' => 'Przenośny tor minigolfa — spokojna, rodzinna zabawa dostępna dosłownie wszędzie. Świetna jako atrakcja towarzysząca większym eventom lub samodzielny punkt programu.',
        'tags'        => ['Dla rodzin', 'Plener', 'Łatwy transport'],
        'badge'       => '',
        'url'         => '/atrakcje/mobilny-minigolf/',
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
            'tags'        => $tags,
        ];
    }
    $context['attractions'] = $attractions;
} else {
    $context['attractions'] = $defaults;
}

Timber::render('blocks/featured-attractions/featured-attractions.twig', $context);
