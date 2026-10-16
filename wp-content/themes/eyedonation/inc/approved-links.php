<?php
/** Approved destinations for the original homepage/header placeholder links. */
defined('ABSPATH') || exit;
function ed_approved_link_destinations() {
    return [
        'recipients' => home_url('/transplant-recipients-donor-families/'),
        'research' => home_url('/researchers/'),
        'transplant' => home_url('/transplant/'),
        'about' => home_url('/about-the-eye-bank/'),
        'utility' => 'https://www.eyecanseeagain.org/',
        'video' => home_url('/video/adams-story'),
        'donation' => 'https://host.nxt.blackbaud.com/adaptive-donor-form/?formId=185999c1-3922-45d8-8724-5d45e919c907&envid=p-t70-gTghE0ScbPMC-otWOw&zone=usa',
    ];
}
function ed_resolve_approved_links($html) {
    if (!str_contains($html, 'eyedonation-placeholder')) return $html;
    $tags = new WP_HTML_Tag_Processor($html);
    $destinations = ed_approved_link_destinations();
    while ($tags->next_tag('A')) {
        $href = $tags->get_attribute('href');
        if (!is_string($href)) continue;
        parse_str((string) wp_parse_url(html_entity_decode($href), PHP_URL_QUERY), $query);
        $key = $query['eyedonation-placeholder'] ?? '';
        if (is_string($key) && isset($destinations[$key])) $tags->set_attribute('href', $destinations[$key]);
    }
    return $tags->get_updated_html();
}
// Also resolve saved Site Editor headers and older portable homepage backups.
add_filter('render_block', 'ed_resolve_approved_links', 20);

/** Keep leadership's internal links on the current installation. */
function ed_localize_leadership_links($html) {
    $tags = new WP_HTML_Tag_Processor($html);
    while ($tags->next_tag('A')) {
        $href = $tags->get_attribute('href');
        if (!is_string($href)) continue;
        $url = wp_parse_url($href);
        if (!in_array(strtolower($url['host'] ?? ''), ['eyedonation.org', 'www.eyedonation.org'], true)) continue;
        $relative = $url['path'] ?? '/';
        if (isset($url['query'])) $relative .= '?' . $url['query'];
        if (isset($url['fragment'])) $relative .= '#' . $url['fragment'];
        $tags->set_attribute('href', $relative);
    }
    return $tags->get_updated_html();
}
add_filter('render_block_core/group', function ($html, $block) {
    return in_array('ed-leadership', explode(' ', $block['attrs']['className'] ?? ''), true)
        ? ed_localize_leadership_links($html) : $html;
}, 20, 2);
