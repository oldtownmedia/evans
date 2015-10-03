<?php
/**
 * Display Several Recent Posts
 *
 * @package   Evans_Recent_Posts
 * @author    OTM <support@oldtownmediainc.com>
 * @license   GPL-2.0+
 * @link      http://oldtownmediainc.com
 * @copyright 2014 Old Town Media
 */

 // Prevent direct file access
if ( ! defined ( 'ABSPATH' ) ){
	exit;
}

class Evans_Recent_Posts extends WP_Widget {

    /**
     * Unique identifier for your widget.
     *
     * @since    1.0.0
     * @var      string
     */
    protected $widget_slug = 'evans-recent-posts';

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
			__( 'Custom Recent Posts', 'evans-mu' ),
			array(
				'classname'  => $this->get_widget_slug().'-class',
				'description' => __( 'Display the most recent posts from the blog.', 'evans-mu' )
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

		if ( ! isset ( $args['widget_id'] ) ){
			$args['widget_id'] = $this->id;
		}

		if ( isset ( $cache[ $args['widget_id'] ] ) ){
			return print $cache[ $args['widget_id'] ];
		}

		extract( $args, EXTR_SKIP );

		$widget_string = $before_widget;

		ob_start();
		include( plugin_dir_path( __FILE__ ) . 'recent-posts-views/widget.php' );
		$widget_string .= ob_get_clean();
		$widget_string .= $after_widget;


		$cache[ $args['widget_id'] ] = $widget_string;

		wp_cache_set( $this->get_widget_slug(), $cache, 'widget' );

		print $widget_string;

	} // end widget


	public function flush_widget_cache(){
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
		$instance['title']		= strip_tags( $new_instance['title'] );
		$instance['category']	= strip_tags( $new_instance['category'] );
		$instance['num_posts']	= strip_tags( $new_instance['num_posts'] );
		$instance['char_length']= strip_tags( $new_instance['char_length'] );

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
				'title' 		=> 'Recent News',
				'category' 		=> '',
				'num_posts' 	=> '1',
				'char_length'	=> '300'
			)
		);

		// Display the admin form
		include( plugin_dir_path(__FILE__) . 'recent-posts-views/admin.php' );

	} // end form

} // end class

add_action( 'widgets_init', create_function( '', 'register_widget("Evans_Recent_Posts");' ) );
