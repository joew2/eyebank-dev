<?php
/**
 * Plugin Name: Custom Landing No Header Footer CWS
 * Description: Install the content-only landing page template and assign it to the existing Fall Benefit page.
 * Version: 1.0.0
 * Author: Creative Way Solutions
 * License: GPL-3.0-or-later
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Text Domain: custom-landing-no-header-footer
 */
namespace CWS\LandingNoHeaderFooter;
defined('ABSPATH') || exit;
const DIR=__DIR__;
const JOB_OPTION='cwslnhf_job_v1';
require DIR.'/includes/core.php';
require DIR.'/includes/records.php';
require DIR.'/includes/queue.php';
require DIR.'/includes/migration.php';
require DIR.'/includes/admin.php';
