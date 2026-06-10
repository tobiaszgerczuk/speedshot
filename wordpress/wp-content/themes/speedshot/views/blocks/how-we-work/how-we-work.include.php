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
        'title' => 'Sprawdź ofertę i skontaktuj się z nami',
        'text'  => 'Zerknij na nasze atrakcje i zadzwoń do nas. Nie musisz martwić się wyborem — nasi doradcy podpowiedzą, jakie gry warto wykorzystać na Twoim wydarzeniu.',
    ],
    [
        'title' => 'Wybierzmy wspólnie termin i miejsce',
        'text'  => 'Wszystko dopasowujemy do Ciebie i naszej dostępności. Dbamy o to, by każda atrakcja była dopasowana do Waszych wymagań, warunków i miejsca.',
    ],
    [
        'title' => 'Spotkajmy się i bawmy wyśmienicie',
        'text'  => 'Chcemy przez nasze spotkania integrować, bawić i rozwijać ludzi. Każda atrakcja to nowoczesny sprzęt obsługiwany zawsze pod okiem doświadczonego opiekuna.',
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
