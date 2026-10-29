<?php
/** Values icons are decorative; their visible headings provide the labels. */
defined('ABSPATH') || exit;
function ed_about_value_icons_without_tooltips($html) {
 if (!str_contains($html, 'ed-about-value-icon')) return $html;
 $html = preg_replace('~<title\b[^>]*>.*?</title\s*>~is', '', $html);
 $tags = new WP_HTML_Tag_Processor($html);
 while ($tags->next_tag()) $tags->remove_attribute('title');
 return $tags->get_updated_html();
}
add_filter('render_block_core/html', 'ed_about_value_icons_without_tooltips');
