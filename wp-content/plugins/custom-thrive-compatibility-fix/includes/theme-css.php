<?php
/** Correct Thrive's full-URL theme-name match without editing vendor code. */
namespace CWS\ThriveCompatibilityFix;
defined('ABSPATH') || exit;

/** Compare URL paths only; the hostname and query string cannot identify a theme. */
function normalize_path($path) {
    $parts = [];
    foreach (explode('/', rawurldecode($path)) as $part) {
        if ($part === '' || $part === '.') continue;
        if ($part === '..') {
            array_pop($parts);
        } else {
            $parts[] = $part;
        }
    }
    return '/' . implode('/', $parts);
}

/**
 * Retain Thrive's opt-out and only remove CSS inside the active theme directories.
 * Called by Thrive's own CSS removal pass, not on ordinary WordPress enqueues.
 * No Thrive dependency is required: without Thrive this filter is never invoked.
 */
function filter_theme_css_removal($remove, $src) {
    if (!$remove || !is_string($src) || $src === '') return $remove;

    $path = wp_parse_url($src, PHP_URL_PATH);
    if (!is_string($path) || $path === '') return $remove;
    $path = normalize_path($path);
    $theme_paths = [];
    foreach ([get_template_directory_uri(), get_stylesheet_directory_uri()] as $uri) {
        $directory = wp_parse_url($uri, PHP_URL_PATH);
        if (!is_string($directory) || $directory === '') continue;
        $directory = normalize_path($directory);
        // A root URL cannot safely distinguish theme files from other assets.
        if ($directory !== '/') $theme_paths[] = $directory . '/';
    }
    if (!$theme_paths) return $remove;
    foreach (array_unique($theme_paths) as $directory) {
        if (strpos($path, $directory) === 0) return $remove;
    }
    return false;
}

add_filter('tcb_remove_theme_css', __NAMESPACE__ . '\\filter_theme_css_removal', 20, 2);
