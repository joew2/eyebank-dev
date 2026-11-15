<?php
namespace SWS\DownloadsPortal;

defined('ABSPATH') || exit;

final class Rest {
    private const MAX_UPLOAD = 52428800;
    private const MAX_ENTRIES = 500;

    public static function register(): void {
        register_rest_route(REST_NAMESPACE, '/libraries', array('methods' => 'GET', 'callback' => array(self::class, 'libraries'), 'permission_callback' => array(self::class, 'approved_user')));
        register_rest_route(REST_NAMESPACE, '/entries', array('methods' => 'GET', 'callback' => array(self::class, 'entries'), 'permission_callback' => static fn($r) => Access::rest_permission($r, 'read')));
        register_rest_route(REST_NAMESPACE, '/folders', array('methods' => 'GET', 'callback' => array(self::class, 'folders'), 'permission_callback' => static fn($r) => Access::rest_permission($r, 'read')));
        register_rest_route(REST_NAMESPACE, '/uploads', array('methods' => 'POST', 'callback' => array(self::class, 'upload'), 'permission_callback' => static fn($r) => Access::rest_permission($r, 'manage')));
        register_rest_route(REST_NAMESPACE, '/folders', array('methods' => 'POST', 'callback' => array(self::class, 'create_folder'), 'permission_callback' => static fn($r) => Access::rest_permission($r, 'manage')));
        register_rest_route(REST_NAMESPACE, '/delete', array('methods' => 'POST', 'callback' => array(self::class, 'delete'), 'permission_callback' => static fn($r) => Access::rest_permission($r, 'manage')));
    }

    public static function approved_user() {
        if (!is_user_logged_in()) {
            return new \WP_Error('cdp_signed_out', __('Please sign in to access downloads.', 'custom-downloads-portal'), array('status' => 401));
        }
        return Access::permissions() ? true : new \WP_Error('cdp_forbidden', __('You do not have access to the downloads portal.', 'custom-downloads-portal'), array('status' => 403));
    }

    private static function require_ready() {
        return Protection::ready() ? true : new \WP_Error('cdp_protection_unverified', __('The portal is locked until direct-access protection is verified by an administrator.', 'custom-downloads-portal'), array('status' => 503));
    }

    public static function libraries(): \WP_REST_Response {
        $definitions = Access::libraries();
        $result = array();
        foreach (Access::permissions() as $id => $permission) {
            $result[] = array('id' => $id, 'label' => $definitions[$id]['label'], 'rootLabel' => $definitions[$id]['root_label'], 'canManage' => $permission === 'manage');
        }
        return rest_ensure_response(array('libraries' => $result, 'protectionReady' => Protection::ready()));
    }

    public static function entries(\WP_REST_Request $request) {
        $ready = self::require_ready();
        if (is_wp_error($ready)) return $ready;
        $library = sanitize_key((string) $request['library']);
        $relative = Paths::normalize_relative((string) ($request['path'] ?? ''));
        if (is_wp_error($relative)) return $relative;
        $directory = Paths::resolve($library, $relative);
        if (is_wp_error($directory)) return $directory;
        if (!is_dir($directory)) return new \WP_Error('cdp_not_folder', __('The requested path is not a folder.', 'custom-downloads-portal'), array('status' => 400));
        $sort = (string) ($request['sort'] ?? 'newest');
        if (!in_array($sort, array('newest', 'oldest', 'name_asc', 'name_desc'), true)) $sort = 'newest';
        $items = self::read_directory($library, $directory, $sort);
        if (is_wp_error($items)) return $items;
        $root = Paths::root($library);
        $definition = Access::library($library);
        $crumbs = array(array('name' => $definition['root_label'], 'path' => ''));
        $built = '';
        if ($relative !== '') {
            foreach (explode('/', $relative) as $part) {
                $built = ltrim($built . '/' . $part, '/');
                $crumbs[] = array('name' => $part, 'path' => $built);
            }
        }
        return rest_ensure_response(array(
            'library' => $library, 'path' => $relative, 'parent' => $relative === '' ? null : dirname(str_replace('/', DIRECTORY_SEPARATOR, $relative)),
            'breadcrumbs' => $crumbs, 'entries' => $items, 'count' => count($items),
            'canManage' => Access::can($library, 'manage'), 'downloadNonce' => wp_create_nonce('cdp_download_' . $library),
            'downloadUrl' => admin_url('admin-post.php'), 'serverUploadLimit' => self::environment_upload_limit(),
        ));
    }

