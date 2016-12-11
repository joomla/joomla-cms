<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Twitter
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JLinkedinOauth.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Twitter
 * @since       12.3
 */
class JTwitterOauthTest extends TestCase
{
	/**
	 * @var    JRegistry  Options for the Twitter object.
	 * @since  12.3
	 */
	protected $options;

	/**
	 * @var    JTeitterHttp  Mock http object.
	 * @since  12.3
	 */
	protected $client;

	/**
	 * @var    JInput The input object to use in retrieving GET/POST data.
	 * @since  12.3
	 */
	protected $input;

	/**
	 * @var    JApplicationWeb  The application object to send HTTP headers for redirects.
	 * @since  12.3
	 */
	protected $application;

	/**
	 * @var    JTwitterOauth  Authentication object for the Twitter object.
	 * @since  12.3
	 */
	protected $oauth;

	/**
	 * @var    string  Sample JSON string.
	 * @since  12.3
	 */
	protected $sampleString = '{"a":1,"b":2,"c":3,"d":4,"e":5}';

	/**
	 * @var    string  Sample JSON error message.
	 * @since  12.3
	 */
	protected $errorString = '{"errorCode":401, "message": "Generic error"}';

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

		$key = "app_key";
		$secret = "app_secret";
		$my_url = "http://127.0.0.1/twitter_test.php";

		$this->options = new JRegistry;
		$this->input = $this->getMockInput();
		$this->application = $this->getMockWeb();
		$this->client = $this->getMockBuilder('JHttp')->setMethods(array('get', 'post', 'delete', 'put'))->getMock();

		$this->options->set('consumer_key', $key);
		$this->options->set('consumer_secret', $secret);
		$this->options->set('callback', $my_url);
		$this->oauth = new JTwitterOauth($this->options, $this->client, $this->input, $this->application);
		$this->oauth->setToken(array('key' => $key, 'secret' => $secret));
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 */
	protected function tearDown()
	{
		$_SERVER = $this->backupServer;
		unset($this->backupServer);
		unset($this->options);
		unset($this->input);
		unset($this->application);
		unset($this->client);
		unset($this->oauth);
	}

	/**
	* Provides test data for request format detection.
	*
	* @return array
	*
	* @since 12.3
	*/
	public function seedVerifyCredentials()
	{
		// Code, body, expected
		return array(
			array(200, $this->sampleString, true),
			array(401, $this->errorString, false)
			);
	}

	/**
	 * Tests the verifyCredentials method
	 *
	 * @param   integer  $code      The return code.
	 * @param   string   $body      The JSON string.
	 * @param   boolean  $expected  Expected return value.
	 *
	 * @return  void
	 *
	 * @dataProvider seedVerifyCredentials
	 * @since   12.3
	 */
	public function testVerifyCredentials($code, $body, $expected)
	{
		$path = 'https://api.twitter.com/1.1/account/verify_credentials.json';

		$returnData = new stdClass;
		$returnData->code = $code;
		$returnData->body = $body;

		$this->client->expects($this->once())
			->method('get')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->oauth->verifyCredentials(),
			$this->equalTo($expected)
		);
	}

	/**
	 * Tests the endSession method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testEndSession()
	{
		$path = 'https://api.twitter.com/1.1/account/end_session.json';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('post')
			->with($path)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->oauth->endSession(),
			$this->equalTo(json_decode($this->sampleString))
		);
	}
}
