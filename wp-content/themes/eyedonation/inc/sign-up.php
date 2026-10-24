<?php
/** Editable mailing-list page defaults and scoped form presentation. */
defined('ABSPATH') || exit;
function ed_sign_up_content(){return file_get_contents(get_theme_file_path('/assets/sign-up/content.html'));}
add_action('wp_enqueue_scripts',function(){
 if(!is_page('sign-up'))return;
 wp_enqueue_style('ed-sign-up',get_theme_file_uri('/assets/css/sign-up.css'),['ed-shared'],filemtime(get_theme_file_path('/assets/css/sign-up.css')));
 wp_enqueue_script('ed-sign-up',get_theme_file_uri('/assets/js/sign-up.js'),[],filemtime(get_theme_file_path('/assets/js/sign-up.js')),true);
});
add_action('enqueue_block_editor_assets',function(){wp_enqueue_style('ed-sign-up-editor',get_theme_file_uri('/assets/css/sign-up.css'),[],filemtime(get_theme_file_path('/assets/css/sign-up.css')));});
add_filter('render_block_core/button',function($html,$block){
 if(!is_page('about-donation')||!in_array('ed-newsletter-join',explode(' ',$block['attrs']['className']??''),true))return $html;
 return preg_replace('/href="[^"]*"/','href="'.esc_url(home_url('/sign-up/')).'"',$html,1);
},10,2);
