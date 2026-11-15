<?php
namespace SWS\DownloadsPortal;

defined('ABSPATH') || exit;

final class Paths {
    public const ALLOWED_EXTENSIONS = array('pdf', 'jpg', 'jpeg', 'xls', 'xlsx', 'ppt', 'pptx', 'doc', 'docx', 'zip');
    private const CONTROL_NAMES = array('.htaccess', '.user.ini', '.tmb', '.quarantine', '.ds_store', 'web.config');

    public static function normalize_relative($value) {
        if (!is_string($value) || strlen($value) > 2048 || preg_match('/[\x00-\x1F\x7F]/', $value)) {
            return new \WP_Error('cdp_bad_path', __('Invalid folder path.', 'custom-downloads-portal'), array('status' => 400));
        }
        $value = str_replace('\\', '/', $value);
        if (preg_match('#^(?:[a-z][a-z0-9+.-]*:|/|[a-z]:)#i', $value) || str_contains($value, '//')) {
            return new \WP_Error('cdp_bad_path', __('Invalid folder path.', 'custom-downloads-portal'), array('status' => 400));
        }
        $value = trim($value, '/');
        if ($value === '') {
            return '';
        }
        $parts = explode('/', $value);
        foreach ($parts as $part) {
            if (!self::valid_existing_name($part)) {
                return new \WP_Error('cdp_bad_path', __('Invalid folder path.', 'custom-downloads-portal'), array('status' => 400));
            }
        }
        return implode('/', $parts);
    }

    public static function valid_existing_name(string $name): bool {
        return $name !== '' && $name !== '.' && $name !== '..' && !str_contains($name, '/') && !str_contains($name, '\\') && !preg_match('/[\x00-\x1F\x7F]/', $name);
    }

    public static function validate_new_name($name, bool $is_file = false) {
        if (!is_string($name)) {
            return new \WP_Error('cdp_bad_name', __('Enter a valid name.', 'custom-downloads-portal'), array('status' => 400));
        }
        $name = trim($name);
        if (strlen($name) > 255 || !self::valid_existing_name($name) || $name[0] === '.' || str_contains($name, ':') || rtrim($name, ". ") !== $name || in_array(strtolower($name), self::CONTROL_NAMES, true)) {
            return new \WP_Error('cdp_bad_name', __('That name is not permitted. Use one plain file or folder name.', 'custom-downloads-portal'), array('status' => 400));
        }
        if (preg_match('/^(?:con|prn|aux|nul|com[1-9]|lpt[1-9])(?:\.|$)/i', $name)) {
            return new \WP_Error('cdp_bad_name', __('That reserved name is not permitted.', 'custom-downloads-portal'), array('status' => 400));
        }
        if ($is_file) {
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
                return new \WP_Error('cdp_bad_type', __('This file type is not allowed.', 'custom-downloads-portal'), array('status' => 415));
            }
            $dangerous = array('php', 'php3', 'php4', 'php5', 'phtml', 'phar', 'cgi', 'pl', 'py', 'sh', 'exe', 'dll', 'js', 'html', 'htm', 'svg');
            $segments = array_map('strtolower', explode('.', $name));
            array_pop($segments);
            if (array_intersect($dangerous, $segments)) {
                return new \WP_Error('cdp_bad_type', __('Double-extension and executable filenames are not allowed.', 'custom-downloads-portal'), array('status' => 415));
            }
        }
        return $name;
    }

    public static function is_control_name(string $name): bool {
        return $name[0] === '.' || in_array(strtolower($name), self::CONTROL_NAMES, true);
    }

    public static function root(string $library) {
        $definition = Access::library($library);
        if (!$definition || !is_dir($definition['root']) || is_link($definition['root'])) {
            return new \WP_Error('cdp_root_unavailable', __('The document library is unavailable. Contact an administrator.', 'custom-downloads-portal'), array('status' => 503));
        }
        $root = realpath($definition['root']);
        return $root ?: new \WP_Error('cdp_root_unavailable', __('The document library is unavailable.', 'custom-downloads-portal'), array('status' => 503));
    }

    public static function resolve(string $library, $relative = '', bool $must_exist = true) {
        $relative = self::normalize_relative($relative);
        if (is_wp_error($relative)) {
            return $relative;
        }
        $root = self::root($library);
        if (is_wp_error($root)) {
            return $root;
        }
        $candidate = $relative === '' ? $root : $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
        if ($must_exist) {
            if (!file_exists($candidate) || self::contains_symlink($root, $candidate)) {
                return new \WP_Error('cdp_not_found', __('The requested item was not found.', 'custom-downloads-portal'), array('status' => 404));
            }
            $resolved = realpath($candidate);
            if (!$resolved || !self::inside($root, $resolved)) {
                return new \WP_Error('cdp_unsafe_path', __('The requested item is outside the permitted library.', 'custom-downloads-portal'), array('status' => 400));
            }
            return $resolved;
        }
        $parent_path = dirname($candidate);
        $parent = realpath($parent_path);
        if (!$parent || self::contains_symlink($root, $parent_path) || !self::inside($root, $parent)) {
            return new \WP_Error('cdp_unsafe_path', __('The destination folder is invalid.', 'custom-downloads-portal'), array('status' => 400));
        }
        return $parent . DIRECTORY_SEPARATOR . basename($candidate);
    }

    public static function inside(string $root, string $path): bool {
        return $path === $root || str_starts_with($path, $root . DIRECTORY_SEPARATOR);
    }

    public static function relative(string $root, string $path): string {
        return $path === $root ? '' : str_replace(DIRECTORY_SEPARATOR, '/', substr($path, strlen($root) + 1));
    }

    private static function contains_symlink(string $root, string $path): bool {
        if (!self::inside($root, $path)) return true;
        $relative = self::relative($root, $path);
        if ($relative === '') return is_link($root);
        $cursor = $root;
        foreach (explode('/', str_replace(DIRECTORY_SEPARATOR, '/', $relative)) as $part) {
            $cursor .= DIRECTORY_SEPARATOR . $part;
            if (is_link($cursor)) return true;
        }
        return false;
    }

    public static function is_supported_file(string $path): bool {
        if (!is_file($path) || is_link($path) || !in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), self::ALLOWED_EXTENSIONS, true)) {
            return false;
        }
        $stat = @stat($path);
        return is_array($stat) && (int) $stat['nlink'] <= 1;
    }
}
