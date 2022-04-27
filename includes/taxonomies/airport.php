<?php
/**
 * Airport taxonomy.
 *
 * @package WP Airline Manager 4
 */

namespace WPAirlineManager4\Taxonomies\Airport;

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
	add_action( 'fm_term_' . get_taxonomy_name(), n( 'add_custom_fields' ) );
}

/**
 * Gets the taxonomy name.
 *
 * @return string
 */
function get_taxonomy_name() {
	return apply_filters( 'wp_am4_get_airport_taxonomy_name', 'wp-am4-airport' );
}

/**
 * Gets the args for registering the option codee taxonomy.
 *
 * @return array
 */
function get_taxonomy_args() {

	$labels = [
		'name'                  => _x( 'Airports', 'Taxonomy Airports', 'wp-airline-manager-4' ),
		'singular_name'         => _x( 'Airport', 'Taxonomy Airport', 'wp-airline-manager-4' ),
		'search_items'          => __( 'Search Airports', 'wp-airline-manager-4' ),
		'popular_items'         => __( 'Popular Airports', 'wp-airline-manager-4' ),
		'all_items'             => __( 'All Airports', 'wp-airline-manager-4' ),
		'parent_item'           => __( 'Parent Airport', 'wp-airline-manager-4' ),
		'parent_item_colon'     => __( 'Parent Airport', 'wp-airline-manager-4' ),
		'edit_item'             => __( 'Edit Airport', 'wp-airline-manager-4' ),
		'update_item'           => __( 'Update Airport', 'wp-airline-manager-4' ),
		'add_new_item'          => __( 'Add New Airport', 'wp-airline-manager-4' ),
		'new_item_name'         => __( 'New Airport Name', 'wp-airline-manager-4' ),
		'add_or_remove_items'   => __( 'Add or remove Airports', 'wp-airline-manager-4' ),
		'choose_from_most_used' => __( 'Choose from most used Airports', 'wp-airline-manager-4' ),
		'menu_name'             => __( 'Airports', 'wp-airline-manager-4' ),
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
		'rewrite'           => 'airport',
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
	$object_types = apply_filters( 'wp_am4_get_airport_object_types', [] );

	register_taxonomy( get_taxonomy_name(), $object_types, get_taxonomy_args() );
}

/**
 * Adds Fieldmanager custom fields.
 *
 * @return void
 */
function add_custom_fields() {

	$children = [];

	$children['hub']     = new \Fieldmanager_Checkbox( __( 'Hub', 'wp-airline-manager-4' ) );
	$children['country'] = new \Fieldmanager_TextField( __( 'Country', 'wp-airline-manager-4' ) );

	$children['runway'] = new \Fieldmanager_TextField(
		__( 'Runway (ft)', 'wp-airline-manager-4' ),
		[
			'input_type'    => 'number',
			'default_value' => 0,
			'field_class'   => 'small-text',
			'attributes'    => [
				'min' => 1,
				'max' => 20000,
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
				'max' => 20000,
			],
		]
	);

	$children['market'] = new \Fieldmanager_TextField(
		__( 'Market (%)', 'wp-airline-manager-4' ),
		[
			'input_type'    => 'number',
			'default_value' => 0,
			'field_class'   => 'small-text',
			'attributes'    => [
				'min' => 1,
				'max' => 100,
			],
		]
	);

	$fm = new Fieldmanager_Group(
		[
			'name'           => 'airport_details',
			'serialize_data' => false,
			'children'       => $children,
		]
	);

	$fm->add_term_meta_box( 'Airport Details', get_taxonomy_name() );
}
