<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Http
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JHttp.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Http
 * @since       3.4
 */
class JHttpTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Tests the constructor to ensure only arrays or ArrayAccess objects are allowed
	 *
	 * @return  void
	 *
	 * @expectedException  \InvalidArgumentException
	 */
	public function testConstructorDisallowsNonArrayObjects()
	{
		new JHttp(new stdClass);
	}
}
