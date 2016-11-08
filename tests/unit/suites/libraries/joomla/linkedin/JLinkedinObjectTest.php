<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Linkedin
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/stubs/JLinkedinObjectMock.php';

/**
 * Test class for JLinkedinObject.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Linkedin
 *
 * @since       13.1
 */
class JLinkedinObjectTest extends TestCase
{
	/**
	 * @var    JRegistry  Options for the Linkedin object.
	 * @since  13.1
	 */
	protected $options;

	/**
	 * @var    JHttp  Mock client object.
	 * @since  13.1
	 */
	protected $client;

	/**
	 * @var    JLinkedinObjectMock  Object under test.
	 * @since  13.1
	 */
	protected $object;

	/**
	 * @var    string  Sample JSON string.
	 * @since  13.1
	 */
	protected $sampleString = '{"a":1,"b":2,"c":3,"d":4,"e":5}';

	/**
	 * @var    string  Sample JSON error message.
	 * @since  13.1
	 */
	protected $errorString = '{"errors":[{"message":"Sorry, that page does not exist","code":34}]}';

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
		$this->options = new JRegistry;
		$this->client = $this->getMock('JHttp', array('get', 'post', 'delete', 'put'));

		$this->object = new JLinkedinObjectMock($this->options, $this->client);
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
		unset($this->client);
		unset($this->options);
		unset($this->object);
	}

	/**
	 * Tests the setOption method
	 *
	 * @return void
	 *
	 * @since 13.1
	 */
	public function testSetOption()
	{
		$this->object->setOption('api.url', 'https://example.com/settest');

		$this->assertThat(
			$this->options->get('api.url'),
			$this->equalTo('https://example.com/settest')
		);
	}
}
