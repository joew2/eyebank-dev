<?php
/**
 * Plugin Name: Custom Resource Photo Framing CWS
 * Description: One-button responsive framing adjustment for the homepage Transplant Recipients & Donor Families resource photograph.
 * Version: 1.0.0
 * Author: Creative Way Solutions
 * License: GPL-3.0-or-later
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Text Domain: custom-resource-photo-framing
 */
namespace CWS\ResourcePhotoFraming;
defined('ABSPATH') || exit;
const DIR=__DIR__;
const JOB_OPTION='cwsrpf_job_v1';
require DIR.'/includes/core.php';
require DIR.'/includes/queue.php';
require DIR.'/includes/migration.php';
require DIR.'/includes/admin.php';
