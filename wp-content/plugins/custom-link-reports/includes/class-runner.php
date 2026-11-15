<?php
namespace CWS\CustomLinkReports;

defined('ABSPATH') || exit;

final class Runner {
    public static function scheduled(int $report_id, int $revision, int $occurrence): void {
        $config = Reports::get($report_id);
        // Always create the next future occurrence independently of this run.
        Scheduler::schedule_next($report_id, $config);
        if ((int) $config['revision'] !== $revision || empty($config['schedule_enabled']) || !empty($config['paused']) || !empty(Settings::get()['global_pause'])) return;
        $latest_due = Date_Windows::most_recent_occurrence($config);
        if ($occurrence < $latest_due - DAY_IN_SECONDS) {
            $old_reference = (new \DateTimeImmutable('@' . $occurrence))->setTimezone(new \DateTimeZone($config['timezone']));
            $old_period = Date_Windows::period($config['window'], $old_reference);
            $skipped_id = Runs::create($report_id, hash('sha256', $report_id . '|' . $occurrence . '|skipped'), $occurrence, $old_period, $config, 1);
            if ($skipped_id) Runs::update($skipped_id, array('status' => 'skipped', 'error' => 'A newer weekly occurrence was already due; catch-up burst suppressed.', 'completed_at' => current_time('mysql', true)));
            $occurrence = $latest_due;
        }
        $reference = (new \DateTimeImmutable('@' . $occurrence))->setTimezone(new \DateTimeZone($config['timezone']));
        $period = Date_Windows::period($config['window'], $reference);
        self::execute($report_id, $config, $period, $occurrence, false, array(), 1);
    }

