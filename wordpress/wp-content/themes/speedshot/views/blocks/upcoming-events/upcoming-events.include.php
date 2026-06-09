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
    ['block-upcoming-events', !empty($context['block']['align']) ? 'align' . $context['block']['align'] : '']
);
$context['section_id'] = SectionSettings::section_id($context['fields']);
$context['section_style'] = SectionSettings::inline_style($context['fields']);

$limit = isset($fields['upcoming_events_count']) ? max(1, (int) $fields['upcoming_events_count']) : 6;
$now = current_time('Y-m-d H:i:s');

$query = new WP_Query([
    'post_type' => 'event',
    'post_status' => 'publish',
    'posts_per_page' => $limit,
    'meta_key' => 'event_start',
    'orderby' => 'meta_value',
    'order' => 'ASC',
    'meta_query' => [
        [
            'key' => 'event_start',
            'value' => $now,
            'compare' => '>=',
            'type' => 'DATETIME',
        ],
    ],
]);

$events = [];

foreach ($query->posts as $event) {
    $start = (string) get_post_meta($event->ID, 'event_start', true);
    $end = (string) get_post_meta($event->ID, 'event_end', true);
    $startTs = $start !== '' ? strtotime($start) : false;
    $endTs = $end !== '' ? strtotime($end) : false;
    $timeRange = '';

    if ($startTs) {
        $timeRange = wp_date('H:i', $startTs);
    }

    if ($endTs) {
        $timeRange .= $timeRange !== '' ? ' - ' . wp_date('H:i', $endTs) : wp_date('H:i', $endTs);
    }

    $events[] = [
        'title' => get_the_title($event),
        'url' => get_permalink($event),
        'excerpt' => has_excerpt($event) ? get_the_excerpt($event) : wp_trim_words(wp_strip_all_tags((string) $event->post_content), 18),
        'day' => $startTs ? wp_date('d', $startTs) : '',
        'month' => $startTs ? wp_date('M', $startTs) : '',
        'weekday' => $startTs ? wp_date('l', $startTs) : '',
        'time_range' => $timeRange,
        'location' => (string) get_post_meta($event->ID, 'event_location', true),
        'badge' => (string) get_post_meta($event->ID, 'event_badge', true),
    ];
}

$context['events'] = $events;

Timber::render('blocks/upcoming-events/upcoming-events.twig', $context);
