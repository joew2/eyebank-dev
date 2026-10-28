<?php
/** Publication dates belong to news cards only; other page/post date blocks stay hidden. */
defined('ABSPATH') || exit;
add_filter('render_block_eyedonation/first-paragraph',function($html,$parsed,$block){
    $post=get_post((int)($block->context['postId']??0));
    if(!$post||$post->post_type!=='post'||$post->post_status!=='publish'||$post->post_password)return $html;
    $label=get_the_date('F jS, Y',$post);
    $datetime=get_the_date(DATE_W3C,$post);
    return '<time class="ed-news-date" datetime="'.esc_attr($datetime).'">'.esc_html($label).'</time>'.$html;
},20,3);
