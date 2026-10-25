<?php
/** Homepage heading refinements, shared by saved content and the backup pattern. */
defined('ABSPATH') || exit;

function ed_link_homepage_news_heading($html) {
    return preg_replace(
        '~(<h2\b[^>]*>)Eye-Bank News(</h2>)~',
        '$1<a class="ed-home-news-link" href="' . esc_url(home_url('/about-donation/')) . '">Eye-Bank News</a>$2',
        $html
    );
}

add_filter('render_block_core/heading', function ($html) {
    return is_front_page() ? ed_homepage_resource_label(ed_link_homepage_news_heading($html)) : $html;
});

function ed_homepage_resource_label($html) {
    return preg_replace(
        '~(<h3\b[^>]*>\s*<a\b[^>]*>)Transplant Recipients (?:&amp;|&) Donor Families~',
        '$1Transplant Recipients<br>&amp; Donor Families',
        $html
    );
}
