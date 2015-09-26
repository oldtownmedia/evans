<?php

// If we haven't run theme setup, include our setup script
if ( !get_option( 'otm_theme_setup' ) && get_option( 'otm_theme_setup' ) != 'setup' ){
	require_once 'includes/initial-install.php';
}

require_once 'includes/class-tgm-plugin-activation.php';
require_once 'includes/auto-install-plugins.php';

