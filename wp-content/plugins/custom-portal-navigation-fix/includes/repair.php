<?php
namespace CWS\PortalNavigationFix;
defined('ABSPATH') || exit;
function bulk_steps(){return [
 ['id'=>'portal_position_v1','label'=>'Move portal links into the active blue-bar menu','kind'=>'links'],
 ['id'=>'portal_spacing_v1','label'=>'Adjust blue-bar spacing for the restored links','kind'=>'spacing','depends'=>'portal_position_v1'],
];}
function portal_item($block){
 if(!in_array($block['blockName']??'',['core/button','core/navigation-link','core/navigation-submenu'],true))return false;
 $label=trim(html_entity_decode(wp_strip_all_tags($block['attrs']['label']??$block['innerHTML']??''),ENT_QUOTES,'UTF-8'));
 if(in_array(strtolower($label),['transplant portal','research portal'],true))return true;
 $url=$block['attrs']['url']??'';
 if(!$url&&preg_match('~href=["\']([^"\']+)~i',$block['innerHTML']??'',$match))$url=html_entity_decode($match[1],ENT_QUOTES,'UTF-8');
 return in_array(rtrim((string)wp_parse_url($url,PHP_URL_PATH),'/'),['/transplant','/researchers'],true);
}
/** Drop selected child blocks and their matching innerContent slots, preserving all other markup. */
function prune_children($block,$predicate){
 $keep=[];$children=[];
 foreach($block['innerBlocks']??[] as $i=>$child){$keep[$i]=!$predicate($child);if($keep[$i])$children[]=$child;}
 $content=[];$i=0;foreach($block['innerContent']??[] as $part){if($part===null){if($keep[$i]??false)$content[]=null;$i++;}else $content[]=$part;}
 $block['innerBlocks']=$children;$block['innerContent']=$content;return $block;
}
function clean_utility($block){
 $block=prune_children($block,__NAMESPACE__.'\\portal_item');
 foreach($block['innerBlocks'] as &$child)$child=clean_utility($child);unset($child);
 if(($block['blockName']??'')==='core/group'){
  $classes=preg_split('/\s+/',$block['attrs']['className']??'');
  if(in_array('ed-brand',$classes,true)&&in_array('cwsr-header',$classes,true)){
   $block['attrs']['className']=implode(' ',array_values(array_diff($classes,['cwsr-header'])));
   $patch=static function($html){$tags=new \WP_HTML_Tag_Processor($html);if($tags->next_tag('DIV'))$tags->remove_class('cwsr-header');return $tags->get_updated_html();};
   $block['innerHTML']=$patch($block['innerHTML']);foreach($block['innerContent'] as &$piece)if(is_string($piece)){$piece=$patch($piece);break;}unset($piece);
  }
 }
 return $block;
}
function ordered_links($blocks){
 $blocks=array_values(array_filter($blocks,static fn($b)=>!portal_item($b)));
 $give=null;$more=null;
 foreach($blocks as $i=>$block){$label=strtolower(trim(html_entity_decode(wp_strip_all_tags($block['attrs']['label']??''),ENT_QUOTES,'UTF-8')));if($label==='give now')$give=$i;if($label==='more')$more=$i;}
 if($give===null||$more===null||$give>=$more)fail('The active blue-bar menu must contain Give Now before More. No menu content was changed.');
 $new=[];foreach(['Transplant Portal'=>'/transplant/','Research Portal'=>'/researchers/'] as $label=>$path)$new[]=['blockName'=>'core/navigation-link','attrs'=>['label'=>$label,'kind'=>'custom','url'=>home_url($path)],'innerBlocks'=>[],'innerHTML'=>'','innerContent'=>[]];
 array_splice($blocks,$more,0,$new);return $blocks;
}
/** Find the navigation actually rendered by Header, including a referenced wp_navigation record. */
function repair_plan($header){
 $blocks=parse_blocks($header->content);$records=[];$count=0;$brand=0;
 $walk=function(&$items)use(&$walk,&$records,&$count,&$brand){foreach($items as &$block){
  $classes=preg_split('/\s+/',$block['attrs']['className']??'');
  if(($block['blockName']??'')==='core/group'&&in_array('ed-brand',$classes,true)){$brand++;$block=clean_utility($block);}
  if(($block['blockName']??'')==='core/navigation'&&in_array('ed-primary-navigation',$classes,true)){
   $count++;
   if(!empty($block['attrs']['ref'])){
    $nav=get_post((int)$block['attrs']['ref']);
    if(!$nav||$nav->post_type!=='wp_navigation'||$nav->post_status==='trash')fail('The header references an unavailable Navigation menu.');
    if(!current_user_can('edit_post',$nav->ID))fail('You cannot edit the active Navigation menu.');
    $records[]=['id'=>(int)$nav->ID,'type'=>'wp_navigation','slug'=>$nav->post_name,'content'=>serialize_blocks(ordered_links(parse_blocks($nav->post_content))),'meta'=>[]];
   }else{
    $block['innerBlocks']=ordered_links($block['innerBlocks']);$block['innerHTML']='';$block['innerContent']=array_fill(0,count($block['innerBlocks']),null);
   }
  }elseif(!empty($block['innerBlocks']))$walk($block['innerBlocks']);
 }};$walk($blocks);
 if($count!==1||$brand!==1)fail('Expected one EyeDonation utility header and one active primary Navigation block. No menu content was changed.');
 // Write the real menu before removing old utility links; interrupted runs reconcile on retry.
 $records[]=['id'=>(int)($header->wp_id??0),'type'=>'wp_template_part','slug'=>'header','content'=>serialize_blocks($blocks),'meta'=>[]];
 return $records;
}
function bulk_execute($step){
 preflight();
 if($step['kind']==='links'){
  if((history()[$step['id']]['status']??'')==='completed')return 'Portal placement already corrected; later menu edits preserved.';
  $header=get_block_template('eyedonation//header','wp_template_part');if(!$header)fail('EyeDonation header not found.');
  return bulk_records($step['id'],repair_plan($header));
 }
 return spacing();
}
function spacing(){
 $lease=lock_acquire();try{
  $h=history();$key='portal_spacing_v1';$state=$h[$key]??[];
  if(($state['status']??'')==='completed')return 'Blue-bar spacing already corrected.';
  $current=wp_get_custom_css('eyedonation');
  if(!$state){$state=['status'=>'running','before'=>$current,'after'=>rtrim($current)."\n/* CWS PORTAL NAVIGATION START */\n".file_get_contents(DIR.'/assets/navigation.css')."\n/* CWS PORTAL NAVIGATION END */\n",'actor'=>get_current_user_id()];$h[$key]=$state;save_history($h);}
  if($current!==$state['before']&&$current!==$state['after'])fail('Additional CSS changed during processing. Review it before resuming.');
  lock_check($lease);$result=wp_update_custom_css_post($state['after'],['stylesheet'=>'eyedonation']);if(is_wp_error($result))fail($result->get_error_message());
  if(wp_get_custom_css('eyedonation')!==$state['after'])fail('Navigation spacing verification failed.');
  $state['status']='completed';$h[$key]=$state;save_history($h);return 'Spacing saved; existing menu font sizes retained.';
 }finally{lock_release($lease);}
}
