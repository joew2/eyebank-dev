<?php
/** Route the homepage hero signup through the site's donor information page. */
defined('ABSPATH') || exit;
function ed_update_hero_donor_link($html) {
    $tags = new WP_HTML_Tag_Processor($html);
    while ($tags->next_tag('A')) {
        if (!$tags->has_class('ed-donor-panel-link')) continue;
        $url = wp_parse_url(html_entity_decode((string) $tags->get_attribute('href')));
        if (strtolower($url['host'] ?? '') === 'donatelife.ny.gov' && rtrim($url['path'] ?? '', '/') === '/ref/EBSRlink') $tags->set_attribute('href', '/become-a-donor/');
    }
    return $tags->get_updated_html();
}
add_filter('render_block_core/group', static function ($html, $block) {
    return in_array('ed-donor-panel', explode(' ', $block['attrs']['className'] ?? ''), true) ? ed_update_hero_donor_link($html) : $html;
}, 20, 2);
