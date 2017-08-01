<?php
namespace evans;

/**
 * Class to generate custom post types using only a few variables.
 *
 * @abstract
 * @package    WordPress
 * @subpackage Evans
 * @author     Old Town Media
 */
abstract class CPT {

	/**
	 * Slug ID for the cpt.
	 *
	 * @var string
	 */
	protected $cptslug;

	/**
	 * Plural slug ID for the cpt.
	 *
	 * @var string
	 */
	protected $cptslug_plural;

	/**
	 * Singular string used for messages/labels.
	 *
	 * @var string
	 */
	protected $singular;

	/**
	 * Plural string used for messages/labels.
	 *
	 * @var mixed
	 */
	protected $plural;

	/**
	 * Array of arguments used in register_custom_post_type.
	 * Generally [ exclude_from_search, show_in_nav_menus, publicly_queryable, supports, has_archive ]
	 *
	 * @var array
	 */
	protected $cpt_args;

	/**
	 * Icon ID from Dashicons for the cpt icon in the admin nav menu.
	 * https://developer.wordpress.org/resource/dashicons/
	 *
	 * @var string
	 */
	protected $icon;

	/**
	 * Choose whether or not you want to hide the "View {cptslug}" on the admin side.
	 * Used in cases where the cpt doesn't have a single on the front end.
	 *
	 * @var boolean
	 */
	protected $hide_view;

	/**
	 * Array of arguments used in the WP_Query loop.
	 * Generally [ orderby, order, quantity ]
	 *
	 * @var array
	 */
	protected $loop_args;

	/**
	 * Array of arguments used if you'd like to create a custom thumbnail size
	 * for images used on the front end by the cpt.
	 * Accepted args: [ width, height ]
	 *
	 * @var array
	 */
	protected $thumbnail_size;

	/**
	 * Slug ID used by a taxonomy if you want one.
	 *
	 * @var string
	 */
	protected $tax_slug;

	/**
	 * Singular pretty name used by messages/labels
	 *
	 * @var mixed
	 */
	protected $taxonomy_name;

	/**
	 * Plural pretty name used by messages/labels
	 *
	 * @var mixed
	 */
	protected $taxonomy_plural;

	/**
	 * Prefix string used by all metaboxes
	 *
	 * @var string
	 */
	protected $prefix;


	/**
	 * Build our prefix variable in construct instead of hooks
	 */
	public function __construct() {
		// Define our prefix for metaboxes
		$this->prefix = cmb_prefix( $this->cptslug );
	}

	/**
	 * Hooks function to fire off the events we need.
	 */
	public function hooks() {
		add_action( 'init', [ $this, 'define_cpt' ] );
		add_filter( 'cmb2_admin_init', [ $this, 'cmb_metaboxes' ] );
		add_filter( 'cpt_array_filter', [ $this, 'dashboard_cpt_loop' ], 10 );
		add_shortcode( $this->cptslug_plural, [ $this, 'shortcode' ] );

		// If fed dimensions for a thumbnails
		if ( ! empty( $this->thumbnail_size ) ) {
			add_image_size( $this->cptslug . '-thumb', $this->thumbnail_size['width'], $this->thumbnail_size['height'], true );
		}

		// If we want to use the taxonomy, hook in the taxonomy functions
		if ( ! empty( $this->taxonomy_name ) ) {
			add_action( 'init', [ $this, 'define_taxonomy' ], 0 );
		}

		// If we don't want links to the single to appear in the admin section
		if ( true === $this->hide_view ) {
			add_filter( 'post_row_actions', [ $this, 'remove_view_from_row' ], 10, 2 );
			add_filter( 'page_row_actions', [ $this, 'remove_view_from_row' ], 10, 2 );	// In case post is hierarchical
			add_filter( 'get_sample_permalink_html', [ $this, 'remove_permalink_option' ], '', 1 );
		}
	}

