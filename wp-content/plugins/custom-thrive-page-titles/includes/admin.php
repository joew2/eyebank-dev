<?php
namespace CWS\ThrivePageTitles;

defined('ABSPATH') || exit;

add_action('admin_menu', static function (): void {
    add_management_page('Thrive Page Titles CWS', 'Thrive Page Titles CWS', 'manage_options', 'cws-thrive-page-titles', __NAMESPACE__ . '\\screen');
});

function screen(): void {
    if (!current_user_can('manage_options')) return;
    $notice = '';
    $error = '';
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['cws_tpt_process'])) {
        check_admin_referer('cws_tpt_process');
        if (($_POST['cws_tpt_agree'] ?? '') !== '1') {
            $error = 'Check the backup agreement before processing.';
        } else {
            try { $notice = process_migration(); }
            catch (\Throwable $exception) { $error = $exception->getMessage(); }
        }
    }
    echo '<div class="wrap"><h1>Restore Thrive landing-page titles</h1>';
    echo '<p>This installs the EyeDonation theme compatibility module that restores browser and SEO document titles when Thrive bypasses the block-template canvas. It does not add a visible heading, alter Thrive content, or modify either Spanish page.</p>';
    if ($notice) echo '<div class="notice notice-success"><p>' . esc_html($notice) . '</p></div>';
    if ($error) echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>';
    echo '<form method="post">';
    wp_nonce_field('cws_tpt_process');
    echo '<p><label><input type="checkbox" name="cws_tpt_agree" value="1"> I have a recoverable file and database backup and agree to install the title-support module.</label></p>';
    echo '<p><button type="submit" name="cws_tpt_process" value="1" class="button button-primary button-hero">Process all updates</button></p>';
    echo '</form><p><strong>After processing:</strong> clear WordPress, Nginx/Engintron, and CDN caches, then check the browser tab and page source for one <code>&lt;title&gt;</code> element.</p></div>';
}

