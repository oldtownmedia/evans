<?php
namespace evans;

/**
 * Testimonials
 *
 * Testimonials custom post type.
 *
 * @package    WordPress
 * @subpackage Evans
 */
final class Testimonials extends CPT {
	protected $cptslug 			= 'testimonial';
	protected $cptslug_plural	= 'testimonials';
	protected $singular			= 'Testimonial';
	protected $plural			= 'Testimonials';
	protected $icon				= 'dashicons-format-quote';
	protected $hide_view 		= true;

	// Arguments to define the CPT
	protected $cpt_args			= array(
		'exclude_from_search'	=> true,
		'show_in_nav_menus'		=> false,
	);

	// Arguments for the CPT loop
	protected $loop_args = array(
		'no_found_rows'	=> true,
		'orderby' 		=> 'menu_order',
		'order' 		=> 'ASC',
		'posts_per_page' => 100,
	);

	/**
	 * Display a single item from the queried posts.
	 *
	 * This is the most often-overrideen function and will often contain CMB
	 * calls and custom display HTML.
	 *
	 * @param int $ Post ID.
	 * @return string HTML contents for the individual post.
	 */
	public function display_single( $pid ) {
		$html = '';

		$reviewer = get_post_meta( $pid, cmb_prefix( get_post_type() ) . 'reviewer', true );

		$html .= "<li class='" . esc_attr( $this->cptslug ) . "'>";

			$html .= '<h3>' . esc_html( get_the_title() ) . '</h3>';

			$html .= apply_filters( 'the_content', get_the_content() );

		if ( ! empty( $reviewer ) ) {
			$html .= "<p class='cite'> - <span class='reviewer' itemprop='reviewer'>" . esc_html( $reviewer ) . '</span></p>';
		}

		$html .= '</li>';

		return $html;
	}

	/**
	 * Add in array of custom metabox fields for use with CMB2.
	 */
	public function cmb_metaboxes() {
		// Setup the main CMB box
		$cmb = parent::cmb_metaboxes();

		$cmb->add_field( array(
			'name'	=> __( 'Reviewer Name', 'evans-mu' ),
			'id'	=> $this->prefix . 'reviewer',
			'type'	=> 'text',
		) );
	}
}

/*
 * Instantiate the hooks method
 */
$testimonials = new Testimonials;
$testimonials->hooks();
