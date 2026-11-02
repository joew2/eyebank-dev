<?php
/** Keep migration panels inside the admin content column without changing frozen migration payloads. */
namespace EyeDonation\SiteMigrations;
defined('ABSPATH') || exit;
add_action('admin_menu', function () {
    $hook = get_plugin_page_hookname('eyedonation-migrations', 'tools.php');
    global $wp_filter;
    // Migration 007 replaces the original page callback at priority 99; preserve its panel too.
    foreach ($wp_filter[$hook]->callbacks ?? [] as $priority => $callbacks) {
        foreach ($callbacks as $entry) {
            $callback = $entry['function'];
            if (!($callback instanceof \Closure) && !is_string($callback)) continue;
            $file = (new \ReflectionFunction($callback))->getFileName();
            if (!$file || strpos($file, DIR . '/') !== 0) continue;
            remove_action($hook, $callback, $priority);
            add_action('edm_admin_primary', $callback, $priority, $entry['accepted_args']);
        }
    }
    add_action($hook, __NAMESPACE__ . '\\contained_admin_screen');
    // Historical installers registered their panels on admin_footer, which is outside #wpcontent.
    // Move only callbacks belonging to this plugin; leave other plugins' footer callbacks alone.
    global $wp_filter;
    if (empty($wp_filter['admin_footer'])) return;
    foreach ($wp_filter['admin_footer']->callbacks as $priority => $callbacks) {
        foreach ($callbacks as $entry) {
            $callback = $entry['function'];
            if (!$callback instanceof \Closure) continue;
            $file = (new \ReflectionFunction($callback))->getFileName();
            if (!$file || strpos($file, DIR . '/migrations/') !== 0) continue;
            remove_action('admin_footer', $callback, $priority);
            add_action('edm_admin_sections', $callback, $priority, $entry['accepted_args']);
        }
    }
}, 100);
function contained_admin_screen() {
    if (!current_user_can('manage_options')) return;
    echo '<div class="edm-admin-screen">';
    echo '<div class="notice notice-info inline"><p><strong>Page installation is manual.</strong> Previewing does not replace page content. The first six rows and their media preflight belong to the original deployment workflow; later page installers have their own Preview buttons below. Do not run every historical migration in sequence.</p><p>A missing <code>support_photo</code> in the original media preflight concerns historical homepage installer 003. The newer editable homepage installer 014 and bundled-media updates use their own checks. Navigation dependency incomplete means the referenced earlier menu step has not been run, not that a plugin is missing.</p></div>';
    do_action('edm_admin_primary', '');
    do_action('edm_admin_sections', '');
    echo '</div>';
}
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'tools_page_eyedonation-migrations') return;
    wp_enqueue_style('edm-admin-layout', plugins_url('assets/admin-layout.css', __FILE__), [], filemtime(__DIR__ . '/assets/admin-layout.css'));
});
