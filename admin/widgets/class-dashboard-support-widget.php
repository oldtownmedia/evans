<?php
namespace evans\Widgets;

use evans\Abstracts;

/**
 * CompanySupportWidget
 *
 * Support information for display in the admin section
 *
 * @package    WordPress
 * @subpackage Evans
 * @author     Old Town Media
 */
final class CompanySupportWidget extends Abstracts\Dashboard_Widget{

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

		echo "<img src='" . esc_url( plugins_url( '../assets/images/widget-logo.png', __FILE__ ) ) . "' style='float:right;'>";

		echo "<h2>" . esc_html__( 'Hello!', 'evans-mu' ) . "</h2>";

		echo "<p>" . esc_html__( 'We are here to support you with your website. If you have any questions or concerns, please reach out to us!' , 'evans-mu' ) . "</p>";

		echo "<p>";

			echo "<strong>" . __( 'Phone:', 'evans-mu' ) . "</strong> " . esc_html( $this->phone ) . "<br>";
			echo "<strong>" . __( 'Email:', 'evans-mu' ) . "</strong> <a href='mailto:" . esc_attr( $this->email )."'>" . esc_html( $this->email ) . "</a><br>";
			echo "<strong>" . __( 'Hours:', 'evans-mu' ) . "</strong> " . esc_html( $this->hours );

		echo "</p>";

	}

}

$widget = new CompanySupportWidget();
$widget->hooks();
