<?php
/** Current newsletter naming without altering the original page migration payload. */
defined('ABSPATH') || exit;
function ed_newsletter_copy($content){return str_replace(['<em>eye-to-eye</em>','>Join the mailing list'],['<em>eye to eye</em>','>Join our mailing list'],$content);}
add_filter('render_block_core/paragraph',function($html){return is_page('about-donation')?ed_newsletter_copy($html):$html;});

add_filter('render_block_core/button',function($html,$block){
 if(!is_page('about-donation')||!in_array('ed-newsletter-join',explode(' ',$block['attrs']['className']??''),true))return $html;
 return ed_newsletter_copy($html);
},20,2);
