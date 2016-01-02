<?php

/**
 * Class to make external references to internally-namespaced functionality eaiser & quicker.
 *
 * @abstract
 * @package    WordPress
 * @subpackage Evans
 * @author     Old Town Media
 */
class Evans{


	/**
	 * Call the custom post type class as a function.
	 *
	 * @param string $class Classname for a custom post type.
	 * @return object Class object.
	 */
	public static function cpt( $class ){

		if ( empty( $class ) ){
			return;
		}

		$class_name = "evans\$class";
		return new $class_name;

	}


	/**
	 * Retrive a custom metabox using our inbuilt naming function.
	 *
	 * @see cmb_prefix, get_post_meta
	 *
	 * @param string $slug CMB tail ID.
	 * @param int $pid Optional. Post ID.
	 * @param string $post_type Optional. Post Type identifier.
	 * @return mixed Description.
	 */
	public static function cmb( $slug, $pid = '', $post_type = '' ){

		if ( empty( $slug ) ){
			return;
		}

		if ( empty( $pid ) ){
			$pid = get_the_id();
		}

		if ( empty( $post_type ) ){
			$post_type = get_post_type();
		}

		return get_post_meta( $pid, evans\cmb_prefix( $post_type ) . $slug, true );

	}


	/**
	 * Retrieve image URL with the properly-sized thumbnail.
	 *
	 * @see get_post_meta, wp_get_attachment_image_src
	 *
	 * @param string $slug Optional. CMB tail ID.
	 * @param int $pid Optional. Post ID.
	 * @param string $post_type Optional. Post Type identifier.
	 * @return string Image URL.
	 */
	public static function thumbnail( $slug = 'image_id', $pid = '', $post_type = '' ){

		if ( empty( $pid ) ){
			$pid = get_the_id();
		}

		if ( empty( $post_type ) ){
			$post_type = get_post_type();
		}

		$img_id		= get_post_meta( $pid, cmb_prefix( $post_type ) . $slug, true);
		$img		= wp_get_attachment_image_src( $img_id, $post_type.'-thumb' );

		return $img[0];

	}

}