<?php
/**
 * General helper functionality.
 *
 * @package    WordPress
 * @subpackage evans
 */

namespace evans\Helpers;

/**
 * Add attributes to various script tags.
 *
 * @param string $tag    The complete script tag.
 * @param string $handle The script's handle.
 * @param string $src    The scripts source URL.
 * @return string        The updated script tag.
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
 * Add the site url to the bottom of every Gravity Form notification.
 *
 * @param array $notification Current notification information
 * @returns array Modified notification
 */
add_filter( 'gform_notification', function( $notification ) {
	$notification['message'] .= "\n<small> Sent from " . esc_html( site_url() ) . '</small>';
	return $notification;
}, 10, 1 );
