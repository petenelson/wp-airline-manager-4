<?php
/**
 * Fleet Flag taxonomy.
 *
 * @package WP Airline Manager 4
 */

namespace WPAirlineManager4\Taxonomies\FleetFlag;

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
	add_action( 'init', n( 'register' ) );
}

/**
 * Gets the taxonomy name.
 *
 * @return string
 */
function get_taxonomy_name() {
	return apply_filters( 'wp_am4_get_fleet_flag_taxonomy_name', 'wp-am4-fleet-flag' );
}

/**
 * Gets the args for registering the option codee taxonomy.
 *
 * @return array
 */
function get_taxonomy_args() {

	$labels = [
		'name'                  => _x( 'Fleet Flags', 'Taxonomy Fleet Flags', 'wp-airline-manager-4' ),
		'singular_name'         => _x( 'Fleet Flag', 'Taxonomy Fleet Flag', 'wp-airline-manager-4' ),
		'search_items'          => __( 'Search Fleet Flags', 'wp-airline-manager-4' ),
		'popular_items'         => __( 'Popular Fleet Flags', 'wp-airline-manager-4' ),
		'all_items'             => __( 'All Fleet Flags', 'wp-airline-manager-4' ),
		'parent_item'           => __( 'Parent Fleet Flag', 'wp-airline-manager-4' ),
		'parent_item_colon'     => __( 'Parent Fleet Flag', 'wp-airline-manager-4' ),
		'edit_item'             => __( 'Edit Fleet Flag', 'wp-airline-manager-4' ),
		'update_item'           => __( 'Update Fleet Flag', 'wp-airline-manager-4' ),
		'add_new_item'          => __( 'Add New Fleet Flag', 'wp-airline-manager-4' ),
		'new_item_name'         => __( 'New Fleet Flag Name', 'wp-airline-manager-4' ),
		'add_or_remove_items'   => __( 'Add or remove Fleet Flags', 'wp-airline-manager-4' ),
		'choose_from_most_used' => __( 'Choose from most used Fleet Flags', 'wp-airline-manager-4' ),
		'menu_name'             => __( 'Fleet Flags', 'wp-airline-manager-4' ),
	];

	$args = array(
		'labels'            => $labels,
		'public'            => true,
		'show_in_nav_menus' => true,
		'show_admin_column' => false,
		'hierarchical'      => false,
		'show_tagcloud'     => true,
		'show_ui'           => true,
		'query_var'         => true,
		'rewrite'           => false,
		'query_var'         => true,
		'capabilities'      => array(),
	);

	return apply_filters( 'wp_am4_get_airport_taxonomy_args', $args );
}

/**
 * Registers the taxonomy.
 *
 * @return void
 */
function register() {
	// Use a filter to get a list of post types.
	$object_types = apply_filters( 'wp_am4_get_fleet_flag_object_types', [] );

	register_taxonomy( get_taxonomy_name(), $object_types, get_taxonomy_args() );
}
