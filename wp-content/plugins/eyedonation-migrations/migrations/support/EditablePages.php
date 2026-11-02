<?php
namespace EyeDonation\SiteMigrations;
defined('ABSPATH') || exit;
/** Preview-first, resumable migrations for a fixed set of approved native page payloads. */
final class EditablePageMigration {
 private $entry;
 private $source;
 public function __construct(array $entry,string $source){$this->entry=$entry;$this->source=$source;}
 public function id(){return sprintf('%03d_install_%s',$this->entry['number'],str_replace('-','_',$this->entry['slug']));}
 private function key(){return 'edm_selected_'.$this->entry['slug'];}
 public function checksum(){
  $slug=$this->entry['slug'];$path=get_theme_file_path('/assets/page-conversions/'.$slug.'.html');
  if(!is_file($path))fail('Matching page payload missing: '.$slug);
  $content=file_get_contents($path);$files=[$this->source,$path,get_theme_file_path('/templates/'.$slug.'.html'),get_theme_file_path('/templates/'.$slug.'-layout.html')];
  preg_match_all('/\{\{asset:([a-zA-Z0-9_.-]+)\}\}/',$content,$assets);
  foreach(array_unique($assets[1]) as $asset)$files[]=get_theme_file_path('/assets/page-conversions/'.$asset);
  $hash=hash_init('sha256');hash_update($hash,wp_json_encode($this->entry));
  foreach($files as $file){if(!is_file($file))fail('Missing theme asset: '.basename($file));hash_update($hash,basename($file).file_get_contents($file));}
  return hash_final($hash);
 }
 private function dependencies(){
  if(!function_exists('ed_converted_page_content'))fail('Install the matching theme with the selected page layouts first.');
  $deps=$this->entry['dependencies'];
  foreach($deps as $dep)if(in_array($dep,['wpv-view','cred_user_form','gravityform','wpv-login-form','wpv-reset-password-form','filemanager'],true)&&!shortcode_exists($dep))fail('Required shortcode unavailable: '.$dep);
  if(in_array('wpv-view',$deps,true)&&!get_page_by_path($deps[1],OBJECT,'view'))fail('Required Toolset View missing: '.$deps[1]);
  if(in_array('cred_user_form',$deps,true)&&!get_page_by_path($deps[1],OBJECT,'cred-user-form'))fail('Required Toolset user form missing: '.$deps[1]);
  if(in_array('gravityform',$deps,true)&&(!class_exists('GFAPI')||!\GFAPI::get_form((int)$deps[1])))fail('Required Gravity Form missing: '.$deps[1]);
 }
 public function plan(){
  preflight();$this->dependencies();$page=get_page_by_path($this->entry['slug']);$id=$page?(int)$page->ID:0;
  if($id&&!current_user_can('edit_post',$id))fail('Cannot edit '.$this->entry['title']);
  if(!$id&&!current_user_can('publish_pages'))fail('Cannot create pages.');
  $after=['_wp_page_template'=>$this->entry['slug'],'post_content'=>ed_converted_page_content($this->entry['slug']),'tcb_editor_disabled'=>'1','tcb_editor_enabled'=>''];$ops=[];
  foreach($after as $field=>$value)$ops[]=['field'=>$field,'before'=>$id?$this->read($id,$field):($field==='post_content'?null:''),'after'=>$value];
  return ['page_id'=>$id,'checksum'=>$this->checksum(),'operations'=>$ops];
 }
 public function preview(){
  $state=history()[$this->id()]??[];if(isset($state['checksum'])&&$state['checksum']!==$this->checksum())fail('Installed migration payload checksum changed.');
  $plan=$this->plan();$plan['actor']=get_current_user_id();$plan['created']=time();update_option($this->key().'_preview',$plan,false);return $plan;
 }
 private function read($id,$field){return $field==='post_content'?get_post_field('post_content',$id):get_post_meta($id,$field,true);}
 private function emptyBefore($plan,$op,$current){return $plan['page_id']===0&&$op['field']==='post_content'&&$current==='';}
 public function run(){
  preflight();$token=lock_acquire();
  try{
   $idKey=$this->id();$h=history();$state=$h[$idKey]??[];
   if(isset($state['checksum'])&&$state['checksum']!==$this->checksum())fail('Installed migration payload checksum changed.');
   if(($state['status']??'')==='completed')return $this->entry['title'].' already installed; editorial changes preserved.';
   $preview=get_option($this->key().'_preview');
   if(!$preview||$preview['actor']!==get_current_user_id()||$preview['created']<time()-DAY_IN_SECONDS||$preview['checksum']!==$this->checksum())fail('A fresh preview by the current administrator is required.');
   $saved=$state['plan']??$preview;$plan=$this->plan();$id=(int)($state['destination_id']??$saved['page_id']);
   if(!$id&&!empty($state['run_id'])&&$plan['page_id']&&get_post_meta($plan['page_id'],'_edm_selected_run_id',true)===$state['run_id'])$id=$plan['page_id'];
   if($plan['page_id']!==$id)fail('Destination page changed after preview.');
   if(fingerprint(array_column($plan['operations'],'after','field'))!==fingerprint(array_column($saved['operations'],'after','field')))fail('Page payload changed after preview.');
   foreach($saved['operations'] as $op){
    $current=$id?$this->read($id,$op['field']):($op['field']==='post_content'?null:'');
    if($current!==$op['before']&&$current!==$op['after']&&!$this->emptyBefore($saved,$op,$current))fail('Page edited since preview: '.$op['field']);
    if(isset($state['operations'][$op['field']])&&$current!==$op['after'])fail('An applied field has since been edited.');
   }
   $state=array_merge($state,['status'=>'running','checksum'=>$this->checksum(),'actor'=>get_current_user_id(),'run_id'=>$state['run_id']??wp_generate_uuid4(),'started'=>$state['started']??time(),'destination_id'=>$id,'plan'=>$saved]);
   $h[$idKey]=$state;lock_check($token);save_history($h);
   if($id&&!get_option($this->key().'_backup')){
    wp_save_post_revision($id);
    add_option($this->key().'_backup',['page_id'=>$id,'created'=>time(),'title'=>get_the_title($id),'operations'=>$saved['operations']],'',false);
   }
   if(!$id){
    $id=wp_insert_post(wp_slash(['post_type'=>'page','post_status'=>'publish','post_name'=>$this->entry['slug'],'post_title'=>$this->entry['title'],'post_content'=>'','meta_input'=>['_edm_selected_run_id'=>$state['run_id']]]),true);
    if(is_wp_error($id)||!$id)fail('Could not create page.');$state['destination_id']=$id;$h[$idKey]=$state;save_history($h);
   }
   foreach($saved['operations'] as $op){
    lock_check($token);$current=$this->read($id,$op['field']);
    if($current!==$op['after']){
     if($current!==$op['before']&&!$this->emptyBefore($saved,$op,$current))fail('Page edited during migration.');
     if($op['field']==='post_content'){$result=wp_update_post(['ID'=>$id,'post_content'=>wp_slash($op['after'])],true);if(is_wp_error($result))fail($result->get_error_message());}
     elseif($op['after']==='')delete_post_meta($id,$op['field']);else update_post_meta($id,$op['field'],$op['after']);
    }
    if($this->read($id,$op['field'])!==$op['after'])fail('Page field verification failed: '.$op['field']);
    $state['operations'][$op['field']]=true;$h[$idKey]=$state;save_history($h);
   }
   $state['status']='completed';$state['completed']=time();$h[$idKey]=$state;lock_check($token);save_history($h);
   return 'Editable '.$this->entry['title'].' page installed.';
  }finally{lock_release($token);}
 }
 public function register(){
  $action='edm_selected_'.$this->entry['slug'];
  foreach(['preview','run'] as $mode)add_action('admin_post_'.$action.'_'.$mode,function()use($action,$mode){
   if(!current_user_can('manage_options'))wp_die('Unauthorized');check_admin_referer($action.'_'.$mode);
   try{if($mode==='run'&&(empty($_POST['backup'])||empty($_POST['replace'])))fail('Backup and replacement acknowledgment required.');$result=$mode==='preview'?$this->preview():$this->run();$notice=is_string($result)?$result:'Preview ready.';}catch(\Throwable $e){$notice=$e->getMessage();}
   update_option($this->key().'_notice',$notice,false);wp_safe_redirect(admin_url('tools.php?page=eyedonation-migrations#'.$action));exit;
  });
  add_action('admin_footer',function()use($action){
   if(($_GET['page']??'')!=='eyedonation-migrations'||!current_user_can('manage_options'))return;
   echo '<div class="wrap" id="'.esc_attr($action).'"><h2>'.esc_html($this->entry['number'].' — '.$this->entry['title']).'</h2><p>Replaces only /'.esc_html($this->entry['slug']).'/ with editable blocks and its matching template. Existing builder data is retained. Status: '.esc_html(history()[$this->id()]['status']??'pending').'</p><p>'.esc_html(get_option($this->key().'_notice','')).'</p>';
   foreach(['preview'=>'Preview page','run'=>'Install / Resume page'] as $mode=>$label){echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="'.esc_attr($action.'_'.$mode).'">';wp_nonce_field($action.'_'.$mode);if($mode==='run')echo '<p><label><input type="checkbox" name="backup" value="1" required> I have a recoverable backup.</label><br><label><input type="checkbox" name="replace" value="1" required> Replace this page with the previewed layout.</label></p>';submit_button($label);echo '</form>';}
   $preview=get_option($this->key().'_preview');if($preview)echo '<details><summary>Current / proposed page fields</summary><pre>'.esc_html(wp_json_encode($preview,JSON_PRETTY_PRINT)).'</pre></details>';echo '</div>';
  });
 }
}
function register_editable_page_migration(array $entry,string $source){
 $migration=new EditablePageMigration($entry,$source);$GLOBALS['edm_selected_pages'][$entry['slug']]=$migration;$migration->register();
}
