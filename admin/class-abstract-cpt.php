<?php

/**
 * Class to generate custom post types using only a few variables.
 *
 * @abstract
 * @package    WordPress
 * @subpackage Evans
 * @author     Old Town Media
 */
abstract class CPT{

	/**
	 * cptslug
	 * Slug ID for the cpt.
	 *
	 * @var string
	 * @access protected
	 */
	protected $cptslug;

	/**
	 * cptslug_plural
	 * Plural slug ID for the cpt.
	 *
	 * @var string
	 * @access protected
	 */
	protected $cptslug_plural;

	/**
	 * singular
	 * Singular string used for messages/labels.
	 *
	 * @var string
	 * @access protected
	 */
	protected $singular;

	/**
	 * plural
	 * Plural string used for messages/labels.
	 *
	 * @var mixed
	 * @access protected
	 */
	protected $plural;

	/**
	 * cpt_args
	 * Array of arguments used in register_custom_post_type.
	 * Generally [ exclude_from_search, show_in_nav_menus, publicly_queryable, supports, has_archive ]
	 *
	 * @var array
	 * @access protected
	 */
	protected $cpt_args;

	/**
	 * icon
	 * Icon ID from Dashicons for the cpt icon in the admin nav menu.
	 * https://developer.wordpress.org/resource/dashicons/
	 *
	 * @var string
	 * @access protected
	 */
	protected $icon;

	/**
	 * hide_view
	 * Choose whether or not you want to hide the "View {cptslug}" on the admin side.
	 * Used in cases where the cpt doesn't have a single on the front end.
	 *
	 * @var boolean
	 * @access protected
	 */
	protected $hide_view;

	/**
	 * loop_args
	 * Array of arguments used in the WP_Query loop.
	 Generally [ orderby, order, quantity ]
	 *
	 * @var array
	 * @access protected
	 */
	protected $loop_args;

	/**
	 * thumbnail_size
	 * Array of arguments used if you'd like to create a custom thumbnail size
	 * for images used on the front end by the cpt.
	 * Acceptd agrs: [ width, height ]
	 *
	 * @var array
	 * @access protected
	 */
	protected $thumbnail_size;

	/**
	 * tax_slug
	 * Slug ID used by a taxonomy if you want one.
	 *
	 * @var string
	 * @access protected
	 */
	protected $tax_slug;

	/**
	 * taxonomy_name
	 * Singular pretty name used by messages/labels
	 *
	 * @var mixed
	 * @access protected
	 */
	protected $taxonomy_name;

	/**
	 * taxonomy_plural
	 * Plural pretty name used by messages/labels
	 *
	 * @var mixed
	 * @access protected
	 */
	protected $taxonomy_plural;


	/**
	 * Hooks function to fire off the events we need.
	 *
	 * @see add_action, add_filter, add_shortcode
	 *
	 * @return void.
	 */
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

