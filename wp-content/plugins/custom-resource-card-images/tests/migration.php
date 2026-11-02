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
require dirname(__DIR__).'/custom-resource-card-images.php';
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
$GLOBALS['sequence']=100;$GLOBALS['wpdb']=new RepairDBFixture();$GLOBALS['options']=['show_on_front'=>'page','page_on_front'=>11];$GLOBALS['css']=".unrelated{color:red}\n/* CWS RESOURCE PHOTO FRAMING START */\n.old-zoom{transform:scale(1.05)}\n/* CWS RESOURCE PHOTO FRAMING END */\n";
$content=str_replace(['{{theme_url}}','{{home_url}}'],['https://example.test/wp-content/themes/eyedonation','https://example.test'],file_get_contents(ABSPATH.'wp-content/plugins/custom-homepage-restore/payloads/homepage.html'));
$GLOBALS['posts']=[11=>['post_type'=>'page','post_status'=>'publish','post_name'=>'eye-bank-home','post_content'=>$content,'post_title'=>'Home'],17=>['post_type'=>'attachment','post_status'=>'inherit','post_name'=>'desktop-v2'],18=>['post_type'=>'attachment','post_status'=>'inherit','post_name'=>'mobile-v2'],40=>['post_type'=>'page','post_name'=>'other','post_content'=>'Keep this page']];
$manifest=CWS\ResourceCardImages\image_manifest();$GLOBALS['files']=[17=>dirname(__DIR__).'/assets/'.$manifest['desktop']['file'],18=>dirname(__DIR__).'/assets/'.$manifest['mobile']['file']];$GLOBALS['meta']=[11=>['_wp_page_template'=>'homepage','custom'=>'preserve'],17=>['_cwsrcimg_sha256'=>$manifest['desktop']['sha256']],18=>['_cwsrcimg_sha256'=>$manifest['mobile']['sha256']]];
foreach(CWS\ResourceCardImages\bulk_steps() as $step)CWS\ResourceCardImages\bulk_execute($step);
check($GLOBALS['options']['cwsrcimg_assets_v1']===['desktop'=>17,'mobile'=>18],'Existing verified imports not reused');
$updated=$GLOBALS['posts'][11]['post_content'];check(str_contains($updated,'<picture class="cws-resource-picture">')&&str_contains($updated,'media="(max-width: 781px)"')&&str_contains($updated,'desktop-v2.jpg')&&str_contains($updated,'mobile-v2.jpg'),'Responsive image URLs missing');
check(!str_contains($GLOBALS['css'],'CWS RESOURCE PHOTO FRAMING START')&&str_contains($GLOBALS['css'],'.unrelated{color:red}')&&str_contains($GLOBALS['css'],'CWS RESOURCE CARD IMAGES V2 START'),'Old framing not removed or unrelated CSS lost');
$before=parse_blocks($content);$after=parse_blocks($updated);foreach($before as $i=>$block)if($i!==1)check(serialize_block($block)===serialize_block($after[$i]),'Other homepage section changed');
check($GLOBALS['posts'][40]['post_content']==='Keep this page'&&$GLOBALS['meta'][11]['_wp_page_template']==='homepage','Unrelated page/template changed');
$picture=CWS\ResourceCardImages\picture_block();check(CWS\ResourceCardImages\replace_resource_card($updated,$picture)===$updated,'Replacement is not idempotent');
check(CWS\ResourceCardImages\history()['resource_images_page_v2']['records'][0]['before']===$content,'Original page not backed up');
$GLOBALS['posts'][11]['post_content']='Later editorial edit';CWS\ResourceCardImages\bulk_execute(CWS\ResourceCardImages\bulk_steps()[3]);check($GLOBALS['posts'][11]['post_content']==='Later editorial edit','Completed retry overwrote edits');
try{CWS\ResourceCardImages\replace_resource_card('No card',$picture);throw new RuntimeException('Expected missing-card error');}catch(RuntimeException $e){check(str_contains($e->getMessage(),'exactly one'),'Missing card not reported');}
file_put_contents('/private/tmp/cwsrcimg-page.html',$updated);
ob_start();CWS\ResourceCardImages\bulk_screen();$screen=ob_get_clean();file_put_contents('/private/tmp/cwsrcimg-screen.html',$screen);check(substr_count($screen,'type="checkbox"')===1&&substr_count($screen,'<button')===1,'Single-button screen missing');
echo "PASS: bundled asset hashes, deduplicated imports, responsive markup, original crop CSS removed, unrelated sections/pages/templates/styles preserved, before-images, idempotence and one-button screen.\n";
