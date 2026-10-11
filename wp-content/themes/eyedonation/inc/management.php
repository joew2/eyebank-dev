<?php
defined('ABSPATH') || exit;
add_action('admin_menu',function(){ add_theme_page('EyeDonation Site Management','EyeDonation Site Management','edit_theme_options','eyedonation',function(){
 if(!current_user_can('edit_theme_options')) return;
 echo '<div class="wrap"><h1>EyeDonation Site Management</h1><p>Use the native editors to manage shared design. Page content is edited under Pages.</p><ul>';
 foreach(['Header'=>'/wp_template_part/eyedonation//header','Footer'=>'/wp_template_part/eyedonation//footer','Navigation'=>'/navigation','Templates'=>'/wp_template','Global styles'=>'/styles'] as $label=>$path) echo '<li><a href="'.esc_url(add_query_arg('path',$path,admin_url('site-editor.php'))).'">'.esc_html($label).'</a></li>';
 echo '</ul><p>Deployment setup: Tools → Site Migrations. Activation never replaces content.</p></div>';
 }); });

add_action('admin_notices',function(){
 if(!current_user_can('edit_theme_options'))return;
 foreach(['header','footer'] as $part){$template=get_block_template('eyedonation//'.$part,'wp_template_part');if(!$template||empty($template->wp_id))continue;$recorded=get_post_meta($template->wp_id,'_edm_source_sha256',true);if($recorded&&$recorded!==hash_file('sha256',get_theme_file_path('/parts/'.$part.'.html')))echo '<div class="notice notice-warning"><p>EyeDonation '.esc_html($part).' file has changed beneath a managed Site Editor override. Review the saved part and prepare a specific follow-up migration; the saved part remains in use.</p></div>';}
});
