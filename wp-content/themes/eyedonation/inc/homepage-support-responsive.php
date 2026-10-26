<?php
/** Cropped desktop/mobile support artwork; retain native image editing and the existing panel. */
defined('ABSPATH') || exit;
function ed_update_homepage_support_responsive($content,$strict=false){
 $blocks=parse_blocks(ed_update_homepage_support_photo($content,$strict));$found=0;
 $walk=function(&$items)use(&$walk,&$found){foreach($items as &$block){
  if(($block['blockName']??'')==='core/image'&&in_array('ed-support-photo',explode(' ',$block['attrs']['className']??''),true)){
   $found++;$url=get_theme_file_uri('/assets/homepage-support-v3/support-desktop.jpg');
   $classes=array_filter(explode(' ',$block['attrs']['className']??''));$classes[]='ed-support-photo-responsive';$block['attrs']['className']=implode(' ',array_unique($classes));unset($block['attrs']['id']);
   $patch=static function($html)use($url){$tags=new WP_HTML_Tag_Processor($html);if($tags->next_tag('figure'))$tags->add_class('ed-support-photo-responsive');if($tags->next_tag('img')){$tags->set_attribute('src',$url);$tags->remove_attribute('srcset');$tags->remove_attribute('sizes');}return $tags->get_updated_html();};
   $block['innerHTML']=$patch($block['innerHTML']);foreach($block['innerContent'] as &$part)if(is_string($part))$part=$patch($part);unset($part);
  }
  if(!empty($block['innerBlocks']))$walk($block['innerBlocks']);
 }};$walk($blocks);if($strict&&$found!==1)throw new RuntimeException('Expected one homepage support photograph.');return serialize_blocks($blocks);
}
add_filter('render_block_core/image',function($html,$block){
 if(!in_array('ed-support-photo-responsive',explode(' ',$block['attrs']['className']??''),true))return $html;
 $tags=new WP_HTML_Tag_Processor($html);if(!$tags->next_tag('img'))return $html;
 $src=preg_replace('~^https?:~','',(string)$tags->get_attribute('src'));
 $expected=preg_replace('~^https?:~','',get_theme_file_uri('/assets/homepage-support-v3/support-desktop.jpg'));
 if($src!==$expected)return $html; // Preserve independent replacement images chosen in the editor.
 $source='<source media="(max-width: 781px)" srcset="'.esc_url(get_theme_file_uri('/assets/homepage-support-v3/support-mobile.jpg')).'" width="900" height="634">';
 return preg_replace_callback('~<img\b[^>]*>~',static function($match)use($source){return '<picture>'.$source.$match[0].'</picture>';},$html,1);
},20,2);
