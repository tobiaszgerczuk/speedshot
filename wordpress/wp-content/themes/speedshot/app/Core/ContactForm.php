<?php

namespace Speedshot\Theme\Core;

class ContactForm
{
    private const ACTION = 'speedshot_contact_submit';
    private const NONCE_ACTION = 'speedshot_contact_form';
    private const NONCE_FIELD = 'speedshot_contact_nonce';
    private const RECIPIENT_FIELD = 'speedshot_recipient';
    private const DEFAULT_RECIPIENT = 'info@centrumrozrywki-speedshot.pl';

    public static function boot(): void
    {
        add_action('admin_post_' . self::ACTION, [self::class, 'handle']);
        add_action('admin_post_nopriv_' . self::ACTION, [self::class, 'handle']);
        add_action('wp_ajax_' . self::ACTION, [self::class, 'handle_ajax']);
        add_action('wp_ajax_nopriv_' . self::ACTION, [self::class, 'handle_ajax']);
    }

    public static function action(): string
    {
        return self::ACTION;
    }

    public static function handle_ajax(): void
    {
        if (!isset($_POST[self::NONCE_FIELD]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::NONCE_FIELD])), self::NONCE_ACTION)) {
            wp_send_json_error(['message' => 'Nieprawidłowy token. Odśwież stronę i spróbuj ponownie.'], 403);
        }

        $honeypot = isset($_POST['website']) ? trim((string) wp_unslash($_POST['website'])) : '';
        if ($honeypot !== '') {
            wp_send_json_success(['message' => 'Dziękujemy, wiadomość została wysłana.']);
        }

        $recipient = self::resolve_recipient();
        $name      = self::posted_text('name');
        $phone     = self::posted_text('phone');
        $email     = self::posted_email('email');
        $place     = self::posted_text('place');
        $people    = self::posted_text('people');
        $date      = self::posted_text('date');
        $message   = self::posted_textarea('message');
        $attractions = self::posted_array('attractions');

        if ($name === '' || $phone === '' || $email === '' || $place === '' || $people === '' || $date === '' || $attractions === []) {
            wp_send_json_error(['message' => 'Wypełnij wszystkie wymagane pola.'], 422);
        }

        $subject = sprintf('Zapytanie ze strony SpeedShot - %s', $name);
        $body    = self::message_body([
            'Imię i nazwisko' => $name,
            'Telefon'         => $phone,
            'Email'           => $email,
            'Miejsce'         => $place,
            'Liczba osób'     => $people,
            'Data'            => $date,
            'Atrakcje'        => implode(', ', $attractions),
            'Wiadomość'       => $message,
        ]);
        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'Reply-To: ' . $name . ' <' . $email . '>',
        ];

        $sent = wp_mail($recipient, $subject, $body, $headers);

        if ($sent) {
            wp_send_json_success(['message' => 'Dziękujemy, wiadomość została wysłana. Skontaktujemy się najszybciej jak to możliwe.']);
        } else {
            wp_send_json_error(['message' => 'Nie udało się wysłać formularza. Spróbuj ponownie lub skontaktuj się telefonicznie.'], 500);
        }
    }

    public static function nonce_action(): string
    {
        return self::NONCE_ACTION;
    }

    public static function nonce_field(): string
    {
        return self::NONCE_FIELD;
    }

    public static function recipient_field(): string
    {
        return self::RECIPIENT_FIELD;
    }

    public static function encrypt_recipient(string $email): string
    {
        $email = $email !== '' ? $email : self::DEFAULT_RECIPIENT;
        return base64_encode(wp_hash($email) . '|' . $email);
    }

    public static function handle(): void
    {
        $redirectUrl = self::redirect_url('error');

        if (!isset($_POST[self::NONCE_FIELD]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::NONCE_FIELD])), self::NONCE_ACTION)) {
            wp_safe_redirect($redirectUrl);
            exit;
        }

        $honeypot = isset($_POST['website']) ? trim((string) wp_unslash($_POST['website'])) : '';
        if ($honeypot !== '') {
            wp_safe_redirect(self::redirect_url('sent'));
            exit;
        }

        $recipient = self::resolve_recipient();
        $name = self::posted_text('name');
        $phone = self::posted_text('phone');
        $email = self::posted_email('email');
        $place = self::posted_text('place');
        $people = self::posted_text('people');
        $date = self::posted_text('date');
        $message = self::posted_textarea('message');
        $attractions = self::posted_array('attractions');

        if ($name === '' || $phone === '' || $email === '' || $place === '' || $people === '' || $date === '' || $attractions === []) {
            wp_safe_redirect($redirectUrl);
            exit;
        }

        $subject = sprintf('Zapytanie ze strony SpeedShot - %s', $name);
        $body = self::message_body([
            'Imię i nazwisko' => $name,
            'Telefon' => $phone,
            'Email' => $email,
            'Miejsce' => $place,
            'Liczba osób' => $people,
            'Data' => $date,
            'Atrakcje' => implode(', ', $attractions),
            'Wiadomość' => $message,
        ]);
        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'Reply-To: ' . $name . ' <' . $email . '>',
        ];

        $sent = wp_mail($recipient, $subject, $body, $headers);

        wp_safe_redirect(self::redirect_url($sent ? 'sent' : 'error'));
        exit;
    }

    private static function resolve_recipient(): string
    {
        if (!isset($_POST[self::RECIPIENT_FIELD])) {
            return self::DEFAULT_RECIPIENT;
        }

        $raw = base64_decode(sanitize_text_field(wp_unslash($_POST[self::RECIPIENT_FIELD])), true);
        if ($raw === false || strpos($raw, '|') === false) {
            return self::DEFAULT_RECIPIENT;
        }

        [$hash, $email] = explode('|', $raw, 2);
        $email = sanitize_email($email);

        if ($email === '' || !is_email($email)) {
            return self::DEFAULT_RECIPIENT;
        }

        if (!hash_equals(wp_hash($email), $hash)) {
            return self::DEFAULT_RECIPIENT;
        }

        return $email;
    }

    private static function posted_text(string $key): string
    {
        if (!isset($_POST[$key])) {
            return '';
        }

        return sanitize_text_field(wp_unslash($_POST[$key]));
    }

    private static function posted_email(string $key): string
    {
        if (!isset($_POST[$key])) {
            return '';
        }

        return sanitize_email(wp_unslash($_POST[$key]));
    }

    private static function posted_textarea(string $key): string
    {
        if (!isset($_POST[$key])) {
            return '';
        }

        return sanitize_textarea_field(wp_unslash($_POST[$key]));
    }

    private static function posted_array(string $key): array
    {
        if (!isset($_POST[$key]) || !is_array($_POST[$key])) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn($item): string => sanitize_text_field(wp_unslash($item)),
            $_POST[$key]
        )));
    }

    private static function message_body(array $rows): string
    {
        $lines = ['Nowe zapytanie ze strony SpeedShot:', ''];

        foreach ($rows as $label => $value) {
            if ($value === '') {
                continue;
            }

            $lines[] = $label . ': ' . $value;
        }

        return implode("\n", $lines);
    }

    private static function redirect_url(string $status): string
    {
        $fallback = home_url('/#kontakt');
        $referer = wp_get_referer();
        $url = $referer ?: $fallback;
        $url = remove_query_arg('contact_status', $url);

        return add_query_arg('contact_status', $status, $url) . '#kontakt';
    }
}
