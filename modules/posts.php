<?php
namespace evans\Posts;

/**
 * Hooks function to fire off the events we need.
 */
public function hooks() {
	add_filter( 'manage_edit-post_columns', __NAMESPACE__ . '\\post_columns' );
	add_action( 'manage_post_posts_custom_column', __NAMESPACE__ . '\\modified_date_column', 10, 2 );
}

/**
 * Remove unnecessary columns from posts & add in a modified date column.
 *
 * @param array $columns Original list of columns and names.
 * @return array Modified list of columns.
 */
public function post_columns( $columns ) {
	// Remove unnecessary columns
	unset( $columns['tags'] );
	unset( $columns['comments'] );
	unset( $columns['wpseo-score'] );

	// Add in our modified columns
	$columns['modified'] 	= 'Last Modified';
	$columns['wpseo-score']	= 'SEO'; // Adding this back in, but at the very end

	return $columns;
}

/**
 * Print out the modified date in the appropriate column.
 *
 * @param string $column ID of current column being parsed.
 * @param int $post_id Post ID of current row.
 */
public function modified_date_column( $column, $post_id ) {
	if ( 'modified' === $column ) {
		echo esc_html( human_time_diff( get_post_modified_time( 'U', true, $post_id ), time() ) );
	}
}
