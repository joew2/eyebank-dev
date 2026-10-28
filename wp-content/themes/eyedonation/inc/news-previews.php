<?php
/** Consistent news previews with measured line fitting; retain publication dates and links. */
defined('ABSPATH') || exit;
add_filter('render_block_eyedonation/first-paragraph',function($html,$parsed,$block){
 $id=(int)($block->context['postId']??0);$post=$id?get_post($id):null;
 if(!$post||$post->post_type!=='post'||$post->post_status!=='publish'||$post->post_password)return $html;
 $text=trim(wp_strip_all_tags($post->post_excerpt));
 if($text===''){
  $body=preg_replace('~<(figure|figcaption|h[1-6]|iframe|video|audio|script|style)\b[^>]*>.*?</\1>~is','',$post->post_content);
  $body=preg_replace('/\[(?:[^\]]+)\]/u','',strip_shortcodes($body));
  if(preg_match_all('~<p\b[^>]*>(.*?)</p>~is',$body,$matches))$text=implode(' ',array_map('wp_strip_all_tags',$matches[1]));
  else $text=wp_strip_all_tags($body);
 }
 $text=trim(preg_replace('/\s+/u',' ',html_entity_decode($text,ENT_QUOTES|ENT_HTML5,'UTF-8')));
 $text=wp_html_excerpt($text,1200,'');
 return preg_replace_callback('~(<div class="ed-news-intro"><p>).*?(\s*<a class="ed-news-read-more".*?</a></p></div>)~s',static function($m)use($text){return $m[1].'<span class="ed-news-preview-copy">'.esc_html($text).'</span> '.$m[2];},$html,1);
},30,3);
add_action('wp_enqueue_scripts',function(){
 if(!is_front_page()&&!is_page('about-donation'))return;
 wp_enqueue_script('ed-news-previews',get_theme_file_uri('/assets/js/news-previews.js'),[],filemtime(get_theme_file_path('/assets/js/news-previews.js')),true);
});
