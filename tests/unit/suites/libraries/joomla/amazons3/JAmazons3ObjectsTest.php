<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JAmazons3.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Amazons3
 *
 * @since       ??.?
 */
class JAmazons3ObjectsTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JRegistry  Options for the Amazons3 object.
	 * @since  ??.?
	 */
	protected $options;

	/**
	 * @var    JAmazons3  Object under test.
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
		$this->object = new JAmazons3($this->options);
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
	 * Tests the magic __get method - get
	 *
	 * @since  ??.?
	 *
	 * @return void
	 */
	public function test__GetGet()
	{
		$this->assertThat(
			$this->object->objects->get,
			$this->isInstanceOf('JAmazons3ObjectsGet')
		);
	}

	/**
	 * Tests the magic __get method - head
	 *
	 * @since  ??.?
	 *
	 * @return void
	 */
	public function test__GetHead()
	{
		$this->assertThat(
			$this->object->objects->head,
			$this->isInstanceOf('JAmazons3ObjectsHead')
		);
	}

	/**
	 * Tests the magic __get method - delete
	 *
	 * @since  ??.?
	 *
	 * @return void
	 */
	public function test__GetDelete()
	{
		$this->assertThat(
			$this->object->objects->delete,
			$this->isInstanceOf('JAmazons3ObjectsDelete')
		);
	}

	/**
	 * Tests the magic __get method - put
	 *
	 * @since  ??.?
	 *
	 * @return void
	 */
	public function test__GetPut()
	{
		$this->assertThat(
			$this->object->objects->put,
			$this->isInstanceOf('JAmazons3ObjectsPut')
		);
	}

	/**
	 * Tests the magic __get method - post
	 *
	 * @since  ??.?
	 *
	 * @return void
	 */
	public function test__GetPost()
	{
		$this->assertThat(
			$this->object->objects->post,
			$this->isInstanceOf('JAmazons3ObjectsPost')
		);
	}

	/**
	 * Tests the magic __get method - options
	 *
	 * @since  ??.?
	 *
	 * @return void
	 */
	public function test__GetOptions()
	{
		$this->assertThat(
			$this->object->objects->optionss3,
			$this->isInstanceOf('JAmazons3ObjectsOptionss3')
		);
	}
}
