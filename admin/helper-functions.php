<?php
namespace evans;

// @todo:: fix the autoloading/deferring
// @todo:: break gform mod into its own file.

/**
 * Add possibility to asynchroniously load javascript files
 *
 * Filters all URL strings called in clean_url() for the  || #deferload value
 * and replaces said string with async='async' OR defer='defer'
 *
 * @param string $url The URL for the script resource.
 * @returns string Modified script string
 */
add_filter( 'clean_url', __NAMESPACE__ . '\add_loading_variables', 11, 1 );
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
 * Add the site url to the bottom of every Gravity Form notification.
 *
 * @param array $notification Current notification information
 * @returns array Modified notification
 */
add_filter( 'gform_notification', __NAMESPACE__ . '\add_siteurl_to_notifications', 10, 1 );
function add_siteurl_to_notifications( $notification ) {

    $notification['message'] .= "\n<small> Sent from " . esc_html( site_url() ) . "</small>";
    return $notification;

}
