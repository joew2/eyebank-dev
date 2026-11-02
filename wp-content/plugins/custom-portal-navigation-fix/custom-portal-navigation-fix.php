<?php
/**
 * Plugin Name: Custom Portal Navigation Fix CWS
 * Description: Move Transplant Portal and Research Portal from the utility header to the active blue-bar navigation.
 * Version: 1.0.0
 * Author: Creative Way Solutions
 * License: GPL-3.0-or-later
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Text Domain: custom-portal-navigation-fix
 */
namespace CWS\PortalNavigationFix;
defined('ABSPATH') || exit;
const DIR=__DIR__;
const JOB_OPTION='cwspn_job_v1';
require DIR.'/includes/core.php';
require DIR.'/includes/records.php';
require DIR.'/includes/queue.php';
require DIR.'/includes/repair.php';
require DIR.'/includes/admin.php';
