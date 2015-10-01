<?php
defined( 'ABSPATH' ) OR exit;

function add_hello_dashboard_widget() {

	// define the MU_PLUGINS_URL
	if (! defined('MU_PLUGINS_URL') ){
		define('MU_PLUGINS_URL', plugins_url( '' ,  __FILE__ ) );
	}

	wp_add_dashboard_widget(
		'otm_support_dashboard_widget',		// Widget slug.
		__( 'Welcome to Your Dashboard', 'evans-mu' ),		// Title.
		'otm_support_dashboard_widget'		// Display function.
	);

}
add_action( 'wp_dashboard_setup', 'add_hello_dashboard_widget' );


function otm_support_dashboard_widget() {
	global $wpdb;

	echo "<img src='".plugins_url( '../resources/images/widget-logo.png', __FILE__ )."' style='float:right;'>";

	echo "<h2>".__( 'Hello!', 'evans-mu' )."</h2>";

	echo "<p>".__( 'We are here to support you with your website. If you have any questions or concerns, please reach out to us!' , 'evans-mu' )."</p>";

	echo "<p>";

		echo "<strong>".__( 'Phone:', 'evans-mu' )."</strong> 970.568.5250<br>";
		echo "<strong>".__( 'Email:', 'evans-mu' )."</strong> <a href='mailto:support@oldtownmediainc.com'>support@oldtownmediainc.com</a><br>";
		echo "<strong>".__( 'Hours:', 'evans-mu' )."</strong> 8:00 am - 5:00 pm";

	echo "</p>";

}


?>