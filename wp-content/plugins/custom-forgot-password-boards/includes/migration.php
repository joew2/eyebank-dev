<?php
namespace CWS\ForgotPasswordBoards;
defined('ABSPATH') || exit;
function bulk_steps(){return [
 ['id'=>'boards_page_v1','label'=>'Editable boards password page and template','kind'=>'page'],
 ['id'=>'boards_styles_v1','label'=>'Centered password-form styling','kind'=>'styles','depends'=>'boards_page_v1'],
];}
function destinations(){
 $pages=[];foreach(['member','enter-new-password'] as $slug){$page=get_page_by_path($slug,OBJECT,'page');if(!$page||$page->post_status!=='publish')fail('Required destination page missing or unpublished: /'.$slug.'/. Publish or restore it before processing.');$pages[$slug]=get_permalink($page);}
 return $pages;
}
function page_content(){
 $urls=destinations();return str_replace(['{{member_url}}','{{reset_url}}'],[esc_url($urls['member']),esc_url($urls['enter-new-password'])],file_get_contents(DIR.'/payloads/content.html'));
}
function target_page(){
 $page=get_page_by_path('password-boards',OBJECT,'page');if($page)return $page;
 $ids=get_posts(['post_type'=>'page','post_status'=>['publish','draft','pending','private'],'numberposts'=>2,'fields'=>'ids','meta_key'=>'_wp_page_template','meta_value'=>'page-forgot-password-boards.php']);
 if(count($ids)>1)fail('Multiple pages use the legacy boards password template. Resolve the duplicate before processing.');
 return $ids?get_post($ids[0]):null;
}
function bulk_execute($step){
 preflight();
 if($step['kind']==='styles')return install_styles();
 if((history()[$step['id']]['status']??'')==='completed')return 'Boards page already installed; later edits preserved.';
 foreach(['wpv-forgot-password-form','wpv-conditional','wpv-current-user'] as $tag)if(!shortcode_exists($tag))fail('Activate Toolset Views: required shortcode unavailable: '.$tag);
 $page=target_page();$template=get_block_template('eyedonation//forgot-password-boards','wp_template');
 return bulk_records($step['id'],[
  ['id'=>(int)($template->wp_id??0),'type'=>'wp_template','slug'=>'forgot-password-boards','title'=>'Forgot Password Boards — Editable Page','content'=>file_get_contents(DIR.'/payloads/template.html'),'meta'=>[]],
  ['id'=>$page?(int)$page->ID:0,'type'=>'page','slug'=>'password-boards','title'=>'Forgot Password Boards','content'=>page_content(),'meta'=>['_wp_page_template'=>'forgot-password-boards','tcb_editor_disabled'=>'1','tcb_editor_enabled'=>'']],
 ]);
}
function install_styles(){
 $token=lock_acquire();try{
  $h=history();$key='boards_styles_v1';$state=$h[$key]??[];if(($state['status']??'')==='completed')return 'Styling already installed.';
  $current=wp_get_custom_css('eyedonation');
  if(!$state){$state=['status'=>'running','before'=>$current,'after'=>rtrim($current)."\n/* CWS FORGOT PASSWORD BOARDS START */\n".file_get_contents(DIR.'/assets/page.css')."\n/* CWS FORGOT PASSWORD BOARDS END */\n",'actor'=>get_current_user_id()];$h[$key]=$state;save_history($h);}
  if($current!==$state['before']&&$current!==$state['after'])fail('Additional CSS changed during migration. Review it before resuming.');
  lock_check($token);$result=wp_update_custom_css_post($state['after'],['stylesheet'=>'eyedonation']);if(is_wp_error($result))fail($result->get_error_message());
  if(wp_get_custom_css('eyedonation')!==$state['after'])fail('Form styling verification failed.');
  $state['status']='completed';$h[$key]=$state;save_history($h);return 'Password-form styling installed.';
 }finally{lock_release($token);}
}
