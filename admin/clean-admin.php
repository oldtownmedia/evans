<?php
/**
 * Cleans up the admin by removing and adding certain functionality.
 *
 * @package    WordPress
 * @subpackage evans
 */

namespace evans\Clean_Admin;

/**
 * Hooks function to fire off the events we need.
 */
function setup() {
	// Couple of public functions
	add_action( 'admin_bar_menu', __NAMESPACE__ . '\\add_toolbar_links', 999 );

	// Only keep going if we're in the admin section
	if ( ! is_admin() ) {
		return;
	}

	if ( get_option( 'clean_admin_bar', 'on' ) === 'on' ) {
		add_action( 'admin_menu', __NAMESPACE__ . '\\remove_menus', 105 );
	}

	add_filter( 'admin_init' , __NAMESPACE__ . '\\register_fields' );
	add_action( 'admin_menu', __NAMESPACE__ . '\\remove_dashboard_widgets' );
	add_action( 'admin_menu', __NAMESPACE__ . '\\remove_core_update_nag', 2 );
	add_action( 'admin_notices', __NAMESPACE__ . '\\custom_update_nag', 99 );
	add_action( 'admin_notices', __NAMESPACE__ . '\\custom_update_nag', 99 );
	add_filter( 'menu_order', __NAMESPACE__ . '\\menu_order', 9999 );
	add_filter( 'custom_menu_order', '__return_true' );
	add_filter( 'gettext', __NAMESPACE__ . '\\relabel_soliloquy_envira', 10, 3 );
	add_filter( 'envira_gallery_skipped_posttypes', __NAMESPACE__ . '\\envira_skip_cpts' );
	add_filter( 'manage_posts_columns', __NAMESPACE__ . '\\remove_columns' );
	add_action( 'admin_menu', __NAMESPACE__ . '\\remove_post_meta_boxes' );
	add_filter( 'manage_pages_columns', __NAMESPACE__ . '\\clean_post_columns' );
	add_action( 'wp_before_admin_bar_render', __NAMESPACE__ . '\\remove_admin_bar_links' );
	add_filter( 'mce_buttons', __NAMESPACE__ . '\\extended_editor_mce_buttons', 0 );
	add_filter( 'mce_buttons_2', __NAMESPACE__ . '\\extended_editor_mce_buttons_2', 0 );
	add_action( 'admin_bar_menu', __NAMESPACE__ . '\\add_admin_toolbar_links', 999 );
}

/**
 * Add new fields to wp-admin/options-general.php page.
 *
 * Add an option for a user to override the clean-admin options and show
 * the default WP layout.
 */
function register_fields() {
	// If our user isn't an admin, don't show the new option.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	register_setting( 'general', 'clean_admin_bar', 'esc_attr' );
	add_settings_field(
		'clean_admin_bar',
		'<label for="clean_admin_bar">' . esc_html__( 'Hide unnecessary menu items' , 'evans-mu' ) . '</label>',
		function() {
			$value = get_option( 'clean_admin_bar', 'on' );
			?>
				<label>
					<input type="radio" id="clean_admin_bar" name="clean_admin_bar" value="on" <?php checked( 'on', $value ); ?> />
					<?php echo esc_html__( 'Hide items', 'evans-mu' ); ?>
				</label><br>

				<label>
					<input type="radio" id="clean_admin_bar" name="clean_admin_bar" value="off" <?php checked( 'off', $value ); ?> />
						<?php echo esc_html__( 'Show items', 'evans-mu' ); ?>
				</label>
			<?php
		},
		'general'
	);
}

/*************
* Menu Stuff *
*************/

/**
 * Remove unnecesary menu & sub-menu items.
 *
 * @global array $menu Array of main menu items.
 * @global array $submenu Array of submenu items.
 */
