<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/googlecloudstorage/googlecloudstorage.php';

/**
 * Test class for JGooglecloudstorage.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Googlecloudstorage
 *
 * @since       ??.?
 */
class JGooglecloudstorageTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JRegistry  Options for the Googlecloudstorage object.
	 * @since  ??.?
	 */
	protected $options;

	/**
	 * @var    JGooglecloudstorage  Object under test.
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
		$this->object = new JGooglecloudstorage($this->options);
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
			$this->object->getOption('api.host'),
			$this->object->getOption('api.oauth.privateKeyPassword'),
			$this->object->getOption('api.oauth.assertionTarget'),
			$this->object->getOption('api.oauth.scope.read-only'),
			$this->object->getOption('api.oauth.scope.read-write'),
			$this->object->getOption('api.oauth.scope.full-control'),
		);
		$validOptions = array(
			'storage.googleapis.com',
			'accounts.google.com',
			'notasecret',
			'https://accounts.google.com/o/oauth2/token',
			'https://www.googleapis.com/auth/devstorage.read_only',
			'https://www.googleapis.com/auth/devstorage.read_write',
			'https://www.googleapis.com/auth/devstorage.full_control',
		);

		$this->assertThat(
			$optionsArray,
			$this->equalTo($validOptions)
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
			$this->isInstanceOf('JGooglecloudstorageService')
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
			$this->isInstanceOf('JGooglecloudstorageBuckets')
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
			$this->isInstanceOf('JGooglecloudstorageObjects')
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
