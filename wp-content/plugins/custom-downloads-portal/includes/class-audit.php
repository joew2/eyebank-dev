<?php
namespace SWS\DownloadsPortal;

defined('ABSPATH') || exit;

final class Audit {
    private const OPTION = 'cdp_audit_log';
    private const RETENTION = 7776000;
    private const MAX_ENTRIES = 500;

    public static function record(string $action, string $library, string $target, string $outcome, string $detail = ''): void {
        $entries = get_option(self::OPTION, array());
        $cutoff = time() - self::RETENTION;
        $entries = array_values(array_filter(is_array($entries) ? $entries : array(), static fn($row) => (int) ($row['time'] ?? 0) >= $cutoff));
        $entries[] = array(
            'time' => time(), 'actor' => get_current_user_id(), 'action' => $action,
            'library' => $library, 'target' => mb_substr($target, 0, 500),
            'outcome' => $outcome, 'detail' => mb_substr($detail, 0, 300),
        );
        $entries = array_slice($entries, -self::MAX_ENTRIES);
        update_option(self::OPTION, $entries, false);
    }
}

