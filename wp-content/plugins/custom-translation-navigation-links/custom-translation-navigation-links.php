<?php
/**
 * Plugin Name: Custom Translation Navigation Links Plugin
 * Plugin URI:  https://www.eyedonation.org/
 * Description: Manage English-to-Spanish URL mappings and render translation links with shortcodes.
 * Version:     1.0.0
 * Author:      Creative Way Solutions
 * License:     GPL-2.0-or-later
 * Text Domain: custom-translation-navigation-links
 *
 * @package Custom_Translation_Navigation_Links
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CTNL_VERSION', '1.0.0' );
define( 'CTNL_SPANISH_LINK_LABEL', 'Español' );
define( 'CTNL_ENGLISH_LINK_LABEL', 'English' );

register_activation_hook( __FILE__, 'ctnl_activate_plugin' );

add_action( 'admin_menu', 'ctnl_register_admin_menu' );
add_action( 'admin_init', 'ctnl_handle_admin_actions' );
add_shortcode( 'spanish_page_link', 'ctnl_render_spanish_page_link' );
add_shortcode( 'english_page_link', 'ctnl_render_english_page_link' );

/**
 * Create or update the translation mapping table.
 */
function ctnl_activate_plugin() {
	global $wpdb;

	$table_name      = ctnl_get_table_name();
	$charset_collate = $wpdb->get_charset_collate();

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$sql = "CREATE TABLE {$table_name} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		english_path varchar(255) NOT NULL,
		spanish_path varchar(255) NOT NULL,
		enabled tinyint(1) NOT NULL DEFAULT 1,
		created_at datetime NOT NULL,
		updated_at datetime NOT NULL,
		PRIMARY KEY  (id),
		KEY english_path (english_path(191)),
		KEY spanish_path (spanish_path(191))
	) {$charset_collate};";

	dbDelta( $sql );

	update_option( 'ctnl_version', CTNL_VERSION );
}

/**
 * Get the plugin table name.
 *
 * @return string
 */
function ctnl_get_table_name() {
	global $wpdb;

	return $wpdb->prefix . 'ctnl_translation_mappings';
}

/**
 * Register the Translation admin page.
 */
function ctnl_register_admin_menu() {
	add_menu_page(
		__( 'Custom Translation Navigation Links', 'custom-translation-navigation-links' ),
		__( 'Translation', 'custom-translation-navigation-links' ),
		'manage_options',
		'ctnl-translation-links',
		'ctnl_render_admin_page',
		'dashicons-translation',
		80
	);
}

/**
 * Handle admin save and delete actions.
 */
function ctnl_handle_admin_actions() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['ctnl_save_mappings'] ) ) {
		ctnl_save_mappings();
	}

	if (
		isset( $_GET['page'], $_GET['action'], $_GET['mapping_id'] ) &&
		'ctnl-translation-links' === $_GET['page'] &&
		'delete' === $_GET['action']
	) {
		ctnl_delete_mapping();
	}
}

/**
 * Save all submitted mappings.
 */
function ctnl_save_mappings() {
	global $wpdb;

	check_admin_referer( 'ctnl_save_mappings', 'ctnl_nonce' );

	$submitted = isset( $_POST['ctnl_mappings'] ) && is_array( $_POST['ctnl_mappings'] )
		? wp_unslash( $_POST['ctnl_mappings'] )
		: array();

	$rows          = array();
	$english_seen  = array();
	$spanish_seen  = array();
	$error_messages = array();

	foreach ( $submitted as $index => $mapping ) {
		$id            = isset( $mapping['id'] ) ? absint( $mapping['id'] ) : 0;
		$english_path  = isset( $mapping['english_path'] ) ? ctnl_normalize_path( $mapping['english_path'] ) : '';
		$spanish_path  = isset( $mapping['spanish_path'] ) ? ctnl_normalize_path( $mapping['spanish_path'] ) : '';
		$enabled       = ! empty( $mapping['enabled'] ) ? 1 : 0;

		if ( '' === $english_path && '' === $spanish_path ) {
			continue;
		}

		if ( '' === $english_path || '' === $spanish_path ) {
			$error_messages[] = sprintf(
				/* translators: %d: row number */
				__( 'Row %d was not saved because both English and Spanish paths are required.', 'custom-translation-navigation-links' ),
				absint( $index ) + 1
			);
			continue;
		}

		if ( isset( $english_seen[ $english_path ] ) ) {
			$error_messages[] = sprintf(
				/* translators: %s: URL path */
				__( 'Duplicate English path prevented: %s', 'custom-translation-navigation-links' ),
				esc_html( $english_path )
			);
			continue;
		}

		if ( isset( $spanish_seen[ $spanish_path ] ) ) {
			$error_messages[] = sprintf(
				/* translators: %s: URL path */
				__( 'Duplicate Spanish path prevented: %s', 'custom-translation-navigation-links' ),
				esc_html( $spanish_path )
			);
			continue;
		}

		$english_seen[ $english_path ] = true;
		$spanish_seen[ $spanish_path ] = true;

		$rows[] = array(
			'id'            => $id,
			'english_path'  => $english_path,
			'spanish_path'  => $spanish_path,
			'enabled'       => $enabled,
		);
	}

	if ( ! empty( $error_messages ) ) {
		set_transient( 'ctnl_admin_notices', array( 'error' => $error_messages ), 30 );
		ctnl_redirect_to_admin_page();
	}

	$table_name = ctnl_get_table_name();
	$now        = current_time( 'mysql' );

	foreach ( $rows as $row ) {
		$data = array(
			'english_path'  => $row['english_path'],
			'spanish_path'  => $row['spanish_path'],
			'enabled'       => $row['enabled'],
			'updated_at'    => $now,
		);

		$formats = array( '%s', '%s', '%d', '%s' );

		if ( $row['id'] > 0 ) {
			$wpdb->update(
				$table_name,
				$data,
				array( 'id' => $row['id'] ),
				$formats,
				array( '%d' )
			);
		} else {
			$data['created_at'] = $now;
			$wpdb->insert(
				$table_name,
				$data,
				array_merge( $formats, array( '%s' ) )
			);
		}
	}

	set_transient(
		'ctnl_admin_notices',
		array( 'success' => array( __( 'Translation mappings saved.', 'custom-translation-navigation-links' ) ) ),
		30
	);

	ctnl_redirect_to_admin_page();
}

