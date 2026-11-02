<?php
namespace {
define('ABSPATH',dirname(__DIR__,4).'/');require ABSPATH.'wp-includes/plugin.php';
}
namespace EyeDonation\Search {
$GLOBALS['admin_allowed']=true;$GLOBALS['valid_nonce']=true;
function settings(){return ['enhanced'=>true,'filters'=>true,'highlight'=>true,'per_page'=>6,'excluded_types'=>[],'excluded_ids'=>[],'groups'=>[]];}
function rebuild_status(){return ['status'=>'pending','running'=>true,'indexed'=>1,'total'=>3,'missing'=>2,'error'=>''];}
function get_option($key,$default=false){return $key==='eds_rebuild_job'?['cursor'=>100]:$default;}
function current_user_can($cap){return $GLOBALS['admin_allowed'];}
function esc_html($v){return htmlspecialchars((string)$v,ENT_QUOTES);}
function esc_attr($v){return esc_html($v);}
function esc_textarea($v){return esc_html($v);}
function wp_nonce_field(...$args){echo '<input type="hidden" name="_wpnonce" value="test">';}
function checked($a,$b,$echo=false){return $a===$b?'checked':'';}
function wp_json_encode($v,$flags=0){return json_encode($v,$flags);}
function submit_button($label){echo '<button type="submit">'.$label.'</button>';}
function wp_enqueue_script(...$args){}
function wp_add_inline_script(...$args){}
function plugins_url($path,$file){return $path;}
function wp_create_nonce($name){return 'test';}
class Denied extends \RuntimeException{}
function wp_send_json_error($data,$code=400){throw new Denied($data['message'],$code);}
function check_ajax_referer($name){if(!$GLOBALS['valid_nonce'])throw new Denied('Invalid nonce',403);}
require dirname(__DIR__).'/src/Admin.php';
$_SERVER['REQUEST_METHOD']='GET';ob_start();admin_screen();$html=ob_get_clean();file_put_contents('/private/tmp/eds-rebuild-admin.html',$html);
if(!str_contains($html,'Indexed 1 of 3')||!str_contains($html,'id="eds-rebuild"'))throw new \RuntimeException('Progress controls missing');
$handler=$GLOBALS['wp_filter']['wp_ajax_eds_rebuild_batch']->callbacks[10];$handler=reset($handler)['function'];
$GLOBALS['admin_allowed']=false;try{$handler();throw new \RuntimeException('Expected permission error');}catch(Denied $e){if($e->getCode()!==403)throw $e;}
$GLOBALS['admin_allowed']=true;$GLOBALS['valid_nonce']=false;try{$handler();throw new \RuntimeException('Expected nonce error');}catch(Denied $e){if($e->getMessage()!=='Invalid nonce')throw $e;}
echo "PASS: admin progress/count rendering, administrator-only AJAX and nonce enforcement.\n";
}
