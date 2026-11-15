<?php
namespace CWS\CustomLinkReports;

defined('ABSPATH') || exit;

final class Settings {
    public static function defaults(): array {
        return array(
            'api_key' => array(), 'credential_generation' => 1, 'default_domain' => '',
            'timezone' => wp_timezone_string() ?: 'UTC', 'recipients' => '', 'heading' => get_bloginfo('name'),
            'accent' => '#0057b8', 'logo_id' => 0, 'footer' => '', 'retention_days' => 90,
            'cache_minutes' => 15, 'global_pause' => false, 'delete_on_uninstall' => false,
            'last_check' => 0, 'domain_count' => null, 'connection_error' => '',
        );
    }

    public static function ensure(): void {
        if (get_option(SETTINGS_OPTION, null) === null) add_option(SETTINGS_OPTION, self::defaults(), '', false);
    }

    public static function get(): array {
        $saved = get_option(SETTINGS_OPTION, array());
        return array_merge(self::defaults(), is_array($saved) ? $saved : array());
    }

    public static function update(array $settings): void {
        update_option(SETTINGS_OPTION, array_merge(self::defaults(), $settings), false);
    }

    public static function api_key(): string {
        return Crypto::decrypt(self::get()['api_key']);
    }

    public static function replace_key(string $key): void {
        $settings = self::get();
        $settings['api_key'] = Crypto::encrypt(trim($key));
        $settings['credential_generation'] = (int) $settings['credential_generation'] + 1;
        $settings['last_check'] = 0;
        $settings['domain_count'] = null;
        $settings['connection_error'] = '';
        self::update($settings);
    }

    public static function remove_key(): void {
        $settings = self::get();
        $settings['api_key'] = array();
        $settings['credential_generation'] = (int) $settings['credential_generation'] + 1;
        $settings['default_domain'] = '';
        $settings['last_check'] = 0;
        $settings['domain_count'] = null;
        $settings['connection_error'] = '';
        self::update($settings);
        Scheduler::clear_all();
    }

    public static function has_key(): bool {
        return self::api_key() !== '';
    }
}