/**
 * Delete one mapping.
 */
function ctnl_delete_mapping() {
	global $wpdb;

	$mapping_id = absint( $_GET['mapping_id'] );

	check_admin_referer( 'ctnl_delete_mapping_' . $mapping_id );

	if ( $mapping_id > 0 ) {
		$wpdb->delete(
			ctnl_get_table_name(),
			array( 'id' => $mapping_id ),
			array( '%d' )
		);
	}

	set_transient(
		'ctnl_admin_notices',
		array( 'success' => array( __( 'Translation mapping deleted.', 'custom-translation-navigation-links' ) ) ),
		30
	);

	ctnl_redirect_to_admin_page();
}

/**
 * Redirect back to the plugin admin page.
 */
function ctnl_redirect_to_admin_page() {
	wp_safe_redirect( admin_url( 'admin.php?page=ctnl-translation-links' ) );
	exit;
}

/**
 * Render the Translation admin page.
 */
function ctnl_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$mappings = ctnl_get_all_mappings();
	$notices  = get_transient( 'ctnl_admin_notices' );

	delete_transient( 'ctnl_admin_notices' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Custom Translation Navigation Links', 'custom-translation-navigation-links' ); ?></h1>

		<?php if ( ! empty( $notices['success'] ) ) : ?>
			<div class="notice notice-success is-dismissible">
				<?php foreach ( $notices['success'] as $message ) : ?>
					<p><?php echo esc_html( $message ); ?></p>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $notices['error'] ) ) : ?>
			<div class="notice notice-error is-dismissible">
				<?php foreach ( $notices['error'] as $message ) : ?>
					<p><?php echo esc_html( wp_strip_all_tags( $message ) ); ?></p>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<form method="post" action="">
			<?php wp_nonce_field( 'ctnl_save_mappings', 'ctnl_nonce' ); ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'English URL Path', 'custom-translation-navigation-links' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Spanish URL Path', 'custom-translation-navigation-links' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Enabled', 'custom-translation-navigation-links' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Delete', 'custom-translation-navigation-links' ); ?></th>
					</tr>
				</thead>
				<tbody id="ctnl-mapping-rows">
					<?php
					$row_index = 0;
					foreach ( $mappings as $mapping ) :
						ctnl_render_admin_table_row( $row_index, $mapping );
						$row_index++;
					endforeach;

					ctnl_render_admin_table_row(
						$row_index,
						array(
							'id'            => 0,
							'english_path'  => '',
							'spanish_path'  => '',
							'enabled'       => 1,
						)
					);
					?>
				</tbody>
			</table>

			<p class="description">
				<?php esc_html_e( 'Add a new mapping in the blank row. Full URLs are accepted, but paths are stored for reliable matching across domains.', 'custom-translation-navigation-links' ); ?>
			</p>

			<p>
				<button type="button" class="button" id="ctnl-add-row">
					<?php esc_html_e( 'Add New Row', 'custom-translation-navigation-links' ); ?>
				</button>
			</p>

			<?php submit_button( __( 'Save Mappings', 'custom-translation-navigation-links' ), 'primary', 'ctnl_save_mappings' ); ?>
		</form>
	</div>
	<script>
		(function() {
			var addButton = document.getElementById('ctnl-add-row');
			var rows = document.getElementById('ctnl-mapping-rows');
			var nextIndex = <?php echo absint( $row_index + 1 ); ?>;

			if (!addButton || !rows) {
				return;
			}

			addButton.addEventListener('click', function() {
				var sourceRow = rows.querySelector('tr:last-child');
				var newRow;

				if (!sourceRow) {
					return;
				}

				newRow = sourceRow.cloneNode(true);

				newRow.querySelectorAll('input').forEach(function(input) {
					input.name = input.name.replace(/ctnl_mappings\[[^\]]+\]/, 'ctnl_mappings[' + nextIndex + ']');

					if ('checkbox' === input.type) {
						input.checked = true;
					} else if ('hidden' === input.type) {
						input.value = '0';
					} else {
						input.value = '';
					}
				});

				newRow.querySelector('td:last-child').innerHTML = '&mdash;';
				rows.appendChild(newRow);
				nextIndex++;
			});
		}());
	</script>
	<?php
}

