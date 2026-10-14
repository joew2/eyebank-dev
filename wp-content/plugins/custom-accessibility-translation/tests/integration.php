<?php
/** Run: php tests/integration.php /absolute/path/to/wp-load.php */
if (empty($argv[1])) { fwrite(STDERR, "Pass the path to wp-load.php.\n"); exit(1); }
define('DISABLE_WP_CRON', true);
define('WP_HTTP_BLOCK_EXTERNAL', true);
require $argv[1];
$count = 0;
function atp_assert($value, $label) { global $count; if (!$value) throw new RuntimeException($label); $count++; }
try {
 atp_assert(function_exists('cwsatp_language_pair'), 'Plugin must be active.');
 atp_assert(cwsatp_normalize_path('https://example.org/a/?x=1#hash') === '/a/', 'Full URL normalization');
 atp_assert(cwsatp_normalize_path('/a') === '/a/', 'Trailing slash normalization');
 atp_assert(cwsatp_normalize_path('https://example.org') === '/', 'Root URL normalization');
 atp_assert(cwsatp_normalize_path('javascript:alert(1)') === '', 'Reject unsafe scheme');
 atp_assert(cwsatp_normalize_path('/a/%2e%2e/b/') === '', 'Reject traversal');
 atp_assert(cwsatp_normalize_path([]) === '', 'Reject malformed input');
 atp_assert(shortcode_exists('spanish_page_link') && shortcode_exists('english_page_link'), 'Legacy shortcodes');
 $before = cwsatp_get_all_mappings();
 cwsatp_import_legacy_mappings();
 atp_assert($before === cwsatp_get_all_mappings(), 'Import idempotency');
 $wpdb->query('START TRANSACTION');
 $now = current_time('mysql');
 $en = '/__atp-test-en-'.wp_generate_password(8,false).'/'; $es = str_replace('-en-', '-es-', $en);
 $wpdb->insert(cwsatp_get_table_name(), ['english_path'=>$en,'spanish_path'=>$es,'enabled'=>1,'created_at'=>$now,'updated_at'=>$now]);
 $_SERVER['REQUEST_URI'] = $en.'?tracking=test';
 $pair = cwsatp_language_pair();
 atp_assert($pair['current'] === 'en' && $pair['es'] === home_url($es), 'English to Spanish widget');
 atp_assert(strpos(cwsatp_render_spanish_page_link(), esc_url(home_url($es))) !== false, 'Spanish shortcode destination');
 $_SERVER['REQUEST_URI'] = $es;
 $pair = cwsatp_language_pair();
 atp_assert($pair['current'] === 'es' && $pair['en'] === home_url($en), 'Spanish to English widget');
 $wpdb->update(cwsatp_get_table_name(), ['enabled'=>0], ['english_path'=>$en]);
 atp_assert(cwsatp_language_pair() === null, 'Disabled pair hidden');
 $_SERVER['REQUEST_URI'] = '/__atp-no-mapping/';
 atp_assert(cwsatp_language_pair() === null && cwsatp_render_spanish_page_link() === '', 'No unrelated fallback');
 $wpdb->query('ROLLBACK');
 atp_assert($before === cwsatp_get_all_mappings(), 'Fixtures rolled back');
 $administrators = get_users(['role'=>'administrator','number'=>1]);
 wp_set_current_user($administrators[0]->ID);
 do_action('admin_init');
 $registered = get_registered_settings();
 $sanitize = $registered['cwsatp_settings']['sanitize_callback'];
 $clean = call_user_func($sanitize, ['side'=>'invalid','offset'=>9999,'enabled'=>1]);
 atp_assert($clean['side'] === 'right' && $clean['offset'] === 500 && $clean['remember'] === 0, 'Settings sanitization');
 ob_start(); cwsatp_render_admin_page(); $admin = ob_get_clean();
 atp_assert(strpos($admin, 'name="cwsatp_nonce"') !== false && strpos($admin, 'name="_wpnonce"') !== false, 'Nonce-protected admin forms');
 atp_assert(strpos($admin, 'cwsatp_settings[side]') !== false && strpos($admin, 'cwsatp_mappings[') !== false, 'Unified settings and mappings');
 wp_set_current_user(0);
 ob_start(); cwsatp_render_admin_page(); $denied = ob_get_clean();
 atp_assert($denied === '', 'Admin screen capability gate');
 echo "ATP integration: {$count} assertions passed.\n";
} catch (Throwable $e) { $wpdb->query('ROLLBACK'); fwrite(STDERR,$e->getMessage()."\n");exit(1); }
