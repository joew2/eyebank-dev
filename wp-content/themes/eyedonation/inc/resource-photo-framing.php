<?php
/** Responsive framing for the recipients/families Resources photograph. */
defined('ABSPATH') || exit;
add_action('wp_enqueue_scripts',function(){
 if(!is_front_page())return;
 wp_enqueue_style('ed-resource-photo-framing',get_theme_file_uri('/assets/css/resource-photo-framing.css'),['ed-shared'],filemtime(get_theme_file_path('/assets/css/resource-photo-framing.css')));
});
