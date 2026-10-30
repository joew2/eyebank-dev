<?php
/** Portable Medical Advisory Board defaults; Toolset views remain dynamic. */
defined('ABSPATH') || exit;
function ed_medical_advisory_board_content(){
 $content=file_get_contents(get_theme_file_path('/assets/medical-advisory-board/content.html'));
 return str_replace('{{asset:header-medical-advisory-board.jpg}}',esc_url(get_theme_file_uri('/assets/medical-advisory-board/header-medical-advisory-board.jpg')),$content);
}
add_action('wp_enqueue_scripts',function(){
 if(!is_page('medical-advisory-board')&&!is_page_template(['medical-advisory-board','medical-advisory-board-layout']))return;
 wp_enqueue_style('ed-information-pages',get_theme_file_uri('/assets/css/information-pages.css'),['ed-shared'],filemtime(get_theme_file_path('/assets/css/information-pages.css')));
});
