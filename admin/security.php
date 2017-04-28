<?php
namespace evans\Security;

/**
 * Hooks function to fire off the events we need.
 */
function setup() {
	// Couple of public functions
	add_filter( 'xmlrpc_enabled', '__return_false' );	// Disable xmlrpc

	// @todo:: disable all editor access.
}
