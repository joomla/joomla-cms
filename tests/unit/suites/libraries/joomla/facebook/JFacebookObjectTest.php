<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Facebook
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/stubs/JFacebookObjectMock.php';

use Joomla\Registry\Registry;

/**
 * Test class for JFacebookObject.
 *
 * @package     Joomla.Platform
 * @subpackage  Facebook
 * @since       13.1
 */
class JFacebookObjectTest extends TestCase
{
	/**
	 * @var    Registry  Options for the Facebook object.
	 * @since  13.1
	 */
	protected $options;

	/**
	 * @var    JHttp  Mock client object.
	 * @since  13.1
	 */
	protected $client;

	/**
	 * @var    JFacebookObjectMock  Object under test.
	 * @since  13.1
	 */
	protected $object;

	/**
	 * @var    string  Sample JSON string.
	 * @since  13.1
	 */
	protected $sampleString = '{"a":1,"b":2,"c":3,"d":4,"e":5}';

	/**
	 * @var    string  Sample JSON error message.
	 * @since  13.1
	 */
	protected $errorString = '{"error": {"message": "Generic Error."}}';

	/**
	 * Backup of the SERVER superglobal
	 *
	 * @var  array
	 */
	protected $backupServer;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return   void
	 *
	 * @since    13.1
	 */
	protected function setUp()
	{
		$this->backupServer = $_SERVER;
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';
		$_SERVER['REQUEST_URI'] = '/index.php';
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		$this->options = new Registry;
		$this->client = $this->getMock('JHttp', array('get', 'post', 'delete', 'put'));

		$this->object = new JFacebookObjectMock($this->options, $this->client);

		parent::setUp();
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		$_SERVER = $this->backupServer;
		unset($this->backupServer);
		unset($this->options);
		unset($this->client);
		unset($this->object);
		parent::tearDown();
	}


	/**
	* Provides test data for request format detection.
	*
	* @return array
	*
	* @since 13.1
	*/
	public function seedFetchUrl()
	{
		// Limit, offset, until, since and expected
		return array(
			array(0, 0, null, null, 'https://graph.facebook.com/456431243/likes?access_token=235twegsdgsdhtry3tgwgf'),
			array(5, 0, null, null, 'https://graph.facebook.com/456431243/likes?access_token=235twegsdgsdhtry3tgwgf&limit=5'),
			array(5, 1, null ,null, 'https://graph.facebook.com/456431243/likes?access_token=235twegsdgsdhtry3tgwgf&limit=5&offset=1'),
			array(5, 1, 1893909600, null, 'https://graph.facebook.com/456431243/likes?access_token=235twegsdgsdhtry3tgwgf&limit=5&offset=1&until=1893909600'),
			array(5, 1, null, 1325829600, 'https://graph.facebook.com/456431243/likes?access_token=235twegsdgsdhtry3tgwgf&limit=5&offset=1&since=1325829600'),
			array(0, 0, 1893909600, 1325829600,
				'https://graph.facebook.com/456431243/likes?access_token=235twegsdgsdhtry3tgwgf&until=1893909600&since=1325829600')
		);
	}

	/**
	 * Test the fetchUrl method.
	 *
	 * @param   integer    $limit     The number of objects per page.
	 * @param   integer    $offset    The object's number on the page.
	 * @param   timestamp  $until     A unix timestamp or any date accepted by strtotime.
	 * @param   timestamp  $since     A unix timestamp or any date accepted by strtotime.
	 * @param   string     $expected  The expected result.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedFetchUrl
	 *
	 * @since    13.1
	 */
	public function testFetchUrl($limit, $offset, $until, $since, $expected)
	{
		$apiUrl = 'https://graph.facebook.com/';
		$path = '456431243/likes?access_token=235twegsdgsdhtry3tgwgf';

		$this->options->set('api.url', $apiUrl);

		$this->assertThat(
			$this->object->fetchUrl($path, $limit, $offset, $until, $since),
			$this->equalTo($expected)
		);
	}

	/**
	 * Tests the sendRequest method.
	 *
	 * @return  void
	 *
	 * @since    13.1
	 */
	public function testSendRequest()
	{
		// Method tested via requesting classes
		$this->markTestSkipped('This method is tested via requesting classes.');
	}

	/**
	 * Tests the get method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGet()
	{
		// Method tested via requesting classes
		$this->markTestSkipped('This method is tested via requesting classes.');
	}

	/**
	 * Tests the get method - failure
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetFailure()
	{
		// Method tested via requesting classes
		$this->markTestSkipped('This method is tested via requesting classes.');
	}

	/**
	 * Tests the getConnection method
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetConnection()
	{
		// Method tested via requesting classes
		$this->markTestSkipped('This method is tested via requesting classes.');
	}

	/**
	 * Tests the getConnection method - failure
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testGetConnectionFailure()
	{
		// Method tested via requesting classes
		$this->markTestSkipped('This method is tested via requesting classes.');
	}

	/**
	 * Tests the createConnection method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testCreateConnection()
	{
		// Method tested via requesting classes
		$this->markTestSkipped('This method is tested via requesting classes.');
	}

	/**
	 * Tests the createConnection method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testCreateConnectionFailure()
	{
		// Method tested via requesting classes
		$this->markTestSkipped('This method is tested via requesting classes.');
	}

	/**
	 * Tests the deleteConnection method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testDeleteConnection()
	{
		// Method tested via requesting classes
		$this->markTestSkipped('This method is tested via requesting classes.');
	}

	/**
	 * Tests the deleteConnection method - failure.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testDeleteConnectionFailure()
	{
		// Method tested via requesting classes
		$this->markTestSkipped('This method is tested via requesting classes.');
	}
}
