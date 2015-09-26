<?php

/**
 * Define metabox prefix for entire site - will need to change if using Gravity Forms to post cmbs
 *
 * @param string $slug The appended id of the cmb key
 * @return string The full CMB key
 */
function otm_cmb_prefix( $slug = '' ){

	$base = '_cmb2_';
	if ( $slug ) {
		$base .= $slug . '_';
	}

	return $base;
}