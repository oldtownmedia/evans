<?php
namespace evans;

/**
 * Define metabox prefix for entire site
 * will need to change if using Gravity Forms to post cmbs
 *
 * @param string $slug The appended id of the cmb key
 * @return string The full CMB key
 */
function cmb_prefix( $slug = '' ){

	$base = '_cmb2_';
	if ( $slug ) {
		$base .= $slug . '_';
	}

	return $base;
}


/**
 * Add possibility to asynchroniously load javascript files
 *
 * Filters all URL strings called in clean_url() for the  || #deferload value
 * and replaces said string with async='async' OR defer='defer'
 *
 * @param string $url The URL for the script resource.
 * @returns string Modified script string
 */
add_filter('clean_url', __NAMESPACE__ . '\add_loading_variables', 11, 1);
function add_loading_variables( $url ){

	// Catchall replace text in admin
	if ( is_admin() ){
		$url = str_replace( '#asyncload', '', $url );
		$url = str_replace( '#deferload', '', $url );
	}

	if ( ! is_admin() ){

		// Asyncload
	    if ( strpos( $url, '#asyncload' ) !== false ){
	        $url = trim( str_replace( '#asyncload', '', $url ) ) . "' async='async";
	    }

	    // Deferload
	    if ( strpos( $url, '#deferload' ) !== false ){
	        $url = trim( str_replace( '#deferload', '', $url ) ) . "' defer='defer";
	    }

    }

    return $url;

}


/**
 * Add possibility to Defer load javascript files
 *
 * Filters all URL strings called in clean_url() for the #deferload value
 * and replaces said string with defer='defer'
 *
 * @param string $src The URL for the script resource.
 * @returns string Modified script string
 */
add_filter( 'script_loader_src', __NAMESPACE__ . '\remove_script_version', 15, 1 );
add_filter( 'style_loader_src', __NAMESPACE__ . '\remove_script_version', 15, 1 );
function remove_script_version( $src ){

	$parts = explode( '?ver', $src );
	return $parts[0];

}


/**
 * Automatically creates custom messages for all post types
 *
 * @param string $messages Existing registered messaged, if any
 * @returns array Messages for the custom post type
 *
 * With thanks from: http://wp-bytes.com/function/2013/02/changing-post-updated-messages/
 */
add_filter( 'post_updated_messages', __NAMESPACE__ . '\set_messages' );
function set_messages( $messages ) {

	global $post, $post_ID;
	$post_type = get_post_type( $post_ID );

	$obj = get_post_type_object( $post_type );
	$singular = $obj->labels->singular_name;

	$messages[$post_type] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __( $singular.' updated. <a href="%s">View '.strtolower( $singular ).'</a>', 'evans-mu' ), esc_url( get_permalink( $post_ID ) ) ),
		2 => __( 'Custom field updated.', 'evans-mu' ),
		3 => __( 'Custom field deleted.', 'evans-mu' ),
		4 => __( $singular.' updated.', 'evans-mu' ),
		5 => isset( $_GET['revision'] ) ? sprintf( __( $singular.' restored to revision from %s', 'evans-mu' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __( $singular.' published. <a href="%s">View '.strtolower( $singular ).'</a>' , 'evans-mu' ), esc_url( get_permalink( $post_ID ) ) ),
		7 => __( 'Page saved.' ),
		8 => sprintf( __( $singular.' submitted. <a target="_blank" href="%s">Preview '.strtolower( $singular ).'</a>', 'evans-mu' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		9 => sprintf( __( $singular.' scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview '.strtolower( $singular ).'</a>', 'evans-mu' ), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
		10 => sprintf( __( $singular.' draft updated. <a target="_blank" href="%s">Preview '.strtolower( $singular ).'</a>', 'evans-mu' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
	);

	return $messages;

}


/**
 * Add the site url to the bottom of every Gravity Form notification.
 *
 * @param array $notification Current notification information
 * @returns array Modified notification
 */
add_filter( 'gform_notification', __NAMESPACE__ . '\add_siteurl_to_notifications', 10, 1 );
function add_siteurl_to_notifications( $notification ) {

    $notification['message'] .= "\n<small> Sent from ".site_url()."</small>";
    return $notification;

}
