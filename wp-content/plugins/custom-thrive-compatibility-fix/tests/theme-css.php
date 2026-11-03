<?php
/** Isolated regression tests using WordPress hooks and Thrive's real cleanup code. */
define('ABSPATH', dirname(__DIR__, 4) . '/');
require ABSPATH . 'wp-includes/plugin.php';
$GLOBALS['parent_uri'] = 'https://www.eyedonation.org/wp-content/themes/eyedonation';
$GLOBALS['child_uri'] = $GLOBALS['parent_uri'];
function wp_parse_url($url, $component = -1) { return parse_url($url, $component); }
function get_template_directory_uri() { return $GLOBALS['parent_uri']; }
function get_stylesheet_directory_uri() { return $GLOBALS['child_uri']; }
function get_template() { return 'eyedonation'; }
function get_stylesheet_directory() { return '/site/wp-content/themes/eyedonation'; }
function wp_deregister_style($handle) { $GLOBALS['removed'][] = $handle; }
function check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
require dirname(__DIR__) . '/custom-thrive-compatibility-fix.php';
$callback = 'CWS\\ThriveCompatibilityFix\\filter_theme_css_removal';
check(has_filter('tcb_remove_theme_css', $callback) === 20, 'Compatibility hook missing');
$cases = [
    ['https://www.eyedonation.org/wp-content/plugins/custom-accessibility-translation/assets/widget.css', false],
    ['https://www.eyedonation.org/wp-content/plugins/thrive-visual-editor/landing-page/templates/css/blank_v2.css', false],
    ['https://www.eyedonation.org/wp-includes/css/dashicons.min.css', false],
    ['https://www.eyedonation.org/wp-content/uploads/generated-eyedonation.css', false],
    ['https://www.eyedonation.org/wp-content/themes/eyedonation/assets/css/base.css', true],
    ['https://cdn.example.test/wp-content/themes/eyedonation/style.css?ver=1', true],
    ['//www.eyedonation.org/wp-content/plugins/widget/widget.css', false],
    ['/wp-content/themes/eyedonation/style.css', true],
    ['/wp-content/themes/eyedonation-other/style.css', false],
    ['/wp-content/plugins/widget.css?theme=/wp-content/themes/eyedonation/style.css', false],
    ['/wp-content/themes/eyedonation/../other/style.css', false],
    ['/wp-content/themes/eyedonation/%2e%2e/other/style.css', false],
    ['/wp-content/themes/eyedonation/assets/../style.css', true],
    ['', true], [false, true], [null, true], ['http:///', true],
];
foreach ($cases as [$src, $expected]) {
    check(apply_filters('tcb_remove_theme_css', true, $src) === $expected, 'Wrong decision: ' . var_export($src, true));
    check(apply_filters('tcb_remove_theme_css', false, $src) === false, 'Existing opt-out overridden');
}
$GLOBALS['child_uri'] = 'https://cdn.example.test/wp-content/themes/eye-child';
check(apply_filters('tcb_remove_theme_css', true, '/wp-content/themes/eye-child/style.css') === true, 'Child theme not recognized');
check(apply_filters('tcb_remove_theme_css', true, '/wp-content/themes/eyedonation/style.css') === true, 'Parent theme not recognized');
$GLOBALS['parent_uri'] = 'https://host.test/subsite/custom-content/themes/my%20theme';
$GLOBALS['child_uri'] = $GLOBALS['parent_uri'];
check(apply_filters('tcb_remove_theme_css', true, 'https://cdn.test/subsite/custom-content/themes/my%20theme/style.css') === true, 'Subdirectory/encoded theme path failed');
check(apply_filters('tcb_remove_theme_css', true, 'https://cdn.test/subsite/custom-content/plugins/widget.css') === false, 'Custom content plugin misclassified');
$GLOBALS['parent_uri'] = $GLOBALS['child_uri'] = 'https://host.test/';
check(apply_filters('tcb_remove_theme_css', true, '/unknown.css') === true, 'Unclassifiable theme directory should preserve decision');
$GLOBALS['parent_uri'] = $GLOBALS['child_uri'] = 'https://host.test/wp-content/themes/eyedonation';
$source = file_get_contents(ABSPATH . 'wp-content/plugins/thrive-visual-editor/inc/functions.php');
check((bool) preg_match('/function tve_remove_theme_css\(\) \{.*?\n\}/s', $source, $matches), 'Actual Thrive cleanup function unavailable');
eval($matches[0]);
foreach ([false, true] as $fixed) {
    if (!$fixed) remove_filter('tcb_remove_theme_css', $callback, 20);
    else add_filter('tcb_remove_theme_css', $callback, 20, 2);
    foreach (['https://eyebank:8890', 'https://www.eyedonation.org'] as $host) {
        $GLOBALS['removed'] = [];
        $paths = [
            'cwsatp' => '/wp-content/plugins/custom-accessibility-translation/assets/widget.css',
            'tve_landing_page_blank_v2' => '/wp-content/plugins/thrive-visual-editor/landing-page/templates/css/blank_v2.css',
            'ed-base' => '/wp-content/themes/eyedonation/assets/css/base.css',
            'wp-core' => '/wp-includes/css/dashicons.min.css',
        ];
        $wp_styles = (object) ['queue' => array_keys($paths), 'registered' => []];
        foreach ($paths as $handle => $path) $wp_styles->registered[$handle] = (object) ['src' => $host . $path];
        tve_remove_theme_css();
        $expected = ($fixed || $host === 'https://eyebank:8890') ? ['ed-base'] : array_keys($paths);
        check($GLOBALS['removed'] === $expected, 'Actual Thrive regression failed: ' . $host);
    }
}
echo "PASS: actual Thrive cleanup, local/live hostname collision, plugin/core preservation, parent/child/CDN paths, opt-outs and invalid input.\n";