/**
 * Render one admin table row.
 *
 * @param int   $index Row index.
 * @param array $mapping Mapping values.
 */
function ctnl_render_admin_table_row( $index, $mapping ) {
	$mapping = wp_parse_args(
		$mapping,
		array(
			'id'           => 0,
			'english_path' => '',
			'spanish_path' => '',
			'enabled'      => 1,
		)
	);
	?>
	<tr>
		<td>
			<input type="hidden" name="ctnl_mappings[<?php echo esc_attr( $index ); ?>][id]" value="<?php echo esc_attr( $mapping['id'] ); ?>">
			<input class="regular-text" type="text" name="ctnl_mappings[<?php echo esc_attr( $index ); ?>][english_path]" value="<?php echo esc_attr( $mapping['english_path'] ); ?>" placeholder="/become-a-donor/">
		</td>
		<td>
			<input class="regular-text" type="text" name="ctnl_mappings[<?php echo esc_attr( $index ); ?>][spanish_path]" value="<?php echo esc_attr( $mapping['spanish_path'] ); ?>" placeholder="/sea-donante/">
		</td>
		<td>
			<label>
				<input type="checkbox" name="ctnl_mappings[<?php echo esc_attr( $index ); ?>][enabled]" value="1" <?php checked( (int) $mapping['enabled'], 1 ); ?>>
				<?php esc_html_e( 'Enabled', 'custom-translation-navigation-links' ); ?>
			</label>
		</td>
		<td>
			<?php if ( ! empty( $mapping['id'] ) ) : ?>
				<a class="submitdelete" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=ctnl-translation-links&action=delete&mapping_id=' . absint( $mapping['id'] ) ), 'ctnl_delete_mapping_' . absint( $mapping['id'] ) ) ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Delete this translation mapping?', 'custom-translation-navigation-links' ) ); ?>');">
					<?php esc_html_e( 'Delete', 'custom-translation-navigation-links' ); ?>
				</a>
			<?php else : ?>
				&mdash;
			<?php endif; ?>
		</td>
	</tr>
	<?php
}

/**
 * Get all mappings for the admin page.
 *
 * @return array
 */
function ctnl_get_all_mappings() {
	global $wpdb;

	$table_name = ctnl_get_table_name();

	return $wpdb->get_results( "SELECT * FROM {$table_name} ORDER BY english_path ASC", ARRAY_A );
}

/**
 * Render an English page to Spanish page link.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function ctnl_render_spanish_page_link( $atts = array() ) {
	return ctnl_render_translation_link( 'spanish', $atts );
}

/**
 * Render a Spanish page to English page link.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function ctnl_render_english_page_link( $atts = array() ) {
	return ctnl_render_translation_link( 'english', $atts );
}

/**
 * Render a translation link for the requested direction.
 *
 * @param string $direction Link direction. Accepts spanish or english.
 * @param array  $atts Shortcode attributes.
 * @return string
 */
