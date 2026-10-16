<?php
/** Omit publication/modified date blocks, including saved Site Editor layouts. */
defined('ABSPATH') || exit;
add_filter('pre_render_block', static function ($pre_render, $block) {
    return ($block['blockName'] ?? '') === 'core/post-date' ? '' : $pre_render;
}, 10, 2);
