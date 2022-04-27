<?php
/**
 * Fleet post type.
 *
 * @package WP Airline Manager 4
 */

namespace WPAirlineManager4\PostTypes\Fleet;

use WPAirlineManager4\Taxonomies\Plane;
use WPAirlineManager4\PostTypes\Route;

use Fieldmanager_Group;
use Fieldmanager_Datasource_Term;
use Fieldmanager_Autocomplete;

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

	// TODO needs route admin column.

	// Opt this in to taxonomies.
	add_filter( 'wp_am4_get_plane_object_types', n( 'opt_in' ) );
}

/**
 * Gets the post type name.
 *
 * @return string
 */
function get_post_type_name() {
	return apply_filters( 'wp_am4_get_fleet_plane_post_type_name', 'wp-am4-fleet-plane' );
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
		'name'               => __( 'Fleet Planes', 'wp-airline-manager-4' ),
		'singular_name'      => __( 'Fleet Plane', 'wp-airline-manager-4' ),
		'add_new'            => _x( 'Add New Fleet Plane', 'wp-airline-manager-4', 'wp-airline-manager-4' ),
		'add_new_item'       => __( 'Add New Fleet Plane', 'wp-airline-manager-4' ),
		'edit_item'          => __( 'Edit Fleet Plane', 'wp-airline-manager-4' ),
		'new_item'           => __( 'New Fleet Plane', 'wp-airline-manager-4' ),
		'view_item'          => __( 'View Fleet Plane', 'wp-airline-manager-4' ),
		'search_items'       => __( 'Search Fleet Planes', 'wp-airline-manager-4' ),
		'not_found'          => __( 'No Fleet Planes found', 'wp-airline-manager-4' ),
		'not_found_in_trash' => __( 'No Fleet Planes found in Trash', 'wp-airline-manager-4' ),
		'parent_item_colon'  => __( 'Parent Fleet Plane:', 'wp-airline-manager-4' ),
		'menu_name'          => __( 'Fleet Planes', 'wp-airline-manager-4' ),
	];

	$args = [
		'labels'              => $labels,
		'hierarchical'        => false,
		'description'         => __( 'List of Fleet Planes', 'wp-airline-manager-4' ),
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
			'slug' => 'fleet-plane',
		],
		'capability_type'     => 'post',
		'supports'            => [
			'title',
			'excerpt',
		],
	];

	return apply_filters( 'wp_am4_get_fleet_plane_post_type_args', $args );
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

	$children['plane'] = new \Fieldmanager_Autocomplete(
		__( 'Plane', 'wp-airline-manager-4' ),
		[
			'datasource' => new \Fieldmanager_Datasource_Term( [ 'taxonomy' => Plane\get_taxonomy_name() ] ),
		]
	);

	$children['route'] = new \Fieldmanager_Autocomplete(
		__( 'Route', 'wp-airline-manager-4' ),
		[
			'datasource' => new \Fieldmanager_Datasource_Post(
				[
					'query_args' => [
						'post_type' => Route\get_post_type_name(),
					],
				] )
		]
	);

	$fm = new Fieldmanager_Group(
		[
			'name'           => 'fleet_details',
			'serialize_data' => false,
			'children'       => $children,
		]
	);

	$fm->add_meta_box( 'Fleet Details', get_post_type_name() );
}
