<?php
defined('ABSPATH') || exit;
// Render blocks before wp_head so WordPress can discover module dependencies.
$ed_header = do_blocks('<!-- wp:template-part {"slug":"header","tagName":"header"} /-->');
$ed_footer = do_blocks('<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->');
?><!doctype html><html <?php language_attributes(); ?>><head><meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><?php wp_head(); ?></head><body <?php body_class(); ?>><?php wp_body_open(); ?><div class="wp-site-blocks"><?php
 echo $ed_header;
 ?><main id="ed-main" class="ed-main"><h1><?php esc_html_e('Destination pending','eyedonation'); ?></h1><p><?php esc_html_e('This is a placeholder link in the local design preview. Its final destination has not been configured yet.','eyedonation'); ?></p><p><a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Return to the homepage','eyedonation'); ?></a></p></main><?php
 echo $ed_footer;
 ?></div><?php wp_footer(); ?></body></html>
