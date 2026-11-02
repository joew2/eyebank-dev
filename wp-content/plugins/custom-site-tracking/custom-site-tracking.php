<?php
/**
 * Plugin Name: Custom Site Tracking CWS
 * Description: Theme-independent Eye-Bank Google Tag Manager, PostHog, and Facebook domain verification.
 * Version: 1.0.0
 * Author: Creative Way Solutions
 * License: GPL-3.0-or-later
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Text Domain: custom-site-tracking
 */
defined('ABSPATH') || exit;
require_once __DIR__ . '/includes.php';
register_activation_hook(__FILE__, function () {
    add_option('cwstr_settings', cwstr_defaults(), '', false);
});
add_action('admin_init', 'cwstr_register_settings');
add_action('admin_menu', function () {
    add_options_page('Site Tracking CWS', 'Site Tracking CWS', 'manage_options', 'cwstr-settings', 'cwstr_settings_page');
});
add_action('wp_head', 'cwstr_head', 1);
add_action('wp_body_open', 'cwstr_body', 1);
// Fallback for older themes without wp_body_open; the renderer outputs only once.
add_action('wp_footer', 'cwstr_body', 1);
// Preserve the stored Genesis settings for rollback, while giving this plugin sole ownership.
add_filter('genesis_header_scripts', 'cwstr_remove_legacy', 1);
add_filter('genesis_footer_scripts', 'cwstr_remove_legacy', 1);
