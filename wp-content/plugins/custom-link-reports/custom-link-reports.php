<?php
/**
 * Plugin Name: Custom Link Reports CWS
 * Description: Admin-only Short.io analytics reports with previews, email delivery, and weekly scheduling.
 * Version: 1.3.0
 * Author: Creative Way Solutions
 * License: GPL-3.0-or-later
 * Requires at least: 6.2
 * Requires PHP: 8.1
 * Text Domain: custom-link-reports
 */
namespace CWS\CustomLinkReports;

defined('ABSPATH') || exit;

const VERSION = '1.3.0';
const FILE = __FILE__;
const DIR = __DIR__;
const SETTINGS_OPTION = 'cws_clr_settings';
const REPORT_TYPE = 'cws_clr_report';
const CRON_HOOK = 'cws_clr_run_report';
const RETRY_HOOK = 'cws_clr_retry_report';

foreach (array(
    'class-crypto.php', 'class-settings.php', 'class-schema.php', 'class-reports.php',
    'class-date-windows.php', 'class-api.php', 'class-renderer.php', 'class-mailer.php',
    'class-runs.php', 'class-scheduler.php', 'class-runner.php', 'class-admin.php',
) as $file) {
    require_once DIR . '/includes/' . $file;
}

add_action('init', array(Reports::class, 'register_type'));
add_action('admin_menu', array(Admin::class, 'menu'));
add_filter('custom_menu_order', '__return_true');
add_filter('menu_order', array(Admin::class, 'menu_order'));
add_action('admin_enqueue_scripts', array(Admin::class, 'assets'));
add_action('admin_init', array(Admin::class, 'handle_actions'));
add_action(CRON_HOOK, array(Runner::class, 'scheduled'), 10, 3);
add_action(RETRY_HOOK, array(Runner::class, 'retry'), 10, 2);

register_activation_hook(FILE, static function (bool $network_wide): void {
    if (is_multisite() && $network_wide) {
        deactivate_plugins(plugin_basename(FILE));
        wp_die(esc_html__('Custom Link Reports CWS must be activated separately on each site.', 'custom-link-reports'));
    }
    Schema::install();
    Settings::ensure();
    Reports::register_type();
});

register_deactivation_hook(FILE, static function (): void {
    Scheduler::clear_all();
});

