<?php
/** New-account native preferences only; existing accounts opt in manually. */
defined('ABSPATH') || exit;
add_action('user_register',function($id){
 if(version_compare(get_bloginfo('version'),'7.1','<'))return;
 global $wpdb;$key=$wpdb->get_blog_prefix().'persisted_preferences';$prefs=get_user_meta($id,$key,true);if(!is_array($prefs))$prefs=[];$changed=false;
 foreach(['page','post'] as $type)if(!isset($prefs['core']['renderingModes']['eyedonation'][$type])){$prefs['core']['renderingModes']['eyedonation'][$type]='post-only';$changed=true;}
 if($changed){$prefs['_modified']=gmdate('Y-m-d\TH:i:s.000\Z');update_user_meta($id,$key,$prefs);}
});
