<?php
defined('ABSPATH') || exit;
/** Scope the approved replacement to the two native About hero image blocks. */
function ed_update_about_hero($content, $strict=false) {
 $manifest=json_decode(file_get_contents(get_theme_file_path('/assets/about-hero-v2/manifest.json')),true);
 $uploads=wp_upload_dir();$media=[];
 foreach($manifest as $view=>$asset){$path=$uploads['basedir'].'/'.$asset['upload_path'];$media[$view]=is_file($path)&&hash_file('sha256',$path)===$asset['sha256']?trailingslashit($uploads['baseurl']).$asset['upload_path']:get_theme_file_uri('/assets/about-hero-v2/'.$asset['file']);}
 $blocks=parse_blocks($content);$found=['desktop'=>0,'mobile'=>0];
 $walk=function(&$items)use(&$walk,&$found,$media){foreach($items as &$block){foreach($media as $view=>$url){if(($block['blockName']??'')!=='core/image'||!in_array('ed-about-hero-'.$view,preg_split('/\s+/',$block['attrs']['className']??''),true))continue;$found[$view]++;$html=new WP_HTML_Tag_Processor($block['innerHTML']);if(!$html->next_tag('img'))throw new RuntimeException('About hero image markup missing.');$html->set_attribute('src',$url);$html->remove_attribute('srcset');$html->remove_attribute('sizes');$block['innerHTML']=$html->get_updated_html();foreach($block['innerContent'] as &$part)if(is_string($part)){$processor=new WP_HTML_Tag_Processor($part);if($processor->next_tag('img')){$processor->set_attribute('src',$url);$processor->remove_attribute('srcset');$processor->remove_attribute('sizes');$part=$processor->get_updated_html();}}unset($part);}if(!empty($block['innerBlocks']))$walk($block['innerBlocks']);}};
 $walk($blocks);if($strict&&($found['desktop']!==1||$found['mobile']!==1))throw new RuntimeException('Expected one desktop and one mobile About hero.');return serialize_blocks($blocks);
}
function ed_latest_about_content(){return ed_update_about_hero(ed_about_content(),true);}
