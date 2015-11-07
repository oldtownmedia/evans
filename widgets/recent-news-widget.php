<?php
namespace evans;

/**
 * Display the Most Recent News in a widget.
 *
 * @package   Evans
 * @author    OTM <support@oldtownmediainc.com>
 * @link      https://oldtownmediainc.com
 * @copyright 2015 Old Town Media
 */
class RecentNewsWidget extends Evans_Widget{

	protected $base			= 'recent-news';
	protected $title		= 'Recent News';
	protected $description	= 'Display the most recent posts from your blog.';


	/**
	 * Array of fields for the admin editing of the widget.
	 *
	 * @return array list of fields
	 */
	public function widget_fields(){

		// Get list of categories to choose from
		$categories = get_categories();
		$cat_array = array( '' => 'Select a Category' );

		foreach ( $categories as $category ){
			$cat_array[ $category->slug ] = $category->cat_name;
		}

		return array(
			array(
				'id'		=> 'title',
				'name'		=> 'Title',
				'type'		=> 'text',
				'default'	=> 'Contact Info'
			),
			array(
				'id'		=> 'category',
				'name'		=> 'Category',
				'desc'		=> 'optional',
				'type'		=> 'select',
				'options'	=> $cat_array
			),
			array(
				'id'		=> 'num_posts',
				'name'		=> 'Number of Posts',
				'desc'		=> 'optional',
				'type'		=> 'text',
			),
			array(
				'id'		=> 'char_length',
				'name'		=> 'Snippet Length in characters',
				'desc'		=> 'optional',
				'type'		=> 'text',
			)
		);

	}


	/**
	 * The front-end view of the widget
	 *
	 * @param array $args Base widget data such as before_title.
	 * @param arry $instance Widget data.
	 * @return string Widget HTML.
	 */
	public function view( $args, $instance ){
		$html  = $cat = '';

		if( isset( $instance['num_posts'] ) && !empty( $instance['num_posts'] ) ){
			$posts_per = absint( $instance['num_posts'] );
		} else {
			$posts_per = 1;
		}

		// If user specifies specific categories, declare. Otherwise use all cats
		if ( isset( $instance['category'] ) ) {
			$cat = esc_attr( $instance['category'] );
		} else {
			$categories = get_categories();
			foreach ( $categories as $category ) {
				$cat .= $category->slug . ",";
			}
		}

		// Our loop arguments
		$query = array(
			'posts_per_page'	=> $posts_per,
			'category_name'		=> $cat,
		    'order'            	=> 'DESC',
		    'orderby'			=> 'date',
		);

		$objects = new \WP_Query( $query );

		if ( $objects->have_posts() ) :

			$html .= $this->get_widget_title( $args, $instance );

			while ( $objects->have_posts() ) : $objects->the_post();

				$html .= "<h3>".get_the_title()."</h3>";

				$html .= apply_filters( 'the_content', wp_trim_words( get_the_content(), '50' ) );

				$html .= "<a href='".get_permalink()."' class='button'>Read More</a>";

			endwhile;

		endif;

		return $html;

	}

}

add_action( 'widgets_init', create_function( '', 'register_widget("'.__NAMESPACE__.'\RecentNewsWidget");' ) );
