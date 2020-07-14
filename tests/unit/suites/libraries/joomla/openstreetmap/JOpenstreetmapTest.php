<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Openstreetmap
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JOpenstreetmap.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Openstreetmap
 * @since       3.2.0
 */
class JOpenstreetmapTest extends TestCase
{
	/**
	 * @var    JRegistry  Options for the Openstreetmap object.
	 * @since  3.2.0
	 */
	protected $options;

	/**
	 * @var    JHttp  Mock http object.
	 * @since  3.2.0
	 */
	protected $client;

	/**
	 * @var    JOpenstreetmap  Object under test.
	 * @since  3.2.0
	 */
	protected $object;

	/**
	 * @var JOpenstreetmapOAuth OAuth 1 client
	 * @since 3.2.0
	 */
	protected $oauth;

	/**
	 * Backup of the SERVER superglobal
	 *
	 * @var  array
	 * @since  3.6
	 */
	protected $backupServer;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$this->backupServer = $_SERVER;
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';
		$_SERVER['REQUEST_URI'] = '/index.php';
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		$this->options = new JRegistry;
		$this->client = $this->getMockBuilder('JHttp')->setMethods(array('get', 'post', 'delete', 'put'))->getMock();

		$this->object = new JOpenstreetmap($this->oauth, $this->options, $this->client);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 *
	 * @see     \PHPUnit\Framework\TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		$_SERVER = $this->backupServer;
		unset($this->backupServer, $this->options, $this->client, $this->object);
	}

	/**
	 * Tests the magic __get method - changesets
	 *
	 * @return  void
	 *
	 * @since   3.2.0
	 */
	public function test__GetChangesets()
	{
		$this->assertThat(
				$this->object->changesets,
				$this->isInstanceOf('JOpenstreetmapChangesets')
		);
	}

	/**
	 * Tests the magic __get method - elements
	 *
	 * @return  void
	 *
	 * @since   3.2.0
	 */
	public function test__GetElements()
	{
		$this->assertThat(
				$this->object->elements,
				$this->isInstanceOf('JOpenstreetmapElements')
		);
	}

	/**
	 * Tests the magic __get method - gps
	 *
	 * @return  void
	 *
	 * @since   3.2.0
	 */
	public function test__GetGps()
	{
		$this->assertThat(
				$this->object->gps,
				$this->isInstanceOf('JOpenstreetmapGps')
		);
	}

	/**
	 * Tests the magic __get method - info
	 *
	 * @return  void
	 *
	 * @since   3.2.0
	 */
	public function test__GetInfo()
	{
		$this->assertThat(
				$this->object->info,
				$this->isInstanceOf('JOpenstreetmapInfo')
		);
	}

	/**
	 * Tests the magic __get method - user
	 *
	 * @return  void
	 *
	 * @since   3.2.0
	 */
	public function test__GetUser()
	{
		$this->assertThat(
				$this->object->user,
				$this->isInstanceOf('JOpenstreetmapUser')
		);
	}

	/**
	 * Tests the magic __get method - other (non existent)
	 *
	 * @return  void
	 *
	 * @since   3.2.0
	 * @expectedException  InvalidArgumentException
	 */
	public function test__GetOther()
	{
		$this->object->other;
	}

	/**
	 * Tests the setOption method
	 *
	 * @return  void
	 *
	 * @since   3.2.0
	 */
	public function testSetOption()
	{
		$this->object->setOption('api.url', 'https://example.com/settest');

		$this->assertThat(
				$this->options->get('api.url'),
				$this->equalTo('https://example.com/settest')
		);
	}

	/**
	 * Tests the getOption method
	 *
	 * @return  void
	 *
	 * @since   3.2.0
	 */
	public function testGetOption()
	{
		$this->options->set('api.url', 'https://example.com/gettest');

		$this->assertThat(
				$this->object->getOption('api.url', 'https://example.com/gettest'),
				$this->equalTo('https://example.com/gettest')
		);
	}
}
