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
final class ContactWidget extends Widget{

	protected $base			= 'contact';
	protected $title		= 'Contact Information';
	protected $description	= 'Display Your Contact Information';


	/**
	 * Array of fields for the admin editing of the widget.
	 *
	 * @return array list of fields
	 */
	public function widget_fields(){

		return array(
			array(
				'id'		=> 'title',
				'name'		=> __( 'Title', 'evans-mu' ),
				'type'		=> 'text',
				'default'	=> __( 'Contact Info', 'evans-mu' )
			),
			array(
				'id'		=> 'address',
				'name'		=> __( 'Address', 'evans-mu' ),
				'desc'		=> __( 'Street Address', 'evans-mu' ),
				'type'		=> 'text',
			),
			array(
				'id'		=> 'city',
				'name'		=> __( 'City', 'evans-mu' ),
				'type'		=> 'text',
				'default'	=> __( 'Fort Collins', 'evans-mu' )
			),
			array(
				'id'		=> 'state',
				'name'		=> __( 'State', 'evans-mu' ),
				'type'		=> 'text',
				'default'	=> __( 'CO', 'evans-mu' )
			),
			array(
				'id'		=> 'zip',
				'name'		=> __( 'ZIP', 'evans-mu' ),
				'type'		=> 'text',
				'default'	=> '80521'
			),
			array(
				'id'		=> 'phone',
				'name'		=> __( 'Phone', 'evans-mu' ),
				'type'		=> 'text',
				'default'	=> '(970) 123-4567'
			),
			array(
				'id'		=> 'fax',
				'name'		=> __( 'Fax', 'evans-mu' ),
				'type'		=> 'text',
				'default'	=> ''
			),
			array(
				'id'		=> 'email',
				'name'		=> __( 'Email', 'evans-mu' ),
				'type'		=> 'text',
				'sanitize'	=> 'sanitize_email',
				'default'	=> 'me@thissite.com'
			),
			array(
				'id'		=> 'map',
				'name'		=> __( 'Map?', 'evans-mu' ),
				'desc'		=> __( 'Choose whether to show a map underneath the info or not.', 'evans-mu' ),
				'type'		=> 'checkbox',
				'default'	=> 'on'
			)
		);

	}


	/**
	 * The front-end view of the widget
	 *
	 * @param array $args Base widget data such as before_title.
	 * @param arry $instance Widget data.
	 * @return string Widget HTML.
	 */
	public function view( $args, $instance ){
		$html = $address_string = '';

		$html .= $this->get_widget_title( $args, $instance );

		// Display the contact information
		$html .= "<p>";

			if ( ! empty( $instance['address'] ) ){
				$address_string .= esc_html( $instance['address'] ) . "<br>";
			}

			if ( ! empty( $instance['city'] ) ){
				$address_string .= esc_html( $instance['city'] ) . ", ";
			}

			if ( ! empty( $instance['state'] ) ){
				$address_string .= esc_html( $instance['state'] ) . " ";
			}

			if ( ! empty( $instance['zip'] ) ){
				$address_string .= esc_html( $instance['zip'] );
			}

			// echo the address string
			if ( ! empty( $address_string ) ){
				$html .= $address_string . "<br>";
			}

			if ( ! empty( $instance['phone'] ) ){
				$html .= "<strong>" . esc_html__( 'Phone:', 'evans-mu' ) . "</strong> " . esc_html( $instance['phone'] ) . "<br>";
			}

			if ( ! empty( $instance['fax'] ) ){
				$html .= "<strong>" . esc_html__( 'Fax:', 'evans-mu' )."</strong> " . esc_html( $instance['fax'] ) . "<br>";
			}

			if ( ! empty( $instance['email'] ) ){
				$html .= "<a href='mailto:" . sanitize_email( $instance['email'] ) . "'>" . sanitize_email( $instance['email'] ) . "</a><br>";
			}

		$html .= "</p>";

		// If map is checked, display the map using Simple Google Maps Short Code
		if ( $instance['map'] == 'on' && ! empty( $address_string ) ){
			$html .= do_shortcode('[pw_map address="'.$address_string.'"]');
		}

		return $html;

	}

}

add_action( 'widgets_init', function(){
     register_widget( __NAMESPACE__ . '\ContactWidget' );
} );
