<?php
defined('ABSPATH') || exit;
add_action('wp_enqueue_scripts', function(){
 foreach(['base','search'] as $file) wp_enqueue_style('ed-'.$file,get_theme_file_uri('/assets/css/'.$file.'.css'),[],filemtime(get_theme_file_path('/assets/css/'.$file.'.css')));
 wp_enqueue_script('ed-interface',get_theme_file_uri('/assets/js/interface.js'),[],filemtime(get_theme_file_path('/assets/js/interface.js')),true);
 wp_localize_script('ed-interface','edSearchLabels',apply_filters('ed_search_labels',['title'=>__('Search the Eye-Bank','eyedonation'),'description'=>__('Find public news, donor resources and professional information.','eyedonation'),'open'=>__('Search','eyedonation'),'close'=>__('Close search','eyedonation')]));
});
