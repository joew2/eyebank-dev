<?php
namespace CWS\ForgotPasswordBoards;
defined('ABSPATH') || exit;
add_action('admin_menu',function(){add_management_page('Forgot Password Boards CWS','Forgot Password Boards CWS','manage_options','cws-forgot-password-boards',__NAMESPACE__.'\\bulk_screen');});
function bulk_screen() {
    if (!current_user_can('manage_options')) return;
    $steps=bulk_steps();$job=get_option(JOB_OPTION,[]);
    echo '<div class="wrap cwsfpb-admin"><h1>Migrate Forgot Password Boards</h1><p>Install the editable boards password-recovery page at /password-boards/, preserving the original Toolset form, Member return destination and Enter New Password reset destination.</p>';
    echo '<p><label><input type="checkbox" id="cwsfpb-bulk-agree"> I have a recoverable database backup and agree to replace the boards password page with the editable layout.</label></p><p><button type="button" class="button button-primary button-hero" id="cwsfpb-bulk-process">Process all updates</button></p>';
    echo '<p role="status" aria-live="polite" id="cwsfpb-bulk-status">Ready. Check the agreement, then click Process all updates.</p><progress id="cwsfpb-bulk-progress" max="'.count($steps).'" value="0" style="width:100%;max-width:640px"></progress><ol id="cwsfpb-bulk-results"></ol><noscript><p>Enable JavaScript in your browser to process the updates.</p></noscript></div>';
    wp_enqueue_script('cwsfpb-bulk',plugins_url('assets/process.js',DIR.'/custom-forgot-password-boards.php'),[],filemtime(DIR.'/assets/process.js'),true);
    wp_add_inline_script('cwsfpb-bulk','window.cwsfpbBulk='.wp_json_encode(['nonce'=>wp_create_nonce('cwsfpb_process'),'steps'=>array_map(static function($s){return ['id'=>$s['id'],'label'=>$s['label']];},$steps),'job'=>$job]).';','before');
}
add_action('wp_ajax_cwsfpb_process_all',function(){
    if (!current_user_can('manage_options')||!current_user_can('edit_theme_options')||!current_user_can('edit_pages')) wp_send_json_error(['message'=>'Administrator permission required.'],403);
    check_ajax_referer('cwsfpb_process');
    if (($_POST['agree']??'')!=='1') wp_send_json_error(['message'=>'Check the backup and replacement agreement first.'],400);
    $lease=null;$response=null;$error=null;
    try {
        preflight();$lease=acquire('cwsfpb_request_lock');$steps=bulk_steps();
        if (($_POST['mode']??'')==='start') $response=bulk_begin($steps);
        else {
            $job=get_option(JOB_OPTION,[]);
            if (!$job||($job['id']??'')!==sanitize_text_field(wp_unslash($_POST['job']??''))||($job['actor']??0)!==get_current_user_id()) fail('This update session changed. Click Process all updates to resume.');
            if (($job['status']??'')!=='running') $response=$job;
            else $response=bulk_advance($job,$steps);
        }
    } catch (\Throwable $e) {$error=$e->getMessage();}
    finally {if($lease) release('cwsfpb_request_lock',$lease);}
    if ($error!==null) wp_send_json_error(['message'=>$error],409);
    wp_send_json_success($response);
});
