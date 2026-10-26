<?php
/** Full uncropped support photograph with the existing editable call to action. */
defined('ABSPATH') || exit;
function ed_update_homepage_support_photo($content,$strict=false){
 $blocks=parse_blocks($content);$found=0;
 $walk=function(&$items)use(&$walk,&$found){foreach($items as &$block){
  $classes=explode(' ',$block['attrs']['className']??'');
  if(($block['blockName']??'')==='core/group'&&in_array('ed-support-full-photo',$classes,true)){$found++;continue;}
  if(($block['blockName']??'')==='core/cover'&&in_array('ed-support',$classes,true)){
   $found++;$panels=array_filter($block['innerBlocks']??[],static function($child){return ($child['blockName']??'')==='core/group'&&in_array('ed-support-panel',explode(' ',$child['attrs']['className']??''),true);});
   if(count($panels)!==1||count($block['innerBlocks'])!==1)throw new RuntimeException('Expected one editable support panel.');
   $url=esc_url(get_theme_file_uri('/assets/homepage-support-v2/support-our-sight-saving-programs.jpg'));
   $image='<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"ed-support-photo"} --><figure class="wp-block-image size-full ed-support-photo"><img src="'.$url.'" alt="Cornea transplant recipient Austin reading with his family"/></figure><!-- /wp:image -->';
   $html='<!-- wp:group {"className":"ed-support ed-support-full-photo","layout":{"type":"default"}} --><div class="wp-block-group ed-support ed-support-full-photo">'.$image.serialize_blocks($block['innerBlocks']).'</div><!-- /wp:group -->';
   $block=parse_blocks($html)[0];continue;
  }
  if(!empty($block['innerBlocks']))$walk($block['innerBlocks']);
 }};$walk($blocks);
 if($strict&&$found!==1)throw new RuntimeException('Expected one homepage support section.');
 return serialize_blocks($blocks);
}
