<?php
/**
 * WP-CLI commands.
 *
 * @package WP Airline Manager 4
 */

namespace WPAirlineManager4\CLI;

use WPAirlineManager4\Taxonomies\Airport;
use WPAirlineManager4\Taxonomies\Plane;
use WPAirlineManager4\PostTypes\Route;
use WPAirlineManager4\PostTypes\Fleet;

if ( ! defined( 'WP_CLI' ) || ( defined( 'WP_CLI' ) && ! WP_CLI ) ) {
	return;
}

/**
 * Create a namespaced function.
 *
 * @param  string $function The function name,
 * @return string
 */
function n( $function ) {
	return __NAMESPACE__ . "\\$function";
};

$commands = [
	'import-airports' => n( 'import_airports' ),
	'import-planes'   => n( 'import_planes' ),
	'import-routes'   => n( 'import_routes' ),
	'update-routes'   => n( 'update_routes' ),
];

foreach ( $commands as $command => $callback ) {
	\WP_CLI::add_command( 'wpam4 ' . $command, $callback );
}

/**
 * Imports airports
 *.
 * @return void
 */
function import_airports() {

	$airports = file_get_contents( WP_AIRLINE_MANAGER_4_PATH . 'data/airports.json' );
	$airports = json_decode( $airports, true );

	$taxonomy = Airport\get_taxonomy_name();

	foreach ( $airports as $airport_code => $data ) {
		$data = wp_parse_args(
			$data,
			[
				'runway'  => 0,
				'market'  => 0.78,
				'country' => '',
				'name'    => '',
			]
		);

		\WP_CLI::line( 'Adding ' . $airport_code . ' ' . $data['name'] );

		$term_data = wp_insert_term(
			$airport_code,
			$taxonomy,
			[
				'description' => $data['name'],
			]
		);

		update_term_meta( $term_data['term_id'], 'airport_details_runway', $data['runway'] );
		update_term_meta( $term_data['term_id'], 'airport_details_market', $data['market'] * 100 );
		update_term_meta( $term_data['term_id'], 'airport_details_country', $data['country'] );
	}

	\WP_CLI::success( 'Done' );
}

/**
 * Import Planes
 *
 * @return void
 */
function import_planes() {

	$planes = file_get_contents( WP_AIRLINE_MANAGER_4_PATH . 'data/planes.json' );
	$planes = json_decode( $planes, true );

	$taxonomy = Plane\get_taxonomy_name();

	foreach ( $planes as $plane => $data ) {
		$data = wp_parse_args(
			$data,
			[
				'range'  => 0,
				'speed'  => 0,
				'seats'  => 0,
				'runway' => 0,
				'fuel'   => 0,
			]
		);

		\WP_CLI::line( 'Adding ' . $plane );

		$term_data = wp_insert_term(
			$plane,
			$taxonomy
		);

		update_term_meta( $term_data['term_id'], 'plane_details_range', $data['range'] );
		update_term_meta( $term_data['term_id'], 'plane_details_speed', $data['speed'] );
		update_term_meta( $term_data['term_id'], 'plane_details_seats', $data['seats'] );
		update_term_meta( $term_data['term_id'], 'plane_details_runway', $data['runway'] );
		update_term_meta( $term_data['term_id'], 'plane_details_fuel', $data['fuel'] );
		update_term_meta( $term_data['term_id'], 'plane_details_type', 'pax' );
	}

	\WP_CLI::success( 'Done' );
}

/**
 * Import Routes
 *
 * @return void
 */
function import_routes() {

	$from = 'EDDF';

	$routes = file_get_contents( WP_AIRLINE_MANAGER_4_PATH . 'data/routes/eddf.json' );
	$routes = json_decode( $routes, true );

	$airport = Airport\get_taxonomy_name();
	$route   = Route\get_post_type_name();

	// $query = new \WP_Query(
	// 	[
	// 		'posts_per_page' => -1,
	// 		'post_type'   => $route,
	// 	]
	// );

	// foreach ( $query->posts as $post ) {
	// 	wp_delete_post( $post->ID, true );
	// }

	foreach ( $routes as $to => $data ) {
		$to_airport = get_term_by( 'slug', $to, $airport );
		if ( ! is_a( $to_airport, '\WP_Term' ) ) {
			die( $to ); // phpcs:ignore
		}
	}

	foreach ( $routes as $to => $data ) {

		\WP_CLI::line( 'Adding ' . $to );

		$post_id = wp_insert_post(
			[
				'post_type'   => $route,
				'post_status' => 'publish',
				'post_title'  => "{$from}-{$to}",
			]
		);

		update_post_meta( $post_id, 'route_details_distance', $data['distance'] );
		update_post_meta( $post_id, 'route_details_demand_y', $data['demand']['y'] );
		update_post_meta( $post_id, 'route_details_demand_j', $data['demand']['j'] );
		update_post_meta( $post_id, 'route_details_demand_f', $data['demand']['f'] );

		$from_airport = get_term_by( 'slug', $from, $airport );
		$to_airport   = get_term_by( 'slug', $to, $airport );

		wp_set_post_terms( $post_id, [ $from_airport->term_id, $to_airport->term_id ], $airport );

		update_post_meta( $post_id, 'route_details_from', $from_airport->term_id );
		update_post_meta( $post_id, 'route_details_to', $to_airport->term_id );
	}

	\WP_CLI::success( 'Done' );
}

/**
 * Updates routes post meta.
 *
 * @return void
 */
function update_routes() {

	$query = new \WP_Query(
		[
			'posts_per_page' => -1,
			'post_type'   => Fleet\get_post_type_name(),
		]
	);

	foreach ( $query->posts as $post ) {
		Fleet\update_flight_time( $post->ID );
		Fleet\update_average_income( $post->ID );
	}
}
