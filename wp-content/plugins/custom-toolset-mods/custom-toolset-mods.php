<?php
/**
 * Plugin Name: Custom Toolset Mods CWS
 * Description: Site-specific Toolset customizations, including last-name sorting for selected user Views.
 * Version: 1.0.0
 * Author: Creative Way Solutions
 * License: GPL-3.0-or-later
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Text Domain: custom-toolset-mods
 */
namespace CWS\ToolsetMods;
defined('ABSPATH') || exit;

require_once __DIR__ . '/includes/user-query-sorting.php';
