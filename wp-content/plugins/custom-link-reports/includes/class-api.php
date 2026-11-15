<?php
namespace CWS\CustomLinkReports;

defined('ABSPATH') || exit;

final class Api {
    private string $key;
    private array $settings;

    public function __construct(?string $key = null) {
        $this->settings = Settings::get();
        $this->key = $key ?? Settings::api_key();
    }

    private function request(string $method, string $url, ?array $body = null) {
        if ($this->key === '') return new \WP_Error('cws_clr_no_key', __('Connect a Short.io private API key first.', 'custom-link-reports'));
        $host = strtolower((string) wp_parse_url($url, PHP_URL_HOST));
        if (!in_array($host, array('api.short.io', 'statistics.short.io'), true) || !str_starts_with($url, 'https://')) {
            return new \WP_Error('cws_clr_bad_host', __('Blocked an unexpected API destination.', 'custom-link-reports'));
        }
        $args = array(
            'method' => $method, 'timeout' => 20, 'redirection' => 0, 'sslverify' => true,
            'headers' => array('Authorization' => $this->key, 'Accept' => 'application/json'),
        );
        if ($body !== null) {
            $args['headers']['Content-Type'] = 'application/json';
            $args['body'] = wp_json_encode($body);
        }
        $response = wp_remote_request($url, $args);
        if (is_wp_error($response)) return new \WP_Error('cws_clr_transport', __('Short.io could not be reached: ', 'custom-link-reports') . $response->get_error_message());
        $code = wp_remote_retrieve_response_code($response);
        $decoded = json_decode(wp_remote_retrieve_body($response), true);
        if ($code >= 200 && $code < 300 && is_array($decoded)) return $decoded;
        $message = is_array($decoded) ? sanitize_text_field((string) ($decoded['message'] ?? $decoded['error'] ?? '')) : '';
        if (in_array($code, array(401, 403), true)) return new \WP_Error('cws_clr_auth', __('Short.io rejected the key or its permissions.', 'custom-link-reports'), array('status' => $code));
        if ($code === 429) {
            $retry = absint(wp_remote_retrieve_header($response, 'retry-after'));
            return new \WP_Error('cws_clr_rate', __('Short.io rate limit reached. Try again later.', 'custom-link-reports'), array('status' => 429, 'retry_after' => $retry));
        }
        if ($code >= 500) return new \WP_Error('cws_clr_server', __('Short.io is temporarily unavailable.', 'custom-link-reports'), array('status' => $code));
        return new \WP_Error('cws_clr_response', $message ?: sprintf(__('Unexpected Short.io response (HTTP %d).', 'custom-link-reports'), $code), array('status' => $code));
    }

    private function cache_key(string $type, array $parts): string {
        return 'cws_clr_' . $type . '_' . md5(wp_json_encode(array_merge(array((int) $this->settings['credential_generation']), $parts)));
    }

    public function domains(bool $refresh = false) {
        $key = $this->cache_key('domains', array());
        if (!$refresh && ($cached = get_transient($key)) !== false) return $cached;
        $all = array();
        for ($offset = 0; $offset < 3000; $offset += 300) {
            $data = $this->request('GET', add_query_arg(array('limit' => 300, 'offset' => $offset), 'https://api.short.io/api/domains'));
            if (is_wp_error($data)) return $data;
            $page = array_is_list($data) ? $data : (array) ($data['domains'] ?? array());
            foreach ($page as $domain) {
                if (!is_array($domain)) continue;
                $id = (string) ($domain['id'] ?? $domain['DomainId'] ?? '');
                if ($id === '') continue;
                $all[] = array('id' => $id, 'hostname' => sanitize_text_field((string) ($domain['hostname'] ?? $domain['domain'] ?? $domain['name'] ?? $id)));
            }
            if (count($page) < 300) break;
        }
        set_transient($key, $all, max(60, (int) $this->settings['cache_minutes'] * MINUTE_IN_SECONDS));
        return $all;
    }

