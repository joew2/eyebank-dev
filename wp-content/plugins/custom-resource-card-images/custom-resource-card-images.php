<?php
/**
 * Plugin Name: Custom Resource Card Images CWS
 * Description: Import the supplied desktop/mobile family photos, undo the previous framing changes and migrate the homepage Resources card.
 * Version: 1.0.0
 * Author: Creative Way Solutions
 * License: GPL-3.0-or-later
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Text Domain: custom-resource-card-images
 */
namespace CWS\ResourceCardImages;
defined('ABSPATH') || exit;
const DIR=__DIR__;
const JOB_OPTION='cwsrcimg_job_v1';
require DIR.'/includes/core.php';
require DIR.'/includes/records.php';
require DIR.'/includes/queue.php';
require DIR.'/includes/images.php';
require DIR.'/includes/migration.php';
require DIR.'/includes/admin.php';
