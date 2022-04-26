<?php
/**
 * WP-CLI commands.
 *
 * @package WP Airline Manager 4
 */

namespace WPAirlineManager4\CLI;

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
