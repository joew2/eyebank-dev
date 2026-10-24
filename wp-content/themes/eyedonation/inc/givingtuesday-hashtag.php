<?php
/** Editable hashtag beneath the campaign call to action. */
defined('ABSPATH') || exit;
function ed_givingtuesday_add_hashtag($content){
    if(strpos($content,'ed-gt-hashtag')!==false)return $content;
    $thanks='<!-- wp:paragraph {"className":"ed-gt-thanks"} --><p class="ed-gt-thanks">Thank You!</p><!-- /wp:paragraph -->';
    $hashtag='<!-- wp:paragraph {"className":"ed-gt-hashtag"} --><p class="ed-gt-hashtag">#GIVINGTUESDAY</p><!-- /wp:paragraph -->';
    return str_replace($thanks,$thanks."\n".$hashtag,$content);
}
