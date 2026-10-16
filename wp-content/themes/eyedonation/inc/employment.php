<?php
/** Portable native employment page defaults. */
defined('ABSPATH') || exit;
function ed_employment_content(){
 $content=file_get_contents(get_theme_file_path('/assets/employment/content.html'));
 return preg_replace_callback('/\{\{asset:([a-zA-Z0-9_.-]+)\}\}/',static function($m){return esc_url(get_theme_file_uri('/assets/employment/'.$m[1]));},$content);
}
