<?php
namespace CW\CustomPopUpApp;
defined( 'ABSPATH' ) || exit;
function cw_popup_editor_url( $id = 0 ) {
	return add_query_arg(
		array(
			'post_type' => 'cw_popup',
			'page'      => 'cw_popup_edit',
			'popup_id'  => absint( $id ),
		),
		admin_url( 'edit.php' )
	); }
function cw_popup_menu() {
	add_submenu_page( 'edit.php?post_type=cw_popup', 'Add Popup', 'Add New', 'manage_options', 'cw_popup_edit', __NAMESPACE__ . '\\cw_popup_editor' );
	// Replace the generated Add New link, preserving native All Popups/status/trash screens.
	remove_submenu_page( 'edit.php?post_type=cw_popup', 'post-new.php?post_type=cw_popup' );
}
function cw_popup_menu_order( $order ) {
	if ( ! is_array( $order ) ) {
		return $order;
	}
	$slug = 'edit.php?post_type=cw_popup';
	if ( ! in_array( $slug, $order, true ) ) {
		return $order;
	}
	$order = array_values( array_diff( $order, array( $slug ) ) );
	// Inspected Custom Team App CW: its actual top-level menu slug is cw-team.
	$index = array_search( 'cw-team', $order, true );
	array_splice( $order, false === $index ? count( $order ) : $index + 1, 0, array( $slug ) );
	return $order;
}
function cw_popup_edit_link( $link, $id, $context ) {
	return 'cw_popup' === get_post_type( $id ) ? cw_popup_editor_url( $id ) : $link; }
function cw_popup_redirect_editor() {
	$id   = absint( $_GET['post'] ?? 0 );
	$type = $id ? get_post_type( $id ) : sanitize_key( $_GET['post_type'] ?? '' );
	if ( 'cw_popup' !== $type || ! in_array( $_GET['action'] ?? 'edit', array( 'edit', '' ), true ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'You cannot manage popups.', '', array( 'response' => 403 ) );
	}
	wp_safe_redirect( cw_popup_editor_url( $id ) );
	exit;
}
function cw_popup_row_actions( $actions, $post ) {
	if ( 'cw_popup' === $post->post_type ) {
		unset( $actions['inline hide-if-no-js'], $actions['view'], $actions['preview'] );
	}
	return $actions;
}
function cw_popup_bulk_actions( $actions ) {
	unset( $actions['edit'] );
	return $actions; }
function cw_popup_columns( $columns ) {
	return array(
		'cb'                 => $columns['cb'],
		'title'              => 'Name',
		'cw_popup_image'     => 'Image',
		'cw_popup_status'    => 'Status',
		'cw_popup_target'    => 'Targeting',
		'cw_popup_frequency' => 'Frequency',
		'cw_popup_priority'  => 'Priority',
		'cw_popup_modified'  => 'Last modified',
	);
}
function cw_popup_column( $column, $id ) {
	$s = cw_popup_settings( $id );
	switch ( $column ) {
		case 'cw_popup_image':
			echo wp_get_attachment_image(
				$s['image'],
				array( 70, 70 ),
				false,
				array(
					'alt'   => '',
					'style' => 'max-width:70px;max-height:70px;width:auto;height:auto;',
				)
			);
			break;
		case 'cw_popup_status':
			$post   = get_post( $id );
			$status = get_post_status_object( $post->post_status );
			echo esc_html( $status ? $status->label : $post->post_status );
			if ( 'publish' === $post->post_status && ! cw_popup_valid_published( $post ) ) {
				echo '<br><strong>Needs attention — not displayed</strong>';
			}
			break;
		case 'cw_popup_target':
			echo esc_html( 'home' === $s['target'] ? 'Homepage only' : 'Selected pages: ' . implode( ', ', $s['pages'] ) );
			break;
		case 'cw_popup_frequency':
			echo esc_html(
				array(
					'session' => 'Once per tab session',
					'always'  => 'Every eligible page load',
					'daily'   => 'Once every 24 hours',
				)[ $s['frequency'] ] ?? 'Invalid'
			);
			break;
		case 'cw_popup_priority':
			echo (int) $s['priority'];
			break;
		case 'cw_popup_modified':
			echo esc_html( get_the_modified_date( 'Y-m-d H:i', $id ) );
			break;
	}
}
function cw_popup_admin_assets() {
	$screen = get_current_screen();
	if ( ! $screen || ( 'cw_popup' !== $screen->post_type && 'cw_popup_edit' !== ( $_GET['page'] ?? '' ) ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	wp_enqueue_style( 'cw_popup_admin', plugins_url( 'assets/admin.css', CW_POPUP_FILE ), array(), filemtime( dirname( CW_POPUP_FILE ) . '/assets/admin.css' ) );
	if ( 'cw_popup_edit' !== ( $_GET['page'] ?? '' ) ) {
		return;
	}
	wp_enqueue_media();
	cw_popup_assets();
	wp_enqueue_script( 'cw_popup_admin', plugins_url( 'assets/admin.js', CW_POPUP_FILE ), array( 'media-editor', 'cw_popup_frontend' ), filemtime( dirname( CW_POPUP_FILE ) . '/assets/admin.js' ), true );
	wp_localize_script(
		'cw_popup_admin',
		'cw_popup_admin',
		array(
			'ajax'         => admin_url( 'admin-ajax.php' ),
			'searchNonce'  => wp_create_nonce( 'cw_popup_search' ),
			'previewNonce' => wp_create_nonce( 'cw_popup_preview' ),
		)
	);
}
function cw_popup_page_label( $id ) {
	$p = get_post( $id );
	if ( ! $p || 'page' !== $p->post_type ) {
		return 'Unavailable page #' . $id;
	}
	return wp_strip_all_tags( get_the_title( $id ) ) . ' (#' . $id . ') — /' . get_page_uri( $id ) . '/';
}
function cw_popup_editor() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'You cannot manage popups.', '', array( 'response' => 403 ) );
	}
	$id   = absint( $_GET['popup_id'] ?? 0 );
	$post = $id ? get_post( $id ) : null;
	if ( $id && ( ! $post || 'cw_popup' !== $post->post_type || 'trash' === $post->post_status ) ) {
		wp_die( 'This popup is unavailable. Restore it from Trash first.' );
	}
	$data   = array_merge(
		cw_popup_settings( $id ),
		array(
			'name'   => $post ? $post->post_title : '',
			'status' => $post ? $post->post_status : 'draft',
		)
	);
	$errors = array();
	if ( ! empty( $_GET['cw_popup_error'] ) ) {
		$key    = 'cw_popup_error_' . get_current_user_id() . '_' . sanitize_key( $_GET['cw_popup_error'] );
		$failed = get_transient( $key );
		if ( $failed ) {
			$data   = $failed['data'];
			$errors = $failed['errors'];
			delete_transient( $key ); }
	}
	?>
