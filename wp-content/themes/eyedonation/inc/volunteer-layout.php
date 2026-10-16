<?php
/** Arrange editable Volunteer blocks beside the existing Gravity Form. */
defined('ABSPATH') || exit;
function ed_reflow_volunteer_content($content) {
    if (str_contains($content, 'ed-volunteer-layout')) return $content;
    $root = null;
    foreach (parse_blocks($content) as $block) {
        if (in_array('ed-volunteer', explode(' ', $block['attrs']['className'] ?? ''), true)) $root = $block;
    }
    if (!$root) throw new RuntimeException('Volunteer page group is missing.');
    $banner = $heading = $form = $intro = null;
    foreach ($root['innerBlocks'] as $block) {
        $classes = explode(' ', $block['attrs']['className'] ?? '');
        if (in_array('ed-volunteer-banner', $classes, true)) $banner = $block;
        if (in_array('ed-volunteer-intro', $classes, true)) $intro = $block;
        if (in_array('ed-volunteer-form', $classes, true)) $form = $block;
        if (($block['blockName'] ?? '') === 'core/heading' && ($block['attrs']['level'] ?? 2) === 1) $heading = $block;
    }
    if (!$banner || !$heading || !$intro || !$form) throw new RuntimeException('Volunteer sections are missing.');
    $left = serialize_block($heading);
    foreach ($intro['innerBlocks'] as $column) {
        if (($column['blockName'] ?? '') === 'core/column') $left .= serialize_blocks($column['innerBlocks']);
    }
    return '<!-- wp:group {"className":"ed-section ed-volunteer","layout":{"type":"default"}} --><div class="wp-block-group ed-section ed-volunteer">'
        .serialize_block($banner)
        .'<!-- wp:columns {"className":"ed-volunteer-layout"} --><div class="wp-block-columns ed-volunteer-layout">'
        .'<!-- wp:column --><div class="wp-block-column">'.$left.'</div><!-- /wp:column -->'
        .'<!-- wp:column --><div class="wp-block-column">'.serialize_block($form).'</div><!-- /wp:column -->'
        .'</div><!-- /wp:columns --></div><!-- /wp:group -->';
}
function ed_current_volunteer_content() {
    return ed_reflow_volunteer_content(ed_volunteer_content());
}
