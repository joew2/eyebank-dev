<?php
defined('ABSPATH') || exit;
// Do not present WordPress's arbitrary fallback page list as approved project navigation.
add_filter('pre_render_block',function($pre,$block){
 if($pre!==null||is_admin()||($block['blockName']??'')!=='core/navigation'||!empty($block['attrs']['ref'])||!empty($block['innerBlocks']))return $pre;
 $classes=explode(' ',$block['attrs']['className']??'');if(!array_intersect($classes,['ed-primary-navigation','ed-footer-navigation']))return $pre;
 return '<nav aria-label="'.esc_attr($block['attrs']['ariaLabel']??__('Navigation','eyedonation')).'"><span class="screen-reader-text">'.esc_html__('Navigation awaits approved setup.','eyedonation').'</span></nav>';
},10,2);
