<?php

/**
 * Clean Admin
 *
 * Cleans up the admin section by removing unnecessary distractions
 *
 * @package WordPress
 * @category mu_plugin
 * @author Old Town Media
 */
class Clean_Admin {

	/**
	 * Constructor function.
	 *
	 * @access public
	 * @since 0.0.0
	 * @return void
	 */
	function __construct() {

		add_filter( 'admin_init' , array( $this , 'register_fields' ) );
		add_action( 'admin_menu', array( $this, 'otm_remove_menus' ), 105 );
		add_action( 'admin_menu', array( $this, 'otm_remove_dashboard_widgets' ) );
		add_action( 'admin_notices', array( $this, 'custom_update_nag' ), 99 );
		add_action( 'admin_menu', array( $this, 'remove_core_update_nag' ), 2 );
		add_action( 'admin_notices', array( $this, 'custom_update_nag' ), 99 );
		add_filter( 'menu_order', array( $this, 'menu_order' ), 9999 );
		add_filter( 'custom_menu_order', '__return_true' );
        add_filter( 'manage_posts_columns', array( $this, 'otm_remove_columns' ) );
        add_filter( 'gettext', array( $this, 'tgm_soliloquy_envira_whitelabel' ), 10, 3 );
        add_filter( 'envira_gallery_skipped_posttypes', array( $this, 'envira_skip_custom_post_type' ) );
        add_action( 'admin_menu', array( $this, 'remove_post_meta_boxes' ) );
        add_filter( 'manage_pages_columns', array( $this, 'clean_post_columns' ) );
        add_action( 'wp_before_admin_bar_render', array( $this, 'remove_admin_bar_links' ) );
        add_action( 'login_head', array( $this, 'otm_login_css' ) );
        add_filter( 'mce_buttons', array( $this, 'otm_extended_editor_mce_buttons' ), 0 );
		add_filter( 'mce_buttons_2', array( $this, 'otm_extended_editor_mce_buttons_2' ), 0 );
		add_action( 'add_meta_boxes', array( $this, 'otm_remove_seo_metabox' ), 11 );
		add_action( 'dashboard_glance_items', array( $this, 'right_now_cpt_count' ) );
		add_action( 'admin_bar_menu', array( $this, 'otm_add_toolbar_links' ), 999 );
		add_action( 'admin_bar_menu', array( $this, 'otm_add_admin_toolbar_links' ), 999 );

	}

	/**
	 * Add new fields to wp-admin/options-general.php page
	 *
	 * Add an option for a user to override the clean-admin
	 * options and show the default WP layout
	 */
	public function register_fields() {

		// If our user isn't an admin, don't show the new option
		if ( !current_user_can( 'manage_options' ) ){
			return;
		}

		register_setting( 'general', 'otm_clean_admin_bar', 'esc_attr' );
		add_settings_field(
			'otm_clean_admin_bar',
			'<label for="favorite_color">' . __( 'Hide unnecessary menu items' , 'otm_clean_admin_bar' ) . '</label>',
			array( $this, 'fields_html' ),
			'general'
		);
	}

	/**
	 * HTML for extra settings fields in the general settings page
	 */
	public function fields_html() {
		$value = get_option( 'otm_clean_admin_bar', 'on' );
		$on = $off = '';

		if ( $value == 'on' ){
			$on = 'checked="checked"';
		} else {
			$off = 'checked="checked"';
		}

		echo '<label><input type="radio" id="otm_clean_admin_bar" name="otm_clean_admin_bar" value="on" '.$on.' /> Hide items</label><br>';
		echo '<label><input type="radio" id="otm_clean_admin_bar" name="otm_clean_admin_bar" value="off" '.$off.' /> Show items</label>';

	}


	/*************
	* Menu Stuff *
	*************/

