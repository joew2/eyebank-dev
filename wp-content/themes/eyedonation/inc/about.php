<?php
defined('ABSPATH') || exit;
function ed_about_content() {
 $content=file_get_contents(get_theme_file_path('/assets/about/content.html'));
 return preg_replace_callback('/\{\{asset:([a-zA-Z0-9_.-]+)\}\}/',static function($m){return esc_url(get_theme_file_uri('/assets/about/'.$m[1]));},$content);
}
