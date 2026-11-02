<?php
/**
 * Plugin Name: Custom Forgot Password Boards CWS
 * Description: One-button migration of the legacy boards password-recovery page into editable WordPress blocks.
 * Version: 1.0.0
 * Author: Creative Way Solutions
 * License: GPL-3.0-or-later
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Text Domain: custom-forgot-password-boards
 */
namespace CWS\ForgotPasswordBoards;
defined('ABSPATH') || exit;
const DIR=__DIR__;
const JOB_OPTION='cwsfpb_job_v1';
require DIR.'/includes/core.php';
require DIR.'/includes/records.php';
require DIR.'/includes/queue.php';
require DIR.'/includes/migration.php';
require DIR.'/includes/admin.php';
// Authentication form pages must bypass WordPress-level page caching while this plugin is active.
add_action('template_redirect',function(){if(is_page('password-boards')||is_page_template('forgot-password-boards')){if(!defined('DONOTCACHEPAGE'))define('DONOTCACHEPAGE',true);nocache_headers();}});
