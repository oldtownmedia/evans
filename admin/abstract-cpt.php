<?php

abstract class CPT{

	protected $cptslug;
	protected $cptslug_plural;
	protected $singular;
	protected $plural;
	protected $cpt_args;
	protected $icon;
	protected $loop_args;
	protected $thumbnail_size;
	protected $tax_slug;
	protected $taxonomy_name;
	protected $taxonomy_plural;

	public function hooks(){

		add_action( 'init', array( $this, 'define_cpt' ) );
		add_filter( 'cmb2_meta_boxes', array( $this, 'cmb_metaboxes' ) );
		add_filter( 'cpt_array_filter', array( $this, 'dashboard_cpt_loop' ), 10 );
		add_shortcode( $this->cptslug_plural, array( $this, 'shortcode' ) );

		// If fed dimensions for a thumbnails
		if ( !empty( $this->thumbnail_size ) ){
			add_image_size( $this->cptslug.'-thumb', $this->thumbnail_size['width'], $this->thumbnail_size['height'], true );
		}

		// If we want to use the taxonomy, hook in the taxonomy functions
		if ( !empty( $this->taxonomy_name ) ){
			add_action( 'init', array( $this, 'define_taxonomy' ), 0 );
		}

	}

	// Wrapper for loop_cpt in case of legacy naming from V1
	public function get_cpt( $args ){

		return loop_cpt( $args );

	}

	public function loop_cpt( $args ){

		$html .= "";
		$defaults = $this->loop_args;

		$args = wp_parse_args( $args, $defaults );

		$query = array(
			'post_type'			=> $this->cptslug,
			'no_found_rows' 	=> $args['no_found_rows'],
			'posts_per_page'	=> $args['quantity'],
			'order'				=> $args['order'],
			'orderby'			=> $args['orderby'],
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

			$html .= "<ul class='".$this->cptslug."-listing group'>";

			while ( $objects->have_posts() ) : $objects->the_post();

				$html .= $this->display_loop( get_the_id() );

			endwhile;

			$html .= "</ul>";

		} else {

			$html = "<h3>".sprintf( __( "There are no %s to list. Check back soon!", 'otm-mu' ), $this->cptslug_plural )."</h3>";

		}

		wp_reset_postdata();

		return $html;

	}

	public function display_loop( $pid ){

		$html = "";

		$html .= "<li class='".$this->cptslug."'>";

			$html .= "<h3>".get_the_title( $pid )."</h3>";

			$html .= apply_filters( 'the_content', get_the_content() );

		$html .= "</li>";

		return $html;

	}

	public function taxonomy_loop( $args ){

		$html = "";

		$terms = get_terms(
			$this->tax_slug,
			array(
				'hide_empty'	=> true,
				'number'		=> 500
			)
		);

		if ( !empty( $terms ) ){

			foreach ( $terms as $term ) :

				$html .= "<h2>".$term->name."</h2>";

				$html .= "<p>".term_description( $term->term_id, $this->tax_slug )."</p>";

				// Add the group we're querying to the get_cpt arguments
				$args['group'] = $term->slug;
				$html .= $this->loop_cpt( $args );

			endforeach;

		}

		return $html;

	}

	public function shortcode( $atts ){

		$atts = shortcode_atts(
			array(
				'quantity' 	=> '',
				'group'		=> '',
				'id'		=> '',
				'random'	=> false
			),
			$atts
		);

		$args = array(
			'quantity'	=> $atts['quantity'],
			'group'		=> $atts['group'],
			'random'	=> $atts['random'],
			'pid'		=> $atts['id'],
			'echo'		=> false
		);

		return $this->loop_cpt( $args );

	}

	public function define_cpt(){

		$labels = array(
			'name'               => sprintf( _x( '%s', 'post type general name', 'otm-mu' ), $this->plural ),
			'singular_name'      => sprintf( _x( '%s', 'post type singular name', 'otm-mu' ), $this->singular ),
			'add_new'            => sprintf( _x( 'Add New', '%s', 'otm-mu' ), $this->cptslug ),
			'add_new_item'       => sprintf( __( 'Add New %s', 'otm-mu' ), $this->singular ),
			'edit_item'          => sprintf( __( 'Edit %s', 'otm-mu' ), $this->singular ),
			'new_item'           => sprintf( __( 'New %s', 'otm-mu' ), $this->singular ),
			'all_items'          => sprintf( __( 'All %s', 'otm-mu' ), $this->plural ),
			'view_item'          => sprintf( __( 'View %s', 'otm-mu' ), $this->singular ),
			'search_items'       => sprintf( __( 'Search %s', 'otm-mu' ), $this->plural ),
			'not_found'          => sprintf( __( 'No %s found', 'otm-mu' ), $this->plural ),
			'not_found_in_trash' => sprintf( __( 'No %s found in the Trash', 'otm-mu' ), $this->plural ),
			'parent_item_colon'  => sprintf( __( 'Parent %s', 'otm-mu' ), $this->singular ),
			'menu_name'          => sprintf( __( '%s', 'otm-mu' ), $this->plural )
		);

		$defaults = array(
			'labels'		=> $labels,
			'public'        => true,
			'menu_position' => 7,
			'menu_icon'		=> $this->icon,
			'rewrite'       => false,
			'hierarchical'	=> true,
			'supports'      => array( 'title', 'editor' ),
		);

		$args = wp_parse_args( $this->cpt_args, $defaults );

		register_post_type( $this->cptslug, $args );

	}

	public function cmb_metaboxes( array $meta_boxes ){
		return $meta_boxes;
	}

	public function define_taxonomy(){

		$labels = array(
			'name'              => sprintf( _x( '%s', 'taxonomy general name', 'otm-mu' ), $this->taxonomy_name ),
			'singular_name'     => sprintf( _x( '%s', 'taxonomy singular name', 'otm-mu' ), $this->taxonomy_name ),
			'search_items'      => sprintf( __( 'Search %s', 'otm-mu' ), $this->taxonomy_plural ),
			'all_items'         => sprintf( __( 'All %s', 'otm-mu' ), $this->taxonomy_plural ),
			'parent_item'       => sprintf( __( 'Parent %s', 'otm-mu' ), $this->taxonomy_name ),
			'parent_item_colon' => sprintf( __( 'Parent %s:', 'otm-mu' ), $this->taxonomy_name ),
			'edit_item'         => sprintf( __( 'Edit %s', 'otm-mu' ), $this->taxonomy_name ),
			'update_item'       => sprintf( __( 'Update %s', 'otm-mu' ), $this->taxonomy_name ),
			'add_new_item'      => sprintf( __( 'Add New %s', 'otm-mu' ), $this->taxonomy_name ),
			'new_item_name'     => sprintf( __( 'New %s', 'otm-mu' ), $this->taxonomy_name ),
			'menu_name'         => sprintf( __( '%s', 'otm-mu' ), $this->taxonomy_plural ),
		);

		$args = array(
			'labels' 			=> $labels,
			'show_tagcloud'		=> false,
			'show_admin_column' => true,
			'hierarchical'		=> true
		);

		register_taxonomy( $this->tax_slug, $this->cptslug, $args );

	}

	public function dashboard_cpt_loop( $cpt_array ){

		$cpt_array[] = array(
			'slug'		=> $this->cptslug,
			'singular'	=> $this->singular,
			'plural'	=> $this->plural,
			'class'		=> $this->icon
		);

		return $cpt_array;

	}

}