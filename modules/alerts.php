<?php

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
		'quantity'		=> 3,

	);

	public function loop_cpt( $args = array() ){

		$html .= "";
		$defaults = $this->loop_args;

		$args = wp_parse_args( $args, $defaults );

		$query = array(
			'no_found_rows' 	=> true,
			'update_post_term_cache' => false,
	        'post_type'        	=> $this->cptslug,
	        'order'            	=> $args['order'],
	        'orderby'			=> $args['orderby'],
	        'nopaging'			=> $args['nopaging'],
			'meta_query' 	=> array(
				'relation'	=> 'OR',
	            array(
		            array(
		                'key' 		=> evans_cmb_prefix( $this->cptslug ) . 'active',
		                'value' 	=> 'active',
		                'compare' 	=> '=',
		            ),
		            array(
		                'key' 		=> evans_cmb_prefix( $this->cptslug ) . 'start_date',
		                'compare' 	=> 'NOT EXISTS',
		            ),
	            ),
	            array(
		            array(
		                'key' 		=> evans_cmb_prefix( $this->cptslug ) . 'active',
		                'value' 	=> 'active',
		                'compare' 	=> '=',
		            ),
		            array(
		                'key' 		=> evans_cmb_prefix( $this->cptslug ) . 'start_date',
		                'value' 	=> time(),
		                'compare' 	=> '<=',
		                'type'		=> 'char'
		            ),
	            ),
	        ),
		);

		// If our shortcode passed in the random as true
		if ( !empty( $args['random'] ) && $args['random'] == true ){
			$query['no_found_rows']		= false;
			$query['posts_per_page']	= 1;
			$query['orderby']			= 'rand';
		}

		// If our shortcode passed in an id
		if ( !empty( $args['pid'] ) ){
			$query['no_found_rows']		= false;
			$query['posts_per_page']	= 1;
			$query['post__in']			= array( $args['pid'] );
		}

		// If our shortcode passed in a group id OR our taxonomy_loop passes in a group id
		if ( !empty( $args['group'] ) ){
			$query[$this->tax_slug]			= array( $args['group'] );
		}

		$objects = new WP_Query( $query );

		if ( $objects->have_posts() ){

			$title_tag = $args['title_tag'];

			while ( $objects->have_posts() ) : $objects->the_post();

				$html .= $this->display_loop( get_the_id() );

			endwhile;

		}

		wp_reset_postdata();

		return $html;

	}

	public function display_loop( $pid ){

		$html = "";

			$end = get_post_meta( $pid, evans_cmb_prefix( $this->cptslug ) . 'end_date', true );

			if ( !$end || time() <= $end ){

				$html .= "<div class='".$this->cptslug."'>";

					$html .= "<$title_tag>" . get_the_title() . "</$title_tag>";

					$html .= apply_filters( 'the_content', get_the_content() );

				$html .= "</div>";

			}

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

$alerts = new Alerts;
$alerts->hooks();