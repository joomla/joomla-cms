<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/oauth/2client.php';

/**
 * Test class for JOauth2client.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Oauth
 * @since       12.2
 */
class JOauth2clientTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JRegistry  Options for the JOauth2client object.
	 */
	protected $options;

	/**
	 * @var    JHttp  Mock client object.
	 */
	protected $client;

	/**
	 * @var    JInput  The input object to use in retrieving GET/POST data.
	 */
	protected $input;

	/**
	 * @var    JOauth2client  Object under test.
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 * @return void
	 */
	protected function setUp()
	{
		$this->options = new JRegistry;
		$this->http = $this->getMock('JOauthHttp', array('head', 'get', 'delete', 'trace', 'post', 'put', 'patch'), array($this->options));
		$this->input = new JInput;
		$this->object = new JOauth2client($this->options, $this->http, $this->input);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @access protected
	 * @return void
	 */
	protected function tearDown()
	{
	}

	/**
	 * Tests the auth method
	 *
	 * @group	JOauth
	 * @return void
	 */
	public function testAuth()
	{
		$this->object->setOption('authurl', 'https://accounts.google.com/o/oauth2/auth');
		$this->object->setOption('clientid', '01234567891011.apps.googleusercontent.com');
		$this->object->setOption('scope', array('https://www.googleapis.com/auth/adsense', 'https://www.googleapis.com/auth/calendar'));
		$this->object->setOption('redirecturi', 'http://localhost/oauth');
		$this->object->setOption('requestparams', array('access_type' => 'offline', 'approval_prompt' => 'auto'));
		$this->object->setOption('sendheaders', true);

		$this->object->auth();
		$headers = JResponse::getHeaders();
		$location = false;
		foreach ($headers as $header)
		{
			if ($header['name'] == 'Location')
			{
				$location = true;
				$this->assertEquals($this->object->createUrl(), $header['value']);
			}
		}
		$this->assertEquals(true, $location);

		$this->object->setOption('tokenurl', 'https://accounts.google.com/o/oauth2/token');
		$this->object->setOption('clientsecret', 'jeDs8rKw_jDJW8MMf-ff8ejs');
		$this->input->set('code', '4/wEr_dK8SDkjfpwmc98KejfiwJP-f4wm.kdowmnr82jvmeisjw94mKFIJE48mcEM');
		$this->http->expects($this->once())->method('post')->will($this->returnCallback('postCallback'));
		$result = $this->object->auth();
		$this->assertEquals('accessvalue', $result['access_token']);
		$this->assertEquals('refreshvalue', $result['refresh_token']);
		$this->assertEquals(3600, $result['expires_in']);
		$this->assertEquals(time(), $result['created'], 10);
	}

	/**
	 * Tests the auth method
	 *
	 * @group	JOauth
	 * @return void
	 */
	public function testCreateUrl()
	{
		$this->object->setOption('authurl', 'https://accounts.google.com/o/oauth2/auth');
		$this->object->setOption('clientid', '01234567891011.apps.googleusercontent.com');
		$this->object->setOption('scope', array('https://www.googleapis.com/auth/adsense', 'https://www.googleapis.com/auth/calendar'));
		$this->object->setOption('redirecturi', 'http://localhost/oauth');
		$this->object->setOption('requestparams', array('access_type' => 'offline', 'approval_prompt' => 'auto'));

		$url = $this->object->createUrl();
		$expected = 'https://accounts.google.com/o/oauth2/auth?response_type=code';
		$expected .= '&client_id=01234567891011.apps.googleusercontent.com';
		$expected .= '&redirect_uri=http%3A%2F%2Flocalhost%2Foauth';
		$expected .= '&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fadsense';
		$expected .= '+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fcalendar';
		$expected .= '&access_type=offline&approval_prompt=auto';
		$this->assertEquals($expected, $url);
	}

	/**
	 * Tests the auth method
	 *
	 * @group	JOauth
	 * @return void
	 */
	public function testQuery()
	{
		$token['access_token'] = 'accessvalue';
		$token['refresh_token'] = 'refreshvalue';
		$token['created'] = time() - 1800;
		$token['expires_in'] = 3600;
		$this->object->setToken($token);

		$this->http->expects($this->once())->method('get')->will($this->returnCallback('getCallback'));
		$result = $this->object->query('https://www.googleapis.com/auth/calendar', array('param' => 'value'), array(), 'get');
		$this->assertEquals($result->body, 'Lorem ipsum dolor sit amet.');
		$this->assertEquals(200, $result->code);

		$this->http->expects($this->once())->method('post')->will($this->returnCallback('postCallback'));
		$result = $this->object->query('https://www.googleapis.com/auth/calendar', array('param' => 'value'), array(), 'post');
		$this->assertEquals($result->body, 'Lorem ipsum dolor sit amet.');
		$this->assertEquals(200, $result->code);
	}

	/**
	 * Tests the setOption method
	 *
	 * @group	JOauth
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
	 * @group	JOauth
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
	 * Tests the setToken method
	 *
	 * @group	JOauth
	 * @return void
	 */
	public function testSetToken()
	{
		$this->object->setToken(array('access_token' => 'RANDOM STRING OF DATA'));

		$this->assertThat(
			$this->options->get('accesstoken'),
			$this->equalTo(array('access_token' => 'RANDOM STRING OF DATA'))
		);

		$this->object->setToken(array('access_token' => 'RANDOM STRING OF DATA', 'expires_in' => 3600));

		$this->assertThat(
			$this->options->get('accesstoken'),
			$this->equalTo(array('access_token' => 'RANDOM STRING OF DATA', 'expires_in' => 3600))
		);

		$this->object->setToken(array('access_token' => 'RANDOM STRING OF DATA', 'expires' => 3600));

		$this->assertThat(
			$this->options->get('accesstoken'),
			$this->equalTo(array('access_token' => 'RANDOM STRING OF DATA', 'expires_in' => 3600))
		);
	}

	/**
	 * Tests the getToken method
	 *
	 * @group	JOauth
	 * @return void
	 */
	public function testGetToken()
	{
		$this->options->set('accesstoken', array('access_token' => 'RANDOM STRING OF DATA'));

		$this->assertThat(
			$this->object->getToken(),
			$this->equalTo(array('access_token' => 'RANDOM STRING OF DATA'))
		);
	}

	/**
	 * Tests the refreshToken method
	 *
	 * @group	JOauth
	 * @return void
	 */
	public function testRefreshToken()
	{
		$this->object->setOption('tokenurl', 'https://accounts.google.com/o/oauth2/token');
		$this->object->setOption('clientid', '01234567891011.apps.googleusercontent.com');
		$this->object->setOption('clientsecret', 'jeDs8rKw_jDJW8MMf-ff8ejs');
		$this->object->setOption('redirecturi', 'http://localhost/oauth');
		$this->object->setOption('userefresh', true);
		$this->object->setToken(array('access_token' => 'RANDOM STRING OF DATA', 'expires' => 3600, 'refresh_token' => ' RANDOM STRING OF DATA'));

		$this->http->expects($this->once())->method('post')->will($this->returnCallback('postCallback'));
		$result = $this->object->refreshToken();
		$this->assertEquals('accessvalue', $result['access_token']);
		$this->assertEquals('refreshvalue', $result['refresh_token']);
		$this->assertEquals(3600, $result['expires_in']);
		$this->assertEquals(time(), $result['created'], 10);
	}
}

