<?php
namespace CWS\CustomLinkReports;

defined('ABSPATH') || exit;

final class Crypto {
    private static function key(): string {
        return hash('sha256', wp_salt('auth') . '|' . ABSPATH . '|cws-clr-v1', true);
    }

    public static function available(): bool {
        return function_exists('sodium_crypto_secretbox') && function_exists('sodium_crypto_secretbox_open');
    }

    public static function encrypt(string $plain): array {
        if (!self::available()) {
            throw new \RuntimeException(__('Authenticated encryption is unavailable. Enable the PHP sodium extension.', 'custom-link-reports'));
        }
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        return array(
            'v' => 1,
            'n' => base64_encode($nonce),
            'c' => base64_encode(sodium_crypto_secretbox($plain, $nonce, self::key())),
        );
    }

    public static function decrypt($payload): string {
        if (!self::available() || !is_array($payload) || (int) ($payload['v'] ?? 0) !== 1) return '';
        $nonce = base64_decode((string) ($payload['n'] ?? ''), true);
        $cipher = base64_decode((string) ($payload['c'] ?? ''), true);
        if (!is_string($nonce) || !is_string($cipher) || strlen($nonce) !== SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) return '';
        $plain = sodium_crypto_secretbox_open($cipher, $nonce, self::key());
        return is_string($plain) ? $plain : '';
    }
}

