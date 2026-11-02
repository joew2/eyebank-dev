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
require dirname(__DIR__).'/custom-mobile-donor-panel.php';
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



$GLOBALS['sequence']=100;$GLOBALS['wpdb']=new RepairDBFixture();$GLOBALS['options']=[];$GLOBALS['css']='.existing{color:red}';$GLOBALS['posts']=[11=>['post_type'=>'page','post_content'=>'Keep all homepage content']];$GLOBALS['meta']=[];
$steps=CWS\MobileDonorPanel\bulk_steps();check(count($steps)===1,'Only one CSS step expected');CWS\MobileDonorPanel\bulk_execute($steps[0]);
check(str_contains($GLOBALS['css'],'.existing{color:red}')&&str_contains($GLOBALS['css'],'transform:scale(.8)')&&str_contains($GLOBALS['css'],'bottom:12px'),'Existing styles lost or mobile fix missing');
check($GLOBALS['posts'][11]['post_content']==='Keep all homepage content','Homepage content was changed');
check(CWS\MobileDonorPanel\history()['mobile_donor_panel_v1']['before']==='.existing{color:red}','Original CSS not backed up');
$first=$GLOBALS['css'];CWS\MobileDonorPanel\bulk_execute($steps[0]);check($GLOBALS['css']===$first&&substr_count($first,'CWS MOBILE DONOR PANEL START')===1,'Retry duplicated CSS');
$GLOBALS['css'].="\n.later{color:blue}";CWS\MobileDonorPanel\bulk_execute($steps[0]);check(str_contains($GLOBALS['css'],'.later{color:blue}'),'Completed retry lost later edits');
ob_start();CWS\MobileDonorPanel\bulk_screen();$html=ob_get_clean();check(substr_count($html,'type="checkbox"')===1&&substr_count($html,'<button')===1,'One-button screen expected');file_put_contents('/private/tmp/cwsmdp-screen.html',$html);
echo "PASS: CSS-only scope, original before-image, unrelated CSS/page preservation, no duplicate migration, repeat-run edit preservation and single-button admin screen.\n";
