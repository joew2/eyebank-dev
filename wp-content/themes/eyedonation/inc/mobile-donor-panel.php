<?php
/** Keep the mobile signup card small and near the lower edge of the hero. */
defined('ABSPATH') || exit;
add_action('wp_enqueue_scripts',function(){
 if(!is_front_page())return;
 wp_enqueue_style('ed-mobile-donor-panel',get_theme_file_uri('/assets/css/mobile-donor-panel.css'),['ed-shared'],filemtime(get_theme_file_path('/assets/css/mobile-donor-panel.css')));
});
