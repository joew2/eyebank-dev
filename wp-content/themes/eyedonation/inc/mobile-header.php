<?php
/** Shared tablet/mobile donor link for file defaults and saved header parts. */
defined('ABSPATH') || exit;
add_filter('render_block_core/group', function ($html, $block) {
    if (!in_array('ed-brand', preg_split('/\s+/', $block['attrs']['className'] ?? ''), true)) return $html;
    $link = '<a class="ed-header-donor" href="https://donatelife.ny.gov/ref/EBSRlink/" aria-label="Sign up today to be a donor"><img src="'.esc_url(get_theme_file_uri('/assets/images/donate-life-logo.png')).'" alt="Donate Life"><span>Sign up today<br>to be a donor</span><svg viewBox="0 0 16 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M0 0L16 12L0 24Z"></path></svg></a>';
    $position = strrpos($html, '</div>');
    return $position === false ? $html : substr_replace($html, $link, $position, 0);
}, 10, 2);
