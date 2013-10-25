<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/dropbox/dropbox.php';

/**
 * Test class for JDropbox.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Dropbox
 *
 * @since       ??.?
 */
class JDropboxTest extends PHPUnit_Framework_TestCase
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
	 * Tests the __construct method
	 *
	 * @return  void
	 *
	 * @since   ??.?
	 */
	public function test__Construct()
	{
		$optionsArray = array(
			$this->object->getOption('api.url'),
			$this->object->getOption('api.content'),
			$this->object->getOption('api.oauth1.request_token'),
			$this->object->getOption('api.oauth2.authorize'),
			$this->object->getOption('api.oauth2.access_token'),
		);
		$validOptions = array(
			'api.dropbox.com',
			'api-content.dropbox.com',
			'https://api.dropbox.com/1/oauth/request_token',
			'https://www.dropbox.com/1/oauth2/authorize',
			'https://api.dropbox.com/1/oauth2/token',
		);

		$this->assertThat(
			$optionsArray,
			$this->equalTo($validOptions)
		);
	}

	/**
	 * Tests the magic __get method - accounts
	 *
	 * @since  ??.?
	 *
	 * @return void
	 */
	public function test__GetAccounts()
	{
		$this->assertThat(
			$this->object->accounts,
			$this->isInstanceOf('JDropboxAccounts')
		);
	}

	/**
	 * Tests the magic __get method - files
	 *
	 * @since  ??.?
	 *
	 * @return void
	 */
	public function test__GetFiles()
	{
		$this->assertThat(
			$this->object->files,
			$this->isInstanceOf('JDropboxFiles')
		);
	}

	/**
	 * Tests the magic __get method - fileops
	 *
	 * @since  ??.?
	 *
	 * @return void
	 */
	public function test__GetFileops()
	{
		$this->assertThat(
			$this->object->fileops,
			$this->isInstanceOf('JDropboxFileops')
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
