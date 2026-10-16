<?php
/** Portable, editable History layout; no legacy custom-field dependency. */
defined('ABSPATH') || exit;
function ed_our_history_content(){
 $content=file_get_contents(get_theme_file_path('/assets/our-history/content.html'));
 return preg_replace_callback('/\{\{asset:([a-zA-Z0-9_.-]+)\}\}/',static function($m){return esc_url(get_theme_file_uri('/assets/our-history/'.$m[1]));},$content);
}
