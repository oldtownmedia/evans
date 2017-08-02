<?php
namespace evans;

/**
 * Highlights
 *
 * Home highlights custom post type.
 *
 * @package    WordPress
 * @subpackage Evans
 */
final class Highlights extends CPT {
	protected $cptslug        = 'highlight';
	protected $cptslug_plural = 'highlights';
	protected $singular       = 'Home Highlight';
	protected $plural         = 'Home Highlights';
	protected $icon           = 'dashicons-archive';
	protected $hide_view      = true;
	protected $thumbnail_size = [
		'width'		=> 400,
		'height'	=> 400,
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
		'no_found_rows'  => true,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
		'posts_per_page' => 3,
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
		ob_start();

		$link		= get_post_meta( $pid, cmb_prefix( get_post_type() ) . 'url', true );
		$link_text	= get_post_meta( $pid, cmb_prefix( get_post_type() ) . 'url_text', true );
		$content	= get_post_meta( $pid, cmb_prefix( get_post_type() ) . 'content', true );
		$img_id		= get_post_meta( $pid, cmb_prefix( get_post_type() ) . 'image_id', true );
		$img		= wp_get_attachment_image_src( $img_id, get_post_type() . '-thumb' );
		?>

		<li class="<?php echo esc_attr( $this->cptslug );?>">

			<?php echo $this->get_img( $img, $link ); ?>

			<h3><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( get_the_title() );?></a></h3>

		<?php
		if ( ! empty( $content ) ) :
			 echo apply_filters( 'the_content', $content );
		endif;

		if ( ! empty( $link_text ) ) : ?>
			<a href="<?php echo esc_url( $link );?>" class='button' role='button'><?php echo esc_html( $link_text );?></a>
		<?php endif; ?>

		</li>

		<?php

		return ob_get_clean();
	}

	/**
	 * Add in array of custom metabox fields for use with CMB2.
	 */
	public function cmb_metaboxes() {
		// Setup the main CMB box
		$cmb = parent::cmb_metaboxes();

		$cmb->add_field( [
			'name' => __( 'Content', 'evans-mu' ),
			'desc' => sprintf( __( 'Enter any content that you would like to appear in the %s', 'evans-mu' ), $this->singular ),
			'id'   => $this->prefix . 'content',
			'type' => 'text',
		] );

		$cmb->add_field( [
			'name' => __( 'Link URL', 'evans-mu' ),
			'desc' => __( 'Enter the URL from the page you want to link to.', 'evans-mu' ),
			'id'   => $this->prefix . 'url',
			'type' => 'text_url',
		] );

		$cmb->add_field( [
			'name'    => __( 'Link Text', 'evans-mu' ),
			'desc'    => __( 'Enter text for the link.', 'evans-mu' ),
			'id'      => $this->prefix . 'url_text',
			'type'    => 'text',
			'default' => __( 'Read More', 'evans-mu' ),
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
$highlights = new Highlights;
$highlights->hooks();
