<?php
namespace evans;

/**
 * Posts
 *
 * Modification/adjustments to the Post post type.
 *
 * @package    WordPress
 * @subpackage Evans
 * @author     Old Town Media
 */
class Posts{

	/**
	 * Hooks function to fire off the events we need.
	 *
	 * @see add_action, add_filter, add_shortcode
	 */
	public function hooks(){

		add_filter( 'manage_edit-post_columns', array( $this, 'post_columns' ) );
		add_action( 'manage_post_posts_custom_column', array( $this, 'modified_date_column' ), 10, 2 );

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
	 * @see human_time_diff, get_post_modified_time
	 *
	 * @param string $column ID of current column being parsed.
	 * @param int $post_id Post ID of current row.
	 */
	public function modified_date_column( $column, $post_id ) {

		if ( $column == 'modified' ){
			echo human_time_diff( get_post_modified_time( 'U', true, $post_id ), time() );
		}

	}

}


/*
 * Instantiate the hooks method
 */
$staff = new Posts;
$staff->hooks();