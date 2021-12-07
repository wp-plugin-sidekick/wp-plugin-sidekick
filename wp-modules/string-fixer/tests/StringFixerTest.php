<?php
/**
 * Class SampleTest
 *
 * @package Plugin_Sample
 */

/**
 * Sample test case.
 */
class StringFixerTests extends WP_UnitTestCase {

	/**
	 * @test
	 */
	function stringfixer_return_fail() {
		$this->assertSame(false, true);
	}

	/**
	 * @test
	 */
	function stringfixer_return_pass() {
		$this->assertSame(true, true);
	}
}