function remove_menus() {
	// Remove links.
	remove_menu_page( 'link-manager.php' );						// Links page
	remove_menu_page( 'edit-comments.php' );					// Comments page
	remove_menu_page( 'upload.php' );							// Media page
	remove_submenu_page( 'themes.php', 'customize.php' );		// Remove Customizer page
	remove_submenu_page( 'themes.php', 'theme-editor.php' );	// Theme Editor
	remove_submenu_page( 'plugins.php', 'plugin-editor.php' );	// Plugin Editor Settings

	remove_submenu_page( 'wpseo_dashboard', 'wpseo_licenses' );	// WPSEO Extensions
	remove_submenu_page( 'wpseo_dashboard', 'wpseo_files' );	// WPSEO Edit Files
	remove_submenu_page( 'wpseo_dashboard', 'wpseo_import' );	// WPSEO Edit Files
	remove_submenu_page( 'wpseo_dashboard', 'wpseo_bulk-editor' );	// WPSEO Bulk Editor

	// Move pages item to top.
	global $menu, $submenu;
	$menu[6] = $menu[5];	// Move posts from 5 to 6
	$menu[5] = $menu[20];	// Move pages from 20 to 5
	unset( $menu[20] );		// Remove spot that pages was in

	// Remove customizer link.
	unset( $submenu['themes.php'][6] );	// Alternative method of removing customizer

	// Rename Widgets.
	if ( isset( $submenu['themes.php'][7] ) ) {
		$submenu['themes.php'][7][0] = 'Sidebar Items';
	}

	// Move Media into Pages menu item.
	$submenu['edit.php?post_type=page'][15] = [
		'0' => 'Media',
		'1'	=> 'edit_pages',
		'2'	=> 'upload.php',
	];

	// Remove menu items only for non-admins.
	$current_user = wp_get_current_user();
	if ( 'otm' !== $current_user->user_login ) {
		remove_submenu_page( 'themes.php', 'themes.php' );		// Remove Theme page
		remove_submenu_page( 'gf_edit_forms', 'gf_export' );	// GF Export/Import
		remove_submenu_page( 'gf_edit_forms', 'gf_addons' );	// GF Export/Import
		remove_submenu_page( 'gf_edit_forms', 'gf_help' );		// GF Export/Import
	}
}

/**
 * Identify OK items to stay in the top of the admin section
 *
 * @param string $item Name of a menu item.
 * @return array list of whitelisted items.
 */
function is_gracious_menu_item( $item ) {
	return 'wp-help-documents' === $item;
}


/**
 * Move all items in the main admin menu that aren't whitelisted
 * to the very bottom. Because seriously, it's rediculous
 *
 * @param array $menu Original menu array.
 * @return array $menu Modified menu array.
 */
function menu_order( $menu ) {
	$penalty_box = [];

	foreach ( $menu as $key => $item ) {

		if ( 'separator1' === $item ) {
			break;
		} elseif ( 'index.php' !== $item && ! $this->is_gracious_menu_item( $item ) ) {
			// Yank it out and put it in the penalty box.
			$penalty_box[] = $item;
			unset( $menu[ $key ] );
		}
	}

	// Shove the penalty box items onto the end
	return array_merge( $menu, $penalty_box );
}

/**
 * Modify the text for Soliloquy & Envira to whitelabeled strings
 *
 * @param string $translated_text translated version of the string.
 * @param string $source_text original text.
 * @return string modified text.
 */
function relabel_soliloquy_envira( $translated_text, $source_text ) {
	// If not in the admin, return the default string.
	if ( ! is_admin() ) {
		return $translated_text;
	}

	$strings = [
		'Soliloquy Slider'      => 'Slider',
		'Soliloquy slider'      => 'slider',
		'Soliloquy Sliders'     => 'Sliders',
		'Soliloquy'             => 'Slider',
		'an Envira'             => 'a Gallery',
		'Envira Gallery'        => 'Gallery',
		'Envira Galleries'      => 'Galleries',
		'Native Envira Gallery' => 'Native Gallery',
		'Envira'                => 'Gallery',
	];

	if ( array_key_exists( $source_text, $strings ) ) {
		return $strings[ $source_text ];
	}

	return $translated_text;
}

/**
 * Filter out Envira from showing on a custom post type.
 *
 * @param array $post_types  Default post types to skip.
 * @return array $post_types Modified post types to skip.
 */
