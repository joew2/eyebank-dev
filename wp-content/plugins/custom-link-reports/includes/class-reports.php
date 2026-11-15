<?php
namespace CWS\CustomLinkReports;

defined('ABSPATH') || exit;

final class Reports {
    private const META = '_cws_clr_config';

    public static function register_type(): void {
        register_post_type(REPORT_TYPE, array(
            'labels' => array('name' => __('Link Reports', 'custom-link-reports')),
            'public' => false, 'show_ui' => false, 'show_in_rest' => false,
            'supports' => array('title'), 'capability_type' => 'post', 'map_meta_cap' => true,
        ));
    }

    public static function defaults(): array {
        $settings = Settings::get();
        return array(
            'domain_id' => (string) $settings['default_domain'], 'links' => array(),
            'recipients' => self::emails((string) $settings['recipients']),
            'subject' => '{report_name}: {date_start} – {date_end}',
            'introduction' => '', 'footer' => '', 'heading' => '', 'accent' => '', 'logo_id' => 0,
            'sections' => array('summary', 'daily', 'referrers', 'countries', 'browsers', 'os'),
            'top_limit' => 10, 'compare' => false, 'schedule_enabled' => false, 'paused' => true,
            'weekday' => 1, 'time' => '09:00', 'timezone' => (string) $settings['timezone'],
            'window' => 'previous_7', 'revision' => 1, 'next_run' => 0,
        );
    }

    public static function get(int $id): array {
        $config = get_post_meta($id, self::META, true);
        return array_merge(self::defaults(), is_array($config) ? $config : array());
    }

    public static function save(int $id, array $input): array {
        $old = self::get($id);
        $config = self::sanitize($input);
        $config['revision'] = (int) $old['revision'] + 1;
        update_post_meta($id, self::META, $config);
        Scheduler::reschedule($id, $config);
        return $config;
    }

    public static function sanitize(array $in): array {
        $out = self::defaults();
        $out['domain_id'] = preg_replace('/[^0-9]/', '', (string) ($in['domain_id'] ?? ''));
        $out['links'] = array();
        foreach ((array) ($in['links'] ?? array()) as $link) {
            if (!is_array($link)) continue;
            $id = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($link['id'] ?? ''));
            if ($id === '') continue;
            $out['links'][] = array(
                'id' => $id, 'domain_id' => preg_replace('/[^0-9]/', '', (string) ($link['domain_id'] ?? $out['domain_id'])),
                'label' => sanitize_text_field((string) ($link['label'] ?? '')),
                'order' => absint($link['order'] ?? count($out['links'])),
                'short_url' => esc_url_raw((string) ($link['short_url'] ?? '')),
                'destination_url' => esc_url_raw((string) ($link['destination_url'] ?? '')),
            );
        }
        usort($out['links'], static fn($a, $b) => $a['order'] <=> $b['order']);
        $out['recipients'] = self::emails($in['recipients'] ?? array());
        $out['subject'] = sanitize_text_field((string) ($in['subject'] ?? $out['subject']));
        $out['introduction'] = wp_kses((string) ($in['introduction'] ?? ''), self::allowed_html());
        $out['footer'] = wp_kses((string) ($in['footer'] ?? ''), self::allowed_html());
        $out['heading'] = sanitize_text_field((string) ($in['heading'] ?? ''));
        $accent = sanitize_hex_color((string) ($in['accent'] ?? ''));
        $out['accent'] = $accent ?: '';
        $out['logo_id'] = absint($in['logo_id'] ?? 0);
        $allowed_sections = array('summary', 'daily', 'referrers', 'countries', 'browsers', 'os', 'human');
        $out['sections'] = array_values(array_intersect($allowed_sections, array_map('sanitize_key', (array) ($in['sections'] ?? array()))));
        $limit = absint($in['top_limit'] ?? 10);
        $out['top_limit'] = in_array($limit, array(5, 10, 20), true) ? $limit : 10;
        $out['compare'] = !empty($in['compare']);
        $out['schedule_enabled'] = !empty($in['schedule_enabled']);
        $out['paused'] = empty($in['schedule_enabled']) || !empty($in['paused']);
        $out['weekday'] = min(7, max(1, absint($in['weekday'] ?? 1)));
        $out['time'] = preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', (string) ($in['time'] ?? '')) ? (string) $in['time'] : '09:00';
        $tz = sanitize_text_field((string) ($in['timezone'] ?? 'UTC'));
        $out['timezone'] = in_array($tz, timezone_identifiers_list(), true) ? $tz : 'UTC';
        $out['window'] = in_array(($in['window'] ?? ''), array('previous_7', 'previous_week'), true) ? (string) $in['window'] : 'previous_7';
        return $out;
    }

    public static function all(): array {
        return get_posts(array('post_type' => REPORT_TYPE, 'post_status' => 'private', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC'));
    }

    public static function emails($value): array {
        $items = is_array($value) ? $value : preg_split('/[\s,;]+/', (string) $value);
        $valid = array();
        foreach ((array) $items as $email) {
            $email = sanitize_email(trim((string) $email));
            if ($email !== '' && is_email($email)) $valid[strtolower($email)] = $email;
        }
        return array_values($valid);
    }

    public static function allowed_html(): array {
        return array('a' => array('href' => true, 'title' => true), 'strong' => array(), 'b' => array(), 'em' => array(), 'i' => array(), 'br' => array(), 'p' => array());
    }
}

