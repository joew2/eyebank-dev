<?php
/** Current campaign presentation without altering frozen migration payloads. */
defined('ABSPATH') || exit;
function ed_gt_current_donation_url(){return 'https://host.nxt.blackbaud.com/adaptive-donor-form/?formId=a1ed5d6a-a86e-4dc2-9f59-9c24c43b58de&envid=p-t70-gTghE0ScbPMC-otWOw&zone=usa';}
function ed_gt_current_links($html,$donation=false){
 $html=str_replace('>givingtuesday.org<','>GivingTuesday.org<',$html);
 if(!$donation)return $html;
 $tags=new WP_HTML_Tag_Processor($html);
 while($tags->next_tag('A'))$tags->set_attribute('href',ed_gt_current_donation_url());
 return $tags->get_updated_html();
}
function ed_gt_current_content($content){
 $walk=function($block,$donation=false)use(&$walk){
  $classes=explode(' ',$block['attrs']['className']??'');
  if(in_array('ed-gt-hashtag',$classes,true))return null;
  $donation=$donation||in_array('ed-gt-donate',$classes,true);
  $children=[];$keep=[];
  foreach($block['innerBlocks'] as $index=>$child){$updated=$walk($child,$donation);$keep[$index]=$updated!==null;if($updated!==null)$children[]=$updated;}
  $inner=[];$index=0;
  foreach($block['innerContent'] as $part){if($part===null){if($keep[$index]??false)$inner[]=null;$index++;}else $inner[]=ed_gt_current_links($part,$donation);}
  $block['innerBlocks']=$children;$block['innerContent']=$inner;$block['innerHTML']=ed_gt_current_links($block['innerHTML'],$donation);
  return $block;
 };
 return serialize_blocks(array_values(array_filter(array_map($walk,parse_blocks($content)),static fn($b)=>$b!==null)));
}
// Existing destination pages also receive the approved updates when the theme is uploaded.
add_filter('render_block_core/group',function($html,$block){
 $classes=explode(' ',$block['attrs']['className']??'');
 if(in_array('ed-gt-donate',$classes,true))$html=ed_gt_current_links($html,true);
 if(in_array('ed-givingtuesday',$classes,true)){
  $html=preg_replace('~<p\b[^>]*class="[^"]*\bed-gt-hashtag\b[^"]*"[^>]*>.*?</p>~s','',$html);
  $html=ed_gt_current_links($html);
 }
 return $html;
},30,2);
