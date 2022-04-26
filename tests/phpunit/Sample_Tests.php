<?php // phpcs:ignore

namespace WPAirlineManager4\Tests;

/**
 * Sample tests.
 */
class Sample_Tests extends \WP_UnitTestCase {

	/**
	 * Basic test to verify unit tests are running.
	 *
	 * @return void
	 */
	public function test_not_logged_in() {
		$this->assertFalse( is_user_logged_in() );
	}
}
