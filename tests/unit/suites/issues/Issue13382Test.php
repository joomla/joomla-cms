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
 * Loading a custom field can result in a Fatal Error, if a core field exists with the same filename, and the custom
 * field extends the core field.
 *
 * Consider a file `testfields/radio.php` containing
 *
 * ```php
 * <?php
 *
 * class TestFormFieldRadio extends JFormFieldRadio {}
 * ```
 *
 * Calling `JFormHelper::loadFieldClass('radio')` will yield in a Fatal error: Class 'JFormFieldRadio' not found.
 *
 * There are two reasons for this.
 *
 * 1. JLoader cannot autoload JFormFieldRadio, because the class name (JFormField*) does not match the path name
 *    (joomla/form/fields/* - notice the plural on fields).
 *
 * 2. JFormHelper cannot load JFormFieldRadio, because custom paths are scanned first, and the requested field type
 *    ('radio') gets resolved before the core classes are reached.
 *
 * Solution:
 *
 * Require the core field directly:
 *
 * ```php
 * <?php
 * require_once JPATH_LIBRARIES . '/joomla/form/fields/radio.php';
 *
 * class TestFormFieldRadio extends JFormFieldRadio {}
 * ```
 *
 * and use `JFormHelper::loadFieldClass` properly with 'test.radio' instead of 'radio'.
 *
 * @since  __DEPLOY_VERSION__
 */
class Issue13382Test extends PHPUnit_Framework_TestCase
{
	/**
	 * Test expected behaviour
	 *
	 * `JFormHelper::loadFieldClass('radio')` should return 'JFormFieldRadio'
	 * `JFormHelper::loadFieldClass('test.radio')` should return 'TestFormFieldRadio'
	 *
	 * @see      https://issues.joomla.org/tracker/joomla-cms/13382#event-231756
	 *
	 * @testdox  Custom field types can extend core types with the same filename
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
