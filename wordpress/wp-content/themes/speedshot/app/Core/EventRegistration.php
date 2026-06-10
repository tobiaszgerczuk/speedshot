<?php

namespace Speedshot\Theme\Core;

class EventRegistration
{
    private const POST_TYPE      = 'event_registration';
    private const AJAX_ACTION    = 'speedshot_event_register';
    private const NONCE_ACTION   = 'speedshot_event_registration';
    private const NONCE_FIELD    = 'speedshot_event_reg_nonce';
    private const MENU_SLUG      = 'event-registrations';
    private const PER_PAGE       = 30;

    // -------------------------------------------------------------------------
    // Boot
    // -------------------------------------------------------------------------

    public static function boot(): void
    {
        add_action('init',                         [self::class, 'register_post_type']);
        add_action('wp_ajax_'        . self::AJAX_ACTION, [self::class, 'handle_ajax']);
        add_action('wp_ajax_nopriv_' . self::AJAX_ACTION, [self::class, 'handle_ajax']);
        add_action('admin_menu',                   [self::class, 'admin_menu']);
        add_action('admin_enqueue_scripts',        [self::class, 'admin_assets']);
        add_action('restrict_manage_posts',        [self::class, 'admin_event_filter']);
        add_action('pre_get_posts',                [self::class, 'apply_event_filter']);
    }

    // -------------------------------------------------------------------------
    // Post type
    // -------------------------------------------------------------------------

    public static function register_post_type(): void
    {
        register_post_type(self::POST_TYPE, [
            'labels' => [
                'name'               => 'Zapisy na wydarzenia',
                'singular_name'      => 'Zapis na wydarzenie',
                'menu_name'          => 'Zapisy',
                'all_items'          => 'Wszystkie zapisy',
                'view_item'          => 'Zobacz zapis',
                'search_items'       => 'Szukaj zapisów',
                'not_found'          => 'Nie znaleziono zapisów',
                'not_found_in_trash' => 'Brak zapisów w koszu',
            ],
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => false, // własna strona admina
            'show_in_rest'        => false,
            'supports'            => ['title'],
            'capability_type'     => 'post',
            'capabilities'        => ['create_posts' => 'do_not_allow'],
            'map_meta_cap'        => true,
        ]);
    }

    // -------------------------------------------------------------------------
    // AJAX handler
    // -------------------------------------------------------------------------

