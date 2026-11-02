<?php
/**
 * Plugin Name: Custom Site Repairs CWS
 * Description: One-button repair of portal header links, navigation sizing, Downloads, tissue request pages and Sign Up.
 * Version: 1.1.0
 * Author: Creative Way Solutions
 * License: GPL-3.0-or-later
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Text Domain: custom-site-repairs
 */
namespace CWS\SiteRepairs;
defined('ABSPATH') || exit;
const DIR=__DIR__;
const JOB_OPTION='cwsr_job_v1';
require DIR.'/includes/core.php';
require DIR.'/includes/records.php';
require DIR.'/includes/queue.php';
require DIR.'/includes/repairs.php';
require DIR.'/includes/admin.php';
