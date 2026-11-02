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
require dirname(__DIR__).'/custom-portal-navigation-fix.php';
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

$header=file_get_contents(get_theme_file_path('/parts/header.html'));
$blocks=parse_blocks($header);
$blocks[0]['attrs']['className'].=' cwsr-header';
$blocks[0]['innerContent'][0]=str_replace('ed-brand','ed-brand cwsr-header',$blocks[0]['innerContent'][0]);
$buttons=&$blocks[0]['innerBlocks'][1];
foreach(['Transplant Portal'=>'/transplant/','Research Portal'=>'/researchers/'] as $label=>$url){$button=parse_blocks('<!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="'.$url.'">'.$label.'</a></div><!-- /wp:button -->')[0];$buttons['innerBlocks'][]=$button;array_splice($buttons['innerContent'],count($buttons['innerContent'])-1,0,[null]);}
unset($buttons);
$wrongHeader=serialize_blocks($blocks);
$GLOBALS['sequence']=100;$GLOBALS['wpdb']=new RepairDBFixture();$GLOBALS['options']=[];$GLOBALS['css']='.unrelated{color:red}';$GLOBALS['meta']=[];
$GLOBALS['posts']=[21=>['post_type'=>'wp_template_part','post_name'=>'header','post_content'=>$wrongHeader,'post_title'=>'Header'],387956=>['post_type'=>'page','post_name'=>'sign-up','post_content'=>'Keep this page','post_title'=>'Sign Up']];
$plan=CWS\PortalNavigationFix\repair_plan((object)['content'=>$wrongHeader,'wp_id'=>21]);
check(count($plan)===1,'Inline navigation should update only Header');
$fixed=$plan[0]['content'];
$parsed=parse_blocks($fixed);$nav=$parsed[1]['innerBlocks'][0];
$labels=array_column(array_column($nav['innerBlocks'],'attrs'),'label');
check($labels===['About The Eye-Bank','Become a Donor','Volunteer','Give Now','Transplant Portal','Research Portal','More'],'Incorrect inline menu order');
$utility=serialize_blocks($parsed[0]['innerBlocks']);
check(!str_contains($utility,'>Transplant Portal<')&&!str_contains($utility,'>Research Portal<'),'Utility portal buttons remain');
check(str_contains($utility,'EyeCanSeeAgain.org'),'Existing utility link lost');
check(!str_contains(serialize_blocks([$parsed[0]]),'cwsr-header'),'Old layout class remains');
check(CWS\PortalNavigationFix\repair_plan((object)['content'=>$fixed,'wp_id'=>21])[0]['content']===$fixed,'Inline transformation not idempotent');
foreach(CWS\PortalNavigationFix\bulk_steps() as $step)CWS\PortalNavigationFix\bulk_execute($step);
check($GLOBALS['posts'][21]['post_content']===$fixed,'Header not persisted');
check($GLOBALS['posts'][387956]['post_content']==='Keep this page','Unrelated page changed');
check(str_contains($GLOBALS['css'],'.unrelated{color:red}'),'Unrelated CSS lost');
check(CWS\PortalNavigationFix\history()['portal_position_v1']['records'][0]['before']===$wrongHeader,'Header original not backed up');
$GLOBALS['posts'][21]['post_content']='Later edit';CWS\PortalNavigationFix\bulk_execute(CWS\PortalNavigationFix\bulk_steps()[0]);check($GLOBALS['posts'][21]['post_content']==='Later edit','Completed correction overwrote later edits');

// A header pointing at a saved menu must change that specific record, not a menu guessed by name.
$refBlocks=parse_blocks($wrongHeader);$originalNav=$refBlocks[1]['innerBlocks'][0]['innerBlocks'];
$refBlocks[1]['innerBlocks'][0]['attrs']['ref']=31;$refBlocks[1]['innerBlocks'][0]['innerBlocks']=[];$refBlocks[1]['innerBlocks'][0]['innerContent']=[];$refBlocks[1]['innerBlocks'][0]['innerHTML']='';
$refHeader=serialize_blocks($refBlocks);
$GLOBALS['posts'][21]['post_content']=$refHeader;
$GLOBALS['posts'][31]=['post_type'=>'wp_navigation','post_status'=>'publish','post_name'=>'main','post_content'=>serialize_blocks($originalNav)];
$GLOBALS['posts'][32]=['post_type'=>'wp_navigation','post_status'=>'publish','post_name'=>'ed-primary','post_content'=>'Do not change this inactive menu'];
$plan=CWS\PortalNavigationFix\repair_plan((object)['content'=>$refHeader,'wp_id'=>21]);
check($plan[0]['id']===31&&$plan[1]['id']===21,'Incorrect saved-menu target');
CWS\PortalNavigationFix\bulk_records('reference_test',$plan);
$labels=array_column(array_column(parse_blocks($GLOBALS['posts'][31]['post_content']),'attrs'),'label');
check($labels===['About The Eye-Bank','Become a Donor','Volunteer','Give Now','Transplant Portal','Research Portal','More'],'Incorrect saved-menu order');
check($GLOBALS['posts'][32]['post_content']==='Do not change this inactive menu','Inactive ED Primary menu was changed');
$again=CWS\PortalNavigationFix\repair_plan((object)['content'=>$GLOBALS['posts'][21]['post_content'],'wp_id'=>21]);
check($again[0]['content']===$GLOBALS['posts'][31]['post_content'],'Saved-menu retry duplicated links');
$GLOBALS['posts'][31]['post_content']=str_replace('"label":"More"','"label":"Other"',$GLOBALS['posts'][31]['post_content']);
try{CWS\PortalNavigationFix\repair_plan((object)['content'=>$refHeader,'wp_id'=>21]);throw new RuntimeException('Expected missing anchor error');}catch(RuntimeException $e){check(str_contains($e->getMessage(),'Give Now before More'),'Missing anchor not reported');}
file_put_contents('/private/tmp/cwspn-header-blocks.json',json_encode(parse_blocks($fixed)));
ob_start();CWS\PortalNavigationFix\bulk_screen();$screen=ob_get_clean();check(substr_count($screen,'type="checkbox"')===1&&substr_count($screen,'<button')===1,'More than one checkbox/button');file_put_contents('/private/tmp/cwspn-screen.html',$screen);
echo "PASS: inline and referenced Main navigation, correct order, utility removal, existing EyeCanSeeAgain/submenus preserved, inactive ED Primary untouched, no duplicate links, durable backups, CSS preservation, completed edits and missing anchors.\n";
