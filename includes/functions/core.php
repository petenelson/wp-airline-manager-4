<?php
/**
 * Core plugin functionality.
 *
 * @package WP Airline Manager 4
 */

namespace WPAirlineManager4\Core;

/**
 * Quickly provide a namespaced way to get functions.
 *
 * @param string $function Name of function in namespace.
 * @return string
 */
function n( $function ) {
	return __NAMESPACE__ . "\\$function";
}

/**
 * Default setup routine
 *
 * @return void
 */
function setup() {
	add_action( 'init', n( 'i18n' ) );
	add_action( 'init', n( 'register_scripts_styles' ) );
	add_action( 'wp_enqueue_scripts', n( 'scripts' ) );
	add_action( 'wp_enqueue_scripts', n( 'styles' ) );
	// add_action( 'admin_enqueue_scripts', n( 'localize_admin_data' ) );
	add_action( 'admin_enqueue_scripts', n( 'admin_scripts' ) );
	add_action( 'admin_enqueue_scripts', n( 'admin_styles' ) );
}

/**
 * Registers the default textdomain.
 *
 * @return void
 */
function i18n() {
	$locale = apply_filters( 'plugin_locale', get_locale(), 'wp-airline-manager-4' );
	load_textdomain( 'wp-airline-manager-4', WP_LANG_DIR . '/wp-airline-manager-4/wp-airline-manager-4-' . $locale . '.mo' );
	load_plugin_textdomain( 'wp-airline-manager-4', false, plugin_basename( WP_AIRLINE_MANAGER_4_PATH ) . '/lang/' );
}

/**
 * Register scripts and styles.
 *
 * @return void
 */
function register_scripts_styles() {
	wp_register_script(
		'wp-airline-manager-4-admin',
		WP_AIRLINE_MANAGER_4_URL . '/dist/js/admin.js',
		[],
		WP_AIRLINE_MANAGER_4_VERSION,
		true
	);

	wp_register_script(
		'wp-airline-manager-4',
		WP_AIRLINE_MANAGER_4_URL . '/dist/js/frontend.js',
		[],
		WP_AIRLINE_MANAGER_4_VERSION,
		true
	);

	wp_register_style(
		'wp-airline-manager-4-admin',
		WP_AIRLINE_MANAGER_4_URL . '/dist/css/admin-style.css',
		[],
		WP_AIRLINE_MANAGER_4_VERSION
	);
}

/**
 * Performs any plugin upgrades due to version changes.
 *
 * @return void
 */
function upgrade() {

}

/**
 * Activate the plugin
 *
 * @return void
 */
function activate() {

}

/**
 * Deactivate the plugin
 *
 * Uninstall routines should be in uninstall.php
 *
 * @return void
 */
function deactivate() {

}

/**
 * Enqueue scripts for front-end.
 *
 * @return void
 */
function scripts() {
	wp_enqueue_script( 'jquery' );
}

/**
 * Enqueue scripts for admin.
 *
 * @return void
 */
function admin_scripts() {
	wp_enqueue_script( 'wp-airline-manager-4-admin' );
}

/**
 * Enqueue styles for front-end.
 *
 * @return void
 */
function styles() {

}

/**
 * Enqueue styles for admin.
 *
 * @return void
 */
function admin_styles() {
	wp_enqueue_style( 'wp-airline-manager-4-admin' );
}
