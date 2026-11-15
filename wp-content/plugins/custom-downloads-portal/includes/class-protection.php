<?php
namespace SWS\DownloadsPortal;

defined('ABSPATH') || exit;

final class Protection {
    private const STATUS_OPTION = 'cdp_protection_status';
    private const TEST_FILE = '.cdp-direct-access-test.txt';

    public static function activate(): void {
        foreach (Access::libraries() as $library) {
            if (!is_dir($library['root'])) {
                continue;
            }
            self::install_apache_rule($library['root']);
            self::write_test_file($library['root']);
        }
        $status = get_option(self::STATUS_OPTION, array());
        $fingerprint = self::environment_fingerprint();
        // Plugin replacement may reactivate the same installation. Preserve a result
        // only when it belongs to this exact URL and filesystem environment.
        if (!is_array($status) || !hash_equals((string) ($status['fingerprint'] ?? ''), $fingerprint)) {
            update_option(self::STATUS_OPTION, array('verified' => false, 'fingerprint' => $fingerprint, 'message' => 'Direct-access protection must be tested on this environment.', 'checked' => time(), 'details' => array()), false);
        }
    }

    private static function install_apache_rule(string $root): void {
        $file = $root . '/.htaccess';
        $start = '# BEGIN Custom Downloads Portal SWS';
        $end = '# END Custom Downloads Portal SWS';
        $block = $start . "\nOptions -Indexes\n<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n" . $end;
        $existing = is_file($file) ? (string) file_get_contents($file) : '';
        if (str_contains($existing, $start)) {
            return;
        }
        $payload = rtrim($existing) . ($existing === '' ? '' : "\n\n") . $block . "\n";
        @file_put_contents($file, $payload, LOCK_EX);
    }

    private static function write_test_file(string $root): void {
        $path = $root . '/' . self::TEST_FILE;
        if (!is_file($path)) {
            @file_put_contents($path, 'CDP-DENY-' . wp_generate_password(32, false, false), LOCK_EX);
        }
    }

    public static function ready(): bool {
        $status = get_option(self::STATUS_OPTION, array());
        return !empty($status['verified']) && hash_equals((string) ($status['fingerprint'] ?? ''), self::environment_fingerprint());
    }

    public static function admin_menu(): void {
        add_management_page(__('Downloads Portal Security', 'custom-downloads-portal'), __('Downloads Portal Security', 'custom-downloads-portal'), 'manage_options', 'cdp-security', array(self::class, 'admin_page'));
    }

    public static function admin_notice(): void {
        if (!current_user_can('manage_options') || self::ready()) {
            return;
        }
        echo '<div class="notice notice-error"><p><strong>' . esc_html__('Custom Downloads Portal is locked:', 'custom-downloads-portal') . '</strong> ' . esc_html__('direct HTTP access to both document roots has not been verified. Open Tools → Downloads Portal Security.', 'custom-downloads-portal') . '</p></div>';
    }

    public static function admin_page(): void {
        if (!current_user_can('manage_options')) {
            return;
        }
        $status = get_option(self::STATUS_OPTION, array());
        echo '<div class="wrap"><h1>' . esc_html__('Downloads Portal Security', 'custom-downloads-portal') . '</h1>';
        echo '<p>' . esc_html__('The portal remains locked until a request made without a WordPress login is denied for a test file in each protected root. This tests the effective web-server path, not merely whether .htaccess exists.', 'custom-downloads-portal') . '</p>';
        echo '<p><strong>' . esc_html__('Status:', 'custom-downloads-portal') . '</strong> ' . esc_html(!empty($status['message']) ? $status['message'] : __('Not tested.', 'custom-downloads-portal')) . '</p>';
        if (!empty($status['verified']) && !self::ready()) {
            echo '<p class="notice notice-warning inline"><strong>' . esc_html__('Environment changed:', 'custom-downloads-portal') . '</strong> ' . esc_html__('the saved verification belongs to a different site URL or filesystem path, so it cannot be reused.', 'custom-downloads-portal') . '</p>';
        }
        self::render_diagnostics($status['details'] ?? array());
        if (self::is_local_environment()) {
            echo '<p><em>' . esc_html__('Local development detected: the verification request will allow a self-signed HTTPS certificate. Direct file access must still return a denied HTTP response.', 'custom-downloads-portal') . '</em></p>';
        }
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="cdp_verify_protection">';
        wp_nonce_field('cdp_verify_protection');
        submit_button(__('Verify direct-access protection', 'custom-downloads-portal'));
        echo '</form><h2>' . esc_html__('Nginx / reverse proxy', 'custom-downloads-portal') . '</h2><p>' . esc_html__('If verification fails while Apache rules are present, install the bundled server/nginx-protected-libraries.conf rules in the effective Nginx virtual host, reload Nginx, purge caches, and test again.', 'custom-downloads-portal') . '</p></div>';
    }