	/**
	 * Loop through custom post type and return combined HTML from posts.
	 *
	 * @param array $args Query arguments.
	 * @return string Combined HTML contents of the looped query.
	 */
	public function loop_cpt( $args = [] ) {
		ob_start();

		$objects = new \WP_Query( $this->query_mods( [], $args ) );

		if ( $objects->have_posts() ) { ?>
			<ul class="<?php echo esc_attr( $this->cptslug ); ?>-listing">

				<?php	while ( $objects->have_posts() ) : $objects->the_post();

					$this->display_single( get_the_id() );

				endwhile; ?>

			</ul> <?php
		} else { ?>
			<h3><?php sprintf( esc_html__('There are no %s to list. Check back soon!', 'evans-mu' ), $this->cptslug_plural ); ?></h3>

		<?php }

		wp_reset_postdata();

		return ob_get_clean();
	}

	/**
	 * Perform query modifications without touching our loop function.
	 *
	 * @param array $original_query Original array to parse.
	 * @param array $args Incoming arguments.
	 * @return string Modified query arguments.
	 */
	public function query_mods( $original_query, $args ) {
		// Pull in the defaults we set in the variables
		$defaults = $this->loop_args;

		$query = wp_parse_args( $args, $defaults );

		// Set the post type because it's kind of important
		$query['post_type']	= $this->cptslug;

		// Merge in a passed array with our default arguments
		$query = array_merge( $original_query, $query );

		// If our shortcode passed in the random as true
		if ( ! empty( $args['random'] ) && $args['random'] == true ) {
			$query['no_found_rows']  = false;
			$query['posts_per_page'] = 1;
			$query['orderby']        = 'rand';
		}

		// If our shortcode passed in an id
		if ( ! empty( $args['pid'] ) ) {
			$query['no_found_rows']	 = false;
			$query['posts_per_page'] = 1;
			$query['post__in']       = [ $args['pid'] ];
		}

		// If our shortcode passed in a group id OR our taxonomy_loop passes in a group id
		if ( ! empty( $args['group'] ) ) {
			$query[ $this->tax_slug ] = [ $args['group'] ];
		}

		return $query;
	}

	/**
	 * Display a single item from the queried posts.
	 *
	 * This is the most often-overridden function and will often contain CMB
	 * calls and custom display HTML.
	 *
	 * @param int $pid Post ID.
	 * @return string HTML contents for the individual post.
	 */
	public function display_single( $pid ) {
		ob_start(); ?>

		<li class="<?php esc_attr( $this->cptslug ); ?>">

			<h3><?php esc_html( get_the_title( $pid )); ?></h3>

			<?php apply_filters( 'the_content', get_the_content() ); ?>

		</li>

		<?php return ob_get_clean();
	}

	/**
	 * Assemble the HTML for an img tag to display within a loop.
	 *
	 * @param object $img Attachment source object
	 * @param string $link Link to put around the img. Optional.
	 * @return string HTML contents for the image and link.
	 */
	protected function get_img( $img, $link = '' ) {
		ob_start();

		if ( empty( $img ) ) {
			return;
		}

		if ( ! empty( $link ) ) { ?>
				<a href="<?php echo esc_url( $link ); ?>"></a>;
		<?php } ?>

			<img src="<?php echo esc_url($img[0]); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>"/>

		<?php
		if ( ! empty( $link ) ) { ?>
			</a>
		<?php }

		return ob_get_clean();
	}

	/**
	 * Loop through a taxonomy.
	 *
	 * This function will loop through all items in a taxonomy and call loop_cpt
	 * on each and every one.
	 *
	 * @param array $args Arguments to be passed to get_terms.
	 * @return string HTML content of the looped taxonomy & posts.
	 */
	public function taxonomy_loop( $args = [] ) {
		ob_start();

		$terms = get_terms(
			$this->tax_slug,
			[
				'hide_empty' => true,
				'number'     => 500,
			]
		);

		if ( ! empty( $terms ) ) {

			foreach ( $terms as $term ) :
				?>
				<h2><?php echo esc_html( apply_filters( 'the_title', $term->name ) ); ?><h2>
				<?php
				$description = term_description( $term->term_id, $this->tax_slug );

				if ( ! empty( $description ) ) {
					apply_filters( 'the_content', $description );
				}

				// Add the group we're querying to the get_cpt arguments
				$args['group'] = $term->slug;
				$this->loop_cpt( $args );

			endforeach;

		}

		return ob_get_clean();
	}

