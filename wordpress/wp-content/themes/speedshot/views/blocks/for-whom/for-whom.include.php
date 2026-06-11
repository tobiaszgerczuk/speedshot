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
    ['block-for-whom', !empty($block['align']) ? 'align' . $block['align'] : '']
);
$context['section_id']    = SectionSettings::section_id($fields);
$context['section_style'] = SectionSettings::inline_style($fields);

$context['panels'] = [
    [
        'audience' => 'Dla dorosłych',
        'lead'     => 'Integracja, rywalizacja i aktywna rozrywka dla każdej grupy.',
        'items'    => [
            'Imprezy firmowe i team building',
            'Festyny i pikniki plenerowe',
            'Eventy gminne i kulturalne',
            'Wieczory kawalerskie i panieńskie',
        ],
        'icon'     => 'users',
        'modifier' => 'adults',
    ],
    [
        'audience' => 'Dla dzieci i młodzieży',
        'lead'     => 'Bezpieczna zabawa pełna emocji pod opieką doświadczonych animatorów.',
        'items'    => [
            'Kolonie i zielone szkoły',
            'Wycieczki szkolne',
            'Dni sportu i aktywności',
            'Eventy dla rodzin z dziećmi',
        ],
        'icon'     => 'star',
        'modifier' => 'kids',
    ],
];

Timber::render('blocks/for-whom/for-whom.twig', $context);
