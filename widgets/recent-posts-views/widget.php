<?php

//Our variables from the widget settings.
$title		= apply_filters('widget_title', $instance['title'] );
$num_posts 	= absint( $instance['num_posts'] );
$category 	= esc_attr( $instance['category'] );
$char_length= absint( $instance['char_length'] );
$cat = '';

// If user specifies number of posts, use. Otherwise, just one post.
if( !empty( $num_posts ) ){
	$posts_per = $num_posts;
} else {
	$posts_per = 1;
}

// If user specifies specific categories, declare. Otherwise use all cats
if ( !empty( $category ) ) {
	$cat = $category;
} else {
	$categories = get_categories();
	foreach ( $categories as $category ) {
		$cat .= $category->slug . ",";
	}
}

// Our loop arguments
$args = array(
	'posts_per_page'	=> $posts_per,
	'category_name'		=> $cat,
    'order'            	=> 'DESC',
    'orderby'			=> 'date',
);

$objects = new WP_Query( $args );

if ( $objects->have_posts() ) :

	// Display the widget title
	if ( $title ){
		echo $before_title . $title . $after_title;
	}

	while ( $objects->have_posts() ) : $objects->the_post();

		echo "<h3>".get_the_title()."</h3>";

		echo apply_filters( 'the_content', wp_trim_words( get_the_content(), '50' ) );

		echo "<a href='".get_permalink()."' class='button'>Read More</a>";

	endwhile;

endif;