	/**
	 * Remove Unnecesary Menu & Sub-Menu Items
	 *
	 * @global array $menu Array of main menu items.
	 * @global array $submenu Array of submenu items.
	 */
	public function otm_remove_menus() {

		// Has the client opted out of cleaning the admin area?
		$hide = get_option( 'otm_clean_admin_bar' );

		if ( $hide == 'off' ){
			return '';
		}

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

		// Move pages item to top
		global $menu, $submenu;
		$menu[6] = $menu[5];	// Move posts from 5 to 6
		$menu[5] = $menu[20];	// Move pages from 20 to 5
		unset( $menu[20] );		// Remove spot that pages was in
		unset( $submenu['themes.php'][6] );	// Alternative method of removing customizer

		$submenu['themes.php'][7][0] = 'Sidebar Items';		// Rename Widget
		$submenu['edit.php?post_type=page'][15] = array( 	// Move Media into Pages menu item
			'0' => 'Media',
			'1'	=> 'edit_pages',
			'2'	=> 'upload.php'
		);

		// Remove menu items only for non-admins
		$current_user = wp_get_current_user();
		if ( $current_user->user_login != 'otm' && $current_user->user_login != 'Mike' && $current_user->user_login != 'Miles' ){

			remove_menu_page( 'activity_log_page' );				// Aryo Activity log
			remove_submenu_page( 'themes.php', 'themes.php' );		// Remove Theme page
			remove_submenu_page( 'gf_edit_forms', 'gf_export' );	// GF Export/Import
			remove_submenu_page( 'gf_edit_forms', 'gf_addons' );	// GF Export/Import
			remove_submenu_page( 'gf_edit_forms', 'gf_help' );		// GF Export/Import

		}
	}

	/**
	 * Identify OK items to stay in the top of the admin section
	 *
	 * @access private
	 *
	 * @see menu_order
	 *
	 * @param string $item Name of a menu item.
	 * @return array list of whitelisted items.
	 */
	private function is_gracious_menu_item( $item ) {
		return in_array( $item, array(
			'wp-help-documents',
		) );
	}


