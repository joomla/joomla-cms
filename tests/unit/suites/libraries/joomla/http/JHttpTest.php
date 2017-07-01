<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Http
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JHttp.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Http
 * @since       3.4
 */
class JHttpTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * Tests the constructor disallows any non array object
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
