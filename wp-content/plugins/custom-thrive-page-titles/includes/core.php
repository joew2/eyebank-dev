<?php
namespace CWS\ThrivePageTitles;

defined('ABSPATH') || exit;

function fail(string $message): void {
    throw new \RuntimeException($message);
}

function preflight(): string {
    if (is_multisite()) fail('This migration supports single-site WordPress only.');
    if (get_stylesheet() !== 'eyedonation') fail('Activate the EyeDonation theme before processing.');
    if (!current_user_can('manage_options') || !current_user_can('edit_theme_options')) fail('Administrator theme-editing permissions are required.');
    $theme = wp_normalize_path(get_stylesheet_directory());
    $root = wp_normalize_path(get_theme_root());
    if ($theme !== $root . '/eyedonation' || !is_dir($theme . '/inc')) fail('The active EyeDonation theme directory could not be verified.');
    return $theme;
}

/** Pure source transformation used by the migration and regression test. */
function patch_functions_source(string $source): string {
    if (str_contains($source, "'document-title-compatibility'")) return $source;
    $count = 0;
    $patched = preg_replace(
        "/(foreach\\s*\\(\\s*\\[\\s*)('setup'\\s*,)/",
        "$1'document-title-compatibility',$2",
        $source,
        1,
        $count
    );
    if (!is_string($patched) || $count !== 1) fail('The theme module list was not recognized. Install the matching EyeDonation theme version, then retry.');
    return $patched;
}

function atomic_write(string $target, string $contents): void {
    if (is_link($target)) fail('Refusing to replace a symbolic link: ' . basename($target));
    $directory = dirname($target);
    if (!is_dir($directory) || !is_writable($directory)) fail('The theme directory is not writable: ' . basename($directory));
    $temp = $directory . '/.cws-title-' . wp_generate_password(12, false, false) . '.tmp';
    $handle = @fopen($temp, 'x');
    if (!$handle) fail('A temporary theme file could not be created.');
    try {
        if (!flock($handle, LOCK_EX)) fail('The temporary theme file could not be locked.');
        $length = strlen($contents);
        $written = fwrite($handle, $contents);
        if ($written !== $length || !fflush($handle)) fail('The complete theme update could not be written.');
        @chmod($temp, file_exists($target) ? (fileperms($target) & 0777) : 0644);
        flock($handle, LOCK_UN);
        fclose($handle);
        $handle = null;
        if (!@rename($temp, $target)) fail('The verified theme update could not be installed.');
    } finally {
        if (is_resource($handle)) fclose($handle);
        if (file_exists($temp)) @unlink($temp);
    }
    clearstatcache(true, $target);
    if (!is_file($target) || file_get_contents($target) !== $contents) fail('Theme file verification failed after writing.');
}

