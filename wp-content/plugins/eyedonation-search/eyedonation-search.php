<?php
/**
 * Plugin Name: EyeDonation Search
 * Description: Public derived-text indexing and accessible grouped search. Independent of site migrations.
 * Version: 0.1.0
 * Requires PHP: 8.1
 * Requires at least: 6.6
 * Text Domain: eyedonation-search
 */
namespace EyeDonation\Search;
defined('ABSPATH') || exit;
const VERSION='0.1.0';
require __DIR__.'/src/Search.php';
require __DIR__.'/src/Admin.php';
register_activation_hook(__FILE__,__NAMESPACE__.'\\request_rebuild');
add_action('eds_rebuild_tick',__NAMESPACE__.'\\rebuild_batch');
add_action('save_post',function($id){if(!wp_is_post_revision($id)) index_post($id);},20);
add_action('set_object_terms',function($id){index_post($id);},20);
add_action('before_delete_post',function($id){delete_post_meta($id,'_eds_public_text');});
add_action('pre_get_posts',__NAMESPACE__.'\\prepare_query');
add_filter('posts_search',__NAMESPACE__.'\\search_sql',20,2);
add_filter('pre_render_block',__NAMESPACE__.'\\render_results',10,2);

add_filter('posts_clauses',__NAMESPACE__.'\\query_clauses',20,2);
add_action('template_redirect',function(){global $wp_query;if($wp_query->get('eds_enhanced')&&(int)$wp_query->get('paged')>1&&!$wp_query->posts){wp_safe_redirect(add_query_arg(array_filter(['s'=>$wp_query->get('s'),'group'=>$wp_query->get('eds_group')]),home_url('/')),302);exit;}},1);