function envira_skip_cpts( $post_types ) {
	$post_types[] = 'post';
	$post_types[] = 'page';
	$post_types[] = 'product';
	$post_types[] = 'alert';
	$post_types[] = 'document';
	$post_types[] = 'highlight';
	$post_types[] = 'event';
	$post_types[] = 'partner';
	$post_types[] = 'portfolio';
	$post_types[] = 'staff';
	$post_types[] = 'testimonial';

	return $post_types;
}

/****************
* General Admin *
****************/

/**
 * Remove unnecessary post columns from posts and pages.
 *
 * Remove author & comments columns
 *
 * @param array $columns columns for the post type
 * @return array $columns Modified columns array
 */
function clean_post_columns( $columns ) {
	unset(
		$columns['author'],
		$columns['comments']
	);

	return $columns;
}

/**
 * Remove unnecessary admin bar links.
 *
 * @global array $wp_admin_bar Full admin bar object.
 */
function remove_admin_bar_links() {
	global $wp_admin_bar;

	$wp_admin_bar->remove_menu( 'wp-logo' );          // Remove the WordPress logo
	$wp_admin_bar->remove_menu( 'about' );            // Remove the about WordPress link
	$wp_admin_bar->remove_menu( 'wporg' );            // Remove the WordPress.org link
	$wp_admin_bar->remove_menu( 'documentation' );    // Remove the WordPress documentation link
	$wp_admin_bar->remove_menu( 'support-forums' );   // Remove the support forums link
	$wp_admin_bar->remove_menu( 'feedback' );         // Remove the feedback link
	$wp_admin_bar->remove_menu( 'comments' );         // Remove the comments link
}

/**
 * Remove author & tags boxes from the post edit box.
 */
function remove_post_meta_boxes() {
	remove_meta_box( 'authordiv', 'post', 'normal' );
	remove_meta_box( 'tagsdiv-post_tag', 'post', 'normal' );
}

/**
 * Remove unnecesary dashboard widgets from the admin dashboard.
 */
function remove_dashboard_widgets() {
	remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'core' );		// Incoming Links
	remove_meta_box( 'dashboard_plugins', 'dashboard', 'core' );			// Plugins
	remove_meta_box( 'dashboard_secondary', 'dashboard', 'core' );			// the WordPress Blog
	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'core' );	// Recent Comments
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'core' );		// Quick Press
	remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'core' );		// Recent Drafts
	remove_meta_box( 'dashboard_primary', 'dashboard', 'core' );			// WordPress Blog
	remove_meta_box( 'dashboard_activity', 'dashboard', 'core' );			// Dashboard Activity
	remove_meta_box( 'wpseo-dashboard-overview', 'dashboard', 'core' );     // Yoast SEO dashboard
}


/**
 * Remove default nag
 */
function remove_core_update_nag() {
	remove_action( 'admin_notices', 'update_nag', 3 );
	remove_action( 'network_admin_notices', 'update_nag', 3 );
}

/**
 * Custom dashboard nag that tells customers to contact us.
 *
 * @global string $pagenow Current page in the admin dashboard.
 */
function custom_update_nag() {
	if ( is_multisite() && ! current_user_can( 'update_core' ) ) {
		return false;
	}

	global $pagenow;

	if ( 'update-core.php' === $pagenow ) {
		return;
	}

	$cur = get_preferred_from_update_core();

	if ( ! isset( $cur->response ) || 'upgrade' !== $cur->response ) {
		return false;
	}

	$msg = sprintf(
		esc_html__( 'WordPress %1$s is available! Please contact OTM at (970) 568 5250 or %2$s to schedule your update.' ),
		$cur->current,
		'<a href="mailto:support@oldtownmediainc.com">support@oldtownmediainc.com</a>'
	);

	echo "<div class='update-nag'>" . wp_kses_post( $msg ) . '</div>';
}

/**
 * Remove author column from post listing.
 *
 * @param array $defaults Default column listin.
 * @return array $defaults Modified column listing.
 */
