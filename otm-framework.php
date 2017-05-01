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
	'admin',
	'admin/abstracts',
	'admin/widgets',
];

/*
 * Loop through our directory array and require any PHP files without individual calls.
 */
foreach ( $dirs as $dir ) {
	foreach ( glob( WPMU_PLUGIN_DIR . "/$dir/*.php", GLOB_NOSORT ) as $filename ) {
		require_once $filename;
	}
}

evans\CleanAdmin\setup();
evans\DisableEmojis\setup();
evans\Security\setup();

// @todo:: Use an autoloader
// @todo:: Use PSR-4 file/classnames
// @todo:: Add namespaces to everything
// @todo:: Put abstracts into their own folder
// @todo:: Put CMB in vendor folder
// @todo:: Put all files in one folder?
