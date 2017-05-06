<?php
/**
 * Dashboard RSS Feed from OTM Site widget.
 *
 * @package    WordPress
 * @subpackage evans
 */

namespace evans\Widgets;

use evans\Abstracts;

/**
 * CompanyRSSFeedWidget
 *
 * Calls up the feed URL and the most recent 5 posts from the feed for display
 * in the admin section
 */
final class CompanyRSSFeedWidget extends Abstracts\Dashboard_Widget {

	/**
	 * ID used in the class and as a slug.
	 *
	 * @var string
	 */
	protected $id = 'rss_widget';

	/**
	 * Title for the dashboard widget.
	 *
	 * @var string
	 */
	protected $title = 'Latest News';

	/**
	 * URL of the feed you want to hook into.
	 *
	 * @var string
	 */
	protected $feed_url = 'https://oldtownmediainc.com/feed/';

	/**
	 * The number of posts to show.
	 *
	 * @var int
	 */
	protected $num_posts = 5;


	/**
	 * Build our widget and output the posts.
	 *
	 * Look for our RSS feed, parse for errors, and then loop through the results.
	 */
	public function build_the_widget() {
		 $rss = fetch_feed( $this->feed_url );

		 // Look for errors and outut if necessary
		 $errors = $this->parse_for_errors( $rss );

		if ( ! empty( $errors ) ) {
			echo esc_html( $errors );
			return;
		}

		// Start the output
		echo "<ul>\n";

		// Loop through posts
		foreach ( $rss->get_items( 0, $this->num_posts ) as $item ) {

			echo "<li class='rss-widget'>";
				echo "<a class='rsswidget' href='" . esc_url( strip_tags( $item->get_link() ) ) . "'>" . esc_html( $item->get_title() ) . '</a>';
				echo "<span class='rss-date'> - " . esc_html( $item->get_date( 'F jS, Y' ) ) . "</span>\n";
				echo "<div class='rssSummary'>" . wp_kses_post( wp_html_excerpt( $item->get_content(), 350 ) ) . "...</div>\n\r\n";
			echo '</li>';

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
	private function parse_for_errors( $rss ) {
		$html = '';

		// If there is an error in receiving the posts
		if ( is_wp_error( $rss ) ) {

			if ( is_admin() || current_user_can( 'manage_options' ) ) {
				$html .= '<p>';
					$html .= '<strong>' . esc_html__( 'RSS Error: ', 'evans-mu' ) . '</strong>' . esc_html( $rss->get_error_message() );
				$html .= '</p>';
			}

			return $html;
		}

		// If there are no posts to show
		if ( ! $rss->get_item_quantity() ) {
			 $html .= '<p>' . esc_html__( 'Apparently, there are no updates to show!', 'evans-mu' ) . '</p>';
			 $rss->__destruct();
			 unset( $rss );
		}

		return $html;
	}
}

$widget = new CompanyRSSFeedWidget();
$widget->hooks();
