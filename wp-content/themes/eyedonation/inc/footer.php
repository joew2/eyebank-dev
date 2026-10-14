<?php
/** Keep the approved footer wording editable while rendering the site's current year. */
defined('ABSPATH') || exit;
add_filter('render_block_core/group', function ($html, $block) {
    $classes = preg_split('/\s+/', $block['attrs']['className'] ?? '');
    if (!in_array('ed-footer', $classes, true)) return $html;
    return preg_replace_callback(
        '/(Copyright\s+(?:©|&copy;|&#169;)\s+)\d{4}(?=\s+The Eye-Bank for Sight Restoration, Inc\.)/u',
        static function ($match) { return $match[1] . esc_html(wp_date('Y')); },
        $html
    );
}, 10, 2);