<div class="wrap" id="cw_popup_admin_root">
<h1><?php echo $id ? 'Edit Popup' : 'Add Popup'; ?></h1>
	<?php
	if ( $errors ) :
		?>
		<div class="notice notice-error" role="alert"><p><strong>Not saved. Correct the following:</strong></p><ul>
		<?php
		foreach ( $errors as $error ) {
			echo '<li>' . esc_html( $error ) . '</li>';}
		?>
		</ul><p>Your entered values are preserved below. The saved version has not changed.</p></div><?php endif; ?>
	<?php
	if ( isset( $_GET['cw_popup_saved'] ) ) :
		?>
		<div class="notice notice-success"><p>Popup saved. If your site uses a page cache, purge it after publication or targeting changes.</p></div><?php endif; ?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="cw_popup_form">
<input type="hidden" name="action" value="cw_popup_save"><input type="hidden" name="popup_id" value="<?php echo (int) $id; ?>">
	<?php wp_nonce_field( 'cw_popup_save_' . $id ); ?>
<div class="cw_popup_grid"><div>
<div class="postbox"><h2>Popup content</h2><div class="inside">
<p><label for="cw_popup_name"><strong>Popup name</strong></label><input type="text" class="large-text" id="cw_popup_name" name="name" value="<?php echo esc_attr( $data['name'] ); ?>"><span class="description">Internal name only; never printed over the graphic. Required to publish.</span></p>
<input type="hidden" name="image" id="cw_popup_image" value="<?php echo (int) $data['image']; ?>">
<div id="cw_popup_image_preview"><?php echo wp_get_attachment_image( $data['image'], 'medium', false, array( 'alt' => '' ) ); ?></div>
<p id="cw_popup_dimensions">
	<?php
	$dims = cw_popup_image( $data['image'] );
	if ( $dims ) {
		echo esc_html( $dims['width'] . ' × ' . $dims['height'] . ' px · ' . ( $dims['height'] > $dims['width'] ? 'Portrait' : 'Landscape / square' ) );
	} elseif ( $data['image'] ) {
		echo 'The selected image is unavailable. Please replace it.';}
	?>
