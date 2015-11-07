<?php
namespace evans;

/**
 * Class to build test data for custom post types.
 */
class BuildTestData{

	/**
	 * Hooks function
	 *
	 * This function is used to avoid loading any unnecessary functions/code
	 *
	 * @see admin_menu, wp_ajax actions
	 */
	public function hooks(){

		add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );
		add_action( 'wp_ajax_handle_test_data', array( $this, 'handle_test_data_callback' ) );

	}

	/**
	 * Add the admin-side menu item for creating & deleting test data.
	 *
	 * @see add_submenu_page
	 */
	public function add_menu_item() {

		$page = add_submenu_page(
			'tools.php',
			__( 'Create Test Data', 'evans-mu' ),
			__( 'Test Data', 'evans-mu' ),
			'manage_options',
			'evans_mu-test_data',
			array( $this, 'admin_page' )
		);

	}

	/**
	 * Ajax callback function for triggering the creatin & deletion of test data.
	 *
	 * @see wp_ajax filter, $this->add_menu_item
	 */
	public function handle_test_data_callback() {

		$cptslug	= $_REQUEST['cptslug'];
		$action		= $_REQUEST['todo'];
		$nonce		= $_REQUEST['nonce'];

		// Verify that we have a proper logged in user and it's the right person
		if ( !empty( $nonce ) && wp_verify_nonce( $nonce, 'handle-test-data' ) ){

			if ( $action == 'delete' ){

				$this->delete_test_content( $cptslug );
				echo "Deleted " . $cptslug ."s";

			} elseif ( $action == 'create' ){

				$this->create_post_type_content( $cptslug );
				echo "Created " . $cptslug ."s";

			}

		} // end nonce check

		die();

	}

	/**
	 * Print out our admin page to control test data.
	 */
	public function admin_page(){

		$html = "";

		$html .= '<div class="wrap" id="options_editor">' . "\n";

			$html .= '<h2>' . __( 'Create Test Data' , 'evans-mu' ) . '</h2>' . "\n";

			// Loop through all other cpts
			$post_types = get_post_types( array( 'public' => true ), 'objects' );

			foreach ( $post_types as $post_type ) {

				// Skip Attachments
				if ( $post_type->name == 'attachment' ){
					continue;
				}

				$html .= "<div class='test-data-cpt'>";

					$html .= "<h3>";

						$html .= "<span style='width: 20%; display: inline-block;'>" . $post_type->labels->name . "</span>";
						$html .= " <a href='javascript:void(0);' data-cpt='".$post_type->name."' data-todo='create' class='button-primary handle-test-data' /><span class='dashicons dashicons-plus' style='margin-top: 6px; font-size: 1.2em'></span> Create Test Data</a>";
						$html .= " <a href='javascript:void(0);' data-cpt='".$post_type->name."' data-todo='delete' class='button-primary handle-test-data' /><span class='dashicons dashicons-trash' style='margin-top: 4px; font-size: 1.2em'></span> Delete Test Data</a>";

					$html .= "</h3>";

				$html .= "</div>";

			}

		$html .= "</div>";

			ob_start();

			?>

			<script type='text/javascript'>
				jQuery(document).ready(function($) {

					jQuery( '.handle-test-data' ).on( 'click', function(){

						$( this ).after( '<img src="<?php echo plugins_url( '../assets/images/loading.gif', __FILE__ ); ?>" class="loading-icon" style="height: 20px; margin-bottom: -4px; margin-left: 4px;">' );

						var data = {
							'action' : 'handle_test_data',
							'todo' : jQuery( this ).data( 'todo' ),
							'cptslug' : jQuery( this ).data( 'cpt' ),
							'nonce' : '<?php echo wp_create_nonce( 'handle-test-data' ); ?>'
						};

						// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
						jQuery.post( ajaxurl, data, function(response) {
							console.log( '<?php __( 'Got this from the server: ', 'evans-mu' ); ?>' + response );
							$( '.loading-icon' ).remove();
						});

					});

				});
			</script>

			<?php

			$html .= ob_get_clean();

		echo $html;

	}

	/**
	 * Create test data posts.
	 *
	 * This is where the magic begins. We accept a cpt id (slug) and potntially
	 * a number of posts to create. We then fetch the supports & metaboxes
	 * for that cpt and feed them into a function to create each post individually.
	 *
	 * @access private
	 *
	 * @see $this-get_cpt_supports, $this->get_cpt_metaboxes, $this->create_test_object
	 *
	 * @param string $cptslug a custom post type ID.
	 * @param int $num Optional. Number of posts to create.
	 */
	private function create_post_type_content( $cptslug, $num = '' ){

		// If we're missing a custom post type id - don't do anything
		if ( empty( $cptslug ) ){
			return;
		}

		// Gather the necessary data to create the posts
		$supports 	= $this->get_cpt_supports( $cptslug );
		$metaboxes	= $this->get_cpt_metaboxes( $cptslug );

		// If we forgot to put in a quantity, make one for us
		if ( empty( $num ) ){
			$num = rand( 5, 30 );
		}

		// Create test posts
		for( $i = 1; $i <= $num; $i++ ){
			$this->create_test_object( $cptslug, $supports, $metaboxes );
		}

	}


	/**
	 * Delete test data posts.
	 *
	 * This function will search for all posts of a particular post type ($cptslug)
	 * and delete them all using a particular cmb flag that we set when creating
	 * the posts. Validates the user first.
	 *
	 * @access private
	 *
	 * @see WP_Query, wp_delete_post
	 *
	 * @param string $cptslug a custom post type ID.
	 */
	private function delete_test_content( $cptslug ){

		if ( empty( $cptslug ) || !is_user_logged_in() || !current_user_can( 'delete_posts' ) ){
			return;
		}

		// Find our test data by the unique flag we set when we created the data
		$query = array(
			'post_type' 		=> $cptslug,
			'posts_per_page'	=> 500,
			'meta_query' 		=> array(
				array(
					'key'     => 'evans_test_content',
					'value'   => '__test__',
					'compare' => '=',
				),
			),
		);

		$objects = new \WP_Query( $query );

		if ( $objects->have_posts() ){

			while ( $objects->have_posts() ) : $objects->the_post();

				// Force delete the post
				wp_delete_post( get_the_id(), true );

			endwhile;

		}

	}

	/**
	 * Creates the individual test data post.
	 *
	 * Create individual posts for testing with. Gathers basic information such
	 * as title, content, thumbnail, etc. and inserts them with the post. Also
	 * adds metaboxes if applicable .
	 *
	 * @access private
	 *
	 * @see TestContent, wp_insert_post, add_post_meta, update_post_meta, $this->random_metabox_content
	 *
	 * @param string $cptslug a custom post type ID.
	 * @param array $supports Features that the post type supports.
	 * @param array $supports All CMB2 metaboxes attached to the post type.
	 */
	private function create_test_object( $cptslug, $supports, $metaboxes ){

		// Get a random title
		$title = TestContent::title();

		// First, insert our post
		$post = array(
		  'post_name'      => sanitize_title( $title ),
		  'post_status'    => 'publish',
		  'post_type'      => $cptslug,
		  'ping_status'    => 'closed',
		  'comment_status' => 'closed',
		);

		// Add title if supported
		if ( $supports['title'] === true ){
			$post['post_title'] = $title;
		}

		// Add main content if supported
		if ( $supports['editor'] === true ){
			$post['post_content'] = TestContent::paragraphs( rand( 1, 5 ) );
		}

		// Insert then post object
		$post_id = wp_insert_post( $post );

		// Then, set a test content flag on the new post for later deletion
		add_post_meta( $post_id, 'evans_test_content', '__test__', true );

		// Add thumbnail if supported
		if ( $supports['thumbnail'] === true || in_array( $cptslug, array( 'post', 'page' ) ) ){
			 update_post_meta( $post_id, '_thumbnail_id', TestContent::image( $post_id ) );
		}

		// Spin up metaboxes
		if ( !empty( $metaboxes ) ){
			foreach ( $metaboxes as $cmb ) :
				$this->random_metabox_content( $post_id, $cmb );
			endforeach;
		}

	}


	/**
	 * Assemble supports statements for a particular post type.
	 *
	 * @access private
	 *
	 * @see post_type_supports
	 *
	 * @param string $cptslug a custom post type ID.
	 * @return array Array of necessary supports booleans.
	 */
	private function get_cpt_supports( $cptslug ){

		$supports = array(
			'title'		=> post_type_supports( $cptslug, 'title' ),
			'editor'	=> post_type_supports( $cptslug, 'editor' ),
			'thumbnail'	=> post_type_supports( $cptslug, 'thumbnail' )
		);

		return $supports;

	}


	/**
	 * Gets all CMBS custom metaboxes associated with a post type.
	 *
	 * Loops through all custom metabox fields registered with CMB2 and
	 * looks through them for matches on the given post type ID. Returns a single
	 * array of all boxes associated with the post type.
	 *
	 * @access private
	 *
	 * @see cmb2_meta_boxescmb
	 *
	 * @param string $cptslug a custom post type ID.
	 * @return array Array of fields.
	 */
	private function get_cpt_metaboxes( $cptslug ){

		$fields = array();

		// Get all metaboxes from CMB2 library
		$all_metaboxes = apply_filters( 'cmb2_meta_boxes', array() );

		// Loop through all possible sets of metaboxes
		foreach ( $all_metaboxes as $metabox_array ){

			// If the custom post type ID matches this set of fields, set & stop
			if ( in_array( $cptslug, $metabox_array['object_types'] ) ) {

				// If this is the first group of fields, simply set the value
				// Else, merge this group with the previous one
				if ( empty( $fields ) ){
					$fields = $metabox_array['fields'];
				} else {
					$fields = array_merge( $fields, $metabox_array['fields'] );
				}
			}

		}

		return $fields;

	}


	/**
	 * Assigns the proper testing data to a custom metabox.
	 *
	 * Swaps through the possible types of CMB2 supported fields and
	 * insert the appropriate data based on type & id.
	 * Some types are not yet supported due to low frequency of use.
	 *
	 * @access private
	 *
	 * @see TestContent, add_post_meta
	 *
	 * @param int $post_id Single post ID.
	 * @param array $cmb custom metabox array from CMB2.
	 */
	private function random_metabox_content( $post_id, $cmb ){

		if ( !empty( $cmb ) && !empty( $post_id ) ){

			switch( $cmb['type'] ){

				case 'text':
				case 'text_small':
				case 'text_medium':

					if ( stripos( $cmb['id'], 'phone' ) ){
						$value = TestContent::phone();
					} elseif ( stripos( $cmb['id'], 'email' ) ){
						$value = TestContent::email();
					} else {
						$value = TestContent::plain_text();
					}

					break;

				case 'text_url':

					$value = TestContent::link();

					break;

				case 'text_email':

					$value = TestContent::email();

					break;

				// case 'text_time': break;
				// case 'select_timezone': break;

				case 'text_date':

					$value = TestContent::date( 'm/d/Y' );

					break;

				case 'text_date_timestamp':
				case 'text_datetime_timestamp':

					$value = TestContent::date( 'U' );

					break;

				// case 'text_datetime_timestamp_timezone': break;

				case 'text_money':

					$value = rand( 0, 100000 );

					break;

				case 'test_colorpicker':

					$value = '#' . str_pad( dechex( mt_rand( 0, 0xFFFFFF ) ), 6, '0', STR_PAD_LEFT );

					break;

				case 'textarea':
				case 'textarea_small':
				case 'textarea_code':

					$value = TestContent::plain_text();

					break;

				// case 'title': break;

				case 'select':
				case 'radio_inline':
				case 'radio':

					// Grab a random item out of the array and return the key
					$new_val = array_slice( $cmb['options'], rand( 0, count( $cmb['options'] ) ), 1 );
					$value = key( $new_val );

					break;

				// case 'taxonomy_radio': break;
				// case 'taxonomy_select': break;
				// case 'taxonomy_multicheck': break;

				case 'checkbox':

					// 50/50 odds of being turned on
					if ( rand( 0, 1 ) == 1 ){
						$value = 'on';
					}

					break;

				// case 'multicheck': break;

				case 'wysiwyg':

					$value = TestContent::paragraphs();

					break;

				case 'file':

					$value = TestContent::image( $post_id );

					break;

				// case 'file_list': break;
				// case 'embed': break;

			}

			// Value must exist to attempt to insert
			if ( !empty( $value ) && !is_wp_error( $value ) ){

				// Files must be treated separately - they use the attachment ID
				// & url of media for separate cmb values
				if ( $cmb['type'] != 'file' ){
					add_post_meta( $post_id, $cmb['id'], $value, true );
				} else {
					add_post_meta( $post_id, $cmb['id'].'_id', $value, true );
					add_post_meta( $post_id, $cmb['id'], wp_get_attachment_url( $value ), true );
				}

			} elseif ( is_wp_error( $value ) ){
				echo $value->get_error_message();
			}

		} // end if

	} // end random_metabox_content

}

$content = new BuildTestData;
$content->hooks();