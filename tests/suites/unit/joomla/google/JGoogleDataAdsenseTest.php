<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/google/data/adsense.php';

/**
 * Test class for JGoogleDataAdsense.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Google
 * @since       12.2
 */
class JGoogleDataAdsenseTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JRegistry  Options for the JOauthV2client object.
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
	 * @var    JOauthV2client  The OAuth client for sending requests to Google.
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
		$this->oauth = new JOauthV2client($this->options, $this->http, $this->input);
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
	 * @group	JGoogle
	 * @return void
	 */
	public function testAuth()
	{
		$this->assertEquals($this->auth->auth(), $this->object->auth());
	}

	/**
	 * Tests the isauth method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testIsAuth()
	{
		$this->assertEquals($this->auth->isAuth(), $this->object->authenticated());
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
		$this->assertEquals($result, array('items' => array('1' => 1, '2' => 2)));
	}

	/**
	 * Tests the listAccounts method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testListAccounts()
	{
		$this->http->expects($this->once())->method('get')->will($this->returnCallback('jsonAdsenseCallback'));
		$result = $this->object->listAccounts(array('option' => 'value'));
		$this->assertEquals($result, array('1' => 1, '2' => 2));
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
		$this->assertEquals($result, array('items' => array('1' => 1, '2' => 2)));
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
		$this->assertEquals($result, array('items' => array('1' => 1, '2' => 2)));
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
		$result = $this->object->listUrlChannels('accountID', array('option' => 'value'));
		$this->assertEquals($result, array('1' => 1, '2' => 2));
	}

	/**
	 * Tests the createCalendar method
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
		$end->setTimestamp(time() + 3600)->setTimeZone($timezone);

		$result = $this->object->generateReport('accountID', time(), time() + 100000, array('option' => 'value'));
		$this->assertEquals($result, array('rows' => array(1, 2), 'totalMatchedRows' => 1));

		$result = $this->object->generateReport('accountID', time(), false, array('option' => 'value'));
		$this->assertEquals($result, array('rows' => array(1, 2), 'totalMatchedRows' => 1));

		$result = $this->object->generateReport('accountID', '1-1-2000', '1-2-2012', array('option' => 'value'));
		$this->assertEquals($result, array('rows' => array(1, 2), 'totalMatchedRows' => 1));

		$result = $this->object->generateReport('accountID', $start, $end, array('option' => 'value'));
		$this->assertEquals($result, array('rows' => array(1, 2), 'totalMatchedRows' => 1));
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
 * @since   12.2
 */
function jsonAdsenseCallback($url, array $headers = null, $timeout = null)
{
	$response->code = 200;
	$response->headers = array('Content-Type' => 'application/json');
	$response->body = '{"items":{"1":1,"2":2}}';

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
 * @since   12.2
 */
function jsonAdsenseReportCallback($url, array $headers = null, $timeout = null)
{
	$response->code = 200;
	$response->headers = array('Content-Type' => 'application/json');
	$response->body = '{"rows":{"0":1,"1":2},"totalMatchedRows":1}';

	return $response;
}
