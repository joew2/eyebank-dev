<?php
/**
 * Plugin Name: Custom Homepage Restore CWS
 * Description: Restore only the current EyeDonation homepage design as editable blocks, independently of earlier migrations.
 * Version: 1.0.0
 * Author: Creative Way Solutions
 * License: GPL-3.0-or-later
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Text Domain: custom-homepage-restore
 */
namespace CWS\HomepageRestore;
defined('ABSPATH') || exit;
const DIR=__DIR__;
const JOB_OPTION='cwshr_job_v1';
require DIR.'/includes/core.php';
require DIR.'/includes/records.php';
require DIR.'/includes/queue.php';
require DIR.'/includes/restore.php';
require DIR.'/includes/admin.php';
