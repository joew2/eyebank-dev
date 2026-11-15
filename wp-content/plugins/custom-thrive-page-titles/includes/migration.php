<?php
namespace CWS\ThrivePageTitles;

defined('ABSPATH') || exit;

function process_migration(): string {
    $theme = preflight();
    $functions = $theme . '/functions.php';
    $module = $theme . '/inc/document-title-compatibility.php';
    $payload = file_get_contents(DIR . '/payloads/document-title-compatibility.php');
    if (!is_string($payload) || $payload === '') fail('The bundled title-support module is missing.');
    if (!is_file($functions) || is_link($functions)) fail('The EyeDonation functions.php file could not be verified.');

    $current_functions = file_get_contents($functions);
    $current_module = is_file($module) ? file_get_contents($module) : null;
    if (!is_string($current_functions)) fail('The EyeDonation functions.php file could not be read.');
    if ($current_module !== null && $current_module !== $payload) fail('A different document-title compatibility module already exists. Review it before processing.');

    $patched_functions = patch_functions_source($current_functions);
    $history = get_option(HISTORY_OPTION, array());
    if (!is_array($history) || !$history) {
        $history = array(
            'started' => time(),
            'actor' => get_current_user_id(),
            'functions_before' => $current_functions,
            'module_existed' => $current_module !== null,
            'module_before' => $current_module,
            'functions_after_sha256' => hash('sha256', $patched_functions),
            'module_after_sha256' => hash('sha256', $payload),
            'status' => 'running',
        );
        update_option(HISTORY_OPTION, $history, false);
    }

    $lock_path = $theme . '/.cws-thrive-title-migration.lock';
    $lock = @fopen($lock_path, 'c');
    if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) fail('Another title migration is running. Wait a moment, then retry.');
    try {
        if ($current_module === null) atomic_write($module, $payload);
        if ($patched_functions !== $current_functions) atomic_write($functions, $patched_functions);
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
        @unlink($lock_path);
    }

    $installed_functions = file_get_contents($functions);
    $installed_module = file_get_contents($module);
    if (!str_contains((string) $installed_functions, "'document-title-compatibility'") || $installed_module !== $payload) fail('The installed title-support files did not pass verification.');
    $history['status'] = 'completed';
    $history['completed'] = time();
    update_option(HISTORY_OPTION, $history, false);

    $pages = array();
    foreach (array('es', 'spanish-nuestra-historia') as $slug) {
        $page = get_page_by_path($slug, OBJECT, 'page');
        if ($page && $page->post_status !== 'trash') $pages[] = '/' . $slug . '/';
    }
    return 'Document title support installed and verified' . ($pages ? ' for the affected pages ' . implode(' and ', $pages) : '') . '. Purge caches before testing.';
}

