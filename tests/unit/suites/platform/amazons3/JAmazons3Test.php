<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/amazons3/amazons3.php';

/**
 * Test class for JAmazons3.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Amazons3
 *
 * @since       ??.?
 */
class JAmazons3Test extends PHPUnit_Framework_TestCase
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
	 * Tests the __construct method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function test__Construct()
	{
		$this->assertThat(
			$this->object->getOption('api.url'),
			$this->equalTo("s3.amazonaws.com")
		);
	}

	/**
	 * Tests the magic __get method - service
	 *
	 * @since  ??.?
	 *
	 * @return void
	 */
	public function test__GetService()
	{
		$this->assertThat(
			$this->object->service,
			$this->isInstanceOf('JAmazons3OperationsService')
		);
	}

	/**
	 * Tests the magic __get method - buckets
	 *
	 * @since  ??.?
	 *
	 * @return void
	 */
	public function test__GetBuckets()
	{
		$this->assertThat(
			$this->object->buckets,
			$this->isInstanceOf('JAmazons3OperationsBuckets')
		);
	}

	/**
	 * Tests the magic __get method - objects
	 *
	 * @since  ??.?
	 *
	 * @return void
	 */
	public function test__GetObjects()
	{
		$this->assertThat(
			$this->object->objects,
			$this->isInstanceOf('JAmazons3OperationsObjects')
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
