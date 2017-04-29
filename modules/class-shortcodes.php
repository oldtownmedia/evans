<?php
namespace evans;

/*
 * Create a button for the editor.
 *
 * @param array $atts Attributes
 * @return string HTML output
 */
add_shortcode( 'button', __NAMESPACE__ . '\button_shortcode' );
function button_shortcode( $atts ) {

	$atts = shortcode_atts(
		array(
			'link'		=> '',
			'text'		=> 'Read More',
			'target'	=> '',
		),
		$atts
	);

	// Make sure we're actually linking somewhere
	if ( empty( $atts['link'] ) ) {
		return;
	}

	$link = "<a class='button'";
		$link .= "href='" . esc_url( $atts['link'] ) . "'";
	if ( ! empty( $atts['target'] ) ) { $link .= " target='" . esc_attr( $atts['target'] ) . "'"; }
	$link .= '>';
		$link .= esc_html( $atts['text'] );
	$link .= '</a>';

	return $link;

}
