<?php
/**
 * Display Contact Information
 *
 * @package   Evans
 * @author    OTM <support@oldtownmediainc.com>
 * @link      https://oldtownmediainc.com
 * @copyright 2015 Old Town Media
 */

class ContactWidget extends Evans_Widget{

	protected $base			= 'contact';
	protected $title		= 'Contact Information';
	protected $description	= 'Display Your Contact Information';

	public function widget_fields(){

		return array(
			array(
				'id'		=> 'title',
				'name'		=> 'Title',
				'type'		=> 'text',
				'default'	=> 'Contact Info'
			),
			array(
				'id'		=> 'address',
				'name'		=> 'Address',
				'desc'		=> 'Street Address',
				'type'		=> 'text',
			),
			array(
				'id'		=> 'city',
				'name'		=> 'City',
				'type'		=> 'text',
				'default'	=> 'Fort Collins'
			),
			array(
				'id'		=> 'state',
				'name'		=> 'State',
				'type'		=> 'text',
				'default'	=> 'CO'
			),
			array(
				'id'		=> 'zip',
				'name'		=> 'ZIP',
				'type'		=> 'text',
				'default'	=> '80521'
			),
			array(
				'id'		=> 'phone',
				'name'		=> 'Phone',
				'type'		=> 'text',
				'default'	=> '(970) 123-4567'
			),
			array(
				'id'		=> 'fax',
				'name'		=> 'Fax',
				'type'		=> 'text',
				'default'	=> ''
			),
			array(
				'id'		=> 'email',
				'name'		=> 'Email',
				'type'		=> 'text',
				'sanitize'	=> 'sanitize_email',
				'default'	=> 'me@thissite.com'
			),
			array(
				'id'		=> 'map',
				'name'		=> 'Map?',
				'desc'		=> 'Choose whether to show a map underneath the info or not.',
				'type'		=> 'checkbox',
				'default'	=> 'on'
			)
		);

	}

	public function view( $args, $instance ){
		$html = $address_string = '';

		if ( !empty( $instance['title'] ) ){
			$html .= $args['before_title'] . apply_filters('widget_title', $instance['title'] ) . $args['after_title'];
		}

		// Display the contact information
		$html .= "<p>";

			if ( !empty( $instance['address'] ) ){
				$address_string .= esc_attr( $instance['address'] )."<br>";
			}

			if ( !empty( $instance['city'] ) ){
				$address_string .= esc_attr( $instance['city'] ).", ";
			}

			if ( !empty( $instance['state'] ) ){
				$address_string .= esc_attr( $instance['state'] )." ";
			}

			if ( !empty( $instance['zip'] ) ){
				$address_string .= esc_attr( $instance['zip'] );
			}

			// echo the address string
			if ( !empty( $address_string ) ){
				$html .= $address_string."<br>";
			}

			if ( !empty( $instance['phone'] ) ){
				$html .= "<strong>Phone:</strong> ".esc_attr( $instance['phone'] )."<br>";
			}

			if ( !empty( $instance['fax'] ) ){
				$html .= "<strong>Fax:</strong> ".esc_attr( $instance['fax'] )."<br>";
			}

			if ( !empty( $instance['email'] ) ){
				$html .= "<a href='mailto:".sanitize_email( $instance['email'] )."'>".sanitize_email( $instance['email'] )."</a><br>";
			}

		$html .= "</p>";

		// If map is checked, display the map using Simple Google Maps Short Code
		if ( $instance['map'] == 'on' && !empty( $address_string ) ){
			$html .= do_shortcode('[pw_map address="'.$address_string.'"]');
		}

		return $html;

	}

}

add_action( 'widgets_init', create_function( '', 'register_widget("ContactWidget");' ) );
