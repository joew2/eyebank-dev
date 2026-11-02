<?php
namespace CWS\HomepageRestore;
defined('ABSPATH') || exit;
function bulk_steps(){return [['id'=>'homepage_restore','label'=>'Restore the editable homepage and its display templates','kind'=>'home']];}
function target_page(){
 $id=(int)get_option('page_on_front');$page=$id?get_post($id):null;
 if(get_option('show_on_front')!=='page'||!$page||$page->post_type!=='page'||in_array($page->post_status,['trash','auto-draft'],true))fail('Under Settings → Reading, select your existing static homepage, then click Restore homepage.');
 if(!current_user_can('edit_post',$id))fail('You cannot edit the selected homepage.');
 return $page;
}
function homepage_content(){
 $content=file_get_contents(DIR.'/payloads/homepage.html');
 if(str_contains($content,'https://example.test'))fail('The packaged homepage contains an unresolved source URL.');
 preg_match_all('~\{\{theme_url\}\}([^"\'<>\s]+)~',$content,$matches);
 foreach(array_unique($matches[1]) as $path){
  if(str_contains($path,'..')||!is_file(get_theme_file_path($path)))fail('A required homepage theme asset is missing: '.$path.'. Re-upload the matching EyeDonation theme.');
 }
 $content=str_replace(['{{theme_url}}','{{home_url}}'],[untrailingslashit(get_theme_file_uri('/')),untrailingslashit(home_url('/'))],$content);
 if(str_contains($content,'{{')||str_contains($content,'/placeholder.svg'))fail('The packaged homepage contains an unresolved asset or link.');
 return $content;
}
function bulk_execute($step){
 preflight();$page=target_page();$job=get_option(JOB_OPTION,[]);
 if(empty($job['id'])||empty($job['target_page_id']))fail('Start the homepage restore from its agreement button.');
 if((int)$page->ID!==(int)$job['target_page_id'])fail('The selected homepage changed during processing. Restore the original Reading selection before resuming.');
 $records=[['id'=>(int)$page->ID,'type'=>'page','slug'=>$page->post_name,'content'=>homepage_content(),'meta'=>['_wp_page_template'=>'homepage','tcb_editor_disabled'=>'1','tcb_editor_enabled'=>'']]];
 foreach(['front-page','homepage'] as $slug){
  $file=DIR.'/payloads/display-template.html';if(!is_file($file))fail('The packaged homepage display template is missing.');
  $content=file_get_contents($file);if(!str_contains($content,'wp:post-content'))fail('The theme homepage template must display editable Post Content.');
  $template=get_block_template('eyedonation//'.$slug,'wp_template');
  $records[]=['id'=>(int)($template->wp_id??0),'type'=>'wp_template','slug'=>$slug,'content'=>$content,'meta'=>[]];
 }
 return bulk_records('homepage_restore_'.$job['id'],$records);
}
