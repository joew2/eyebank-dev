<?php
/** Editable physician login and request choices, with server-side audience visibility. */
defined('ABSPATH') || exit;
function ed_for_physicians_content(){return file_get_contents(get_theme_file_path('/assets/for-physicians/content.html'));}
// Skip hidden groups before their children (including the login shortcode) are rendered.
add_filter('pre_render_block',function($result,$block){
 if(($block['blockName']??'')!=='core/group')return $result;
 $classes=explode(' ',$block['attrs']['className']??'');
 if(in_array('ed-physicians-guests',$classes,true)&&is_user_logged_in())return '';
 if(in_array('ed-physicians-members',$classes,true)&&!is_user_logged_in())return '';
 return $result;
},10,2);
add_action('template_redirect',function(){
 if(is_page('for-physicians')||is_page_template(['for-physicians','for-physicians-layout'])){
  if(!defined('DONOTCACHEPAGE'))define('DONOTCACHEPAGE',true);
  nocache_headers();
 }
});
add_action('wp_enqueue_scripts',function(){
 if(!is_page('for-physicians')&&!is_page_template(['for-physicians','for-physicians-layout']))return;
 wp_enqueue_style('ed-for-physicians',get_theme_file_uri('/assets/css/for-physicians.css'),['ed-shared'],filemtime(get_theme_file_path('/assets/css/for-physicians.css')));
});
add_action('enqueue_block_editor_assets',function(){wp_enqueue_style('ed-for-physicians-editor',get_theme_file_uri('/assets/css/for-physicians.css'),[],filemtime(get_theme_file_path('/assets/css/for-physicians.css')));});
