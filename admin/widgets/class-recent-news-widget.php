<?php
/**
 * Front-end most recent posts widget.
 *
 * @package    WordPress
 * @subpackage evans
 */

namespace evans\Widgets;

use evans\Abstracts;

/**
 * Display the Most Recent News in a widget.
 */
final class RecentNewsWidget extends Abstracts\Widget {
	protected $base			= 'recent-news';
	protected $title		= 'Recent News';
	protected $description	= 'Display the most recent posts from your blog.';

	/**
	 * Array of fields for the admin editing of the widget.
	 *
	 * @return array list of fields
	 */
	public function widget_fields() {
		// Get list of categories to choose from
		$categories = get_categories();
		$cat_array = [
			'' => 'Select a Category',
		];

		foreach ( $categories as $category ) {
			$cat_array[ $category->slug ] = $category->cat_name;
		}

		return [
			[
				'id'      => 'title',
				'name'    => __( 'Title', 'evans-mu' ),
				'type'    => 'text',
				'default' => 'Recent News',
			],
			[
				'id'      => 'category',
				'name'    => __( 'Category', 'evans-mu' ),
				'desc'    => 'optional',
				'type'    => 'select',
				'options' => $cat_array,
			],
			[
				'id'   => 'num_posts',
				'name' => __( 'Number of Posts', 'evans-mu' ),
				'desc' => 'optional',
				'type' => 'text',
			],
			[
				'id'   => 'word_length',
				'name' => __( 'Number of Words per Post to Display', 'evans-mu' ),
				'desc' => 'optional',
				'type' => 'text',
			],
		];
	}

	/**
	 * The front-end view of the widget
	 *
	 * @param array $args Base widget data such as before_title.
	 * @param arry $instance Widget data.
	 * @return string Widget HTML.
	 */
	public function view( $args, $instance ) {
		$html = '';

		if ( isset( $instance['num_posts'] ) && ! empty( $instance['num_posts'] ) ) {
			$posts_per = absint( $instance['num_posts'] );
		} else {
			$posts_per = 1;
		}

		// If user specifies specific categories, declare. Otherwise use all cats
		if ( isset( $instance['category'] ) && ! empty( $instance['category'] ) ) {
			$cat = esc_attr( $instance['category'] );
		} else {
			$cat = '';
			$categories = get_categories();
			foreach ( $categories as $category ) {
				$cat .= $category->slug . ',';
			}
		}

		if ( isset( $instance['word_length'] ) && ! empty( $instance['word_length'] ) ) {
			$word_length = $instance['word_length'];
		} else {
			$word_length = 50;
		}

		// Our loop arguments
		$query = [
			'posts_per_page' => $posts_per,
			'category_name'  => $cat,
			'order'          => 'DESC',
			'orderby'        => 'date',
		];

		$objects = new \WP_Query( $query );

		if ( $objects->have_posts() ) :

			$html .= $this->get_widget_title( $args, $instance );

			while ( $objects->have_posts() ) : $objects->the_post();

				$html .= '<h3>' . esc_html( get_the_title() ) . '</h3>';

				$html .= apply_filters( 'the_content', wp_trim_words( get_the_content(), $word_length ) );

				$html .= "<a href='" . esc_url( get_permalink() ) . "' class='button' role='button'>" . esc_html__( 'Read More', 'evans-mu' ) . '</a>';

			endwhile;

		endif;

		return $html;
	}
}

add_action( 'widgets_init', function() {
	 register_widget( __NAMESPACE__ . '\RecentNewsWidget' );
} );
