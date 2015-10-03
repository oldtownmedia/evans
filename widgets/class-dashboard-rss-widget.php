<?php

class CompanyRSSFeedWidget extends Dashboard_Widget{

	// Defaults
	protected $id 		= 'rss_widget';
	protected $title 	= 'Latest News';

	// Specific widget info
	protected $feed_url		= 'https://oldtownmediainc.com/feed/';
	protected $num_posts	= 5;

	/**
	 * Build our widget and output the posts.
	 *
	 * Look for our RSS feed, parse for errors, and then loop through the results.
	 */
	function build_the_widget() {
	     $rss = fetch_feed( $this->feed_url );

		 // Look for errors and outut if necessary
		 $errors = $this->parse_for_errors( $rss );

		 if ( !empty( $errors ) ){
			 echo $errors;
			 return;
		 }

		// Start the output
		echo "<ul>\n";

		// Loop through posts
		foreach ( $rss->get_items( 0, $this->num_posts ) as $item ) {

			$publisher = $site_link = $link = $content = '';

			$date = $item->get_date( 'F jS, Y' );
			$link = esc_url( strip_tags( $item->get_link() ) );
			$title = esc_html( $item->get_title() );
			$content = wp_html_excerpt( $item->get_content(), 350 ) . '...';

			echo "<li class='rss-widget'><a class='rsswidget' href='$link'>$title</a><span class='rss-date'> - $date</span>\n<div class='rssSummary'>$content</div>\n\r\n";

		}

		echo "</ul>\n";

		$rss->__destruct();
		unset( $rss );

	}

	/**
	 * Parse our RSS feed for errors and return them if there are any.
	 *
	 * @access private
	 *
	 * @see is_wp_error, is_admin, current_user_can
	 *
	 * @param object $rss RSS object to parse for errors
	 * @return string Error if there is one.
	 */
	private function parse_for_errors( $rss ){
		$html = '';

		// If there is an error in receiving the posts
	    if ( is_wp_error( $rss ) ) {

	        if ( is_admin() || current_user_can( 'manage_options' ) ) {
	            $html .=  '<p>';
		            $html .= sprintf( __( '<strong>RSS Error</strong>: %s', 'evans-mu' ), $rss->get_error_message() );
	            $html .=  '</p>';
	        }

	        return $html;

		}

		// If there are no posts to show
		if ( !$rss->get_item_quantity() ) {
		     $html .=  '<p>'.__( 'Apparently, there are no updates to show!', 'evans-mu' ).'</p>';
		     $rss->__destruct();
		     unset( $rss );
		}

		return $html;

	}

}

$widget = new CompanyRSSFeedWidget();
$widget->hooks();

?>