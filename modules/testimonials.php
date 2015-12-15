<?php
namespace evans;

/**
 * Testimonials
 *
 * Testimonials custom post type.
 *
 * @package    WordPress
 * @subpackage Evans
 * @author     Old Town Media
 */
class Testimonials extends CPT{

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
		'quantity'		=> 500,
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
	public function display_single( $pid ){

		$html = "";

		$reviewer = get_post_meta( $pid, cmb_prefix( get_post_type() ) . 'reviewer', true );

		$html .= "<li class='".$this->cptslug."'>";

			$html .= "<h3>".get_the_title()."</h3>";

			$html .= apply_filters( 'the_content', get_the_content() );

			if ( !empty( $reviewer ) ){
				$html .= "<p class='cite'> - <span class='reviewer' itemprop='reviewer'>".esc_attr( $reviewer )."</span></p>";
			}

		$html .= "</li>";

		return $html;

	}


	/**
	 * Add in array of custom metabox fields for use with CMB2.
	 *
	 * @param array $meta_boxes Passed through with CMB2.
	 * @return array Revised array of all metaboxes.
	 */
	public function cmb_metaboxes( array $meta_boxes ) {

		// Start with an underscore to hide fields from custom fields list
		$prefix = cmb_prefix( $this->cptslug );

		$meta_boxes[] = array(
			'id'			=> $this->cptslug.'_metabox',
			'title'			=> sprintf( __( '%s Information', 'evans-mu' ), $this->singular ),
			'object_types'	=> array( $this->cptslug, ),
			'context'		=> 'normal',
			'priority'		=> 'high',
			'show_names'	=> true,
			'fields'		=> array(
				array(
					'name'	=> __( 'Reviewer Name', 'evans-mu' ),
					'id'	=> $prefix . 'reviewer',
					'type'	=> 'text',
				),
			),
		);

		return $meta_boxes;

	}

}


/*
 * Instantiate the hooks method
 */
$testimonials = new Testimonials;
$testimonials->hooks();