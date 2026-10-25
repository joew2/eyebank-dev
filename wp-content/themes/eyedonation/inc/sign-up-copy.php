<?php
/** Current signup introduction, shared with the reusable pattern. */
defined('ABSPATH') || exit;
function ed_sign_up_copy($content){return str_replace('Sign up for news, inspiring stories, and updates about the gift of sight.','Sign up for news, inspiring stories and updates about the gift of sight.',$content);}
add_filter('render_block_core/paragraph',function($html){return is_page('sign-up')?ed_sign_up_copy($html):$html;});
