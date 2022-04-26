<?php // phpcs:disable
/*
Plugin Name: WP Airline Manager 4
Description: Manage stats for your Airline Manager 4 fleet.
Plugin URI: https://github.com/petenelson/wp-airline-manager-4
Version: 0.1.0
Author: Pete Nelson (@CodeGeekATX)
Text Domain: wp-airline-manager-4
Domain Path: /lang
*/
// phpcs:enable

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

// Useful global constants.
define( 'WP_AIRLINE_MANAGER_4_VERSION', '0.1.0' );
define( 'WP_AIRLINE_MANAGER_4_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'WP_AIRLINE_MANAGER_4_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'WP_AIRLINE_MANAGER_4_INC', WP_AIRLINE_MANAGER_4_PATH . 'includes/' );

// Include files.
$files = [
	'functions/core.php'     => 'Core',
	'functions/cli.php'      => false,
	'functions/cache.php'    => false,
	'post-types/route.php'   => 'PostTypes\Route',
	'taxonomies/airport.php' => 'Taxonomies\Airport',
];

foreach ( $files as $file => $namespace ) {
	require_once WP_AIRLINE_MANAGER_4_INC . $file;
	if ( ! empty( $namespace ) ) {
		$callback = '\WPAirlineManager4\\' . $namespace . '\setup';
		call_user_func( $callback );
	}
}

// Activation/Deactivation.
register_activation_hook( __FILE__, '\WPAirlineManager4\Core\activate' );
register_deactivation_hook( __FILE__, '\WPAirlineManager4\Core\deactivate' );
