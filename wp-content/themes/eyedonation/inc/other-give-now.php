<?php
/** Other Ways to Give reuses the native Details accordion enhancement from FAQs. */
defined('ABSPATH') || exit;
add_action('wp_enqueue_scripts',function(){
 if(!is_page('other-give-now')&&!is_page_template(['other-give-now','other-give-now-layout']))return;
 wp_enqueue_style('ed-faqs',get_theme_file_uri('/assets/css/faqs.css'),['ed-shared'],filemtime(get_theme_file_path('/assets/css/faqs.css')));
 wp_enqueue_style('ed-other-give-now',get_theme_file_uri('/assets/css/other-give-now.css'),['ed-page-conversions','ed-faqs'],filemtime(get_theme_file_path('/assets/css/other-give-now.css')));
 wp_enqueue_script('ed-faqs',get_theme_file_uri('/assets/js/faqs.js'),[],filemtime(get_theme_file_path('/assets/js/faqs.js')),true);
});
add_action('enqueue_block_editor_assets',function(){
 foreach(['faqs','other-give-now'] as $file)wp_enqueue_style('ed-'.$file.'-editor',get_theme_file_uri('/assets/css/'.$file.'.css'),[],filemtime(get_theme_file_path('/assets/css/'.$file.'.css')));
});
