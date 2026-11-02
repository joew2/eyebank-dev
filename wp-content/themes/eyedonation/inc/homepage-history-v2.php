<?php
defined('ABSPATH') || exit;
add_action('wp_enqueue_scripts',function(){if(is_front_page())wp_enqueue_style('ed-homepage-history-v2',get_theme_file_uri('/assets/css/homepage-history-v2.css'),['ed-shared'],filemtime(get_theme_file_path('/assets/css/homepage-history-v2.css')));});
