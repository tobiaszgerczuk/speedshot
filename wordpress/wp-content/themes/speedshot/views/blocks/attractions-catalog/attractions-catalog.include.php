<?php

use Timber\Timber;
use Speedshot\Theme\Blocks\SectionSettings;

$context = Timber::context();
$fields  = get_fields() ?: [];

$context['fields']     = $fields;
$context['block']      = $block ?? [];
$context['is_preview'] = $is_preview ?? false;

$context['section_classes'] = SectionSettings::build_classes(
    $fields,
    ['block-attractions-catalog', !empty($block['align']) ? 'align' . $block['align'] : '']
);
$context['section_id']    = SectionSettings::section_id($fields);
$context['section_style'] = SectionSettings::inline_style($fields);

$context['eyebrow']   = $fields['attractions_catalog_eyebrow'] ?? '';
$context['title']     = $fields['attractions_catalog_title'] ?: 'Katalog atrakcji';
$context['subtitle']  = $fields['attractions_catalog_subtitle'] ?? '';
$context['intro']     = $fields['attractions_catalog_intro'] ?? '';

$rows  = $fields['attractions_catalog_files'] ?? [];
$files = [];

if (is_array($rows)) {
    foreach ($rows as $row) {
        $file = $row['file'] ?? null;

        // Pole file (return_format=array) -> tablica; obsluz tez ID/URL na wszelki wypadek.
        if (is_array($file)) {
            $url      = $file['url'] ?? '';
            $filename = $file['filename'] ?? '';
            $bytes    = (int) ($file['filesize'] ?? 0);
        } elseif (is_numeric($file)) {
            $url      = (string) wp_get_attachment_url((int) $file);
            $path     = get_attached_file((int) $file);
            $filename = $path ? basename($path) : '';
            $bytes    = ($path && file_exists($path)) ? (int) filesize($path) : 0;
        } else {
            continue;
        }

        if ($url === '') {
            continue;
        }

        $label = trim((string) ($row['label'] ?? ''));
        if ($label === '') {
            $label = $filename !== '' ? $filename : 'Pobierz PDF';
        }

        $files[] = [
            'url'   => $url,
            'label' => $label,
            'note'  => trim((string) ($row['note'] ?? '')),
            'size'  => $bytes > 0 ? size_format($bytes) : '',
        ];
    }
}

$context['files'] = $files;

Timber::render('blocks/attractions-catalog/attractions-catalog.twig', $context);
