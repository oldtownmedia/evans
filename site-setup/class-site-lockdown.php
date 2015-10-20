<?php
defined( 'ABSPATH' ) OR exit;

/**
 * Site Lockdown
 *
 * Serves up a custom splash page to visitors not on our network if
 * the evans_site_lockdown option is set to 'locked'
 *
 * @package WordPress
 * @category mu_plugin
 * @author Old Town Media
 */
class Site_Lockdown {

	 /**
	 * Constructor function. Run the hooks ONLY if the server is on 104.131.16.207
	 */
	function __construct() {

		// If we're not in the dev.otmoffice.com server
		if ( $_SERVER['SERVER_ADDR'] != '104.131.16.207' ){
			return;
		}

		add_action( 'admin_bar_menu', array( $this, "page_lockdown_display" ), 500 );
		add_action( 'admin_init', array( $this, 'set_lockdown_option' ), 105 );
		add_action( 'init', array( $this, 'lockdown_this_thing' ), 105 );
		add_action( 'init', array( $this, 'lockdown_actions' ), 5 );
		add_action( 'admin_init', array( $this, 'lockdown_actions' ), 5 );

	}

	 /**
	 * Build an array of OTM-accessed IPs
	 *
	 * @return array Array of IPs that are whitelisted.
	 */
	public function evans_acceptible_ips() {

		return array(
			'50.78.80.53',		// Comcast
			'209.181.65.144',	// Centurylink
			'174.16.201.183',	// Mike's House
		);

	}

	/**
	 * Create our admin menu main node
	 *
	 * @param type $wp_admin_bar Global wp_admin_bar object.
	 */
	public function page_lockdown_display( $wp_admin_bar ) {

		if (!is_super_admin() || !is_admin_bar_showing() ){
			return;
		}

		$current_user = wp_get_current_user();
		if ( $current_user->user_login == 'otm' ){

			$locked = get_option( 'evans_site_lockdown' );

			if ( $locked == 'locked' ){
				$title = '<span style="color:green">'.__( 'Site is locked', 'evans-mu' ).'</span>';
				$link = $this->maybe_add_get_to_url() . 'unlock_site=' . wp_create_nonce( 'evans_unlock_site' );
			} else {
				$title = '<span style="color:red">'.__( 'Site is unlocked', 'evans-mu' ).'</span>';
				$link = $this->maybe_add_get_to_url() . 'lock_site=' . wp_create_nonce( 'evans_lock_site' );
			}

			$wp_admin_bar->add_node(
				array(
					'id' 		=> 'site_lockdown',
					'title' 	=> $title,
					'parent'	=> false,
					'href' 		=> $link
				)
			);

		}

	}

	/**
	 * Check our option value and see if we need to block out the site
	 *
	 * If the site lock option was clicked, lock or unlock the sites from
	 * outsiders
	 */
	public function lockdown_this_thing() {

		// If there's no parameter, stop
		if ( !$_GET ){
			return;
		}

		if ( isset( $_GET['lock_site'] ) ){

			if ( wp_verify_nonce( $_GET['lock_site'], 'evans_lock_site' ) ){
				update_option( 'evans_site_lockdown', 'locked' );
			}

		} else if ( isset( $_GET['unlock_site'] ) ){

			if ( wp_verify_nonce( $_GET['unlock_site'], 'evans_unlock_site' ) ){
				update_option( 'evans_site_lockdown', 'unlocked' );
			}

		}

	}

	/**
	 * Check our option value and see if we need to block out the site
	 *
	 * If we get the wrong IP back and the site is locked, we display
	 * a page served from the dev server resources folder
	 */
	public function lockdown_actions() {

		$locked = get_option( 'evans_site_lockdown' );

		if ( $locked == 'locked' && !is_admin() ){

			// Check to see if the current user is allowed
			if ( !in_array( $_SERVER['REMOTE_ADDR'], $this->evans_acceptible_ips() ) ){

				$response = wp_remote_get( 'http://dev.otmoffice.com/resources/index.html' );

				if( is_array( $response ) ) {
				  echo $response['body']; // use the content
				}

				exit;

			}

		}

	}

	 /**
	 * Initially create the option to ensure the site is NOT locked down
	 */
	function set_lockdown_option() {

		if ( get_option( 'evans_site_lockdown' ) === false ){
			add_option( 'evans_site_lockdown', 'unlocked', '', 'yes' );
		}

	}

	/**
	 * Parse the current url to see which type of prefix needs to be added to our URL
	 *
	 * @return string reformatted URL with our parameter appended
	 */
	public function maybe_add_get_to_url(){

		$url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
		$url .= ( $_SERVER["SERVER_PORT"] !== 80 ) ? ":".$_SERVER["SERVER_PORT"] : "";
		$url .= $_SERVER["REQUEST_URI"];

		if ( strpos( $url, '?' ) !== false ){
			return $url . "&";
		} else {
			return $url . "?";
		}
	}

}

$evans_simple_admin = new Site_Lockdown();

?>