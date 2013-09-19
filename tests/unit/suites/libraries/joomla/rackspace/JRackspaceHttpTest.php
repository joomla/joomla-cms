<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/rackspace/http.php';

/**
 * Test class for JRackspaceHttp.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Rackspace
 *
 * @since       ??.?
 */
class JRackspaceHttpTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JRackspaceHttp  Object under test.
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

		$this->client = $this->getMock('JRackspaceHttp', array('delete', 'deleteWithBody', 'get', 'head', 'put', 'post'));
		$this->object = new JRackspaceHttp;
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
