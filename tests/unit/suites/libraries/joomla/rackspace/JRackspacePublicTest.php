<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JRackspacePublic.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Rackspace
 *
 * @since       ??.?
 */
class JRackspacePublicTest extends PHPUnit_Framework_TestCase
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
	 * Tests the magic __get method - tempurl
	 *
	 * @since  ??.?
	 *
	 * @return void
	 */
	public function test__GetTempurl()
	{
		$this->assertThat(
			$this->object->public->tempurl,
			$this->isInstanceOf('JRackspacePublicTempurl')
		);
	}

	/**
	 * Tests the magic __get method - formpost
	 *
	 * @since  ??.?
	 *
	 * @return void
	 */
	public function test__GetFormpost()
	{
		$this->assertThat(
			$this->object->public->formpost,
			$this->isInstanceOf('JRackspacePublicFormpost')
		);
	}
}
