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
final class Alerts extends CPT {
	protected $cptslug        = 'alert';
	protected $cptslug_plural = 'alerts';
	protected $singular       = 'Alert';
	protected $plural         = 'Alerts';
	protected $icon           = 'dashicons-megaphone';
	protected $hide_view      = true;

	// Arguments to define the CPT
	protected $cpt_args	= [
		'exclude_from_search' => true,
		'show_in_nav_menus'   => false,
		'publicly_queryable'  => false,
		'has_archive'         => false,
	];

	// Arguments for the CPT loop
	protected $loop_args = [
		'no_found_rows'  => true,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
		'posts_per_page' => 1,
		'no_found_rows'	 => true,
	];

	/**
	 * Loop through custom post type and return combined HTML from posts.
	 *
	 * @param array $args Description.
	 * @return string Combined HTML contents of the looped query.
	 */
	public function loop_cpt( $args = [] ) {
		ob_start();

		$objects = new \WP_Query( $this->query_mods( [], $args ) );

		if ( $objects->have_posts() ) :

			while ( $objects->have_posts() ) : $objects->the_post();

				echo $this->display_single( get_the_id() );

			endwhile;

		endif;

		wp_reset_postdata();

		return ob_get_clean();
	}

	/**
	 * Perform query modifications without touching our loop function.
	 *
	 * @param array $query Set query arguments.
	 * @param array $args Incoming arguments.
	 * @return string Modified query arguments.
	 */
	public function query_mods( $query, $args ) {
		$query['meta_query'] = [
			'relation' => 'OR',
			[
				[
					'key'     => $this->prefix . 'active',
					'value'   => 'active',
					'compare' => '=',
				],
				[
					'key'     => $this->prefix . 'start_date',
					'compare' => 'NOT EXISTS',
				],
			],
			[
				[
					'key'     => $this->prefix . 'active',
					'value'   => 'active',
					'compare' => '=',
				],
				[
					'key'     => $this->prefix . 'start_date',
					'value'   => time(),
					'compare' => '<=',
					'type'    => 'char',
				],
			],
		];

		return parent::query_mods( $query, $args );
	}

	/**
	 * Display a single item from the queried posts.
	 *
	 * This is the most often-overridden function and will often contain CMB
	 * calls and custom display HTML.
	 *
	 * @param int $ Post ID.
	 * @return string HTML contents for the individual post.
	 */
	public function display_single( $pid ) {
		$end = get_post_meta( $pid, cmb_prefix( get_post_type() ) . 'end_date', true );

		ob_start();
		if ( ! $end || time() <= $end ) :
			?>
			<div class='<?php echo esc_attr( $this->cptslug ); ?>'>
				<h4><?php echo esc_html( get_the_title() ); ?></h4>
				<?php echo apply_filters( 'the_content', get_the_content() ); ?>
			</div>
			<?php
		endif;

		return ob_get_clean();
	}

	/**
	 * Add in array of custom metabox fields for use with CMB2.
	 */
	public function cmb_metaboxes() {
		// Setup the main CMB box
		$cmb = parent::cmb_metaboxes();

		$cmb->add_field( [
			'name'    => __( 'Active?', 'evans-mu' ),
			'desc'    => sprintf( __( 'Choose whether this %s should be active or not', 'evans-mu' ), $this->cptslug ),
			'id'      => $this->prefix . 'active',
			'type'    => 'radio',
			'options' => [
				'active'   => __( 'Active', 'evans-mu' ),
				'inactive' => __( 'Inactive', 'evans-mu' ),
			],
		] );

		$cmb->add_field( [
			'name'    => __( 'Start Date', 'evans-mu' ),
			'desc'    => sprintf( __( 'If you would like to schedule this %s, enter a start date.', 'evans-mu' ), $this->cptslug ),
			'id'      => $this->prefix . 'start_date',
			'type'    => 'text_datetime_timestamp',
			'default' => time(),
		] );

		$cmb->add_field( [
			'name' => __( 'End Date', 'evans-mu' ),
			'desc' => sprintf( __( 'If you would like to schedule this %s, enter an end date.', 'evans-mu' ), $this->cptslug ),
			'id'   => $this->prefix . 'end_date',
			'type' => 'text_datetime_timestamp',
		] );
	}
}

/*
 * Instantiate the hooks method
 */
$alerts = new Alerts;
$alerts->hooks();
