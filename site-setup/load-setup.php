<?php
namespace evans;

// If we haven't run theme setup, include our setup script
if ( !get_option( 'evans_theme_setup' ) && get_option( 'evans_theme_setup' ) != 'setup' ){
	require_once 'includes/class-initial-install.php';
}

// If we haven't installed our plugins, include our setup script
if ( !get_option( 'evans_plugins_installed' ) && get_option( 'evans_plugins_installed' ) != 'installed' ){
	require_once 'includes/class-tgm-plugin-activation.php';
	require_once 'includes/auto-install-plugins.php';
}

/*
 * Once we have our plugins installed, create a flag to stop
 * the loading of the plugin install files
 */
add_action( 'admin_init', __NAMESPACE__ . '\flag_plugins_installed' );
function flag_plugins_installed(){
	if ( is_plugin_active( 'wpremote/plugin.php' ) && is_plugin_active( 'wordpress-seo/wp-seo.php' ) ){
		add_option( 'evans_plugins_installed', 'installed', '', 'no' );
	}
}