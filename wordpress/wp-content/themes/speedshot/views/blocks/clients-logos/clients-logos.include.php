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
    ['block-clients-logos', !empty($block['align']) ? 'align' . $block['align'] : '']
);
$context['section_id']    = SectionSettings::section_id($fields);
$context['section_style'] = SectionSettings::inline_style($fields);

// Loga z ACF lub fallback: galeria ze strony O nas (IDs załączników)
$image_ids = !empty($fields['clients_logos_images']) && is_array($fields['clients_logos_images'])
    ? $fields['clients_logos_images']
    : range(378, 409);

$logos = [];
foreach ($image_ids as $image_id) {
    $id  = is_array($image_id) ? ($image_id['ID'] ?? 0) : (int) $image_id;
    $url = $id ? wp_get_attachment_image_url($id, 'medium') : '';
    if (!$url) {
        continue;
    }
    $logos[] = [
        'url' => $url,
        'alt' => $id ? (get_post_meta($id, '_wp_attachment_image_alt', true) ?: '') : '',
    ];
}

$context['logos'] = $logos;

Timber::render('blocks/clients-logos/clients-logos.twig', $context);
