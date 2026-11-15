<?php
/** Run locally: wp eval-file wp-content/plugins/custom-popup-app-cw/tests/integration.php */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit; }
use function CW\CustomPopUpApp\cw_popup_defaults;
use function CW\CustomPopUpApp\cw_popup_validate;
use function CW\CustomPopUpApp\cw_popup_menu_order;
use function CW\CustomPopUpApp\cw_popup_matches;
use function CW\CustomPopUpApp\cw_popup_candidates;
$ids            = array();
$count          = 0;
$original_query = $GLOBALS['wp_query'];
$assert         = static function ( $condition, $message ) use ( &$count ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message ); }
	++$count;
};
try {
	$type = get_post_type_object( 'cw_popup' );
	$assert( $type && ! $type->public && ! $type->show_in_rest && ! $type->publicly_queryable && ! $type->show_in_nav_menus, 'CPT privacy' );
	foreach ( array( 'edit_posts', 'create_posts', 'publish_posts', 'delete_posts' ) as $cap ) {
		$assert( 'manage_options' === $type->cap->$cap, 'Administrator capability: ' . $cap );
	}
	$assert( ! get_role( 'editor' )->has_cap( 'manage_options' ), 'Editors cannot manage pop-ups' );
	$assert( array( 'a', 'cw-team', 'edit.php?post_type=cw_popup', 'b' ) === cw_popup_menu_order( array( 'a', 'edit.php?post_type=cw_popup', 'cw-team', 'b' ) ), 'Menu follows Team, preserves order' );
	$assert( array( 'a', 'b', 'edit.php?post_type=cw_popup' ) === cw_popup_menu_order( array( 'a', 'edit.php?post_type=cw_popup', 'b' ) ), 'Absent Team fallback' );
	$admin = get_users(
		array(
			'role'   => 'administrator',
			'number' => 1,
		)
	)[0];
	$assert( user_can( $admin, 'manage_options' ), 'Administrator primitive capability remains intact' );
	$base = cw_popup_defaults();
	foreach ( array( 600, 650, 700, 800 ) as $width ) {
		list( $clean, $errors ) = cw_popup_validate( array( 'width' => $width ) );
		$assert( ! $errors && $width === $clean['width'], 'Allowed display width' );
	}
	$assert( 650 === cw_popup_defaults()['width'], 'Existing pop-ups default to 650px' );
	$assert( (bool) cw_popup_validate( array( 'width' => 651 ) )[1], 'Unsupported width rejected' );

	$assert( 'home' === $base['target'] && 1 === $base['delay'] && 'session' === $base['frequency'] && 10 === $base['priority'], 'Defaults' );
	$assert( ! cw_popup_validate( $base )[1], 'Incomplete drafts allowed' );
	$assert( count( cw_popup_validate( $base, true )[1] ) >= 3, 'Incomplete publishing rejected' );
	foreach ( array(
		'delay'     => 61,
		'priority'  => -1,
		'image'     => array( 1 ),
		'frequency' => 'invalid',
		'target'    => 'all',
		'status'    => 'future',
		'url'       => 'javascript:alert(1)',
		'pages'     => array( -1 ),
		'new_tab'   => 'yes',
	) as $key => $value ) {
		$assert( (bool) cw_popup_validate( array_merge( $base, array( $key => $value ) ) )[1], 'Reject malformed ' . $key );
	}
	$assert( array( 12, 13 ) === cw_popup_validate( array_merge( $base, array( 'pages' => array( 12, '12', 13 ) ) ) )[0]['pages'], 'Deduplicate page IDs' );
	$assert( '61' === cw_popup_validate( array_merge( $base, array( 'delay' => '61' ) ) )[0]['delay'], 'Invalid entered value preserved' );
	$assert( ! cw_popup_validate( array_merge( $base, array( 'url' => 'https://example.com/path?a=1&b=2' ) ) )[1], 'HTTP URL accepted' );
	$page                     = wp_insert_post(
		array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_title'  => 'CW Popup integration fixture',
		)
	);
	$ids[]                    = $page;
	$child                    = wp_insert_post(
		array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_title'  => 'CW Popup child fixture',
			'post_parent' => $page,
		)
	);
	$ids[]                    = $child;
	$query                    = new WP_Query();
	$GLOBALS['wp_query']      = $query;
	$query->is_page           = true;
	$query->is_singular       = true;
	$query->queried_object_id = $page;
	$query->queried_object    = get_post( $page );
	$settings                 = array_merge(
		$base,
		array(
			'target' => 'pages',
			'pages'  => array( $page ),
		)
	);
	$assert( cw_popup_matches( $settings ), 'Exact selected page' );
	$query->queried_object_id = $child;
	$query->queried_object    = get_post( $child );
	$assert( ! cw_popup_matches( $settings ), 'Children excluded' );
	$query->is_page     = false;
	$query->is_singular = false;
	$query->is_home     = true;
	$latest             = static function () {
		return 'posts';
	};
	add_filter( 'pre_option_show_on_front', $latest );
	$assert( cw_popup_matches( $base ), 'Latest-posts front page' );
	remove_filter( 'pre_option_show_on_front', $latest );
	$static = static function () {
		return 'page';
	};
	$front  = static function () use ( $page ) {
		return $page;
	};
	add_filter( 'pre_option_show_on_front', $static );
	add_filter( 'pre_option_page_on_front', $front );
	$assert( ! cw_popup_matches( $base ), 'Separate posts index excluded' );
	$query->is_home           = false;
	$query->is_page           = true;
	$query->is_singular       = true;
	$query->queried_object_id = $page;
	$query->queried_object    = get_post( $page );
	$assert( cw_popup_matches( $base ), 'Static front page' );
	$query->is_404 = true;
	$assert( array() === cw_popup_candidates(), '404 suppressed' );
	$query->is_404     = false;
	$query->is_preview = true;
	$assert( array() === cw_popup_candidates(), 'Preview suppressed' );
	$query->is_preview = false;
	$query->is_feed    = true;
	$assert( array() === cw_popup_candidates(), 'Feed suppressed' );
	$query->is_feed = false;
	wp_update_post(
		array(
			'ID'            => $page,
			'post_password' => 'secret',
		)
	);
	$assert( ! CW\CustomPopUpApp\cw_popup_public_page( $page ), 'Password page excluded' );
	wp_update_post(
		array(
			'ID'            => $page,
			'post_password' => '',
			'post_status'   => 'private',
		)
	);
	$assert( ! CW\CustomPopUpApp\cw_popup_public_page( $page ), 'Private page excluded' );
	$popup = wp_insert_post(
		array(
			'post_type'   => 'cw_popup',
			'post_status' => 'publish',
			'post_title'  => 'CW Popup invalid fixture',
		)
	);
	$ids[] = $popup;
	$assert( 'draft' === get_post_status( $popup ), 'Direct incomplete publishing forced to draft' );
	$assert( user_can( $admin, 'edit_post', $popup ) && user_can( $admin, 'delete_post', $popup ), 'Administrator mapped per-post capabilities' );
	wp_update_post(
		array(
			'ID'          => $popup,
			'post_status' => 'future',
		)
	);
	$assert( 'draft' === get_post_status( $popup ), 'Scheduling rejected' );
	update_post_meta( $popup, 'cw_popup_settings', array_merge( $base, array( 'delay' => 999 ) ) );
	$assert( ! CW\CustomPopUpApp\cw_popup_valid_published( get_post( $popup ) ), 'Invalid direct metadata ineligible' );
	WP_CLI::success( $count . ' integration assertions passed.' );
} finally {
	if ( isset( $latest ) ) {
		remove_filter( 'pre_option_show_on_front', $latest );
	}
	if ( isset( $static ) ) {
		remove_filter( 'pre_option_show_on_front', $static );
	}
	if ( isset( $front ) ) {
		remove_filter( 'pre_option_page_on_front', $front );
	}
	$GLOBALS['wp_query'] = $original_query;
	foreach ( $ids as $id ) {
		wp_delete_post( $id, true );
	}
}
