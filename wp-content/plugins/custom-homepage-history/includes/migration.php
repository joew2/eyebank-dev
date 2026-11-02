<?php
namespace CWS\HomepageHistory;
defined('ABSPATH') || exit;
function bulk_steps(){return [
 ['id'=>'history_photo_v3','label'=>'Import new desktop history background','kind'=>'image'],
 ['id'=>'history_mobile_photo_v3','label'=>'Import new mobile history background','kind'=>'mobile','depends'=>'history_photo_v3'],
 ['id'=>'history_styles_v6','label'=>'Fit the new photo dimensions with original typography','kind'=>'css','depends'=>'history_mobile_photo_v3'],
 ['id'=>'history_page_v5','label'=>'Update only the homepage history section','kind'=>'page','depends'=>'history_styles_v6'],
];}
function group_block($attrs,$children){
 $tag=$attrs['tagName']??'div';if(!in_array($tag,['div','section'],true))fail('Unexpected history section wrapper.');
 $html='<!-- wp:group '.wp_json_encode($attrs).' --><'.$tag.' class="wp-block-group '.esc_attr($attrs['className']??'').'">'.serialize_blocks($children).'</'.$tag.'><!-- /wp:group -->';
 return parse_blocks($html)[0];
}
function update_history($content,$id,$url,$mobile_id=null,$mobile_url=null){
 $blocks=parse_blocks($content);$sections=0;
 $walk=function(&$items)use(&$walk,&$sections,$id,$url,$mobile_id,$mobile_url){foreach($items as &$b){
  $classes=preg_split('/\s+/',$b['attrs']['className']??'');
  if(($b['blockName']??'')==='core/group'&&in_array('ed-history',$classes,true)){
   $sections++;$desktop=0;$mobile=0;$panels=0;$buttons=[];
   $edit=function(&$children)use(&$edit,&$desktop,&$mobile,&$panels,&$buttons,$id,$url,$mobile_id,$mobile_url){foreach($children as &$child){
    $cc=preg_split('/\s+/',$child['attrs']['className']??'');
    if(($child['blockName']??'')==='core/image'&&in_array('ed-history-desktop',$cc,true)){
     $desktop++;$attrs=$child['attrs'];$attrs['id']=$id;$attrs['sizeSlug']='full';
     $html='<!-- wp:image '.wp_json_encode($attrs).' --><figure class="wp-block-image size-full '.esc_attr($attrs['className']).'"><img src="'.esc_url($url).'" alt="The Eye-Bank’s original cornea laboratory in 1961" class="wp-image-'.$id.'"/></figure><!-- /wp:image -->';$child=parse_blocks($html)[0];
    }elseif(($child['blockName']??'')==='core/image'&&(in_array('ed-history-mobile',$cc,true)||in_array('cws-history-mobile-v3',$cc,true))&&$mobile_id!==null){
     $mobile++;$attrs=$child['attrs'];$attrs['className']=implode(' ',array_unique(array_merge(array_values(array_diff($cc,['ed-history-mobile'])),['cws-history-mobile-v3'])));$attrs['id']=$mobile_id;$attrs['sizeSlug']='full';
     $html='<!-- wp:image '.wp_json_encode($attrs).' --><figure class="wp-block-image size-full '.esc_attr($attrs['className']).'"><img src="'.esc_url($mobile_url).'" alt="The Eye-Bank’s original cornea laboratory in 1961" class="wp-image-'.$mobile_id.'"/></figure><!-- /wp:image -->';$child=parse_blocks($html)[0];
    }elseif(($child['blockName']??'')==='core/group'&&in_array('ed-history-panel',$cc,true)){
     $panels++;$keep=[];foreach($child['innerBlocks'] as $part){if(($part['blockName']??'')==='core/buttons')$buttons[]=$part;else {
      if(($part['blockName']??'')==='core/paragraph'){
       // Nonbreaking space keeps the two words together without forcing a line break.
       $join=static fn($html)=>preg_replace('/\bsight(?: |&nbsp;|\x{00a0})+to\b/u','sight&nbsp;to',$html);
       $part['innerHTML']=$join($part['innerHTML']);
       foreach($part['innerContent'] as &$fragment)if(is_string($fragment))$fragment=$join($fragment);unset($fragment);
      }
      $keep[]=$part;
     }}$child=group_block($child['attrs'],$keep);
    }elseif(($child['blockName']??'')==='core/group'&&!empty($child['innerBlocks'])){
     $edit($child['innerBlocks']);$child=group_block($child['attrs'],$child['innerBlocks']);
    }
   }};
   // The history section has two image blocks and a single content wrapper.
   $children=$b['innerBlocks'];$edit($children);
   if($desktop!==1||$panels!==1||($mobile_id!==null&&$mobile!==1))fail('Expected one desktop history photo and one text panel. No page content was changed.');
   $wrappers=0;foreach($children as &$child){if(($child['blockName']??'')!=='core/group')continue;$wrappers++;
    $parts=[];foreach($child['innerBlocks'] as $part){if(($part['blockName']??'')==='core/buttons')$buttons[]=$part;else $parts[]=$part;}
    if(count($buttons)!==1)fail('Expected one About Us button group. No page content was changed.');
    $button=$buttons[0];$button['attrs']['className']=implode(' ',array_unique(array_filter(explode(' ',($button['attrs']['className']??'').' ed-history-about'))));
    $button=parse_blocks('<!-- wp:buttons '.wp_json_encode($button['attrs']).' --><div class="wp-block-buttons '.esc_attr($button['attrs']['className']).'">'.serialize_blocks($button['innerBlocks']).'</div><!-- /wp:buttons -->')[0];
    $parts[]=$button;$attrs=$child['attrs'];$attrs['className']='ed-history-layout-v2';$child=group_block($attrs,$parts);
   }unset($child);
   if($wrappers!==1)fail('Expected one history content wrapper. No page content was changed.');
   $attrs=$b['attrs'];$classes[]='cws-history-v2';$attrs['className']=implode(' ',array_unique(array_filter($classes)));$b=group_block($attrs,$children);
  }elseif(!empty($b['innerBlocks']))$walk($b['innerBlocks']);
 }};$walk($blocks);if($sections!==1)fail('Expected exactly one history section on the selected homepage. No page content was changed.');return tablet_resource_source(serialize_blocks($blocks));
}
/** Extend the existing responsive Resources image to the tablet breakpoint. */
function tablet_resource_source($content){
 $blocks=parse_blocks($content);
 $walk=function(&$items,$inside=false)use(&$walk){foreach($items as &$block){
  $target=$inside||in_array('cws-resource-images-v2',preg_split('/\s+/',$block['attrs']['className']??''),true);
  if($target&&($block['blockName']??'')==='core/html'&&str_contains($block['innerHTML']??'','cws-resource-picture')){
   $processor=new \WP_HTML_Tag_Processor($block['innerHTML']);
   while($processor->next_tag('SOURCE'))if(in_array($processor->get_attribute('media'),['(max-width: 781px)','(max-width: 900px)'],true))$processor->set_attribute('media','(max-width: 900px)');
   $html=$processor->get_updated_html();$block['innerHTML']=$html;$block['innerContent']=[$html];
  }
  if(!empty($block['innerBlocks']))$walk($block['innerBlocks'],$target);
 }};$walk($blocks);return serialize_blocks($blocks);
}
function bulk_execute($step){
 preflight();if($step['kind']==='image')return import_image('desktop');if($step['kind']==='mobile')return import_image('mobile');if($step['kind']==='css')return install_styles();
 if((history()[$step['id']]['status']??'')==='completed')return 'History section updated; later edits preserved.';
 $id=(int)(get_option('cwshh_assets_v1',[])['desktop']??0);if(!$id||!verified_attachment($id,image_manifest()['desktop']))fail('Import the desktop photo first.');
 $url=wp_get_attachment_url($id);if(!$url)fail('Desktop image URL unavailable.');$mobile=(int)(get_option('cwshh_assets_v1',[])['mobile']??0);if(!$mobile||!verified_attachment($mobile,image_manifest()['mobile']))fail('Import the mobile photo first.');$mobile_url=wp_get_attachment_url($mobile);if(!$mobile_url)fail('Mobile image URL unavailable.');$page=homepage();$content=update_history($page->post_content,$id,$url,$mobile,$mobile_url);
 return bulk_records($step['id'],[['id'=>(int)$page->ID,'type'=>'page','slug'=>$page->post_name,'content'=>$content,'meta'=>[]]]);
}
function install_styles(){
 $lease=lock_acquire();try{
 $h=history();$key='history_styles_v6';$state=$h[$key]??[];if(($state['status']??'')==='completed')return 'History styling already installed; later edits preserved.';
 $current=wp_get_custom_css('eyedonation');
 if(!$state){$after=preg_replace('~/\* CWS HOMEPAGE HISTORY V2 START \*/.*?/\* CWS HOMEPAGE HISTORY V2 END \*/~s','',$current);$after=rtrim($after)."\n/* CWS HOMEPAGE HISTORY V2 START */\n".file_get_contents(DIR.'/assets/history.css')."\n/* CWS HOMEPAGE HISTORY V2 END */\n";$state=['status'=>'running','before'=>$current,'after'=>$after,'actor'=>get_current_user_id()];$h[$key]=$state;lock_check($lease);save_history($h);}
 if($current!==$state['before']&&$current!==$state['after'])fail('Additional CSS changed during migration. Review before retrying.');
 lock_check($lease);$result=wp_update_custom_css_post($state['after'],['stylesheet'=>'eyedonation']);if(is_wp_error($result))fail($result->get_error_message());if(wp_get_custom_css('eyedonation')!==$state['after'])fail('History styling verification failed.');$state['status']='completed';$h[$key]=$state;save_history($h);return 'History panel and separate button styles installed.';
 }finally{lock_release($lease);}
}
