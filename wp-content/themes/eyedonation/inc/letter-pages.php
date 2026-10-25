<?php
/** Portable editable correspondence guidance, including legacy custom-field content. */
defined('ABSPATH') || exit;
function ed_letter_page_content($slug){
 if(!in_array($slug,['cornea-recipients','donor-families'],true))return '';
 $content=file_get_contents(get_theme_file_path('/assets/letter-pages/'.$slug.'.html'));
 return preg_replace_callback('/\{\{asset:([a-zA-Z0-9_.-]+)\}\}/',static function($m){return esc_url(get_theme_file_uri('/assets/letter-pages/'.$m[1]));},$content);
}
function ed_cornea_recipients_content(){return ed_letter_page_content('cornea-recipients');}
function ed_donor_families_content(){return ed_letter_page_content('donor-families');}
add_action('wp_enqueue_scripts',function(){
 if(!is_page(['cornea-recipients','donor-families'])&&!is_page_template(['cornea-recipients','donor-families','cornea-recipients-layout','donor-families-layout']))return;
 wp_enqueue_style('ed-letter-pages',get_theme_file_uri('/assets/css/letter-pages.css'),['ed-shared'],filemtime(get_theme_file_path('/assets/css/letter-pages.css')));
});
add_action('enqueue_block_editor_assets',function(){wp_enqueue_style('ed-letter-pages-editor',get_theme_file_uri('/assets/css/letter-pages.css'),[],filemtime(get_theme_file_path('/assets/css/letter-pages.css')));});
