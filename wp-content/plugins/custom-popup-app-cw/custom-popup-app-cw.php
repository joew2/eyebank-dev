<?php
/**
 * Plugin Name: Custom Popup App CW
 * Description: Accessible image pop-ups with homepage/page targeting and per-browser display frequency.
 * Version: 1.0.3
 * Requires at least: 6.6
 * Requires PHP: 7.4
 * Text Domain: custom-popup-app-cw
 */
namespace CW\CustomPopUpApp;
defined( 'ABSPATH' ) || exit;
define( 'CW_POPUP_FILE', __FILE__ );
require_once __DIR__ . '/includes/storage.php';
require_once __DIR__ . '/includes/frontend.php';
require_once __DIR__ . '/includes/admin.php';
add_action( 'init', __NAMESPACE__ . '\\cw_popup_register' );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\cw_popup_frontend' );
add_action( 'admin_menu', __NAMESPACE__ . '\\cw_popup_menu' );
add_filter( 'custom_menu_order', '__return_true' );
add_filter( 'menu_order', __NAMESPACE__ . '\\cw_popup_menu_order', 1001 );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\cw_popup_admin_assets' );
add_action( 'load-post.php', __NAMESPACE__ . '\\cw_popup_redirect_editor' );
add_action( 'load-post-new.php', __NAMESPACE__ . '\\cw_popup_redirect_editor' );
add_action( 'admin_post_cw_popup_save', __NAMESPACE__ . '\\cw_popup_save' );
add_action( 'wp_ajax_cw_popup_search', __NAMESPACE__ . '\\cw_popup_search' );
add_action( 'wp_ajax_cw_popup_preview', __NAMESPACE__ . '\\cw_popup_preview' );
add_filter( 'get_edit_post_link', __NAMESPACE__ . '\\cw_popup_edit_link', 10, 3 );
add_filter( 'post_row_actions', __NAMESPACE__ . '\\cw_popup_row_actions', 10, 2 );
add_filter( 'bulk_actions-edit-cw_popup', __NAMESPACE__ . '\\cw_popup_bulk_actions' );
add_filter( 'manage_cw_popup_posts_columns', __NAMESPACE__ . '\\cw_popup_columns' );
add_action( 'manage_cw_popup_posts_custom_column', __NAMESPACE__ . '\\cw_popup_column', 10, 2 );
add_filter( 'wp_insert_post_data', __NAMESPACE__ . '\\cw_popup_guard_status', 10, 2 );
