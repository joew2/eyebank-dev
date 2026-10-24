<?php
/** Homepage news heading destination, shared by saved content and the backup pattern. */
defined('ABSPATH') || exit;

function ed_link_homepage_news_heading($html) {
    return preg_replace(
        '~(<h2\b[^>]*>)Eye-Bank News(</h2>)~',
        '$1<a class="ed-home-news-link" href="' . esc_url(home_url('/about-donation/')) . '">Eye-Bank News</a>$2',
        $html
    );
}

add_filter('render_block_core/heading', function ($html) {
    return is_front_page() ? ed_link_homepage_news_heading($html) : $html;
});
