<?php
/** Read-only pattern defaults; deployment still requires verified migration mappings. */
defined('ABSPATH') || exit;
function ed_pattern_media($path,$alt){
 $ids=get_posts(['post_type'=>'attachment','post_status'=>'inherit','numberposts'=>2,'fields'=>'ids','meta_key'=>'_wp_attached_file','meta_value'=>$path]);
 if(count($ids)===1&&wp_attachment_is_image($ids[0])&&is_file(get_attached_file($ids[0])))return ['id'=>(int)$ids[0],'url'=>wp_get_attachment_image_url($ids[0],'full'),'alt'=>get_post_meta($ids[0],'_wp_attachment_image_alt',true)?:$alt];
 return ['id'=>0,'url'=>get_theme_file_uri('/assets/images/placeholder.svg'),'alt'=>'Development placeholder — select the supplied Support photograph'];
}

/** Complete, reusable native homepage; read-only and independent of migrations. */
function ed_homepage_pattern_content(){
 $root=get_theme_file_path('/assets/patterns');$content=file_get_contents($root.'/homepage.html');$manifest=json_decode(file_get_contents($root.'/homepage-assets.json'),true);$resolved=[];
 foreach($manifest as $key=>$asset){$media=ed_pattern_media($asset['path'],$asset['alt']);
  if(!$media['id']){global $wpdb;$candidates=$wpdb->get_col($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_wp_attachment_metadata' AND meta_value LIKE %s",'%'.$wpdb->esc_like(basename($asset['path'])).'%'));$matches=[];foreach($candidates as $id){$meta=wp_get_attachment_metadata($id);$file=get_post_meta($id,'_wp_attached_file',true);if(!empty($meta['original_image'])&&dirname($file).'/'.$meta['original_image']===$asset['path']&&wp_attachment_is_image($id))$matches[]=$id;}if(count($matches)===1)$media=['id'=>(int)$matches[0],'url'=>wp_get_attachment_image_url($matches[0],'full'),'alt'=>get_post_meta($matches[0],'_wp_attachment_image_alt',true)?:$asset['alt']];}
  $resolved[$key]=$media;$content=str_replace(['{{asset:'.$key.'}}','{{alt:'.$key.'}}'],[esc_url($media['url']),esc_attr($media['alt'])],$content);
 }
 // Keep destinations editable and use the homepage's approved placeholder defaults.
 foreach(['donor','recipients','research','transplant','about','utility','video','donation'] as $key)$content=str_replace('{{link:'.$key.'}}',esc_url(add_query_arg('eyedonation-placeholder',$key,home_url('/'))),$content);
 $blocks=parse_blocks($content);$remap=function(&$blocks)use(&$remap,$resolved){foreach($blocks as &$b){foreach($resolved as $asset){if(!$asset['id'])continue;$image=($b['blockName']??'')==='core/image'&&str_contains($b['innerHTML'],$asset['url']);$cover=($b['blockName']??'')==='core/cover'&&($b['attrs']['url']??'')===$asset['url'];if($image||$cover){$b['attrs']['id']=$asset['id'];$class='wp-image-'.$asset['id'];$patch=fn($html)=>$cover?str_replace('class="wp-block-cover__image-background"','class="wp-block-cover__image-background '.$class.'"',$html):str_replace('<img ','<img class="'.$class.'" ',$html);$b['innerHTML']=$patch($b['innerHTML']);foreach($b['innerContent'] as &$piece)if(is_string($piece))$piece=$patch($piece);unset($piece);}}if(!empty($b['innerBlocks']))$remap($b['innerBlocks']);}};$remap($blocks);return serialize_blocks($blocks);
}
