<?php
/**
 * Abstract Dashboard Widget.
 *
 * @package    WordPress
 * @subpackage evans
 */

namespace evans\Abstracts;

/**
 * Abstract lass to quickly and easily generate widgets.
 *
 * @abstract
 */
abstract class Widget extends \WP_Widget {

	/**
	 * base
	 * Base for widget - to be used in referencing files, folders, creating a
	 * class, etc.
	 *
	 * @var string
	 * @access protected
	 */
	protected $base;

	/**
	 * title
	 * Title for the widget choice box.
	 *
	 * @var string
	 * @access protected
	 */
	protected $title;

	/**
	 * description
	 * Description of the widget.
	 *
	 * @var string
	 * @access protected
	 */
	protected $description;

	/**
	 * Constructor function
	 *
	 * @see add_action, add_filter
	 */
	public function __construct() {
		parent::__construct(
			$this->base,
			$this->title,
			[
				'classname'   => $this->base . '-class',
				'description' => esc_html( $this->description ),
			]
		);

		// Admin Styles
		add_action( 'admin_print_styles', [ $this, 'register_admin_styles' ] );

		// Refreshing the widget's cached output with each new post
		add_action( 'save_post',    [ $this, 'flush_widget_cache' ] );
		add_action( 'deleted_post', [ $this, 'flush_widget_cache' ] );
		add_action( 'switch_theme', [ $this, 'flush_widget_cache' ] );
	}

	/**
	 * Flushed the cache of the widget.
	 *
	 * @see wp_cache_delete
	 */
	public function flush_widget_cache() {
		wp_cache_delete( $this->base, 'widget' );
	}

	/**
	 * Check cache for the widget & display.
	 *
	 * @see $this->view, wp_cache_get
	 *
	 * @param type $args Base widget arguments.
	 * @param type $instance The data from the widget editing.
	 */
	public function widget( $args, $instance ) {
		// Check if there is a cached output
		$cache = wp_cache_get( $this->base, 'widget' );

		if ( ! is_array( $cache ) ) {
			$cache = [];
		}

		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			return $cache[ $args['widget_id'] ];
		}

		$widget_string = $before_widget;

		$widget_string .= $this->view( $args, $instance );
		$widget_string .= $after_widget;

		if ( isset( $args['widget_id'] ) ) {

			$cache[ $args['widget_id'] ] = $widget_string;
			wp_cache_set( $this->base, $cache, 'widget' );

		}