    private static function read_directory(string $library, string $directory, string $sort) {
        $root = Paths::root($library);
        if (is_wp_error($root)) return $root;
        $handle = @opendir($directory);
        if (!$handle) return new \WP_Error('cdp_unreadable', __('This folder cannot be read.', 'custom-downloads-portal'), array('status' => 500));
        $items = array();
        while (($name = readdir($handle)) !== false) {
            if ($name === '.' || $name === '..' || Paths::is_control_name($name)) continue;
            $path = $directory . DIRECTORY_SEPARATOR . $name;
            if (is_link($path)) continue;
            $is_dir = is_dir($path);
            if (!$is_dir && !Paths::is_supported_file($path)) continue;
            $stat = @stat($path);
            if (!$stat) continue;
            $items[] = array(
                'name' => $name, 'path' => Paths::relative($root, $path), 'type' => $is_dir ? 'folder' : 'file',
                'extension' => $is_dir ? '' : strtolower(pathinfo($name, PATHINFO_EXTENSION)),
                'modified' => (int) $stat['mtime'], 'modifiedLabel' => wp_date('M j, Y', (int) $stat['mtime']),
                'size' => $is_dir ? null : (int) $stat['size'], 'sizeLabel' => $is_dir ? '—' : size_format((int) $stat['size'], 1),
            );
            if (count($items) > self::MAX_ENTRIES) {
                closedir($handle);
                return new \WP_Error('cdp_too_many_entries', __('This folder contains too many items to display safely. Contact an administrator.', 'custom-downloads-portal'), array('status' => 413));
            }
        }
        closedir($handle);
        usort($items, static function ($a, $b) use ($sort) {
            if ($a['type'] !== $b['type']) return $a['type'] === 'folder' ? -1 : 1;
            if ($sort === 'name_asc') return strnatcasecmp($a['name'], $b['name']);
            if ($sort === 'name_desc') return strnatcasecmp($b['name'], $a['name']);
            $value = $a['modified'] <=> $b['modified'];
            return $sort === 'oldest' ? $value : -$value;
        });
        return $items;
    }

    public static function folders(\WP_REST_Request $request) {
        $ready = self::require_ready(); if (is_wp_error($ready)) return $ready;
        $library = sanitize_key((string) $request['library']);
        $relative = Paths::normalize_relative((string) ($request['path'] ?? ''));
        if (is_wp_error($relative)) return $relative;
        $directory = Paths::resolve($library, $relative);
        if (is_wp_error($directory) || !is_dir($directory)) return is_wp_error($directory) ? $directory : new \WP_Error('cdp_not_folder', __('Folder not found.', 'custom-downloads-portal'), array('status' => 404));
        $root = Paths::root($library); $folders = array();
        foreach (new \FilesystemIterator($directory, \FilesystemIterator::SKIP_DOTS) as $entry) {
            $name = $entry->getFilename();
            if ($entry->isLink() || !$entry->isDir() || Paths::is_control_name($name)) continue;
            $folders[] = array('name' => $name, 'path' => Paths::relative($root, $entry->getPathname()), 'hasChildren' => self::has_child_folders($entry->getPathname()));
        }
        usort($folders, static function($a, $b) use ($relative) {
            if ($relative === '') {
                $priority = static function($name) {
                    $trimmed = trim($name);
                    if (in_array($trimmed, array('New Board Member Welcome Packet', 'New MAB Member Welcome Packet'), true)) return 0;
                    if ($trimmed === 'Directories') return 1;
                    if (preg_match('/^\d{4}$/', $trimmed)) return 10000 - (int) $trimmed;
                    return 20000;
                };
                $pa = $priority($a['name']); $pb = $priority($b['name']);
                if ($pa !== $pb) return $pa <=> $pb;
            }
            return strnatcasecmp($a['name'], $b['name']);
        });
        return rest_ensure_response(array('folders' => $folders));
    }

    private static function has_child_folders(string $path): bool {
        foreach (new \FilesystemIterator($path, \FilesystemIterator::SKIP_DOTS) as $entry) {
            if (!$entry->isLink() && $entry->isDir() && !Paths::is_control_name($entry->getFilename())) return true;
        }
        return false;
    }

