<?php
/** Real SQL against uniquely named disposable tables. Original WP tables are never written. */
namespace {
if(empty($argv[1]))die("Pass /absolute/path/to/wp-load.php\n");define('SHORTINIT',true);require $argv[1];
$originalPosts=$wpdb->posts;$originalMeta=$wpdb->postmeta;$originalOptions=$wpdb->options;
$suffix=bin2hex(random_bytes(4));$wpdb->posts='eds_posts_'.$suffix;$wpdb->postmeta='eds_meta_'.$suffix;$wpdb->options='eds_options_'.$suffix;
$wpdb->query("CREATE TABLE $wpdb->posts (ID BIGINT PRIMARY KEY,post_type VARCHAR(30),post_status VARCHAR(30),post_password VARCHAR(255) DEFAULT '',post_title TEXT,post_excerpt TEXT,post_content LONGTEXT,post_parent BIGINT DEFAULT 0,post_date DATETIME DEFAULT '2026-01-01 00:00:00')");
$wpdb->query("CREATE TABLE $wpdb->postmeta (meta_id BIGINT AUTO_INCREMENT PRIMARY KEY,post_id BIGINT,meta_key VARCHAR(255),meta_value LONGTEXT)");
$wpdb->query("CREATE TABLE $wpdb->options (option_name VARCHAR(191) PRIMARY KEY,option_value LONGTEXT)");
$fixtureTables=[$wpdb->posts,$wpdb->postmeta,$wpdb->options];
$wpdb->suppress_errors(true);
}
namespace EyeDonation\Search {
function get_post_types($args=[],$output='names'){$types=['post'=>(object)['name'=>'post','public'=>true,'publicly_queryable'=>true,'exclude_from_search'=>false],'page'=>(object)['name'=>'page','public'=>true,'publicly_queryable'=>false,'exclude_from_search'=>false]];return $output==='objects'?$types:array_keys($types);}
function get_post_type_object($type){return get_post_types([],'objects')[$type]??(object)['name'=>$type,'public'=>false,'publicly_queryable'=>false,'exclude_from_search'=>true];}
function get_post($id){global $wpdb;return $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE ID=%d",$id));}
function get_post_ancestors($id){return [];}
function update_post_meta($id,$key,$value){global $wpdb;delete_post_meta($id,$key);$wpdb->insert($wpdb->postmeta,['post_id'=>$id,'meta_key'=>$key,'meta_value'=>(string)$value]);}
function delete_post_meta($id,$key){global $wpdb;$wpdb->delete($wpdb->postmeta,['post_id'=>$id,'meta_key'=>$key]);}
function get_option($key,$default=false){global $wpdb;$raw=$wpdb->get_var($wpdb->prepare("SELECT option_value FROM $wpdb->options WHERE option_name=%s",$key));return $raw===null?$default:unserialize($raw);}
function update_option($key,$value,...$args){global $wpdb;$wpdb->replace($wpdb->options,['option_name'=>$key,'option_value'=>serialize($value)]);}
function add_option($key,$value,...$args){global $wpdb;return (bool)$wpdb->insert($wpdb->options,['option_name'=>$key,'option_value'=>serialize($value)]);}
function delete_option($key){global $wpdb;$wpdb->delete($wpdb->options,['option_name'=>$key]);}
function maybe_serialize($value){return serialize($value);}
function wp_cache_delete(...$args){}
function wp_generate_uuid4(){return bin2hex(random_bytes(12));}
function wp_next_scheduled($hook){return $GLOBALS['scheduled']??false;}
function wp_schedule_single_event(...$args){$GLOBALS['scheduled']=true;}
function wp_clear_scheduled_hook(...$args){$GLOBALS['scheduled']=false;}
function is_admin(){return false;}
require dirname(__DIR__).'/src/Search.php';
function check($ok,$message){if(!$ok)throw new \RuntimeException($message);echo "PASS $message\n";}
function ids_for($term){global $wpdb;$q=new class($term){private $s;function __construct($s){$this->s=$s;}function get($k){return $k==='eds_enhanced'?true:$this->s;}};$where=public_index_where('p');$sql=search_sql('',$q);$sql=str_replace($wpdb->posts.'.','p.',$sql);$ids=$wpdb->get_col("SELECT p.ID FROM $wpdb->posts p WHERE $where $sql ORDER BY p.ID");check(!$wpdb->last_error,'valid search SQL for '.$term.($wpdb->last_error?' — '.$wpdb->last_error:''));return array_map('intval',$ids);}
}
namespace {
use function EyeDonation\Search\{check,ids_for,get_option,update_option,request_rebuild,rebuild_batch,rebuild_status,index_post};
try{
foreach([
[1,'post','publish','','Edwin appointed','','Executive director'],[2,'post','publish','','Appointment','','Edwin body match'],[3,'post','draft','','Edwin draft','','Hidden'],[4,'post','private','','Edwin private','','Hidden'],[5,'post','publish','secret','Edwin protected','','Hidden'],[6,'attachment','inherit','','Edwin attachment','','Hidden'],[7,'revision','inherit','','Edwin revision','','Hidden'],[8,'post','publish','','Other headline','Edwin excerpt','Body'],
] as $row){[$id,$type,$status,$password,$title,$excerpt,$content]=$row;$wpdb->insert($wpdb->posts,['ID'=>$id,'post_type'=>$type,'post_status'=>$status,'post_password'=>$password,'post_title'=>$title,'post_excerpt'=>$excerpt,'post_content'=>$content]);}
for($i=10;$i<45;$i++)$wpdb->insert($wpdb->posts,['ID'=>$i,'post_type'=>'post','post_status'=>'publish','post_title'=>'Other '.$i,'post_excerpt'=>'','post_content'=>'Public text']);
for($i=100;$i<200;$i++)$wpdb->insert($wpdb->posts,['ID'=>$i,'post_type'=>'revision','post_status'=>'inherit','post_title'=>'Edwin revision','post_excerpt'=>'','post_content'=>'Hidden']);
$wpdb->insert($wpdb->postmeta,['post_id'=>3,'meta_key'=>'_eds_public_text','meta_value'=>'Edwin stale draft']);
check(ids_for('Edwin')===[1,8],'title/excerpt matches work with no public index; drafts/private/password/attachments excluded');
check(ids_for('Edwin -appointed')===[8],'negative terms also apply to unindexed titles');
request_rebuild();check(get_option('eds_rebuild_job')['cursor']===0,'new rebuild starts at zero');
$GLOBALS['scheduled']=false;request_rebuild();check($GLOBALS['scheduled'],'existing stalled job reschedules a missing cron tick');
update_option('eds_rebuild_lock',['token'=>'stale','expires'=>time()-10]);
check(rebuild_batch()===false,'expired lease reclaimed and first bounded batch processed');
check(get_option('eds_rebuild_job')['processed']===30,'batch limited to 30 candidates');
check(rebuild_batch()===true,'second batch completes without scanning 100 revisions');
$status=rebuild_status();check(!$status['running']&&$status['missing']===0&&$status['indexed']===$status['total']&&$status['total']===38,'completion verified by public index coverage');
check(ids_for('Edwin')===[1,2,8],'completed index adds body-only matches');
check(ids_for('"Edwin body"')===[2],'quoted body phrase preserved');
check((int)$wpdb->get_var("SELECT COUNT(*) FROM $wpdb->postmeta WHERE post_id=3 AND meta_key='_eds_public_text'")===0,'old ineligible indexed draft cleaned');
request_rebuild();$before=get_option('eds_rebuild_job');update_option('eds_rebuild_lock',['token'=>'active','expires'=>time()+60]);check(rebuild_batch()===false&&get_option('eds_rebuild_job')===$before,'active lease prevents concurrent batch');
\EyeDonation\Search\delete_option('eds_rebuild_lock');
// Use a nonexistent connection-local table pointer for a controlled query failure.
$real=$wpdb->posts;$wpdb->posts='eds_missing_fixture_table';
try{rebuild_batch();throw new \RuntimeException('Expected database error');}catch(\Throwable $e){check(str_contains($e->getMessage(),'database query failed'),'database failure is reported rather than mistaken for completion');}
$wpdb->posts=$real;check(get_option('eds_rebuild_job')['status']==='failed','failed rebuild remains resumable and visible');
echo "PASS all disposable-table search/index regression checks.\n";
}finally{foreach($fixtureTables as $table)$wpdb->query("DROP TABLE IF EXISTS $table");$wpdb->posts=$originalPosts;$wpdb->postmeta=$originalMeta;$wpdb->options=$originalOptions;}
}
