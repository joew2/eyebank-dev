<?php
namespace CW\CustomPopUpApp;
defined( 'ABSPATH' ) || exit;

function cw_popup_register() {
	$caps = array_fill_keys( array( 'edit_post', 'read_post', 'delete_post', 'edit_posts', 'edit_others_posts', 'publish_posts', 'read_private_posts', 'delete_posts', 'delete_private_posts', 'delete_published_posts', 'delete_others_posts', 'edit_private_posts', 'edit_published_posts', 'create_posts', 'read' ), 'manage_options' );
	// Meta capabilities must remain distinct from primitive manage_options.
	// WordPress maps these through the post ID to the primitive capabilities below.
	$caps['edit_post']   = 'cw_popup_edit_post';
	$caps['read_post']   = 'cw_popup_read_post';
	$caps['delete_post'] = 'cw_popup_delete_post';
	register_post_type(
		'cw_popup',
		array(
			'labels'              => array(
				'name'          => __( 'All Popups', 'custom-popup-app-cw' ),
				'all_items'     => __( 'All Popups', 'custom-popup-app-cw' ),
				'singular_name' => __( 'Popup', 'custom-popup-app-cw' ),
				'menu_name'     => 'Popup',
				'add_new'       => __( 'Add New', 'custom-popup-app-cw' ),
				'add_new_item'  => __( 'Add Popup', 'custom-popup-app-cw' ),
				'edit_item'     => __( 'Edit Popup', 'custom-popup-app-cw' ),
				'search_items'  => __( 'Search Popups', 'custom-popup-app-cw' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => false,
			'show_in_nav_menus'   => false,
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
			'can_export'          => true,
			'supports'            => array( 'title' ),
			'capabilities'        => $caps,
			'map_meta_cap'        => true,
			'menu_icon'           => 'dashicons-format-chat',
			'menu_position'       => 99,
		)
	);
	register_post_meta(
		'cw_popup',
		'cw_popup_settings',
		array(
			'type'              => 'object',
			'single'            => true,
			'show_in_rest'      => false,
			'sanitize_callback' => __NAMESPACE__ . '\\cw_popup_sanitize_meta',
			'auth_callback'     => static function () {
				return current_user_can( 'manage_options' ); },
		)
	);
}
function cw_popup_defaults() {
	return array(
		'image'       => 0,
		'description' => '',
		'url'         => '',
		'new_tab'     => false,
		'target'      => 'home',
		'pages'       => array(),
		'delay'       => 1,
		'width'       => 650,
		'frequency'   => 'session',
		'priority'    => 10,
	);
}
function cw_popup_settings( $id ) {
	$value = get_post_meta( $id, 'cw_popup_settings', true );
	return array_merge( cw_popup_defaults(), is_array( $value ) ? $value : array() );
}
function cw_popup_scalar( $value ) {
	return is_scalar( $value ) ? (string) $value : ''; }
function cw_popup_public_page( $id ) {
	$page = get_post( $id );
	return $page && 'page' === $page->post_type && 'publish' === $page->post_status && '' === $page->post_password && is_post_publicly_viewable( $page );
}
function cw_popup_image( $id ) {
	if ( ! $id || 'attachment' !== get_post_type( $id ) || ! wp_attachment_is_image( $id ) ) {
		return false;
	}
	$mime = get_post_mime_type( $id );
	if ( ! in_array( $mime, array_values( get_allowed_mime_types() ), true ) || in_array( $mime, array( 'image/svg+xml', 'image/svg' ), true ) ) {
		return false;
	}
	$meta = wp_get_attachment_metadata( $id );
	$file = get_attached_file( $id );
	if ( ! is_array( $meta ) || (int) ( $meta['width'] ?? 0 ) < 1 || (int) ( $meta['height'] ?? 0 ) < 1 || ! $file || ! is_file( $file ) ) {
		return false;
	}
	return array(
		'width'  => (int) $meta['width'],
		'height' => (int) $meta['height'],
	);
}
/** Return sanitized form values and errors, without mutating a saved popup. */
function cw_popup_validate( $input, $complete = false, $preview = false ) {
	$raw            = is_array( $input ) ? $input : array();
	$data           = cw_popup_defaults();
	$errors         = array();
	$data['name']   = sanitize_text_field( cw_popup_scalar( $raw['name'] ?? '' ) );
	$data['status'] = cw_popup_scalar( $raw['status'] ?? 'draft' );
	if ( ! in_array( $data['status'], array( 'draft', 'publish' ), true ) ) {
		$errors[] = 'Choose Draft or Published. Scheduling is not supported.';
	}
	foreach ( array(
		'image'    => array( 0, PHP_INT_MAX ),
		'delay'    => array( 0, 60 ),
		'priority' => array( 0, 100 ),
	) as $field => $range ) {
		$v = cw_popup_scalar( $raw[ $field ] ?? $data[ $field ] );
		if ( ! preg_match( '/^\d+$/', $v ) || (float) $v < $range[0] || (float) $v > $range[1] ) {
			$errors[] = sprintf( '%s must be a whole number between %s and %s.', ucfirst( $field ), $range[0], $range[1] );
		}
		$data[ $field ] = preg_match( '/^\d+$/', $v ) && (float) $v >= $range[0] && (float) $v <= $range[1] ? (int) $v : $v;
	}
	$data['width'] = (int) cw_popup_scalar( $raw['width'] ?? 650 );
	if ( ! in_array( cw_popup_scalar( $raw['width'] ?? 650 ), array( '600', '650', '700', '800' ), true ) ) {
		$errors[] = 'Choose a valid popup width.';
	}
	$data['description'] = sanitize_textarea_field( cw_popup_scalar( $raw['description'] ?? '' ) );
	$url                 = trim( cw_popup_scalar( $raw['url'] ?? '' ) );
	$valid_url           = ! $url || ( filter_var( $url, FILTER_VALIDATE_URL ) && in_array( strtolower( (string) wp_parse_url( $url, PHP_URL_SCHEME ) ), array( 'http', 'https' ), true ) );
	if ( ! $valid_url ) {
		$errors[] = 'Link URL must be a complete, valid HTTP or HTTPS URL.';
	}
	$data['url'] = $valid_url ? esc_url_raw( $url, array( 'http', 'https' ) ) : $url;
	$new_tab     = $raw['new_tab'] ?? false;
	if ( ! in_array( $new_tab, array( false, true, 0, 1, '0', '1', '' ), true ) ) {
		$errors[] = 'Invalid new-tab setting.';
	}
	$data['new_tab'] = in_array( $new_tab, array( true, 1, '1' ), true );
	foreach ( array(
		'target'    => array( 'home', 'pages' ),
		'frequency' => array( 'session', 'always', 'daily' ),
	) as $key => $allowed ) {
		$data[ $key ] = cw_popup_scalar( $raw[ $key ] ?? $data[ $key ] );
		if ( ! in_array( $data[ $key ], $allowed, true ) ) {
			$errors[] = 'Choose a valid ' . $key . ' setting.';
		}
	}
	$pages = $raw['pages'] ?? array();
	if ( ! is_array( $pages ) ) {
		$errors[] = 'Invalid page selection.';
		$pages    = array(); }
	foreach ( $pages as $page ) {
		if ( ! is_scalar( $page ) || ! preg_match( '/^[1-9]\d*$/', (string) $page ) || (float) $page > PHP_INT_MAX ) {
			$errors[] = 'Page IDs must be positive integers.';
			continue; }
		$data['pages'][] = (int) $page;
	}
	$data['pages'] = array_values( array_unique( $data['pages'] ) );
	if ( $complete ) {
		if ( ! $preview && '' === $data['name'] ) {
			$errors[] = 'Enter a popup name before publishing.';
		}
		if ( ! cw_popup_image( $data['image'] ) ) {
			$errors[] = 'Choose a valid Media Library raster image with usable dimensions.';
		}
		if ( '' === $data['description'] ) {
			$errors[] = 'Enter an accessible description of the graphic and its link purpose.';
		}
		if ( ! $preview && 'pages' === $data['target'] ) {
			if ( ! $data['pages'] ) {
				$errors[] = 'Select at least one published, public page.';
			}
			foreach ( $data['pages'] as $page ) {
				if ( ! cw_popup_public_page( $page ) ) {
					$errors[] = sprintf( 'Page #%d is unavailable. Remove it or restore its public published status.', $page );
				}
			}
		}
	}
	return array( $data, array_values( array_unique( $errors ) ) );
}
function cw_popup_sanitize_meta( $value ) {
	list( $data, $errors ) = cw_popup_validate( $value );
	unset( $data['name'], $data['status'] );
	// Invalid direct metadata updates become ineligible rather than active malformed data.
	if ( $errors ) {
		$data['invalid'] = true;
	}
	return $data;
}
function cw_popup_valid_published( $post ) {
	$data = cw_popup_settings( $post->ID );
	if ( ! empty( $data['invalid'] ) ) {
		return false;
	}
	list( $clean, $errors ) = cw_popup_validate(
		array_merge(
			$data,
			array(
				'name'   => $post->post_title,
				'status' => 'publish',
			)
		),
		true
	);
	return ! $errors;
}
function cw_popup_guard_status( $data, $raw ) {
	if ( 'cw_popup' !== ( $data['post_type'] ?? '' ) ) {
		return $data;
	}
	if ( ! in_array( $data['post_status'], array( 'draft', 'publish', 'trash', 'auto-draft' ), true ) ) {
		$data['post_status'] = 'draft';
	}
	if ( 'publish' === $data['post_status'] ) {
		$id                     = absint( $raw['ID'] ?? 0 );
		list( $clean, $errors ) = cw_popup_validate(
			array_merge(
				cw_popup_settings( $id ),
				array(
					'name'   => wp_unslash( $data['post_title'] ),
					'status' => 'publish',
				)
			),
			true
		);
		if ( $errors || ! empty( cw_popup_settings( $id )['invalid'] ) ) {
			$data['post_status'] = 'draft';
		}
	}
	return $data;
}
