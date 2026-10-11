<?php
/** Overlay the native video button without rewriting editable page content. */
defined('ABSPATH') || exit;
add_filter('render_block_core/column', function ($html, $block) {
    $has_image = false;
    $has_video_button = false;
    foreach ($block['innerBlocks'] ?? [] as $child) {
        if (($child['blockName'] ?? '') === 'core/image') {
            $has_image = true;
        }
        if (($child['blockName'] ?? '') === 'core/buttons') {
            foreach ($child['innerBlocks'] ?? [] as $button) {
                $text = wp_strip_all_tags($button['innerHTML'] ?? '');
                if (preg_match('/Watch\s+Adam[’\x27]s\s+Video/iu', $text)) {
                    $has_video_button = true;
                }
            }
        }
    }
    if (!$has_image || !$has_video_button) {
        return $html;
    }
    $processor = new WP_HTML_Tag_Processor($html);
    if ($processor->next_tag(['class_name' => 'wp-block-column'])) {
        $processor->add_class('ed-testimonial-photo');
    }
    return $processor->get_updated_html();
}, 10, 2);
