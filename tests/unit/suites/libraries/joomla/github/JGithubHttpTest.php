<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/github/http.php';
require_once JPATH_PLATFORM . '/joomla/http/transport/stream.php';

/**
 * Test class for JGithub.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Github
 *
 * @since       11.1
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
	 * @var    JGithubHttp  Object under test.
	 * @since  11.4
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->options = new JRegistry;
		$this->transport = $this->getMock('JHttpTransportStream', array('request'), array($this->options), 'CustomTransport', false);

		$this->object = new JGithubHttp($this->options, $this->transport);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   11.4
	 */
	protected function tearDown()
	{
	}

	/**
	 * Tests the __construct method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function test__Construct()
	{
		// Verify the options are set in the object
		$this->assertThat(
			$this->object->getOption('userAgent'),
			$this->equalTo('JGitHub/2.0')
		);

		$this->assertThat(
			$this->object->getOption('timeout'),
			$this->equalTo(120)
		);
	}
}
