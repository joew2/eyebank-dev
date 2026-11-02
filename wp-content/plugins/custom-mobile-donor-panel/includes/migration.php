<?php
namespace CWS\MobileDonorPanel;
defined('ABSPATH') || exit;
function bulk_steps(){return [['id'=>'mobile_donor_panel_v1','label'=>'80% mobile donor panel, anchored near the hero bottom','kind'=>'css']];}
function bulk_execute($step){
 preflight();$lease=lock_acquire();
 try{
  $h=history();$key='mobile_donor_panel_v1';$state=$h[$key]??[];
  if(($state['status']??'')==='completed')return 'Mobile adjustment already installed; later CSS edits preserved.';
  $current=wp_get_custom_css('eyedonation');
  if(!$state){
   $after=preg_replace('~/\* CWS MOBILE DONOR PANEL START \*/.*?/\* CWS MOBILE DONOR PANEL END \*/~s','',$current);
   $after=rtrim($after)."\n/* CWS MOBILE DONOR PANEL START */\n".file_get_contents(DIR.'/assets/mobile-donor-panel.css')."\n/* CWS MOBILE DONOR PANEL END */\n";
   $state=['status'=>'running','before'=>$current,'after'=>$after,'actor'=>get_current_user_id(),'started'=>time()];$h[$key]=$state;lock_check($lease);save_history($h);
  }
  if($current!==$state['before']&&$current!==$state['after'])fail('Additional CSS changed during processing. Review it before resuming.');
  lock_check($lease);$result=wp_update_custom_css_post($state['after'],['stylesheet'=>'eyedonation']);if(is_wp_error($result))fail($result->get_error_message());
  if(wp_get_custom_css('eyedonation')!==$state['after'])fail('Mobile donor panel CSS verification failed.');
  $state['status']='completed';$state['completed']=time();$h[$key]=$state;save_history($h);
  return 'Mobile card reduced proportionally by 20% and anchored 12px above the hero bottom.';
 }finally{lock_release($lease);}
}
