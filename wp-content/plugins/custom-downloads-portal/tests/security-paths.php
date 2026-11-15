<?php
// Focused smoke tests for the pure path/name rules. Run: php tests/security-paths.php
namespace {
    define('ABSPATH', __DIR__ . '/');
    final class WP_Error {}
    function __($text) { return $text; }
}

namespace SWS\DownloadsPortal {
require dirname(__DIR__) . '/includes/class-paths.php';

$failures = array();
$check = static function ($condition, $message) use (&$failures) { if (!$condition) $failures[] = $message; };
$check(Paths::normalize_relative('2026/Meeting Materials') === '2026/Meeting Materials', 'valid relative path');
foreach (array('../wp-config.php', '/etc/passwd', 'php://filter', 'C:\\Windows', '2026//secret', "bad\0name") as $bad) {
    $check(Paths::normalize_relative($bad) instanceof \WP_Error, 'reject ' . json_encode($bad));
}
$check(Paths::validate_new_name('Meeting Materials') === 'Meeting Materials', 'valid folder name');
foreach (array('.htaccess', '../escape', 'folder/name', 'NUL', 'bad:name', '.hidden') as $bad) {
    $check(Paths::validate_new_name($bad) instanceof \WP_Error, 'reject name ' . $bad);
}
$check(Paths::validate_new_name('Board Agenda.pdf', true) === 'Board Agenda.pdf', 'valid upload name');
$check(Paths::validate_new_name('payload.php.pdf.php', true) instanceof \WP_Error, 'reject executable extension');
$check(Paths::validate_new_name('payload.php.pdf', true) instanceof \WP_Error, 'reject executable inner extension');
$check(Paths::validate_new_name('macro.docm', true) instanceof \WP_Error, 'reject macro document');
if ($failures) { fwrite(STDERR, "FAIL\n- " . implode("\n- ", $failures) . "\n"); exit(1); }
echo "PASS: path and filename security rules\n";
}

