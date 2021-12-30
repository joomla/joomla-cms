<?php

/**
 * Polyfill phpunit assertTag
 */
trait AssertTag {

	/**
	 * Dummy function
	 */
	public function assertTag($matcher, $string, $message) {

		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