		// If we don't want links to the single to appear in the admin section
		if ( $this->hide_view === true ){
			add_filter( 'post_row_actions', array( $this, 'remove_view_from_row' ), 10, 2 );
			add_filter( 'page_row_actions', array( $this, 'remove_view_from_row' ), 10, 2 );	// In case pot is hierarchical
			add_filter( 'get_sample_permalink_html', array( $this, 'remove_permalink_option' ), '', 4 );
		}

	}

	/**
	 * Wrapper for loop_cpt in case of legacy naming from V1.
	 *
	 * @see loop_cpt
	 *
	 * @param array $args Array of arguments to use in the WP_QUery loop.
	 * @return string HTML contents of the looped query.
	 */
	public function get_cpt( $args = array() ){

		return $this->loop_cpt( $args );

	}


	/**
	 * Loop through custom post type and return combined HTML from posts.
	 *
	 * @see WP_Query, $this->display_loop
	 *
	 * @param array $args Description.
	 * @return string Combined HTML contents of the looped query.
	 */
	public function loop_cpt( $args = array() ){
		$html = "";
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
			$query[$this->tax_slug]		= array( $args['group'] );
		}

		$objects = new WP_Query( $query );

		if ( $objects->have_posts() ){

			$html .= "<ul class='".$this->cptslug."-listing group'>";

			while ( $objects->have_posts() ) : $objects->the_post();

				$html .= $this->display_loop( get_the_id() );

			endwhile;

			$html .= "</ul>";

		} else {

			$html = "<h3>".sprintf( __( "There are no %s to list. Check back soon!", 'evans-mu' ), $this->cptslug_plural )."</h3>";

		}

		wp_reset_postdata();

		return $html;

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
	public function display_loop( $pid ){
		$html = "";

		$html .= "<li class='".$this->cptslug."'>";

			$html .= "<h3>".get_the_title( $pid )."</h3>";

			$html .= apply_filters( 'the_content', get_the_content() );

		$html .= "</li>";

		return $html;

	}


	/**
	 * Loop through a taxonomy.
	 *
	 * This function will loop through all items in a taxonomy and call loop_cpt
	 * on each and every one.
	 *
	 * @see $this->loop_cpt, get_terms
	 *
	 * @param array $args Arguments to be passed to get_terms.
	 * @return string HTML content of the looped taxonomy & posts.
	 */
	public function taxonomy_loop( $args = array() ){
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


	/**
	 * Shortcode definition to call a loop of the cpt within the content.
	 *
	 * @see shortcode_args, $this->loop_cpt
	 *
	 * @param array $atts arguments to be passed through to the loop.
	 * @return string HTML content of the looped query and posts.
	 */
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


	/**
	 * Custom Post Type definition.
	 *
	 * We're actually creating out custom post type here and passing in custom
	 * arguments if we have any. Several defaults include supports of title & editor,
	 * public, menu position, etc.
	 *
	 * @see register_post_type
	 *
	 * @return void.
	 */
	public function define_cpt(){

		$labels = array(
			'name'               => sprintf( _x( '%s', 'post type general name', 'evans-mu' ), $this->plural ),
			'singular_name'      => sprintf( _x( '%s', 'post type singular name', 'evans-mu' ), $this->singular ),
			'add_new'            => sprintf( _x( 'Add New', '%s', 'evans-mu' ), $this->cptslug ),
			'add_new_item'       => sprintf( __( 'Add New %s', 'evans-mu' ), $this->singular ),
			'edit_item'          => sprintf( __( 'Edit %s', 'evans-mu' ), $this->singular ),
			'new_item'           => sprintf( __( 'New %s', 'evans-mu' ), $this->singular ),
			'all_items'          => sprintf( __( 'All %s', 'evans-mu' ), $this->plural ),
			'view_item'          => sprintf( __( 'View %s', 'evans-mu' ), $this->singular ),
			'search_items'       => sprintf( __( 'Search %s', 'evans-mu' ), $this->plural ),
			'not_found'          => sprintf( __( 'No %s found', 'evans-mu' ), $this->plural ),
			'not_found_in_trash' => sprintf( __( 'No %s found in the Trash', 'evans-mu' ), $this->plural ),
			'parent_item_colon'  => sprintf( __( 'Parent %s', 'evans-mu' ), $this->singular ),
			'menu_name'          => sprintf( __( '%s', 'evans-mu' ), $this->plural )
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


	/**
	 * Placeholder for setting metaboxes using the CMB2 library.
	 *
	 * Optional.
	 *
	 * @param array $meta_boxes Passed through with CMB2.
	 * @return array Passthrough of all metaboxes.
	 */
	public function cmb_metaboxes( array $meta_boxes ){
		return $meta_boxes;
	}


	/**
	 * Taxonomy definition if we have set the proper variables at the beginning.
	 *
	 * @see register_taxonomy
	 *
	 * @return void.
	 */
	public function define_taxonomy(){

		$labels = array(
			'name'              => sprintf( _x( '%s', 'taxonomy general name', 'evans-mu' ), $this->taxonomy_name ),
			'singular_name'     => sprintf( _x( '%s', 'taxonomy singular name', 'evans-mu' ), $this->taxonomy_name ),
			'search_items'      => sprintf( __( 'Search %s', 'evans-mu' ), $this->taxonomy_plural ),
			'all_items'         => sprintf( __( 'All %s', 'evans-mu' ), $this->taxonomy_plural ),
			'parent_item'       => sprintf( __( 'Parent %s', 'evans-mu' ), $this->taxonomy_name ),
			'parent_item_colon' => sprintf( __( 'Parent %s:', 'evans-mu' ), $this->taxonomy_name ),
			'edit_item'         => sprintf( __( 'Edit %s', 'evans-mu' ), $this->taxonomy_name ),
			'update_item'       => sprintf( __( 'Update %s', 'evans-mu' ), $this->taxonomy_name ),
			'add_new_item'      => sprintf( __( 'Add New %s', 'evans-mu' ), $this->taxonomy_name ),
			'new_item_name'     => sprintf( __( 'New %s', 'evans-mu' ), $this->taxonomy_name ),
			'menu_name'         => sprintf( __( '%s', 'evans-mu' ), $this->taxonomy_plural ),
		);

		$args = array(
			'labels' 			=> $labels,
			'show_tagcloud'		=> false,
			'show_admin_column' => true,
			'hierarchical'		=> true
		);

		register_taxonomy( $this->tax_slug, $this->cptslug, $args );

	}


	/**
	 * Remove the view link from the cpt if we have set hide_view to true.
	 *
	 * @param array $actions Array of all action links assigned to the cpt.
	 * @param object $post Post object of the post we're on.
	 * @return array Passed through actions array.
	 */
	public function remove_view_from_row( $actions, $post ){

	    if( $post->post_type === $this->cptslug ){
	        unset( $actions['inline hide-if-no-js'] );
	        unset( $actions['view'] );
	    }

	    return $actions;
	}


	/**
	 * Remove the permalink box from the cpt if we have set hide_view to true.
	 *
	 * @see get_sample_permalink_html
	 * @global object $post Post object.
	 *
	 * @param string $return Return HTML objects with action buttons & revised link.
	 * @param integer $id Post ID
	 * @param string $new_title New title
	 * @param string $new_slug New slug
	 * @return string $return Return HTML objects with action buttons & revised link.
	 */
	public function remove_permalink_option( $return, $id, $new_title, $new_slug ){
	    global $post;

	    if( !empty( $post ) && $post->post_type === $this->cptslug ){
	        return;
	    }

	    return $return;
	}


	/**
	 * Adds the cpt to our custom count seciont in the Right Now dashboard widget.
	 *
	 * @param array $cpt_array Passthrough objects to add.
	 * @return array $cpt_array Passthrough objects to add.
	 */
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