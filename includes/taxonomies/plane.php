<?php
/**
 * Plane taxonomy.
 *
 * @package WP Airline Manager 4
 */

namespace WPAirlineManager4\Taxonomies\Plane;

use Fieldmanager_TextField;
use Fieldmanager_Link;
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
	add_action( 'fm_term_' . get_taxonomy_name(), n( 'add_custom_fields' ) );
}

/**
 * Gets the taxonomy name.
 *
 * @return string
 */
function get_taxonomy_name() {
	return apply_filters( 'wp_am4_get_plane_taxonomy_name', 'wp-am4-plane' );
}

/**
 * Gets the args for registering the option codee taxonomy.
 *
 * @return array
 */
function get_taxonomy_args() {

	$labels = [
		'name'                  => _x( 'Planes', 'Taxonomy Planes', 'wp-airline-manager-4' ),
		'singular_name'         => _x( 'Plane', 'Taxonomy Plane', 'wp-airline-manager-4' ),
		'search_items'          => __( 'Search Planes', 'wp-airline-manager-4' ),
		'popular_items'         => __( 'Popular Planes', 'wp-airline-manager-4' ),
		'all_items'             => __( 'All Planes', 'wp-airline-manager-4' ),
		'parent_item'           => __( 'Parent Plane', 'wp-airline-manager-4' ),
		'parent_item_colon'     => __( 'Parent Plane', 'wp-airline-manager-4' ),
		'edit_item'             => __( 'Edit Plane', 'wp-airline-manager-4' ),
		'update_item'           => __( 'Update Plane', 'wp-airline-manager-4' ),
		'add_new_item'          => __( 'Add New Plane', 'wp-airline-manager-4' ),
		'new_item_name'         => __( 'New Plane Name', 'wp-airline-manager-4' ),
		'add_or_remove_items'   => __( 'Add or remove Planes', 'wp-airline-manager-4' ),
		'choose_from_most_used' => __( 'Choose from most used Planes', 'wp-airline-manager-4' ),
		'menu_name'             => __( 'Planes', 'wp-airline-manager-4' ),
	];

	$args = array(
		'labels'            => $labels,
		'public'            => true,
		'show_in_nav_menus' => true,
		'show_admin_column' => true,
		'hierarchical'      => false,
		'show_tagcloud'     => true,
		'show_ui'           => true,
		'query_var'         => true,
		'rewrite'           => 'plane',
		'query_var'         => true,
		'capabilities'      => array(),
	);

	return apply_filters( 'wp_am4_get_plane_taxonomy_args', $args );
}

/**
 * Registers the taxonomy.
 *
 * @return void
 */
function register() {
	// Use a filter to get a list of post types.
	$object_types = apply_filters( 'wp_am4_get_plane_object_types', [] );

	register_taxonomy( get_taxonomy_name(), $object_types, get_taxonomy_args() );
}

/**
 * Adds Fieldmanager custom fields.
 *
 * @return void
 */
function add_custom_fields() {

	$children = [];

	$children['speed'] = new \Fieldmanager_TextField(
		__( 'Speed (km/h)', 'wp-airline-manager-4' ),
		[
			'input_type'    => 'number',
			'default_value' => 0,
			'field_class'   => 'small-text',
			'attributes'    => [
				'min' => 1,
				'max' => 4000,
			],
		]
	);

	$children['range'] = new \Fieldmanager_TextField(
		__( 'Range (km)', 'wp-airline-manager-4' ),
		[
			'input_type'    => 'number',
			'default_value' => 0,
			'field_class'   => 'small-text',
			'attributes'    => [
				'min' => 1,
				'max' => 25000,
			],
		]
	);

	$children['seats'] = new \Fieldmanager_TextField(
		__( 'Seats', 'wp-airline-manager-4' ),
		[
			'input_type'    => 'number',
			'default_value' => 0,
			'field_class'   => 'small-text',
			'attributes'    => [
				'min' => 1,
				'max' => 1000,
			],
		]
	);

	$children['runway'] = new \Fieldmanager_TextField(
		__( 'Runway (ft)', 'wp-airline-manager-4' ),
		[
			'input_type'    => 'number',
			'default_value' => 0,
			'field_class'   => 'small-text',
			'attributes'    => [
				'min' => 1,
				'max' => 15000,
			],
		]
	);

	$children['fuel'] = new \Fieldmanager_TextField(
		__( 'Fuel (lbs/km)', 'wp-airline-manager-4' ),
		[
			'input_type'    => 'number',
			'default_value' => 0,
			'field_class'   => 'small-text',
			'attributes'    => [
				'min'  => 0,
				'max'  => 100,
				'step' => 0.01
			],
		]
	);

	$children['type'] = new \Fieldmanager_Select(
		__( 'Type', 'wp-airline-manager-4' ),
		[
			'options' => [
				'pax'   => __( 'Passenger', 'wp-airline-manager-4' ),
				'cargo' => __( 'Cargo', 'wp-airline-manager-4' ),
			],
		]
	);

	$children['image_url'] = new \Fieldmanager_Link( __( 'Image URL', 'wp-airline-manager-4' ) );

	$fm = new Fieldmanager_Group(
		[
			'name'           => 'plane_details',
			'serialize_data' => false,
			'children'       => $children,
		]
	);

	$fm->add_term_meta_box( 'Plane Details', get_taxonomy_name() );
}

/**
 * Gets the speed of the plane.
 *
 * @param  int $term_id The term ID.
 * @return int
 */
function get_speed( $term_id ) {
	return absint( get_term_meta( $term_id, 'plane_details_speed', true ) );
}
