<?php
/**
 * Route post type.
 *
 * @package WP Airline Manager 4
 */

namespace WPAirlineManager4\PostTypes\Route;

use Fieldmanager_TextField;
use Fieldmanager_Group;

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
	add_action( 'fm_post_' . get_post_type_name(), n( 'add_custom_fields' ) );

	// Add this to taxonomies.
	add_filter( 'wp_am4_get_airport_object_types', n( 'opt_in' ) );
}

/**
 * Gets the post type name.
 *
 * @return string
 */
function get_post_type_name() {
	return apply_filters( 'wp_am4_get_route_post_type_name', 'wp-am4-route' );
}

/**
 * Opts this post type into other functionality.
 *
 * @param  array $post_types List of post types.
 * @return array
 */
function opt_in( $post_types ) {
	$post_types[] = get_post_type_name();
	return $post_types;
}

/**
 * Gets the post type args for registering the post type.
 *
 * @return array
 */
function get_post_type_args() {

	$labels = [
		'name'               => __( 'Routes', 'wp-airline-manager-4' ),
		'singular_name'      => __( 'Route', 'wp-airline-manager-4' ),
		'add_new'            => _x( 'Add New Route', 'wp-airline-manager-4', 'wp-airline-manager-4' ),
		'add_new_item'       => __( 'Add New Route', 'wp-airline-manager-4' ),
		'edit_item'          => __( 'Edit Route', 'wp-airline-manager-4' ),
		'new_item'           => __( 'New Route', 'wp-airline-manager-4' ),
		'view_item'          => __( 'View Route', 'wp-airline-manager-4' ),
		'search_items'       => __( 'Search Routes', 'wp-airline-manager-4' ),
		'not_found'          => __( 'No Routes found', 'wp-airline-manager-4' ),
		'not_found_in_trash' => __( 'No Routes found in Trash', 'wp-airline-manager-4' ),
		'parent_item_colon'  => __( 'Parent Route:', 'wp-airline-manager-4' ),
		'menu_name'          => __( 'Routes', 'wp-airline-manager-4' ),
	];

	$args = [
		'labels'              => $labels,
		'hierarchical'        => false,
		'description'         => __( 'List of Routes', 'wp-airline-manager-4' ),
		'taxonomies'          => [],
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_admin_bar'   => true,
		'show_in_rest'        => true,
		'menu_position'       => null,
		'menu_icon'           => null,
		'show_in_nav_menus'   => true,
		'publicly_queryable'  => true,
		'exclude_from_search' => false,
		'has_archive'         => true,
		'query_var'           => true,
		'can_export'          => true,
		'rewrite'             => [
			'slug' => 'route',
		],
		'capability_type'     => 'post',
		'supports'            => [
			'excerpt',
		],
	];

	return apply_filters( 'wp_am4_get_route_post_type_args', $args );
}

/**
 * Registers the post types.
 *
 * @return void
 */
function register() {
	register_post_type( get_post_type_name(), get_post_type_args() );
}

/**
 * Adds Fieldmanager custom fields.
 *
 * @return void
 */
function add_custom_fields() {

	$children = [];

	$children['distance'] = new \Fieldmanager_TextField(
		__( 'Distance (km)', 'wp-airline-manager-4' ),
		[
			'input_type'    => 'number',
			'default_value' => 0,
			'field_class'   => 'small-text',
			'attributes'    => [
				'min' => 1,
			],
		]
	);

	$fm = new Fieldmanager_Group(
		[
			'name'           => 'route_details',
			'serialize_data' => false,
			'children'       => $children,
		]
	);

	$fm->add_meta_box( 'Route Details', get_post_type_name() );
}