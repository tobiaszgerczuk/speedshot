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

$iconMap = [
    'laserowy paintball'                                 => 'crosshair',
    'bitwy nerf'                                         => 'crosshair',
    'archer tag'                                         => 'target',
    'bubble football'                                    => 'circle-dot',
    'bitwy wodne'                                        => 'waves',
    'kule wodne'                                         => 'waves',
    'piana party'                                        => 'cloud',
    'mini golf'                                          => 'flag',
    'dmuchańce'                                          => 'castle',
    'poszukiwanie skarbów wykrywaczami metali'           => 'gem',
    'autka zdalnie sterowane'                            => 'car',
    'wyścigi autek zdalnie sterowanych'                  => 'trophy',
    'mobilny tor wyścigowy carrera'                      => 'gauge',
    'bitwy czołgów zdalnie sterowanych'                  => 'radio',
    'wirtualna rzeczywistość'                            => 'glasses',
    'laserowa strzelnica'                                => 'target',
    'turniej fifa/fc'                                    => 'gamepad-2',
    'tor małych mistrzów'                                => 'medal',
    'robo pies'                                          => 'sparkles',
    // pozycje spoza listy klienta (zostawione)
    'piłka wodna łódkami zdalnie sterowanymi'            => 'sailboat',
    'symulator lotu samolotem i rajdu samochodowego'     => 'plane',
];

$descriptionMap = [
    'laserowy paintball'                                 => 'Bezpieczna, bezkontaktowa rywalizacja laserowa dla grup w różnym wieku.',
    'bitwy nerf'                                         => 'Bezpieczne starcia na piankowe strzałki, dużo ruchu i emocji.',
    'archer tag'                                         => 'Dynamiczna gra zespołowa z łukami i miękkimi strzałami.',
    'bubble football'                                    => 'Piłka nożna w dmuchanych kulach, śmiech gwarantowany.',
    'bitwy wodne'                                        => 'Lekka, letnia zabawa na pistolety wodne idealna na upały.',
    'kule wodne'                                         => 'Chodzenie po wodzie w wielkich kulach i mnóstwo frajdy.',
    'piana party'                                        => 'Spektakularne widowisko z białą, puszystą pianą.',
    'mini golf'                                          => 'Przenośny tor do spokojniejszej, celnej zabawy.',
    'dmuchańce'                                          => 'Mini festiwal na 5 dmuchanych zamkach dla każdego wieku.',
    'poszukiwanie skarbów wykrywaczami metali'           => 'Misja odkrywców z detektorami i zadaniami w terenie.',
    'autka zdalnie sterowane'                            => 'Profesjonalne modele RC i tor przeszkód pełen emocji.',
    'wyścigi autek zdalnie sterowanych'                  => 'Modele RC 1:10 z napędem 4×4 i tor pełen adrenaliny.',
    'mobilny tor wyścigowy carrera'                      => 'Widowiskowy tor z komputerowym pomiarem czasu.',
    'bitwy czołgów zdalnie sterowanych'                  => 'Taktyczne pojedynki metalowych czołgów sterowanych radiowo.',
    'wirtualna rzeczywistość'                            => 'Wejście do świata VR, które robi wrażenie na uczestnikach.',
    'laserowa strzelnica'                                => 'Celność, refleks i sportowe emocje w bezpiecznej formie.',
    'turniej fifa/fc'                                    => 'Esportowy turniej z prostą organizacją i zdrową rywalizacją.',
    'tor małych mistrzów'                                => 'Sportowo-rekreacyjny tor przeszkód dla najmłodszych.',
    'robo pies'                                          => 'Pokazy i warsztaty z czworonożnym robotem Unitree Go2.',
    // pozycje spoza listy klienta (zostawione)
    'piłka wodna łódkami zdalnie sterowanymi'            => 'Sterowane łódki, bramki i wodna rywalizacja.',
    'symulator lotu samolotem i rajdu samochodowego'     => 'Emocje pilota i kierowcy rajdowego w jednym stanowisku.',
];

$menu = wp_get_nav_menu_object('Main');
$allItems = $menu ? (wp_get_nav_menu_items($menu->term_id) ?: []) : [];

// Znajdź ID pozycji "Atrakcje"
$attractionsId = 0;
foreach ($allItems as $item) {
    if ((int) $item->menu_item_parent === 0 && mb_strtolower(wp_strip_all_tags($item->title)) === 'atrakcje') {
        $attractionsId = (int) $item->ID;
        break;
    }
}

// Zbierz atrakcje — obsługuje zarówno płaską strukturę jak i grupowaną (CZĘŚĆ 1/2)
$attractions = [];
if ($attractionsId) {
    // Zbierz bezpośrednie dzieci "Atrakcje"
    $directChildren = [];
    foreach ($allItems as $item) {
        if ((int) $item->menu_item_parent === $attractionsId) {
            $directChildren[] = $item;
        }
    }

    // Sprawdź czy którekolwiek dziecko ma własne dzieci (struktura grupowana)
    $directChildIds = array_map(fn($i) => (int) $i->ID, $directChildren);
    $hasGrandchildren = false;
    foreach ($allItems as $item) {
        if (in_array((int) $item->menu_item_parent, $directChildIds, true)) {
            $hasGrandchildren = true;
            break;
        }
    }

    if ($hasGrandchildren) {
        // Struktura grupowana: zbierz wnuki (dzieci grup CZĘŚĆ 1/2)
        foreach ($allItems as $item) {
            if (!in_array((int) $item->menu_item_parent, $directChildIds, true)) {
                continue;
            }
            $title = wp_strip_all_tags($item->title);
            $key   = mb_strtolower($title);
            $attractions[] = [
                'title'       => $title,
                'url'         => $item->url,
                'icon'        => $iconMap[$key]        ?? 'sparkles',
                'description' => $descriptionMap[$key] ?? 'Mobilna atrakcja dopasowana do wydarzeń dla dzieci, szkół i grup.',
            ];
        }
    } else {
        // Struktura płaska: bezpośrednie dzieci to atrakcje
        foreach ($directChildren as $item) {
            $title = wp_strip_all_tags($item->title);
            $key   = mb_strtolower($title);
            $attractions[] = [
                'title'       => $title,
                'url'         => $item->url,
                'icon'        => $iconMap[$key]        ?? 'sparkles',
                'description' => $descriptionMap[$key] ?? 'Mobilna atrakcja dopasowana do wydarzeń dla dzieci, szkół i grup.',
            ];
        }
    }
}

$context['attractions'] = $attractions;

Timber::render('blocks/attractions-section/attractions-section.twig', $context);
