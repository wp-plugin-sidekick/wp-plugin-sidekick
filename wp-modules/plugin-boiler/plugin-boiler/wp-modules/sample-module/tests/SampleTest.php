<?php
/**
 * Class SampleTest
 *
 * @package plugin-boiler
 */

/**
 * Test this module's functions.
 */
class SampleTest extends WP_UnitTestCase {

	public function test_sample_admin_notice() {
		$expected = '<div class="notice notice-success"><p>I come from the sample module! I recommend removing this module and creating your own! Modules make it easy to add, share, and delete code as your project grows.</p></div>';

		ob_start();
		\SampleModule\sample_admin_notice( __FILE__ );
		$result = ob_get_clean();

		$this->assertSame( $expected, $result );
	}
}
