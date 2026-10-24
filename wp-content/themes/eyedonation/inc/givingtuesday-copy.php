<?php
/** Current campaign copy, preserving the original reusable migration payload. */
defined('ABSPATH') || exit;
function ed_givingtuesday_final_copy($content){
    $sentences=[
        'Your support today will help restore sight to one of the thousands of New Yorkers in need of a sight-saving cornea transplant.',
        'Your gift will also fund research advancements that may help countless others see again.',
        'Consider becoming a monthly donor and help give the gift of sight all year long.',
    ];
    $old=[];
    foreach($sentences as $sentence)$old[]='<!-- wp:paragraph --><p>'.$sentence.'</p><!-- /wp:paragraph -->';
    $content=str_replace(implode("\n",$old),'<!-- wp:paragraph --><p>'.implode(' ',$sentences).'</p><!-- /wp:paragraph -->',$content);
    $thanks='<!-- wp:paragraph --><p>Thank You!</p><!-- /wp:paragraph -->';
    if(strpos($content,$thanks)!==false){
        $content=str_replace($thanks."\n",'',$content);
        $content=preg_replace('~(<!-- /wp:buttons -->)~','$1'."\n".'<!-- wp:paragraph {"className":"ed-gt-thanks"} --><p class="ed-gt-thanks">Thank You!</p><!-- /wp:paragraph -->',$content,1);
    }
    return str_replace('>Donate Today</a>','>Give Now</a>',$content);
}
