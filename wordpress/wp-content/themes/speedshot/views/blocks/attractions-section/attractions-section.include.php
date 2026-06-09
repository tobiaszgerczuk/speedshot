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
    ['block-attractions-section', !empty($context['block']['align']) ? 'align' . $context['block']['align'] : '']
);
$context['section_id'] = SectionSettings::section_id($context['fields']);
$context['section_style'] = SectionSettings::inline_style($context['fields']);

$items = [];
$menu = wp_get_nav_menu_object('Main');

if ($menu) {
    $items = wp_get_nav_menu_items($menu->term_id) ?: [];
}

$childrenByParent = [];
foreach ($items as $item) {
    $parent = (int) $item->menu_item_parent;
    $childrenByParent[$parent][] = $item;
}

$iconMap = [
    'archery tag' => 'target',
    'bitwy nerf' => 'crosshair',
    'bitwy na pistolety wodne' => 'waves',
    'bitwy czołgów zdalnie sterowanych' => 'radio',
    'bubble football' => 'circle-dot',
    'dmuchańce na wynajem' => 'castle',
    'kule wodne waterball' => 'waves',
    'laser tag' => 'scan-search',
    'mobilny minigolf' => 'flag',
    'piłka wodna łódkami zdalnie sterowanymi' => 'sailboat',
    'poszukiwanie skarbów wykrywaczami metali' => 'gem',
    'symulator lotu samolotem i rajdu samochodowego' => 'plane',
    'turniej fifa/fc' => 'gamepad-2',
    'wirtualna rzeczywistość' => 'sparkles',
    'wirtualna strzelnica' => 'target',
    'wyścigi aut zdalnie sterowanych' => 'car',
];

$descriptionMap = [
    'archery tag' => 'Dynamiczna gra zespołowa z łukami i miękkimi strzałami.',
    'bitwy nerf' => 'Bezpieczne starcia, dużo ruchu i scenariusze pełne emocji.',
    'bitwy na pistolety wodne' => 'Lekka, letnia zabawa idealna na plener i upały.',
    'bitwy czołgów zdalnie sterowanych' => 'Taktyczne pojedynki pojazdów sterowanych radiowo.',
    'bubble football' => 'Piłka nożna w dmuchanych kulach, śmiech gwarantowany.',
    'dmuchańce na wynajem' => 'Kolorowe atrakcje, które od razu przyciągają dzieci.',
    'kule wodne waterball' => 'Chodzenie po wodzie w wielkich kulach i mnóstwo frajdy.',
    'laser tag' => 'Bezpieczna laserowa rywalizacja dla grup w różnym wieku.',
    'mobilny minigolf' => 'Przenośny tor do spokojniejszej, celnej zabawy.',
    'piłka wodna łódkami zdalnie sterowanymi' => 'Sterowane łódki, bramki i wodna rywalizacja.',
    'poszukiwanie skarbów wykrywaczami metali' => 'Misja odkrywców z detektorami i zadaniami w terenie.',
    'symulator lotu samolotem i rajdu samochodowego' => 'Emocje pilota i kierowcy rajdowego w jednym stanowisku.',
    'turniej fifa/fc' => 'Esportowy turniej z prostą organizacją i zdrową rywalizacją.',
    'wirtualna rzeczywistość' => 'Wejście do świata VR, które robi wrażenie na uczestnikach.',
    'wirtualna strzelnica' => 'Celność, refleks i sportowe emocje w bezpiecznej formie.',
    'wyścigi aut zdalnie sterowanych' => 'Zdalnie sterowane auta i tor pełen adrenaliny.',
];

$attractionsRoot = null;
foreach ($items as $item) {
    if ((int) $item->menu_item_parent === 0 && mb_strtolower(wp_strip_all_tags($item->title)) === 'atrakcje') {
        $attractionsRoot = $item;
        break;
    }
}

$groups = [];
if ($attractionsRoot) {
    foreach ($childrenByParent[(int) $attractionsRoot->ID] ?? [] as $groupItem) {
        $cards = [];
        foreach ($childrenByParent[(int) $groupItem->ID] ?? [] as $cardItem) {
            $title = wp_strip_all_tags($cardItem->title);
            $key = mb_strtolower($title);
            $cards[] = [
                'title' => $title,
                'url' => $cardItem->url,
                'icon' => $iconMap[$key] ?? 'sparkles',
                'description' => $descriptionMap[$key] ?? 'Mobilna atrakcja dopasowana do wydarzeń dla dzieci, szkół i grup.',
            ];
        }

        $groups[] = [
            'title' => wp_strip_all_tags($groupItem->title),
            'cards' => $cards,
        ];
    }
}

$context['attractions_groups'] = $groups;

Timber::render('blocks/attractions-section/attractions-section.twig', $context);
