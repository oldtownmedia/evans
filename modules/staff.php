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
class Staff extends CPT{

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
		'quantity'		=> 500,
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
	public function display_loop( $pid ){

		$html = "";

		$img_id		= get_post_meta( $pid, cmb_prefix( $this->cptslug ) . 'image_id', true);
		$img		= wp_get_attachment_image_src( $img_id, $this->cptslug.'-thumb' );
		$title		= get_post_meta( $pid, cmb_prefix( $this->cptslug ) . 'position', true );
		$phone		= get_post_meta( $pid, cmb_prefix( $this->cptslug ) . 'phone', true );
		$email		= get_post_meta( $pid, cmb_prefix( $this->cptslug ) . 'email', true );

		$html .= "<li class='".$this->cptslug."' itemscope itemtype ='http://schema.org/Person'>";

			if ( !empty( $img[0] ) ){
				$html .= "<img src='$img[0]' itemprop='image' alt='".get_the_title()." photo'>";
			}

			$html .= "<h3 itemprop='name'>".get_the_title()."</h3>";

			if ( !empty( $title ) || !empty( $phone ) || !empty( $email ) ){ $html .= "<p>"; }

				if ( !empty( $title ) ){
					$html .= "<strong>" . __( 'Position:', 'evans-mu' ) . "</strong> <span itemprop='jobTitle'>".esc_attr( $title )."</span><br>";
				}

				if ( !empty( $phone ) ){
					$html .= "<strong>" . __( 'Phone:', 'evans-mu' ) . "</strong> <span itemprop='contactPoint'>".esc_attr( $phone )."</span><br>";
				}

				if ( !empty( $email ) && is_email( $email ) ){
					$html .= "<strong>" . __( 'Email:', 'evans-mu' ) . "</strong> <span itemprop='email'><a href='mailt:o".esc_attr( $email )."'>".esc_attr( $email )."</a></span><br>";
				}

			if ( !empty( $title ) || !empty( $phone ) || !empty( $email ) ){ $html .= "</p>"; }

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
					'name' => __( 'Position/Title', 'evans-mu' ),
					'desc' => __( 'Enter the title for the '.$this->cptslug, 'evans-mu' ),
					'id'   => $prefix . 'position',
					'type' => 'text',
				),
				array(
					'name' => __( 'Phone #', 'evans-mu' ),
					'desc' => __( 'Enter the phone # for the '.$this->cptslug, 'evans-mu' ),
					'id'   => $prefix . 'phone',
					'type' => 'text',
				),
				array(
					'name' => __( 'Email', 'evans-mu' ),
					'desc' => __( 'Enter the email for the '.$this->cptslug, 'evans-mu' ),
					'id'   => $prefix . 'email',
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


/*
 * Instantiate the hooks method
 */
$staff = new Staff;
$staff->hooks();