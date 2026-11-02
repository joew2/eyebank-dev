<?php
namespace CWS\ResourcePhotoFraming;
defined('ABSPATH') || exit;
function bulk_steps(){return [['id'=>'resource_photo_framing_v1','label'=>'Recipients and families resource photo framing','kind'=>'css']];}
function bulk_execute($step){
 preflight();$lease=lock_acquire();
 try{
  $h=history();$key='resource_photo_framing_v1';$state=$h[$key]??[];
  if(($state['status']??'')==='completed')return 'Photo framing already installed; later CSS edits preserved.';
  $current=wp_get_custom_css('eyedonation');
  if(!$state){
   $after=preg_replace('~/\* CWS RESOURCE PHOTO FRAMING START \*/.*?/\* CWS RESOURCE PHOTO FRAMING END \*/~s','',$current);
   $after=rtrim($after)."\n/* CWS RESOURCE PHOTO FRAMING START */\n".file_get_contents(DIR.'/assets/resource-photo-framing.css')."\n/* CWS RESOURCE PHOTO FRAMING END */\n";
   $state=['status'=>'running','before'=>$current,'after'=>$after,'actor'=>get_current_user_id(),'started'=>time()];$h[$key]=$state;lock_check($lease);save_history($h);
  }
  if($current!==$state['before']&&$current!==$state['after'])fail('Additional CSS changed during processing. Review it before resuming.');
  lock_check($lease);$result=wp_update_custom_css_post($state['after'],['stylesheet'=>'eyedonation']);if(is_wp_error($result))fail($result->get_error_message());
  if(wp_get_custom_css('eyedonation')!==$state['after'])fail('Resource photo CSS verification failed.');
  $state['status']='completed';$state['completed']=time();$h[$key]=$state;save_history($h);
  return 'Resource photo enlarged 5%, shifted right with mobile downward framing, and bounded to preserve the head and image coverage.';
 }finally{lock_release($lease);}
}
