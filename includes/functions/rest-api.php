<?php
/**
 * REST API functionality.
 *
 * @package WP Airline Manager 4
 */

namespace WPAirlineManager4\REST_API;

use WPAirlineManager4\Taxonomies\Airport;
use WPAirlineManager4\Taxonomies\Plane;
use WPAirlineManager4\PostTypes\Route;

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
	add_action( 'rest_api_init', n( 'register_routes' ) );
}

/**
 * Registers REST routes.
 * @return void
 */
function register_routes() {

	$routes = [
		'airports' => 'handle_airports',
		'planes'   => 'handle_planes',
		'routes'   => 'handle_routes',
	];

	foreach ( $routes as $route => $callback ) {

		register_rest_route(
			'wp-am4',
			$route,
			[
				'permission_callback' => '__return_true',
				'callback'            => n( $callback ),
			]
		);
	}
}

/**
 * REST handler for getting a list of airports.
 *
 * @return WP_REST_Response
 */
function handle_airports() {

	$response = [];

	$term_args = [
		'taxonomy'   => Airport\get_taxonomy_name(),
		'hide_empty' => false,
	];

	$cache_key   = 'wpam4-rest-airports-' . md5( wp_json_encode( $term_args ) . wp_cache_get_last_changed( 'terms' ) );
	$cached_list = wp_cache_get( $cache_key );

	if ( false !== $cached_list ) {
		return rest_ensure_response( $cached_list );
	}

	$term_query = new \WP_Term_Query( $term_args );

	foreach ( $term_query->get_terms() as $term ) {
		$response[ $term->name  ] = [
			'name'    => $term->description,
			'runway'  => absint( get_term_meta( $term->term_id, 'airport_details_runway', true ) ),
			'market'  => absint( get_term_meta( $term->term_id, 'airport_details_market', true ) ) / 100,
			'country' => get_term_meta( $term->term_id, 'airport_details_country', true )
		];
	}

	wp_cache_set( $cache_key, $response, '', DAY_IN_SECONDS * 1 );

	return rest_ensure_response( $response );
}

/**
 * REST handler for getting a list of planes.
 *
 * @return WP_REST_Response
 */
function handle_planes() {

	$response = [];

	$term_args = [
		'taxonomy'   => Plane\get_taxonomy_name(),
		'hide_empty' => false,
	];

	$cache_key   = 'wpam4-rest-planes-' . md5( wp_json_encode( $term_args ) . wp_cache_get_last_changed( 'terms' ) );
	$cached_list = wp_cache_get( $cache_key );

	if ( false !== $cached_list ) {
		return rest_ensure_response( $cached_list );
	}

	$term_query = new \WP_Term_Query( $term_args );

	foreach ( $term_query->get_terms() as $term ) {
		$response[ $term->name  ] = [
			'runway'  => absint( get_term_meta( $term->term_id, 'plane_details_runway', true ) ),
			'range'   => absint( get_term_meta( $term->term_id, 'plane_details_range', true ) ),
			'speed'   => absint( get_term_meta( $term->term_id, 'plane_details_speed', true ) ),
			'seats'   => absint( get_term_meta( $term->term_id, 'plane_details_seats', true ) ),
			'fuel'    => get_term_meta( $term->term_id, 'plane_details_fuel', true ),
			'cost'    => 0,
			'a-check' => 0,
		];
	}

	wp_cache_set( $cache_key, $response, '', DAY_IN_SECONDS * 1 );

	return rest_ensure_response( $response );
}

/**
 * REST handler for getting a list of routes.
 *
 * @return WP_REST_Response
 */
function handle_routes() {

	$response = [];

	$query_args = [
		'post_type'      => Route\get_post_type_name(),
		'posts_per_page' => -1,
	];

	$cache_key   = 'wpam4-rest-routes-' . md5( wp_json_encode( $query_args ) . wp_cache_get_last_changed( 'posts' ) );
	$cached_list = wp_cache_get( $cache_key );

	if ( false !== $cached_list ) {
		// return rest_ensure_response( $cached_list );
	}

	$query = new \WP_Query( $query_args );

	foreach ( $query->posts as $post ) {
		$response[ $post->post_title  ] = [
			'distance'  => absint( get_post_meta( $post->ID, 'route_details_distance', true ) ),
			'demand'    => [
				'y' => absint( get_post_meta( $post->ID, 'route_details_demand_y', true ) ),
				'j' => absint( get_post_meta( $post->ID, 'route_details_demand_j', true ) ),
				'f' => absint( get_post_meta( $post->ID, 'route_details_demand_f', true ) ),
			]
		];
	}

	wp_cache_set( $cache_key, $response, '', DAY_IN_SECONDS * 1 );

	return rest_ensure_response( $response );
}
