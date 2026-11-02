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
function shortcode_exists($tag){return ($GLOBALS['disabled_shortcode']??'')!==$tag;}
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
require dirname(__DIR__).'/custom-forgot-password-boards.php';
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
function wp_set_object_terms($id,$terms,$taxonomy){if($GLOBALS['posts'][$id]['post_type']==='wp_navigation')throw new RuntimeException('Navigation must not receive theme taxonomy');$GLOBALS['terms'][$id][$taxonomy]=$terms;return [1];}
function wp_get_custom_css($theme){return $GLOBALS['css'];}
function wp_update_custom_css_post($css,$args){$GLOBALS['css']=$css;return (object)['ID'=>1000];}


function get_permalink($page){return home_url('/'.$page->post_name.'/');}
$GLOBALS['sequence']=1000;$GLOBALS['wpdb']=new RepairDBFixture();$GLOBALS['options']=[];$GLOBALS['css']='.existing{color:red}';
$GLOBALS['posts']=[
 380388=>['post_type'=>'page','post_name'=>'password-boards','post_title'=>'forgot password boards','post_status'=>'publish','post_content'=>''],
 670=>['post_type'=>'page','post_name'=>'member','post_title'=>'Member','post_status'=>'publish','post_content'=>'Existing member page'],
 961=>['post_type'=>'page','post_name'=>'enter-new-password','post_title'=>'Enter New Password','post_status'=>'publish','post_content'=>'Existing reset page'],
 733=>['post_type'=>'page','post_name'=>'forgot-password','post_title'=>'Forgot Password','post_status'=>'publish','post_content'=>'Unchanged physician reset'],
];
$GLOBALS['meta']=[380388=>['_wp_page_template'=>'page-forgot-password-boards.php','custom_access'=>'preserve','tcb_editor_enabled'=>'1']];
$steps=CWS\ForgotPasswordBoards\bulk_steps();check(count($steps)===2,'Unexpected scope');
$GLOBALS['disabled_shortcode']='wpv-forgot-password-form';
try{CWS\ForgotPasswordBoards\bulk_execute($steps[0]);throw new RuntimeException('Expected Toolset error');}catch(RuntimeException $e){check(str_contains($e->getMessage(),'Activate Toolset Views'),'Missing Toolset not reported');}
check(CWS\ForgotPasswordBoards\history()===[],'Missing Toolset wrote records');$GLOBALS['disabled_shortcode']='';
$GLOBALS['posts'][961]['post_name']='missing-reset';
try{CWS\ForgotPasswordBoards\bulk_execute($steps[0]);throw new RuntimeException('Expected destination error');}catch(RuntimeException $e){check(str_contains($e->getMessage(),'Required destination page missing'),'Missing destination not reported');}
check(CWS\ForgotPasswordBoards\history()===[],'Missing destination wrote records');$GLOBALS['posts'][961]['post_name']='enter-new-password';
foreach($steps as $step)CWS\ForgotPasswordBoards\bulk_execute($step);
$content=$GLOBALS['posts'][380388]['post_content'];
check(str_contains($content,'[wpv-forgot-password-form redirect_url="https://example.test/member/" redirect_url_fail="https://example.test/member/" reset_password_url="https://example.test/enter-new-password/"]'),'Legacy destination behavior not preserved');
check(!str_contains($content,'wwww.')&&!str_contains($content,'{{')&&str_contains($content,'You are currently logged in.'),'Content unresolved or original notice lost');
check($GLOBALS['meta'][380388]['_wp_page_template']==='forgot-password-boards'&&$GLOBALS['meta'][380388]['custom_access']==='preserve','Template assignment or unrelated metadata changed');
check($GLOBALS['posts'][733]['post_content']==='Unchanged physician reset'&&$GLOBALS['posts'][670]['post_content']==='Existing member page'&&$GLOBALS['posts'][961]['post_content']==='Existing reset page','Unrelated pages changed');
$template=get_block_template('eyedonation//forgot-password-boards','wp_template');check($template&&str_contains($template->content,'wp:post-content'),'Editable template not created');
check(str_contains($GLOBALS['css'],'.existing{color:red}')&&substr_count($GLOBALS['css'],'CWS FORGOT PASSWORD BOARDS START')===1,'Existing CSS lost or migration duplicated');
$backup=CWS\ForgotPasswordBoards\history()['boards_page_v1']['records'][1];check($backup['before']===''&&$backup['meta_before']['_wp_page_template']==='page-forgot-password-boards.php','Original page/template backup missing');
$GLOBALS['posts'][380388]['post_content']='Later editorial change';foreach($steps as $step)CWS\ForgotPasswordBoards\bulk_execute($step);check($GLOBALS['posts'][380388]['post_content']==='Later editorial change','Repeat run overwrote edits');
ob_start();CWS\ForgotPasswordBoards\bulk_screen();$screen=ob_get_clean();file_put_contents('/private/tmp/cwsfpb-screen.html',$screen);check(substr_count($screen,'type="checkbox"')===1&&substr_count($screen,'<button')===1,'Single checkbox/button expected');
echo "PASS: boards page identity, original Toolset shortcode and redirects, typo correction, editable template, unrelated pages/access metadata/CSS, before-images, missing dependencies, repeat-run edits and single-button UI.\n";
