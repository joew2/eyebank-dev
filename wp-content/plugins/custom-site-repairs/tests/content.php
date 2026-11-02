<?php
/** Build the current homepage using core's block/HTML parser without database/media lookups. */
define('ABSPATH',dirname(__DIR__,4).'/');define('WPINC','wp-includes');define('OBJECT','OBJECT');
require ABSPATH.WPINC.'/plugin.php';
foreach(['class-wp-block-parser-block.php','class-wp-block-parser-frame.php','class-wp-block-parser.php','blocks.php','kses.php','compat-utf8.php','utf8.php','class-wp-token-map.php','html-api/html5-named-character-references.php','html-api/class-wp-html-attribute-token.php','html-api/class-wp-html-span.php','html-api/class-wp-html-doctype-info.php','html-api/class-wp-html-text-replacement.php','html-api/class-wp-html-decoder.php','html-api/class-wp-html-tag-processor.php'] as $file)require ABSPATH.WPINC.'/'.$file;
function get_theme_file_path($p){return ABSPATH.'wp-content/themes/eyedonation'.$p;}
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
require dirname(__DIR__).'/custom-site-repairs.php';
require get_theme_file_path('/inc/sign-up.php');
$header=file_get_contents(get_theme_file_path('/parts/header.html'));
$repaired=CWS\SiteRepairs\header_links($header);
check(substr_count($repaired,'>Transplant Portal<')===1,'Transplant link missing/duplicated');
check(substr_count($repaired,'>Research Portal<')===1,'Research link missing/duplicated');
check(str_contains($repaired,'https://www.eyecanseeagain.org/'),'Existing utility link lost');
check(str_contains($repaired,'ed-primary-navigation'),'Primary navigation lost');
check(CWS\SiteRepairs\header_links($repaired)===$repaired,'Header patch is not idempotent');
$before=parse_blocks($header);$after=parse_blocks($repaired);
check($before[1]===$after[1],'Primary navigation changed');
file_put_contents('/private/tmp/cwsr-header.html',$repaired);
file_put_contents('/private/tmp/cwsr-header-blocks.json',json_encode(parse_blocks($repaired)));
$embed='<script id="original" src="https://app.hipaatizer.com/shared/hipaatizer-form-renderer.js"></script><script>new Hipaatizer("00000000-0000-0000-0000-000000000001", true, "custom").render();</script>';
$page=(object)['ID'=>11,'post_content'=>'<!-- wp:html -->'.$embed.'<!-- /wp:html -->'];
check(str_contains(CWS\SiteRepairs\transplant_embed($page),'true, "custom"'),'Existing transplant configuration changed');
try{CWS\SiteRepairs\transplant_embed((object)['ID'=>11,'post_content'=>'<iframe src="https://example.test/other-form"></iframe>']);throw new RuntimeException('Expected fail');}catch(RuntimeException $e){check(str_contains($e->getMessage(),'left intact'),'Unknown content was not protected');}
check(str_contains(CWS\SiteRepairs\transplant_embed(null),'39830e66-a85a-480b-a84b-def1bb893104'),'Original fallback lost');
$code='[gravityform id="80" title="false" description="false"]';
check(CWS\SiteRepairs\research_shortcode((object)['ID'=>11,'post_content'=>$code])===$code,'Destination research form not preserved');
check(str_contains(CWS\SiteRepairs\research_shortcode(null),'id="67"'),'Missing-page original form fallback lost');
try{CWS\SiteRepairs\research_shortcode((object)['ID'=>11,'post_content'=>'[gravityform id="99"]']);throw new RuntimeException('Expected fail');}catch(RuntimeException $e){check(str_contains($e->getMessage(),'restore research request form 99'),'Missing form not reported');}
$steps=CWS\SiteRepairs\bulk_steps();check(count($steps)===7,'Unexpected repair scope');
ob_start();CWS\SiteRepairs\bulk_screen();$screen=ob_get_clean();
check(substr_count($screen,'type="checkbox"')===1&&substr_count($screen,'<button')===1,'Screen must have one checkbox/button');
file_put_contents('/private/tmp/cwsr-screen.html',$screen);
echo "PASS: seven-step scope, header links and sizing class, untouched primary navigation, no duplicates on retry, original form configuration, missing/unknown form handling, single-button screen.\n";

