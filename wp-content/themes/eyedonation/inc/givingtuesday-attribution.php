<?php
/** Editable campaign attribution links shared by the landing page and banner. */
defined('ABSPATH') || exit;
function ed_gt_attribution_block(){
 return '<!-- wp:paragraph {"className":"ed-gt-attribution"} --><p class="ed-gt-attribution"><a href="https://www.givingtuesday.org/">givingtuesday.org</a> · <a href="https://creativecommons.org/licenses/by/4.0/legalcode.en">Creative Commons Attribution 4.0 International License</a></p><!-- /wp:paragraph -->';
}
function ed_gt_add_attribution($content){
 if(strpos($content,'ed-gt-attribution')!==false)return $content;
 $hashtag='<!-- wp:paragraph {"className":"ed-gt-hashtag"} --><p class="ed-gt-hashtag">#GIVINGTUESDAY</p><!-- /wp:paragraph -->';
 if(strpos($content,$hashtag)===false)throw new RuntimeException('Expected Giving Tuesday hashtag block before adding attribution.');
 return str_replace($hashtag,$hashtag."\n".ed_gt_attribution_block(),$content);
}
