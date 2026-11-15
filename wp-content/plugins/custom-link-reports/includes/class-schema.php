<?php
namespace CWS\CustomLinkReports;

defined('ABSPATH') || exit;

final class Schema {
    public const VERSION = '1.0.0';

    public static function table(): string {
        global $wpdb;
        return $wpdb->prefix . 'cws_clr_runs';
    }

    public static function install(): void {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $table = self::table();
        $charset = $wpdb->get_charset_collate();
        dbDelta("CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            report_id bigint(20) unsigned NOT NULL,
            run_key varchar(191) NOT NULL,
            occurrence_utc bigint(20) unsigned NOT NULL DEFAULT 0,
            period_start date NOT NULL,
            period_end date NOT NULL,
            timezone varchar(64) NOT NULL,
            status varchar(32) NOT NULL,
            attempt tinyint(3) unsigned NOT NULL DEFAULT 1,
            config_snapshot longtext NULL,
            aggregate_snapshot longtext NULL,
            recipient_state longtext NULL,
            error text NULL,
            created_at datetime NOT NULL,
            completed_at datetime NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY run_key (run_key),
            KEY report_id (report_id),
            KEY status (status),
            KEY created_at (created_at)
        ) {$charset};");
        update_option('cws_clr_db_version', self::VERSION, false);
    }
}