    public static function create_folder(\WP_REST_Request $request) {
        $ready = self::require_ready(); if (is_wp_error($ready)) return $ready;
        $library = sanitize_key((string) $request['library']);
        $parent = Paths::resolve($library, (string) ($request['path'] ?? ''));
        if (is_wp_error($parent) || !is_dir($parent)) return is_wp_error($parent) ? $parent : new \WP_Error('cdp_bad_parent', __('Destination folder not found.', 'custom-downloads-portal'), array('status' => 404));
        $name = Paths::validate_new_name($request['name'] ?? '', false);
        if (is_wp_error($name)) return $name;
        $destination = $parent . DIRECTORY_SEPARATOR . $name;
        $lock = self::lock($library); if (is_wp_error($lock)) return $lock;
        if (file_exists($destination)) { self::unlock($lock); return new \WP_Error('cdp_exists', __('A file or folder with this name already exists.', 'custom-downloads-portal'), array('status' => 409)); }
        if (!@mkdir($destination, 0750)) {
            self::unlock($lock);
            Audit::record('create_folder', $library, (string) ($request['path'] ?? '') . '/' . $name, 'failed');
            return new \WP_Error('cdp_create_failed', __('The folder could not be created.', 'custom-downloads-portal'), array('status' => 500));
        }
        self::unlock($lock);
        Audit::record('create_folder', $library, Paths::relative(Paths::root($library), $destination), 'success');
        return rest_ensure_response(array('message' => __('Folder created.', 'custom-downloads-portal')));
    }

