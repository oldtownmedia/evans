<?php
namespace evans;

/**
 * Events
 *
 * Events custom post type.
 *
 * @package    WordPress
 * @subpackage Evans
 * @author     Old Town Media
 */
final class Events extends CPT{

	protected $cptslug 			= 'event';
	protected $cptslug_plural	= 'events';
	protected $singular			= 'Event';
	protected $plural			= 'Events';
	protected $icon				= 'dashicons-calendar';
	protected $hide_view 		= false;
	protected $thumbnail_size	= array(
		'width'		=> 300,
		'height'	=> 200
	);

	// Arguments to define the CPT
	protected $cpt_args			= array(
		'show_in_nav_menus'	=> false
	);

	// Arguments for the CPT loop
	protected $loop_args = array(
		'orderby' 			=> 'meta_value',
		'order' 			=> 'ASC',
		'nopaging'			=> false,
		'no_found_rows' 	=> true,
		'update_post_term_cache' => false,
	);


	/**
	 * Perform query modifications without touching our loop function.
	 *
	 * @param array $query Set query arguments.
	 * @param array $args Incoming arguments.
	 * @return string Modified query arguments.
	 */
	public function query_mods( $query, $args ){

		$query['meta_key']		= cmb_prefix( $this->cptslug ) . 'date';
		$query['meta_query'] 	= array(
            array(
                'key' 		=> cmb_prefix( $this->cptslug ) . 'date',
                'value' 	=> date( 'U', strtotime( '-1 day' ) ),
                'compare' 	=> '>=',
                'type'		=> 'char'
            ),
        );

		return parent::query_mods( $query, $args );

	}


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

		$date		= get_post_meta( $pid, cmb_prefix( get_post_type() ) . 'date', true);
		$cost		= get_post_meta( $pid, cmb_prefix( get_post_type() ) . 'cost', true);
		$img_id		= get_post_meta( $pid, cmb_prefix( get_post_type() ) . 'image_id', true);
		$img		= wp_get_attachment_image_src( $img_id, get_post_type().'-thumb' );

			$html .= "<li itemscope itemtype='http://data-vocabulary.org/Event' class='".$this->cptslug." group'>";

				$html .= $this->get_img( $img );

				$html .= "<h3>" . esc_attr( get_the_title() ) . "</h3>";

				$html .= "<p>";

					$html .= date( 'm/d/Y', esc_attr( $date ) ) . " " . date( 'g:i A', esc_attr( $date ) )."<br>";

					if ( !empty( $cost ) ){
						$html .= __( 'Cost:', 'evans-mu' ) . " ".esc_attr( $cost )."<br>";
					}

				$html .= "</p>";

				$html .= apply_filters( 'the_content', get_the_content() );

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
					'name' => __( 'Start Date/Time', 'evans-mu' ),
					'desc' => __( 'Enter a date for your '.$this->cptslug, 'evans-mu' ),
					'id'   => $prefix . 'date',
					'type' => 'text_datetime_timestamp',
				),
				array(
					'name' => __( 'Event Cost', 'evans-mu' ),
					'desc' => __( 'Enter the guest cost for your '.$this->cptslug.' (optional)', 'evans-mu' ),
					'id'   => $prefix . 'cost',
					'type' => 'text_money',
				),
				array(
					'name' => __( 'Image', 'evans-mu' ),
					'id'   => $prefix . 'image',
					'type' => 'file',
					'allow' => array( 'attachment' )
				),
			),
		);

		return $meta_boxes;

	}

}


/*
 * Instantiate the hooks method
 */
$events = new Events;
$events->hooks();
