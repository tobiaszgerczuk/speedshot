<?php
// Tworzy/aktualizuje stronę atrakcji "Robo Pies" + galerię + pozycję w menu "Atrakcje".
require '/var/www/html/wp-load.php'; // uruchom: docker compose exec -T wordpress php /var/www/html/wp-content/themes/speedshot/scripts/robo-pies/seed.php
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
kses_remove_filters();

$slug  = 'robo-pies';
$title = 'Robo Pies';
$dir   = __DIR__;

// --- zdjęcia (tymczasowe, z Wikimedia Commons) ---
$meta = json_decode(file_get_contents("$dir/meta.json"), true);
$imageIds = [];
foreach ($meta as $m) {
    $existing = get_posts(['post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => 1,
        'meta_query' => [['key' => '_wp_attached_file', 'value' => '/' . $m['file'], 'compare' => 'LIKE']]]);
    if ($existing) { $imageIds[] = $existing[0]->ID; continue; }
    $tmp = wp_tempnam($m['file']);
    copy("$dir/{$m['file']}", $tmp);
    $id = media_handle_sideload(['name' => $m['file'], 'tmp_name' => $tmp], 0, 'Robo Pies – robot Unitree Go2', [
        'post_excerpt' => $m['credit'],
        'post_content' => 'Źródło: ' . $m['source'],
    ]);
    if (is_wp_error($id)) { fwrite(STDERR, $id->get_error_message() . "\n"); exit(1); }
    update_post_meta($id, '_wp_attachment_image_alt', 'Robo Pies – czworonożny robot Unitree Go2');
    $imageIds[] = $id;
}

