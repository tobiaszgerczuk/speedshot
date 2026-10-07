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
    ['block-photo-gallery', !empty($block['align']) ? 'align' . $block['align'] : '']
);
$context['section_id']    = SectionSettings::section_id($fields);
$context['section_style'] = SectionSettings::inline_style($fields);

$raw_images = $fields['photo_gallery_images'] ?? [];
$images = [];
foreach ($raw_images as $img) {
    if (empty($img)) continue;
    if (is_array($img)) {
        $images[] = [
            'url'   => $img['url']           ?? '',
            'thumb' => $img['sizes']['large'] ?? $img['url'] ?? '',
            'alt'   => $img['alt']            ?? '',
        ];
    } elseif (is_numeric($img)) {
        $src = wp_get_attachment_image_src($img, 'full');
        $thumb = wp_get_attachment_image_src($img, 'large');
        if ($src) {
            $images[] = [
                'url'   => $src[0],
                'thumb' => $thumb ? $thumb[0] : $src[0],
                'alt'   => get_post_meta($img, '_wp_attachment_image_alt', true) ?: '',
            ];
        }
    }
}

$context['images']  = $images;
$context['columns'] = (int) ($fields['photo_gallery_columns'] ?? 3);

$fit = $fields['photo_gallery_fit'] ?? 'cover';
$context['fit'] = in_array($fit, ['cover', 'contain'], true) ? $fit : 'cover';

Timber::render('blocks/photo-gallery/photo-gallery.twig', $context);
