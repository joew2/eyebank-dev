<?php
/**
 * Plugin Name: Custom Downloads Portal SWS
 * Description: Private, role-restricted Board and Medical Advisory Board document portal.
 * Version: 1.0.8
 * Author: Creative Way Solutions
 * License: GPL-3.0-or-later
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Text Domain: custom-downloads-portal
 */
namespace SWS\DownloadsPortal;

defined('ABSPATH') || exit;

const VERSION = '1.0.8';
const FILE = __FILE__;
const DIR = __DIR__;
const REST_NAMESPACE = 'custom-downloads-portal/v1';

require_once DIR . '/includes/class-access.php';
require_once DIR . '/includes/class-paths.php';
require_once DIR . '/includes/class-audit.php';
require_once DIR . '/includes/class-protection.php';
require_once DIR . '/includes/class-rest.php';
require_once DIR . '/includes/class-download.php';
require_once DIR . '/includes/class-shortcode.php';

add_action('init', array(Shortcode::class, 'register'));
add_action('rest_api_init', array(Rest::class, 'register'));
add_action('admin_post_cdp_download', array(Download::class, 'handle'));
add_action('admin_post_cdp_verify_protection', array(Protection::class, 'handle_verification'));
add_action('admin_menu', array(Protection::class, 'admin_menu'));
add_action('admin_notices', array(Protection::class, 'admin_notice'));
add_filter('rest_post_dispatch', static function ($response, $server, $request) {
    if (str_starts_with($request->get_route(), '/' . REST_NAMESPACE . '/')) {
        $response->header('Cache-Control', 'private, no-store, max-age=0');
        $response->header('X-Content-Type-Options', 'nosniff');
    }
    return $response;
}, 10, 3);

register_activation_hook(FILE, array(Protection::class, 'activate'));

