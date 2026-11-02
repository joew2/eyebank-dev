<?php
/** Match the mobile recipients/families card to its Resources neighbors. */
defined('ABSPATH') || exit;
add_action('wp_enqueue_scripts',function(){
 if(!is_front_page())return;
 wp_enqueue_style('ed-mobile-resource-card-height',get_theme_file_uri('/assets/css/mobile-resource-card-height.css'),['ed-resource-card-images'],filemtime(get_theme_file_path('/assets/css/mobile-resource-card-height.css')));
});
