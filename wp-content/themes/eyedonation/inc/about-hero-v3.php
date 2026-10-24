<?php
/** Updated compact, watermarked About hero photographs; preserve all other blocks. */
defined('ABSPATH') || exit;
function ed_update_about_hero_v3($content, $strict=false) {
 $blocks=parse_blocks($content);$found=['desktop'=>0,'mobile'=>0];
 $walk=function(&$items)use(&$walk,&$found){foreach($items as &$block){foreach(['desktop','mobile'] as $view){if(($block['blockName']??'')!=='core/image'||!in_array('ed-about-hero-'.$view,explode(' ',$block['attrs']['className']??''),true))continue;$found[$view]++;$url=get_theme_file_uri('/assets/about-hero-v3/photo-about-eye-bank-'.$view.'.jpg');
 $patch=static function($html)use($url){$tags=new WP_HTML_Tag_Processor($html);if($tags->next_tag('img')){$tags->set_attribute('src',$url);$tags->remove_attribute('srcset');$tags->remove_attribute('sizes');$class=preg_replace('/\bwp-image-\d+\b/','',$tags->get_attribute('class')??'');if(trim($class)!=='')$tags->set_attribute('class',trim($class));else $tags->remove_attribute('class');}return $tags->get_updated_html();};
 unset($block['attrs']['id']);$block['innerHTML']=$patch($block['innerHTML']);foreach($block['innerContent'] as &$part)if(is_string($part))$part=$patch($part);unset($part);
 }if(!empty($block['innerBlocks']))$walk($block['innerBlocks']);}};$walk($blocks);if($strict&&($found['desktop']!==1||$found['mobile']!==1))throw new RuntimeException('Expected both About hero image blocks.');return serialize_blocks($blocks);
}
