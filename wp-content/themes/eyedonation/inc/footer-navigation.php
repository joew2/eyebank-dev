<?php
/** Approved footer destinations, kept separate from the primary navigation. */
defined('ABSPATH') || exit;
function ed_order_footer_links($links) {
    $order = array_flip(['About Us','Careers','Transplant Recipients & Donor Families','Research Portal','Transplant Portal','Terms and Conditions','Privacy Policy']);
    $rank = static function ($block) use ($order) {
        $label = str_replace('\\u0026', '&', html_entity_decode($block['attrs']['label'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        return $order[$label] ?? PHP_INT_MAX;
    };
    usort($links, static fn($a, $b) => $rank($a) <=> $rank($b));
    return $links;
}
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
                $block['innerBlocks'] = ed_order_footer_links($links);
                $block['innerContent'] = array_fill(0, count($links), null);
                $block['innerHTML'] = '';
            } elseif (!empty($block['innerBlocks'])) $walk($block['innerBlocks']);
        }
    };
    $walk($blocks);
    return serialize_blocks($blocks);
}
