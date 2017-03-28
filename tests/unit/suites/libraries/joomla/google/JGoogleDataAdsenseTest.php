<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JGoogleDataAdsense.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Google
 * @since       12.3
 */
class JGoogleDataAdsenseTest extends TestCase
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
	 * @var    JGoogleAuthOauth2  The Google OAuth client for sending requests.
	 */
	protected $auth;

	/**
	 * @var    JGoogleDataAdsense  Object under test.
	 */
	protected $object;

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
		$this->input = new JInput;
		$this->oauth = new JOAuth2Client($this->options, $this->http, $this->input);
		$this->auth = new JGoogleAuthOauth2($this->options, $this->oauth);
		$this->object = new JGoogleDataAdsense($this->options, $this->auth);

		$this->object->setOption('clientid', '01234567891011.apps.googleusercontent.com');
		$this->object->setOption('clientsecret', 'jeDs8rKw_jDJW8MMf-ff8ejs');
		$this->object->setOption('redirecturi', 'http://localhost/oauth');

		$token['access_token'] = 'accessvalue';
		$token['refresh_token'] = 'refreshvalue';
		$token['created'] = time() - 1800;
		$token['expires_in'] = 3600;
		$this->oauth->setToken($token);
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     \PHPUnit\Framework\TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		$_SERVER = $this->backupServer;
		unset($this->backupServer);
		unset($this->options);
		unset($this->http);
		unset($this->input);
		unset($this->auth);
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
		$this->assertEquals($this->auth->authenticate(), $this->object->authenticate());
	}

	/**
	 * Tests the isauth method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testIsAuth()
	{
		$this->assertEquals($this->auth->isAuthenticated(), $this->object->isAuthenticated());
	}

	/**
	 * Tests the getAccount method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGetAccount()
	{
		$this->http->expects($this->once())->method('get')->will($this->returnCallback('jsonAdsenseCallback'));
		$result = $this->object->getAccount('accountID');
		$this->assertEquals($result, array('items' => array('1' => 1, '2' => 2), 'nextPageToken' => '1234'));
	}

	/**
	 * Tests the listAccounts method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testListAccounts()
	{
		$this->http->expects($this->exactly(2))->method('get')->will($this->returnCallback('jsonAdsenseCallback'));
		$result = $this->object->listAccounts(array('option' => 'value', 'option2' => 'value2'), 2);
		$this->assertEquals($result, array(1, 2, 1, 2));
	}

	/**
	 * Tests the listClients method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testListClients()
	{
		$this->http->expects($this->once())->method('get')->will($this->returnCallback('jsonAdsenseCallback'));
		$result = $this->object->listClients('accountID', array('option' => 'value'));
		$this->assertEquals($result, array('1' => 1, '2' => 2));
	}

	/**
	 * Tests the getUnit method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGetUnit()
	{
		$this->http->expects($this->once())->method('get')->will($this->returnCallback('jsonAdsenseCallback'));
		$result = $this->object->getUnit('accountID', 'clientID', 'unitID');
		$this->assertEquals($result, array('items' => array('1' => 1, '2' => 2), 'nextPageToken' => '1234'));
	}

	/**
	 * Tests the listUnitChannels method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testListUnitChannels()
	{
		$this->http->expects($this->once())->method('get')->will($this->returnCallback('jsonAdsenseCallback'));
		$result = $this->object->listUnitChannels('accountID', 'clientID', 'unitID', array('option' => 'value'));
		$this->assertEquals($result, array('1' => 1, '2' => 2));
	}

	/**
	 * Tests the getChannel method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGetChannel()
	{
		$this->http->expects($this->once())->method('get')->will($this->returnCallback('jsonAdsenseCallback'));
		$result = $this->object->getChannel('accountID', 'clientID', 'channelID');
		$this->assertEquals($result, array('items' => array('1' => 1, '2' => 2), 'nextPageToken' => '1234'));
	}

	/**
	 * Tests the listChannels method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testListChannels()
	{
		$this->http->expects($this->once())->method('get')->will($this->returnCallback('jsonAdsenseCallback'));
		$result = $this->object->listChannels('accountID', 'clientID', array('option' => 'value'));
		$this->assertEquals($result, array('1' => 1, '2' => 2));
	}

	/**
	 * Tests the listChannelUnits method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testListChannelUnits()
	{
		$this->http->expects($this->once())->method('get')->will($this->returnCallback('jsonAdsenseCallback'));
		$result = $this->object->listChannelUnits('accountID', 'clientID', 'channelID', array('option' => 'value'));
		$this->assertEquals($result, array('1' => 1, '2' => 2));
	}

	/**
	 * Tests the listUrlChannels method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testListUrlChannels()
	{
		$this->http->expects($this->once())->method('get')->will($this->returnCallback('jsonAdsenseCallback'));
		$result = $this->object->listUrlChannels('accountID', 'clientID', array('option' => 'value'));
		$this->assertEquals($result, array('1' => 1, '2' => 2));
	}

	/**
	 * Tests the generateReport method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGenerateReport()
	{
		$this->http->expects($this->exactly(4))->method('get')->will($this->returnCallback('jsonAdsenseReportCallback'));
		$timezone = new DateTimeZone('Europe/London');
		$start = new DateTime('now');
		$end = new DateTime;
		$end->setTimestamp(time() + 3600)->setTimezone($timezone);

		$result = $this->object->generateReport('accountID', time(), time() + 100000, array('option' => 'value'));
		$this->assertEquals($result, array('rows' => array(1, 2), 'totalMatchedRows' => 1));

		$result = $this->object->generateReport('accountID', time(), false, array('option' => 'value'));
		$this->assertEquals($result, array('rows' => array(1, 2), 'totalMatchedRows' => 1));

		$result = $this->object->generateReport('accountID', '1-1-2000', '1-2-2012', array('option' => 'value'));
		$this->assertEquals($result, array('rows' => array(1, 2), 'totalMatchedRows' => 1));

		$result = $this->object->generateReport('accountID', $start, $end, array('option' => 'value'));
		$this->assertEquals($result, array('rows' => array(1, 2), 'totalMatchedRows' => 1));
	}

	/**
	 * Tests the generateReport method with a bad start date
	 *
	 * @group	JGoogle
	 * @expectedException InvalidArgumentException
	 * @return void
	 */
	public function testGenerateReportStartException()
	{
		$this->object->generateReport('accountID', array(true));
	}

	/**
	 * Tests the generateReport method with a bad end date
	 *
	 * @group	JGoogle
	 * @expectedException InvalidArgumentException
	 * @return void
	 */
	public function testGenerateReportEndException()
	{
		$this->object->generateReport('accountID', time(), array(true));
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
	 * Tests that all functions properly return false
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testFalse()
	{
		$this->oauth->setToken(false);

		$functions['getAccount'] = array('accountID');
		$functions['listAccounts'] = array(array('option' => 'value'));
		$functions['listClients'] = array(array('option' => 'value'));
		$functions['getUnit'] = array('accountID', 'clientID', 'unitID');
		$functions['listUnitChannels'] = array('accountID', 'clientID', 'unitID', array('option' => 'value'));
		$functions['getChannel'] = array('accountID', 'clientID', 'channelID');
		$functions['listChannels'] = array('accountID', 'clientID', array('option' => 'value'));
		$functions['listChannelUnits'] = array('accountID', 'clientID', 'channelID', array('option' => 'value'));
		$functions['listUrlChannels'] = array('accountID', array('option' => 'value'));
		$functions['generateReport'] = array('accountID', time(), time() + 100000, array('option' => 'value'));

		foreach ($functions as $function => $params)
		{
			$this->assertFalse(call_user_func_array(array($this->object, $function), $params));
		}
	}

	/**
	 * Tests that all functions properly return Exceptions
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testExceptions()
	{
		$this->http->expects($this->atLeastOnce())->method('get')->will($this->returnCallback('adsenseExceptionCallback'));

		$functions['getAccount'] = array('accountID');
		$functions['listAccounts'] = array(array('option' => 'value'));
		$functions['listClients'] = array('accountID', array('option' => 'value'));
		$functions['getUnit'] = array('accountID', 'clientID', 'unitID');
		$functions['listUnitChannels'] = array('accountID', 'clientID', 'unitID', array('option' => 'value'));
		$functions['getChannel'] = array('accountID', 'clientID', 'channelID');
		$functions['listChannels'] = array('accountID', 'clientID', array('option' => 'value'));
		$functions['listChannelUnits'] = array('accountID', 'clientID', 'channelID', array('option' => 'value'));
		$functions['listUrlChannels'] = array('accountID', 'clientID', array('option' => 'value'));
		$functions['generateReport'] = array('accountID', time(), time() + 100000, array('option' => 'value'));

		foreach ($functions as $function => $params)
		{
			$exception = false;

			try
			{
				call_user_func_array(array($this->object, $function), $params);
			}
			catch (UnexpectedValueException $e)
			{
				$exception = true;
				$this->assertEquals($e->getMessage(), 'Unexpected data received from Google: `BADDATA`.');
			}
			$this->assertTrue($exception);
		}
	}
}

/**
 * Dummy method
 *
 * @param   string   $url      Path to the resource.
 * @param   array    $headers  An array of name-value pairs to include in the header of the request.
 * @param   integer  $timeout  Read timeout in seconds.
 *
 * @return  JHttpResponse
 *
 * @since   12.3
 */
function jsonAdsenseCallback($url, array $headers = null, $timeout = null)
{
	$response = new stdClass;

	$response->code = 200;
	$response->headers = array('Content-Type' => 'application/json');
	$response->body = '{"items":{"1":1,"2":2},"nextPageToken":"1234"}';

	return $response;
}

/**
 * Dummy method
 *
 * @param   string   $url      Path to the resource.
 * @param   array    $headers  An array of name-value pairs to include in the header of the request.
 * @param   integer  $timeout  Read timeout in seconds.
 *
 * @return  JHttpResponse
 *
 * @since   12.3
 */
function jsonAdsenseReportCallback($url, array $headers = null, $timeout = null)
{
	$response = new stdClass;

	$response->code = 200;
	$response->headers = array('Content-Type' => 'application/json');
	$response->body = '{"rows":{"0":1,"1":2},"totalMatchedRows":1}';

	return $response;
}

/**
 * Dummy method
 *
 * @param   string   $url      Path to the resource.
 * @param   array    $headers  An array of name-value pairs to include in the header of the request.
 * @param   integer  $timeout  Read timeout in seconds.
 *
 * @return  JHttpResponse
 *
 * @since   12.3
 */
function adsenseExceptionCallback($url, array $headers = null, $timeout = null)
{
	$response = new stdClass;

	$response->code = 200;
	$response->headers = array('Content-Type' => 'application/json');
	$response->body = 'BADDATA';

	return $response;
}