    public function links(string $domain_id, string $page_token = '', bool $refresh = false) {
        if (!preg_match('/^\d+$/', $domain_id)) return new \WP_Error('cws_clr_domain', __('Invalid domain identifier.', 'custom-link-reports'));
        $key = $this->cache_key('links', array($domain_id, $page_token));
        if (!$refresh && ($cached = get_transient($key)) !== false) return $cached;
        $query = array('domain_id' => $domain_id, 'limit' => 150);
        if ($page_token !== '') $query['pageToken'] = $page_token;
        $data = $this->request('GET', add_query_arg($query, 'https://api.short.io/api/links'));
        if (is_wp_error($data)) return $data;
        $items = array();
        foreach ((array) ($data['links'] ?? array()) as $link) {
            if (!is_array($link)) continue;
            $id = (string) ($link['idString'] ?? $link['id'] ?? '');
            if ($id === '') continue;
            $items[] = array(
                'id' => sanitize_text_field($id), 'domain_id' => (string) ($link['DomainId'] ?? $domain_id),
                'label' => sanitize_text_field((string) ($link['title'] ?? $link['path'] ?? $id)),
                'short_url' => esc_url_raw((string) ($link['secureShortURL'] ?? $link['shortURL'] ?? '')),
                'destination_url' => esc_url_raw((string) ($link['originalURL'] ?? '')),
            );
        }
        $result = array('links' => $items, 'next_page_token' => sanitize_text_field((string) ($data['nextPageToken'] ?? '')), 'count' => isset($data['count']) ? absint($data['count']) : null);
        set_transient($key, $result, max(60, (int) $this->settings['cache_minutes'] * MINUTE_IN_SECONDS));
        return $result;
    }

    public function statistics(string $link_id, string $start, string $end, string $timezone, bool $refresh = false) {
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $link_id)) return new \WP_Error('cws_clr_link', __('Invalid link identifier.', 'custom-link-reports'));
        if (!self::valid_date($start) || !self::valid_date($end) || !in_array($timezone, timezone_identifiers_list(), true)) return new \WP_Error('cws_clr_period', __('Invalid reporting period.', 'custom-link-reports'));
        $key = $this->cache_key('stats', array($link_id, $start, $end, $timezone));
        if (!$refresh && ($cached = get_transient($key)) !== false) return $cached;
        $url = 'https://statistics.short.io/statistics/link/' . rawurlencode($link_id);
        $data = $this->request('POST', $url, array('period' => 'custom', 'tz' => $timezone, 'startDate' => $start, 'endDate' => $end, 'clicksChartInterval' => 'day', 'skipTops' => false));
        if (is_wp_error($data)) return $data;
        $result = self::normalize($data, $link_id, $start, $end, $timezone);
        if (is_wp_error($result)) return $result;
        set_transient($key, $result, max(60, (int) $this->settings['cache_minutes'] * MINUTE_IN_SECONDS));
        return $result;
    }

    public static function normalize(array $data, string $link_id, string $start, string $end, string $timezone) {
        if (!array_key_exists('totalClicks', $data) || !is_numeric($data['totalClicks'])) return new \WP_Error('cws_clr_malformed', __('Short.io returned analytics in an unsupported format.', 'custom-link-reports'));
        $daily = array();
        foreach ((array) ($data['clickStatistics']['datasets'] ?? array()) as $dataset) {
            foreach ((array) ($dataset['data'] ?? array()) as $point) {
                if (!is_array($point) || !isset($point['x']) || !is_numeric($point['y'] ?? null)) continue;
                try { $date = (new \DateTimeImmutable((string) $point['x']))->setTimezone(new \DateTimeZone($timezone))->format('Y-m-d'); }
                catch (\Exception $e) { continue; }
                $daily[$date] = ($daily[$date] ?? 0) + (int) $point['y'];
            }
        }
        ksort($daily);
        return array(
            'link_id' => $link_id, 'period_start' => $start, 'period_end' => $end, 'timezone' => $timezone,
            'fetched_at' => time(), 'total_clicks' => (int) $data['totalClicks'],
            'human_clicks' => isset($data['humanClicks']) && is_numeric($data['humanClicks']) ? (int) $data['humanClicks'] : null,
            'daily' => $daily,
            'breakdowns' => array(
                'referrers' => self::ranked($data['referer'] ?? null, 'referer'),
                'countries' => self::ranked($data['country'] ?? null, 'countryName', 'country'),
                'browsers' => self::ranked($data['browser'] ?? null, 'browser'),
                'os' => self::ranked($data['os'] ?? null, 'os'),
            ),
        );
    }

    private static function ranked($rows, string $label_key, string $fallback = ''): ?array {
        if (!is_array($rows)) return null;
        $out = array();
        foreach ($rows as $row) {
            if (!is_array($row) || !is_numeric($row['score'] ?? null)) continue;
            $label = sanitize_text_field((string) ($row[$label_key] ?? ($fallback !== '' ? ($row[$fallback] ?? '') : '')));
            if ($label !== '') $out[] = array('label' => $label, 'value' => (int) $row['score']);
        }
        return $out;
    }

    private static function valid_date(string $date): bool {
        $d = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}

