<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Twitter
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JTwitter.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Twitter
 * @since       12.3
 */
class JTwitterTest extends TestCase
{
	/**
	 * @var    JRegistry  Options for the Twitter object.
	 * @since  12.3
	 */
	protected $options;

	/**
	 * @var    JHttp  Mock http object.
	 * @since  12.3
	 */
	protected $client;

	/**
	 * @var    JTwitter  Object under test.
	 * @since  12.3
	 */
	protected $object;

	/**
	 * @var JTwitterOAuth Facebook OAuth 2 client
	 * @since 12.3
	 */
	protected $oauth;

	/**
	 * Backup of the SERVER superglobal
	 *
	 * @var  array
	 * @since  3.6
	 */
	protected $backupServer;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$this->backupServer = $_SERVER;
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';
		$_SERVER['REQUEST_URI'] = '/index.php';
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		$this->options = new JRegistry;
		$this->client = $this->getMock('JHttp', array('get', 'post', 'delete', 'put'));

		$this->object = new JTwitter($this->oauth, $this->options, $this->client);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
		$_SERVER = $this->backupServer;
		unset($this->backupServer);
		unset($this->options);
		unset($this->client);
		unset($this->object);
	}

	/**
	 * Tests the magic __get method - friends
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function test__GetFriends()
	{
		$this->assertThat(
			$this->object->friends,
			$this->isInstanceOf('JTwitterFriends')
		);
	}

	/**
	 * Tests the magic __get method - help
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function test__GetHelp()
	{
		$this->assertThat(
			$this->object->help,
			$this->isInstanceOf('JTwitterHelp')
		);
	}

	/**
	 * Tests the magic __get method - other (non existant)
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @expectedException  InvalidArgumentException
	 */
	public function test__GetOther()
	{
		$this->object->other;
	}

	/**
	 * Tests the magic __get method - statuses
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function test__GetStatuses()
	{
		$this->assertThat(
			$this->object->statuses,
			$this->isInstanceOf('JTwitterStatuses')
		);
	}

	/**
	 * Tests the magic __get method - users
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function test__GetUsers()
	{
		$this->assertThat(
			$this->object->users,
			$this->isInstanceOf('JTwitterUsers')
		);
	}

	/**
	 * Tests the magic __get method - search
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function test__GetSearch()
	{
		$this->assertThat(
			$this->object->search,
			$this->isInstanceOf('JTwitterSearch')
		);
	}

	/**
	 * Tests the magic __get method - favorites
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function test__GetFavorites()
	{
		$this->assertThat(
			$this->object->favorites,
			$this->isInstanceOf('JTwitterFavorites')
		);
	}

	/**
	 * Tests the magic __get method - directMessages
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function test__GetDirectMessages()
	{
		$this->assertThat(
			$this->object->directmessages,
			$this->isInstanceOf('JTwitterDirectmessages')
		);
	}

	/**
	 * Tests the magic __get method - lists
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function test__GetLists()
	{
		$this->assertThat(
			$this->object->lists,
			$this->isInstanceOf('JTwitterLists')
		);
	}

	/**
	 * Tests the magic __get method - places
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function test__GetPlaces()
	{
		$this->assertThat(
			$this->object->places,
			$this->isInstanceOf('JTwitterPlaces')
		);
	}

	/**
	 * Tests the magic __get method - trends
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function test__GetTrends()
	{
		$this->assertThat(
			$this->object->trends,
			$this->isInstanceOf('JTwitterTrends')
		);
	}

	/**
	 * Tests the magic __get method - block
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function test__GetBlock()
	{
		$this->assertThat(
			$this->object->block,
			$this->isInstanceOf('JTwitterBlock')
		);
	}

	/**
	 * Tests the magic __get method - profile
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function test__GetProfile()
	{
		$this->assertThat(
			$this->object->profile,
			$this->isInstanceOf('JTwitterProfile')
		);
	}

	/**
	 * Tests the setOption method
	 *
	 * @return  void
	 *
	 * @since   12.3
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
	 * @return  void
	 *
	 * @since   12.3
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
