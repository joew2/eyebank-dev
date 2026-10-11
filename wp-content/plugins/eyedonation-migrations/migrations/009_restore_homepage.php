<?php
namespace EyeDonation\SiteMigrations;
defined('ABSPATH') || exit;
/** Explicit recovery of the owner-emptied homepage; earlier migrations stay immutable. */
final class RestoreHomepageMigration {
 const ID='009_restore_emptied_homepage';
 const BEFORE='402152bbaf991da77c5e63f4472f59a88b5c47b0092737767a65794085d84f4d';
 public static function checksum(){return hash('sha256',file_get_contents(__FILE__).checksum('003_apply_homepage_content'));}
 public static function run(){preflight();$token=lock_acquire();try{$h=history();$old=$h[self::ID]??[];if(isset($old['checksum'])&&$old['checksum']!==self::checksum())fail('009 checksum changed.');$p=page(payload()['homepage']['path']);if(!current_user_can('edit_post',$p->ID))fail('Cannot edit homepage.');$after=homepage_content();if(($old['status']??'')==='completed'){if($p->post_content!==$after)fail('Restored homepage was subsequently edited; use the complete homepage pattern or a new recovery migration.');return 'Homepage restoration already completed.';}
  if(hash('sha256',$p->post_content)!==self::BEFORE&&$p->post_content!==$after)fail('Homepage does not match the reviewed empty content.');if(get_post_meta($p->ID,'tcb_editor_disabled',true)!=='1')fail('Legacy editor override must remain disabled.');
  $h[self::ID]=array_merge($old,['status'=>'running','checksum'=>self::checksum(),'actor'=>get_current_user_id(),'run_id'=>$old['run_id']??wp_generate_uuid4(),'started'=>$old['started']??time(),'destination_id'=>$p->ID,'before'=>$old['before']??$p->post_content]);lock_check($token);save_history($h);
  if($p->post_content!==$after){$result=wp_update_post(['ID'=>$p->ID,'post_content'=>wp_slash($after)],true);if(is_wp_error($result))fail($result->get_error_message());}
  lock_check($token);if(get_post_field('post_content',$p->ID)!==$after)fail('Homepage restoration verification failed.');$h[self::ID]['status']='completed';$h[self::ID]['completed']=time();$h[self::ID]['after_fingerprint']=fingerprint($after);save_history($h);return 'Complete native homepage restored.';
 }finally{lock_release($token);}}
}
