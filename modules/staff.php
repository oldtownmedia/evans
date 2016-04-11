<?php
namespace evans;

/**
 * Staff
 *
 * Staff custom post type.
 *
 * @package    WordPress
 * @subpackage Evans
 * @author     Old Town Media
 */
final class Staff extends CPT{

	protected $cptslug 			= 'staff';
	protected $cptslug_plural	= 'staff';
	protected $singular			= 'Staff';
	protected $plural			= 'Staff';
	protected $icon				= 'dashicons-businessman';
	protected $hide_view 		= false;
	protected $thumbnail_size	= array(
		'width'		=> 300,
		'height'	=> 300
	);

	// Arguments to define the CPT
	protected $cpt_args			= array(
		'rewrite'				=> true
	);

	// Arguments for the CPT loop
	protected $loop_args = array(
		'orderby' 		=> 'menu_order',
		'order' 		=> 'ASC',
		'posts_per_page'=> 500,
		'no_found_rows'	=> false
	);


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

		$img_id		= get_post_meta( $pid, cmb_prefix( get_post_type() ) . 'image_id', true);
		$img		= wp_get_attachment_image_src( $img_id, get_post_type().'-thumb' );
		$title		= get_post_meta( $pid, cmb_prefix( get_post_type() ) . 'position', true );

		$html .= "<li class='".$this->cptslug."' itemscope itemtype ='http://schema.org/Person'>";

			$html .= $this->get_img( $img );

			$html .= "<h3 itemprop='name'>".esc_attr( get_the_title() )."</h3>";


			if ( !empty( $title ) ){
				$html .= "<p><strong>" . __( 'Position:', 'evans-mu' ) . "</strong> <span itemprop='jobTitle'>".esc_attr( $title )."</span></p>";
			}


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
	public function cmb_metaboxes() {

		// Setup the main CMB box
		$cmb = parent::cmb_metaboxes();

		$cmb->add_field( array(
			'name' => __( 'Position/Title', 'evans-mu' ),
			'desc' => __( 'Enter the title for the '.$this->cptslug, 'evans-mu' ),
			'id'   => $this->prefix . 'position',
			'type' => 'text'
	    ) );

		$cmb->add_field( array(
			'name' => __( 'Image', 'evans-mu' ),
			'id'   => $this->prefix . 'image',
			'type' => 'file',
			'allow' => array( 'attachment' )
		) );

	}

}


/*
 * Instantiate the hooks method
 */
$staff = new Staff;
$staff->hooks();
