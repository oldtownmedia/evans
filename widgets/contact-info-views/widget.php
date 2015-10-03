<?php

//Our variables from the widget settings.
$title		= apply_filters('widget_title', $instance['title'] );
$address1 	= esc_attr( $instance['address1'] );
$address2 	= esc_attr( $instance['address2'] );
$city		= esc_attr( $instance['city'] );
$state 		= esc_attr( $instance['state'] );
$zip 		= esc_attr( $instance['zip'] );
$phone 		= esc_attr( $instance['phone'] );
$fax 		= esc_attr( $instance['fax'] );
$email 		= sanitize_email( $instance['email'] );
$map		= $instance['map'];

$address_string = '';

// Display the widget title
if ( !empty( $title ) ){
	echo $before_title . $title . $after_title;
}

// Display the contact information
echo "<p>";

	if ( !empty( $address1 ) ){
		$address_string .= "$address1<br>";
	}

	if ( !empty( $city ) ){
		$address_string .= "$city, ";
	}

	if ( !empty( $state ) ){
		$address_string .= "$state ";
	}

	if ( !empty( $zip ) ){
		$address_string .= "$zip";
	}

	// echo the address string
	if ( !empty( $address_string ) ){
		echo $address_string."<br>";
	}

	if ( !empty( $phone ) ){
		echo "Phone: $phone<br>";
	}

	if ( !empty( $fax ) ){
		echo "Fax: $fax<br>";
	}

	if ( !empty( $email ) ){
		echo "Email: <a href='mailto:$email'>$email</a><br>";
	}

echo "</p>";

// If map is checked, display the map using Simple Google Maps Short Code
if ( $map == 'true' && !empty( $address_string ) ){
	$address_string = $address1 ." ". $address_string;
	echo do_shortcode('[pw_map address="'.$address_string.'"]');
}