<?php
/** Static landing-page images live in the theme, independently of attachments. */
defined('ABSPATH') || exit;
function ed_portable_asset_url($url) {
    $path = wp_parse_url(html_entity_decode($url), PHP_URL_PATH);
    if (!is_string($path) || !str_contains($path, '/uploads/')) return $url;
    $relative = explode('/uploads/', $path, 2)[1];
    $manifest = json_decode(file_get_contents(get_theme_file_path('/assets/portable-pages/manifest.json')), true);
    if (!isset($manifest[$relative])) return $url;
    return get_theme_file_uri('/assets/portable-pages/'.$manifest[$relative]['file']);
}
function ed_portable_page_content($content, $replace_hero=false) {
    $blocks = parse_blocks($content);
    $walk = function (&$items) use (&$walk, $replace_hero) {
        foreach ($items as &$block) {
            $hero = $replace_hero && ($block['blockName'] ?? '') === 'core/cover' && in_array('ed-hero', explode(' ', $block['attrs']['className'] ?? ''), true);
            $changed = false;
            foreach (['url','mediaUrl'] as $attribute) {
                if (!empty($block['attrs'][$attribute])) {
                    $old = $block['attrs'][$attribute];
                    $new = ed_portable_asset_url($old);
                    if ($hero && $attribute === 'url') $new = get_theme_file_uri('/assets/portable-pages/main_hero_photo_v4.jpg');
                    if ($old !== $new) { $block['attrs'][$attribute] = $new; $changed = true; }
                }
            }
            $patch = static function ($html) use ($hero, &$changed) {
                $tags = new WP_HTML_Tag_Processor($html);
                while ($tags->next_tag()) {
                    if ($tags->get_tag() !== 'IMG') continue;
                    $old = $tags->get_attribute('src');
                    if (!is_string($old)) continue;
                    $new = ed_portable_asset_url($old);
                    if ($hero) $new = get_theme_file_uri('/assets/portable-pages/main_hero_photo_v4.jpg');
                    if ($old !== $new || $hero) {
                        $changed = true;
                        $tags->set_attribute('src', $new);
                        $tags->remove_attribute('srcset');$tags->remove_attribute('sizes');
                        $class = preg_replace('/\bwp-image-\d+\b/', '', $tags->get_attribute('class') ?? '');
                        if (trim($class) !== '') $tags->set_attribute('class', trim($class)); else $tags->remove_attribute('class');
                        if ($hero) {
                            $tags->set_attribute('style', 'object-position:50% 100%');
                            $tags->set_attribute('data-object-position', '50% 100%');
                        }
                    }
                }
                return $tags->get_updated_html();
            };
            $block['innerHTML'] = $patch($block['innerHTML']);
            foreach ($block['innerContent'] as &$part) if (is_string($part)) $part = $patch($part);
            unset($part);
            if ($changed && in_array($block['blockName'], ['core/image','core/cover'], true)) unset($block['attrs']['id']);
            if ($hero) { $block['attrs']['url'] = get_theme_file_uri('/assets/portable-pages/main_hero_photo_v4.jpg');$block['attrs']['focalPoint'] = ['x'=>0.5,'y'=>1]; }
            if (!empty($block['innerBlocks'])) $walk($block['innerBlocks']);
        }
    };
    $walk($blocks);
    return serialize_blocks($blocks);
}
function ed_portable_homepage_content() {
    $content = file_get_contents(get_theme_file_path('/assets/patterns/homepage.html'));
    $assets = json_decode(file_get_contents(get_theme_file_path('/assets/patterns/homepage-assets.json')), true);
    foreach ($assets as $key=>$asset) {
        $url = ed_portable_asset_url(home_url('/wp-content/uploads/'.$asset['path']));
        $content = str_replace(['{{asset:'.$key.'}}','{{alt:'.$key.'}}'], [esc_url($url),esc_attr($asset['alt'])], $content);
    }
    foreach (['donor','recipients','research','transplant','about','utility','video','donation'] as $key) $content = str_replace('{{link:'.$key.'}}', esc_url(add_query_arg('eyedonation-placeholder',$key,home_url('/'))), $content);
    // Normalize the legacy empty image class in the frozen pattern source.
    $content = str_replace([' size-full "',' size-full  "'], ' size-full"', $content);
    return ed_resolve_approved_links(ed_portable_page_content(ed_update_donor_panel($content), true));
}
