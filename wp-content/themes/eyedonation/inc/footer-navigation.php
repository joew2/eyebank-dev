<?php
/** Approved footer destinations, kept separate from the primary navigation. */
defined('ABSPATH') || exit;
function ed_update_footer_navigation($content) {
    $blocks = parse_blocks($content);
    $walk = function (&$items) use (&$walk) {
        foreach ($items as &$block) {
            if (($block['blockName'] ?? '') === 'core/navigation' && in_array('ed-footer-navigation', explode(' ', $block['attrs']['className'] ?? ''), true)) {
                $links = []; $combined = false;
                foreach ($block['innerBlocks'] as $link) {
                    $label = $link['attrs']['label'] ?? '';
                    if ($label === 'Researchers') $link['attrs']['label'] = 'Research Portal';
                    if ($label === 'Surgeons') $link['attrs']['label'] = 'Transplant Portal';
                    if (in_array($label, ['Transplant Recipients','Donor Families','Transplant Recipients & Donor Families'], true)) {
                        if ($combined) continue;
                        $combined = true;
                        $link['attrs']['label'] = 'Transplant Recipients & Donor Families';
                        $link['attrs']['url'] = '/transplant-recipients-donor-families/';
                    }
                    $links[] = $link;
                }
                $block['innerBlocks'] = $links;
                $block['innerContent'] = array_fill(0, count($links), null);
                $block['innerHTML'] = '';
            } elseif (!empty($block['innerBlocks'])) $walk($block['innerBlocks']);
        }
    };
    $walk($blocks);
    return serialize_blocks($blocks);
}
