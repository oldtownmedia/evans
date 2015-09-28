<?php

//Our variables from the widget settings.
$title		= apply_filters('widget_title', $instance['title'] );
$address1 	= $instance['address1'];
$address2 	= $instance['address2'];
$city		= $instance['city'];
$state 		= $instance['state'];
$zip 		= $instance['zip'];
$phone 		= $instance['phone'];
$fax 		= $instance['fax'];
$email 		= $instance['email'];
$map		= $instance['map'];

	// Display the widget title
	if ( $title )
		echo $before_title . $title . $after_title;

	// Display the contact information
	echo "<p>";

		if ( $address1 )
			echo "$address1<br>";

		if ( $address2 )
			echo "$address2<br>";

		if ( $city )
			$address_string .= "$city, ";

		if ( $state )
			$address_string .= "$state ";

		if ( $zip )
			$address_string .= "$zip";

		// echo the built string
		echo $address_string."<br>";

		if ( $phone )
			echo "Phone: $phone<br>";

		if ( $fax )
			echo "Fax: $fax<br>";

		if ( $email )
			echo "Email: <a href='mailto:$email'>$email</a><br>";

	echo "</p>";

	if ( $map == 'true' ){
		$address_string = $address1 ." ". $address2 ." ". $address_string;
		echo do_shortcode('[pw_map address="'.$address_string.'"]');
	}

?>