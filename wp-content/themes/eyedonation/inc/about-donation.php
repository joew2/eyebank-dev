<?php
/** Editable newsletter hero plus the dynamic nine-story news block. */
defined('ABSPATH') || exit;
function ed_about_donation_content(){
 $content=file_get_contents(get_theme_file_path('/assets/about-donation/content.html'));
 $content=preg_replace_callback('/\{\{asset:([a-zA-Z0-9_.-]+)\}\}/',static fn($m)=>esc_url(get_theme_file_uri('/assets/about-donation/'.$m[1])),$content);
 foreach(['mailing','issues'] as $key)$content=str_replace('{{link:'.$key.'}}',esc_url(add_query_arg('eyedonation-placeholder','newsletter-'.$key,home_url('/'))),$content);
 return $content;
}
