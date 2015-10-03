<?php

class CompanySupportWidget extends Dashboard_Widget{

	// Defaults
	protected $id 		= 'evans_support_dashboard_widget';
	protected $title 	= 'Welcome to Your Dashboard';

	// Specific widget info
	protected $phone = '(970) 568 5250';
	protected $email = 'support@oldtownmediainc.com';
	protected $hours = '8:00 am - 5:00 pm';

	// Output function
	public function build_the_widget() {

		echo "<img src='".plugins_url( '../resources/images/widget-logo.png', __FILE__ )."' style='float:right;'>";

		echo "<h2>".__( 'Hello!', 'evans-mu' )."</h2>";

		echo "<p>".__( 'We are here to support you with your website. If you have any questions or concerns, please reach out to us!' , 'evans-mu' )."</p>";

		echo "<p>";

			echo "<strong>".__( 'Phone:', 'evans-mu' )."</strong> ".esc_attr( $this->phone )."<br>";
			echo "<strong>".__( 'Email:', 'evans-mu' )."</strong> <a href='mailto:".sanitize_email( $this->email )."'>".sanitize_email( $this->email )."</a><br>";
			echo "<strong>".__( 'Hours:', 'evans-mu' )."</strong> ".esc_attr( $this->hours );

		echo "</p>";

	}

}

$widget = new CompanySupportWidget();
$widget->hooks();


?>