    public static function handle_verification(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You are not allowed to do that.', 'custom-downloads-portal'), '', array('response' => 403));
        }
        check_admin_referer('cdp_verify_protection');
        $failures = array();
        $details = array();
        foreach (Access::libraries() as $definition) {
            self::install_apache_rule($definition['root']);
            self::write_test_file($definition['root']);
            $url = content_url('/' . rawurlencode($definition['root_label']) . '/' . rawurlencode(self::TEST_FILE));
            foreach (array('GET', 'HEAD') as $method) {
                $response = wp_remote_request($url, array(
                    'method' => $method,
                    'timeout' => 12,
                    'redirection' => 0,
                    'sslverify' => !self::is_local_environment(),
                    'headers' => array('Cache-Control' => 'no-cache'),
                    'cookies' => array(),
                ));
                if (is_wp_error($response)) {
                    $error = $response->get_error_message();
                    $failures[] = $definition['root_label'] . ' ' . $method . ': ' . $error;
                    $details[] = array('library' => $definition['root_label'], 'method' => $method, 'url' => $url, 'result' => 'Connection error', 'detail' => $error);
                    continue;
                }
                $code = wp_remote_retrieve_response_code($response);
                $body = $method === 'GET' ? wp_remote_retrieve_body($response) : '';
                $denied = $code >= 400 && !str_starts_with($body, 'CDP-DENY-');
                $details[] = array('library' => $definition['root_label'], 'method' => $method, 'url' => $url, 'result' => $denied ? 'Denied as required' : 'FAILED', 'detail' => 'HTTP ' . $code);
                if (!$denied) {
                    $failures[] = $definition['root_label'] . ' ' . $method . ': direct request returned HTTP ' . $code . ($body !== '' && str_starts_with($body, 'CDP-DENY-') ? ' and exposed the test file' : '');
                }
            }
        }
        $verified = !$failures;
        $message = $verified ? __('Verified: direct requests to both test files were denied.', 'custom-downloads-portal') : __('Not verified: ', 'custom-downloads-portal') . implode('; ', $failures);
        update_option(self::STATUS_OPTION, array('verified' => $verified, 'fingerprint' => self::environment_fingerprint(), 'message' => $message, 'checked' => time(), 'details' => $details), false);
        wp_safe_redirect(admin_url('tools.php?page=cdp-security'));
        exit;
    }

    private static function environment_fingerprint(): string {
        return hash('sha256', home_url('/') . '|' . ABSPATH . '|' . WP_CONTENT_DIR);
    }

    private static function render_diagnostics($details): void {
        if (!is_array($details) || !$details) return;
        echo '<h2>' . esc_html__('Last verification details', 'custom-downloads-portal') . '</h2><table class="widefat striped"><thead><tr><th>' . esc_html__('Library', 'custom-downloads-portal') . '</th><th>' . esc_html__('Method', 'custom-downloads-portal') . '</th><th>' . esc_html__('Result', 'custom-downloads-portal') . '</th><th>' . esc_html__('Response', 'custom-downloads-portal') . '</th><th>' . esc_html__('Test URL', 'custom-downloads-portal') . '</th></tr></thead><tbody>';
        foreach ($details as $row) {
            $url = (string) ($row['url'] ?? '');
            echo '<tr><td>' . esc_html((string) ($row['library'] ?? '')) . '</td><td>' . esc_html((string) ($row['method'] ?? '')) . '</td><td>' . esc_html((string) ($row['result'] ?? '')) . '</td><td>' . esc_html((string) ($row['detail'] ?? '')) . '</td><td><a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . esc_html__('Open test URL', 'custom-downloads-portal') . '</a></td></tr>';
        }
        echo '</tbody></table><p>' . esc_html__('A passing result is an HTTP denial for both GET and HEAD. A connection error means WordPress could not reach its own public hostname; HTTP 200 means the test file was publicly served.', 'custom-downloads-portal') . '</p>';
    }

    private static function is_local_environment(): bool {
        if (function_exists('wp_get_environment_type') && in_array(wp_get_environment_type(), array('local', 'development'), true)) {
            return true;
        }
        $host = strtolower((string) wp_parse_url(home_url('/'), PHP_URL_HOST));
        if ($host === '' || $host === 'localhost' || $host === '127.0.0.1' || $host === '::1') {
            return true;
        }
        if (!str_contains($host, '.') || preg_match('/\.(?:test|local|localhost)$/', $host)) {
            return true;
        }
        return (bool) preg_match('/^(?:10\.|127\.|192\.168\.|172\.(?:1[6-9]|2\d|3[01])\.)/', $host);
    }
}

