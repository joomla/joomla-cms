<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JGoogleAuthOauth2Test .
 *
 * @package     Joomla.UnitTest
 * @subpackage  Google
 * @since       12.3
 */
class JGoogleAuthOauth2Test extends TestCase
{
	/**
	 * @var    JRegistry  Options for the JOAuth2Client object.
	 */
	protected $options;

	/**
	 * @var    JHttp  Mock client object.
	 */
	protected $http;

	/**
	 * @var    JInput  The input object to use in retrieving GET/POST data.
	 */
	protected $input;

	/**
	 * @var    JOAuth2Client  The OAuth client for sending requests to Google.
	 */
	protected $oauth;

	/**
	 * @var    JApplicationWeb  The application object to send HTTP headers for redirects.
	 */
	protected $application;

	/**
	 * @var    JGoogleAuthOauth2  Object under test.
	 */
	protected $object;


	/**
	 * Code that the app closes with.
	 *
	 * @var  int
	 */
	private static $closed;

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
	 * @access protected
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();
		$this->backupServer = $_SERVER;
		$_SERVER['HTTP_HOST'] = 'mydomain.com';
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';
		$_SERVER['REQUEST_URI'] = '/index.php';
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		$this->options = new JRegistry;
		$this->http = $this->getMockBuilder('JHttp')
					->setMethods(array('head', 'get', 'delete', 'trace', 'post', 'put', 'patch'))
					->setConstructorArgs(array($this->options))
					->getMock();
		$this->input = $this->getMockInput();

		$mockApplication = $this->getMockWeb();
		$mockApplication->expects($this->any())
			->method('close')
			->willReturnCallback(array($this, 'mockClose'));
		$this->application = $mockApplication;

