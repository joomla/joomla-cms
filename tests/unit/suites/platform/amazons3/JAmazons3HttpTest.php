<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/github/http.php';
require_once JPATH_PLATFORM . '/joomla/http/transport/stream.php';

/**
 * Test class for JAmazons3.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Amazons3
 *
 * @since       ??.?
 */
class JAmazons3HttpTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JRegistry  Options for the GitHub object.
	 * @since  ??.?
	 */
	protected $options;

	/**
	 * @var    JAmazons3Http  Mock client object.
	 * @since  ??.?
	 */
	protected $transport;

	/**
	 * @var    JAmazons3Http  Object under test.
	 * @since  ??.?
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->options = new JRegistry;
		$this->transport = $this->getMock('JHttpTransportStream', array('request'), array($this->options), 'CustomTransport', false);

		$this->object = new JAmazons3Http($this->options, $this->transport);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   ??.?
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
			$this->object->getOption('timeout'),
			$this->equalTo(120)
		);
	}
}
