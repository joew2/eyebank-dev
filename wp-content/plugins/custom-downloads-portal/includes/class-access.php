<?php
namespace SWS\DownloadsPortal;

defined('ABSPATH') || exit;

final class Access {
    /** Exact keys from the existing site. Labels are checked to prevent accidental key reuse. */
    private const ROLE_POLICY = array(
        'administrator'          => array('label' => 'Administrator', 'bod' => 'manage', 'mab' => 'manage'),
        'eb_staff'               => array('label' => 'EB Staff', 'bod' => 'manage', 'mab' => 'manage'),
        'eb_staff_bod_only'      => array('label' => 'EB Staff BOD ONLY', 'bod' => 'manage'),
        'board_of_director'      => array('label' => 'Board of Director', 'bod' => 'read'),
        'medical_advisory_board' => array('label' => 'Medical Advisory Board', 'mab' => 'read'),
    );

    public static function libraries(): array {
        return array(
            'bod' => array('label' => __('Board of Directors', 'custom-downloads-portal'), 'root_label' => 'BOD_files', 'root' => WP_CONTENT_DIR . '/BOD_files'),
            'mab' => array('label' => __('Medical Advisory Board', 'custom-downloads-portal'), 'root_label' => 'MAB_files', 'root' => WP_CONTENT_DIR . '/MAB_files'),
        );
    }

    public static function permissions(?\WP_User $user = null): array {
        $user = $user ?: wp_get_current_user();
        if (!$user || !$user->exists()) {
            return array();
        }
        $roles = wp_roles()->roles;
        $result = array();
        foreach ((array) $user->roles as $role_key) {
            if (!isset(self::ROLE_POLICY[$role_key], $roles[$role_key])) {
                continue;
            }
            $policy = self::ROLE_POLICY[$role_key];
            if (($roles[$role_key]['name'] ?? '') !== $policy['label']) {
                continue;
            }
            foreach (array('bod', 'mab') as $library) {
                $level = $policy[$library] ?? '';
                if ($level === 'manage' || ($level === 'read' && !isset($result[$library]))) {
                    $result[$library] = $level;
                }
            }
        }
        return $result;
    }

    public static function can(string $library, string $operation = 'read'): bool {
        $permissions = self::permissions();
        if (!isset($permissions[$library])) {
            return false;
        }
        return $operation === 'read' || $permissions[$library] === 'manage';
    }

    public static function library(string $id): ?array {
        $libraries = self::libraries();
        return $libraries[$id] ?? null;
    }

    public static function rest_permission(\WP_REST_Request $request, string $operation = 'read') {
        if (!is_user_logged_in()) {
            return new \WP_Error('cdp_signed_out', __('Your session has expired. Please sign in again.', 'custom-downloads-portal'), array('status' => 401));
        }
        $library = sanitize_key((string) $request->get_param('library'));
        if (!self::can($library, $operation)) {
            return new \WP_Error('cdp_forbidden', __('You do not have access to this document library.', 'custom-downloads-portal'), array('status' => 403));
        }
        return true;
    }
}

