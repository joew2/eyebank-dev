<?php
namespace CW\CustomPopUpApp;
defined( 'ABSPATH' ) || exit;
function cw_popup_matches( $settings ) {
	if ( 'home' === $settings['target'] ) {
		return is_front_page();
	}
	return is_page() && in_array( (int) get_queried_object_id(), $settings['pages'], true );
}
function cw_popup_candidates() {
	if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || is_feed() || is_embed() || is_404() || is_preview() || is_customize_preview() ) {
		return array();
	}
	if ( ! is_front_page() && ! is_page() ) {
		return array();
	}
	if ( is_singular() && ! cw_popup_public_page( get_queried_object_id() ) ) {
		return array();
	}
	$posts   = get_posts(
		array(
			'post_type'     => 'cw_popup',
			'post_status'   => 'publish',
			'numberposts'   => -1,
			'orderby'       => array(
				'modified' => 'DESC',
				'ID'       => 'DESC',
			),
			'no_found_rows' => true,
		)
	);
	$matches = array();
	foreach ( $posts as $post ) {
		$settings = cw_popup_settings( $post->ID );
		if ( cw_popup_matches( $settings ) && cw_popup_valid_published( $post ) ) {
			$matches[] = array(
				'post'     => $post,
				'settings' => $settings,
			);
		}
	}
	usort(
		$matches,
		static function ( $a, $b ) {
			$priority = $b['settings']['priority'] <=> $a['settings']['priority'];
			if ( $priority ) {
				return $priority;
			}
			$modified = strcmp( $b['post']->post_modified_gmt, $a['post']->post_modified_gmt );
			return $modified ? $modified : ( $b['post']->ID <=> $a['post']->ID );
		}
	);
	$result = array();
	foreach ( $matches as $match ) {
		$candidate = cw_popup_candidate( $match['settings'], $match['post']->ID );
		if ( $candidate ) {
			$result[] = $candidate;
		}
	}
	return $result;
}
function cw_popup_candidate( $settings, $id = 0 ) {
	$image = cw_popup_image( $settings['image'] );
	if ( ! $image ) {
		return false;
	}
	$source = wp_get_attachment_image_src( $settings['image'], 'large' );
	if ( ! $source ) {
		return false;
	}
	return array(
		'id'           => (int) $id,
		'src'          => $source[0],
		'srcset'       => (string) wp_get_attachment_image_srcset( $settings['image'], 'full' ),
		'width'        => $image['width'],
		'height'       => $image['height'],
		'alt'          => $settings['description'],
		'url'          => $settings['url'],
		'newTab'       => $settings['new_tab'],
		'delay'        => $settings['delay'],
		'displayWidth' => $settings['width'],
		'frequency'    => $settings['frequency'],
	);
}
function cw_popup_assets() {
	wp_enqueue_style( 'cw_popup_frontend', plugins_url( 'assets/frontend.css', CW_POPUP_FILE ), array(), filemtime( dirname( CW_POPUP_FILE ) . '/assets/frontend.css' ) );
	wp_enqueue_script(
		'cw_popup_frontend',
		plugins_url( 'assets/frontend.js', CW_POPUP_FILE ),
		array(),
		filemtime( dirname( CW_POPUP_FILE ) . '/assets/frontend.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}
function cw_popup_frontend() {
	$candidates = cw_popup_candidates();
	if ( ! $candidates ) {
		return;
	}
	cw_popup_assets();
	// Stable per-installation path/blog namespace; URLs remain portable and attachment-based.
	$namespace = 'cw_popup_' . substr( hash( 'sha256', home_url( '/' ) . '|' . get_current_blog_id() ), 0, 16 );
	add_action(
		'wp_footer',
		static function () use ( $candidates, $namespace ) {
			echo '<script type="application/json" id="cw_popup_config">' . wp_json_encode(
				array(
					'site'       => $namespace,
					'candidates' => $candidates,
				),
				JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
			) . '</script>';
		},
		5
	);
}
