<?php
/** Offline checks: php tests/render.php. No WordPress writes or remote requests. */
define('ABSPATH', __DIR__);
$stored=[];$environment='production';$admin=false;$feed=false;$preview=false;$allowed=true;
function get_option($name,$default=[]){global $stored;return $stored;}
function is_admin(){global $admin;return $admin;}
function is_feed(){global $feed;return $feed;}
function is_preview(){global $preview;return $preview;}
function wp_get_environment_type(){global $environment;return $environment;}
function apply_filters($name,$value){global $allowed;return $allowed;}
function esc_attr($value){return htmlspecialchars($value,ENT_QUOTES);}
function esc_url($value){return htmlspecialchars($value,ENT_QUOTES);}
function wp_json_encode($value,$flags=0){return json_encode($value,$flags);}
function wp_print_inline_script_tag($value,$attrs){echo '<script id="'.$attrs['id'].'">'.$value.'</script>';}
function add_settings_error(...$args){}
function __($value,$domain){return $value;}
require dirname(__DIR__).'/includes.php';
function output($fn){ob_start();$fn();return ob_get_clean();}
function check($condition,$message){if(!$condition)throw new RuntimeException($message);}
$head=output('cwstr_head');
check(strpos($head,'GTM-NP6FHDK')!==false && strpos($head,'posthog.init(')!==false && strpos($head,'facebook-domain-verification')!==false,'Production integrations missing');
check(strpos($head,"defaults:'2025-05-24',person_profiles:'identified_only'")!==false,'Legacy PostHog config changed');
foreach(['local','development','staging'] as $environment){$head=output('cwstr_head');check(strpos($head,'<script')===false && strpos($head,'facebook-domain-verification')!==false,'Environment gate failed');}
$environment='production';
foreach(['admin','feed','preview'] as $flag){$$flag=true;check(strpos(output('cwstr_head'),'<script')===false,'Context gate failed: '.$flag);$$flag=false;}
$allowed=false;check(strpos(output('cwstr_head'),'<script')===false,'Consent filter failed');$allowed=true;
$stored=['enabled'=>false];check(strpos(output('cwstr_head'),'<script')===false,'Disable failed');$stored=[];
$body=output('cwstr_body');check(substr_count($body,'<iframe')===1 && output('cwstr_body')==='','Body fallback duplicated');
$bad=cwstr_sanitize(['enabled'=>1,'gtm_id'=>'GTM-A"></script>','posthog_key'=>['bad'],'facebook_verification'=>'<script>']);
check($bad===['enabled'=>true,'gtm_id'=>'','posthog_key'=>'','facebook_verification'=>''],'Invalid settings accepted');
$stored=['gtm_id'=>'','posthog_key'=>'','facebook_verification'=>''];check(output('cwstr_head')==='','Blank IDs should disable integrations');
echo "PASS: production output, legacy config, environments, admin/feed/preview, consent, disable, body fallback, invalid settings, blank IDs.\n";