/**
 * Method to send the POST command to the server.
 *
 * @param   string   $url      Path to the resource.
 * @param   mixed    $data     Either an associative array or a string to be sent with the request.
 * @param   array    $headers  An array of name-value pairs to include in the header of the request
 * @param   integer  $timeout  Read timeout in seconds.
 *
 * @return  JHttpResponse
 *
 * @since   12.2
 */
function postCallback($url, $data, array $headers = null, $timeout = null)
{
	if (isset($data['grant_type']))
	{
		$response->code = 200;
		$response->headers = array('Content-Type' => 'application/json');
		$response->body = '{"access_token":"accessvalue","refresh_token":"refreshvalue","expires_in":3600}';
	}
	else
	{
		$response->code = 200;
		$response->headers = array('Content-Type' => 'text/html');
		$response->body = 'Lorem ipsum dolor sit amet.';
	}
	return $response;
}

/**
 * Method to send the GET command to the server.
 *
 * @param   string   $url      Path to the resource.
 * @param   array    $headers  An array of name-value pairs to include in the header of the request.
 * @param   integer  $timeout  Read timeout in seconds.
 *
 * @return  JHttpResponse
 *
 * @since   12.2
 */
function getCallback($url, array $headers = null, $timeout = null)
{
	$response->code = 200;
	$response->headers = array('Content-Type' => 'text/html');
	$response->body = 'Lorem ipsum dolor sit amet.';

	return $response;
}
