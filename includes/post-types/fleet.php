<?php
/**
 * Fleet post type.
 *
 * @package WP Airline Manager 4
 */

namespace WPAirlineManager4\PostTypes\Fleet;

use WPAirlineManager4\Taxonomies\Plane;
use WPAirlineManager4\Taxonomies\FleetFlag;
use WPAirlineManager4\PostTypes\Route;
use Fieldmanager_Group;
use Fieldmanager_Datasource_Term;
use Fieldmanager_Autocomplete;
use Fieldmanager_TextField;

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
	add_action( 'updated_postmeta', n( 'maybe_update_post_meta' ), 10, 3 );

	// Opt this in to taxonomies.
	add_filter( 'wp_am4_get_plane_object_types', n( 'opt_in' ) );
	add_filter( 'wp_am4_get_fleet_flag_object_types', n( 'opt_in' ) );

	add_action( 'manage_' . get_post_type_name() . '_posts_columns', n( 'update_table_columns' ) );
	add_action( 'manage_' . get_post_type_name() . '_posts_custom_column', n( 'handle_columns' ), 10, 2 );

	add_filter( 'redirect_post_location', n( 'redirect_to_list' ), 10, 2 );
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
		'menu_icon'           => get_icon_url(),
		'show_in_nav_menus'   => true,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
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
			'required'   => true,
			'datasource' => new \Fieldmanager_Datasource_Term( [ 'taxonomy' => Plane\get_taxonomy_name() ] ),
		]
	);

	$children['route'] = new \Fieldmanager_Autocomplete(
		__( 'Route', 'wp-airline-manager-4' ),
		[
			'required'   => true,
			'datasource' => new \Fieldmanager_Datasource_Post(
				[
					'query_args' => [
						'post_type' => Route\get_post_type_name(),
					],
				] )
		]
	);

	$children['income'] = new Fieldmanager_TextField(
		__( 'Flight Income $', 'wp-airline-manager-4' ),
		[
			'add_more_label' => __( 'Add Flight', 'wp-airline-manager-4' ),
			'limit'          => 10,
			'input_type'     => 'number',
			'default_value'  => 0,
			'field_class'    => 'small-text',
			'attributes'     => [
				'min' => 0,
			],
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

/**
 * Updates average income for a fleet plane.
 * @param int $post_id  Post ID.
 * @return void
 */
function update_average_income( $post_id ) {
	$fleet_details_income = get_post_meta( $post_id, 'fleet_details_income', true );

	$total_flights  = 0;
	$total_income   = 0;

	if ( is_array( $fleet_details_income ) ) {

		foreach ( $fleet_details_income as $income ) {
			$income = absint( $income );
			if ( ! empty( $income ) ) {
				$total_flights++;
				$total_income += $income;
			}
		}
	}

	$average_income = absint( ceil( $total_flights > 0 ? $total_income / $total_flights : 0 ) );

	update_post_meta( $post_id, 'fleet_average_income', $average_income );

	$flight_time   = get_flight_time( $post_id );
	$hourly_income = 0;

	if ( ! empty( $flight_time ) && ! empty( $average_income ) ) {
		$income_per_minute = $average_income / $flight_time;
		$hourly_income     = $income_per_minute * 60;
	}

	update_post_meta( $post_id, 'fleet_average_hourly_income', floor( $hourly_income ) );
}

/**
 * Updates the flight time for a fleet plane's route.
 *
 * @param  int $post_id The post ID.
 * @return void
 */
function update_flight_time( $post_id ) {

	$speed        = get_speed( $post_id );
	$distance    = 0;
	$flight_time = 0;

	$route_id = get_post_meta( $post_id, 'fleet_details_route', true );
	if ( ! empty( $route_id ) ) {
		$distance = Route\get_distance( $route_id );
	}

	if ( $speed > 0 ) {
		$flight_time = absint( ceil( ( $distance / $speed ) * 60 ) );
	}

	update_post_meta( $post_id, 'fleet_flight_time', $flight_time );
}

/**
 * Gets the speed of the assigned plane in km/h.
 *
 * @param  int $post_id The post ID.
 * @return int
 */
function get_speed( $post_id ) {

	$speed = 0;

	$term_id = get_post_meta( $post_id, 'fleet_details_plane', true );
	if ( ! empty( $term_id ) ) {
		$speed = Plane\get_speed( $term_id );
	}

	$fleet_flags = get_the_terms( $post_id, FleetFlag\get_taxonomy_name() );
	$fleet_flags = ! is_array( $fleet_flags ) ? [] : $fleet_flags;

	$slugs = wp_list_pluck( $fleet_flags, 'slug' );

	if ( in_array( 'speed-increase', $slugs, true ) ) {
		$speed = $speed * 1.10;
	}

	return $speed;
}

/**
 * Updates the aggregate income meta values when updating the
 * fleet_details_income post meta field.
 *
 * @param int    $meta_id  ID of metadata entry to update.
 * @param int    $post_id  Post ID.
 * @param string $meta_key Metadata key.
 * @return void
 */
function maybe_update_post_meta( $meta_id, $post_id, $meta_key ) {

	if ( get_post_type_name() == get_post_type( $post_id ) && 'publish' === get_post_status( $post_id ) ) {

		switch ( $meta_key ) {
			case 'fleet_details_income':
				update_average_income( $post_id );
				update_flight_time( $post_id );
				break;

			case 'fleet_details_plane':
			case 'fleet_details_route':
				update_flight_time( $post_id );
				break;
		}
	}
}

/**
 * Gets a list of custom columns and labels.
 *
 * @return array
 */
function get_custom_columns() {

	$columns = [
		'route'          => __( 'Route', 'wp-airline-manager-4' ),
		'flight_time'    => __( 'Flight Time', 'wp-airline-manager-4' ),
		'average_income' => __( 'Avg Income', 'wp-airline-manager-4' ),
		'hourly_income'  => __( 'Hourly Income', 'wp-airline-manager-4' ),
		'notes'          => __( 'Notes', 'wp-airline-manager-4' ),
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
		case 'average_income':
			$average_income = '$' . number_format( get_average_income( $post_id ) );
			echo esc_html( $average_income );
			break;

		case 'hourly_income':
			$hourly_income = '$' . number_format( get_average_hourly_income( $post_id ) );
			echo esc_html( $hourly_income );
			break;

		case 'flight_time':
			$minutes = get_flight_time( $post_id );
			$hours   = floor( $minutes / 60 );
			$minutes = $minutes - ( $hours * 60 );

			echo esc_html( $hours . ":" . str_pad( $minutes, 2, "0", STR_PAD_LEFT) );
			break;

		case 'notes':
			echo wp_kses_post( get_post_field( 'post_excerpt', $post_id, $context = 'display' ) );
			break;

		case 'route':
			$route_id = get_post_meta( $post_id, 'fleet_details_route', true );
			if ( ! empty( $route_id ) ) {
				$html = '<a href="' . admin_url( 'edit.php?post_type=' . rawurlencode( Route\get_post_type_name() ) . '&s=' . rawurlencode( get_post_field( 'post_title', $route_id ) ) ) . '">';
				$html .= get_post_field( 'post_title', $route_id ) . '</a>';
				echo wp_kses_post( $html );
			}
			break;
	}
}

/**
 * Gets the average income for a fleet plane.
 *
 * @param  int $post_id The post ID.
 * @return int
 */
function get_average_income( $post_id ) {

	if ( empty( get_post_meta( $post_id, 'fleet_average_income', true ) ) ) {
		update_average_income( $post_id );
	}

	return absint( get_post_meta( $post_id, 'fleet_average_income', true ) );
}

/**
 * Gets the flight time for this fleet plane's route.
 *
 * @param  int $post_id The post ID.
 * @return int
 */
function get_flight_time( $post_id ) {

	if ( empty( get_post_meta( $post_id, 'fleet_flight_time', true ) ) ) {
		update_flight_time( $post_id );
	}

	return absint( get_post_meta( $post_id, 'fleet_flight_time', true ) );
}

/**
 * Gets the average hourly income for a fleet plane.
 *
 * @param  int $post_id The post ID.
 * @return int
 */
function get_average_hourly_income( $post_id ) {

	if ( empty( get_post_meta( $post_id, 'fleet_average_hourly_income', true ) ) ) {
		update_average_income( $post_id );
	}

	return absint( get_post_meta( $post_id, 'fleet_average_hourly_income', true ) );
}

/**
 * Redirect to the list after updating a fleet plane.
 *
 * @param  string $location The default location.
 * @param  int    $post_id  The post ID.
 * @return string
 */
function redirect_to_list( $location, $post_id ) {

	if ( get_post_type_name() === get_post_type( $post_id ) ) {

		$location = add_query_arg(
			[
				'post_type' => rawurlencode( get_post_type_name() ),
				's'         => rawurlencode( get_post_field( 'post_title', $post_id, 'raw' ) ),
			],
			admin_url( 'edit.php' )
		);
	}

	return $location;
}
