<?php
namespace evans;

/**
 * Define metabox prefix for entire site.
 * Will need to change if using Gravity Forms to post cmbs.
 *
 * @param string $slug The appended id of the cmb key.
 * @return string The full CMB key
 */
function cmb_prefix( $slug = '' ) {
	$base = '_cmb2_';
	if ( $slug ) {
		$base .= $slug . '_';
	}

	return $base;
}

/**
 * Add attributes to various script tags.
 *
 * @param string $tag    The complete script tag.
 * @param string $handle The script's handle.
 * @param string $src    The scripts source URL.
 * @return string The updated script tag.
 */
add_filter( 'script_loader_tag', function( $tag, $handle, $src ) {
	$async_scripts = [];
	$defer_scripts = [];

	// Asynchronous scripts
	if ( in_array( $handle, $async_scripts, true ) ) {
		$tag = str_replace( ' src', ' async="async" src', $tag );
	}

	// Deferred scripts
	if ( in_array( $handle, $async_scripts, true ) ) {
		$tag = str_replace( ' src', ' defer="defer" src', $tag );
	}

	return $tag;
}, 10, 3 );

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
	global $post, $post_id;
	$post_type = get_post_type( $post_id );

	$obj = get_post_type_object( $post_type );
	$singular = $obj->labels->singular_name;

	$messages[ $post_type ] = [
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf(
			__( $singular . ' updated. <a href="%s">View ' . strtolower( $singular ) . '</a>', 'evans-mu' ),
			esc_url( get_permalink( $post_id ) )
		),
		2 => __( 'Custom field updated.', 'evans-mu' ),
		3 => __( 'Custom field deleted.', 'evans-mu' ),
		4 => __( $singular . ' updated.', 'evans-mu' ),
		5 => isset( $_GET['revision'] ) ? sprintf( __( $singular . ' restored to revision from %s', 'evans-mu' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf(
			__( $singular . ' published. <a href="%s">View ' . strtolower( $singular ) . '</a>' , 'evans-mu' ),
			esc_url( get_permalink( $post_id ) )
		),
		7 => __( 'Page saved.' ),
		8 => sprintf(
			__( $singular . ' submitted. <a target="_blank" href="%s">Preview ' . strtolower( $singular ) . '</a>', 'evans-mu' ),
			esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_id ) ) )
		),
		9 => sprintf(
			__( $singular . ' scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview ' . strtolower( $singular ) . '</a>', 'evans-mu' ),
			date_i18n( __( 'M j, Y @ G:i' ),
			strtotime( $post->post_date ) ),
			esc_url( get_permalink( $post_id ) )
		),
		10 => sprintf(
			__( $singular . ' draft updated. <a target="_blank" href="%s">Preview ' . strtolower( $singular ) . '</a>', 'evans-mu' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_id ) ) )
		),
	];

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
	$notification['message'] .= "\n<small> Sent from " . esc_html( site_url() ) . '</small>';
	return $notification;
}
