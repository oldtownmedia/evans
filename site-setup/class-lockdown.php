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
class Lockdown {

	/**
	 * IP Address of the server for this script to run on.
	 *
	 * @var string
	 */
	private $server_ip = '104.131.16.207';

	/**
	 * URL to pull the block page from.
	 *
	 * @var string
	 */
	private $block_page	= 'http://dev.otmoffice.com/resources/index.html';

	 /**
	  * Constructor function.
	  */
	public function __construct() {
		// If we're not in the correct server, don't do anything.
		if ( $this->server_ip !== $_SERVER['SERVER_ADDR'] ) {
			return;
		}

		add_action( 'admin_bar_menu', [ $this, 'page_lockdown_display' ], 500 );
		add_action( 'admin_init', [ $this, 'set_lockdown_option' ], 105 );
		add_action( 'init', [ $this, 'lockdown_this_thing' ], 105 );
		add_action( 'init', [ $this, 'lockdown_actions' ], 5 );
		add_action( 'admin_init', [ $this, 'lockdown_actions' ], 5 );
	}

	/**
	 * Create our admin menu main node for turning lockdown on or off.
	 *
	 * @param object $wp_admin_bar Global wp_admin_bar object.
	 */
	public function page_lockdown_display( $wp_admin_bar ) {
		// Check that the admin bar is showing and is admin user
		if ( ! is_super_admin() || ! is_admin_bar_showing() ) {
			return;
		}

		$current_user = wp_get_current_user();
		if ( 'otm' === $current_user->user_login ) {

			$locked = get_option( 'evans_site_lockdown' );

			// @todo:: run a URL here.

			if ( $locked == 'locked' ) {
				$title	= '<span style="color:green">' . esc_html__( 'Site is locked', 'evans-mu' ) . '</span>';
				$link   = add_query_arg( 'unlock_site',  wp_create_nonce( 'evans_unlock_site' ), $url );
			} else {
				$title	= '<span style="color:red">' . esc_html__( 'Site is unlocked', 'evans-mu' ) . '</span>';
				$link   = add_query_arg( 'lock_site',  wp_create_nonce( 'evans_lock_site' ), $url );
			}

			$wp_admin_bar->add_node(
				[
					'id'     => 'site_lockdown',
					'title'  => $title,
					'parent' => false,
					'href'   => $link,
				]
			);
		}
	}

	/**
	 * Check our option value and see if we need to block out the site
	 *
	 * If the site lock option was clicked, lock or unlock the sites from
	 * outsiders.
	 */
	public function lockdown_this_thing() {
		// If there's no parameter, stop.
		if ( empty( $_GET ) ) {
			return;
		}

		if ( isset( $_GET['lock_site'] ) && wp_verify_nonce( $_GET['lock_site'], 'evans_lock_site' ) ) {
			update_option( 'evans_site_lockdown', 'locked' );
		} elseif ( isset( $_GET['unlock_site'] ) && wp_verify_nonce( $_GET['unlock_site'], 'evans_unlock_site' ) ) {
			update_option( 'evans_site_lockdown', 'unlocked' );
		}
	}

	/**
	 * Check our option value and see if we need to block out the site.
	 *
	 * If we get the wrong IP back and the site is locked, we display
	 * a page served from the dev server resources folder.
	 */
	public function lockdown_actions() {
		// We're logged in and validated, return.
		if ( is_user_logged_in() || in_array( $GLOBALS['pagenow'], [ 'wp-login.php', 'wp-register.php' ], true ) ) {
			return;
		}

		$locked = get_option( 'evans_site_lockdown' );

		if ( 'locked' === $locked && ! is_admin() ) {

			// Check to see if the current user is allowed
			if ( ! in_array( $_SERVER['REMOTE_ADDR'], $this->acceptable_ips, true ) ) {
				$response = wp_remote_get( $this->block_page );

				if ( is_array( $response ) ) {
					echo wp_post_kses( $response['body'] );
				}

				exit;
			}
		}
	}

	/**
	 * Initially create the option to ensure the site is NOT locked down.
	 *
	 * Option is autoloaded.
	 */
	public function set_lockdown_option() {
		if ( get_option( 'evans_site_lockdown' ) === false ) {
			add_option( 'evans_site_lockdown', 'unlocked', '', 'yes' );
		}
	}
}

/*
 * Instantiate our class
 */
$evans_simple_admin = new Lockdown();
