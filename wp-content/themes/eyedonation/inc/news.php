<?php
/** Scoped rendering of the first readable body paragraph; never execute shortcodes. */
defined('ABSPATH') || exit;
function ed_first_paragraph($content) {
 $content = preg_replace('~<(figure|figcaption|h[1-6]|iframe|video|audio)\b[^>]*>.*?</\1>~is', '', $content);
 $content = preg_replace('~<(script|style)\b[^>]*>.*?</\1>~is', '', $content);
 $content = preg_replace('/\[(?:[^\]]+)\]/u', '', $content);
 if(preg_match_all('~<p\b[^>]*>(.*?)</p>~is',$content,$matches)) foreach($matches[1] as $paragraph) { $text=trim(wp_strip_all_tags($paragraph)); if($text!=='') return $text; }
 return '';
}
add_filter('query_loop_block_query_vars',function($query,$block){
 if(str_contains($block->parsed_block['attrs']['className']??'','ed-news')) { $query['post_type']='post'; $query['post_status']='publish'; $query['has_password']=false; $query['posts_per_page']=3; $query['ignore_sticky_posts']=true; $query['orderby']=['date'=>'DESC','ID'=>'DESC']; }
 return $query;
},10,2);
add_action('init',function(){
 wp_register_script('ed-first-paragraph-editor',get_theme_file_uri('/assets/js/news-editor.js'),['wp-blocks','wp-element','wp-server-side-render'],filemtime(get_theme_file_path('/assets/js/news-editor.js')),true);
 register_block_type('eyedonation/first-paragraph',['api_version'=>3,'title'=>'Post first paragraph','category'=>'theme','uses_context'=>['postId','postType'],'editor_script'=>'ed-first-paragraph-editor','render_callback'=>function($attrs,$content,$block){
  $post=get_post($block->context['postId']??0);if(!$post||$post->post_status!=='publish'||$post->post_password)return '';
  return '<div class="ed-news-intro"><p>'.esc_html(ed_first_paragraph($post->post_content)).'</p></div>';
 }]);
});
add_filter('render_block_core/post-featured-image',function($html,$block){
 return !$html && str_contains($block['attrs']['className']??'','ed-news-image') ? '<div class="ed-news-placeholder" aria-hidden="true"></div>' : $html;
},10,2);
