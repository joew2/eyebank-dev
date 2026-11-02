<?php
/** Editable replacement for Eye-Bank's boards password-recovery template. */
defined('ABSPATH') || exit;
function ed_forgot_password_boards_content(){
 $content=file_get_contents(get_theme_file_path('/assets/forgot-password-boards/content.html'));
 $member=get_page_by_path('member');$reset=get_page_by_path('enter-new-password');
 return str_replace(['{{member_url}}','{{reset_url}}'],[esc_url($member?get_permalink($member):home_url('/member/')),esc_url($reset?get_permalink($reset):home_url('/enter-new-password/'))],$content);
}
add_action('template_redirect',function(){
 if(!is_page('password-boards')&&!is_page_template('forgot-password-boards'))return;
 if(!defined('DONOTCACHEPAGE'))define('DONOTCACHEPAGE',true);
 nocache_headers();
});
add_action('wp_enqueue_scripts',function(){
 if(!is_page('password-boards')&&!is_page_template('forgot-password-boards'))return;
 wp_enqueue_style('ed-forgot-password-boards',get_theme_file_uri('/assets/css/forgot-password-boards.css'),['ed-shared'],filemtime(get_theme_file_path('/assets/css/forgot-password-boards.css')));
});
add_action('enqueue_block_editor_assets',function(){wp_enqueue_style('ed-forgot-password-boards-editor',get_theme_file_uri('/assets/css/forgot-password-boards.css'),[],filemtime(get_theme_file_path('/assets/css/forgot-password-boards.css')));});