	/**
	 * Shortcode definition to call a loop of the cpt within the content.
	 *
	 * @param array $atts arguments to be passed through to the loop.
	 * @return string HTML content of the looped query and posts.
	 */
	public function shortcode( $atts ) {
		$atts = shortcode_atts(
			[
				'quantity' => '',
				'group'    => '',
				'id'       => '',
				'random'   => false,
			],
			$atts
		);

		$args = [
			'quantity' => $atts['quantity'],
			'group'    => $atts['group'],
			'random'   => $atts['random'],
			'pid'      => $atts['id'],
		];

		return $this->loop_cpt( $args );
	}

	/**
	 * Custom Post Type definition.
	 *
	 * We're actually creating out custom post type here and passing in custom
	 * arguments if we have any. Several defaults include supports of title & editor,
	 * public, menu position, etc.
	 */
	public function define_cpt() {
		$labels = [
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
			'menu_name'          => sprintf( __( '%s', 'evans-mu' ), $this->plural ),
		];

		$defaults = [
			'labels'        => $labels,
			'public'        => true,
			'menu_position' => 7,
			'menu_icon'	    => $this->icon,
			'rewrite'       => false,
			'hierarchical'  => true,
			'supports'      => [ 'title', 'editor' ],
		];

		$args = wp_parse_args( $this->cpt_args, $defaults );

		register_post_type( $this->cptslug, $args );
	}

	/**
	 * Placeholder for setting metaboxes using the CMB2 library.
	 *
	 * @return object Passthrough of all metaboxes.
	 */
	public function cmb_metaboxes() {
		$cmb = new_cmb2_box( [
			'id'           => $this->cptslug . '_metabox',
			'title'        => sprintf( __( '%s Information', 'evans-mu' ), $this->singular ),
			'object_types' => [ $this->cptslug ],
			'context'      => 'normal',
			'priority'     => 'high',
			'show_names'   => true,
		] );

		return $cmb;
	}

	/**
	 * Taxonomy definition if we have set the proper variables at the beginning.
	 */
	public function define_taxonomy() {
		$labels = [
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
		];

		$args = [
			'labels'            => $labels,
			'show_tagcloud'     => false,
			'show_admin_column' => true,
			'hierarchical'      => true,
		];

		register_taxonomy( $this->tax_slug, $this->cptslug, $args );
	}

	/**
	 * Remove the view link from the cpt if we have set hide_view to true.
	 *
	 * @param array $actions Array of all action links assigned to the cpt.
	 * @param object $post Post object of the post we're on.
	 * @return array Passed through actions array.
	 */
	public function remove_view_from_row( $actions, $post ) {

		if ( $post->post_type === $this->cptslug ) {
			unset( $actions['inline hide-if-no-js'] );
			unset( $actions['view'] );
		}

		return $actions;
	}

	/**
	 * Remove the permalink box from the cpt if we have set hide_view to true.
	 *
	 * @global object $post Post object.
	 *
	 * @param string $return Return HTML objects with action buttons & revised link.
	 * @return string $return Return HTML objects with action buttons & revised link.
	 */
	public function remove_permalink_option( $return ) {
		global $post;

		if ( ! empty( $post ) && $post->post_type === $this->cptslug ) {
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
	public function dashboard_cpt_loop( $cpt_array ) {
		$cpt_array[] = [
			'slug'     => $this->cptslug,
			'singular' => $this->singular,
			'plural'   => $this->plural,
			'class'    => $this->icon,
		];

		return $cpt_array;
	}
}
