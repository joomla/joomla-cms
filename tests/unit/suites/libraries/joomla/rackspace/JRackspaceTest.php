<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/rackspace/rackspace.php';

/**
 * Test class for JRackspace.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Rackspace
 *
 * @since       ??.?
 */
class JRackspaceTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JRegistry  Options for the Rackspace object.
	 * @since  ??.?
	 */
	protected $options;

	/**
	 * @var    JRackspace  Object under test.
	 * @since  ??.?
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->options = new JRegistry;
		$this->object = new JRackspace($this->options);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @access protected
	 *
	 * @return void
	 */
	protected function tearDown()
	{
	}

	/**
	 * Tests the __construct method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function test__Construct()
	{
		$this->assertThat(
			$this->object->getOption('auth.host.us'),
			$this->equalTo("identity.api.rackspacecloud.com")
		);
	}

	/**
	 * Tests the magic __get method - CDN
	 *
	 * @since  ??.?
	 *
	 * @return void
	 */
	public function test__GetCdn()
	{
		$this->assertThat(
			$this->object->cdn,
			$this->isInstanceOf('JRackspaceCdn')
		);
	}

	/**
	 * Tests the magic __get method - public
	 *
	 * @since  ??.?
	 *
	 * @return void
	 */
	public function test__GetPublic()
	{
		$this->assertThat(
			$this->object->public,
			$this->isInstanceOf('JRackspacePublic')
		);
	}

	/**
	 * Tests the magic __get method - storage
	 *
	 * @since  ??.?
	 *
	 * @return void
	 */
	public function test__GetStorage()
	{
		$this->assertThat(
			$this->object->storage,
			$this->isInstanceOf('JRackspaceStorage')
		);
	}

	/**
	 * Tests the setOption method
	 *
	 * @since  ??.?
	 *
	 * @return void
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
	 * @since  ??.?
	 *
	 * @return void
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
