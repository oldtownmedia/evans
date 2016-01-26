<?php
namespace evans;

/**
 * Class to build test data for custom post types.
 *
 * @package    WordPress
 * @subpackage Evans
 * @author     Old Town Media
 */
class BuildTestData{

	/**
	 * Hooks function.
	 *
	 * This function is used to avoid loading any unnecessary functions/code.
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

		add_submenu_page(
			'tools.php',
			__( 'Create Test Data', 'evans-mu' ),
			__( 'Test Data', 'evans-mu' ),
			'manage_options',
			'evans_mu-test_data',
			array( $this, 'admin_page' )
		);

	}

	/**
	 * Ajax callback function for triggering the creation & deletion of test data.
	 *
	 * @see wp_ajax filter, $this->add_menu_item
	 */
	public function handle_test_data_callback() {

		$cptslug	= $_REQUEST['cptslug'];
		$action		= $_REQUEST['todo'];
		$nonce		= $_REQUEST['nonce'];

		// Verify that we have a proper logged in user and it's the right person
		if ( empty( $nonce ) || !wp_verify_nonce( $nonce, 'handle-test-data' ) ){
			return;
		}

		if ( $action == 'delete' ){

			$this->delete_test_content( $cptslug, true );

		} elseif ( $action == 'create' ){

			$this->create_post_type_content( $cptslug, true, 1 );

		}

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

			$html .= "<pre style='display: block; width:95%; height:300px; overflow-y: scroll; background: #fff; padding: 10px;' id='status-updates'></pre>";

		$html .= "</div>";

			ob_start();

			?>

			<script type='text/javascript'>
				jQuery(document).ready(function($) {

					jQuery( '.handle-test-data' ).on( 'click', function(){

						jQuery( this ).after( '<img src="<?php echo plugins_url( '../assets/images/loading.gif', __FILE__ ); ?>" class="loading-icon" style="height: 20px; margin-bottom: -4px; margin-left: 4px;">' );

						var data = {
							'action' : 'handle_test_data',
							'todo' : jQuery( this ).data( 'todo' ),
							'cptslug' : jQuery( this ).data( 'cpt' ),
							'nonce' : '<?php echo wp_create_nonce( 'handle-test-data' ); ?>'
						};

						if ( jQuery( this ).data( 'todo' ) == 'create' ){
							var count = Math.floor( ( Math.random() * 30 ) + 1 );
						} else {
							var count = 1;
						}

						for( var i=1; i<=count; i++ ){

							jQuery.post( ajaxurl, data, function(response) {
								jQuery( '#status-updates' ).append( '<?php __( 'Got this from the server: ', 'evans-mu' ); ?>' + response );
							});

						}

						jQuery( '.loading-icon' ).remove();

						if ( jQuery( this ).data( 'todo' ) == 'create' ){
							jQuery( '#status-updates' ).append( 'Creating ' + count + ' objects\n' );
						}

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
	 * @param boolean $true Whether or not to echo. Optional.
	 * @param int $num Optional. Number of posts to create.
	 */
	private function create_post_type_content( $cptslug, $echo = false, $num = '' ){

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
		for( $i = 0; $i < $num; $i++ ){

			$return = $this->create_test_object( $cptslug, $supports, $metaboxes );

			if ( $echo === true ){
				echo $return;
			}

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
	private function delete_test_content( $cptslug, $echo = false ){

		// Check that $cptslg has a string.
		// Also make sure that the current user is logged in & has full permissions.
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

				// Find any media associated with the test post and delete it as well
				$this->delete_associated_media( get_the_id() );

				if ( $echo === true ){
					echo "Deleted ".get_post_type( get_the_id() )." " . get_the_id()."
";
				}

				// Force delete the post
				wp_delete_post( get_the_id(), true );

			endwhile;

			echo "Deleted objects\n";

		}

	}


	/**
	 * Find and delete attachments associated with a post ID.
	 *
	 * This function finds each attachment that is associated with a post ID
	 * and deletes it completely from the site. This is to prevent leftover
	 * random images from sitting on the site forever.
	 *
	 * @access private
	 *
	 * @see get_attached_media, wp_delete_attachment
	 *
	 * @param int $pid a custom post type ID.
	 */
	private function delete_associated_media( $pid ){

		if ( !is_int( $pid ) ){
			return;
		}

		// Get our images
		$media = get_attached_media( 'image', $pid );

		if ( !empty( $media ) ){

			// Loop through the media & delete each one
			foreach ( $media as $attachment ){
				wp_delete_attachment( $attachment->ID, true );
			}

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
		$return = '';

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
			$post['post_content'] = TestContent::paragraphs();
		}

		// Insert then post object
		$post_id = wp_insert_post( $post );

		// Then, set a test content flag on the new post for later deletion
		add_post_meta( $post_id, 'evans_test_content', '__test__', true );

		// Add thumbnail if supported
		if ( $supports['thumbnail'] === true || in_array( $cptslug, array( 'post', 'page' ) ) ){
			 update_post_meta( $post_id, '_thumbnail_id', TestContent::image( $post_id ) );
		}

		$taxonomies = get_object_taxonomies( $cptslug );

		// Assign the post to terms
		if ( !empty( $taxonomies ) ){
			$return .= $this->assign_terms( $post_id, $taxonomies );
		}

		// Spin up metaboxes
		if ( !empty( $metaboxes ) ){
			foreach ( $metaboxes as $cmb ) :
				$return .= $this->random_metabox_content( $post_id, $cmb );
			endforeach;
		}

		// Check if we have errors and return them or created message
		if ( is_wp_error( $return ) ){
			return $return;
		} else {
			return "Created " . get_post_type( $post_id ) . " $post_id: " . admin_url( '/post.php?post='.$post_id.'&action=edit' ) . "
";
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
	 * Assigns taxonomies to the new post.
	 *
	 * Loop through every taxonomy type associated with a custom post type &
	 * assign the post to a random item out of each taxonomy. Taxonomies must
	 * have at least one term in them for this to work.
	 *
	 * @access private
	 *
	 * @param int $post_id a custom post type ID.
	 * @param array $taxonomies taxonomies assigned to this cpt.
	 * @return object WP Error if there is one.
	 */
	private function assign_terms( $post_id, $taxonomies ){

		// Make sure it's an array & has items
		if ( empty( $taxonomies ) || !is_array( $taxonomies ) ){
			return;
		}

		foreach ( $taxonomies as $tax ){

			// Get the individual terms already existing
			$terms = get_terms( $tax, array( 'hide_empty'	=> false ) );
			$count = count( $terms ) - 1;

			// If there are no terms, skip to the next taxonomy
			if ( empty( $terms ) ){
				continue;
			}

			// Get a random index to use
			$index = rand( 0, $count );

			// Initialize our array
			$post_data = array(
				'ID'	=> $post_id
			);

			// Set the term data to update
			$post_data['tax_input'][ $tax ] = array( $terms[$index]->term_id );

			// Update the post with the taxonomy info
			$return = wp_update_post( $post_data );

			// Return the error if it exists
			if ( is_wp_error( $return ) ){
				return $return->get_error_messages();
			}

		}

	}


	/**
	 * Gets all CMB2 custom metaboxes associated with a post type.
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
		$value = '';

		// First check that our post ID & cmb array aren't empty
		if ( empty( $cmb ) || empty( $post_id ) ){
			return;
		}

		switch( $cmb['type'] ){

			case 'text':
			case 'text_small':
			case 'text_medium':

				// If phone is in the id, fetch a phone #
				if ( stripos( $cmb['id'], 'phone' ) ){
					$value = TestContent::phone();

				// If email is in the id, fetch an email address
				} elseif ( stripos( $cmb['id'], 'email' ) ){
					$value = TestContent::email();

				// If time is in the id, fetch a time string
				} elseif ( stripos( $cmb['id'], 'time' ) ){
					$value = TestContent::time();

				// Otherwise, just a random text string
				} else {
					$value = TestContent::title( rand( 10, 50 ) );
				}

				break;

			case 'text_url':

				$value = TestContent::link();

				break;

			case 'text_email':

				$value = TestContent::email();

				break;

			// case 'text_time': break;

			case 'select_timezone':

				$value = TestContent::timezone();

				break;

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

			case 'multicheck':

				$new_option = array();

				// Loop through each of our options
				foreach ( $cmb['options'] as $key => $value ){

					// 50/50 chance of being included
					if ( rand( 0, 1 ) ){
						$new_option[] = $key;
					}

				}

				$value = $new_option;

				break;

			case 'wysiwyg':

				$value = TestContent::paragraphs();

				break;

			case 'file':

				$value = TestContent::image( $post_id );

				break;

			// case 'file_list': break;

			case 'oembed':

				$value = TestContent::oembed();

				break;

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

		// If we're dealing with a WP Error object, just return the message for debugging
		} elseif ( is_wp_error( $value ) ){
			return $value->get_error_message();
		}

	} // end random_metabox_content

}

$content = new BuildTestData;
$content->hooks();