<?php
/**
 * Plugin Name: Story Chief ACF
 * Plugin URI: https://storychief.io/wordpress-acf
 * Description: This plugin lets Storychief and ACF work together .
 * Version: 1.0.6
 * Author: Gregory Claeyssens
 * Author URI: http://storychief.io
 * License: GPL2
 */

// Make sure we don't expose any info if called directly
if ( ! function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'STORYCHIEF_ACF_VERSION', '1.0.6' );
define( 'STORYCHIEF_ACF__MINIMUM_WP_VERSION', '4.6' );
define( 'STORYCHIEF_ACF__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'STORYCHIEF_ACF__PLUGIN_BASE_NAME', plugin_basename( __FILE__ ) );

require_once( STORYCHIEF_ACF__PLUGIN_DIR . 'class.storychief-acf.php' );

add_action( 'init', array( 'Storychief_ACF', 'init' ) );

if ( is_admin() ) {
	require_once( STORYCHIEF_ACF__PLUGIN_DIR . 'class.storychief-acf-admin.php' );
	add_action( 'init', array( 'Storychief_ACF_Admin', 'init' ) );
	add_filter( 'plugin_action_links_' . STORYCHIEF_ACF__PLUGIN_BASE_NAME, array(
		'Storychief_ACF_Admin',
		'settings_link'
	) );
}
