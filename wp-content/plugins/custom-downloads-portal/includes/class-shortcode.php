<?php
namespace SWS\DownloadsPortal;

defined('ABSPATH') || exit;

final class Shortcode {
    private static bool $assets_enqueued = false;

    public static function register(): void {
        add_shortcode('custom_downloads_portal', array(self::class, 'render'));
    }

    public static function render(): string {
        if (!defined('DONOTCACHEPAGE')) define('DONOTCACHEPAGE', true);
        nocache_headers();
        if (!is_user_logged_in()) {
            $return = wp_validate_redirect((string) (wp_unslash($_SERVER['REQUEST_URI'] ?? '/')), '/');
            return '<div class="cdp-message"><p>' . esc_html__('Please sign in to access member downloads.', 'custom-downloads-portal') . '</p><p><a class="cdp-button" href="' . esc_url(wp_login_url(home_url($return))) . '">' . esc_html__('Sign in', 'custom-downloads-portal') . '</a></p></div>';
        }
        if (!Access::permissions()) {
            return '<div class="cdp-message" role="alert"><p>' . esc_html__('You do not have access to the downloads portal.', 'custom-downloads-portal') . '</p></div>';
        }
        self::enqueue_assets();
        return '<div class="cdp-portal" data-cdp-portal><div class="cdp-status" role="status" aria-live="polite">' . esc_html__('Loading document libraries…', 'custom-downloads-portal') . '</div></div>';
    }

    private static function enqueue_assets(): void {
        if (self::$assets_enqueued) return;
        self::$assets_enqueued = true;
        wp_enqueue_style('custom-downloads-portal', plugins_url('assets/portal.css', FILE), array(), VERSION);
        wp_enqueue_script('custom-downloads-portal', plugins_url('assets/portal.js', FILE), array(), VERSION, true);
        wp_localize_script('custom-downloads-portal', 'CDP_CONFIG', array(
            'restUrl' => esc_url_raw(rest_url(REST_NAMESPACE)), 'nonce' => wp_create_nonce('wp_rest'),
            'maxUpload' => 52428800,
            'strings' => array(
                'loadError' => __('The folder could not be loaded.', 'custom-downloads-portal'),
                'sessionExpired' => __('Your session expired. Please sign in again.', 'custom-downloads-portal'),
                'empty' => __('This folder is empty.', 'custom-downloads-portal'),
                'deleteFolder' => __('Are you sure you want to delete this folder? All files and subfolders inside it will also be permanently deleted. This cannot be undone.', 'custom-downloads-portal'),
                'deleteMultiple' => __('Permanently delete the selected items? Selected folders and everything inside them will be removed. This cannot be undone.', 'custom-downloads-portal'),
                'deleteFiles' => __('Permanently delete the selected files? This cannot be undone.', 'custom-downloads-portal'),
            ),
        ));
    }
}

