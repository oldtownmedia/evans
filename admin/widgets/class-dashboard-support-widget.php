<?php
/**
 * Dashboard Support information widget.
 *
 * @package    WordPress
 * @subpackage evans
 */

namespace evans\Widgets;

use evans\Abstracts;

/**
 * CompanySupportWidget
 *
 * Support information for display in the admin section
 */
final class CompanySupportWidget extends Abstracts\Dashboard_Widget {

	/**
	 * ID used in the class and as a slug.
	 *
	 * @var string
	 */
	protected $id = 'evans_support_dashboard_widget';

	/**
	 * Title for the dashboard widget.
	 *
	 * @var string
	 */
	protected $title = 'Welcome to Your Dashboard';

	/**
	 * Phone # of your company.
	 *
	 * @var string
	 */
	protected $phone = '(970) 568 5250';

	/**
	 * Email address of your company.
	 *
	 * @var string
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

		echo '<h2>' . esc_html__( 'Hello!', 'evans-mu' ) . '</h2>';

		echo '<p>' . esc_html__( 'We are here to support you with your website. If you have any questions or concerns, please reach out to us!' , 'evans-mu' ) . '</p>';

		echo '<p>';

			echo '<strong>' . esc_html__( 'Phone:', 'evans-mu' ) . '</strong> ' . esc_html( $this->phone ) . '<br>';
			echo '<strong>' . esc_html__( 'Email:', 'evans-mu' ) . "</strong> <a href='mailto:" . esc_attr( $this->email ) . "'>" . esc_html( $this->email ) . '</a><br>';
			echo '<strong>' . esc_html__( 'Hours:', 'evans-mu' ) . '</strong> ' . esc_html( $this->hours );

		echo '</p>';
	}
}

$widget = new CompanySupportWidget();
$widget->hooks();