    public static function handle_ajax(): void
    {
        if (
            !isset($_POST[self::NONCE_FIELD]) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::NONCE_FIELD])), self::NONCE_ACTION)
        ) {
            wp_send_json_error(['message' => 'Nieprawidłowy token. Odśwież stronę i spróbuj ponownie.'], 403);
        }

        // honeypot
        if (trim((string) wp_unslash($_POST['website'] ?? '')) !== '') {
            wp_send_json_success(['message' => 'Dziękujemy za zapis!']);
        }

        $event_id = (int) ($_POST['event_id'] ?? 0);
        $name     = sanitize_text_field(wp_unslash($_POST['name']    ?? ''));
        $email    = sanitize_email(wp_unslash($_POST['email']         ?? ''));
        $phone    = sanitize_text_field(wp_unslash($_POST['phone']    ?? ''));
        $people   = max(1, (int) ($_POST['people'] ?? 1));
        $message  = sanitize_textarea_field(wp_unslash($_POST['message'] ?? ''));

        if (!$event_id || !$name || !$email || !$phone) {
            wp_send_json_error(['message' => 'Wypełnij wszystkie wymagane pola.'], 422);
        }

        if (!is_email($email)) {
            wp_send_json_error(['message' => 'Podaj poprawny adres email.'], 422);
        }

        $event = get_post($event_id);
        if (!$event || $event->post_type !== 'event' || $event->post_status !== 'publish') {
            wp_send_json_error(['message' => 'Wydarzenie nie istnieje.'], 422);
        }

        $post_id = wp_insert_post([
            'post_type'   => self::POST_TYPE,
            'post_title'  => $name . ' — ' . get_the_title($event_id),
            'post_status' => 'publish',
        ]);

        if (is_wp_error($post_id)) {
            wp_send_json_error(['message' => 'Błąd zapisu. Spróbuj ponownie.'], 500);
        }

        update_post_meta($post_id, 'reg_event_id',  $event_id);
        update_post_meta($post_id, 'reg_name',      $name);
        update_post_meta($post_id, 'reg_email',     $email);
        update_post_meta($post_id, 'reg_phone',     $phone);
        update_post_meta($post_id, 'reg_people',    $people);
        update_post_meta($post_id, 'reg_message',   $message);
        update_post_meta($post_id, 'reg_date',      current_time('Y-m-d H:i:s'));

        // powiadomienie email do admina
        self::send_notification($post_id, $event_id, $name, $email, $phone, $people, $message);

        wp_send_json_success(['message' => 'Zapisano! Do zobaczenia na wydarzeniu.']);
    }

    private static function send_notification(int $reg_id, int $event_id, string $name, string $email, string $phone, int $people, string $message): void
    {
        $event_title    = get_the_title($event_id);
        $event_start    = (string) get_post_meta($event_id, 'event_start', true);
        $event_location = (string) get_post_meta($event_id, 'event_location', true);
        $recipient      = (string) get_option('admin_email');

        $subject = "Nowy zapis: {$event_title} — {$name}";
        $body    = implode("\n", array_filter([
            "Nowy zapis na wydarzenie: {$event_title}",
            '',
            "Imię i nazwisko: {$name}",
            "Email: {$email}",
            "Telefon: {$phone}",
            "Liczba osób: {$people}",
            $event_start    ? "Data wydarzenia: " . wp_date('d.m.Y H:i', strtotime($event_start)) : '',
            $event_location ? "Miejsce: {$event_location}" : '',
            $message        ? "Wiadomość: {$message}" : '',
            '',
            'Zarządzaj zapisami: ' . admin_url('admin.php?page=' . self::MENU_SLUG),
        ]));

        wp_mail($recipient, $subject, $body, [
            'Content-Type: text/plain; charset=UTF-8',
            "Reply-To: {$name} <{$email}>",
        ]);
    }

    // -------------------------------------------------------------------------
    // Admin menu
    // -------------------------------------------------------------------------

    public static function admin_menu(): void
    {
        add_menu_page(
            'Zapisy na wydarzenia',
            'Zapisy',
            'edit_posts',
            self::MENU_SLUG,
            [self::class, 'admin_page'],
            'dashicons-clipboard',
            26
        );
    }

    public static function admin_assets(string $hook): void
    {
        if ($hook !== 'toplevel_page_' . self::MENU_SLUG) {
            return;
        }

        wp_enqueue_style(
            'event-registrations-admin',
            get_template_directory_uri() . '/public/event-registrations-admin.css',
            [],
            filemtime(get_template_directory() . '/public/event-registrations-admin.css') ?: null
        );
    }

    // -------------------------------------------------------------------------
    // Admin page
    // -------------------------------------------------------------------------

    public static function admin_page(): void
    {
        $event_id    = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;
        $current_page = max(1, (int) ($_GET['paged'] ?? 1));
        $offset      = ($current_page - 1) * self::PER_PAGE;

        $meta_query = [['key' => 'reg_event_id', 'compare' => 'EXISTS']];
        if ($event_id) {
            $meta_query[] = ['key' => 'reg_event_id', 'value' => $event_id, 'compare' => '=', 'type' => 'NUMERIC'];
        }

        $query = new \WP_Query([
            'post_type'      => self::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => self::PER_PAGE,
            'offset'         => $offset,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'meta_query'     => $meta_query,
        ]);

        $total      = $query->found_posts;
        $total_pages = ceil($total / self::PER_PAGE);

        // lista wydarzeń do filtra
        $events_query = new \WP_Query([
            'post_type'      => 'event',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'meta_value',
            'meta_key'       => 'event_start',
            'order'          => 'DESC',
        ]);

        // eksport CSV
        if (isset($_GET['export_csv']) && current_user_can('edit_posts')) {
            self::export_csv($query);
            exit;
        }

        $rows = [];
        foreach ($query->posts as $post) {
            $rows[] = [
                'id'       => $post->ID,
                'name'     => (string) get_post_meta($post->ID, 'reg_name',    true),
                'email'    => (string) get_post_meta($post->ID, 'reg_email',   true),
                'phone'    => (string) get_post_meta($post->ID, 'reg_phone',   true),
                'people'   => (int)    get_post_meta($post->ID, 'reg_people',  true),
                'message'  => (string) get_post_meta($post->ID, 'reg_message', true),
                'date'     => (string) get_post_meta($post->ID, 'reg_date',    true),
                'event_id' => (int)    get_post_meta($post->ID, 'reg_event_id', true),
                'event'    => get_the_title((int) get_post_meta($post->ID, 'reg_event_id', true)),
            ];
        }

        self::render_admin_page($rows, $events_query->posts, $event_id, $total, $current_page, $total_pages);
    }

    private static function export_csv(\WP_Query $query): void
    {
        $filename = 'zapisy-' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF"; // UTF-8 BOM for Excel

        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'Imię i nazwisko', 'Email', 'Telefon', 'Osób', 'Wydarzenie', 'Data zapisu', 'Wiadomość'], ';');

        // pobierz wszystkie (bez limitu)
        $all = new \WP_Query(array_merge($query->query, ['posts_per_page' => -1, 'offset' => 0]));
        foreach ($all->posts as $post) {
            fputcsv($out, [
                $post->ID,
                (string) get_post_meta($post->ID, 'reg_name',    true),
                (string) get_post_meta($post->ID, 'reg_email',   true),
                (string) get_post_meta($post->ID, 'reg_phone',   true),
                (int)    get_post_meta($post->ID, 'reg_people',  true),
                get_the_title((int) get_post_meta($post->ID, 'reg_event_id', true)),
                (string) get_post_meta($post->ID, 'reg_date',    true),
                (string) get_post_meta($post->ID, 'reg_message', true),
            ], ';');
        }

        fclose($out);
    }

    private static function render_admin_page(array $rows, array $events, int $selected_event, int $total, int $current_page, int $total_pages): void
    {
        $base_url   = admin_url('admin.php?page=' . self::MENU_SLUG);
        $filter_url = $selected_event ? $base_url . '&event_id=' . $selected_event : $base_url;
        $export_url = $filter_url . '&export_csv=1';
        ?>
        <div class="wrap er-wrap">
            <h1 class="wp-heading-inline">Zapisy na wydarzenia</h1>
            <span class="er-count"><?= esc_html($total) ?> zapisów</span>

            <div class="er-toolbar">
                <form method="get" class="er-filter-form">
                    <input type="hidden" name="page" value="<?= esc_attr(self::MENU_SLUG) ?>">
                    <select name="event_id" onchange="this.form.submit()">
                        <option value="">— wszystkie wydarzenia —</option>
                        <?php foreach ($events as $ev): ?>
                            <option value="<?= esc_attr($ev->ID) ?>" <?= selected($selected_event, $ev->ID, false) ?>>
                                <?= esc_html(get_the_title($ev)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <a href="<?= esc_url($export_url) ?>" class="button er-export-btn">
                    ⬇ Eksportuj CSV
                </a>
            </div>

            <?php if (empty($rows)): ?>
                <div class="er-empty">Brak zapisów<?= $selected_event ? ' dla wybranego wydarzenia' : '' ?>.</div>
            <?php else: ?>
                <table class="widefat er-table">
                    <thead>
                        <tr>
                            <th>Imię i nazwisko</th>
                            <th>Email</th>
                            <th>Telefon</th>
                            <th>Osób</th>
                            <th>Wydarzenie</th>
                            <th>Data zapisu</th>
                            <th>Wiadomość</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td><strong><?= esc_html($row['name']) ?></strong></td>
                                <td><a href="mailto:<?= esc_attr($row['email']) ?>"><?= esc_html($row['email']) ?></a></td>
                                <td><a href="tel:<?= esc_attr($row['phone']) ?>"><?= esc_html($row['phone']) ?></a></td>
                                <td><?= esc_html($row['people']) ?></td>
                                <td>
                                    <?php if ($row['event_id']): ?>
                                        <a href="<?= esc_url($base_url . '&event_id=' . $row['event_id']) ?>"><?= esc_html($row['event']) ?></a>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc_html($row['date'] ? wp_date('d.m.Y H:i', strtotime($row['date'])) : '—') ?></td>
                                <td class="er-message"><?= esc_html($row['message'] ?: '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($total_pages > 1): ?>
                    <div class="er-pagination tablenav-pages">
                        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                            <a
                                href="<?= esc_url(add_query_arg(['paged' => $p], $filter_url)) ?>"
                                class="button <?= $p === $current_page ? 'button-primary' : '' ?>"
                            ><?= $p ?></a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
    }

    // -------------------------------------------------------------------------
    // WP_Query filter (dla natywnego list table, gdyby był potrzebny)
    // -------------------------------------------------------------------------

    public static function admin_event_filter(): void { /* nie używamy */ }
    public static function apply_event_filter(): void { /* nie używamy */ }

    // -------------------------------------------------------------------------
    // Public helpers
    // -------------------------------------------------------------------------

    public static function ajax_action(): string   { return self::AJAX_ACTION; }
    public static function nonce_action(): string  { return self::NONCE_ACTION; }
    public static function nonce_field(): string   { return self::NONCE_FIELD; }
}
