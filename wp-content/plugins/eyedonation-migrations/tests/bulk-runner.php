<?php
namespace EyeDonation\SiteMigrations;
define('ABSPATH',__DIR__);
$GLOBALS['options']=[];$GLOBALS['posts']=[11=>'Original content'];$GLOBALS['meta']=[11=>['_wp_page_template'=>'old','tcb_editor_enabled'=>'1','tcb_editor_disabled'=>'']];
function get_option($k,$default=false){return $GLOBALS['options'][$k]??$default;}
function update_option($k,$v,...$args){$GLOBALS['options'][$k]=$v;return true;}
function get_current_user_id(){return 7;}
function wp_generate_uuid4(){return 'test-job';}
function fail($message){throw new \RuntimeException($message);}
function history(){return get_option('history',[]);}
function save_history($h){update_option('history',$h);}
function current_user_can(...$args){return empty($GLOBALS['deny']);}
function get_post_field($field,$id){return $GLOBALS['posts'][$id];}
function get_post_meta($id,$field,$single){return $GLOBALS['meta'][$id][$field]??'';}
function update_post_meta($id,$field,$value){$GLOBALS['meta'][$id][$field]=$value;}
function delete_post_meta($id,$field){unset($GLOBALS['meta'][$id][$field]);}
function wp_save_post_revision($id){$GLOBALS['revisions']=($GLOBALS['revisions']??0)+1;}
function wp_slash($v){return $v;}
function wp_update_post($data,$error){if(!empty($GLOBALS['crash']))throw new \RuntimeException('Simulated interruption');$GLOBALS['posts'][$data['ID']]=$data['post_content'];return $data['ID'];}
function is_wp_error($v){return false;}
function lock_acquire(){if(!empty($GLOBALS['lock']))fail('Locked');$GLOBALS['lock']=true;return 'token';}
function lock_release($token){$GLOBALS['lock']=false;}
function lock_check($token){if(empty($GLOBALS['lock']))fail('Lost lock');}
function check($ok,$label){if(!$ok)fail($label);}
require dirname(__DIR__).'/bulk-runner.php';
$steps=[['id'=>'one','label'=>'One'],['id'=>'two','label'=>'Two'],['id'=>'dependent','label'=>'Dependent','depends'=>'two'],['id'=>'three','label'=>'Three']];
$attempts=[];$execute=function($step)use(&$attempts){$attempts[$step['id']]=($attempts[$step['id']]??0)+1;if($step['id']==='two'&&$attempts['two']===1)fail('Missing form');return 'Done';};
$job=bulk_begin($steps);
for($i=0;$i<5;$i++)$job=bulk_advance($job,$steps,$execute);
check($job['status']==='needs_attention','Failures must be visible');
check($job['results']['three']['status']==='completed','Independent work must continue');
check(!isset($attempts['dependent']),'Dependent step must not run after failure');
check($job['results']['two']['message']==='Missing form','Specific error must survive');
$job=bulk_begin($steps);
for($i=0;$i<3;$i++)$job=bulk_advance($job,$steps,$execute);
check($job['status']==='completed','Resume must finish');
check($attempts['one']===1&&$attempts['three']===1&&$attempts['two']===2,'Completed steps must not repeat');
$pendingSteps=[['id'=>'index','label'=>'Search']];$loops=0;
$index=function()use(&$loops){$loops++;return $loops<3?['pending'=>true,'message'=>'Indexing']: 'Indexed';};
$job=['id'=>'index','results'=>[],'status'=>'running'];
for($i=0;$i<4;$i++)$job=bulk_advance($job,$pendingSteps,$index);
check($job['status']==='completed'&&$loops===3,'Long indexing must remain pending until complete');
$record=['id'=>11,'type'=>'page','slug'=>'','content'=>'New editable blocks','meta'=>['_wp_page_template'=>'homepage','tcb_editor_enabled'=>'','tcb_editor_disabled'=>'1']];
$GLOBALS['crash']=true;
try{bulk_records('home',[$record]);fail('Expected interruption');}catch(\RuntimeException $e){check($e->getMessage()==='Simulated interruption','Wrong failure');}
check(!$GLOBALS['lock'],'Interruption must release lease');
check(history()['home']['records'][0]['before']==='Original content','Original content must be backed up before write');
$GLOBALS['crash']=false;bulk_records('home',[$record]);
check($GLOBALS['posts'][11]==='New editable blocks'&&get_post_meta(11,'_wp_page_template',true)==='homepage','Resume must install content and settings');
check(history()['home']['records'][0]['meta_before']['tcb_editor_enabled']==='1','Builder flags must be backed up');
$GLOBALS['posts'][11]='Editorial change';bulk_records('home',[$record]);
check($GLOBALS['posts'][11]==='Editorial change','Repeat run must preserve edits');
$GLOBALS['posts'][11]='Original';$record['content']='Another payload';$GLOBALS['crash']=true;
try{bulk_records('conflict',[$record]);}catch(\RuntimeException $e){}
$GLOBALS['posts'][11]='Concurrent edit';$GLOBALS['crash']=false;
try{bulk_records('conflict',[$record]);fail('Expected conflict');}catch(\RuntimeException $e){check(strpos($e->getMessage(),'Content changed')!==false,'Concurrent edit must stop');}
check($GLOBALS['posts'][11]==='Concurrent edit','Concurrent edit must remain untouched');
echo "PASS: automatic ordering, specific errors, independent continuation, dependency protection, resume, completed-step preservation, search batches, before-images, interrupted writes and concurrent edits.\n";
