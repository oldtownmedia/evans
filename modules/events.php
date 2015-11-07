<?php
namespace evans;

/**
 * Events
 *
 * Events custom post type
 *
 * @package    WordPress
 * @subpackage Evans
 * @author     Old Town Media
 */
class Events extends CPT{

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
		'orderby' 		=> 'meta_value',
		'order' 		=> 'ASC',
		'nopaging'		=> false
	);

	public function loop_cpt( $args = array() ){
		$html = "";
		$defaults = $this->loop_args;

		$args = wp_parse_args( $args, $defaults );

		$query = array(
			'no_found_rows' 	=> true,
			'update_post_term_cache' => false,
	        'post_type'        	=> $this->cptslug,
	        'order'            	=> $args['order'],
	        'orderby'			=> $args['orderby'],
	        'nopaging'			=> $args['nopaging'],
	        'meta_key'			=> evans_cmb_prefix( $this->cptslug ) . 'date',
			'meta_query' 		=> array(
	            array(
	                'key' 		=> evans_cmb_prefix( $this->cptslug ) . 'date',
	                'value' 	=> date( 'U', strtotime( '-1 day' ) ),
	                'compare' 	=> '>=',
	                'type'		=> 'char'
	            ),
	        )
		);

		$objects = new \WP_Query( $query );

		if ( $objects->have_posts() ){

			$html .= "<ul class='".$this->cptslug."-listing group'>";

			while ( $objects->have_posts() ) : $objects->the_post();

				$html .= $this->display_loop( get_the_id() );

			endwhile;

			$html .= "</ul>";

		}

		wp_reset_postdata();

		return $html;

	}

	public function display_loop( $pid ){

		$html = "";

		$date		= get_post_meta( $pid, evans_cmb_prefix( $this->cptslug ) . 'date', true);
		$time		= get_post_meta( $pid, evans_cmb_prefix( $this->cptslug ) . 'time', true);
		$cost		= get_post_meta( $pid, evans_cmb_prefix( $this->cptslug ) . 'cost', true);
		$location	= get_post_meta( $pid, evans_cmb_prefix( $this->cptslug ) . 'location', true);
		$img_id		= get_post_meta( $pid, evans_cmb_prefix( $this->cptslug ) . 'image_id', true);
		$img		= wp_get_attachment_image_src( $img_id, $this->cptslug.'-thumb' );

			$html .= "<li itemscope itemtype='http://data-vocabulary.org/Event' class='".$this->cptslug." group'>";

				if ( !empty( $img[0] ) ){
					$html .= "<img src='$img[0]' itemprop='image' alt='".get_the_title()."'>";
				}

				$html .= "<h3>" . get_the_title() . "</h3>";

				if ( !empty( $date ) || !empty( $time ) || !empty( $location ) || !empty( $cost ) ){ $html .= "<p>"; }

					$html .= esc_attr( $date ) ." ". esc_attr( $time )."<br>";
					if ( !empty( $location ) ){ $html .= __( 'Location:', 'evans-mu' ) . " <span itemprop='location'>".esc_attr( $location )."</span><br>"; }
					if ( !empty( $cost ) ){ $html .= __( 'Cost:', 'evans-mu' ) . " ".esc_attr( $cost )."<br>"; }

				if ( !empty( $date ) || !empty( $time ) || !empty( $location ) || !empty( $cost ) ){ $html .= "</p>"; }

				$html .= apply_filters( 'the_content', get_the_content() );

			$html .= "</li>";

		return $html;

	}

	public function cmb_metaboxes( array $meta_boxes ) {

		// Start with an underscore to hide fields from custom fields list
		$prefix = evans_cmb_prefix( $this->cptslug );

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
					'type' => 'text_date_timestamp',
				),
				array(
					'name' => __( 'Time', 'evans-mu' ),
					'desc' => __( 'Enter a time for your '.$this->cptslug, 'evans-mu' ),
					'id'   => $prefix . 'time',
					'type' => 'text',
				),
				array(
					'name' => __( 'Event Cost', 'evans-mu' ),
					'desc' => __( 'Enter the guest cost for your '.$this->cptslug.' (optional)', 'evans-mu' ),
					'id'   => $prefix . 'cost',
					'type' => 'text_money',
				),
				array(
					'name' => __( 'Location', 'evans-mu' ),
					'desc' => __( 'Enter a location name or address for your '.$this->cptslug, 'evans-mu' ),
					'id'   => $prefix . 'location',
					'type' => 'text',
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

$events = new Events;
$events->hooks();