<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JApplication.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Application
 * @since       12.2
 */
class JApplicationTest extends TestCase
{
	/**
	 * Object under test
	 *
	 * @var  JApplication
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		$this->object = new JApplication(array('session' => false));
		parent::setUp();
	}

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function tearDown()
	{
		$this->object = null;
		parent::tearDown();
	}

	/**
	 * Test JApplication::__construct
	 *
	 * @return  void
	 */
	public function testConstruct()
	{
		$this->assertThat(
			$this->object->input,
			$this->isInstanceOf('JInput'),
			__LINE__ . 'JApplication->input not initialized properly'
		);

		$this->assertInstanceOf(
			'JApplicationWebClient',
			$this->object->client,
			'Client property wrong type'
		);
	}

	/**
	 * Testing JApplication::getHash
	 *
	 * @return  void
	 */
	public function testJApplicationGetHash()
	{
		// Temporarily override the config cache in JFactory.
		$temp = JFactory::$config;
		JFactory::$config = new JObject(array('secret' => 'foo'));

		$this->assertThat(
			JApplication::getHash('This is a test'),
			$this->equalTo(md5('foo' . 'This is a test')),
			'Tests that the secret string is added to the hash.'
		);

		JFactory::$config = $temp;
	}

	/**
	 * Testing JApplicationHelper::getHash
	 *
	 * @return  void
	 */
	public function testJApplicationHelperGetHash()
	{
		// Temporarily override the config cache in JFactory.
		$temp = JFactory::$config;
		JFactory::$config = new JObject(array('secret' => 'foo'));

		$this->assertThat(
			JApplicationHelper::getHash('This is a test'),
			$this->equalTo(md5('foo' . 'This is a test')),
			'Tests that the secret string is added to the hash.'
		);

		JFactory::$config = $temp;
	}

	/**
	 * Test JApplication::isSSLConnection
	 *
	 * @return  void
	 */
	public function testIsSSLConnection()
	{
		unset($_SERVER['HTTPS']);

		$this->assertThat(
			$this->object->isSSLConnection(),
			$this->equalTo(false)
		);

		$_SERVER['HTTPS'] = 'on';

		$this->assertThat(
			$this->object->isSSLConnection(),
			$this->equalTo(true)
		);
	}
}
