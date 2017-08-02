<?php
namespace evans;

/**
 * Display contact information in a widget.
 *
 * @package   Evans
 * @author    OTM <support@oldtownmediainc.com>
 * @link      https://oldtownmediainc.com
 * @copyright 2015 Old Town Media
 */
final class ContactWidget extends Widget {

	protected $base			= 'contact';
	protected $title		= 'Contact Information';
	protected $description	= 'Display Your Contact Information';

	/**
	 * Array of fields for the admin editing of the widget.
	 *
	 * @return array list of fields
	 */
	public function widget_fields() {
		return [
			[
				'id'      => 'title',
				'name'    => __( 'Title', 'evans-mu' ),
				'type'    => 'text',
				'default' => __( 'Contact Info', 'evans-mu' ),
			],
			[
				'id'   => 'address',
				'name' => __( 'Address', 'evans-mu' ),
				'desc' => __( 'Street Address', 'evans-mu' ),
				'type' => 'text',
			],
			[
				'id'      => 'city',
				'name'    => __( 'City', 'evans-mu' ),
				'type'    => 'text',
				'default' => __( 'Fort Collins', 'evans-mu' ),
			],
			[
				'id'      => 'state',
				'name'    => __( 'State', 'evans-mu' ),
				'type'    => 'text',
				'default' => __( 'CO', 'evans-mu' ),
			],
			[
				'id'      => 'zip',
				'name'    => __( 'ZIP', 'evans-mu' ),
				'type'    => 'text',
				'default' => '80521',
			],
			[
				'id'      => 'phone',
				'name'    => __( 'Phone', 'evans-mu' ),
				'type'    => 'text',
				'default' => '(970) 123-4567',
			],
			[
				'id'      => 'fax',
				'name'    => __( 'Fax', 'evans-mu' ),
				'type'    => 'text',
				'default' => '',
			],
			[
				'id'       => 'email',
				'name'     => __( 'Email', 'evans-mu' ),
				'type'     => 'text',
				'sanitize' => 'sanitize_email',
				'default'  => 'me@thissite.com',
			],
			[
				'id'      => 'map',
				'name'    => __( 'Map?', 'evans-mu' ),
				'desc'    => __( 'Choose whether to show a map underneath the info or not.', 'evans-mu' ),
				'type'    => 'checkbox',
				'default' => 'on',
			],
		];
	}

	/**
	 * The front-end view of the widget
	 *
	 * @param array $args Base widget data such as before_title.
	 * @param arry $instance Widget data.
	 * @return string Widget HTML.
	 */
	public function view( $args, $instance ) {
		 $address_string = '';
		ob_start();
		echo $this->get_widget_title( $args, $instance ); ?>

		// Display the contact information
		<p><?php

		if ( ! empty( $instance['address'] ) ) :
			$address_string .= esc_html( $instance['address'] ) . '<br>';
		endif;

		if ( ! empty( $instance['city'] ) ) :
			$address_string .= esc_html( $instance['city'] ) . ', ';
		endif;

		if ( ! empty( $instance['state'] ) ) :
			$address_string .= esc_html( $instance['state'] ) . ' ';
		endif;

		if ( ! empty( $instance['zip'] ) ) :
			$address_string .= esc_html( $instance['zip'] );
		endif;

			// echo the address string
		if ( ! empty( $address_string ) ) :
			echo $address_string . '<br>';
		endif;

		if ( ! empty( $instance['phone'] ) ) : ?>
			<strong><?php echo esc_html__( 'Phone:', 'evans-mu' );?></strong><?php echo esc_html( $instance['phone'] ); ?><br>
		<?php endif;

		if ( ! empty( $instance['fax'] ) ) : ?>
			<strong><?php echo esc_html__( 'Fax:', 'evans-mu' );?></strong><?php echo esc_html( $instance['fax'] ); ?><br>
		<?php endif;

		if ( ! empty( $instance['email'] ) ) : ?>
			<a href='mailto:<? echo sanitize_email( $instance['email'] );?>'><?php echo sanitize_email( $instance['email'] ); ?></a><br>
		<?php endif; ?>

	</p> <?php

		// If map is checked, display the map using Simple Google Maps Short Code
		if ( 'on' === $instance['map'] && ! empty( $address_string ) && function_exists( 'pw_map_shortcode' ) ) :
			echo do_shortcode( '[pw_map address="' . $address_string . '"]' );
		endif;

		return ob_get_clean();
	}
}

add_action( 'widgets_init', function() {
	 register_widget( __NAMESPACE__ . '\ContactWidget' );
} );
