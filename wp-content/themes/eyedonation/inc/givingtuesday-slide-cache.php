<?php
/** Fresh artwork URLs without changing the campaign block or carousel behavior. */
defined('ABSPATH') || exit;
add_filter('render_block_eyedonation/givingtuesday-slideshow',function($html){
    for($i=1;$i<=4;$i++){
        $path='/assets/givingtuesday/slide-desktop-main-'.sprintf('%02d',$i).'.jpg';
        $url=get_theme_file_uri($path);
        $html=str_replace('src="'.esc_url($url).'"','src="'.esc_url(add_query_arg('ver',substr(hash_file('sha256',get_theme_file_path($path)),0,12),$url)).'"',$html);
    }
    return $html;
});
