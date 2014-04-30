<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JRackspaceCdn.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Rackspace
 *
 * @since       ??.?
 */
class JRackspaceCdnTest extends PHPUnit_Framework_TestCase
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
	 * Tests the magic __get method - account
	 *
	 * @since  ??.?
	 *
	 * @return void
	 */
	public function test__GetAccount()
	{
		$this->assertThat(
			$this->object->cdn->account,
			$this->isInstanceOf('JRackspaceCdnAccount')
		);
	}

	/**
	 * Tests the magic __get method - container
	 *
	 * @since  ??.?
	 *
	 * @return void
	 */
	public function test__GetContainer()
	{
		$this->assertThat(
			$this->object->cdn->container,
			$this->isInstanceOf('JRackspaceCdnContainer')
		);
	}

	/**
	 * Tests the magic __get method - object
	 *
	 * @since  ??.?
	 *
	 * @return void
	 */
	public function test__GetObject()
	{
		$this->assertThat(
			$this->object->cdn->object,
			$this->isInstanceOf('JRackspaceCdnObject')
		);
	}
}
