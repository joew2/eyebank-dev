<?php
/** Native editable member navigation; all personalization and visibility are server-side. */
defined('ABSPATH') || exit;
// Resolve original file-based placeholders without changing the frozen installation payload.
add_filter('render_block_data',function($block){
 if(($block['blockName']??'')!=='core/navigation-link')return $block;
 $links=['/?eyedonation-placeholder=member-profile'=>'/member-profile/','/?eyedonation-placeholder=member-transplant-tissue'=>'/request-transplant-tissue/','/?eyedonation-placeholder=member-research-tissue'=>'/request-research-tissue/'];
 $url=$block['attrs']['url']??'';
 if(isset($links[$url]))$block['attrs']['url']=$links[$url];
 return $block;
});
function ed_member_icon($name){
 $paths=[
  'profile'=>'<circle cx="16" cy="9" r="6"/><path d="M4 36v-6c0-7 5-11 12-11s12 4 12 11v6Z"/>',
  'downloads'=>'<path d="M5 3h15l8 8v26H5Z M20 3v9h8 M11 21h11 M11 27h11"/>',
  'transplant'=>'<path d="M5 3h15l8 8v26H5Z M20 3v9h8 M16 20v10 M11 25h10"/>',
  'research'=>'<path d="M5 3h15l8 8v26H5Z M20 3v9h8 M16 20v10 M11 25h10"/>',
  'logout'=>'<path d="M13 4h14v32H13 M3 20h17 M13 13l7 7-7 7"/>',
 ];
 if(!isset($paths[$name]))return '';
 return '<svg class="ed-member-icon" viewBox="0 0 32 40" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">'.$paths[$name].'</svg>';
}
function ed_render_member_account(){
 if(!is_user_logged_in())return '';
 $user=wp_get_current_user();$name=trim($user->first_name)?:($user->display_name?:$user->user_login);
 $initial=wp_html_excerpt($name,1,'');$initial=function_exists('mb_strtoupper')?mb_strtoupper($initial,'UTF-8'):strtoupper($initial);
 return '<div class="ed-member-account"><span class="ed-member-avatar" aria-hidden="true">'.esc_html($initial).'</span><div class="ed-member-account-copy"><p class="ed-member-greeting">'.esc_html(sprintf(__('Welcome, %s','eyedonation'),$name)).'</p><a class="ed-member-logout" href="'.esc_url(wp_logout_url(home_url('/'))).'">'.esc_html__('Log out','eyedonation').ed_member_icon('logout').'</a></div></div>';
}
add_action('init',function(){
 wp_register_script('ed-member-account-editor',get_theme_file_uri('/assets/js/member-account-editor.js'),['wp-blocks','wp-element','wp-block-editor','wp-server-side-render'],filemtime(get_theme_file_path('/assets/js/member-account-editor.js')),true);
 register_block_type('eyedonation/member-account',['api_version'=>3,'title'=>'Member welcome and logout','category'=>'theme','description'=>'Current user’s name, initial and secure logout link.','supports'=>['html'=>false,'multiple'=>false],'editor_script'=>'ed-member-account-editor','render_callback'=>'ed_render_member_account']);
});
add_filter('pre_render_block',function($pre,$block){
 if(is_user_logged_in())return $pre;
 $attrs=$block['attrs']??[];$classes=explode(' ',$attrs['className']??'');
 if((($block['blockName']??'')==='core/template-part'&&($attrs['slug']??'')==='member-menu')||array_intersect($classes,['ed-member-strip','ed-member-navigation']))return '';
 return $pre;
},100,2);
add_filter('render_block_core/template-part',function($html,$block){
 if(!is_user_logged_in()||is_admin()||($block['attrs']['slug']??'')!=='header'||str_contains($html,'ed-member-strip'))return $html;
 // Append outside the sticky header so the expanded mobile strip scrolls with the page.
 return $html.render_block(['blockName'=>'core/template-part','attrs'=>['slug'=>'member-menu','theme'=>get_stylesheet(),'tagName'=>'section','className'=>'ed-member-region'],'innerBlocks'=>[],'innerHTML'=>'','innerContent'=>[]]);
},30,2);
add_filter('render_block_core/navigation-link',function($html,$block){
 if(!is_user_logged_in())return $html;
 $classes=explode(' ',$block['attrs']['className']??'');
 foreach(['profile','downloads','transplant','research'] as $name){
  if(!in_array('ed-member-'.$name,$classes,true))continue;
  return preg_replace_callback('~(<a\b[^>]*>)~',static function($m)use($name){return $m[1].ed_member_icon($name);},$html,1);
 }
 return $html;
},20,2);
add_action('template_redirect',function(){if(is_user_logged_in())nocache_headers();});
add_action('wp_enqueue_scripts',function(){
 if(!is_user_logged_in())return;
 wp_enqueue_style('ed-member-menu',get_theme_file_uri('/assets/css/member-menu.css'),['ed-shared'],filemtime(get_theme_file_path('/assets/css/member-menu.css')));
});
add_action('enqueue_block_editor_assets',function(){wp_enqueue_style('ed-member-menu-editor',get_theme_file_uri('/assets/css/member-menu.css'),[],filemtime(get_theme_file_path('/assets/css/member-menu.css')));});
add_action('admin_menu',function(){
 $hook=add_theme_page('Member Menu','Member Menu','edit_theme_options','ed-member-menu',static function(){});
 add_action('load-'.$hook,function(){
  if(!current_user_can('edit_theme_options'))wp_die('You cannot edit this menu.');
  wp_safe_redirect(add_query_arg(['p'=>'/wp_template_part/'.get_stylesheet().'//member-menu','canvas'=>'edit'],admin_url('site-editor.php')));exit;
 });
});
