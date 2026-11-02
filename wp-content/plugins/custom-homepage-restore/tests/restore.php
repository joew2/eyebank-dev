<?php
/** Build the current homepage using core's block/HTML parser without database/media lookups. */
define('ABSPATH',dirname(__DIR__,4).'/');define('WPINC','wp-includes');define('OBJECT','OBJECT');
require ABSPATH.WPINC.'/plugin.php';
foreach(['class-wp-block-parser-block.php','class-wp-block-parser-frame.php','class-wp-block-parser.php','blocks.php','kses.php','compat-utf8.php','utf8.php','class-wp-token-map.php','html-api/html5-named-character-references.php','html-api/class-wp-html-attribute-token.php','html-api/class-wp-html-span.php','html-api/class-wp-html-doctype-info.php','html-api/class-wp-html-text-replacement.php','html-api/class-wp-html-decoder.php','html-api/class-wp-html-tag-processor.php'] as $file)require ABSPATH.WPINC.'/'.$file;
function get_theme_file_path($p){if(!empty($GLOBALS['missing_asset'])&&str_contains($p,'main_hero_photo_v4.jpg'))return '/missing/homepage-image.jpg';return ABSPATH.'wp-content/themes/eyedonation'.$p;}
function get_theme_file_uri($p){return 'https://example.test/wp-content/themes/eyedonation'.$p;}
function home_url($p='/'){return 'https://example.test'.$p;}
function wp_json_encode($v,$flags=0){return json_encode($v,$flags);}
function esc_url($v){return htmlspecialchars($v,ENT_QUOTES);}
function esc_attr($v){return htmlspecialchars($v,ENT_QUOTES);}
function add_query_arg($k,$v,$url){return $url.'?'.urlencode($k).'='.urlencode($v);}
function wp_parse_url($url,$part=-1){return parse_url($url,$part);}
function _doing_it_wrong(...$args){throw new RuntimeException('Core parser usage error');}

