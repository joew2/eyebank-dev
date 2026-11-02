<?php
/** Settings and frontend renderers. */
defined('ABSPATH') || exit;
function cwstr_defaults() {
    return [
        'enabled' => true,
        'gtm_id' => 'GTM-NP6FHDK',
        'posthog_key' => 'phc_M57LPtrdJtmsmdDtkOvqbYcCbothXLqu8UPlLenFqyn',
        'facebook_verification' => 'h3eljezaq7fill16hiomg2gol0pz7b',
    ];
}
function cwstr_sanitize($input) {
    $input = is_array($input) ? $input : [];
    $result = ['enabled' => !empty($input['enabled'])];
    foreach (['gtm_id' => '/^GTM-[A-Z0-9]+$/', 'posthog_key' => '/^phc_[a-zA-Z0-9]+$/', 'facebook_verification' => '/^[a-zA-Z0-9]+$/'] as $key => $pattern) {
        $value = isset($input[$key]) && is_string($input[$key]) ? trim($input[$key]) : '';
        $result[$key] = preg_match($pattern, $value) ? $value : '';
        if ($value !== '' && $result[$key] === '') {
            add_settings_error('cwstr_settings', $key, sprintf(__('Invalid value for %s. That integration has been disabled.', 'custom-site-tracking'), $key));
        }
    }
    return $result;
}
function cwstr_settings() {
    $stored = get_option('cwstr_settings', []);
    return array_merge(cwstr_defaults(), is_array($stored) ? $stored : []);
}
function cwstr_register_settings() {
    register_setting('cwstr_settings_group', 'cwstr_settings', ['type' => 'array', 'sanitize_callback' => 'cwstr_sanitize']);
}
function cwstr_settings_page() {
    if (!current_user_can('manage_options')) return;
    $settings = cwstr_settings();
    echo '<div class="wrap"><h1>' . esc_html__('Site Tracking CWS', 'custom-site-tracking') . '</h1>';
    echo '<p>' . esc_html__('Google Tag Manager, PostHog and Facebook domain verification, independent of the active theme. Clear an ID to disable that integration. Tracking scripts run only in the production environment; verification remains available everywhere.', 'custom-site-tracking') . '</p>';
    settings_errors();
    echo '<form method="post" action="options.php">';
    settings_fields('cwstr_settings_group');
    echo '<p><label><input type="checkbox" name="cwstr_settings[enabled]" value="1" ' . checked(!empty($settings['enabled']), true, false) . '> ' . esc_html__('Enable tracking scripts', 'custom-site-tracking') . '</label></p><table class="form-table">';
    foreach (['gtm_id' => 'Google Tag Manager container ID', 'posthog_key' => 'PostHog public project key', 'facebook_verification' => 'Facebook domain verification token'] as $key => $label) {
        echo '<tr><th><label for="cwstr-' . esc_attr($key) . '">' . esc_html($label) . '</label></th><td><input class="regular-text" id="cwstr-' . esc_attr($key) . '" name="cwstr_settings[' . esc_attr($key) . ']" value="' . esc_attr($settings[$key]) . '" type="text"></td></tr>';
    }
    echo '</table><p>' . esc_html__('PostHog retains the legacy US host, defaults 2025-05-24 and identified_only person profiles. Facebook verification is a meta tag, not a Pixel.', 'custom-site-tracking') . '</p>';
    submit_button();
    echo '</form></div>';
}
/** Filter can be used by an existing consent integration to defer all tracking. */
function cwstr_can_track() {
    return !is_admin() && !is_feed() && !is_preview() && !empty(cwstr_settings()['enabled'])
        && wp_get_environment_type() === 'production' && apply_filters('cwstr_tracking_allowed', true);
}
function cwstr_head() {
    if (is_admin() || is_feed()) return;
    $settings = cwstr_settings();
    if ($settings['facebook_verification'] !== '') {
        echo '<meta name="facebook-domain-verification" content="' . esc_attr($settings['facebook_verification']) . '">' . "\n";
    }
    if (!cwstr_can_track()) return;
    if ($settings['gtm_id'] !== '') {
        $id = wp_json_encode($settings['gtm_id'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        wp_print_inline_script_tag("(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer'," . $id . ');', ['id' => 'cwstr-gtm']);
    }
    if ($settings['posthog_key'] !== '') {
        $key = wp_json_encode($settings['posthog_key'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        // Audited local bootstrap copied from the legacy Genesis settings, with no arbitrary saved HTML.
        wp_print_inline_script_tag(file_get_contents(__DIR__ . '/assets/posthog-bootstrap.js') . "\nposthog.init(" . $key . ",{api_host:'https://us.i.posthog.com',defaults:'2025-05-24',person_profiles:'identified_only'});", ['id' => 'cwstr-posthog']);
    }
}
function cwstr_body() {
    static $printed = false;
    $settings = cwstr_settings();
    if ($printed || !cwstr_can_track() || $settings['gtm_id'] === '') return;
    $printed = true;
    echo '<noscript><iframe title="Google Tag Manager" src="' . esc_url('https://www.googletagmanager.com/ns.html?id=' . rawurlencode($settings['gtm_id'])) . '" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>' . "\n";
}
/** Remove only the three migrated snippets, leaving fonts and unrelated Genesis code intact. */
function cwstr_remove_legacy($html) {
    foreach ([
        '~<!-- Google Tag Manager -->\s*<script>.*?GTM-NP6FHDK.*?</script>\s*<!-- End Google Tag Manager -->~s',
        '~<!-- Google Tag Manager \(noscript\) -->\s*<noscript>.*?GTM-NP6FHDK.*?</noscript>\s*<!-- End Google Tag Manager \(noscript\) -->~s',
        '~<!-- Posthog tracking -->\s*<script>.*?phc_M57LPtrdJtmsmdDtkOvqbYcCbothXLqu8UPlLenFqyn.*?</script>\s*<!-- End Posthog tracking -->~s',
        '~<meta\s+name="facebook-domain-verification"\s+content="h3eljezaq7fill16hiomg2gol0pz7b"\s*/?>~',
    ] as $pattern) {
        $html = preg_replace($pattern, '', $html);
    }
    return $html;
}