</p>
<p><button type="button" class="button" id="cw_popup_choose">Choose / replace image</button> <button type="button" class="button" id="cw_popup_remove">Remove image</button></p>
<p><label for="cw_popup_description"><strong>Accessible description</strong></label><textarea class="large-text" rows="4" id="cw_popup_description" name="description"><?php echo esc_textarea( $data['description'] ); ?></textarea><span class="description">Required to publish. Include the key information in the graphic and the purpose of its link. Starts with the image’s Media Library alt text when available.</span></p>
<p><label for="cw_popup_url"><strong>Link URL (optional)</strong></label><input class="large-text" type="url" id="cw_popup_url" name="url" value="<?php echo esc_attr( $data['url'] ); ?>" placeholder="https://"><span class="description">Leave empty for an unlinked image. HTTP or HTTPS only.</span></p>
<p><label><input type="checkbox" name="new_tab" value="1" <?php checked( $data['new_tab'] ); ?>> Open link in a new tab</label></p>
</div></div>
<div class="postbox"><h2>Where to display</h2><div class="inside">
<p><label><input type="radio" name="target" value="home" <?php checked( $data['target'], 'home' ); ?>> <strong>Show on Homepage Only</strong></label></p>
<p><label><input type="radio" name="target" value="pages" <?php checked( $data['target'], 'pages' ); ?>> Show on Selected Pages</label></p>
<p class="description">Homepage follows WordPress Reading settings. Selected Pages matches only the pages chosen here, not their children or a separate posts index.</p>
<div id="cw_popup_page_selector" <?php echo 'home' === $data['target'] ? 'hidden' : ''; ?>>
<label for="cw_popup_search"><strong>Find pages</strong></label><input type="search" class="large-text" id="cw_popup_search" placeholder="Type at least 2 characters" autocomplete="off" aria-describedby="cw_popup_search_status">
<p id="cw_popup_search_status" role="status" aria-live="polite"></p><ul id="cw_popup_results"></ul><button type="button" class="button" id="cw_popup_more" hidden>Load More</button>
<h3>Selected pages</h3><ul id="cw_popup_selected">
	<?php
	foreach ( $data['pages'] as $page ) :
		?>
		<li data-page-id="<?php echo (int) $page; ?>"><input type="hidden" name="pages[]" value="<?php echo (int) $page; ?>"><span><?php echo esc_html( cw_popup_page_label( $page ) ); ?>
		<?php
		if ( ! cw_popup_public_page( $page ) ) {
			echo ' — Unavailable: remove or restore this page before publishing.';}
		?>
		</span> <button type="button" class="button cw_popup_remove_page" aria-label="<?php echo esc_attr( 'Remove ' . cw_popup_page_label( $page ) ); ?>">Remove</button></li><?php endforeach; ?>
</ul><p class="description">Selections are retained when temporarily switching to Homepage Only.</p></div>
</div></div></div>
<div><div class="postbox"><h2>Display settings</h2><div class="inside">
<p><label for="cw_popup_status"><strong>Status</strong></label><select name="status" id="cw_popup_status"><option value="draft" <?php selected( $data['status'], 'draft' ); ?>>Draft</option><option value="publish" <?php selected( $data['status'], 'publish' ); ?>>Published</option></select></p>
<fieldset><legend><strong>Popup width</strong></legend>
	<?php foreach ( array( 600, 650, 700, 800 ) as $width ) : ?>
<p><label><input type="radio" name="width" value="<?php echo (int) $width; ?>" <?php checked( $data['width'], $width ); ?>> <?php echo (int) $width; ?> pixels</label></p>
	<?php endforeach; ?>
<p class="description">Applies to portrait and landscape images. Smaller screens scale to the available width. Tall images scroll with the full-window overlay.</p></fieldset>
<p><label for="cw_popup_delay"><strong>Display delay (seconds)</strong></label><input type="number" name="delay" id="cw_popup_delay" min="0" max="60" step="1" value="<?php echo esc_attr( $data['delay'] ); ?>"></p>
<p><label for="cw_popup_frequency"><strong>Display frequency</strong></label><select name="frequency" id="cw_popup_frequency">
	<?php
	foreach ( array(
		'session' => 'Once per tab session',
		'always'  => 'Every eligible page load',
		'daily'   => 'Once every 24 hours',
	) as $value => $label ) {
		echo '<option value="' . esc_attr( $value ) . '" ' . selected( $data['frequency'], $value, false ) . '>' . esc_html( $label ) . '</option>';}
	?>
</select></p>
<p class="description">Frequency is per popup and browser. A tab session uses sessionStorage; 24 hours uses localStorage. Separate tabs, cleared storage, or another browser may show it again. If storage is blocked, it can show once per eligible page load. No cookies or visitor tracking.</p>
<p><label for="cw_popup_priority"><strong>Priority (0–100)</strong></label><input type="number" name="priority" id="cw_popup_priority" min="0" max="100" step="1" value="<?php echo esc_attr( $data['priority'] ); ?>"></p>
<p class="description">Higher priority wins. Ties use last modified time, then ID. At most one popup is shown per page load.</p>
<p><button type="submit" class="button button-primary"><?php echo $id ? 'Update Popup' : 'Save Popup'; ?></button></p>
<p><button type="button" class="button" id="cw_popup_preview">Preview unsaved changes</button></p><p id="cw_popup_preview_status" role="status" aria-live="polite"></p>
<p class="description">Preview bypasses targeting, delay, and frequency. It never saves, publishes, or changes visitor storage.</p>
	<?php
	if ( $id ) {
		echo '<p><a class="submitdelete" href="' . esc_url( get_delete_post_link( $id ) ) . '">Move to Trash</a></p>';}
	?>
