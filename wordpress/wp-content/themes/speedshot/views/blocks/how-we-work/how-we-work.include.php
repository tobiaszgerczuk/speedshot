<?php

use Timber\Timber;
use Speedshot\Theme\Blocks\SectionSettings;

$context = Timber::context();
$fields = get_fields() ?: [];

$context['fields'] = $fields;
$context['block'] = $block ?? [];
$context['is_preview'] = $is_preview ?? false;
$context['post_id'] = $post_id ?? 0;
$context['section_classes'] = SectionSettings::build_classes(
    $fields,
    ['block-how-we-work', !empty($context['block']['align']) ? 'align' . $context['block']['align'] : '']
);
$context['section_id'] = SectionSettings::section_id($fields);
$context['section_style'] = SectionSettings::inline_style($fields);

$default_steps = [
    [
        'title' => 'Wyślij zapytanie lub zadzwoń',
        'text'  => 'Opisz wydarzenie — rodzaj, termin, miejsce i liczbę uczestników. Odpowiemy szybko z propozycją dopasowaną do charakteru imprezy i grupy.',
    ],
    [
        'title' => 'Ustalamy program i szczegóły',
        'text'  => 'Dobieramy atrakcje odpowiednie dla Twojej grupy — wiekowej, tematycznej, budżetowej. Zajmujemy się całą logistyką: transport sprzętu, obsada, harmonogram.',
    ],
    [
        'title' => 'Przyjeżdżamy i realizujemy',
        'text'  => 'Nasz zespół stawia się na miejscu z pełnym sprzętem i prowadzi program od początku do końca. Ty możesz skupić się na gościach — resztą zajmujemy się my.',
    ],
];

$acf_steps = [];
if (!empty($fields['how_we_work_steps']) && is_array($fields['how_we_work_steps'])) {
    foreach ($fields['how_we_work_steps'] as $row) {
        if (!empty($row['title'])) {
            $acf_steps[] = [
                'title' => $row['title'],
                'text'  => $row['text'] ?? '',
            ];
        }
    }
}

$context['steps'] = $acf_steps ?: $default_steps;

Timber::render('blocks/how-we-work/how-we-work.twig', $context);
