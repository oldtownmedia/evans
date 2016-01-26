<?php
namespace evans;

/**
 * Highlights
 *
 * Home highlights custom post type.
 *
 * @package    WordPress
 * @subpackage Evans
 * @author     Old Town Media
 */
class Highlights extends CPT{

	protected $cptslug 			= 'highlight';
	protected $cptslug_plural	= 'highlights';
	protected $singular			= 'Home Highlight';
	protected $plural			= 'Home Highlights';
	protected $icon				= 'dashicons-archive';
	protected $hide_view		= true;
	protected $thumbnail_size	= array(
		'width'		=> 400,
		'height'	=> 400
	);

	// Arguments to define the CPT
	protected $cpt_args			= array(
		'exclude_from_search'	=> true,
		'show_in_nav_menus'		=> false,
		'publicly_queryable'	=> false,
		'supports'      		=> array( 'title' ),
		'has_archive'   		=> false,
	);

	// Arguments for the CPT loop
	protected $loop_args = array(
		'no_found_rows'	=> true,
		'orderby' 		=> 'menu_order',
		'order' 		=> 'ASC',
		'posts_per_page'=> 3,
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

		$link		= get_post_meta( $pid, cmb_prefix( get_post_type() ) . 'url', true );
		$link_text	= get_post_meta( $pid, cmb_prefix( get_post_type() ) . 'url_text', true );
		$content	= get_post_meta( $pid, cmb_prefix( get_post_type() ) . 'content', true );
		$img_id		= get_post_meta( $pid, cmb_prefix( get_post_type() ) . 'image_id', true);
		$img		= wp_get_attachment_image_src( $img_id, get_post_type().'-thumb' );

		$html .= "<li class='".$this->cptslug."'>";

			if ( !empty( $img[0] ) ){
				$html .= "<a href='".esc_url( $link )."'><img src='$img[0]' alt='".get_the_title()."'></a>";
			}

			$html .= "<h3><a href='".esc_url( $link )."'>".get_the_title()."</a></h3>";

			if ( !empty( $content ) ){
				$html .=  apply_filters( 'the_content', $content );
			}

			if ( !empty( $link_text ) ){
				$html .= "<a href='".esc_url( $link )."' class='button' role='button'>".esc_attr( $link_text )."</a>";
			}

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
					'name' => __( 'Content', 'evans-mu' ),
					'desc' => __( 'Enter any content that you would like to appear in the '.$this->singular, 'evans-mu' ),
					'id'   => $prefix . 'content',
					'type' => 'text',
				),
				array(
					'name' => __( 'Link URL', 'evans-mu' ),
					'desc' => __( 'Enter the URL from the page you want to link to.', 'evans-mu' ),
					'id'   => $prefix . 'url',
					'type' => 'text_url',
				),
				array(
					'name' => __( 'Link Text', 'evans-mu' ),
					'desc' => __( 'Enter text for the link.', 'evans-mu' ),
					'id'   => $prefix . 'url_text',
					'type' => 'text',
					'default' => __( 'Read More', 'evans-mu' )
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
$highlights = new Highlights;
$highlights->hooks();