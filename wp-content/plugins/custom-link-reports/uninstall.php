<?php
defined('WP_UNINSTALL_PLUGIN') || exit;
$settings = get_option('cws_clr_settings', array());
if (empty($settings['delete_on_uninstall'])) return;
global $wpdb;
$ids = get_posts(array('post_type' => 'cws_clr_report', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids'));
foreach ($ids as $id) wp_delete_post($id, true);
$wpdb->query('DROP TABLE IF EXISTS `' . str_replace('`', '', $wpdb->prefix . 'cws_clr_runs') . '`');
delete_option('cws_clr_settings'); delete_option('cws_clr_db_version');

