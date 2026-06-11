<?php

use Timber\Timber;
use Speedshot\Theme\Blocks\SectionSettings;

$context = Timber::context();
$fields  = get_fields() ?: [];

$context['block']           = $block ?? [];
$context['is_preview']      = $is_preview ?? false;
$context['section_classes'] = SectionSettings::build_classes(
    $fields,
    ['block-contact-info', !empty($block['align']) ? 'align' . $block['align'] : '']
);
$context['section_id']    = SectionSettings::section_id($fields);
$context['section_style'] = SectionSettings::inline_style($fields);

$context['phone']        = $fields['contact_info_phone']        ?? '+48 536 304 931';
$context['email']        = $fields['contact_info_email']        ?? 'info@centrumrozrywki-speedshot.pl';
$context['response']     = $fields['contact_info_response']     ?? 'Odpowiadamy zwykle w ciągu kilku godzin w dni robocze';
$context['area']         = $fields['contact_info_area']         ?? 'Obsługujemy całą Polskę – dojeżdżamy do Ciebie';

Timber::render('blocks/contact-info/contact-info.twig', $context);
