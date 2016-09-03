<?php
namespace evans;

/**
 * Site Lockdown
 *
 * Serves up a custom splash page to visitors not on our network if
 * the evans_site_lockdown option is set to 'locked'.
 *
 * @package    WordPress
 * @subpackage Evans
 * @author     Old Town Media
 */
class Site_Lockdown {

	/**
	 * server_ip
	 * IP Address of the server for this script to run on.
	 *
	 * @var string
	 * @access private
	 */
	private $server_ip 		= '104.131.16.207';

	/**
	 * acceptable_ips
	 * Array of IPs to check whitelisted users against.
	 *
	 * @var array
	 * @access private
	 */
	private $acceptable_ips	= array(
		'50.78.80.53',		// Comcast
		'209.181.65.144',	// Centurylink
		'174.16.201.183'
	);

	/**
	 * block_page
	 * URL to pull the block page from.
	 *
	 * @var string
	 * @access private
	 */
	private $block_page		= 'http://dev.otmoffice.com/resources/index.html';


	 /**
	  * Constructor function.
	  *
	  * @see add_action
	  */
	public function __construct() {

		// If we're not in the correct server, don't do anything.
		if ( $_SERVER['SERVER_ADDR'] != $this->server_ip ) {
			return;
		}

		add_action( 'admin_bar_menu', array( $this, "page_lockdown_display" ), 500 );
		add_action( 'admin_init', array( $this, 'set_lockdown_option' ), 105 );
		add_action( 'init', array( $this, 'lockdown_this_thing' ), 105 );
		add_action( 'init', array( $this, 'lockdown_actions' ), 5 );
		add_action( 'admin_init', array( $this, 'lockdown_actions' ), 5 );

	}


	/**
	 * Create our admin menu main node for turning lockdown on or off.
	 *
	 * @see get_option, wp_get_current_user, is_super_admin, is_admin_bar_showing
	 *
	 * @param type $wp_admin_bar Global wp_admin_bar object.
	 */
	public function page_lockdown_display( $wp_admin_bar ) {

		// Chck that the admin bar is showing and is admin user
		if ( !is_super_admin() || !is_admin_bar_showing() ) {
			return;
		}

		$current_user = wp_get_current_user();
		if ( $current_user->user_login == 'otm' ) {

			$locked = get_option( 'evans_site_lockdown' );

			if ( $locked == 'locked' ) {
				$title	= '<span style="color:green">' . esc_html__( 'Site is locked', 'evans-mu' ) . '</span>';
				$link	= $this->maybe_add_get_to_url() . 'unlock_site=' . wp_create_nonce( 'evans_unlock_site' );
			} else {
				$title	= '<span style="color:red">' . esc_html__( 'Site is unlocked', 'evans-mu' ) . '</span>';
				$link	= $this->maybe_add_get_to_url() . 'lock_site=' . wp_create_nonce( 'evans_lock_site' );
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
	 * outsiders.
	 *
	 * @see wp_verify_nonce, update_option
	 */
	public function lockdown_this_thing() {

		// If there's no parameter, stop
		if ( empty( $_GET ) ) {
			return;
		}

		if ( isset( $_GET['lock_site'] ) && wp_verify_nonce( $_GET['lock_site'], 'evans_lock_site' ) ) {

			update_option( 'evans_site_lockdown', 'locked' );

		} else if ( isset( $_GET['unlock_site'] ) && wp_verify_nonce( $_GET['unlock_site'], 'evans_unlock_site' ) ) {

			update_option( 'evans_site_lockdown', 'unlocked' );

		}

	}


	/**
	 * Check our option value and see if we need to block out the site.
	 *
	 * If we get the wrong IP back and the site is locked, we display
	 * a page served from the dev server resources folder.
	 *
	 * @see wp_remote_get
	 */
	public function lockdown_actions() {

		// we're logged in and validated, return
		if ( is_user_logged_in() || in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) ) ) {
			return;
		}

		$locked = get_option( 'evans_site_lockdown' );

		if ( $locked == 'locked' && !is_admin() ) {

			// Check to see if the current user is allowed
			if ( !in_array( $_SERVER['REMOTE_ADDR'], $this->acceptable_ips ) ) {

				$response = wp_remote_get( $this->block_page );

				if( is_array( $response ) ) {
				  echo $response['body']; // use the content
				}

				exit;

			}

		}

	}


	 /**
	  * Initially create the option to ensure the site is NOT locked down.
	  *
	  * Option is autoloaded.
	  *
	  * @see get_option,add_option
	  */
	public function set_lockdown_option() {

		if ( get_option( 'evans_site_lockdown' ) === false ) {
			add_option( 'evans_site_lockdown', 'unlocked', '', 'yes' );
		}

	}


	/**
	 * Parse the current url to see which type of prefix needs to be added to our URL.
	 *
	 * @return string reformatted URL with our parameter appended
	 */
	public function maybe_add_get_to_url() {

		$url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
		$url .= ( $_SERVER["SERVER_PORT"] !== 80 ) ? ":".$_SERVER["SERVER_PORT"] : "";
		$url .= $_SERVER["REQUEST_URI"];

		if ( strpos( $url, '?' ) !== false ) {
			return $url . "&";
		} else {
			return $url . "?";
		}
	}

}

/*
 * Instantiate our class
 */
$evans_simple_admin = new Site_Lockdown();
