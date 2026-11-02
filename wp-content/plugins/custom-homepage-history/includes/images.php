<?php
namespace CWS\HomepageHistory;
defined('ABSPATH') || exit;
function image_manifest(){return json_decode(file_get_contents(DIR.'/assets/images.json'),true);}
function homepage(){
 $page=get_post((int)get_option('page_on_front'));
 if(get_option('show_on_front')!=='page'||!$page||$page->post_type!=='page'||$page->post_status==='trash')fail('Select your existing static homepage in Settings → Reading.');
 if(!current_user_can('edit_post',$page->ID))fail('You cannot edit the selected homepage.');
 return $page;
}
function verified_attachment($id,$spec){
 $file=wp_get_original_image_path($id);
 return $file&&is_file($file)&&hash_file('sha256',$file)===$spec['sha256']&&wp_attachment_is_image($id);
}
function import_image($variant){
 if(!current_user_can('upload_files'))fail('Media upload permission is required.');
 $spec=image_manifest()[$variant];$source=DIR.'/assets/'.$spec['file'];
 if(!is_file($source)||hash_file('sha256',$source)!==$spec['sha256'])fail('Bundled '.$variant.' image failed its checksum check.');
 $size=getimagesize($source);if(!$size||$size[0]!==$spec['width']||$size[1]!==$spec['height']||$size['mime']!=='image/jpeg')fail('Invalid bundled '.$variant.' JPEG.');
 $lease=lock_acquire();
 try{
  $found=get_posts(['post_type'=>'attachment','post_status'=>'inherit','fields'=>'ids','numberposts'=>2,'meta_key'=>'_cwshh_sha256','meta_value'=>$spec['sha256']]);
  if(count($found)>1)fail('Multiple imported attachments match the '.$variant.' image. Resolve the duplicate before retrying.');
  if($found){$id=(int)$found[0];if(!verified_attachment($id,$spec))fail('The previously imported '.$variant.' file is unavailable or changed. Restore it before retrying.');}
  else{
   require_once ABSPATH.'wp-admin/includes/file.php';require_once ABSPATH.'wp-admin/includes/media.php';require_once ABSPATH.'wp-admin/includes/image.php';
   $temp=wp_tempnam($spec['file']);if(!$temp||!copy($source,$temp))fail('Unable to prepare the bundled '.$variant.' image for upload.');
   try{$id=media_handle_sideload(['name'=>$spec['file'],'tmp_name'=>$temp],homepage()->ID,'The world’s first eye bank — '.ucfirst($variant),['post_date'=>current_time('mysql'),'meta_input'=>['_cwshh_sha256'=>$spec['sha256'],'_cwshh_variant'=>$variant]]);}finally{if(is_file($temp))unlink($temp);}
   if(is_wp_error($id))fail($id->get_error_message());
   if(!verified_attachment($id,$spec))fail('Imported '.$variant.' image did not pass verification.');
   update_post_meta($id,'_wp_attachment_image_alt','The Eye-Bank’s original cornea laboratory in 1961');
  }
  lock_check($lease);$assets=get_option('cwshh_assets_v1',[]);$assets[$variant]=(int)$id;update_option('cwshh_assets_v1',$assets,false);
  $h=history();$h['import_'.$variant.'_combined_v4']=['status'=>'completed','attachment_id'=>(int)$id,'sha256'=>$spec['sha256'],'actor'=>get_current_user_id(),'completed'=>time()];save_history($h);
  return 'Verified '.$variant.' image in Media Library (attachment '.$id.').';
 }finally{lock_release($lease);}
}
