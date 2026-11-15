<?php
namespace SWS\DownloadsPortal;

defined('ABSPATH') || exit;

final class Download {
    public static function handle(): void {
        $library = sanitize_key((string) ($_POST['library'] ?? ''));
        $relative = isset($_POST['path']) ? wp_unslash((string) $_POST['path']) : '';
        if (!is_user_logged_in() || !Access::can($library, 'read')) {
            self::deny(403);
        }
        check_admin_referer('cdp_download_' . $library, 'cdp_download_nonce');
        if (!Protection::ready()) {
            self::deny(503, __('Downloads are temporarily locked until direct-access protection is verified.', 'custom-downloads-portal'));
        }
        $path = Paths::resolve($library, $relative);
        if (is_wp_error($path) || !Paths::is_supported_file($path)) {
            self::deny(404);
        }
        $name = basename($path);
        $size = filesize($path);
        if ($size === false) {
            self::deny(500);
        }
        while (ob_get_level()) {
            ob_end_clean();
        }
        nocache_headers();
        header('Cache-Control: private, no-store, max-age=0');
        header('X-Content-Type-Options: nosniff');
        $types = array('pdf'=>'application/pdf','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','xls'=>'application/vnd.ms-excel','xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','ppt'=>'application/vnd.ms-powerpoint','pptx'=>'application/vnd.openxmlformats-officedocument.presentationml.presentation','doc'=>'application/msword','docx'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document','zip'=>'application/zip');
        header('Content-Type: ' . ($types[strtolower(pathinfo($name, PATHINFO_EXTENSION))] ?? 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . str_replace(array('"', "\r", "\n"), '', $name) . '"; filename*=UTF-8\'\'' . rawurlencode($name));
        header('Content-Length: ' . (string) $size);
        $stream = fopen($path, 'rb');
        if (!$stream) {
            self::deny(500);
        }
        while (!feof($stream) && !connection_aborted()) {
            echo fread($stream, 1048576); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            flush();
        }
        fclose($stream);
        exit;
    }

    private static function deny(int $status, string $message = ''): void {
        status_header($status);
        nocache_headers();
        wp_die(esc_html($message ?: __('The requested download is unavailable.', 'custom-downloads-portal')), '', array('response' => $status));
    }
}
