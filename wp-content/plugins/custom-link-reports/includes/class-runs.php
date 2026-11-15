<?php
namespace CWS\CustomLinkReports;

defined('ABSPATH') || exit;

final class Runs {
    public static function create(int $report_id, string $run_key, int $occurrence, array $period, array $config, int $attempt = 1): int {
        global $wpdb;
        $wpdb->query($wpdb->prepare(
            'INSERT IGNORE INTO ' . Schema::table() . ' (report_id,run_key,occurrence_utc,period_start,period_end,timezone,status,attempt,config_snapshot,recipient_state,created_at) VALUES (%d,%s,%d,%s,%s,%s,%s,%d,%s,%s,%s)',
            $report_id, $run_key, $occurrence, $period[0], $period[1], $config['timezone'], 'collecting', $attempt,
            wp_json_encode($config), wp_json_encode(array()), current_time('mysql', true)
        ));
        return (int) $wpdb->insert_id;
    }

    public static function update(int $id, array $fields): void {
        global $wpdb;
        $allowed = array('status', 'attempt', 'aggregate_snapshot', 'recipient_state', 'error', 'completed_at');
        $data = array_intersect_key($fields, array_flip($allowed));
        if ($data) $wpdb->update(Schema::table(), $data, array('id' => $id));
    }

    public static function recent(int $limit = 50): array {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . Schema::table() . ' ORDER BY id DESC LIMIT %d', $limit));
    }

    public static function last_for_report(int $report_id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . Schema::table() . ' WHERE report_id=%d ORDER BY id DESC LIMIT 1', $report_id));
    }

    public static function purge(): void {
        global $wpdb;
        $days = max(1, (int) Settings::get()['retention_days']);
        $wpdb->query($wpdb->prepare('DELETE FROM ' . Schema::table() . ' WHERE created_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL %d DAY)', $days));
    }
}

