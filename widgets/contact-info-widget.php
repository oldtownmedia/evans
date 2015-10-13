<?php
/**
 * Display Contact Information
 *
 * @package   Evans_Contact_Info
 * @author    OTM <support@oldtownmediainc.com>
 * @license   GPL-2.0+
 * @link      http://oldtownmediainc.com
 * @copyright 2014 Old Town Media
 */

 // Prevent direct file access
if ( ! defined ( 'ABSPATH' ) ){
	exit;
}

class Evans_Contact_Info extends WP_Widget {

    /**
     * Unique identifier for your widget.
     *
     * @since    1.0.0
     * @var      string
     */
    protected $widget_slug = 'evans-contact-info';

	/*--------------------------------------------------*/
	/* Constructor
	/*--------------------------------------------------*/

	/**
	 * Specifies the classname and description, instantiates the widget,
	 * loads localization files, and includes necessary stylesheets and JavaScript.
	 */
	public function __construct() {

		parent::__construct(
			$this->get_widget_slug(),
			__( 'Contact Information', 'evans-mu' ),
			array(
				'classname'  => $this->get_widget_slug().'-class',
				'description' => __( 'Display your contact information on a sidebar.', 'evans-mu' )
			)
		);

		// Refreshing the widget's cached output with each new post
		add_action( 'save_post',    array( $this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );

	} // end constructor


    /**
     * Return the widget slug.
     *
     * @since    1.0.0
     * @return    Plugin slug variable.
     */
    public function get_widget_slug() {
        return $this->widget_slug;
    }

	/*--------------------------------------------------*/
	/* Widget API Functions
	/*--------------------------------------------------*/

	/**
	 * Outputs the content of the widget.
	 *
	 * @param array args  The array of form elements
	 * @param array instance The current instance of the widget
	 */
	public function widget( $args, $instance ){

		// Check if there is a cached output
		$cache = wp_cache_get( $this->get_widget_slug(), 'widget' );

		if ( !is_array( $cache ) ){
			$cache = array();
		}

		if ( !isset( $args['widget_id'] ) ){
			$args['widget_id'] = $this->id;
		}

		if ( isset( $cache[ $args['widget_id'] ] ) ){
			return print $cache[ $args['widget_id'] ];
		}

		extract( $args, EXTR_SKIP );

		$widget_string = $before_widget;

		ob_start();
		include( plugin_dir_path( __FILE__ ) . 'contact-info-views/widget.php' );
		$widget_string .= ob_get_clean();
		$widget_string .= $after_widget;

		if ( isset( $args['widget_id'] ) ){

			$cache[ $args['widget_id'] ] = $widget_string;
			wp_cache_set( $this->get_widget_slug(), $cache, 'widget' );

		}

		print $widget_string;

	} // end widget


	public function flush_widget_cache() {
    	wp_cache_delete( $this->get_widget_slug(), 'widget' );
	}
	/**
	 * Processes the widget's options to be saved.
	 *
	 * @param array new_instance The new instance of values to be generated via the update.
	 * @param array old_instance The previous instance of values before the update.
	 */
	public function update( $new_instance, $old_instance ){

		$instance = $old_instance;

		//Strip tags from title and name to remove HTML
		$instance['title'] 		= strip_tags( $new_instance['title'] );
		$instance['address1']	= strip_tags( $new_instance['address1'] );
		$instance['city']		= strip_tags( $new_instance['city'] );
		$instance['state']		= strip_tags( $new_instance['state'] );
		$instance['zip']		= strip_tags( $new_instance['zip'] );
		$instance['phone']		= strip_tags( $new_instance['phone'] );
		$instance['fax']		= strip_tags( $new_instance['fax'] );
		$instance['email']		= sanitize_email( strip_tags( $new_instance['email'] ) );
		$instance['map']		= strip_tags( $new_instance['map'] );

		return $instance;

	} // end widget

	/**
	 * Generates the administration form for the widget.
	 *
	 * @param array instance The array of keys and values for the widget.
	 */
	public function form( $instance ){

		$instance = wp_parse_args(
			(array) $instance,
			$defaults = array(
				'title' 		=> 'Contact Information',
				'address1' 		=> '123 Anywhere St',
				'city' 			=> 'Fort Collins',
				'state' 		=> 'CO',
				'zip' 			=> '80521',
				'phone' 		=> '(970) 123 4567',
				'fax' 			=> '(970) 123 4567',
				'email' 		=> 'me@mysite.com',
				'map'			=> 'true'
			)
		);

		// Display the admin form
		include( plugin_dir_path(__FILE__) . 'contact-info-views/admin.php' );

	} // end form

} // end class

add_action( 'widgets_init', create_function( '', 'register_widget("Evans_Contact_Info");' ) );
