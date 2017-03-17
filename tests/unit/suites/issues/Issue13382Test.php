<?php
/**
 * @package     Joomla.UnitTest
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 *
 * @since       __DEPLOY_VERSION__
 */

/**
 * Tests for issue 13382
 *
 * https://issues.joomla.org/tracker/joomla-cms/13382
 *
 * @since  __DEPLOY_VERSION__
 */
class Issue13382Test extends PHPUnit_Framework_TestCase
{
	/**
	 * Test expected behaviour according to https://issues.joomla.org/tracker/joomla-cms/13382#event-231756
	 *
	 * @testdox  Custom field types override core field types
	 *
	 * @since    __DEPLOY_VERSION__
	 */
	public function testIssue13382()
	{
		JFormHelper::addFieldPath(__DIR__ . '/fixtures/issue13382');

		$this->assertEquals('JFormFieldRadio', JFormHelper::loadFieldClass('radio'));
		$this->assertEquals('TestFormFieldRadio', JFormHelper::loadFieldClass('test.radio'));
	}
}