function esc_html($v){return htmlspecialchars($v,ENT_QUOTES);}
function wp_strip_all_tags($value){return strip_tags($value);}
function get_option($key,$default=false){return $GLOBALS['options'][$key]??$default;}
function get_post_meta($id,$key,$single){return $GLOBALS['meta'][$id][$key]??'';}
function shortcode_exists($tag){return true;}
function shortcode_parse_atts($text){preg_match_all('/(\w+)="([^"]*)"/',$text,$m,PREG_SET_ORDER);$a=[];foreach($m as $row)$a[$row[1]]=$row[2];return $a;}
class GFAPI {static function get_form($id){return (in_array($id,[67,80])||($id===85&&empty($GLOBALS['missing_signup_form'])))?['id'=>$id]:false;}}
function check($ok,$message){if(!$ok)throw new RuntimeException($message);}
function current_user_can(...$args){return true;}
function update_option($key,$value,...$args){$GLOBALS['options'][$key]=$value;}
function get_current_user_id(){return 7;}
function wp_create_nonce($a){return 'test';}
function plugins_url($path,$file){return $path;}
function wp_enqueue_script(...$args){}
function wp_add_inline_script(...$args){}
require dirname(__DIR__).'/custom-homepage-restore.php';
function is_multisite(){return false;}
function get_stylesheet(){return 'eyedonation';}
function wp_generate_uuid4(){return 'lease-'.(++$GLOBALS['sequence']);}
function add_option($key,$value,...$args){if(array_key_exists($key,$GLOBALS['options']))return false;$GLOBALS['options'][$key]=$value;return true;}
function maybe_serialize($v){return serialize($v);}
function wp_cache_delete(...$args){}
class RepairDBFixture {
 public $options="wp_options";
 function prepare($query,...$args){return [$query,$args];}
 function query($prepared){[$query,$args]=$prepared;[$key,$expected]=$args;if(isset($GLOBALS['options'][$key])&&serialize($GLOBALS['options'][$key])===$expected){unset($GLOBALS['options'][$key]);return 1;}return 0;}
}
function get_post($id){return isset($GLOBALS['posts'][$id])?(object)(['ID'=>$id]+$GLOBALS['posts'][$id]):null;}
function get_page_by_path($slug,...$args){foreach($GLOBALS['posts'] as $id=>$p)if($p['post_type']==='page'&&$p['post_name']===$slug)return (object)(['ID'=>$id]+$p);return null;}
function get_post_field($field,$id){return $GLOBALS['posts'][$id][$field]??'';}
function get_posts($args){$ids=[];foreach($GLOBALS['posts'] as $id=>$p)if($p['post_type']===$args['post_type']&&($GLOBALS['meta'][$id][$args['meta_key']]??null)===$args['meta_value'])$ids[]=$id;return $ids;}
function get_block_template($name,$type){$slug=explode('//',$name)[1];foreach($GLOBALS['posts'] as $id=>$p)if($p['post_type']===$type&&$p['post_name']===$slug)return (object)['wp_id'=>$id,'content'=>$p['post_content']];return null;}
function wp_insert_post($data,$error){$id=++$GLOBALS['sequence'];$GLOBALS['posts'][$id]=$data;$GLOBALS['meta'][$id]=$data['meta_input']??[];return $id;}
function wp_update_post($data,$error){if(!empty($GLOBALS['crash'])&&$data['ID']===11)throw new RuntimeException('Simulated interruption');$GLOBALS['posts'][$data['ID']]['post_content']=$data['post_content'];return $data['ID'];}
function wp_slash($data){return $data;}
function is_wp_error($value){return false;}
function wp_save_post_revision($id){$GLOBALS['revisions'][]=$id;}
function update_post_meta($id,$key,$value){$GLOBALS['meta'][$id][$key]=$value;}
function delete_post_meta($id,$key){unset($GLOBALS['meta'][$id][$key]);}
function wp_set_object_terms($id,$terms,$taxonomy){if($GLOBALS['posts'][$id]['post_type']==='wp_navigation')throw new RuntimeException('Navigation must not receive theme taxonomy');$GLOBALS['terms'][$id][$taxonomy]=$terms;return [1];}
function wp_get_custom_css($theme){return $GLOBALS['css'];}
function wp_update_custom_css_post($css,$args){$GLOBALS['css']=$css;return (object)['ID'=>1000];}


