<?php

use Timber\Timber;
use Speedshot\Theme\Blocks\SectionSettings;
use Speedshot\Theme\Core\ContactForm;

$context = Timber::context();
$fields = get_fields() ?: [];
$fields['block_id'] = $fields['block_id'] ?? 'kontakt';

$context['fields'] = $fields;
$context['block'] = $block ?? [];
$context['is_preview'] = $is_preview ?? false;
$context['post_id'] = $post_id ?? 0;
$context['section_classes'] = SectionSettings::build_classes(
    $fields,
    ['block-contact-section', !empty($context['block']['align']) ? 'align' . $context['block']['align'] : '']
);
$context['section_id'] = SectionSettings::section_id($fields);
$context['section_style'] = SectionSettings::inline_style($fields);
$recipient = sanitize_email($fields['contact_section_recipient_email'] ?? '');

$context['form_action'] = admin_url('admin-post.php');
$context['form_action_name'] = ContactForm::action();
$context['nonce_action'] = ContactForm::nonce_action();
$context['nonce_field'] = ContactForm::nonce_field();
$context['recipient_field'] = ContactForm::recipient_field();
$context['recipient_token'] = ContactForm::encrypt_recipient($recipient);
$context['contact_status'] = isset($_GET['contact_status']) ? sanitize_key(wp_unslash($_GET['contact_status'])) : '';
$context['contact_details'] = [
    [
        'label' => 'Telefon',
        'value' => '+48 536 304 931',
        'url' => 'tel:+48536304931',
        'icon' => 'phone',
    ],
    [
        'label' => 'Email',
        'value' => 'info@centrumrozrywki-speedshot.pl',
        'url' => 'mailto:info@centrumrozrywki-speedshot.pl',
        'icon' => 'mail',
    ],
    [
        'label' => 'Adres',
        'value' => 'Szmaragdowa 115a, 55-200 Oława',
        'url' => '',
        'icon' => 'pin',
    ],
];
$context['attractions'] = [
    'Laser Tag',
    'Archer Tag',
    'Bubble Football',
    'Waterball',
    'Wyścigi aut',
    'Bitwa pancerna czołgów',
    'Poszukiwanie skarbów wykrywaczami metali',
    'Wirtualna rzeczywistość',
    'Symulator lotu samolotem',
    'Bitwy wodne',
    'Mobilny minigolf',
    'Wirtualna Strzelnica',
    'Bitwy Nerf',
    'Piłka wodna łódkami zdalnie sterowanymi',
    'Dmuchańce na wynajem',
    'Zamki Dmuchane 5 szt.',
    'Tor Małych Mistrzów',
    'Robo Pies',
];

Timber::render('blocks/contact-section/contact-section.twig', $context);
