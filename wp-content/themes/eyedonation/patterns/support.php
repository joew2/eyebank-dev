<?php
/**
 * Title: EyeDonation Support
 * Slug: eyedonation/support
 * Categories: featured
 * Description: Editable Support photograph, copy and donation button; confirm the destination before publishing.
 */
$media = ed_pattern_media('2026/10/support_our_sight-saving_programs_photo.jpg', 'A mother reading with her children at home');
$attrs = ['url'=>$media['url'],'alt'=>$media['alt'],'dimRatio'=>0,'className'=>'ed-support'];
if($media['id']) $attrs['id']=$media['id'];
echo '<!-- wp:cover '.wp_json_encode($attrs).' -->';
?>
<div class="wp-block-cover ed-support"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><img class="wp-block-cover__image-background<?php echo $media['id']?' wp-image-'.esc_attr($media['id']):''; ?>" alt="<?php echo esc_attr($media['alt']); ?>" src="<?php echo esc_url($media['url']); ?>" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:group {"tagName":"div","className":"ed-support-panel","layout":{"type":"constrained"}} --><div class="wp-block-group ed-support-panel"><!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Support our sight-saving programs</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Your support will help restore sight to New Yorkers in need and advance ocular research.</p><!-- /wp:paragraph --><!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"className":""} --><div class="wp-block-button "><a class="wp-block-button__link wp-element-button" href="">Give Now</a></div><!-- /wp:button --></div><!-- /wp:buttons --></div><!-- /wp:group --></div></div><!-- /wp:cover -->
