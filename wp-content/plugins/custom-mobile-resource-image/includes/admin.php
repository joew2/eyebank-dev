<?php
namespace CWS\MobileResourceImage;
defined('ABSPATH') || exit;
add_action('admin_menu',function(){add_management_page('Mobile Resource Image CWS','Mobile Resource Image CWS','manage_options','cws-mobile-resource-image',__NAMESPACE__.'\\bulk_screen');});
function bulk_screen() {
    if (!current_user_can('manage_options')) return;
    $steps=bulk_steps();$job=get_option(JOB_OPTION,[]);
    echo '<div class="wrap cwsmri-admin"><h1>Replace desktop and mobile Resources photos</h1><p>Install the bundled desktop photo and latest mobile photo together. Mobile heights match the other Resources cards, with excess cropped only from the bottom. Includes the same hover and keyboard-focus zoom as the other Resources cards. No earlier image migration is required.</p>';
    echo '<p><label><input type="checkbox" id="cwsmri-bulk-agree"> I have a recoverable database backup and agree to apply these updates.</label></p><p><button type="button" class="button button-primary button-hero" id="cwsmri-bulk-process">Process all updates</button></p>';
    echo '<p role="status" aria-live="polite" id="cwsmri-bulk-status">Ready. Check the agreement, then click Process all updates.</p><progress id="cwsmri-bulk-progress" max="'.count($steps).'" value="0" style="width:100%;max-width:640px"></progress><ol id="cwsmri-bulk-results"></ol><noscript>Enable JavaScript to process updates.</noscript></div>';
    wp_enqueue_script('cwsmri-bulk',plugins_url('assets/process.js',DIR.'/custom-mobile-resource-image.php'),[],filemtime(DIR.'/assets/process.js'),true);
    wp_add_inline_script('cwsmri-bulk','window.cwsmriBulk='.wp_json_encode(['nonce'=>wp_create_nonce('cwsmri_process'),'steps'=>array_map(static function($s){return ['id'=>$s['id'],'label'=>$s['label']];},$steps),'job'=>$job]).';','before');
}
add_action('wp_ajax_cwsmri_process_all',function(){
    if (!current_user_can('manage_options')||!current_user_can('edit_theme_options')||!current_user_can('edit_pages')) wp_send_json_error(['message'=>'Administrator permission required.'],403);
    check_ajax_referer('cwsmri_process');
    if (($_POST['agree']??'')!=='1') wp_send_json_error(['message'=>'Check the backup and replacement agreement first.'],400);
    $lease=null;$response=null;$error=null;
    try {
        preflight();$lease=acquire('cwsmri_request_lock');$steps=bulk_steps();
        if (($_POST['mode']??'')==='start') $response=bulk_begin($steps);
        else {
            $job=get_option(JOB_OPTION,[]);
            if (!$job||($job['id']??'')!==sanitize_text_field(wp_unslash($_POST['job']??''))||($job['actor']??0)!==get_current_user_id()) fail('This update session changed. Click Process all updates to resume.');
            if (($job['status']??'')!=='running') $response=$job;
            else $response=bulk_advance($job,$steps);
        }
    } catch (\Throwable $e) {$error=$e->getMessage();}
    finally {if($lease) release('cwsmri_request_lock',$lease);}
    if ($error!==null) wp_send_json_error(['message'=>$error],409);
    wp_send_json_success($response);
});
