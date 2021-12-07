<?php
/**
 * Class SampleTest
 *
 * @package Plugin_Sample
 */

/**
 * Sample test case.
 */
class SampleTests extends WP_UnitTestCase {

	/**
	 * @test
	 */
	function some_function_return_fail() {
		$this->assertSame(false, true);
	}

	/**
	 * @test
	 */
	function some_function_return_pass() {
		$this->assertSame(true, true);
	}
}
