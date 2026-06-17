<?php

use Timber\Timber;
use Speedshot\Theme\Blocks\SectionSettings;

$context = Timber::context();
$fields  = get_fields() ?: [];

$context['fields']     = $fields;
$context['block']      = $block ?? [];
$context['is_preview'] = $is_preview ?? false;

$context['section_classes'] = SectionSettings::build_classes(
    $fields,
    ['block-attraction-locations', !empty($block['align']) ? 'align' . $block['align'] : '']
);
$context['section_id']    = SectionSettings::section_id($fields);
$context['section_style'] = SectionSettings::inline_style($fields);

$context['locations_title'] = $fields['attraction_locations_title'] ?: 'Zabawę możemy zorganizować:';

// Mapa: slug => [etykieta, ścieżki SVG]. Inline SVG (jak w bloku featured-attractions),
// bo zestaw ikon Lucide w froncie jest tree-shakowany i nie zawiera tych ikon.
$map = [
    'park-las'         => ['Park lub las',               '<path d="M12 3 6 13h3l-3 5h12l-3-5h3z"/><line x1="12" y1="18" x2="12" y2="22"/>'],
    'boisko'           => ['Boisko',                     '<rect x="3" y="6" width="18" height="12" rx="1"/><line x1="12" y1="6" x2="12" y2="18"/><circle cx="12" cy="12" r="2.5"/>'],
    'boisko-trawiaste' => ['Boisko trawiaste',           '<rect x="3" y="6" width="18" height="12" rx="1"/><line x1="12" y1="6" x2="12" y2="18"/><circle cx="12" cy="12" r="2.5"/>'],
    'plac'             => ['Plac',                       '<rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="9" x2="9" y2="21"/>'],
    'sala-sportowa'    => ['Sala sportowa',              '<path d="M3 21V8l9-5 9 5v13"/><rect x="9" y="13" width="6" height="8"/>'],
    'sala'             => ['Sala',                       '<path d="M3 21V8l9-5 9 5v13"/><rect x="9" y="13" width="6" height="8"/>'],
    'mala-sala'        => ['Mała sala',                  '<path d="M3 11l9-8 9 8"/><path d="M5 9v12h14V9"/>'],
    'plaza'            => ['Plaża',                       '<circle cx="12" cy="12" r="4"/><line x1="12" y1="2" x2="12" y2="5"/><line x1="12" y1="19" x2="12" y2="22"/><line x1="2" y1="12" x2="5" y2="12"/><line x1="19" y1="12" x2="22" y2="12"/><line x1="5" y1="5" x2="7" y2="7"/><line x1="17" y1="17" x2="19" y2="19"/><line x1="19" y1="5" x2="17" y2="7"/><line x1="7" y1="17" x2="5" y2="19"/>'],
    'plener'           => ['Każde miejsce w plenerze',   '<path d="M12 21s-7-6.5-7-11a7 7 0 0 1 14 0c0 4.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>'],
];

$selected = $fields['attraction_locations_items'] ?? [];
if (!is_array($selected)) {
    $selected = [];
}

$locations = [];
foreach ($selected as $slug) {
    if (!isset($map[$slug])) {
        continue;
    }
    [$label, $paths] = $map[$slug];
    $locations[] = [
        'label' => $label,
        'svg'   => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths . '</svg>',
    ];
}

$context['locations'] = $locations;

Timber::render('blocks/attraction-locations/attraction-locations.twig', $context);
