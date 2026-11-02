<?php
/** Presentation for the supplied desktop/mobile recipients/families crops. */
defined('ABSPATH') || exit;
add_action('wp_enqueue_scripts',function(){
 if(!is_front_page())return;
 wp_enqueue_style('ed-resource-card-images',get_theme_file_uri('/assets/css/resource-card-images.css'),['ed-shared'],filemtime(get_theme_file_path('/assets/css/resource-card-images.css')));
});
