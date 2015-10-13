<?php

class Partners extends CPT{

	protected $cptslug 			= 'partner';
	protected $cptslug_plural	= 'partners';
	protected $singular			= 'Partner';
	protected $plural			= 'Partners';
	protected $icon				= 'dashicons-star-filled';
	protected $hide_view 		= true;
	protected $thumbnail_size	= array(
		'width'		=> 200,
		'height'	=> 200
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
		'orderby' 		=> 'menu_order',
		'order' 		=> 'ASC',
		'quantity'		=> 500,
		'no_found_rows'	=> false
	);

	public function display_loop( $pid ){

		$html = "";

		$link	= get_post_meta( $pid, evans_cmb_prefix( $this->cptslug ) . 'url', true );
		$img_id	= get_post_meta( $pid, evans_cmb_prefix( $this->cptslug ) . 'image_id', true);
		$img	= wp_get_attachment_image_src( $img_id, $this->cptslug.'-thumb' );

		$html .= "<li class='".$this->cptslug."'>";

			if ( !empty( $link ) ){ $html .= "<a class='group' href='".esc_url( $link )."'>"; }

				if ( !empty( $img[0] ) ){ $html .= "<img src='$img[0]' alt='".get_the_title()."'>"; }

			if ( !empty( $link ) ){ $html .= "</a>"; }

		$html .= "</li>";

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
					'name' => __( 'Link URL', 'evans-mu' ),
					'desc' => __( 'Enter the URL from the page you want to link to.', 'evans-mu' ),
					'id'   => $prefix . 'url',
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

$partners = new Partners;
$partners->hooks();