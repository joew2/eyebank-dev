<?php
namespace EyeDonation\SiteMigrations;
defined('ABSPATH') || exit;
final class MemberMenuMigration {
 const ID='043_install_member_menu';
 public static function checksum(){return hash('sha256',file_get_contents(__FILE__).file_get_contents(get_theme_file_path('/parts/member-menu.html')));}
 private static function nav(){return get_posts(['post_type'=>'wp_navigation','name'=>'ed-member-menu','post_status'=>['publish','draft'],'numberposts'=>1])[0]??null;}
 public static function preview(){
  preflight();if(!current_user_can('edit_theme_options'))fail('Cannot manage member navigation.');
  $part=get_block_template(get_stylesheet().'//member-menu','wp_template_part');
  if(!$part)fail('Matching theme member-menu template part is required.');
  $nav=self::nav();$p=['actor'=>get_current_user_id(),'created'=>time(),'checksum'=>self::checksum(),'part_id'=>(int)($part->wp_id??0),'nav_id'=>$nav?(int)$nav->ID:0,'part_before'=>$part->content,'nav_before'=>$nav?$nav->post_content:null];
  update_option('edm_member_menu_preview',$p,false);return $p;
 }
 public static function run(){
  preflight();if(!current_user_can('edit_theme_options'))fail('Cannot manage member navigation.');$token=lock_acquire();
  try{
   $h=history();$state=$h[self::ID]??[];
   if(isset($state['checksum'])&&$state['checksum']!==self::checksum())fail('043 checksum changed.');
   if(($state['status']??'')==='completed')return 'Member menu already installed; editorial changes preserved.';
   $p=get_option('edm_member_menu_preview');if(!$p||$p['actor']!==get_current_user_id()||$p['created']<time()-DAY_IN_SECONDS||$p['checksum']!==self::checksum())fail('Fresh member menu preview required.');
   $part=get_block_template(get_stylesheet().'//member-menu','wp_template_part');$partId=(int)($part->wp_id??0);
   if(!empty($p['part_id'])){
    if($partId!==$p['part_id']||$part->content!==$p['part_before'])fail('Member menu changed after preview.');
    $h[self::ID]=['status'=>'completed','checksum'=>self::checksum(),'actor'=>get_current_user_id(),'completed'=>time(),'destination_id'=>$partId,'preview'=>$p,'note'=>'Existing customized member template part retained.'];save_history($h);return 'Existing member menu retained; edit it under Appearance → Member Menu.';
   }
   $run=$state['run_id']??wp_generate_uuid4();
   if($partId&&get_post_meta($partId,'_ed_member_menu_run_id',true)!==$run)fail('A member template part was created after preview.');
   $nav=self::nav();
   if($nav&&(int)$nav->ID!==$p['nav_id']&&get_post_meta($nav->ID,'_ed_member_menu_run_id',true)!==$run)fail('Member navigation was created after preview.');
   if($nav&&$p['nav_id']&&$nav->post_content!==$p['nav_before'])fail('Member navigation changed after preview.');
   $state=array_merge($state,['status'=>'running','checksum'=>self::checksum(),'actor'=>get_current_user_id(),'run_id'=>$run,'preview'=>$p]);$h[self::ID]=$state;lock_check($token);save_history($h);
   $blocks=parse_blocks(file_get_contents(get_theme_file_path('/parts/member-menu.html')));$links=null;
   $find=function($items)use(&$find,&$links){foreach($items as $b){if(($b['blockName']??'')==='core/navigation'&&in_array('ed-member-navigation',explode(' ',$b['attrs']['className']??''),true))$links=$b['innerBlocks'];if(!empty($b['innerBlocks']))$find($b['innerBlocks']);}};$find($blocks);
   if(!$links)fail('Default member links missing.');
   if(!$nav){$id=wp_insert_post(wp_slash(['post_type'=>'wp_navigation','post_status'=>'publish','post_name'=>'ed-member-menu','post_title'=>'Member Access','post_content'=>serialize_blocks($links),'meta_input'=>['_ed_member_menu_run_id'=>$run]]),true);if(is_wp_error($id)||!$id)fail('Could not create member navigation.');$nav=get_post($id);}
   $state['nav_id']=(int)$nav->ID;$h[self::ID]=$state;save_history($h);
   $attach=function(&$items)use(&$attach,$nav){foreach($items as &$b){if(($b['blockName']??'')==='core/navigation'&&in_array('ed-member-navigation',explode(' ',$b['attrs']['className']??''),true)){$b['attrs']['ref']=(int)$nav->ID;$b['innerBlocks']=[];$b['innerContent']=[];$b['innerHTML']='';}elseif(!empty($b['innerBlocks']))$attach($b['innerBlocks']);}};$attach($blocks);$content=serialize_blocks($blocks);
   if(!$partId){$partId=wp_insert_post(wp_slash(['post_type'=>'wp_template_part','post_status'=>'publish','post_name'=>'member-menu','post_title'=>'Member Menu','post_content'=>$content,'meta_input'=>['_ed_member_menu_run_id'=>$run]]),true);if(is_wp_error($partId)||!$partId)fail('Could not create member template part.');}
   $themeResult=wp_set_object_terms($partId,get_stylesheet(),'wp_theme');$areaResult=wp_set_object_terms($partId,'uncategorized','wp_template_part_area');if(is_wp_error($themeResult)||is_wp_error($areaResult))fail('Could not assign member template part theme/area.');
   if(get_post_field('post_content',$partId)!==$content)fail('Member template part verification failed.');
   $state['destination_id']=(int)$partId;$state['status']='completed';$state['completed']=time();$h[self::ID]=$state;lock_check($token);save_history($h);return 'Member Access navigation and editable Member Menu template part installed.';
  }finally{lock_release($token);}
 }
}
foreach(['preview','run'] as $mode)add_action('admin_post_edm_member_menu_'.$mode,function()use($mode){if(!current_user_can('manage_options'))wp_die('Unauthorized');check_admin_referer('edm_member_menu_'.$mode);try{if($mode==='run'&&empty($_POST['backup']))fail('Backup acknowledgment required.');$result=$mode==='preview'?MemberMenuMigration::preview():MemberMenuMigration::run();update_option('edm_member_menu_notice',is_string($result)?$result:'Preview ready.',false);}catch(\Throwable $e){update_option('edm_member_menu_notice',$e->getMessage(),false);}wp_safe_redirect(admin_url('tools.php?page=eyedonation-migrations#edm-member-menu'));exit;});
add_action('admin_footer',function(){if(($_GET['page']??'')!=='eyedonation-migrations'||!current_user_can('manage_options'))return;echo '<div class="wrap" id="edm-member-menu"><h2>043 — Native member menu</h2><p>Creates the Member Access navigation and Member Menu template part. Existing customized parts and subsequent edits are preserved. Status: '.esc_html(history()[MemberMenuMigration::ID]['status']??'pending').'</p><p>'.esc_html(get_option('edm_member_menu_notice','')).'</p>';foreach(['preview'=>'Preview member menu','run'=>'Install / Resume member menu'] as $mode=>$label){echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="edm_member_menu_'.$mode.'">';wp_nonce_field('edm_member_menu_'.$mode);if($mode==='run')echo '<p><label><input type="checkbox" name="backup" value="1" required> I have a recoverable backup.</label></p>';submit_button($label);echo '</form>';}echo '</div>';});
