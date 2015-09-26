<?php
defined( 'ABSPATH' ) OR exit;

function dashboard_widget_function() {
     $rss = fetch_feed( "http://oldtownmediainc.com/feed/" );

     if ( is_wp_error( $rss ) ) {

          if ( is_admin() || current_user_can( 'manage_options' ) ) {
               echo '<p>';
	               printf( __( '<strong>RSS Error</strong>: %s', 'otm-mu' ), $rss->get_error_message() );
               echo '</p>';
          }

     return;

	}

	if ( !$rss->get_item_quantity() ) {
	     echo '<p>'.__( 'Apparently, there are no updates to show!', 'otm-mu' ).'</p>';
	     $rss->__destruct();
	     unset( $rss );
	     return;
	}

	echo "<ul>\n";

	if ( !isset($items) ){
	     $items = 5;
	}

	foreach ( $rss->get_items(0, $items) as $item ) {

		$publisher = $site_link = $link = $content = '';

		$date = $item->get_date();
		$link = esc_url( strip_tags( $item->get_link() ) );
		$title = esc_html( $item->get_title() );
		$content = $item->get_content();
		$content = wp_html_excerpt( $content, 350 ) . '...';

		echo "<li class='rss-widget'><a class='rsswidget' href='$link'>$title</a><span class='rss-date'> - $date</span>\n<div class='rssSummary'>$content</div>\n\r\n";

	}

	echo "</ul>\n";

	$rss->__destruct();
	unset( $rss );

}

function add_dashboard_widget() {
     wp_add_dashboard_widget( 'rss_widget', 'Recent Posts from Old Town Media', 'dashboard_widget_function' );
}

add_action( 'wp_dashboard_setup', 'add_dashboard_widget' );


?>