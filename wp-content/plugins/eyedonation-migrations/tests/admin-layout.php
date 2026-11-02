<?php
/** Offline integration check with real WP hooks, no database or migration execution. */
define('ABSPATH', dirname(__DIR__,4).'/');
require ABSPATH.'wp-includes/plugin.php';
function add_management_page($a,$b,$c,$slug,$callback){add_action('tools_page_'.$slug,$callback);}
function remove_submenu_page(...$args){}
function get_plugin_page_hookname($slug,$parent){return 'tools_page_'.$slug;}
function current_user_can(...$args){return true;}
function get_option($key,$default=false){return $default;}
function home_url(){return 'https://example.test';}
function wp_get_environment_type(){return 'staging';}
function get_stylesheet(){return 'eyedonation';}
function wp_get_theme(){return new class {function get($key){return '0.1.0';}};}
function esc_html($value){return htmlspecialchars((string)$value,ENT_QUOTES);}
function esc_attr($value){return esc_html($value);}
function esc_url($value){return esc_html($value);}
function admin_url($path=''){return '/wp-admin/'.$path;}
function wp_nonce_field(...$args){echo '<input type="hidden" name="_wpnonce" value="test">';}
function wp_nonce_url($url,$action){return $url;}
function wp_create_nonce($action){return 'test';}
function submit_button($label){echo '<p><button class="button">'.esc_html($label).'</button></p>';}
function plugins_url($path,$file){return $path;}
function wp_enqueue_script(...$args){}
function wp_enqueue_style(...$args){}
function wp_add_inline_script(...$args){}
function wp_json_encode($value,$flags=0){return json_encode($value,$flags);}
function update_option(...$args){throw new Exception('Unexpected write');}
require dirname(__DIR__).'/eyedonation-migrations.php';
$_GET['page']='eyedonation-migrations';$_SERVER['REQUEST_METHOD']='GET';
$external=function(){echo 'EXTERNAL FOOTER';};add_action('admin_footer',$external);
$before=[];foreach($wp_filter['admin_footer']->callbacks as $entries)foreach($entries as $entry)$before[]=$entry['function'];
// Core checksum before admin registration; presentation registration must not affect it.
$checksum=EyeDonation\SiteMigrations\checksum('001_create_approved_navigation');
do_action('admin_menu');
if(count($before)!==count($wp_filter['edm_admin_sections']->callbacks[10])+1)throw new Exception('Panel lost');
if(!has_action('admin_footer',$external))throw new Exception('External footer modified');
if(has_action('tools_page_eyedonation-migrations','EyeDonation\\SiteMigrations\\admin_screen'))throw new Exception('Original screen still attached');
ob_start();EyeDonation\SiteMigrations\contained_admin_screen();$html=ob_get_clean();
if(substr_count($html,'class="edm-admin-screen"')!==1||strpos($html,'Other Ways to Give')===false)throw new Exception('Missing screen/panel');
ob_start();do_action('admin_footer');$footer=ob_get_clean();if($footer!=='EXTERNAL FOOTER')throw new Exception('Panels remain in footer');
if($checksum!==EyeDonation\SiteMigrations\checksum('001_create_approved_navigation'))throw new Exception('Checksum changed');
file_put_contents('/private/tmp/ed-admin-content.html',$html);
ob_start();do_action('tools_page_eyedonation-migrations');$simple=ob_get_clean();
if(substr_count($simple,'id="edm-bulk-agree"')!==1||substr_count($simple,'id="edm-bulk-process"')!==1||strpos($simple,'Preview Changes')!==false)throw new Exception('Simple screen not installed');
file_put_contents('/private/tmp/ed-bulk-content.html',$simple);
$steps=EyeDonation\SiteMigrations\bulk_steps();
foreach($steps as $step)if($step['kind']==='installer'&&!class_exists($step['class']))throw new Exception('Missing installer class');
if(count($steps)!==40)throw new Exception('Unexpected deployment step count: '.count($steps));

echo 'PASS: '.(count($before)-1)." panels moved into page content, unrelated footer retained, original screen replaced once, no database writes.\n";
