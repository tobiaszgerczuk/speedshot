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
    $context['fields'],
    ['block-available-cities', !empty($context['block']['align']) ? 'align' . $context['block']['align'] : '']
);
$context['section_id'] = SectionSettings::section_id($context['fields']);
$context['section_style'] = SectionSettings::inline_style($context['fields']);

$context['available_cities'] = [
    [
        'city' => 'Dzierżoniów',
        'venue' => 'Szkoła Podstawowa nr 1 im. Noblistów Polskich',
        'address' => 'ul. Generała Władysława Sikorskiego 2, 58-200 Dzierżoniów',
        'url' => home_url('/szczegoly-strefy-sportu-dla-dzieci-dzierzoniow/'),
    ],
    [
        'city' => 'Niemodlin',
        'venue' => 'Zespół Szkół im. Bolesława Chrobrego w Niemodlinie',
        'address' => 'ul. Opolska 34, 49-100 Niemodlin',
        'url' => home_url('/szczegoly-strefy-sportu-dla-dzieci-niemodlin/'),
    ],
    [
        'city' => 'Strzelin',
        'venue' => 'Publiczna Szkoła Podstawowa nr 4 w Strzelinie',
        'address' => 'ul. Henryka Sienkiewicza 41, 57-100 Strzelin',
        'url' => home_url('/szczegoly-strefy-sportu-dla-dzieci-strzelin/'),
    ],
    [
        'city' => 'Skarbimierz',
        'venue' => 'Gminna Biblioteka Publiczna w Skarbimierzu',
        'address' => 'ul. Jaśminowa 1, 49-305 Żłobizna',
        'url' => home_url('/szczegoly-strefy-sportu-dla-dzieci-skarbimierz/'),
    ],
    [
        'city' => 'Syców',
        'venue' => 'MOSiR Syców',
        'address' => 'ul. Komorowska 2, 56-500 Syców',
        'url' => home_url('/szczegoly-strefy-sportu-dla-dzieci-sycow/'),
    ],
    [
        'city' => 'Lubin',
        'venue' => 'Szkoła Podstawowa nr 9 Lubin',
        'address' => 'ul. Legnicka 1, 59-300 Lubin',
        'url' => home_url('/szczegoly-strefy-sportu-dla-dzieci-lubin/'),
    ],
];

Timber::render('blocks/available-cities/available-cities.twig', $context);
