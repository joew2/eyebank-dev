<?php
/** Keep both native Image selections editable; choose a single responsive source publicly. */
defined('ABSPATH') || exit;
add_filter('render_block_core/group',function($html,$block){
 if(!in_array('ed-history',explode(' ',$block['attrs']['className']??''),true))return $html;
 $images=[];foreach($block['innerBlocks']??[] as $child)if(($child['blockName']??'')==='core/image')foreach(['desktop','mobile'] as $view)if(str_contains($child['attrs']['className']??'','ed-history-'.$view)&&!empty($child['attrs']['id']))$images[$view]=(int)$child['attrs']['id'];
 if(count($images)!==2)return $html;
 $desktop=wp_get_attachment_image($images['desktop'],'full',false,['sizes'=>'100vw']);$mobile=wp_get_attachment_image_srcset($images['mobile'],'full')?:wp_get_attachment_image_url($images['mobile'],'full');
 if(!$desktop||!$mobile)return $html;
 $picture='<picture class="ed-history-picture"><source media="(max-width: 600px)" srcset="'.esc_attr($mobile).'" sizes="100vw">'.$desktop.'</picture>';
 $html=preg_replace('~<figure\b[^>]*class="[^"]*ed-history-desktop[^"]*"[^>]*>.*?</figure>~s',$picture,$html,1);
 return preg_replace('~<figure\b[^>]*class="[^"]*ed-history-mobile[^"]*"[^>]*>.*?</figure>~s','',$html,1);
},10,2);
