<?php
namespace EyeDonation\SiteMigrations;
defined('ABSPATH') || exit;
/** Explicit approved footer replacement; completed runs preserve later editorial changes. */
final class ApprovedFooterMigration {
 const ID='015_update_footer';
 public static function checksum(){return hash('sha256',file_get_contents(__FILE__).file_get_contents(DIR.'/migrations/payloads/015-footer.html'));}
 public static function plan(){
  preflight();if(!current_user_can('edit_theme_options'))fail('Footer editing permission required.');
  $template=get_block_template('eyedonation//footer','wp_template_part');$id=(int)($template->wp_id??0);
  $before=$id?get_post_field('post_content',$id):null;
  $after=str_replace('{{accreditation_url}}',esc_url(get_theme_file_uri('/assets/images/ebss-accreditation.png')),file_get_contents(DIR.'/migrations/payloads/015-footer.html'));
  return ['key'=>'part.footer','kind'=>'post','type'=>'wp_template_part','slug'=>'footer','id'=>$id,'before'=>$before,'after'=>$after,'extra'=>['source_sha256'=>hash_file('sha256',get_theme_file_path('/parts/footer.html')),'area'=>'footer'],'action'=>$before===$after?'already-satisfied':($id?'update':'create')];
 }
 public static function preview(){
  $state=history()[self::ID]??[];if(isset($state['checksum'])&&$state['checksum']!==self::checksum())fail('015 checksum changed.');
  $p=['actor'=>get_current_user_id(),'checksum'=>self::checksum(),'operation'=>self::plan(),'created'=>time()];update_option('edm_footer_preview',$p,false);return $p;
 }
 public static function run(){
  preflight();$token=lock_acquire();try{
   $h=history();$state=$h[self::ID]??[];if(isset($state['checksum'])&&$state['checksum']!==self::checksum())fail('015 checksum changed.');
   if(($state['status']??'')==='completed')return 'Footer migration already completed; later editorial changes preserved.';
   $p=get_option('edm_footer_preview');if(!$p||$p['actor']!==get_current_user_id()||$p['checksum']!==self::checksum()||$p['created']<time()-DAY_IN_SECONDS)fail('Fresh footer preview required.');
   $op=self::plan();$expected=$state['operation']??$p['operation'];
   $reconciled=$expected['id']===0&&$op['action']==='already-satisfied'&&$op['id']===managed('part.footer','wp_template_part');
   if((!$reconciled&&$op['id']!==$expected['id'])||$op['after']!==$expected['after']||($op['before']!==$expected['before']&&$op['action']!=='already-satisfied'))fail('Footer changed after preview.');
   $h[self::ID]=array_merge($state,['status'=>'running','checksum'=>self::checksum(),'actor'=>get_current_user_id(),'run_id'=>$state['run_id']??wp_generate_uuid4(),'started'=>$state['started']??time(),'operation'=>$expected]);lock_check($token);save_history($h);
   $id=apply_operation($op);lock_check($token);if(get_post_field('post_content',$id)!==$expected['after'])fail('Footer update verification failed.');
   $h[self::ID]['destination_id']=$id;$h[self::ID]['status']='completed';$h[self::ID]['completed']=time();save_history($h);
   return 'Approved footer content installed. Other template parts and classic menus preserved.';
  }finally{lock_release($token);}
 }
}
foreach(['preview','run'] as $mode)add_action('admin_post_edm_footer_'.$mode,function()use($mode){if(!current_user_can('manage_options'))wp_die('Unauthorized');check_admin_referer('edm_footer_'.$mode);try{if($mode==='run'&&empty($_POST['backup']))fail('Backup acknowledgment required.');$result=$mode==='preview'?ApprovedFooterMigration::preview():ApprovedFooterMigration::run();update_option('edm_footer_notice',is_string($result)?$result:'Preview ready.',false);}catch(\Throwable $e){update_option('edm_footer_notice',$e->getMessage(),false);}wp_safe_redirect(admin_url('tools.php?page=eyedonation-migrations#edm-footer-update'));exit;});
add_action('admin_footer',function(){if(($_GET['page']??'')!=='eyedonation-migrations'||!current_user_can('manage_options'))return;echo '<div class="wrap" id="edm-footer-update"><h2>015 — Approved footer content</h2><p>Replace the Footer template part with the approved contact details, eight links, social profiles, nonprofit caption, and copyright text. Classic menus and the header remain unchanged. Status: '.esc_html(history()[ApprovedFooterMigration::ID]['status']??'pending').'</p><p role="status">'.esc_html(get_option('edm_footer_notice','')).'</p>';foreach(['preview'=>'Preview footer','run'=>'Apply / Resume footer'] as $mode=>$label){echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="edm_footer_'.$mode.'">';wp_nonce_field('edm_footer_'.$mode);if($mode==='run')echo '<p><label><input type="checkbox" name="backup" value="1" required> I have a recoverable backup.</label></p>';submit_button($label);echo '</form>';}$p=get_option('edm_footer_preview');if($p)echo '<details><summary>Current / proposed footer</summary><pre>'.esc_html(wp_json_encode($p['operation'],JSON_PRETTY_PRINT)).'</pre></details>';echo '</div>';});