// Execute all six repairs against an isolated in-memory WordPress fixture.
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
function wp_update_post($data,$error){$GLOBALS['posts'][$data['ID']]['post_content']=$data['post_content'];return $data['ID'];}
function wp_slash($data){return $data;}
function is_wp_error($value){return false;}
function wp_save_post_revision($id){$GLOBALS['revisions'][]=$id;}
function update_post_meta($id,$key,$value){$GLOBALS['meta'][$id][$key]=$value;}
function delete_post_meta($id,$key){unset($GLOBALS['meta'][$id][$key]);}
function wp_set_object_terms($id,$terms,$taxonomy){$GLOBALS['terms'][$id][$taxonomy]=$terms;return [1];}
function wp_get_custom_css($theme){return $GLOBALS['css'];}
function wp_update_custom_css_post($css,$args){$GLOBALS['css']=$css;return (object)['ID'=>1000];}
$GLOBALS['sequence']=100;$GLOBALS['wpdb']=new RepairDBFixture();$GLOBALS['options']=[];$GLOBALS['css']='.unrelated{color:red}';
$GLOBALS['posts']=[
 11=>['post_type'=>'page','post_name'=>'user-downloads','post_content'=>'Original downloads','post_title'=>'User Downloads'],
 12=>['post_type'=>'page','post_name'=>'request-transplant-tissue','post_content'=>$embed,'post_title'=>'Request Transplant Tissue.'],
 13=>['post_type'=>'page','post_name'=>'request-research-tissue','post_content'=>$code,'post_title'=>'Request Research Tissue.'],
 387956=>['post_type'=>'page','post_name'=>'sign-up','post_status'=>'publish','post_content'=>'Original signup placeholder','post_title'=>'Sign Up'],
 21=>['post_type'=>'wp_template_part','post_name'=>'header','post_content'=>$header,'post_title'=>'Header'],
];
$GLOBALS['meta']=[11=>['custom_access'=>'preserve','tcb_editor_enabled'=>'1'],12=>['custom_access'=>'preserve'],13=>['custom_access'=>'preserve']];
$signupStep=CWS\SiteRepairs\bulk_steps()[6];
$GLOBALS['missing_signup_form']=true;
try{CWS\SiteRepairs\bulk_execute($signupStep);throw new RuntimeException('Expected form error');}catch(RuntimeException $e){check(str_contains($e->getMessage(),'mailing-list form 85'),'Missing signup form not reported');}
check($GLOBALS['posts'][387956]['post_content']==='Original signup placeholder','Missing form changed target content');
$GLOBALS['missing_signup_form']=false;
$GLOBALS['posts'][387956]['post_name']='different-page';
try{CWS\SiteRepairs\bulk_execute($signupStep);throw new RuntimeException('Expected target error');}catch(RuntimeException $e){check(str_contains($e->getMessage(),'sign-up permalink'),'Wrong target not rejected');}
$GLOBALS['posts'][387956]['post_name']='sign-up';
foreach(CWS\SiteRepairs\bulk_steps() as $step)CWS\SiteRepairs\bulk_execute($step);
check(str_contains($GLOBALS['posts'][11]['post_content'],'[filemanager foldername="*"]'),'Downloads shortcode lost');
check(str_contains($GLOBALS['posts'][11]['post_content'],'ed-converted-guests')&&str_contains($GLOBALS['posts'][11]['post_content'],'ed-converted-members'),'Downloads audience groups missing');
check(str_contains($GLOBALS['posts'][12]['post_content'],'true, "custom"'),'Original Hipaatizer settings changed');
check(str_contains($GLOBALS['posts'][13]['post_content'],$code),'Original research form changed');
foreach([11,12,13] as $id){check($GLOBALS['meta'][$id]['custom_access']==='preserve','Unrelated page metadata changed');check($GLOBALS['meta'][$id]['_wp_page_template']==='cws-repair-page','Editable template not assigned');}
check(str_contains($GLOBALS['posts'][21]['post_content'],'>Transplant Portal<'),'Saved header not updated');
check(str_contains($GLOBALS['css'],'.unrelated{color:red}')&&str_contains($GLOBALS['css'],'CWS SITE REPAIRS START'),'Unrelated CSS lost or sizing missing');
check(count(CWS\SiteRepairs\history())===7,'Repair history incomplete');
$GLOBALS['posts'][11]['post_content']='Later editorial edit';
CWS\SiteRepairs\bulk_execute(CWS\SiteRepairs\bulk_steps()[3]);
check($GLOBALS['posts'][11]['post_content']==='Later editorial edit','Completed repair overwrote an editorial edit');
unset($GLOBALS['posts'][13]);
CWS\SiteRepairs\bulk_execute(['id'=>'missing_research','kind'=>'research']);
$created=get_page_by_path('request-research-tissue');check($created&&$created->post_title==='Request Research Tissue'&&str_contains($created->post_content,'id="67"'),'Missing research page not restored');
echo "PASS: all seven real repair handlers, existing page IDs and metadata, current embed/form settings, Additional CSS preservation, history/before-images, repeat-run edits and missing-page creation.\n";

check($GLOBALS['posts'][387956]['post_content']===ed_sign_up_content(),'Signup must match the original migration payload');
check(str_contains($GLOBALS['posts'][387956]['post_content'],'[gravityform id="85" title="false"]'),'Mailing-list shortcode changed');
check($GLOBALS['meta'][387956]['_wp_page_template']==='sign-up','Original signup template not assigned');
check(CWS\SiteRepairs\history()['repair_signup_v1']['records'][1]['before']==='Original signup placeholder','Signup original not backed up');
$GLOBALS['posts'][387956]['post_content']='Later signup edit';CWS\SiteRepairs\bulk_execute($signupStep);
check($GLOBALS['posts'][387956]['post_content']==='Later signup edit','Completed signup update overwrote edits');
echo "PASS: exact target page 387956, original signup content/template/form 85, missing-form and wrong-target protection, backup and repeat-run edit preservation.\n";
