<?php
/**
 * Plugin Name: Custom Mobile Resource Image CWS
 * Description: Install both desktop and mobile Resources photos with matching mobile card heights and bottom-only cropping.
 * Version: 1.2.0
 * Author: Creative Way Solutions
 * License: GPL-3.0-or-later
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Text Domain: custom-mobile-resource-image
 */
namespace CWS\MobileResourceImage;
defined('ABSPATH') || exit;
const DIR=__DIR__;
const JOB_OPTION='cwsmri_job_hover_v3';
require DIR.'/includes/core.php';
require DIR.'/includes/records.php';
require DIR.'/includes/queue.php';
require DIR.'/includes/images.php';
require DIR.'/includes/migration.php';
require DIR.'/includes/admin.php';
