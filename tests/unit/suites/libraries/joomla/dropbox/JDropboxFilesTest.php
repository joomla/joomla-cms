<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/dropbox/files.php';

/**
 * Test class for JDropboxFiles.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Dropbox
 *
 * @since       ??.?
 */
class JDropboxFilesTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JRegistry  Options for the Dropbox object.
	 * @since  ??.?
	 */
	protected $options;

	/**
	 * @var    JDropbox  Object under test.
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
		$this->object = new JDropbox($this->options);
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
			$this->object->files->get,
			$this->isInstanceOf('JDropboxFilesGet')
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
			$this->object->files->post,
			$this->isInstanceOf('JDropboxFilesPost')
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
			$this->object->files->put,
			$this->isInstanceOf('JDropboxFilesPut')
		);
	}
}
