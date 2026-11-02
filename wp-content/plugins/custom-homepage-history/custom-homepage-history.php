<?php
/**
 * Plugin Name: Custom Homepage History CWS
 * Description: Update the homepage history photo and move About Us below the narrower text panel.
 * Version: 1.4.0
 * Author: Creative Way Solutions
 * License: GPL-3.0-or-later
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Text Domain: custom-homepage-history
 */
namespace CWS\HomepageHistory;
defined('ABSPATH') || exit;
const DIR=__DIR__;
const JOB_OPTION='cwshh_job_v5';
require DIR.'/includes/core.php';
require DIR.'/includes/records.php';
require DIR.'/includes/queue.php';
require DIR.'/includes/images.php';
require DIR.'/includes/migration.php';
require DIR.'/includes/admin.php';
