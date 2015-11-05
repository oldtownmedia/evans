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
 */
class Initial_Settings{

	/**
	 * Constructor function.
	 *
	 * @access public
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'set_home_page' ), 105 );
		add_action( 'admin_init', array( $this, 'modify_permalinks' ), 105 );
		add_action( 'admin_init', array( $this, 'create_menus' ), 105);
		add_filter( 'admin_init', array( $this, 'update_settings' ), 105 );
		add_action( 'admin_init', array( $this, 'add_customer_admin_role' ), 105 );

	}

	/**
	 * Set the home page
	 *
	 * @access public
	 */
	public function set_home_page(){

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
	 */
	public function modify_permalinks(){
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
	 */
	public function create_menus(){

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
	 */
	public function update_settings(){

		if ( get_option( 'default_comment_status' ) != 'closed' ){	update_option( 'default_comment_status', 'closed' ); }
		if ( get_option( 'default_ping_status' ) != 'closed' ){		update_option( 'default_ping_status', 'closed' ); }
		if ( get_option( 'timezone_string' ) != 'America/Denver' ){ update_option( 'timezone_string', 'America/Denver' ); }

		// Our theme setup is now complete, set a value that we can use later
		// to stop the loading of this file
		add_option( 'evans_theme_setup', 'setup', '', 'no' );

	}

	/**
	 * Create a pseudo-admin custom role with limited priveledges
	 *
	 * @access public
	 */
	public function add_customer_admin_role(){

		$role_id 		= 'customer_admin';
		$role_name 		= __( 'Customer Administrator', 'evans-mu' );
		$capabilities 	= array(

			// Allowed Priveledges
			'edit_files' 			=> true,
			'moderate_comments' 	=> true,
			'manage_categories' 	=> true,
			'manage_links' 			=> true,
			'upload_files' 			=> true,
			'unfiltered_html' 		=> true,
			'edit_posts' 			=> true,
			'edit_others_posts' 	=> true,
			'edit_published_posts' 	=> true,
			'publish_posts' 		=> true,
			'edit_pages'			=> true,
			'read' 					=> true,
			'edit_others_pages' 	=> true,
			'edit_published_pages' 	=> true,
			'publish_pages' 		=> true,
			'delete_pages' 			=> true,
			'delete_others_pages' 	=> true,
			'delete_published_pages' => true,
			'delete_posts' 			=> true,
			'delete_others_posts' 	=> true,
			'delete_published_posts' => true,
			'delete_private_posts' 	=> true,
			'edit_private_posts' 	=> true,
			'read_private_posts' 	=> true,
			'delete_private_pages' 	=> true,
			'edit_private_pages' 	=> true,
			'read_private_pages' 	=> true,
			'unfiltered_upload' 	=> true,

			// Denied Priveledges
			'edit_theme_options' 	=> false,
			'manage_options' 		=> false,
			'edit_dashboard' 		=> false,
			'activate_plugins' 		=> false,
			'edit_plugins' 			=> false,
			'update_plugins' 		=> false,
			'delete_plugins' 		=> false,
			'install_plugins' 		=> false,
			'update_core' 			=> false,
			'edit_users' 			=> false,
			'delete_users' 			=> false,
			'create_users' 			=> false,
			'list_users' 			=> false,
			'remove_users' 			=> false,
			'add_users' 			=> false,
			'promote_users' 		=> false,
			'switch_themes' 		=> false,
			'edit_themes' 			=> false,
			'update_themes' 		=> false,
			'install_themes' 		=> false,
			'delete_themes' 		=> false,
			'import' 				=> false,
			'export' 				=> false,
			'administrator' 		=> false,
		);


		if( ! $GLOBALS['wp_roles']->is_role( $role_id ) ) {

			$result = add_role(
			    $role_id,
			    $role_name,
			    $capabilities
			);

		}

	}

}

$initial_install = new Initial_Settings();
