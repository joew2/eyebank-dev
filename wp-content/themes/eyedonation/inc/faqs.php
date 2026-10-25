<?php
/** Portable editable FAQ page with native Details blocks. */
defined('ABSPATH') || exit;
function ed_faqs_content(){
 $content=file_get_contents(get_theme_file_path('/assets/faqs/content.html'));
 return str_replace('{{asset:faq-header.jpg}}',esc_url(get_theme_file_uri('/assets/faqs/faq-header.jpg')),$content);
}
add_action('wp_enqueue_scripts',function(){
 if(!is_page('faqs')&&!is_page_template(['faqs','faqs-layout']))return;
 wp_enqueue_style('ed-faqs',get_theme_file_uri('/assets/css/faqs.css'),['ed-shared'],filemtime(get_theme_file_path('/assets/css/faqs.css')));
 wp_enqueue_script('ed-faqs',get_theme_file_uri('/assets/js/faqs.js'),[],filemtime(get_theme_file_path('/assets/js/faqs.js')),true);
});
add_action('enqueue_block_editor_assets',function(){wp_enqueue_style('ed-faqs-editor',get_theme_file_uri('/assets/css/faqs.css'),[],filemtime(get_theme_file_path('/assets/css/faqs.css')));});
