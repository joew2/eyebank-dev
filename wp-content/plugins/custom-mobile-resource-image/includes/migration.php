<?php
namespace CWS\MobileResourceImage;
defined('ABSPATH') || exit;
function bulk_steps(){return [
 ['id'=>'import_desktop_combined_v4','label'=>'Import bundled desktop photo','kind'=>'desktop'],
 ['id'=>'import_mobile_combined_v4','label'=>'Import bundled mobile photo','kind'=>'mobile','depends'=>'import_desktop_combined_v4'],
 ['id'=>'resource_images_css_hover_v5','label'=>'Apply responsive photos and matching hover zoom','kind'=>'css','depends'=>'import_mobile_combined_v4'],
 ['id'=>'resource_images_page_combined_v4','label'=>'Replace the recipients/families Resources card','kind'=>'page','depends'=>'resource_images_css_hover_v5'],
];}
function replace_resource_card($content,$picture){
 $blocks=parse_blocks($content);$count=0;
 $walk=function(&$items)use(&$walk,&$count,$picture){foreach($items as &$block){
  $name=$block['blockName']??'';$classes=preg_split('/\s+/',$block['attrs']['className']??'');
  $candidate=in_array($name,['core/cover','core/group'],true)&&in_array('ed-resource-card',$classes,true);
  $html=$candidate?serialize_block($block):'';
  if($candidate&&(str_contains($html,'/transplant-recipients-donor-families')||str_contains($html,'transplant_recipients_donor_families')||in_array('cws-resource-images-v2',$classes,true))){
   $count++;$children=[];
   foreach($block['innerBlocks'] as $child){if(($child['blockName']??'')==='core/html'&&str_contains($child['innerHTML']??'','cws-resource-picture'))continue;$children[]=$child;}
   $classes[]='cws-resource-images-v2';$attrs=['className'=>implode(' ',array_unique(array_filter($classes))),'layout'=>['type'=>'default']];if(!empty($block['attrs']['anchor']))$attrs['anchor']=$block['attrs']['anchor'];
   $inner='<!-- wp:group '.wp_json_encode($attrs).' --><div class="wp-block-group '.esc_attr($attrs['className']).'">'.serialize_block($picture).serialize_blocks($children).'</div><!-- /wp:group -->';
   $block=parse_blocks($inner)[0];
  }elseif(!empty($block['innerBlocks']))$walk($block['innerBlocks']);
 }};$walk($blocks);
 if($count!==1)fail('Expected exactly one recipients/families Resources card on the selected homepage. No page content was changed.');
 return serialize_blocks($blocks);
}
function bulk_execute($step){
 preflight();
 if(in_array($step['kind'],['desktop','mobile'],true))return import_image($step['kind']);
 if($step['kind']==='css')return install_styles();
 if((history()[$step['id']]['status']??'')==='completed')return 'Photo replacement already completed; later page edits preserved.';
 if(!current_user_can('unfiltered_html'))fail('An administrator with unfiltered HTML permission is required to preserve the responsive picture markup.');
 $page=homepage();$content=replace_resource_card($page->post_content,picture_block());
 return bulk_records($step['id'],[['id'=>(int)$page->ID,'type'=>'page','slug'=>$page->post_name,'content'=>$content,'meta'=>[]]]);
}
function install_styles(){
 $lease=lock_acquire();try{
  $h=history();$key='resource_images_css_hover_v5';$state=$h[$key]??[];if(($state['status']??'')==='completed')return 'Old framing already removed; current styles preserved.';
  $current=wp_get_custom_css('eyedonation');
  if(!$state){$after=preg_replace('~/\* CWS RESOURCE PHOTO FRAMING START \*/.*?/\* CWS RESOURCE PHOTO FRAMING END \*/~s','',$current);$after=preg_replace('~/\* CWS RESOURCE CARD IMAGES V2 START \*/.*?/\* CWS RESOURCE CARD IMAGES V2 END \*/~s','',$after);$after=preg_replace('~/\* CWS MOBILE RESOURCE CARD HEIGHT START \*/.*?/\* CWS MOBILE RESOURCE CARD HEIGHT END \*/~s','',$after);$after=rtrim($after)."\n/* CWS RESOURCE CARD IMAGES V2 START */\n".file_get_contents(DIR.'/assets/resource-card-images.css')."\n/* CWS RESOURCE CARD IMAGES V2 END */\n";$state=['status'=>'running','before'=>$current,'after'=>$after,'actor'=>get_current_user_id()];$h[$key]=$state;lock_check($lease);save_history($h);}
  if($current!==$state['before']&&$current!==$state['after'])fail('Additional CSS changed during migration. Review it before resuming.');
  $result=wp_update_custom_css_post($state['after'],['stylesheet'=>'eyedonation']);if(is_wp_error($result))fail($result->get_error_message());if(wp_get_custom_css('eyedonation')!==$state['after'])fail('Responsive image stylesheet verification failed.');
  lock_check($lease);$state['status']='completed';$state['completed']=time();$h[$key]=$state;save_history($h);return 'Desktop and mobile photos installed, with matching mobile card heights and bottom-only cropping.';
 }finally{lock_release($lease);}
}
