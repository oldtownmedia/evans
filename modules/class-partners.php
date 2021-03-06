<?php
namespace evans;

/**
 * Partners
 *
 * Partners custom post type.
 *
 * @package    WordPress
 * @subpackage Evans
 */
final class Partners extends CPT {
	protected $cptslug        = 'partner';
	protected $cptslug_plural = 'partners';
	protected $singular       = 'Partner';
	protected $plural         = 'Partners';
	protected $icon           = 'dashicons-star-filled';
	protected $hide_view      = true;
	protected $thumbnail_size = [
		'width'		=> 200,
		'height'	=> 200,
	];

	// Arguments to define the CPT
	protected $cpt_args = [
		'exclude_from_search' => true,
		'show_in_nav_menus'   => false,
		'publicly_queryable'  => false,
		'supports'            => [ 'title' ],
		'has_archive'         => false,
	];

	// Arguments for the CPT loop
	protected $loop_args = [
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
		'posts_per_page' => 100,
		'no_found_rows'  => false,
	];

	/**
	 * Display a single item from the queried posts.
	 *
	 * This is the most often-overrideen function and will often contain CMB
	 * calls and custom display HTML.
	 *
	 * @param int $ Post ID.
	 * @return string HTML contents for the individual post.
	 */
	public function display_single( $pid ) {
		$html = '';

		$link	= get_post_meta( $pid, cmb_prefix( get_post_type() ) . 'url', true );
		$img_id	= get_post_meta( $pid, cmb_prefix( get_post_type() ) . 'image_id', true );
		$img	= wp_get_attachment_image_src( $img_id, get_post_type() . '-thumb' );

		$html .= "<li class='" . esc_attr( $this->cptslug ) . "'>";

			$html .= $this->get_img( $img, $link );

		$html .= '</li>';

		return $html;
	}

	/**
	 * Add in array of custom metabox fields for use with CMB2.
	 */
	public function cmb_metaboxes() {
		// Setup the main CMB box
		$cmb = parent::cmb_metaboxes();

		$cmb->add_field( [
			'name' => __( 'Link URL', 'evans-mu' ),
			'desc' => __( 'Enter the URL from the page you want to link to.', 'evans-mu' ),
			'id'   => $this->prefix . 'url',
			'type' => 'text',
		] );

		$cmb->add_field( [
			'name'  => __( 'Image', 'evans-mu' ),
			'id'    => $this->prefix . 'image',
			'type'  => 'file',
			'allow' => [ 'attachment' ],
		] );
	}
}

/*
 * Instantiate the hooks method
 */
$partners = new Partners;
$partners->hooks();
