<?php
namespace evans\Security;

/**
 * Hooks function to fire off the events we need.
 */
function setup() {
	// Couple of public functions
	add_filter( 'xmlrpc_enabled', '__return_false' );	// Disable xmlrpc

	// This is really the wrong place to be doing this,
	// BUT let's catch all and make sure we're not missing anything.
	if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
		define( 'DISALLOW_FILE_EDIT', true );
	}
}
