<?php
/**
  * Plugin Name: Longs - the functionality framework for your site
  * Plugin URI: http://oldtownmediainc.com/
  * Description: Plugin to enable all kinds of features on your site.
  * Author: Old Town Media
  * Version: 2.0
  * Author URI: http://oldtownmediainc.com/
  * Text Domain: otm-mu
  */

// Define a list of subfolders to poke through for files
$dirs = array(
	'admin',
	'resources',
	'modules',
	'widgets'
);

if ( file_exists( WPMU_PLUGIN_DIR .'/site-setup' ) && is_dir( WPMU_PLUGIN_DIR . '/site-setup' ) ) {
	$dirs[] = 'site-setup';
}

// Loop through our directory array and require any PHP files directly down
foreach ( $dirs as $dir ){
	foreach ( glob( WPMU_PLUGIN_DIR . "/$dir/*.php", GLOB_NOSORT ) as $filename ){
	    require_once $filename;
	}
}