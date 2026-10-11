<?php
defined('ABSPATH') || exit;
add_action('after_setup_theme', function(){ add_theme_support('editor-styles'); add_editor_style('assets/css/base.css'); add_theme_support('post-thumbnails'); });

// Portable supplied branding fallback; selecting a Site Logo in the editor takes precedence.
add_filter('render_block_core/site-logo',function($html,$block){
 if($html||get_option('site_logo'))return $html;
 return '<div class="wp-block-site-logo"><a href="'.esc_url(home_url('/')).'" rel="home"><img src="'.esc_url(get_theme_file_uri('/assets/images/eye-bank-logo.svg')).'" width="'.esc_attr($block['attrs']['width']??340).'" alt="The Eye-Bank of New York"></a></div>';
},10,2);
