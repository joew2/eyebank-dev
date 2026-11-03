<?php
/**
 * Plugin Name: Custom Thrive Compatibility Fix CWS — REQUIRED
 * Description: DO NOT DEACTIVATE OR DELETE while Thrive landing pages are in use. Prevents Thrive from removing accessibility, plugin and WordPress CSS when the domain contains the theme name. Keep active until the Thrive pages have been replaced.
 * Version: 1.0.0
 * Author: Creative Way Solutions
 * License: GPL-3.0-or-later
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Text Domain: custom-thrive-compatibility-fix
 */
namespace CWS\ThriveCompatibilityFix;
defined('ABSPATH') || exit;

require_once __DIR__ . '/includes/theme-css.php';
