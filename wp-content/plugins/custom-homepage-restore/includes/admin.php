<?php
namespace CWS\HomepageRestore;
defined('ABSPATH') || exit;
add_action('admin_menu',function(){add_management_page('Homepage Restore CWS','Homepage Restore CWS','manage_options','cws-homepage-restore',__NAMESPACE__.'\\bulk_screen');});
function bulk_screen() {
    if (!current_user_can('manage_options')) return;
    $steps=bulk_steps();$job=get_option(JOB_OPTION,[]);
    $target='Not selected — choose a static homepage in Settings → Reading.';
    try{$page=target_page();$target=$page->post_title.' (page '.(int)$page->ID.')';}catch(\Throwable $e){$target=$e->getMessage();}
    echo '<div class="wrap cwshr-admin"><h1>Restore the EyeDonation homepage</h1><p>Restore the latest supplied homepage design as editable WordPress blocks, with the current hero, donor panel, support images and live news section. Existing header, navigation and footer are retained.</p><p>Each new restore replaces the selected homepage content. Keep this tab open while processing.</p>';
    echo '<p><strong>Homepage:</strong> '.esc_html($target).'</p>';
    echo '<p><label><input type="checkbox" id="cwshr-bulk-agree"> I have a recoverable database backup and agree to replace the homepage with the supplied design.</label></p><p><button type="button" class="button button-primary button-hero" id="cwshr-bulk-process">Restore homepage</button></p>';
    echo '<p role="status" aria-live="polite" id="cwshr-bulk-status">Ready. Check the agreement, then click Restore homepage.</p><progress id="cwshr-bulk-progress" max="'.count($steps).'" value="0" style="width:100%;max-width:640px"></progress><ol id="cwshr-bulk-results"></ol><noscript><p>Enable JavaScript in your browser to process the updates.</p></noscript></div>';
    wp_enqueue_script('cwshr-bulk',plugins_url('assets/process.js',DIR.'/custom-homepage-restore.php'),[],filemtime(DIR.'/assets/process.js'),true);
    wp_add_inline_script('cwshr-bulk','window.cwshrBulk='.wp_json_encode(['nonce'=>wp_create_nonce('cwshr_process'),'steps'=>array_map(static function($s){return ['id'=>$s['id'],'label'=>$s['label']];},$steps),'job'=>$job]).';','before');
}
add_action('wp_ajax_cwshr_process_all',function(){
    if (!current_user_can('manage_options')||!current_user_can('edit_theme_options')||!current_user_can('edit_pages')) wp_send_json_error(['message'=>'Administrator permission required.'],403);
    check_ajax_referer('cwshr_process');
    if (($_POST['agree']??'')!=='1') wp_send_json_error(['message'=>'Check the backup and replacement agreement first.'],400);
    $lease=null;$response=null;$error=null;
    try {
        preflight();$lease=acquire('cwshr_request_lock');$steps=bulk_steps();
        if (($_POST['mode']??'')==='start') $response=bulk_begin($steps);
        else {
            $job=get_option(JOB_OPTION,[]);
            if (!$job||($job['id']??'')!==sanitize_text_field(wp_unslash($_POST['job']??''))||($job['actor']??0)!==get_current_user_id()) fail('This update session changed. Click Restore homepage to resume.');
            if (($job['status']??'')!=='running') $response=$job;
            else $response=bulk_advance($job,$steps);
        }
    } catch (\Throwable $e) {$error=$e->getMessage();}
    finally {if($lease) release('cwshr_request_lock',$lease);}
    if ($error!==null) wp_send_json_error(['message'=>$error],409);
    wp_send_json_success($response);
});
