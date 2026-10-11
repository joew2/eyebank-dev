<?php
/**
 * Plugin Name: Custom Top Links English Site
 * Plugin URI:  https://www.eyedonation.org/
 * Description: Adds the English site top navigation links to the Genesis header right area.
 * Version:     1.0.0
 * Author:      Creative Way Solutions
 * License:     GPL-2.0-or-later
 * Text Domain: custom-top-links-english-site
 *
 * @package Custom_Top_Links_English_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'genesis_header_right', 'ctl_es_render_top_links', 1 );

/**
 * Render the custom top links in the Genesis header right area.
 */
function ctl_es_render_top_links() {
	$links = array(
		array(
			'classes' => 'top-nav-item surgeons',
			'url'     => 'https://www.eyedonation.org/transplant/',
			'label'   => 'Transplant Portal',
			'a_class' => 'button-sr-portal',
		),
		array(
			'classes' => 'top-nav-item surgeons',
			'url'     => 'https://www.eyedonation.org/researchers/',
			'label'   => 'Research Portal',
			'a_class' => 'button-sr-portal',
		),
		array(
			'classes' => 'top-nav-item financial',
			'url'     => 'https://host.nxt.blackbaud.com/donor-form/?svcid=renxt&formId=c53c8d6b-c421-4baf-92bb-044c2ccd81b7&envid=p-t70-gTghE0ScbPMC-otWOw&zone=usa',
			'label'   => 'Make a Financial Contribution',
			'a_class' => '',
		),
		array(
			'classes'   => 'top-nav-item',
			'shortcode' => '[spanish_page_link]',
		),
	);
	?>
	<div class="top-nav-container">
		<div class="top-nav">
			<?php foreach ( $links as $link ) : ?>
				<?php
				$link_content = ctl_es_get_link_content( $link );

				if ( '' === $link_content ) {
					continue;
				}
				?>
				<div class="<?php echo esc_attr( $link['classes'] ); ?>">
					<?php echo $link_content; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/**
 * Build an individual top-link item.
 *
 * Shortcode items are expected to return their own complete link markup.
 *
 * @param array $link Link settings.
 * @return string
 */
function ctl_es_get_link_content( $link ) {
	if ( ! empty( $link['shortcode'] ) ) {
		return wp_kses_post( do_shortcode( $link['shortcode'] ) );
	}

	if ( empty( $link['url'] ) || empty( $link['label'] ) ) {
		return '';
	}

	$attributes = array(
		'href' => esc_url( $link['url'] ),
	);

	if ( ! empty( $link['a_class'] ) ) {
		$attributes['class'] = esc_attr( $link['a_class'] );
	}

	$attribute_html = '';

	foreach ( $attributes as $name => $value ) {
		$attribute_html .= sprintf( ' %s="%s"', esc_attr( $name ), $value );
	}

	return sprintf(
		'<a%s>%s</a>',
		$attribute_html,
		esc_html( $link['label'] )
	);
}
