<?php
/**
  * Plugin Name: Evans - the functionality framework for your site
  * Plugin URI: https://oldtownmediainc.com/
  * Description: Plugin to enable all kinds of features on your site.
  * Author: Old Town Media
  * Version: 2.0
  * Author URI: https://oldtownmediainc.com/
  * Text Domain: evans-mu
  * Namespace: evans
  */

// Define a list of subfolders to poke through for files
$dirs = [
	'abstracts',
	'admin',
	'assets',
	'modules',
	'widgets'
];

/*
 * Add site-setup folder to our smart loading ONLY if this folder exists.
 * This allows you to completely remove this folder with no repurcussions in production.
 2*/
if ( file_exists( WPMU_PLUGIN_DIR .'/site-setup' ) && is_dir( WPMU_PLUGIN_DIR . '/site-setup' ) ) {
	$dirs[] = 'site-setup';
}

/*
 * Loop through our directory array and require any PHP files without individual calls.
 */
foreach ( $dirs as $dir ) {
	foreach ( glob( WPMU_PLUGIN_DIR . "/$dir/*.php", GLOB_NOSORT ) as $filename ) {
	    require_once $filename;
	}
}

// @todo:: Use an autoloader
// @todo:: Use PSR-4 file/classnames
// @todo:: Add namespaces to everything
// @todo:: Put CMB in vendor folder
// @todo:: Put all files in one folder?
// @todo:: ob all the things
// @todo:: space all the arrays instead of tabs
