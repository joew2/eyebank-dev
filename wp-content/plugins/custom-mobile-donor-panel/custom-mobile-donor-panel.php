<?php
/**
 * Plugin Name: Custom Mobile Donor Panel CWS
 * Description: One-button migration that proportionally reduces the mobile donor panel and places it near the hero's bottom edge.
 * Version: 1.0.0
 * Author: Creative Way Solutions
 * License: GPL-3.0-or-later
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Text Domain: custom-mobile-donor-panel
 */
namespace CWS\MobileDonorPanel;
defined('ABSPATH') || exit;
const DIR=__DIR__;
const JOB_OPTION='cwsmdp_job_v1';
require DIR.'/includes/core.php';
require DIR.'/includes/queue.php';
require DIR.'/includes/migration.php';
require DIR.'/includes/admin.php';
