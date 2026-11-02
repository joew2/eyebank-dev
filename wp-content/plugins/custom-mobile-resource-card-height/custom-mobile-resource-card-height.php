<?php
/**
 * Plugin Name: Custom Mobile Resource Card Height CWS
 * Description: Match the mobile recipients/families card height to the other Resources cards, cropping mostly from the bottom.
 * Version: 1.1.0
 * Author: Creative Way Solutions
 * License: GPL-3.0-or-later
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Text Domain: custom-mobile-resource-card-height
 */
namespace CWS\MobileResourceCardHeight;
defined('ABSPATH') || exit;
const DIR=__DIR__;
const JOB_OPTION='cwsmrch_job_v1';
require DIR.'/includes/core.php';
require DIR.'/includes/queue.php';
require DIR.'/includes/migration.php';
require DIR.'/includes/admin.php';
