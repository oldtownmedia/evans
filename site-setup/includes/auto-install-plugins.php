<?php
/**
 * This file represents an example of the code that themes would use to register
 * the required plugins.
 *
 * It is expected that theme authors would copy and paste this code into their
 * functions.php file, and amend to suit.
 *
 * @see http://tgmpluginactivation.com/configuration/ for detailed documentation.
 *
 * @package    TGM-Plugin-Activation
 * @subpackage Example
 * @version    2.5.2
 * @author     Thomas Griffin, Gary Jones, Juliette Reinders Folmer
 * @copyright  Copyright (c) 2011, Thomas Griffin
 * @license    http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link       https://github.com/TGMPA/TGM-Plugin-Activation
 */

/**
 * Include the TGM_Plugin_Activation class.
 */
require_once dirname( __FILE__ ) . '/class-tgm-plugin-activation.php';

add_action( 'tgmpa_register', 'my_theme_register_required_plugins' );
/**
 * Register the required plugins for this theme.
 *
 * In this example, we register five plugins:
 * - one included with the TGMPA library
 * - two from an external source, one from an arbitrary source, one from a GitHub repository
 * - two from the .org repo, where one demonstrates the use of the `is_callable` argument
 *
 * The variable passed to tgmpa_register_plugins() should be an array of plugin
 * arrays.
 *
 * This function is hooked into tgmpa_init, which is fired within the
 * TGM_Plugin_Activation class constructor.
 */
function my_theme_register_required_plugins() {

	/*
	 * Array of plugin arrays. Required keys are name and slug.
	 * If the source is NOT from the .org repo, then source is also required.
	 */
	$plugins = array(

		array(
			'name' 		=> 'ARYO Activity Log',
			'slug' 		=> 'aryo-activity-log',
			'required' 	=> true,
		),
		array(
			'name' 		=> 'Crop Thumbnails',
			'slug' 		=> 'crop-thumbnails',
			'required' 	=> true,
		),
		array(
			'name' 		=> 'Custom sidebars',
			'slug' 		=> 'custom-sidebars',
			'required' 	=> true,
		),
		array(
			'name' 		=> 'Google Analytics for WordPress',
			'slug' 		=> 'google-analytics-for-wordpress',
			'required' 	=> false,
		),
		array(
			'name' 		=> 'imsanity',
			'slug' 		=> 'imsanity',
			'required' 	=> true,
		),
		array(
			'name' 		=> 'iThemes Security',
			'slug' 		=> 'better-wp-security',
			'required' 	=> false,
		),
		array(
			'name' 		=> 'Simple 301 Redirects',
			'slug' 		=> 'simple-301-redirects',
			'required' 	=> false,
		),
		array(
			'name' 		=> 'Simple Frontend Template Display',
			'slug' 		=> 'simple-frontend-template-display',
			'required' 	=> true,
		),
		array(
			'name' 		=> 'Simple Image Widget',
			'slug' 		=> 'simple-image-widget',
			'required' 	=> true,
		),
		array(
			'name' 		=> 'Simple Google Maps Short Code',
			'slug' 		=> 'simple-google-maps-short-code',
			'required' 	=> true,
		),
		array(
			'name' 		=> 'Simple Page Ordering',
			'slug' 		=> 'simple-page-ordering',
			'required' 	=> true,
		),
		array(
			'name' 		=> 'WordPress SEO by Yoast',
			'slug' 		=> 'wordpress-seo',
			'required' 	=> true,
		),
		array(
			'name' 		=> 'The WP Remote WordPress Plugin',
			'slug' 		=> 'wpremote',
			'required' 	=> true,
		)
	);

	// If we have a path for premium plugins - include here
	if ( function_exists( 'premium_plugins_path' ) ){

		$premium_plugins = array(
			// Premium Plugins
			array(
				'name' 		=> 'Backup Buddy',
				'slug' 		=> 'backupbuddy',
				'source'	=> premium_plugins_path() . '/backupbuddy.zip',
				'required' 	=> true,
			),
			array(
				'name' 		=> 'Gravity Forms',
				'slug' 		=> 'gravityforms',
				'source'	=> premium_plugins_path() . '/gravityforms.zip',
				'required' 	=> true,
			),
			array(
				'name' 		=> 'Soliloquy',
				'slug' 		=> 'soliloquy',
				'source'	=> premium_plugins_path() . '/soliloquy.zip',
				'required' 	=> true,
			),
			array(
				'name' 		=> 'Wp Rocket',
				'slug' 		=> 'wp-rocket',
				'source'	=> premium_plugins_path() . '/wp-rocket.zip',
				'required' 	=> false,
			),
			array(
				'name' 		=> 'Wp Sent Mail',
				'slug' 		=> 'wp-sent-mail',
				'source'	=> premium_plugins_path() . '/wp-sent-mail.zip',
				'required' 	=> true,
			)
		);

		$plugins = array_merge( $plugins, $premium_plugins );

	}

	/*
	 * Array of configuration settings. Amend each line as needed.
	 *
	 * TGMPA will start providing localized text strings soon. If you already have translations of our standard
	 * strings available, please help us make TGMPA even better by giving us access to these translations or by
	 * sending in a pull-request with .po file(s) with the translations.
	 *
	 * Only uncomment the strings in the config array if you want to customize the strings.
	 */
	$config = array(
		'id'           => 'tgmpa',                 // Unique ID for hashing notices for multiple instances of TGMPA.
		'default_path' => '',                      // Default absolute path to bundled plugins.
		'menu'         => 'tgmpa-install-plugins', // Menu slug.
		'parent_slug'  => 'tools.php',            // Parent menu slug.
		'capability'   => 'edit_theme_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
		'has_notices'  => true,                    // Show admin notices or not.
		'dismissable'  => false,                    // If false, a user cannot dismiss the nag message.
		'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
		'is_automatic' => false,                   // Automatically activate plugins after installation or not.
		'message'      => '',                      // Message to output right before the plugins table.
	);

	tgmpa( $plugins, $config );
}