function ctnl_render_translation_link( $direction, $atts = array() ) {
	$atts = shortcode_atts(
		array(
			'class'  => '',
			'target' => '',
		),
		(array) $atts,
		'ctnl_translation_link'
	);

	$current_paths = ctnl_get_current_paths();

	if ( empty( $current_paths ) ) {
		return '';
	}

	$mapping = ctnl_get_mapping_for_paths( $current_paths, $direction );

	if ( empty( $mapping ) ) {
		return '';
	}

	if ( 'spanish' === $direction ) {
		$url     = ctnl_get_frontend_url( $mapping['spanish_path'] );
		$label   = CTNL_SPANISH_LINK_LABEL;
		$classes = array( 'ctnl-translation-link', 'ctnl-spanish-link' );
	} else {
		$url     = ctnl_get_frontend_url( $mapping['english_path'] );
		$label   = CTNL_ENGLISH_LINK_LABEL;
		$classes = array( 'ctnl-translation-link', 'ctnl-english-link' );
	}

	if ( '' !== trim( $atts['class'] ) ) {
		$extra_classes = preg_split( '/\s+/', sanitize_text_field( $atts['class'] ) );
		$classes       = array_merge( $classes, array_filter( $extra_classes ) );
	}

	$target = ctnl_sanitize_target_attribute( $atts['target'] );

	$attributes = array(
		'href'  => esc_url( $url ),
		'class' => esc_attr( implode( ' ', array_unique( $classes ) ) ),
	);

	if ( '' !== $target ) {
		$attributes['target'] = esc_attr( $target );
		$attributes['rel']    = '_blank' === $target ? 'noopener noreferrer' : 'noopener';
	}

	$attribute_html = '';

	foreach ( $attributes as $name => $value ) {
		$attribute_html .= sprintf( ' %s="%s"', esc_attr( $name ), $value );
	}

	return sprintf( '<a%s>%s</a>', $attribute_html, esc_html( $label ) );
}

/**
 * Get possible current page paths, ordered from most direct to broadest fallback.
 *
 * @return array
 */
function ctnl_get_current_paths() {
	$paths = array();

	if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
		$paths[] = ctnl_normalize_path( wp_unslash( $_SERVER['REQUEST_URI'] ) );
	}

	if ( is_singular() ) {
		$permalink = get_permalink( get_queried_object_id() );

		if ( $permalink ) {
			$paths[] = ctnl_normalize_path( $permalink );
		}
	}

	$post_id = get_the_ID();

	if ( $post_id ) {
		$permalink = get_permalink( $post_id );

		if ( $permalink ) {
			$paths[] = ctnl_normalize_path( $permalink );
		}
	}

	return array_values( array_unique( array_filter( $paths ) ) );
}

/**
 * Get one enabled mapping for the current paths and direction.
 *
 * @param array  $paths Normalized paths.
 * @param string $direction Link direction.
 * @return array|null
 */
function ctnl_get_mapping_for_paths( $paths, $direction ) {
	global $wpdb;

	$column = 'spanish' === $direction ? 'english_path' : 'spanish_path';

	foreach ( $paths as $path ) {
		$mapping = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM " . ctnl_get_table_name() . " WHERE {$column} = %s AND enabled = 1 LIMIT 1",
				$path
			),
			ARRAY_A
		);

		if ( ! empty( $mapping ) ) {
			return $mapping;
		}
	}

	return null;
}

/**
 * Build a frontend URL for a saved site path.
 *
 * @param string $path Saved normalized path.
 * @return string
 */
function ctnl_get_frontend_url( $path ) {
	return home_url( ctnl_normalize_path( $path ) );
}

/**
 * Normalize a full URL or relative path for storage and comparisons.
 *
 * @param string $url URL or path.
 * @return string
 */
function ctnl_normalize_path( $url ) {
	$url = trim( (string) $url );

	if ( '' === $url ) {
		return '';
	}

	$url = html_entity_decode( $url, ENT_QUOTES, get_bloginfo( 'charset' ) );
	$url = str_replace( '\\', '/', $url );
	$url = preg_replace( '/[\x00-\x1F\x7F]/', '', $url );

	$scheme = wp_parse_url( $url, PHP_URL_SCHEME );

	if ( $scheme && ! in_array( strtolower( $scheme ), array( 'http', 'https' ), true ) ) {
		return '';
	}

	$path = wp_parse_url( $url, PHP_URL_PATH );

	if ( null === $path || false === $path || '' === $path ) {
		$path = preg_split( '/[?#]/', $url );
		$path = isset( $path[0] ) ? $path[0] : '';
	}

	$path = trim( rawurldecode( $path ) );

	if ( '' === $path ) {
		return '';
	}

	if ( '/' !== $path[0] ) {
		$path = '/' . $path;
	}

	$path = preg_replace( '#/+#', '/', $path );
	$path = esc_url_raw( $path );

	if ( '' === $path || '/' === $path ) {
		return '/';
	}

	return trailingslashit( untrailingslashit( $path ) );
}

/**
 * Sanitize the optional target attribute.
 *
 * @param string $target Raw target attribute.
 * @return string
 */
function ctnl_sanitize_target_attribute( $target ) {
	$target = sanitize_key( $target );

	if ( '' === $target ) {
		return '';
	}

	$allowed_targets = array( '_blank', '_self', '_parent', '_top' );

	return in_array( $target, $allowed_targets, true ) ? $target : '';
}