// --- treść ---
$p  = fn($t) => "<!-- wp:paragraph -->\n<p>$t</p>\n<!-- /wp:paragraph -->\n\n";
$h  = fn($t, $l = 3) => "<!-- wp:heading {\"level\":$l} -->\n<h$l class=\"wp-block-heading\">$t</h$l>\n<!-- /wp:heading -->\n\n";
$ul = function (array $items) {
    $out = "<!-- wp:list -->\n<ul class=\"wp-block-list\">";
    foreach ($items as $i) $out .= "<!-- wp:list-item -->\n<li>$i</li>\n<!-- /wp:list-item -->\n";
    return $out . "</ul>\n<!-- /wp:list -->\n\n";
};
$block = fn($name, $attrs) => "<!-- wp:$name " . json_encode($attrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . " /-->\n\n";

$content = "<!-- wp:group {\"layout\":{\"type\":\"constrained\",\"contentSize\":\"860px\"}} -->\n<div class=\"wp-block-group\">\n\n"
    . $h('Robo Pies – pokazy i warsztaty', 2)
    . $p('Poznaj prawdziwego robota, który porusza się na czterech łapach i przyciąga uwagę dzieci oraz dorosłych! Robo Pies to spotkanie z nowoczesną technologią, które może stać się edukacyjną przygodą lub atrakcją Twojego wydarzenia.')
    . $p('Centrum Rozrywki Speed Shot przyjeżdża z robotem Unitree Go2 do przedszkoli, szkół oraz na imprezy firmowe, festyny, targi i wydarzenia sportowe. Zapewniamy obsługę prowadzącego i dopasowujemy formę prezentacji do uczestników oraz charakteru spotkania.')
    . $h('Warsztaty dla przedszkoli i szkół')
    . $p('Jak robot utrzymuje równowagę? Co pozwala mu się poruszać? Czy robot przypominający psa jest podobny do prawdziwego zwierzęcia?')
    . $p('Podczas zajęć dzieci obserwują Robo Psa w ruchu, poznają jego możliwości i szukają odpowiedzi na pytania o świat robotów. Prowadzący w przystępny sposób wyjaśnia podstawy robotyki, działanie czujników oraz różnicę między żywym psem a maszyną.')
    . $p('Program dopasowujemy do wieku dzieci przedszkolnych i uczniów szkół podstawowych. Pokaz przeplatamy rozmową, pytaniami i zadaniami angażującymi grupę. Robotem steruje wyłącznie prowadzący, a dzieci uczestniczą jako obserwatorzy i odkrywcy.')
    . $p('To okazja, aby rozbudzić ciekawość, zachęcić do zadawania pytań i pokazać technologię w praktyce.')
    . $h('Robo Pies na Twojej imprezie firmowej!')
    . $p('Szukasz atrakcji, która zainteresuje gości? Robo Pies może pełnić rolę technologicznej maskotki, towarzyszyć prezentacji firmy lub urozmaicić program imprezy.')
    . $p('Sprawdzi się podczas:')
    . $ul([
        'imprez firmowych i pikników rodzinnych,',
        'targów, kongresów i wydarzeń promocyjnych,',
        'festynów i dni miast,',
        'wydarzeń piłkarskich i w strefach kibica.',
    ])
    . $p('Pokazy poruszania się robota, prezentacja jego możliwości oraz możliwość wykonania pamiątkowych zdjęć tworzą okazję do wspólnej zabawy i rozmowy o nowych technologiach.')
    . $h('Przyjeżdżamy do Ciebie')
    . $p('Zapewniamy robota oraz jego obsługę. Przed realizacją ustalamy czas trwania, liczbę uczestników, zakres pokazu i warunki przestrzeni. Dobieramy prezentowane funkcje do miejsca oraz charakteru wydarzenia.')
    . $p('<strong>Chcesz zaprosić Robo Psa do swojej placówki lub na imprezę? Skontaktuj się z nami — przygotujemy ofertę dopasowaną do Twojego wydarzenia!</strong>')
    . $block('acf/attraction-locations', [
        'name' => 'acf/attraction-locations',
        'data' => [
            'attraction_locations_title'  => 'Pokaz możemy zorganizować:',
            '_attraction_locations_title' => 'field_attraction_locations_title',
            'attraction_locations_items'  => ['sala', 'mala-sala', 'sala-sportowa', 'plac', 'boisko', 'plener'],
            '_attraction_locations_items' => 'field_attraction_locations_items',
        ],
        'mode' => 'preview',
    ])
    . "</div>\n<!-- /wp:group -->\n\n"
    . rtrim($block('acf/photo-gallery', [
        'id'   => 'block_robo-pies_gallery',
        'name' => 'acf/photo-gallery',
        'data' => [
            'photo_gallery_images'   => $imageIds,
            '_photo_gallery_images'  => 'field_photo_gallery_images',
            'photo_gallery_columns'  => '3',
            '_photo_gallery_columns' => 'field_photo_gallery_columns',
        ],
        'align' => '',
        'mode'  => 'preview',
    ]));

$postarr = [
    'post_type'    => 'page',
    'post_status'  => 'publish',
    'post_name'    => $slug,
    'post_title'   => $title,
    'post_content' => $content,
    'post_excerpt' => 'Robo Pies to pokazy i warsztaty z czworonożnym robotem Unitree Go2. Centrum Rozrywki SPEED SHOT przyjeżdża z robotem i prowadzącym do przedszkoli, szkół oraz na imprezy firmowe, festyny, targi i wydarzenia sportowe w całej Polsce.',
];
$page = get_page_by_path($slug);
if ($page) $postarr['ID'] = $page->ID;
$pageId = wp_insert_post(wp_slash($postarr), true);
if (is_wp_error($pageId)) { fwrite(STDERR, $pageId->get_error_message() . "\n"); exit(1); }
delete_post_thumbnail($pageId); // inne atrakcje nie mają obrazka wyróżniającego (hero bez podtytułu)

// --- menu: dziecko "Atrakcje" (34), po "Tor Małych Mistrzów" ---
$menuId = 3;
$item = null;
foreach (wp_get_nav_menu_items($menuId) as $i) {
    if ((int) $i->menu_item_parent === 34 && (int) $i->object_id === $pageId) $item = $i;
}
$itemId = wp_update_nav_menu_item($menuId, $item ? $item->ID : 0, [
    'menu-item-title'     => $title,
    'menu-item-object'    => 'page',
    'menu-item-object-id' => $pageId,
    'menu-item-type'      => 'post_type',
    'menu-item-parent-id' => 34,
    'menu-item-position'  => 21,
    'menu-item-status'    => 'publish',
]);

echo "page=$pageId url=" . get_permalink($pageId) . " menu_item=$itemId images=" . implode(',', $imageIds) . "\n";
