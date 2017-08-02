<?php
namespace evans;

/**
 * Abstract lass to quickly and easily generate widgets.
 *
 * @abstract
 * @package    WordPress
 * @subpackage Evans
 * @author     Old Town Media
 */
abstract class Widget extends \WP_Widget {

	/**
	 * Base for widget - to be used in referencing files, folders, creating a
	 * class, etc.
	 *
	 * @var string
	 */
	protected $base;

	/**
	 * Title for the widget choice box.
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * Description of the widget.
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * Constructor function
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
	} // end constructor

	/**
	 * Flushed the cache of the widget.
	 */
	public function flush_widget_cache() {
		wp_cache_delete( $this->base, 'widget' );
	}

	/**
	 * Check cache for the widget & display.
	 *
	 * @param type $args Base widget arguments.
	 * @param type $instance The data from the widget editing.
	 */
	public function widget( $args, $instance ) {
		// Check if there is a cached output
		$cache = wp_cache_get( $this->base, 'widget' );

		if ( ! is_array( $cache ) ) :
			$cache = [];
		endif;

		if ( ! isset( $args['widget_id'] ) ) :
			$args['widget_id'] = $this->id;
		endif;

		if ( isset( $cache[ $args['widget_id'] ] ) ) :
			print $cache[ $args['widget_id'] ];
		endif;

		$widget_string = $before_widget;

		$widget_string .= $this->view( $args, $instance );
		$widget_string .= $after_widget;

		if ( isset( $args['widget_id'] ) ) :

			$cache[ $args['widget_id'] ] = $widget_string;
			wp_cache_set( $this->base, $cache, 'widget' );

		endif;

		print $widget_string;
	}

	/**
	 * Handle the data from the updated widget and parse against the old data.
	 *
	 * @param array $new_instance Updated data.
	 * @param array $old_instance Old data.
	 * @return array New, parsed instance.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$fields = $this->widget_fields();

		if ( empty( $fields ) ) :
			return $instance;
		endif;

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
	 * @param array $field Field options.
	 * @param variable $value Input value.
	 * @return variable Sanitized value.
	 */
	private function sanitize_field( $field, $value ) {
		if ( empty( $field ) ) :
			return;
		endif;

		// If the user has chose a custom sanitization
		if ( isset( $field['sanitize'] ) ) :
			$sanitize = $field['sanitize'];

			// If the user has chosen an array
			if ( is_array( $sanitize ) ) :
				$new_value = '';

				foreach ( $sanitize as $filter ) {
					$new_value = $filter( $value );
				}

				$sanitized = $new_value;

				// Else if we're just dealing one sanitization option
			else:

				$sanitized = $sanitize( $value );

			endif;
		 else :

			$sanitized = strip_tags( $value );

		endif;

		return $sanitized;
	}

	/**
	 * Build and print our admin form for editing the widget
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
		if ( $instance['title'] ) :
			return $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		endif;
	}

	/**
	 * Gets the default values for use in our form from each field.
	 *
	 * @access private
	 *
	 * @return array Default values to parse against.
	 */
	private function get_defaults() {
		$fields = $this->widget_fields();
		$defaults = [];

		if ( ! is_array( $fields ) || empty( $fields ) ) :
			return;
		endif;

		foreach ( $fields as $field ) {

			if ( isset( $field['default'] ) ) :
				$defaults[ $field['id'] ] = $field['default'];
			 else :
				$defaults[ $field['id'] ] = '';
			endif;
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
		ob_start();

		$fields = $this->widget_fields();

		if ( empty( $fields ) ) :
			return;
		endif;

		// Loop through the fields and build an HTML form field
		foreach ( $fields as $field ) {
			echo $this->build_form_field( $field, $instance );
		}

		return ob_get_clean();
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
	 * @param array $field Field information.
	 * @param array $instance Data from the widget.
	 * @return string Compiled field.
	 */
	private function build_form_field( $field, $instance ) {
		ob_start();

		if ( empty( $field ) ) :
			return;
		endif; ?>

		<p class="<?php echo esc_attr( $field['type'] ); ?>-box evans-field">

		<label for="<?php echo esc_attr( $this->get_fied_id( $field['id'] ) ); ?>"><strong><?php echo esc_attr( $field['name'] )?></strong></label>

		<?php
		if ( 'checkbox' !== $field['type'] ) : ?>
			<br>
	<?php	endif;

		switch ( $field['type'] ) {

			case 'text' : ?>

				<input id="<?php echo esc_attr( $this->get_field_id( $field['id'] ) );?>" name="<?php echo esc_attr( $this->get_field_name( $field['id'] ) );?>" value="<?php echo esc_attr( $instance[ $field['id'] ] );?>">

			<?php
			break;

			case 'textarea' : ?>

				<textarea id="<?php echo esc_attr( $this->get_field_id( $field['id'] ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $field['id'] ) );?>"><?php echo esc_html( $instance[ $field['id'] ] );?></textarea>

			<?php

			break;

			case 'select' :

				if ( ! empty( $field['options'] ) ) : ?>

					<select id="<?php echo esc_attr( $this->get_field_id( $field['id'] ) ); ?>" name ="<?php echo esc_attr( $this->get_field_name( $field['id'] ) ); ?>">

					<?php
					foreach ( $field['options'] as $key => $value ) {
						$selected = '';

						if ( $key === $instance[ $field['id'] ] ) :
							$selected = 'selected';
						endif; ?>

						<option value="<?php echo esc_attr( $key );?> <?php echo esc_attr( $selected ) ?>">

						<?php esc_html( $value ); ?>

						</option>
					<?php } ?>

				</select>

			<?php endif;

			break;

			case 'radio' :

				if ( ! empty( $field['options'] ) ) :

					foreach ( $field['options'] as $key => $value ) {
						$selected = '';

						if ( $key === $instance[ $field['id'] ] ) :
							$selected = ' checked"';
						endif; ?>

						<input type="radio" value="<?php echo esc_attr( $key ); ?> <?php echo esc_attr( $selected ); ?>"><span><?php echo esc_html( $value ); ?></span><br>

						<?php

					}
				endif;

			break;

			case 'checkbox' :
				$checked = '';

				if ( 'on' === $instance[ $field['id'] ] ) :
					$checked = ' checked';
				endif; ?>

				<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( $field['id'] ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $field['id'] ) );?> <?php echo esc_attr( $checked );?>" value="on" />

			<?php
			break;

		}// End switch().

		if ( isset( $field['desc'] ) ) : ?>
			<br><span><i><small><?php echo esc_html( $field['desc'] );?></i></small></span>
		<?php endif; ?>

		</p>

		<?php
		return ob_get_clean();
	}

	/**
	 * Registering custom CSS for our form.
	 */
	public function register_admin_styles() {
		wp_enqueue_style( $this->base . '-admin-styles', plugins_url( '../assets/widgets.css', __FILE__ ) );
	}
}