		$this->oauth = new JOAuth2Client($this->options, $this->http, $this->input, $this->application);
		$this->object = new JGoogleAuthOauth2($this->options, $this->oauth);
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
		unset($this->http);
		unset($this->input);
		unset($this->application);
		unset($this->oauth);
		unset($this->object);
		parent::tearDown();
	}

	/**
	 * Tests the auth method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testAuth()
	{
		$this->object->setOption('clientid', '01234567891011.apps.googleusercontent.com');
		$this->object->setOption('scope', array('https://www.googleapis.com/auth/adsense', 'https://www.googleapis.com/auth/calendar'));
		$this->object->setOption('redirecturi', 'http://localhost/oauth');
		$this->object->setOption('sendheaders', true);

		$this->object->authenticate();
		$this->assertEquals(0, self::$closed);

		$this->object->setOption('clientsecret', 'jeDs8rKw_jDJW8MMf-ff8ejs');
		$this->input->set('code', '4/wEr_dK8SDkjfpwmc98KejfiwJP-f4wm.kdowmnr82jvmeisjw94mKFIJE48mcEM');
		$this->http->expects($this->once())->method('post')->willReturnCallback(array($this, 'jsonGrantOauthCallback'));
		$result = $this->object->authenticate();
		$this->assertEquals('accessvalue', $result['access_token']);
		$this->assertEquals('refreshvalue', $result['refresh_token']);
		$this->assertEquals(3600, $result['expires_in']);
		$this->assertEquals(time(), $result['created'], null, 10);
	}

	/**
	 * Tests the isauth method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testIsAuth()
	{
		$this->assertFalse($this->object->isAuthenticated());

		$token['access_token'] = 'accessvalue';
		$token['refresh_token'] = 'refreshvalue';
		$token['created'] = time();
		$token['expires_in'] = 3600;
		$this->oauth->setToken($token);

		$this->assertTrue($this->object->isAuthenticated());

		$token['created'] = time() - 4000;
		$token['expires_in'] = 3600;
		$this->oauth->setToken($token);

		$this->assertFalse($this->object->isAuthenticated());
	}

	/**
	 * Tests the auth method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testQuery()
	{
		$token['access_token'] = 'accessvalue';
		$token['refresh_token'] = 'refreshvalue';
		$token['created'] = time() - 1800;
		$token['expires_in'] = 3600;
		$this->oauth->setToken($token);

		$this->http->expects($this->once())->method('get')->willReturnCallback(array($this, 'getOauthCallback'));
		$result = $this->object->query('https://www.googleapis.com/auth/calendar', array('param' => 'value'), array(), 'get');
		$this->assertEquals($result->body, 'Lorem ipsum dolor sit amet.');
		$this->assertEquals(200, $result->code);

		$this->http->expects($this->once())->method('post')->willReturnCallback(array($this, 'queryOauthCallback'));
		$result = $this->object->query('https://www.googleapis.com/auth/calendar', array('param' => 'value'), array(), 'post');
		$this->assertEquals($result->body, 'Lorem ipsum dolor sit amet.');
		$this->assertEquals(200, $result->code);
	}

	/**
	 * Tests the googlize method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGooglize()
	{
		$this->assertEquals(null, $this->object->getOption('authurl'));
		$this->assertEquals(null, $this->object->getOption('tokenurl'));

		$token['access_token'] = 'accessvalue';
		$token['refresh_token'] = 'refreshvalue';
		$token['created'] = time() - 1800;
		$token['expires_in'] = 3600;
		$this->oauth->setToken($token);

		$this->http->expects($this->once())->method('get')->willReturnCallback(array($this, 'getOauthCallback'));
		$this->object->query('https://www.googleapis.com/auth/calendar', array('param' => 'value'), array(), 'get');

		$this->assertEquals('https://accounts.google.com/o/oauth2/auth', $this->object->getOption('authurl'));
		$this->assertEquals('https://accounts.google.com/o/oauth2/token', $this->object->getOption('tokenurl'));
	}

	/**
	 * Tests the setOption method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testSetOption()
	{
		$this->object->setOption('key', 'value');

		$this->assertThat(
			$this->options->get('key'),
			$this->equalTo('value')
		);
	}

	/**
	 * Tests the getOption method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGetOption()
	{
		$this->options->set('key', 'value');

		$this->assertThat(
			$this->object->getOption('key'),
			$this->equalTo('value')
		);
	}

	/**
	 * Dummy
	 *
	 * @param   string   $url      Path to the resource.
	 * @param   mixed    $data     Either an associative array or a string to be sent with the request.
	 * @param   array    $headers  An array of name-value pairs to include in the header of the request
	 * @param   integer  $timeout  Read timeout in seconds.
	 *
	 * @return  JHttpResponse
	 *
	 * @since   12.3
	 */
	public static function jsonGrantOauthCallback($url, $data, array $headers = null, $timeout = null)
	{
		$response = new stdClass;

		$response->code = 200;
		$response->headers = array('Content-Type' => 'application/json');
		$response->body = '{"access_token":"accessvalue","refresh_token":"refreshvalue","expires_in":3600}';

		return $response;
	}

	/**
	 * Dummy
	 *
	 * @param   string   $url      Path to the resource.
	 * @param   mixed    $data     Either an associative array or a string to be sent with the request.
	 * @param   array    $headers  An array of name-value pairs to include in the header of the request
	 * @param   integer  $timeout  Read timeout in seconds.
	 *
	 * @return  JHttpResponse
	 *
	 * @since   12.3
	 */
	public static function queryOauthCallback($url, $data, array $headers = null, $timeout = null)
	{
		$response = new stdClass;

		$response->code = 200;
		$response->headers = array('Content-Type' => 'text/html');
		$response->body = 'Lorem ipsum dolor sit amet.';

		return $response;
	}

	/**
	 * Dummy
	 *
	 * @param   string   $url      Path to the resource.
	 * @param   array    $headers  An array of name-value pairs to include in the header of the request.
	 * @param   integer  $timeout  Read timeout in seconds.
	 *
	 * @return  JHttpResponse
	 *
	 * @since   12.3
	 */
	public static function getOauthCallback($url, array $headers = null, $timeout = null)
	{
		$response = new stdClass;

		$response->code = 200;
		$response->headers = array('Content-Type' => 'text/html');
		$response->body = 'Lorem ipsum dolor sit amet.';

		return $response;
	}

	/**
	 * @param   integer  $code  The exit code (optional; default is 0).
	 */
	public static function mockClose($code = 0)
	{
		self::$closed = $code;
	}
}
