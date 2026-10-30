<?php
/**
 * Plugin Name: Custom Accessibility Translation CWS
 * Description: A single footer or screen-fixed accessibility toolbar and English/Spanish page switcher. Admin label: ATP.
 * Version: 1.4.1
 * Author: Creative Way Solutions
 * License: GPL-3.0-or-later
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Text Domain: custom-accessibility-translation
 */
defined('ABSPATH') || exit;
require_once __DIR__ . '/includes/mappings.php';
require_once __DIR__ . '/includes/widget.php';
register_activation_hook(__FILE__, function () {
    cwsatp_activate_plugin();
    cwsatp_import_legacy_mappings();
    add_option('cwsatp_settings', cwsatp_defaults());
});
