<?php
/**
 * Route post type.
 *
 * @package WP Airline Manager 4
 */

namespace WPAirlineManager4\PostTypes\Route;

use WPAirlineManager4\Taxonomies\Airport;
use Fieldmanager_TextField;
use Fieldmanager_Group;
use Fieldmanager_Autocomplete;
use Fieldmanager_Datasource_Term;

use function WPAirlineManager4\Core\get_icon_url;

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
	add_filter( 'wp_insert_post_data', n( 'set_custom_post_title' ) );

	// Opt this in to taxonomies.
	add_filter( 'wp_am4_get_airport_object_types', n( 'opt_in' ) );

	add_action( 'manage_' . get_post_type_name() . '_posts_columns', n( 'update_table_columns' ) );
	add_action( 'manage_' . get_post_type_name() . '_posts_custom_column', n( 'handle_columns' ), 10, 2 );
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
		'menu_icon'           => get_icon_url(),
		'show_in_nav_menus'   => true,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
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

	$children['from'] = new \Fieldmanager_Autocomplete(
		__( 'From (Airport Code)', 'wp-airline-manager-4' ),
		[
			'required'   => true,
			'datasource' => new \Fieldmanager_Datasource_Term( [ 'taxonomy' => Airport\get_taxonomy_name() ] ),
		]
	);

	$children['to'] = new \Fieldmanager_Autocomplete(
		__( 'To (Airport Code)', 'wp-airline-manager-4' ),
		[
			'required'   => true,
			'datasource' => new \Fieldmanager_Datasource_Term( [ 'taxonomy' => Airport\get_taxonomy_name() ] ),
		]
	);

	$children['distance'] = new \Fieldmanager_TextField(
		__( 'Distance (km)', 'wp-airline-manager-4' ),
		[
			'required'      => true,
			'input_type'    => 'number',
			'default_value' => 0,
			'field_class'   => 'small-text',
			'attributes'    => [
				'min' => 1,
			],
		]
	);

	$children['demand_y'] = new \Fieldmanager_TextField(
		__( 'Demand (Y/F/J)', 'wp-airline-manager-4' ),
		[
			'required'      => true,
			'input_type'    => 'number',
			'default_value' => 0,
			'field_class'   => 'small-text',
			'attributes'    => [
				'min' => 0,
				'max' => 3000,
			],
		]
	);

	$children['demand_j'] = new \Fieldmanager_TextField(
		[
			'required'      => true,
			'input_type'    => 'number',
			'default_value' => 0,
			'field_class'   => 'small-text',
			'attributes'    => [
				'min' => 0,
				'max' => 3000,
			],
		]
	);

	$children['demand_f'] = new \Fieldmanager_TextField(
		[
			'required'      => true,
			'input_type'    => 'number',
			'default_value' => 0,
			'field_class'   => 'small-text',
			'attributes'    => [
				'min' => 0,
				'max' => 3000,
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

/**
 * Sets the custom post title based on the From/To airport codes.
 *
 * @param array $data An array of slashed, sanitized, and processed post data.
 * @param array
 */
function set_custom_post_title( $data ) {

	if ( $data['post_type'] !== get_post_type_name() || 'publish' !== $data['post_status'] ) {
		return $data;
	}

	$post = filter_var_array(
		$_POST, // phpcs:ignore
		[
			'route_details' => [
				'flags' => FILTER_REQUIRE_ARRAY,
			],
		],
	);

	if ( empty( $post['route_details'] ) ) {
		return $data;
	}

	// Get the from/to airport terms.
	$route_details = filter_var_array(
		$post['route_details'],
		[
			'from' => FILTER_SANITIZE_NUMBER_INT,
			'to'   => FILTER_SANITIZE_NUMBER_INT,
		]
	);

	$route_details['from'] = absint( $route_details['from'] );
	$route_details['to']   = absint( $route_details['to'] );

	if ( ! empty( $route_details['from'] ) && ! empty( $route_details['to'] ) ) {

		$from_term = get_term( $route_details['from'] );
		$to_term   = get_term( $route_details['to'] );

		$data['post_title'] = strtoupper( $from_term->name ) . '-' . strtoupper( $to_term->name );
		$data['post_name']  = strtolower( $data['post_title'] );
	}

	return $data;
}

/**
 * Gets the route distance.
 *
 * @param  int $post_id The post ID.
 * @return int
 */
function get_distance( $post_id ) {
	return absint( get_post_meta( $post_id, 'route_details_distance', true ) );
}

/**
 * Gets a list of custom columns and labels.
 *
 * @return array
 */
function get_custom_columns() {

	$columns = [
		'fleet_plane' => __( 'Fleet Plane', 'wp-airline-manager-4' ),
	];

	return apply_filters( 'wp_am4_fleet_get_custom_columns', $columns );
}

/**
 * Updates the columns for the list of fleet planes in admin.
 *
 * @param array $columns List of columns.
 */
function update_table_columns( $columns ) {
	$columns = array_merge( $columns, get_custom_columns() );

	if ( isset( $columns['title'] ) ) {
		$columns['title'] = __( 'Name' );
	}

	$remove = [
		'author',
		'date',
	];

	foreach ( $remove as $column ) {
		if ( isset( $columns[ $column ] ) ) {
			unset( $columns[ $column ] );
		}
	}

	return $columns;
}

/**
 * Handles the custom columns.
 *
 * @param  string $column  The column name.
 * @param  int    $post_id The post ID.
 * @return void
 */
function handle_columns( $column, $post_id ) {

	switch ( $column ) {
		case 'fleet_plane':
			echo esc_html( 'TODO' );
			break;
	}
}
