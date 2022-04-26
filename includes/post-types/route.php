<?php
/**
 * Route post type.
 *
 * @package WP Airline Manager 4
 */

namespace WPAirlineManager4\PostTypes\Route;

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

	// Add this to taxonomies.
	add_filter( 'wp_am4_get_airport_object_types', n( 'opt_in' ) );
}

/**
 * Gets the post type name.
 *
 * @return string
 */
function get_post_type_name() {
	return apply_filters( 'wp_am4_get_vehicle_post_type_name', 'wp-am4-route' );
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
			'title',
			'editor',
			'author',
			'thumbnail',
			'excerpt',
		],
	];

	return apply_filters( 'wp_am4_get_vehicle_post_type_args', $args );
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
 * Gets a list of custom columns and labels.
 *
 * @return array
 */
function get_custom_columns() {

	// TODO
	$columns = [
		'battery_level'   => __( 'Battery', 'wp-airline-manager-4' ),
		'estimated_range' => __( 'Range', 'wp-airline-manager-4' ),
		'vehicle_id'      => __( 'ID', 'wp-airline-manager-4' ),
		'vin'             => __( 'VIN', 'wp-airline-manager-4' ),
	];

	return apply_filters( 'wp_am4_vehicle_get_custom_columns', $columns );
}

/**
 * Updates the columns for the list of vehicles in admin.
 *
 * @param array $columns List of columns.
 */
function update_table_columns( $columns ) {
	$columns = array_merge( $columns, get_custom_columns() );

	if ( isset( $columns['title'] ) ) {
		$columns['title'] = __( 'Name' );
	}

	if ( isset( $columns['author'] ) ) {
		unset( $columns['author'] );
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

	$vehicle_id = false;

	if ( in_array( $column, array_keys( get_custom_columns() ), true ) ) {
		$vehicle_id = Vehicle\get_vehicle_id( $post_id );
	}

	if ( ! empty( $vehicle_id ) ) {
		do_action( 'wp_am4_vehicle_do_custom_column_' . $column, $vehicle_id );
	}
}

/**
 * Outputs the vehicle ID value.
 *
 * @param  string $vehicle_id The vehicle ID.
 * @return void
 */
function column_vehicle_id( $vehicle_id ) {
	echo esc_html( $vehicle_id );
}

/**
 * Outputs the vehicle battery level.
 *
 * @param  string $vehicle_id The vehicle ID.
 * @return void
 */
function column_battery_level( $vehicle_id ) {
	$battery_level = Vehicle\get_battery_level( $vehicle_id );

	if ( false !== $battery_level ) {
		$battery_level = $battery_level . '%';
	}

	echo esc_html( $battery_level );
}

/**
 * Outputs the vehicle estimated range.
 *
 * @param  string $vehicle_id The vehicle ID.
 * @return void
 */
function column_estimated_range( $vehicle_id ) {
	$est_range = Vehicle\get_estimated_range( $vehicle_id );

	if ( false !== $est_range ) {
		// We'll look into km later.
		$est_range = $est_range . 'mi';
	}

	echo esc_html( $est_range );
}

/**
 * Outputs the VIN.
 *
 * @param  string $vehicle_id The vehicle ID.
 * @return void
 */
function column_vin( $vehicle_id ) {
	echo esc_html( Vehicle\get_vin( $vehicle_id ) );
}
