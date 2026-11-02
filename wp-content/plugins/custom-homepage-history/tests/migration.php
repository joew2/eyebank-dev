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
require dirname(__DIR__).'/custom-homepage-history.php';
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



function wp_get_original_image_path($id){return $GLOBALS['files'][$id]??false;}
function wp_attachment_is_image($id){return isset($GLOBALS['files'][$id]);}
function wp_get_attachment_url($id){return home_url('/wp-content/uploads/'.$GLOBALS['posts'][$id]['post_name'].'.jpg');}
$original=file_get_contents(ABSPATH.'wp-content/plugins/custom-homepage-restore/payloads/homepage.html');
$before=parse_blocks($original);$updated=CWS\HomepageHistory\update_history($original,999,'https://example.test/history-v2.jpg');$after=parse_blocks($updated);
foreach($before as $i=>$block){if(!str_contains($block['attrs']['className']??'','ed-history'))check(serialize_block($block)===serialize_block($after[$i]),'Unrelated homepage block changed');}
check(CWS\HomepageHistory\update_history($updated,999,'https://example.test/history-v2.jpg')===$updated,'Transformation not idempotent');
check(str_contains($updated,'ed-history-mobile')&&str_contains($updated,'worlds_first_eye_bank_photo_mobile'),'Mobile photo lost');
check(str_contains($updated,'sight&nbsp;to'),'Words can split across lines');
check(str_contains($updated,'history-v2.jpg')&&str_contains($updated,'ed-history-about'),'Photo or button missing');
$walk=function($blocks)use(&$walk){foreach($blocks as $b){if(str_contains($b['attrs']['className']??'','ed-history-panel')){check(!str_contains(serialize_blocks($b['innerBlocks']),'wp:buttons'),'Button still inside panel');check(count($b['innerBlocks'])===2,'Heading or paragraph lost');}if(!empty($b['innerBlocks']))$walk($b['innerBlocks']);}};$walk($after);
echo "PASS: native block layout, separate button, photo replacement, unchanged other sections/mobile image and idempotence.\n";
$mobile=CWS\HomepageHistory\update_history($original,999,'https://example.test/history-v3.jpg',1001,'https://example.test/mobile-v3.jpg');
check(str_contains($mobile,'mobile-v3.jpg')&&str_contains($mobile,'wp-image-1001'),'New mobile image missing');
check(CWS\HomepageHistory\update_history($mobile,999,'https://example.test/history-v3.jpg',1001,'https://example.test/mobile-v3.jpg')===$mobile,'Combined photo replacement not idempotent');
check(str_contains($mobile,'sight&nbsp;to'),'Nonbreaking text lost');
echo "PASS: desktop and mobile replacements, rerun idempotence and nonbreaking copy.\n";
check(str_contains($mobile,'cws-history-mobile-v3')&&!str_contains($mobile,'"className":"ed-history-mobile"'),'Mobile source still controlled by the old 600px theme filter');
$resource='<!-- wp:group {"className":"ed-resource-card cws-resource-images-v2"} --><div class="wp-block-group ed-resource-card cws-resource-images-v2"><!-- wp:html --><picture class="cws-resource-picture"><source media="(max-width: 781px)" srcset="mobile.jpg"><img src="desktop.jpg"></picture><!-- /wp:html --></div><!-- /wp:group -->';
$resource=CWS\HomepageHistory\tablet_resource_source($resource);check(str_contains($resource,'(max-width: 900px)')&&str_contains($resource,'srcset="mobile.jpg"')&&str_contains($resource,'src="desktop.jpg"'),'Tablet source switch changed image URLs or missed breakpoint');
check(CWS\HomepageHistory\tablet_resource_source($resource)===$resource,'Tablet source update not idempotent');
echo "PASS: tablet media switch, native mobile history image and preserved desktop/mobile URLs.\n";
