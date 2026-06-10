<?php

use Timber\Timber;
use Speedshot\Theme\Core\EventRegistration;

$context = Timber::context();
$post    = Timber::get_post();
$context['post'] = $post;

$event_id  = $post->ID;
$start_raw = (string) get_post_meta($event_id, 'event_start',    true);
$end_raw   = (string) get_post_meta($event_id, 'event_end',      true);
$startTs   = $start_raw ? strtotime($start_raw) : false;
$endTs     = $end_raw   ? strtotime($end_raw)   : false;

$context['event'] = [
    'id'       => $event_id,
    'location' => (string) get_post_meta($event_id, 'event_location', true),
    'badge'    => (string) get_post_meta($event_id, 'event_badge',    true),
    'date'     => $startTs ? wp_date('d.m.Y',       $startTs) : '',
    'weekday'  => $startTs ? wp_date('l',            $startTs) : '',
    'time_start' => $startTs ? wp_date('H:i',        $startTs) : '',
    'time_end'   => $endTs   ? wp_date('H:i',        $endTs)   : '',
    'is_past'  => $startTs && $startTs < time(),
];

$context['reg_ajax_url']   = admin_url('admin-ajax.php');
$context['reg_ajax_action'] = EventRegistration::ajax_action();
$context['reg_nonce_action'] = EventRegistration::nonce_action();
$context['reg_nonce_field']  = EventRegistration::nonce_field();

Timber::render('single-event.twig', $context);
