<?php
define('ABSPATH', dirname(__DIR__, 4) . '/');
function fail_test($ok, $message) { if (!$ok) throw new RuntimeException($message); }
require dirname(__DIR__) . '/includes/core.php';

$source = "<?php\nforeach (['setup','assets'] as \$module) require \$module;\n";
$patched = CWS\ThrivePageTitles\patch_functions_source($source);
fail_test(str_contains($patched, "['document-title-compatibility','setup','assets']"), 'Module was not inserted before setup.');
fail_test(CWS\ThrivePageTitles\patch_functions_source($patched) === $patched, 'Patch is not idempotent.');
$payload = file_get_contents(dirname(__DIR__) . '/payloads/document-title-compatibility.php');
fail_test(str_contains($payload, "add_theme_support('title-tag')"), 'Payload does not enable title-tag support.');
fail_test(substr_count($payload, "add_theme_support('title-tag')") === 1, 'Payload duplicates title-tag support.');
echo "PASS: narrow title-tag payload, deterministic module insertion, and idempotent retry.\n";