	/**
	 * Move all items in the main admin menu that aren't whitelisted
	 * to the very bottom. Because seriously, it's rediculous
	 *
	 * @see is_gracious_menu_item
	 *
	 * @param array $menu Original menu array.
	 * @return array $menu Modified menu array.
	 */
	public function menu_order( $menu ) {
		$penalty_box = array();

		foreach ( $menu as $key => $item ) {

			if ( 'separator1' == $item ) {
				break;
			} elseif ( 'index.php' !== $item && !$this->is_gracious_menu_item( $item ) ) {
				// Yank it out and put it in the penalty box.
				$penalty_box[] = $item;
				unset( $menu[$key] );
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
	 * @param string $domain textdomain.
	 * @return string modified text.
	 */
	public function tgm_soliloquy_envira_whitelabel( $translated_text, $source_text, $domain ) {

	    // If not in the admin, return the default string.
	    if ( ! is_admin() ) {
	        return $translated_text;
	    }

	    if ( strpos( $source_text, 'Soliloquy Slider' ) !== false ) {
	        return str_replace( 'Soliloquy Slider', 'Slider', $translated_text );
	    }

	    if ( strpos( $source_text, 'Soliloquy Sliders' ) !== false ) {
	        return str_replace( 'Soliloquy Sliders', 'Sliders', $translated_text );
	    }

	    if ( strpos( $source_text, 'Soliloquy slider' ) !== false ) {
	        return str_replace( 'Soliloquy slider', 'slider', $translated_text );
	    }

	    if ( strpos( $source_text, 'Soliloquy' ) !== false ) {
	        return str_replace( 'Soliloquy', 'Slider', $translated_text );
	    }

	    if ( strpos( $source_text, 'an Envira' ) !== false ) {
	        return str_replace( 'a Gallery', '', $translated_text );
	    }

	    if ( strpos( $source_text, 'Envira' ) !== false ) {
	        return str_replace( 'Gallery', '', $translated_text );
	    }

	    return $translated_text;

	}

	/**
	 * Remove unnecessary post columns from posts and pages
	 *
	 * Remove author & comments columns
	 *
	 * @param array $columns columns for the post type.
	 * @return array $columns Modified columns array.
	 */
	public function clean_post_columns( $columns ){

		unset(
			$columns['author'],
			$columns['comments']
		);

		return $columns;
	}

	/**
	 * Remove unnecessary admin bar links
	 *
	 * @global array $wp_admin_bar Full admin bar object.
	 */
	public function remove_admin_bar_links() {
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
	 * Filter out Envira from showing on a custom post type.
	 *
	 * @since 1.0.0
	 *
	 * @param array $post_types  Default post types to skip.
	 * @return array $post_types Modified post types to skip.
	 */
	public function envira_skip_custom_post_type( $post_types ) {

	    // Add your custom post type here.
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

	/**
	 * Remove author & tags boxes from the post edit box
	 */
	public function remove_post_meta_boxes() {
		remove_meta_box( 'authordiv', 'post', 'normal' );
		remove_meta_box( 'tagsdiv-post_tag', 'post', 'normal' );
	}

	/**
	 * Remove unnecesary dashboard widgets from the admin dashboard
	 */
	public function otm_remove_dashboard_widgets() {

		remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'core' );		// Incoming Links
		remove_meta_box( 'dashboard_plugins', 'dashboard', 'core' );			// Plugins
		remove_meta_box( 'dashboard_secondary', 'dashboard', 'core' );			// the WordPress Blog
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'core' );	// Recent Comments
		remove_meta_box( 'dashboard_quick_press', 'dashboard', 'core' );		// Quick Press
		remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'core' );		// Recent Drafts
		remove_meta_box( 'dashboard_primary', 'dashboard', 'core' );			// WordPress Blog
		remove_meta_box( 'dashboard_activity', 'dashboard', 'core' );			// Dashboard Activity

	}

	/**
	 * Remove default nag
	 *
	 * @global string $pagenow Current page in the admin dashboard.
	 */
	function remove_core_update_nag() {
	    remove_action( 'admin_notices', 'update_nag', 3 );
	    remove_action( 'network_admin_notices', 'update_nag', 3 );
	}

	/**
	 * Custom dashboard nag that tells customers to contact us
	 *
	 * @global string $pagenow Current page in the admin dashboard.
	 */
	public function custom_update_nag() {
	    if ( is_multisite() && !current_user_can('update_core') ){
	        return false;
	    }

	    global $pagenow;

	    if ( 'update-core.php' == $pagenow ){
	        return;
	    }

	    $cur = get_preferred_from_update_core();

	    if ( ! isset( $cur->response ) || $cur->response != 'upgrade' ){
	        return false;
	    }

	    if ( current_user_can( 'update_core' ) ) {
	        $msg = sprintf( __( '<a href="http://codex.wordpress.org/Version_%1$s">WordPress %1$s</a> is available! Please contact OTM at (970) 568 5250 or <a href="mailto:support@oldtownmediainc.com">support@oldtownmediainc.com</a> to schedule your update.' ), $cur->current, 'your_custom_url' );
	    } else {
	        $msg = sprintf( __( '<a href="http://codex.wordpress.org/Version_%1$s">WordPress %1$s</a> is available! Please notify the site administrator.' ), $cur->current );
	    }

	    echo "<div class='update-nag'>$msg</div>";
	}

	/**
	 * Remove author column from post listing
	 *
	 * @param array $defaulgts Default column listin.
	 * @return array $defaults Modified column listing.
	 */
	public function otm_remove_columns( $defaults ) {
		unset( $defaults['author'] );
		return $defaults;
	}

	/**
	 * Insert our own stylesheet into the login page to customize it
	 */
	public function otm_login_css() {
		wp_enqueue_style( 'login_css', get_template_directory_uri() . '/styles/login.css' );
	}

	/**
	 * Modify the array of tinymce buttons in the visual editor for row 1
	 *
	 * @param array $buttons Default mce buttons row 1.
	 * @return array Modified array of buttons for row 1.
	 */
	function otm_extended_editor_mce_buttons( $buttons ) {

		return array(
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
			'wp_help'
		);

	}

	/**
	 * Modify the array of tinymce buttons in the visual editor for row 2 (ie: kill them all!)
	 *
	 * @param array $buttons Default mce buttons row 1.
	 * @return array empty array.
	 */
	function otm_extended_editor_mce_buttons_2( $buttons ) {
		return array();
	}

	/**
	 * Remove Yoast SEO columns from all post types
	 *
	 * @param type $columns Default column array.
	 * @return array $columns Modified array of columns.
	 */
	function otm_seo_remove_columns( $columns ) {

		// remove the Yoast SEO columns
		unset( $columns['wpseo-score'] );
		unset( $columns['wpseo-title'] );
		unset( $columns['wpseo-metadesc'] );
		unset( $columns['wpseo-focuskw'] );

		return $columns;

	}

	/**
	 * Remove Yoast SEO custom metaboxes from CPTs that don't have their own single
	 */
	function otm_remove_seo_metabox() {

	    if ( !current_user_can( 'edit_others_posts' ) ){
	        remove_meta_box( 'wpseo_meta', 'alert', 'normal' );
	        remove_meta_box( 'wpseo_meta', 'document', 'normal' );
	        remove_meta_box( 'wpseo_meta', 'highlight', 'normal' );
	        remove_meta_box( 'wpseo_meta', 'partner', 'normal' );
	        remove_meta_box( 'wpseo_meta', 'testimonial', 'normal' );
	        remove_meta_box( 'wpseo_meta', 'video', 'normal' );
	    }
	}

	public function right_now_cpt_count() {
		if ( current_user_can( 'edit_posts' ) && is_admin() ) {

		$cpt_array = apply_filters( 'cpt_array_filter', array() );

			foreach( $cpt_array as $cpt ){

				$count = wp_count_posts( $cpt['slug'] );

				$text = _n( '%s '.$cpt['singular'], '%s '.$cpt['plural'], intval( $count->publish ), 'otm' );
				$text = sprintf( $text, number_format_i18n( $count->publish ) );

				printf( "<li class='$cpt[slug]-count'><a href='edit.php?post_type=$cpt[slug]' class='$cpt[class]'>%s</a></li>", $text );

			}
		}
	}

	/**
	 * Add all top-level header-menu items in the dropdown under "{site name}"
	 * in admin bar on admin side
	 *
	 * @see admin_bar_menu
	 *
	 * @param object $wp_admin_bar admin bar main object.
	 */
	public function otm_add_admin_toolbar_links( $wp_admin_bar ) {

		// Abort if we're not in the back end
		if( !is_admin() || !current_user_can( 'edit_users' ) ) {
			return;
		}

		// Define the WP menu we want to pull from
		$menu_id = 'header-menu';

	    if ( ( $locations = get_nav_menu_locations() ) && isset( $locations[ $menu_id ] ) ) {
			$menu = wp_get_nav_menu_object( $locations[ $menu_id ] );

			$menu_items = wp_get_nav_menu_items( $menu->term_id );

			foreach ( (array) $menu_items as $key => $menu_item ) :

			    $page = array(
					'parent' => 'site-name',
					'id'     => 'front_end_page_'.$menu_item->db_id,
					'title'  => $menu_item->title,
					'href'   => $menu_item->url
				);

				if ( $menu_item->menu_item_parent == 0 ){
					$wp_admin_bar->add_node( $page );
				}

			endforeach;
		} // End if

	}

	/**
	 * Customize the dropdown under "Dashboard" in admin bar on front-end
	 *
	 * @see admin_bar_menu
	 *
	 * @param object $wp_admin_bar admin bar main object.
	 */
	public function otm_add_toolbar_links( $wp_admin_bar ) {

		// Abort if we're not on the front-end
		if( is_admin() || !current_user_can( 'edit_users' ) ) {
			return;
		}

		$plugins = array(
			'parent' => 'site-name',
			'id'     => 'plugins',
			'title'  => __( 'Plugins', 'evans-mu' ),
			'href'   => admin_url( 'plugins.php' )
		);

		$pages = array(
			'parent' => 'site-name',
			'id'     => 'pages',
			'title'  => __( 'Pages', 'evans-mu' ),
			'href'   => admin_url( 'edit.php?post_type=page' )
		);

		$posts = array(
			'parent' => 'site-name',
			'id'     => 'posts',
			'title'  => __( 'Posts', 'evans-mu' ),
			'href'   => admin_url( 'edit.php?post_type=post' )
		);


		// Add our nodes
		$wp_admin_bar->add_node( $plugins );
		$wp_admin_bar->add_node( $pages );
		$wp_admin_bar->add_node( $posts );

		// Remove worthless nodes
		$wp_admin_bar->remove_node( 'customize' );
		$wp_admin_bar->remove_node( 'widgets' );
		$wp_admin_bar->remove_node( 'themes' );

	}

}

$otm_simple_admin = new Clean_Admin();