<?php
/** Isolated runner behavior tests; no WordPress database or network writes. */
namespace EyeDonation\SiteMigrations {
define('ABSPATH',__DIR__);
$fixture=sys_get_temp_dir().'/edm-editable-test-'.bin2hex(random_bytes(5));mkdir($fixture.'/assets/page-conversions',0777,true);mkdir($fixture.'/templates');
file_put_contents($fixture.'/assets/page-conversions/test-page.html','<!-- wp:paragraph --><p>Approved copy</p><!-- /wp:paragraph -->');
foreach(['test-page','test-page-layout'] as $slug)file_put_contents($fixture.'/templates/'.$slug.'.html','test template');
$GLOBALS['options']=[];$GLOBALS['fields']=['post_content'=>'Original copy','_wp_page_template'=>'old.php','tcb_editor_enabled'=>'1','tcb_editor_disabled'=>''];
function get_theme_file_path($path){return $GLOBALS['fixture'].$path;}
function wp_json_encode($v,$flags=0){return json_encode($v,$flags);}
function ed_converted_page_content($slug){return file_get_contents(get_theme_file_path('/assets/page-conversions/'.$slug.'.html'));}
function preflight(){}
function current_user_can(...$args){return true;}
function get_current_user_id(){return 7;}
function shortcode_exists($code){return false;}
function get_page_by_path($slug,...$args){return $slug==='test-page'?(object)['ID'=>11]:null;}
function get_post_field($field,$id){return $GLOBALS['fields'][$field];}
function get_post_meta($id,$field,$single){return $GLOBALS['fields'][$field]??'';}
function update_post_meta($id,$field,$value){$GLOBALS['fields'][$field]=$value;}
function delete_post_meta($id,$field){unset($GLOBALS['fields'][$field]);}
function wp_save_post_revision($id){$GLOBALS['revisions']=($GLOBALS['revisions']??0)+1;}
function get_the_title($id){return 'Test page';}
function wp_update_post($values,$error){if(!empty($GLOBALS['crash']))throw new \RuntimeException('Simulated interruption');$GLOBALS['fields']['post_content']=$values['post_content'];return 11;}
function wp_slash($v){return $v;}
function is_wp_error($v){return false;}
function get_option($key,$default=false){return $GLOBALS['options'][$key]??$default;}
function update_option($key,$value,...$args){$GLOBALS['options'][$key]=$value;}
function add_option($key,$value,...$args){if(isset($GLOBALS['options'][$key]))return false;$GLOBALS['options'][$key]=$value;return true;}
function history(){return get_option('history',[]);}
function save_history($v){update_option('history',$v);}
function fingerprint($v){return hash('sha256',json_encode($v));}
function wp_generate_uuid4(){return 'test-run';}
function lock_acquire(){if(!empty($GLOBALS['locked']))throw new \RuntimeException('Locked');$GLOBALS['locked']=true;return 'token';}
function lock_check($token){if(empty($GLOBALS['locked']))throw new \RuntimeException('Lease lost');}
function lock_release($token){$GLOBALS['locked']=false;}
function fail($message){throw new \RuntimeException($message);}
function check($condition,$message){if(!$condition)throw new \RuntimeException($message);}
function rejects($fn,$needle){try{$fn();}catch(\RuntimeException $e){check(str_contains($e->getMessage(),$needle),'Unexpected error: '.$e->getMessage());return;}throw new \RuntimeException('Expected rejection: '.$needle);}
define('DAY_IN_SECONDS',86400);
require dirname(__DIR__).'/migrations/support/EditablePages.php';
try{
 $entry=['number'=>999,'slug'=>'test-page','title'=>'Test Page','dependencies'=>[]];$m=new EditablePageMigration($entry,__FILE__);
 $m->preview();$GLOBALS['fields']['post_content']='Edited since preview';rejects(fn()=>$m->run(),'Page edited since preview');check($GLOBALS['fields']['_wp_page_template']==='old.php','Conflict changed template');
 $GLOBALS['fields']['post_content']='Original copy';$m->preview();$GLOBALS['crash']=true;rejects(fn()=>$m->run(),'Simulated interruption');check($GLOBALS['fields']['_wp_page_template']==='test-page','First operation was not recorded');check(history()[$m->id()]['status']==='running','Interrupted state missing');
 $GLOBALS['crash']=false;$m->run();check(history()[$m->id()]['status']==='completed','Resume failed');check($GLOBALS['fields']['post_content']===ed_converted_page_content('test-page'),'Payload mismatch');check(get_option('edm_selected_test-page_backup')['operations'][1]['before']==='Original copy','Original backup missing');
 $GLOBALS['fields']['post_content']='Later editorial copy';$m->run();check($GLOBALS['fields']['post_content']==='Later editorial copy','Repeat run overwrote editorial content');
 $bad=new EditablePageMigration(array_merge($entry,['dependencies'=>['filemanager']]),__FILE__);rejects(fn()=>$bad->plan(),'Required shortcode unavailable');
 check(empty($GLOBALS['locked']),'Lease not released');echo "PASS: preview conflicts, backup, interruption/resume, repeat-run preservation, dependency checks and lease cleanup.\n";
}finally{
 foreach(glob($fixture.'/templates/*') as $file)unlink($file);unlink($fixture.'/assets/page-conversions/test-page.html');rmdir($fixture.'/templates');rmdir($fixture.'/assets/page-conversions');rmdir($fixture.'/assets');rmdir($fixture);
}

}
namespace { function ed_converted_page_content($slug){return \EyeDonation\SiteMigrations\ed_converted_page_content($slug);} }
