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
class Test_CMBS extends CPT{

	protected $cptslug 			= 'cmbtest';
	protected $cptslug_plural	= 'cmbtests';
	protected $singular			= 'cmbtest';
	protected $plural			= 'cmbtest';
	protected $icon				= 'dashicons-format-quote';
	protected $hide_view 		= true;

	// Arguments to define the CPT
	protected $cpt_args			= array(

	);

	// Arguments for the CPT loop
	protected $loop_args = array(
		'no_found_rows'	=> true,

	);

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
					'name'	=> __( 'Test Time', 'evans-mu' ),
					'id'	=> $prefix . 'time',
					'type'	=> 'text_time',
				),
				array(
					'name'	=> __( 'Test Timezone', 'evans-mu' ),
					'id'	=> $prefix . 'timezone_timestamp',
					'type'	=> 'text_datetime_timestamp_timezone',
				),
				array(
					'name'	=> __( 'Embed', 'evans-mu' ),
					'id'	=> $prefix . 'embed',
					'type'	=> 'oembed',
				),

			),
		);

		return $meta_boxes;

	}

}


/*
 * Instantiate the hooks method
 */
$testimonials = new Test_CMBS;
$testimonials->hooks();