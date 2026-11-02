<?php
namespace EyeDonation\SiteMigrations;
defined('ABSPATH') || exit;
require_once __DIR__.'/bulk-runner.php';
add_action('admin_menu',function(){
    $hook=get_plugin_page_hookname('eyedonation-migrations','tools.php');
    remove_action($hook,__NAMESPACE__.'\\contained_admin_screen');
    add_action($hook,__NAMESPACE__.'\\bulk_screen');
},200);
function bulk_screen() {
    if (!current_user_can('manage_options')) return;
    $steps=bulk_steps();$job=get_option(BULK_OPTION,[]);
    echo '<div class="wrap edm-admin-screen"><h1>Update the EyeDonation site</h1><p>Install the current editable pages, shared header/footer, member menu and search updates. Completed updates are preserved when you resume.</p><p>This replaces the converted pages’ existing content and the EyeDonation header/footer with the supplied designs. Keep this tab open while processing.</p>';
    echo '<p><label><input type="checkbox" id="edm-bulk-agree"> I have a recoverable database backup and agree to process all site updates.</label></p><p><button type="button" class="button button-primary button-hero" id="edm-bulk-process">Process all updates</button></p>';
    echo '<p role="status" aria-live="polite" id="edm-bulk-status">Ready. Check the agreement, then click Process all updates.</p><progress id="edm-bulk-progress" max="'.count($steps).'" value="0" style="width:100%;max-width:640px"></progress><ol id="edm-bulk-results"></ol><noscript><p>Enable JavaScript in your browser to process the updates.</p></noscript></div>';
    wp_enqueue_script('edm-bulk',plugins_url('assets/bulk.js',__FILE__),[],filemtime(__DIR__.'/assets/bulk.js'),true);
    wp_add_inline_script('edm-bulk','window.edmBulk='.wp_json_encode(['nonce'=>wp_create_nonce('edm_bulk'),'steps'=>array_map(static function($s){return ['id'=>$s['id'],'label'=>$s['label']];},$steps),'job'=>$job]).';','before');
}
/** Independent request lease so existing installers can still acquire their own execution lock. */
function bulk_request_lock() {
    global $wpdb;
    $key='edm_bulk_request_lock';$value=['token'=>wp_generate_uuid4(),'expires'=>time()+180];
    if (add_option($key,$value,'',false)) return $value;
    $old=get_option($key);
    if (($old['expires']??PHP_INT_MAX)>=time()) fail('An update request is still running. Wait a moment before resuming.');
    $deleted=$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name=%s AND option_value=%s",$key,maybe_serialize($old)));
    wp_cache_delete($key,'options');
    if (!$deleted||!add_option($key,$value,'',false)) fail('Another update request is active.');
    return $value;
}
function bulk_request_unlock($lease) {
    global $wpdb;
    $key='edm_bulk_request_lock';
    $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name=%s AND option_value=%s",$key,maybe_serialize($lease)));
    wp_cache_delete($key,'options');
}
add_action('wp_ajax_edm_process_all',function(){
    if (!current_user_can('manage_options')||!current_user_can('edit_theme_options')||!current_user_can('edit_pages')) wp_send_json_error(['message'=>'Administrator permission required.'],403);
    check_ajax_referer('edm_bulk');
    if (($_POST['agree']??'')!=='1') wp_send_json_error(['message'=>'Check the backup and replacement agreement first.'],400);
    $lease=null;$response=null;$error=null;
    try {
        preflight();$lease=bulk_request_lock();$steps=bulk_steps();
        if (($_POST['mode']??'')==='start') $response=bulk_begin($steps);
        else {
            $job=get_option(BULK_OPTION,[]);
            if (!$job||($job['id']??'')!==sanitize_text_field(wp_unslash($_POST['job']??''))||($job['actor']??0)!==get_current_user_id()) fail('This update session changed. Click Process all updates to resume.');
            if (($job['status']??'')!=='running') $response=$job;
            else $response=bulk_advance($job,$steps);
        }
    } catch (\Throwable $e) {$error=$e->getMessage();}
    finally {if($lease) bulk_request_unlock($lease);}
    if ($error!==null) wp_send_json_error(['message'=>$error],409);
    wp_send_json_success($response);
});
