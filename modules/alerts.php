<?php
namespace evans;

/**
 * Alerts
 *
 * Alerts custom post type.
 *
 * @package    WordPress
 * @subpackage Evans
 * @author     Old Town Media
 */
class Alerts extends CPT{

	protected $cptslug 			= 'alert';
	protected $cptslug_plural	= 'alerts';
	protected $singular			= 'Alert';
	protected $plural			= 'Alerts';
	protected $icon				= 'dashicons-megaphone';
	protected $hide_view 		= true;

	// Arguments to define the CPT
	protected $cpt_args			= array(
		'exclude_from_search'	=> true,
		'show_in_nav_menus'		=> false,
		'publicly_queryable'	=> false,
		'has_archive'   		=> false,
	);

	// Arguments for the CPT loop
	protected $loop_args = array(
		'no_found_rows'	=> true,
		'orderby' 		=> 'menu_order',
		'order' 		=> 'ASC',
		'posts_per_page'=> 1,
		'nopaging'		=> true,
	);


	/**
	 * Loop through custom post type and return combined HTML from posts.
	 *
	 * @see WP_Query, $this->display_single
	 *
	 * @param array $args Description.
	 * @return string Combined HTML contents of the looped query.
	 */
	public function loop_cpt( $args = array() ){
		$html = "";

		$objects = new \WP_Query( $this->query_mods( array(), $args ) );

		if ( $objects->have_posts() ){

			while ( $objects->have_posts() ) : $objects->the_post();

				$html .= $this->display_single( get_the_id() );

			endwhile;

		}

		wp_reset_postdata();

		return $html;

	}


	/**
	 * Perform query modifications without touching our loop function.
	 *
	 * @param array $query Set query arguments.
	 * @param array $args Incoming arguments.
	 * @return string Modified query arguments.
	 */
	public function query_mods( $query, $args ){

		$query['meta_query'] 	= array(
			'relation'	=> 'OR',
            array(
	            array(
	                'key' 		=> cmb_prefix( $this->cptslug ) . 'active',
	                'value' 	=> 'active',
	                'compare' 	=> '=',
	            ),
	            array(
	                'key' 		=> cmb_prefix( $this->cptslug ) . 'start_date',
	                'compare' 	=> 'NOT EXISTS',
	            ),
            ),
            array(
	            array(
	                'key' 		=> cmb_prefix( $this->cptslug ) . 'active',
	                'value' 	=> 'active',
	                'compare' 	=> '=',
	            ),
	            array(
	                'key' 		=> cmb_prefix( $this->cptslug ) . 'start_date',
	                'value' 	=> time(),
	                'compare' 	=> '<=',
	                'type'		=> 'char'
	            ),
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

			$end = get_post_meta( $pid, cmb_prefix( get_post_type() ) . 'end_date', true );

			if ( !$end || time() <= $end ){

				$html .= "<div class='".$this->cptslug."'>";

					$html .= "<h4>" . get_the_title() . "</h4>";

					$html .= apply_filters( 'the_content', get_the_content() );

				$html .= "</div>";

			}

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
					'name'    => __( 'Active?', 'evans-mu' ),
					'desc'    => __( 'Choose whether this '.$this->cptslug.' should be active or not', 'evans-mu' ),
					'id'      => $prefix . 'active',
					'type'    => 'radio',
					'options' => array(
						'active'	=> __( 'Active', 'evans-mu' ),
						'inactive'	=>  __( 'Inactive', 'evans-mu' )
					),
				),
				array(
					'name' 		=> __( 'Start Date', 'evans-mu' ),
					'desc' 		=> __( 'If you would like to schedule this '.$this->cptslug.', enter a start date.', 'evans-mu' ),
					'id'   		=> $prefix . 'start_date',
					'type' 		=> 'text_datetime_timestamp',
					'default'	=> time()
				),
				array(
					'name' => __( 'End Date', 'evans-mu' ),
					'desc' => __( 'If you would like to schedule this '.$this->cptslug.', enter an end date.', 'evans-mu' ),
					'id'   => $prefix . 'end_date',
					'type' => 'text_datetime_timestamp',
				),
			),
		);

		return $meta_boxes;

	}

}


/*
 * Instantiate the hooks method
 */
$alerts = new Alerts;
$alerts->hooks();