<p><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=cw_popup' ) ); ?>">Back to All Popups</a></p>
</div></div></div></div></form></div>
	<?php
}
function cw_popup_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'You cannot manage popups.', '', array( 'response' => 403 ) );
	}
	$id = absint( $_POST['popup_id'] ?? 0 );
	check_admin_referer( 'cw_popup_save_' . $id );
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $id ) || ( $id && ( 'cw_popup' !== get_post_type( $id ) || 'trash' === get_post_status( $id ) ) ) ) {
		wp_die( 'Invalid popup.' );
	}
	$raw                   = wp_unslash( $_POST );
	$publish               = isset( $raw['status'] ) && 'publish' === $raw['status'];
	list( $data, $errors ) = cw_popup_validate( $raw, $publish );
	if ( ! $errors ) {
		$meta = $data;
		unset( $meta['name'], $meta['status'] );
		if ( ! $id ) {
			$id = wp_insert_post(
				array(
					'post_type'   => 'cw_popup',
					'post_status' => 'draft',
					'post_title'  => '' !== $data['name'] ? $data['name'] : 'Untitled popup',
				),
				true
			);
			if ( is_wp_error( $id ) ) {
				$errors[] = $id->get_error_message();
				$id       = 0; }
		}
		if ( ! $errors ) {
			$old = get_post_meta( $id, 'cw_popup_settings', true );
			update_post_meta( $id, 'cw_popup_settings', wp_slash( $meta ) );
			$result = wp_update_post(
				wp_slash(
					array(
						'ID'          => $id,
						'post_title'  => '' !== $data['name'] ? $data['name'] : 'Untitled popup',
						'post_status' => $data['status'],
					)
				),
				true
			);
			if ( is_wp_error( $result ) ) {
				update_post_meta( $id, 'cw_popup_settings', wp_slash( $old ) );
				$errors[] = $result->get_error_message(); }
		}
	}
	if ( $errors ) {
		$token = wp_generate_uuid4();
		set_transient(
			'cw_popup_error_' . get_current_user_id() . '_' . $token,
			array(
				'data'   => $data,
				'errors' => $errors,
			),
			10 * MINUTE_IN_SECONDS
		);
		wp_safe_redirect( add_query_arg( 'cw_popup_error', $token, cw_popup_editor_url( $id ) ) );
	} else {
		wp_safe_redirect( add_query_arg( 'cw_popup_saved', 1, cw_popup_editor_url( $id ) ) );
	}
	exit;
}
function cw_popup_ajax_authorize( $action ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'You cannot manage popups.' ), 403 );
	}
	if ( ! check_ajax_referer( $action, 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => 'Your session expired. Reload this screen and try again.' ), 403 );
	}
}
function cw_popup_search() {
	cw_popup_ajax_authorize( 'cw_popup_search' );
	$term = sanitize_text_field( cw_popup_scalar( wp_unslash( $_POST['term'] ?? '' ) ) );
	$page = max( 1, min( 1000, absint( $_POST['page'] ?? 1 ) ) );
	if ( ( function_exists( 'mb_strlen' ) ? mb_strlen( $term ) : strlen( $term ) ) < 2 ) {
		wp_send_json_success(
			array(
				'items' => array(),
				'more'  => false,
			)
		);
	}
	$q     = new \WP_Query(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'has_password'   => false,
			's'              => substr( $term, 0, 100 ),
			'search_columns' => array( 'post_title' ),
			'posts_per_page' => 20,
			'paged'          => $page,
			'orderby'        => array(
				'title' => 'ASC',
				'ID'    => 'ASC',
			),
		)
	);
	$items = array();
	foreach ( $q->posts as $p ) {
		if ( cw_popup_public_page( $p->ID ) ) {
			$items[] = array(
				'id'    => $p->ID,
				'label' => cw_popup_page_label( $p->ID ),
			);
		}
	}
	wp_send_json_success(
		array(
			'items' => $items,
			'more'  => $page < $q->max_num_pages,
		)
	);
}
function cw_popup_preview() {
	cw_popup_ajax_authorize( 'cw_popup_preview' );
	list( $data, $errors ) = cw_popup_validate( wp_unslash( $_POST ), true, true );
	if ( $errors ) {
		wp_send_json_error( array( 'message' => implode( ' ', $errors ) ), 400 );
	}
	wp_send_json_success( cw_popup_candidate( $data ) );
}
