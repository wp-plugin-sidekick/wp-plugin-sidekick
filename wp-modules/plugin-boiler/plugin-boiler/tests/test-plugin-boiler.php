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
	function some_function_return_4() {
		$expected = 'plugin24';
		$result   = some_function2();

		$this->assertSame( $expected, $result );
	}
}
