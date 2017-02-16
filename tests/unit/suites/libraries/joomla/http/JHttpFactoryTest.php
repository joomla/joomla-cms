<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Http
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JHttpFactory.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Http
 * @since       3.4
 */
class JHttpFactoryTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Tests the getHttp method.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testGetHttp()
	{
		$this->assertInstanceOf(
			'JHttp',
			JHttpFactory::getHttp()
		);
	}

	/**
	 * Tests the getHttp method for an exception.
	 *
	 * @return  void
	 *
	 * @since              3.4
	 * @expectedException  RuntimeException
	 */
	public function testGetHttpException()
	{
		JHttpFactory::getHttp(new \Joomla\Registry\Registry, array('fopen'));
	}

	/**
	 * Tests the getAvailableDriver method.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testGetAvailableDriver()
	{
		$this->assertFalse(
			JHttpFactory::getAvailableDriver(new \Joomla\Registry\Registry, array()),
			'Passing an empty array should return false due to there being no adapters to test'
		);

		$this->assertFalse(
			JHttpFactory::getAvailableDriver(new \Joomla\Registry\Registry, array('fopen')),
			'A false should be returned if a class is not present or supported'
		);
	}

	/**
	 * Tests the getHttpTransports method.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testGetHttpTransports()
	{
		$transports = JHttpFactory::getHttpTransports();

		$this->assertEquals(
			'curl',
			$transports[0],
			'CURL should be the first transport returned.'
		);
	}
}
