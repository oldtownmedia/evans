<?php
defined( 'ABSPATH' ) OR exit;

/**
 * Initial Settings
 *
 * Sets the WordPress site up with the proper settings to save time on install
 *
 * @package WordPress
 * @category mu_plugin
 * @author Old Town Media
 * @since 0.0.0
 */
class Initial_Settings{

	/**
	 * Constructor function.
	 *
	 * @access public
	 * @since 0.0.0
	 * @return void
	 */
	function __construct() {

		add_action( 'admin_init', array( $this, 'set_home_page' ), 105 );
		add_action( 'admin_init', array( $this, 'modify_permalinks' ), 105 );
		add_action( 'admin_init', array( $this, 'create_menus' ), 105);
		add_filter( 'admin_init', array( $this, 'update_settings' ), 105 );

	}

	/**
	 * Set the home page
	 *
	 * @access public
	 * @since 1.0.0
	 */
	function set_home_page(){

		$home = get_page_by_title( 'Sample Page' );

		if ( get_option( 'page_on_front' ) != $home->ID ){
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $home->ID );
		}

	}

	/**
	 * Set our permalinks to the proper setting
	 *
	 * @access public
	 * @since 1.0.0
	 */
	function modify_permalinks(){
		global $wp_rewrite;

		if ( get_option( 'permalink_structure' ) !== '/%postname%/' ){
			update_option( 'permalink_structure', '/%postname%/' );
			$wp_rewrite->init();
			$wp_rewrite->flush_rules();
		}

	}

	/**
	 * Create the header menu and apply to header menu
	 *
	 * @access public
	 * @since 1.0.0
	 */
	function create_menus(){

		$evans_nav_theme_mod = false;

		if ( !has_nav_menu( 'header-menu' ) ) {
			$primary_nav_id = wp_create_nav_menu(
				__( 'Header Menu', 'evans-mu' ),
				array(
					'slug' => 'header-menu'
				)
			);

			$evans_nav_theme_mod['header-menu'] = $primary_nav_id;
		}

		if ( $evans_nav_theme_mod ) {
			set_theme_mod( 'nav_menu_locations', $evans_nav_theme_mod );
		}

	}

	/**
	 * Update blog settings
	 *
	 * @access public
	 * @since 1.0.0
	 */
	function update_settings(){

		if ( get_option( 'default_comment_status' ) != 'closed' ){	update_option( 'default_comment_status', 'closed' ); }
		if ( get_option( 'default_ping_status' ) != 'closed' ){		update_option( 'default_ping_status', 'closed' ); }
		if ( get_option( 'timezone_string' ) != 'America/Denver' ){ update_option( 'timezone_string', 'America/Denver' ); }

		// Our theme setup is now complete, set a value that we can use later
		// to stop the loading of this file
		add_option( 'evans_theme_setup', 'setup', '', 'no' );

	}

}

$initial_install = new Initial_Settings();
