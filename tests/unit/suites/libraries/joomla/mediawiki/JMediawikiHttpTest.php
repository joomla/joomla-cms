<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JMediawikiHttp.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Mediawiki
 *
 * @since       12.3
 */
class JMediawikiHttpTest extends PHPUnit_Framework_TestCase
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

	/**
	 * Tests the constructor to ensure a JHttpTransportStream object is set as the transport when one is not provided
	 *
	 * @return  void
	 */
	public function testConstructorSetsStreamTransport()
	{
		$http = new JMediawikiHttp(array());

		$this->assertAttributeInstanceOf('JHttpTransportStream', 'transport', $http);
	}
}
