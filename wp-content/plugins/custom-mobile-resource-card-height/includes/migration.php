<?php
namespace CWS\MobileResourceCardHeight;
defined('ABSPATH') || exit;
function bulk_steps(){return [['id'=>'mobile_resource_card_height_v2','label'=>'Match mobile height and crop 30px from the top','kind'=>'css']];}
function bulk_execute($step){
 preflight();$lease=lock_acquire();
 try{
  $h=history();$key='mobile_resource_card_height_v2';$state=$h[$key]??[];
  if(($state['status']??'')==='completed')return 'Mobile card height already installed; later edits preserved.';
  $current=wp_get_custom_css('eyedonation');
  if(!$state){
   $after=preg_replace('~/\* CWS MOBILE RESOURCE CARD HEIGHT START \*/.*?/\* CWS MOBILE RESOURCE CARD HEIGHT END \*/~s','',$current);
   $after=rtrim($after)."\n/* CWS MOBILE RESOURCE CARD HEIGHT START */\n".file_get_contents(DIR.'/assets/mobile-card-height.css')."\n/* CWS MOBILE RESOURCE CARD HEIGHT END */\n";
   $state=['status'=>'running','before'=>$current,'after'=>$after,'actor'=>get_current_user_id(),'started'=>time()];$h[$key]=$state;lock_check($lease);save_history($h);
  }
  if($current!==$state['before']&&$current!==$state['after'])fail('Additional CSS changed during processing. Review it before resuming.');
  lock_check($lease);$result=wp_update_custom_css_post($state['after'],['stylesheet'=>'eyedonation']);if(is_wp_error($result))fail($result->get_error_message());
  if(wp_get_custom_css('eyedonation')!==$state['after'])fail('Mobile card height CSS verification failed.');
  $state['status']='completed';$state['completed']=time();$h[$key]=$state;save_history($h);
  return 'Mobile height matches the other Resources cards; 30px cropped from the top, with the remaining crop at the bottom. Desktop is unchanged.';
 }finally{lock_release($lease);}
}
