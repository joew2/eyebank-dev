<?php
namespace CWS\CustomLinkReports;

defined('ABSPATH') || exit;

final class Scheduler {
    public static function reschedule(int $report_id, array $config): void {
        self::clear_report($report_id);
        if (empty($config['schedule_enabled']) || !empty($config['paused']) || !Settings::has_key()) return;
        $timestamp = Date_Windows::next_occurrence($config);
        $args = array($report_id, (int) $config['revision'], $timestamp);
        $result = wp_schedule_single_event($timestamp, CRON_HOOK, $args, true);
        if (!is_wp_error($result)) {
            $config['next_run'] = $timestamp;
            update_post_meta($report_id, '_cws_clr_config', $config);
        }
    }

    public static function schedule_next(int $report_id, array $config): void {
        if (get_post_status($report_id) !== 'private') return;
        self::reschedule($report_id, $config);
    }

    public static function clear_report(int $report_id): void {
        $crons = _get_cron_array();
        foreach ($crons as $timestamp => $hooks) {
            foreach (array(CRON_HOOK, RETRY_HOOK) as $hook) {
                foreach ((array) ($hooks[$hook] ?? array()) as $event) {
                    if ((int) ($event['args'][0] ?? 0) === $report_id) wp_unschedule_event((int) $timestamp, $hook, $event['args']);
                }
            }
        }
    }

    public static function clear_all(): void {
        foreach (Reports::all() as $report) self::clear_report((int) $report->ID);
        wp_clear_scheduled_hook(RETRY_HOOK);
    }
}