function remove_columns( $defaults ) {
	unset( $defaults['author'] );
	return $defaults;
}

/**
 * Modify the array of tinymce buttons in the visual editor for row 1.
 *
 * @param array $buttons Default mce buttons row 1.
 * @return array Modified array of buttons for row 1.
 */
function extended_editor_mce_buttons( $buttons ) {
	return [
		'formatselect',
		'bold',
		'italic',
		'sub',
		'sup',
		'alignleft',
		'aligncenter',
		'alignright',
		'bullist',
		'numlist',
		'link',
		'unlink',
		'blockquote',
		'outdent',
		'indent',
		'charmap',
		'pasteword',
		'removeformat',
		'spellchecker',
		'fullscreen',
		'wp_help',
	];
}

/**
 * Modify the array of tinymce buttons in the visual editor for row 2 (ie: kill them all!).
 *
 * @param array $buttons Default mce buttons row 2.
 * @return array empty array.
 */
function extended_editor_mce_buttons_2( $buttons ) {
	return [];
}

/**
 * Remove Yoast SEO columns from all post types.
 *
 * @param type $columns Default column array.
 * @return array $columns Modified array of columns.
 */
function seo_remove_columns( $columns ) {
	unset( $columns['wpseo-score'] );
	unset( $columns['wpseo-title'] );
	unset( $columns['wpseo-metadesc'] );
	unset( $columns['wpseo-focuskw'] );

	return $columns;
}

/**
 * Add all top-level header-menu items in the dropdown under "{site name}"
 * in admin bar on admin side
 *
 * @param object $wp_admin_bar admin bar main object.
 */
function add_admin_toolbar_links( $wp_admin_bar ) {
	// Abort if we're not in the back end
	if ( ! is_admin() || ! current_user_can( 'edit_users' ) ) {
		return;
	}

	// Define the WP menu we want to pull from
	$menu_id = 'header-menu';

	if ( ( $locations = get_nav_menu_locations() ) && isset( $locations[ $menu_id ] ) ) {
		$menu = wp_get_nav_menu_object( $locations[ $menu_id ] );

		if ( ! empty( $menu ) ) :

			$menu_items = wp_get_nav_menu_items( $menu->term_id );

			foreach ( (array) $menu_items as $key => $menu_item ) :

				$page = [
					'parent' => 'site-name',
					'id'     => 'front_end_page_' . $menu_item->db_id,
					'title'  => $menu_item->title,
					'href'   => $menu_item->url,
				];

				if ( 0 === $menu_item->menu_item_parent ) {
					$wp_admin_bar->add_node( $page );
				}

			endforeach;
		endif;
	}
}

/**
 * Customize the dropdown under "Dashboard" in admin bar on front-end
 *
 * @param object $wp_admin_bar admin bar main object.
 */
function add_toolbar_links( $wp_admin_bar ) {
	// Remove worthless nodes.
	$wp_admin_bar->remove_node( 'customize' );
	$wp_admin_bar->remove_node( 'widgets' );
	$wp_admin_bar->remove_node( 'themes' );

	// @todo:: handle permissions on a more granular lever.

	// Abort if we're not on the front-end.
	if ( is_admin() || ! current_user_can( 'edit_users' ) ) {
		return;
	}

	$plugins = [
		'parent' => 'site-name',
		'id'     => 'plugins',
		'title'  => __( 'Plugins', 'evans-mu' ),
		'href'   => admin_url( 'plugins.php' ),
	];

	$pages = [
		'parent' => 'site-name',
		'id'     => 'pages',
		'title'  => __( 'Pages', 'evans-mu' ),
		'href'   => admin_url( 'edit.php?post_type=page' ),
	];

	$posts = [
		'parent' => 'site-name',
		'id'     => 'posts',
		'title'  => __( 'Posts', 'evans-mu' ),
		'href'   => admin_url( 'edit.php?post_type=post' ),
	];

	// Add our nodes.
	$wp_admin_bar->add_node( $plugins );
	$wp_admin_bar->add_node( $pages );
	$wp_admin_bar->add_node( $posts );
}
