<?php
/** Portable campaign content; slide artwork ships with the theme. */
defined('ABSPATH') || exit;
function ed_givingtuesday_content() {
    $content = file_get_contents(get_theme_file_path('/assets/givingtuesday/content.html'));
    $content = str_replace('{{heading}}', esc_url(get_theme_file_uri('/assets/givingtuesday/gt-heading-text.png')), $content);
    return str_replace('{{donate}}', esc_url(add_query_arg('eyedonation-placeholder', 'givingtuesday-donate', home_url('/'))), $content);
}
add_action('init', function () {
    register_block_type('eyedonation/givingtuesday-slideshow', [
        'api_version' => 3,
        'render_callback' => function () {
            $labels = ['New Yorkers whose sight has been restored.', 'Ellen can see to travel around the city.', 'Jaclyn can see to practice law.', 'Oscar can see to give tours at the museum.'];
            $html = '<section class="ed-gt-slider" aria-label="Stories of restored sight" aria-roledescription="carousel"><div class="ed-gt-slides">';
            foreach ($labels as $i => $label) {
                $number = sprintf('%02d', $i + 1);
                $url = get_theme_file_uri('/assets/givingtuesday/');
                $html .= '<div class="ed-gt-slide" role="group" aria-roledescription="slide" aria-label="'.($i+1).' of 4"'.($i ? ' hidden' : '').'><picture><source media="(max-width: 767px)" srcset="'.esc_url($url.'slide-mobile-'.$number.'.jpg').'"><img src="'.esc_url($url.'slide-desktop-main-'.$number.'.jpg').'" width="1700" height="548" alt="'.esc_attr($label).'"'.($i ? ' loading="eager"' : ' fetchpriority="high"').'></picture></div>';
            }
            return $html.'</div><div class="ed-gt-controls" hidden><button type="button" class="ed-gt-prev" aria-label="Previous slide"><svg viewBox="0 0 24 40" aria-hidden="true"><path d="M21 3 4 20l17 17"/></svg></button><button type="button" class="ed-gt-next" aria-label="Next slide"><svg viewBox="0 0 24 40" aria-hidden="true"><path d="m3 3 17 17L3 37"/></svg></button></div></section>';

        }
    ]);
});
add_action('wp_enqueue_scripts', function () {
    if (!is_page('givingtuesday') && !is_page_template('givingtuesday')) return;
    foreach (['css', 'js'] as $type) {
        $path = '/assets/'.$type.'/givingtuesday.'.$type;
        if ($type === 'css') wp_enqueue_style('ed-givingtuesday', get_theme_file_uri($path), ['ed-base'], filemtime(get_theme_file_path($path)));
        else wp_enqueue_script('ed-givingtuesday', get_theme_file_uri($path), [], filemtime(get_theme_file_path($path)), true);
    }
});
add_action('enqueue_block_editor_assets', function () {
    wp_enqueue_style('ed-givingtuesday-editor', get_theme_file_uri('/assets/css/givingtuesday.css'), [], filemtime(get_theme_file_path('/assets/css/givingtuesday.css')));
    wp_enqueue_script('ed-givingtuesday-editor', get_theme_file_uri('/assets/js/givingtuesday-editor.js'), ['wp-blocks','wp-element','wp-block-editor','wp-server-side-render'], filemtime(get_theme_file_path('/assets/js/givingtuesday-editor.js')), true);
});
