<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Linkedin
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JLinkedin.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Linkedin
 * @since       13.1
 */
class JLinkedinTest extends TestCase
{
	/**
	 * @var    JRegistry  Options for the Linkedin object.
	 * @since  13.1
	 */
	protected $options;

	/**
	 * @var    JHttp  Mock http object.
	 * @since  13.1
	 */
	protected $client;

	/**
	 * @var    JLinkedin  Object under test.
	 * @since  13.1
	 */
	protected $object;

	/**
	 * @var JTLinkedinrOAuth Facebook OAuth 2 client
	 * @since 13.1
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
		$this->client = $this->getMockBuilder('JHttp')->setMethods(array('get', 'post', 'delete', 'put'))->getMock();

		$this->object = new JLinkedin($this->oauth, $this->options, $this->client);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 *
	 * @see     \PHPUnit\Framework\TestCase::tearDown()
	 * @since   3.6
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
	 * Tests the magic __get method - people
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function test__GetPeople()
	{
		$this->assertThat(
			$this->object->people,
			$this->isInstanceOf('JLinkedinPeople')
		);
	}

	/**
	 * Tests the magic __get method - groups
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function test__GetGroups()
	{
		$this->assertThat(
			$this->object->groups,
			$this->isInstanceOf('JLinkedinGroups')
		);
	}

	/**
	 * Tests the magic __get method - companies
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function test__GetCompanies()
	{
		$this->assertThat(
			$this->object->companies,
			$this->isInstanceOf('JLinkedinCompanies')
		);
	}

	/**
	 * Tests the magic __get method - jobs
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function test__GetJobs()
	{
		$this->assertThat(
			$this->object->jobs,
			$this->isInstanceOf('JLinkedinJobs')
		);
	}

	/**
	 * Tests the magic __get method - stream
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function test__GetStream()
	{
		$this->assertThat(
			$this->object->stream,
			$this->isInstanceOf('JLinkedinStream')
		);
	}

	/**
	 * Tests the magic __get method - communications
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function test__GetCommunications()
	{
		$this->assertThat(
			$this->object->communications,
			$this->isInstanceOf('JLinkedinCommunications')
		);
	}

	/**
	 * Tests the magic __get method - other (non existant)
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @expectedException  InvalidArgumentException
	 */
	public function test__GetOther()
	{
		$this->object->other;
	}

	/**
	 * Tests the setOption method
	 *
	 * @return  void
	 *
	 * @since   13.1
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
	 * @since   13.1
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