		print $widget_string;
	}

	/**
	 * Handle the data from the updated widget and parse against the old data.
	 *
	 * @see $this->widget_fields, $this->sanitize_field
	 *
	 * @param array $new_instance Updated data.
	 * @param array $old_instance Old data.
	 * @return array New, parsed instance.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$fields = $this->widget_fields();

		if ( empty( $fields ) ) {
			return $instance;
		}

		foreach ( $fields as $field ) {
			$instance[ $field['id'] ] = $this->sanitize_field( $field, $new_instance[ $field['id'] ] );
		}

		return $instance;
	}

	/**
	 * Sanitize each field individually based on sanitization options.
	 *
	 * In the declaration for each field in the child classes, a sanitization
	 * options may be declared. Either an array or single option may be used.
	 * This function will run the a default strip_tags or the chosen sanitization
	 * method.
	 *
	 * @access private
	 *
	 * @see strip_tags
	 *
	 * @param array $field Field options.
	 * @param variable $value Input value.
	 * @return variable Sanitized value.
	 */
	private function sanitize_field( $field, $value ) {
		if ( empty( $field ) ) {
			return null;
		}

		// If the user has chose a custom sanitization
		if ( isset( $field['sanitize'] ) ) {
			$sanitize = $field['sanitize'];

			// If the user has chosen an array
			if ( is_array( $sanitize ) ) {
				$new_value = '';

				foreach ( $sanitize as $filter ) {
					$new_value = $filter( $value );
				}

				$sanitized = $new_value;

				// Else if we're just dealing one sanitization option
			} else {
				$sanitized = $sanitize( $value );
			}
		} else {
			$sanitized = strip_tags( $value );
		}

		return $sanitized;
	}

	/**
	 * Build and print our admin form for editing the widget
	 *
	 * @see $this->admin_form, $this->get_defaults()
	 *
	 * @param array $instance Data for this instance
	 */
	public function form( $instance ) {
		// Check against our defaults
		$instance = wp_parse_args(
			(array) $instance,
			$this->get_defaults()
		);

		// Display the admin form
		echo $this->admin_form( $instance );
	}

	/**
	 * Placeholder function to overwrite when creating your own child widget.
	 *
	 * This is where you add your own fields to the widget so that users can edit
	 * their copy. Using the below guides, you can create as many fields as you
	 * want similarly to CMB2.
	 *
	 * @return array list of fields
	 */
	abstract public function widget_fields();

	/**
	 * Placeholder for the front-end view of your widget.
	 *
	 * This is what you will overwrite in order to display your widget.
	 *
	 * @param array $args Base widget data such as before_title.
	 * @param arry $instance Widget data.
	 * @return string Widget HTML.
	 */
	abstract public function view( $args, $instance );

	/**
	 * Simple return for a title for widgets.
	 *
	 * The title looks the same in almost all widgets so I decided to throw it in
	 * a reusable function
	 *
	 * @param array $args Base widget data such as before_title.
	 * @param arry $instance Widget data.
	 * @return string title HTML.
	 */
	public function get_widget_title( $args, $instance ) {
		// Display the widget title
		if ( $instance['title'] ) {
			return $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
	}

	/**
	 * Gets the default values for use in our form from each field.
	 *
	 * @access private
	 *
	 * @see $this->widget_fields
	 *
	 * @return array Default values to parse against.
	 */
	private function get_defaults() {
		$fields = $this->widget_fields();
		$defaults = [];

		if ( ! is_array( $fields ) || empty( $fields ) ) {
			return null;
		}

		foreach ( $fields as $field ) {

			if ( isset( $field['default'] ) ) {
				$defaults[ $field['id'] ]	= $field['default'];
			} else {
				$defaults[ $field['id'] ] = '';
			}
		}

		return $defaults;
	}

	/**
	 * Build and compile our widget edit form for admin.
	 *
	 * @param array $instance Description.
	 * @return string compiled HTML.
	 */
	public function admin_form( $instance ) {
		$html = '';

		$fields = $this->widget_fields();

		if ( empty( $fields ) ) {
			return null;
		}

		// Loop through the fields and build an HTML form field
		foreach ( $fields as $field ) {
			$html .= $this->build_form_field( $field, $instance );
		}

		return $html;
	}

	/**
	 * Build each individual type of form field.
	 *
	 * Take in the data for each form field as well as set exisisting data for
	 * each field. Right now only a subset of fields are supported, but this
	 * will be growing as needs for these grow.
	 *
	 * @access private
	 *
	 * @see get_field_id, get_field_name
	 *
	 * @param array $field Field information.
	 * @param array $instance Data from the widget.
	 * @return string Compiled field.
	 */
	private function build_form_field( $field, $instance ) {
		$html = '';

		if ( empty( $field ) ) {
			return null;
		}

		$html .= "<p class='" . esc_attr( $field['type'] ) . "-box evans-field'>";

		$html .= "<label for='" . esc_attr( $this->get_field_id( $field['id'] ) ) . "'><strong>" . esc_attr( $field['name'] ) . '</strong></label>';

		if ( 'checkbox' !== $field['type'] ) {
			$html .= '<br>';
		}

		switch ( $field['type'] ) {

			case 'text' :

				$html .= "<input id='" . esc_attr( $this->get_field_id( $field['id'] ) ) . "' name='" . esc_attr( $this->get_field_name( $field['id'] ) ) . "' value='" . esc_attr( $instance[ $field['id'] ] ) . "' />";

			break;

			case 'textarea' :

				$html .= "<textarea id='" . esc_attr( $this->get_field_id( $field['id'] ) ) . "' name='" . esc_attr( $this->get_field_name( $field['id'] ) ) . "'>" . esc_html( $instance[ $field['id'] ] ) . '</textarea>';

			break;

			case 'select' :

				if ( ! empty( $field['options'] ) ) {

					$html .= "<select id='" . esc_attr( $this->get_field_id( $field['id'] ) ) . "' name='" . esc_attr( $this->get_field_name( $field['id'] ) ) . "' >";

					foreach ( $field['options'] as $key => $value ) {
						$selected = '';

						if ( $key === $instance[ $field['id'] ] ) {
							$selected = 'selected';
						}

						$html .= "<option value=' " . esc_attr( $key ) . "' " . esc_attr( $selected ) . '>';
						$html .= esc_html( $value );
						$html .= '</option>';
					}

					$html .= '</select>';

				}

			break;

			case 'radio' :

				if ( ! empty( $field['options'] ) ) {

					foreach ( $field['options'] as $key => $value ) {
						$selected = '';

						if ( $key === $instance[ $field['id'] ] ) {
							$selected = ' checked"';
						}

						$html .= "<input type='radio' value='" . esc_attr( $key ) . "' " . esc_attr( $selected ) . '> <span>' . esc_html( $value ) . '</span><br>';

					}
				}

			break;

			case 'checkbox' :
				$checked = '';

				if ( 'on' === $instance[ $field['id'] ] ) {
					$checked = ' checked';
				}

				$html .= "<input type='checkbox' id='" . esc_attr( $this->get_field_id( $field['id'] ) ) . "' name='" . esc_attr( $this->get_field_name( $field['id'] ) ) . "' " . esc_attr( $checked ) . " value='on' />";

			break;

		}// End switch().

		if ( isset( $field['desc'] ) ) {
			$html .= '<br><span><i><small>' . esc_html( $field['desc'] ) . '</i></small></span>';
		}

		$html .= '</p>';

		return $html;
	}

	/**
	 * Registering custom CSS for our form.
	 *
	 * @see wp_enqueue_style
	 */
	public function register_admin_styles() {
		wp_enqueue_style( $this->base . '-admin-styles', plugins_url( '../assets/widgets.css', __FILE__ ) );
	}
}
