<?php
/** Portable donor, board and contact page defaults. Dynamic shortcodes stay editable. */
defined('ABSPATH') || exit;
function ed_information_page_content($slug){
 if(!in_array($slug,['become-a-donor','board-of-directors','contact-us'],true))return '';
 $content=file_get_contents(get_theme_file_path('/assets/information-pages/'.$slug.'.html'));
 return preg_replace_callback('/\{\{asset:([a-zA-Z0-9_.-]+)\}\}/',static function($m){return esc_url(get_theme_file_uri('/assets/information-pages/'.$m[1]));},$content);
}
function ed_become_a_donor_content(){return ed_information_page_content('become-a-donor');}
function ed_board_of_directors_content(){return ed_information_page_content('board-of-directors');}
function ed_contact_us_content(){return ed_information_page_content('contact-us');}
add_action('wp_enqueue_scripts',function(){
 if(!is_page(['become-a-donor','board-of-directors','contact-us'])&&!is_page_template(['become-a-donor','become-a-donor-layout','board-of-directors','board-of-directors-layout','contact-us','contact-us-layout']))return;
 wp_enqueue_style('ed-information-pages',get_theme_file_uri('/assets/css/information-pages.css'),['ed-shared'],filemtime(get_theme_file_path('/assets/css/information-pages.css')));
});
add_action('enqueue_block_editor_assets',function(){wp_enqueue_style('ed-information-pages-editor',get_theme_file_uri('/assets/css/information-pages.css'),[],filemtime(get_theme_file_path('/assets/css/information-pages.css')));});
