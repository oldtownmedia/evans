<?php
/**
 * Abstract Dashboard Widget.
 *
 * @package    WordPress
 * @subpackage evans
 */

namespace evans\Abstracts;

/**
 * Abstract lass to quickly and easily generate admin dashboard widgets.
 *
 * @abstract
 */
abstract class Dashboard_Widget {

	/**
	 * ID
	 * ID used in the class and as a slug.
	 *
	 * @var string
	 * @access protected
	 */
	protected $id;

	/**
	 * title
	 * Title for the dashboard widget.
	 *
	 * @var string
	 * @access protected
	 */
	protected $title;

	/**
	 * Hooks function
	 *
	 * This function is used to avoid loading any unnecessary functions/code
	 *
	 * @see wp_dashboard_setup
	 */
	public function hooks() {
		add_action( 'wp_dashboard_setup', [ $this, 'register_widget' ] );
	}

	/**
	 * Register our dashboard widget
	 *
	 * @see wp_add_dashboard_widget
	 */
	public function register_widget() {
		wp_add_dashboard_widget(
			$this->id,                    // Widget slug.
			$this->title,                 // Title
			[ $this, 'build_the_widget' ] // Callback function
		);
	}

	/**
	 * Empty dashboard display function
	 *
	 * This is the function you need to override in your child class to create
	 * your widget. Put all of the html output & logic in this function.
	 */
	abstract public function build_the_widget();
}
