<?php
namespace evans;

/**
 * CompanySupportWidget
 *
 * Support information for display in the admin section
 *
 * @package    WordPress
 * @subpackage Evans
 * @author     Old Town Media
 */
class CompanySupportWidget extends Dashboard_Widget{

	/**
	 * ID
	 * ID used in the class and as a slug.
	 *
	 * @var string
	 * @access protected
	 */
	protected $id 		= 'evans_support_dashboard_widget';

	/**
	 * title
	 * Title for the dashboard widget.
	 *
	 * @var string
	 * @access protected
	 */
	protected $title 	= 'Welcome to Your Dashboard';

	/**
	 * phone
	 * Phone # of your company.
	 *
	 * @var string
	 * @access protected
	 */
	protected $phone = '(970) 568 5250';

	/**
	 * email
	 * Email address of your company.
	 *
	 * @var string
	 * @access protected
	 */
	protected $email = 'support@oldtownmediainc.com';

	/**
	 * phone
	 * Hours of your company.
	 *
	 * @var string
	 * @access protected
	 */
	protected $hours = '8:00 am - 5:00 pm';


	/*
	 * Output the contents of the dashboard widget.
	 */
	public function build_the_widget() {

		echo "<img src='".plugins_url( '../assets/images/widget-logo.png', __FILE__ )."' style='float:right;'>";

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