    public static function retry(int $run_id, int $report_id): void {
        global $wpdb;
        $run = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . Schema::table() . ' WHERE id=%d', $run_id));
        if (!$run) return;
        $config = json_decode((string) $run->config_snapshot, true);
        $states = json_decode((string) $run->recipient_state, true);
        if (!is_array($config) || get_post_status($report_id) !== 'private' || (int) Reports::get($report_id)['revision'] !== (int) $config['revision']) return;
        self::execute($report_id, $config, array($run->period_start, $run->period_end), (int) $run->occurrence_utc, false, is_array($states) ? $states : array(), (int) $run->attempt + 1, (int) $run->id);
    }

    public static function manual(int $report_id, array $period, ?string $test_recipient = null, bool $refresh = false) {
        $config = Reports::get($report_id);
        if ($test_recipient !== null) {
            $config['recipients'] = Reports::emails(array($test_recipient));
            $config['bcc_recipients'] = array();
        }
        return self::execute($report_id, $config, $period, time(), $test_recipient !== null, array(), 1, 0, $refresh);
    }

    private static function execute(int $report_id, array $config, array $period, int $occurrence, bool $test, array $states, int $attempt, int $existing_run = 0, bool $refresh = false) {
        if (!Settings::has_key()) return new \WP_Error('cws_clr_no_key', __('The Short.io key is missing.', 'custom-link-reports'));
        if (!$config['links']) return new \WP_Error('cws_clr_no_links', __('The report has no selected links.', 'custom-link-reports'));
        if (!$config['recipients'] && empty($config['bcc_recipients'])) return new \WP_Error('cws_clr_no_recipients', __('The report has no valid recipients.', 'custom-link-reports'));
        $lock = 'cws_clr_lock_' . $report_id;
        if (get_transient($lock)) return new \WP_Error('cws_clr_busy', __('This report is already running.', 'custom-link-reports'));
        set_transient($lock, 1, 20 * MINUTE_IN_SECONDS);
        $run_key = hash('sha256', $report_id . '|' . $occurrence . '|' . ($test ? 'test|' . implode(',', $config['recipients']) . '|' . microtime(true) : 'scheduled'));
        $run_id = $existing_run ?: Runs::create($report_id, $run_key, $occurrence, $period, $config, $attempt);
        if (!$run_id) { delete_transient($lock); return new \WP_Error('cws_clr_duplicate', __('This report occurrence was already processed.', 'custom-link-reports')); }
        $data = Renderer::aggregate($config, $period[0], $period[1], $refresh);
        if (is_wp_error($data)) {
            Runs::update($run_id, array('status' => 'failed', 'attempt' => $attempt, 'error' => sanitize_text_field($data->get_error_message()), 'completed_at' => current_time('mysql', true)));
            self::maybe_retry($run_id, $report_id, $attempt, $data);
            delete_transient($lock); return $data;
        }
        $name = Reports::title($report_id);
        $html = Renderer::html($name, $config, $data);
        $subject = Renderer::subject($config['subject'], $name, $period[0], $period[1]);
        if ($test) $subject = '[TEST] ' . $subject;
        foreach ($config['recipients'] as $recipient) {
            $state_key = 'to:' . strtolower($recipient);
            // Recognize recipient states written by versions before typed delivery keys.
            if (!isset($states[$state_key]) && isset($states[$recipient])) $states[$state_key] = $states[$recipient];
            if (($states[$state_key]['status'] ?? '') === 'accepted') continue;
            if (($states[$state_key]['status'] ?? '') === 'handing_off') { $states[$state_key] = array('status' => 'unknown', 'at' => time(), 'type' => 'to', 'recipient' => $recipient); continue; }
            $states[$state_key] = array('status' => 'handing_off', 'at' => time(), 'type' => 'to', 'recipient' => $recipient);
            Runs::update($run_id, array('recipient_state' => wp_json_encode($states), 'status' => 'sending', 'aggregate_snapshot' => wp_json_encode($data)));
            $accepted = Mailer::send($recipient, $subject, $html);
            $states[$state_key] = array('status' => $accepted ? 'accepted' : 'failed', 'at' => time(), 'type' => 'to', 'recipient' => $recipient);
        }
        // Private copies are sent separately so their addresses never enter a regular recipient's headers.
        foreach (Reports::emails($config['bcc_recipients'] ?? array()) as $recipient) {
            $state_key = 'bcc:' . strtolower($recipient);
            if (($states[$state_key]['status'] ?? '') === 'accepted') continue;
            if (($states[$state_key]['status'] ?? '') === 'handing_off') { $states[$state_key] = array('status' => 'unknown', 'at' => time(), 'type' => 'bcc', 'recipient' => $recipient); continue; }
            $states[$state_key] = array('status' => 'handing_off', 'at' => time(), 'type' => 'bcc', 'recipient' => $recipient);
            Runs::update($run_id, array('recipient_state' => wp_json_encode($states), 'status' => 'sending', 'aggregate_snapshot' => wp_json_encode($data)));
            $accepted = Mailer::send($recipient, $subject, $html);
            $states[$state_key] = array('status' => $accepted ? 'accepted' : 'failed', 'at' => time(), 'type' => 'bcc', 'recipient' => $recipient);
        }
        $failed = array_filter($states, static fn($s) => ($s['status'] ?? '') === 'failed');
        $unknown = array_filter($states, static fn($s) => ($s['status'] ?? '') === 'unknown');
        $status = $unknown ? 'unknown' : ($failed ? 'partial_failed' : 'accepted');
        Runs::update($run_id, array('status' => $status, 'attempt' => $attempt, 'aggregate_snapshot' => wp_json_encode($data), 'recipient_state' => wp_json_encode($states), 'error' => $unknown ? 'Mail handoff state is uncertain; manual review required.' : ($failed ? 'One or more recipients were not accepted by wp_mail().' : ''), 'completed_at' => current_time('mysql', true)));
        if ($failed && !$unknown) self::maybe_retry($run_id, $report_id, $attempt, new \WP_Error('cws_clr_mail', 'Mail submission failed.'));
        Runs::purge(); delete_transient($lock);
        return array('run_id' => $run_id, 'status' => $status, 'data' => $data, 'html' => $html);
    }

    private static function maybe_retry(int $run_id, int $report_id, int $attempt, \WP_Error $error): void {
        if ($attempt >= 3 || $error->get_error_code() === 'cws_clr_auth') return;
        $retry_after = (int) (($error->get_error_data()['retry_after'] ?? 0));
        $delay = max($retry_after, 5 * MINUTE_IN_SECONDS * (2 ** ($attempt - 1)));
        wp_schedule_single_event(time() + $delay, RETRY_HOOK, array($run_id, $report_id), true);
    }
}

