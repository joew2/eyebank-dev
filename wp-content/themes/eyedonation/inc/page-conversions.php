<?php
/** Shared rendering for the selected editable information, scholarship and account pages. */
defined('ABSPATH') || exit;
function ed_converted_page_definitions(){
 static $pages;
 if($pages===null)$pages=json_decode(file_get_contents(get_theme_file_path('/assets/page-conversions/pages.json')),true);
 return $pages;
}
function ed_converted_page_content($slug){
 if(!in_array($slug,array_column(ed_converted_page_definitions(),'slug'),true))return '';
 $content=file_get_contents(get_theme_file_path('/assets/page-conversions/'.$slug.'.html'));
 $content=preg_replace_callback('/\{\{asset:([a-zA-Z0-9_.-]+)\}\}/',static fn($m)=>esc_url(get_theme_file_uri('/assets/page-conversions/'.$m[1])),$content);
 return preg_replace_callback('/\{\{page:([a-z0-9-]+)\}\}/',static fn($m)=>esc_url(home_url('/'.$m[1].'/')),$content);
}
function ed_is_converted_page(){
 $slugs=array_column(ed_converted_page_definitions(),'slug');
 return is_page($slugs)||is_page_template(array_merge($slugs,array_map(static fn($s)=>$s.'-layout',$slugs)));
}
// Hide complete audience groups before rendering their form shortcodes.
add_filter('pre_render_block',function($rendered,$block){
 if(($block['blockName']??'')!=='core/group')return $rendered;
 $classes=explode(' ',$block['attrs']['className']??'');
 if(in_array('ed-converted-members',$classes,true)&&!is_user_logged_in())return '';
 if(in_array('ed-converted-guests',$classes,true)&&is_user_logged_in())return '';
 return $rendered;
},10,2);
add_action('template_redirect',function(){
 $account=['registration-tissue-request','member-profile','enter-new-password','user-downloads'];
 if(is_page($account)||is_page_template(array_merge($account,array_map(static fn($s)=>$s.'-layout',$account)))){
  if(!defined('DONOTCACHEPAGE'))define('DONOTCACHEPAGE',true);
  nocache_headers();
 }
});
// Keep the live winner Views, while serving their existing portraits from the theme.
add_filter('do_shortcode_tag',function($html,$tag,$attributes){
 if($tag!=='wpv-view'||!in_array($attributes['name']??'',['scholarship-winners','stem-scholarship-winners'],true))return $html;
 static $media;
 if($media===null)$media=json_decode(file_get_contents(get_theme_file_path('/assets/page-conversions/media.json')),true);
 $tags=new WP_HTML_Tag_Processor($html);
 while($tags->next_tag('IMG')){
  $src=$tags->get_attribute('src');if(!is_string($src))continue;
  $path=wp_parse_url(html_entity_decode($src),PHP_URL_PATH);
  if(!$path||!str_contains($path,'/uploads/'))continue;
  $relative=rawurldecode(explode('/uploads/',$path,2)[1]);
  if(!isset($media[$relative]))continue;
  $tags->set_attribute('src',get_theme_file_uri('/assets/page-conversions/'.$media[$relative]));
  $tags->remove_attribute('srcset');$tags->remove_attribute('sizes');
 }
 return $tags->get_updated_html();
},10,3);
add_action('wp_enqueue_scripts',function(){
 if(!ed_is_converted_page())return;
 wp_enqueue_style('ed-page-conversions',get_theme_file_uri('/assets/css/page-conversions.css'),['ed-shared'],filemtime(get_theme_file_path('/assets/css/page-conversions.css')));
});
add_action('enqueue_block_editor_assets',function(){wp_enqueue_style('ed-page-conversions-editor',get_theme_file_uri('/assets/css/page-conversions.css'),[],filemtime(get_theme_file_path('/assets/css/page-conversions.css')));});
