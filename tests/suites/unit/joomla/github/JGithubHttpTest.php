<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/github/http.php';
require_once JPATH_PLATFORM.'/joomla/http/transport/stream.php';

/**
 * Test class for JGithub.
 */
class JGithubHttpTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JRegistry  Options for the GitHub object.
	 * @since  11.4
	 */
	protected $options;

	/**
	 * @var    JGithubHttp  Mock client object.
	 * @since  11.4
	 */
	protected $transport;

	/**
	 * @var    JGithubIssues  Object under test.
	 * @since  11.4
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 */
	protected function setUp()
	{
		$this->options = new JRegistry;
		$this->transport = $this->getMock('JHttpTransportStream', array('request'), array($this->options), 'CustomTransport', false);

		$this->object = new JGithubHttp($this->options, $this->transport);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @access protected
	 */
	protected function tearDown()
	{
	}

	/**
	 * Tests the patch method
	 */
	public function testPatch()
	{
		$uri = new JUri('https://example.com/gettest');

		$this->transport->expects($this->once())
			->method('request')
			->with('PATCH', $uri, array())
			->will($this->returnValue('requestResponse'));

		$this->assertThat(
			$this->object->patch('https://example.com/gettest', array()),
			$this->equalTo('requestResponse')
		);
	}
}
