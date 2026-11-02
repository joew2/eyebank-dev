<?php
namespace CWS\ResourcePhotoFraming;
defined('ABSPATH') || exit;
function fail($message){throw new \RuntimeException($message);}
function history(){return get_option('cwsrpf_history',[]);}
function save_history($value){update_option('cwsrpf_history',$value,false);}
function preflight(){
    if(is_multisite())fail('This repair package supports single-site WordPress.');
    if(get_stylesheet()!=='eyedonation')fail('Activate the EyeDonation theme first.');
    if(!current_user_can('manage_options')||!current_user_can('edit_theme_options')||!current_user_can('edit_pages'))fail('Administrator editing permissions are required.');
}
/** Separate request and write leases; never overlap a repair with the original migration runner. */
function acquire($key,$seconds=180){
    global $wpdb;
    $lease=['token'=>wp_generate_uuid4(),'expires'=>time()+$seconds];
    if(add_option($key,$lease,'',false))return $lease;
    $old=get_option($key);
    if(($old['expires']??PHP_INT_MAX)>=time())fail('An update request is still running. Wait a moment, then resume.');
    $deleted=$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name=%s AND option_value=%s",$key,maybe_serialize($old)));
    wp_cache_delete($key,'options');
    if(!$deleted||!add_option($key,$lease,'',false))fail('Another update request is active.');
    return $lease;
}
function release($key,$lease){
    global $wpdb;
    $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name=%s AND option_value=%s",$key,maybe_serialize($lease)));
    wp_cache_delete($key,'options');
}
function lock_acquire(){return acquire('edm_run_lock');}
function lock_check($lease){$current=get_option('edm_run_lock');if(($current['token']??'')!==$lease['token']||($current['expires']??0)<=time())fail('Execution lease expired. Resume the repairs.');}
function lock_release($lease){release('edm_run_lock',$lease);}