    public static function upload(\WP_REST_Request $request) {
        $ready = self::require_ready(); if (is_wp_error($ready)) return $ready;
        $library = sanitize_key((string) $request['library']);
        $parent_rel = (string) ($request['path'] ?? '');
        $parent = Paths::resolve($library, $parent_rel);
        if (is_wp_error($parent) || !is_dir($parent)) return is_wp_error($parent) ? $parent : new \WP_Error('cdp_bad_parent', __('Destination folder not found.', 'custom-downloads-portal'), array('status' => 404));
        $files = $request->get_file_params(); $file = $files['file'] ?? null;
        if (!$file || !isset($file['tmp_name'], $file['name'], $file['size']) || !is_uploaded_file($file['tmp_name'])) return new \WP_Error('cdp_missing_upload', __('No valid uploaded file was received.', 'custom-downloads-portal'), array('status' => 400));
        $name = Paths::validate_new_name(wp_basename((string) $file['name']), true); if (is_wp_error($name)) return $name;
        $size = (int) $file['size'];
        if ($size < 1 || $size > self::MAX_UPLOAD) return new \WP_Error('cdp_file_size', __('Files must be no larger than 50 MB.', 'custom-downloads-portal'), array('status' => 413));
        $environment = self::environment_upload_limit();
        if ($environment > 0 && $size > $environment) return new \WP_Error('cdp_server_limit', sprintf(__('The server currently accepts uploads only up to %s.', 'custom-downloads-portal'), size_format($environment)), array('status' => 413));
        $valid = self::validate_file_content($file['tmp_name'], $name); if (is_wp_error($valid)) return $valid;
        $destination = $parent . DIRECTORY_SEPARATOR . $name;
        if (is_dir($destination)) return new \WP_Error('cdp_collision', __('A folder already uses this name.', 'custom-downloads-portal'), array('status' => 409));
        $overwrite = filter_var($request['overwrite'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $expected_revision = '';
        if (is_file($destination)) {
            $revision = self::revision($destination); $expected_revision = $revision;
            if (!$overwrite) return new \WP_Error('cdp_upload_conflict', __('A file with this name already exists. Would you like to overwrite it?', 'custom-downloads-portal'), array('status' => 409, 'name' => $name, 'folder' => $parent_rel, 'revision' => $revision));
            if (!hash_equals($revision, (string) ($request['revision'] ?? ''))) return new \WP_Error('cdp_stale_overwrite', __('The existing file changed. Review the conflict and confirm again.', 'custom-downloads-portal'), array('status' => 409, 'name' => $name, 'folder' => $parent_rel, 'revision' => $revision));
        }
        $stage_dir = self::stage_directory(); if (is_wp_error($stage_dir)) return $stage_dir;
        $stage = $stage_dir . '/' . wp_generate_uuid4() . '.stage';
        if (!move_uploaded_file($file['tmp_name'], $stage) || filesize($stage) !== $size) { @unlink($stage); return new \WP_Error('cdp_stage_failed', __('The upload could not be staged safely.', 'custom-downloads-portal'), array('status' => 500)); }
        $lock = self::lock($library); if (is_wp_error($lock)) { @unlink($stage); return $lock; }
        if ($overwrite && (!is_file($destination) || !hash_equals($expected_revision, self::revision($destination)))) {
            self::unlock($lock); @unlink($stage);
            return new \WP_Error('cdp_stale_overwrite', __('The existing file changed before replacement. Review the conflict and confirm again.', 'custom-downloads-portal'), array('status' => 409));
        }
        if (!$overwrite && file_exists($destination)) {
            self::unlock($lock); @unlink($stage);
            return new \WP_Error('cdp_upload_conflict', __('A file with this name now exists. Upload again to review the conflict.', 'custom-downloads-portal'), array('status' => 409));
        }
        $ok = @rename($stage, $destination);
        self::unlock($lock);
        @unlink($stage);
        if (!$ok) { Audit::record('upload', $library, $parent_rel . '/' . $name, 'failed'); return new \WP_Error('cdp_store_failed', __('The validated file could not be stored.', 'custom-downloads-portal'), array('status' => 500)); }
        @chmod($destination, 0640);
        Audit::record($overwrite ? 'overwrite' : 'upload', $library, Paths::relative(Paths::root($library), $destination), 'success');
        return rest_ensure_response(array('message' => $overwrite ? __('File replaced.', 'custom-downloads-portal') : __('File uploaded.', 'custom-downloads-portal')));
    }

    public static function delete(\WP_REST_Request $request) {
        $ready = self::require_ready(); if (is_wp_error($ready)) return $ready;
        $library = sanitize_key((string) $request['library']);
        $items = $request->get_param('items');
        if (!is_array($items) || !$items || count($items) > 100) return new \WP_Error('cdp_bad_selection', __('Select between 1 and 100 items.', 'custom-downloads-portal'), array('status' => 400));
        $root = Paths::root($library); if (is_wp_error($root)) return $root;
        $targets = array();
        foreach (array_unique(array_map('strval', $items)) as $relative) {
            $normalized = Paths::normalize_relative($relative); if (is_wp_error($normalized) || $normalized === '') return new \WP_Error('cdp_root_delete', __('Library roots cannot be deleted.', 'custom-downloads-portal'), array('status' => 400));
            $path = Paths::resolve($library, $normalized); if (is_wp_error($path)) return $path;
            if (Paths::is_control_name(basename($path))) return new \WP_Error('cdp_control_file', __('Protected control items cannot be deleted.', 'custom-downloads-portal'), array('status' => 400));
            $targets[$normalized] = $path;
        }
        foreach (array_keys($targets) as $a) foreach (array_keys($targets) as $b) if ($a !== $b && str_starts_with($b, $a . '/')) unset($targets[$b]);
        $count = 0;
        foreach ($targets as $path) { $scan = self::count_delete_nodes($path, $count); if (is_wp_error($scan)) return $scan; }
        $lock = self::lock($library); if (is_wp_error($lock)) return $lock;
        $failed = array();
        foreach ($targets as $relative => $path) if (!self::remove_tree($root, $path)) $failed[] = $relative;
        self::unlock($lock);
        Audit::record('delete', $library, implode(', ', array_keys($targets)), $failed ? 'partial' : 'success', $failed ? implode(', ', $failed) : '');
        if ($failed) return new \WP_Error('cdp_partial_delete', __('Some selected items could not be removed. The current listing will be reloaded.', 'custom-downloads-portal'), array('status' => 500, 'failed' => $failed));
        return rest_ensure_response(array('message' => __('Selected items were permanently deleted.', 'custom-downloads-portal')));
    }

    private static function count_delete_nodes(string $path, int &$count) {
        if (++$count > 2000) return new \WP_Error('cdp_delete_too_large', __('This selection is too large for one safe deletion request. Delete smaller folders separately.', 'custom-downloads-portal'), array('status' => 413));
        if (is_link($path) || Paths::is_control_name(basename($path))) return new \WP_Error('cdp_unsafe_delete', __('Deletion stopped because a protected or linked item was found.', 'custom-downloads-portal'), array('status' => 400));
        if (is_dir($path)) foreach (new \FilesystemIterator($path, \FilesystemIterator::SKIP_DOTS) as $entry) { $result = self::count_delete_nodes($entry->getPathname(), $count); if (is_wp_error($result)) return $result; }
        return true;
    }

    private static function remove_tree(string $root, string $path): bool {
        $real = realpath($path); if (!$real || !Paths::inside($root, $real) || $real === $root || is_link($path)) return false;
        if (is_dir($path)) { foreach (new \FilesystemIterator($path, \FilesystemIterator::SKIP_DOTS) as $entry) if (!self::remove_tree($root, $entry->getPathname())) return false; return @rmdir($path); }
        return is_file($path) && @unlink($path);
    }

    private static function revision(string $path): string { return hash('sha256', filesize($path) . ':' . filemtime($path) . ':' . hash_file('sha256', $path)); }
    private static function stage_directory() {
        $candidates = array(dirname(rtrim(ABSPATH, '/\\')) . '/.custom-downloads-portal-staging', rtrim(sys_get_temp_dir(), '/\\') . '/custom-downloads-portal-staging');
        foreach ($candidates as $dir) {
            if (str_starts_with($dir, rtrim(ABSPATH, '/\\') . DIRECTORY_SEPARATOR)) continue;
            if ((is_dir($dir) || @mkdir($dir, 0700, true)) && is_writable($dir)) {
                @chmod($dir, 0700);
                return $dir;
            }
        }
        return new \WP_Error('cdp_stage_unavailable', __('Private upload staging outside the web root is unavailable.', 'custom-downloads-portal'), array('status' => 500));
    }
    private static function lock(string $library) { $dir = self::stage_directory(); if (is_wp_error($dir)) return $dir; $handle = fopen($dir . '/.' . $library . '.lock', 'c'); if (!$handle || !flock($handle, LOCK_EX)) return new \WP_Error('cdp_busy', __('The library is busy. Try again.', 'custom-downloads-portal'), array('status' => 409)); return $handle; }
    private static function unlock($handle): void { flock($handle, LOCK_UN); fclose($handle); }

    private static function validate_file_content(string $path, string $name) {
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION)); $head = (string) file_get_contents($path, false, null, 0, 16);
        $mime = class_exists('finfo') ? (new \finfo(FILEINFO_MIME_TYPE))->file($path) : '';
        $allowed_mimes = array(
            'pdf'=>array('application/pdf'), 'jpg'=>array('image/jpeg'), 'jpeg'=>array('image/jpeg'),
            'zip'=>array('application/zip','application/x-zip-compressed'),
            'doc'=>array('application/msword','application/x-ole-storage','application/vnd.ms-office','application/octet-stream'),
            'xls'=>array('application/vnd.ms-excel','application/x-ole-storage','application/vnd.ms-office','application/octet-stream'),
            'ppt'=>array('application/vnd.ms-powerpoint','application/x-ole-storage','application/vnd.ms-office','application/octet-stream'),
            'docx'=>array('application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/zip'),
            'xlsx'=>array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/zip'),
            'pptx'=>array('application/vnd.openxmlformats-officedocument.presentationml.presentation','application/zip'),
        );
        if ($mime === '' || !in_array($mime, $allowed_mimes[$extension] ?? array(), true)) return self::invalid_content();
        if ($extension === 'pdf' && !str_starts_with($head, '%PDF-')) return self::invalid_content();
        if (in_array($extension, array('jpg', 'jpeg'), true) && @getimagesize($path)[2] !== IMAGETYPE_JPEG) return self::invalid_content();
        if (in_array($extension, array('doc', 'xls', 'ppt'), true) && !str_starts_with($head, "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1")) return self::invalid_content();
        if (in_array($extension, array('docx', 'xlsx', 'pptx'), true)) {
            if (!class_exists('ZipArchive')) return new \WP_Error('cdp_zip_support', __('The server ZIP extension is required to validate Office documents.', 'custom-downloads-portal'), array('status' => 503));
            $zip = new \ZipArchive(); if ($zip->open($path) !== true || $zip->numFiles > 10000 || $zip->locateName('[Content_Types].xml') === false) { if ($zip instanceof \ZipArchive) $zip->close(); return self::invalid_content(); }
            $marker = array('docx' => 'word/document.xml', 'xlsx' => 'xl/workbook.xml', 'pptx' => 'ppt/presentation.xml')[$extension]; $valid = $zip->locateName($marker) !== false; $zip->close(); if (!$valid) return self::invalid_content();
        }
        if ($extension === 'zip' && !str_starts_with($head, "PK\x03\x04") && !str_starts_with($head, "PK\x05\x06")) return self::invalid_content();
        return true;
    }
    private static function invalid_content(): \WP_Error { return new \WP_Error('cdp_invalid_content', __('The file contents do not match the permitted file type.', 'custom-downloads-portal'), array('status' => 415)); }
    private static function environment_upload_limit(): int { return min(self::parse_bytes(ini_get('upload_max_filesize')), self::parse_bytes(ini_get('post_max_size')) - 1048576, self::MAX_UPLOAD); }
    private static function parse_bytes($value): int { $value = trim((string) $value); if ($value === '') return PHP_INT_MAX; $number = (float) $value; $unit = strtolower(substr($value, -1)); if ($unit === 'g') $number *= 1024; if (in_array($unit, array('g', 'm'), true)) $number *= 1024; if (in_array($unit, array('g', 'm', 'k'), true)) $number *= 1024; return (int) $number; }
}
