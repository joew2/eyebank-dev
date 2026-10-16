<?php
/** Reflow existing History blocks without changing their copy or media. */
defined('ABSPATH') || exit;
function ed_reflow_history_content($content) {
    if (str_contains($content, 'ed-history-flow-opening')) return $content;
    $blocks = parse_blocks($content);
    $root = null;
    foreach ($blocks as $block) {
        if (in_array('ed-our-history', explode(' ', $block['attrs']['className'] ?? ''), true)) $root = $block;
    }
    if (!$root) throw new RuntimeException('History page group is missing.');
    $named = [];
    $collect = function ($items) use (&$collect, &$named) {
        foreach ($items as $item) {
            foreach (explode(' ', $item['attrs']['className'] ?? '') as $class) {
                if (str_starts_with($class, 'ed-history-')) $named[$class] = $item;
            }
            $collect($item['innerBlocks'] ?? []);
        }
    };
    $collect($root['innerBlocks']);
    $required = ['opening','training','paton','surgeon','breckinridge-copy','supporters','donor-card','awareness','breckinridge','roosevelt','pilot','final'];
    foreach ($required as $key) if (!isset($named['ed-history-'.$key])) throw new RuntimeException('Missing History section: '.$key);
    $serialize = static function ($names) use ($named) {
        return implode('', array_map(static fn($name) => serialize_block($named['ed-history-'.$name]), $names));
    };
    $columns = static function ($left, $right, $class) {
        return '<!-- wp:columns {"className":"ed-history-flow '.$class.'"} --><div class="wp-block-columns ed-history-flow '.$class.'">'
            .'<!-- wp:column {"width":"68%"} --><div class="wp-block-column" style="flex-basis:68%">'.$left.'</div><!-- /wp:column -->'
            .'<!-- wp:column {"width":"32%"} --><div class="wp-block-column" style="flex-basis:32%">'.$right.'</div><!-- /wp:column -->'
            .'</div><!-- /wp:columns -->';
    };
    $heading = '';
    $banners = [];
    foreach ($root['innerBlocks'] as $block) {
        if (($block['blockName'] ?? '') === 'core/heading' && ($block['attrs']['level'] ?? 2) === 1) $heading = serialize_block($block);
        if (in_array('ed-history-banner', explode(' ', $block['attrs']['className'] ?? ''), true)) $banners[] = serialize_block($block);
    }
    if (!$heading || count($banners) !== 3) throw new RuntimeException('History heading or banners are missing.');
    $final = $named['ed-history-final'];
    foreach ($final['innerBlocks'] as &$column) {
        // Remove the pilot image from its old position, preserving every other block.
        foreach ($column['innerBlocks'] as $i => $child) {
            if (!in_array('ed-history-pilot', explode(' ', $child['attrs']['className'] ?? ''), true)) continue;
            $nulls = 0;
            foreach ($column['innerContent'] as $j => $piece) {
                if ($piece === null && $nulls++ === $i) { unset($column['innerContent'][$j]); break; }
            }
            unset($column['innerBlocks'][$i]);
            $column['innerBlocks'] = array_values($column['innerBlocks']);
            $column['innerContent'] = array_values($column['innerContent']);
        }
    }
    unset($column);
    $body = $banners[0]
        .$columns($heading.$serialize(['opening','training']), $serialize(['paton','surgeon']), 'ed-history-flow-opening')
        .$banners[1]
        .$columns($serialize(['breckinridge-copy','supporters','donor-card','awareness']), $serialize(['breckinridge','roosevelt','pilot']), 'ed-history-flow-middle')
        .$banners[2].serialize_block($final);
    return '<!-- wp:group {"className":"ed-section ed-our-history","layout":{"type":"default"}} --><div class="wp-block-group ed-section ed-our-history">'.$body.'</div><!-- /wp:group -->';
}
function ed_current_history_content() {
    return ed_reflow_history_content(ed_our_history_content());
}
