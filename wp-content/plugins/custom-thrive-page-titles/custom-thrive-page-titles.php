<?php
/**
 * Plugin Name: Custom Thrive Page Titles CWS
 * Description: One-button migration that restores browser/SEO titles on Thrive landing pages used with the EyeDonation block theme.
 * Version: 1.0.0
 * Author: Creative Way Solutions
 * License: GPL-3.0-or-later
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Text Domain: custom-thrive-page-titles
 */
namespace CWS\ThrivePageTitles;

defined('ABSPATH') || exit;

const DIR = __DIR__;
const HISTORY_OPTION = 'cws_tpt_history_v1';

require DIR . '/includes/core.php';
require DIR . '/includes/migration.php';
require DIR . '/includes/admin.php';

