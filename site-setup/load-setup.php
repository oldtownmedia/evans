<?php
namespace evans;

/*
 * If we haven't run theme setup, include our setup script file.
 *
 * This file takes actions such as setting pretty permalinks, creating a common
 * menu, removing pings, etc.
 */
if ( ! get_option( 'evans_theme_setup' ) && 'setup' !== get_option( 'evans_theme_setup' ) ) {
	require_once 'includes/class-initial-install.php';
}

/*
 * If we haven't installed our plugins, include our auto install plugin.
 *
 * Library used to install plugins is TGM Plugin Installation by Thomas Griffin.
 * http://tgmpluginactivation.com/
 */
if ( ! get_option( 'evans_plugins_installed' ) && 'installed' !== get_option( 'evans_plugins_installed' ) ) {
	require_once 'includes/class-tgm-plugin-activation.php';
	require_once 'includes/auto-install-plugins.php';
}

/*
 * Once we have our plugins installed, create a flag to stop
 * the loading of the plugin install files.
 *
 * This code checks if 2 plugins at the end of our common plugin lists are
 * installed active. Once they are active a non-autoloaded WP Option variable
 * is set to stop loading of the plugin install files.
 */
add_action( 'admin_init', function() {
	if ( is_plugin_active( 'wpremote/plugin.php' ) && is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
		add_option( 'evans_plugins_installed', 'installed', '', 'no' );
	}
} );