function untrailingslashit($url){return rtrim($url,'/\\');}
$GLOBALS['sequence']=100;$GLOBALS['wpdb']=new RepairDBFixture();
$GLOBALS['options']=['show_on_front'=>'page','page_on_front'=>11,'edm_history'=>['old_home'=>['status'=>'completed']]];
$GLOBALS['posts']=[
 11=>['post_type'=>'page','post_name'=>'eye-bank-home','post_status'=>'publish','post_title'=>'Home','post_content'=>'Broken homepage'],
 21=>['post_type'=>'wp_template_part','post_name'=>'header','post_content'=>'Existing corrected portal menu','post_title'=>'Header'],
 22=>['post_type'=>'wp_template_part','post_name'=>'footer','post_content'=>'Existing footer','post_title'=>'Footer'],
 31=>['post_type'=>'wp_template','post_name'=>'front-page','post_content'=>'Wrong static layout','post_title'=>'Front Page'],
 32=>['post_type'=>'wp_template','post_name'=>'homepage','post_content'=>'Wrong homepage display','post_title'=>'Homepage'],
 33=>['post_type'=>'page','post_name'=>'other-page','post_status'=>'publish','post_content'=>'Keep this page','post_title'=>'Other'],
];
$GLOBALS['meta']=[11=>['_wp_page_template'=>'legacy.php','tcb_editor_enabled'=>'1','other_setting'=>'preserve']];
$steps=CWS\HomepageRestore\bulk_steps();check(count($steps)===1,'Homepage-only job expected');
$job=CWS\HomepageRestore\bulk_begin($steps);$firstID=$job['id'];$job=CWS\HomepageRestore\bulk_advance($job,$steps);$job=CWS\HomepageRestore\bulk_advance($job,$steps);
check($job['status']==='completed','First restore failed: '.json_encode($job['results']));
$content=$GLOBALS['posts'][11]['post_content'];
check(str_contains($content,'main_hero_photo_v4.jpg')&&str_contains($content,'homepage-support-v4/support-desktop.jpg'),'Current design images missing');
check(!str_contains($content,'{{')&&!str_contains($content,'example.test/wp-content/uploads'),'Unresolved content or legacy image dependency');
check(str_contains($GLOBALS['posts'][31]['post_content'],'wp:post-content')&&str_contains($GLOBALS['posts'][32]['post_content'],'wp:post-content'),'Display template overrides not repaired');
check($GLOBALS['posts'][21]['post_content']==='Existing corrected portal menu'&&$GLOBALS['posts'][22]['post_content']==='Existing footer'&&$GLOBALS['posts'][33]['post_content']==='Keep this page','Unrelated records changed');
check($GLOBALS['meta'][11]['_wp_page_template']==='homepage'&&$GLOBALS['meta'][11]['tcb_editor_disabled']==='1'&&$GLOBALS['meta'][11]['other_setting']==='preserve','Homepage metadata not reconciled');
check(CWS\HomepageRestore\history()['homepage_restore_'.$firstID]['records'][0]['before']==='Broken homepage','Original homepage not backed up');
check($GLOBALS['options']['edm_history']===['old_home'=>['status'=>'completed']],'Earlier migration history changed');
$GLOBALS['posts'][11]['post_content']='Second accident';
$job=CWS\HomepageRestore\bulk_begin($steps);check($job['id']!==$firstID,'New restore must have independent history');$secondID=$job['id'];$job=CWS\HomepageRestore\bulk_advance($job,$steps);$job=CWS\HomepageRestore\bulk_advance($job,$steps);
check($job['status']==='completed'&&$GLOBALS['posts'][11]['post_content']===$content,'A second restore was incorrectly skipped');
check(CWS\HomepageRestore\history()['homepage_restore_'.$secondID]['records'][0]['before']==='Second accident','Second restore backup missing');
$GLOBALS['posts'][11]['post_content']='Third accident';$GLOBALS['crash']=true;
$job=CWS\HomepageRestore\bulk_begin($steps);$thirdID=$job['id'];$job=CWS\HomepageRestore\bulk_advance($job,$steps);$job=CWS\HomepageRestore\bulk_advance($job,$steps);check($job['status']==='needs_attention','Interruption not reported');
$GLOBALS['crash']=false;$job=CWS\HomepageRestore\bulk_begin($steps);check($job['id']===$thirdID,'Resume discarded the interrupted run');$job=CWS\HomepageRestore\bulk_advance($job,$steps);$job=CWS\HomepageRestore\bulk_advance($job,$steps);
check($job['status']==='completed'&&$GLOBALS['posts'][11]['post_content']===$content,'Interrupted restore did not resume');
$job=CWS\HomepageRestore\bulk_begin($steps);$GLOBALS['options']['page_on_front']=33;
$job=CWS\HomepageRestore\bulk_advance($job,$steps);check($job['results']['homepage_restore']['status']==='failed'&&$GLOBALS['posts'][33]['post_content']==='Keep this page','Changed target not protected');
$GLOBALS['options']['page_on_front']=11;$GLOBALS['missing_asset']=true;
try{CWS\HomepageRestore\homepage_content();throw new RuntimeException('Expected asset error');}catch(RuntimeException $e){check(str_contains($e->getMessage(),'required homepage theme asset'),'Missing asset not reported');}$GLOBALS['missing_asset']=false;
ob_start();CWS\HomepageRestore\bulk_screen();$screen=ob_get_clean();check(substr_count($screen,'type="checkbox"')===1&&substr_count($screen,'<button')===1&&str_contains($screen,'Home (page 11)'),'Single restore button or target display missing');file_put_contents('/private/tmp/cwshr-screen.html',$screen);
echo "PASS: latest bundled homepage, selected page ID, template overrides, header/footer/other-page preservation, independent history, fresh backups on repeated restores, interrupted resume, changed-target and missing-asset protection, single-button screen.\n";
