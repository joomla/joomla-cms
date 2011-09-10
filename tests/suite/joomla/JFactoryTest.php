<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Utilities
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
require_once JPATH_PLATFORM . '/joomla/factory.php';

/**
 * Tests for JDate class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Utilities
 * @since       11.3
 */
class JFactoryTest extends JoomlaTestCase
{
	/**
	 * Sets up the fixture.
	 *
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();
	}

	/**
	 * Tears down the fixture.
	 *
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	function tearDown()
	{
		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	* Tests the JFactory::getConfig method.
	*
	* @return  void
	*
	* @since   11.3
	*/
	function testGetConfig()
	{
		// Temporarily override the config cache in JFactory.
		$temp = JFactory::$config;
		JFactory::$config = null;

		$this->assertThat(
			JFactory::getConfig(JPATH_TESTS.'/config.php'),
			$this->isInstanceOf('JRegistry')
		);

		JFactory::$config = $temp;
	}

	/**
	 * Tests the JFactory::getDate method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGetDate()
	{
		JFactory::$language = $this->getMockLanguage();

		$date = JFactory::getDate('2001-01-01 01:01:01');

		$this->assertThat(
			(string) $date,
			$this->equalTo('2001-01-01 01:01:01'),
			'Tests that a date passed in comes back unchanged.'
		);

		$date = JFactory::getDate('now');
		sleep(2);
		$date2 = JFactory::getDate('now');

		$this->assertThat(
			$date2,
			$this->equalTo($date),
			'Tests that the cache for the same time is working.'
		);

		$tz = 'Etc/GMT+0';
		$date = JFactory::getDate('2001-01-01 01:01:01', $tz);

		$this->assertThat(
			(string) $date,
			$this->equalTo('2001-01-01 01:01:01'),
			'Tests that a date passed in with UTC timezone string comes back unchanged.'
		);

		$tz = new DateTimeZone('Etc/GMT+0');
		$date = JFactory::getDate('2001-01-01 01:01:01', $tz);

		$this->assertThat(
			(string) $date,
			$this->equalTo('2001-01-01 01:01:01'),
			'Tests that a date passed in with UTC timezone comes back unchanged.'
		);
	}
}
