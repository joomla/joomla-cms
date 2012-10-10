<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/google/data/calendar.php';

/**
 * Test class for JGoogleDataCalendar.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Google
 * @since       12.2
 */
class JGoogleDataCalendarTest extends PHPUnit_Framework_TestCase
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
	 * @var    JGoogleDataCalendar  Object under test.
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
		$this->object = new JGoogleDataCalendar($this->options, $this->auth);

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
	 * Tests the removeCalendar method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testRemoveCalendar()
	{
		$this->http->expects($this->once())->method('delete')->will($this->returnCallback('emptyCalendarCallback'));
		$result = $this->object->removeCalendar('calendarID');
		$this->assertEquals($result, true);
	}

	/**
	 * Tests the getCalendar method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGetCalendar()
	{
		$this->http->expects($this->once())->method('get')->will($this->returnCallback('jsonCalendarCallback'));
		$result = $this->object->getCalendar('calendarID');
		$this->assertEquals($result, array('items' => array('1' => 1, '2' => 2)));
	}

	/**
	 * Tests the addCalendar method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testAddCalendar()
	{
		$this->http->expects($this->once())->method('post')->will($this->returnCallback('jsonDataCalendarCallback'));
		$result = $this->object->addCalendar('calendarID', array('option' => 'value'));
		$this->assertEquals($result, array('items' => array('1' => 1, '2' => 2)));
	}

	/**
	 * Tests the listCalendars method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testListCalendars()
	{
		$this->http->expects($this->once())->method('get')->will($this->returnCallback('jsonCalendarCallback'));
		$result = $this->object->listCalendars(array('option' => 'value'));
		$this->assertEquals($result, array('1' => 1, '2' => 2));
	}

	/**
	 * Tests the editCalendarSettings method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testEditCalendarSettings()
	{
		$this->http->expects($this->once())->method('put')->will($this->returnCallback('jsonDataCalendarCallback'));
		$result = $this->object->editCalendarSettings('calendarID', array('option' => 'value'));
		$this->assertEquals($result, array('items' => array('1' => 1, '2' => 2)));
	}

	/**
	 * Tests the clearCalendar method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testClearCalendar()
	{
		$this->http->expects($this->once())->method('post')->will($this->returnCallback('emptyDataCalendarCallback'));
		$result = $this->object->clearCalendar('calendarID');
		$this->assertEquals($result, true);
	}

	/**
	 * Tests the deleteCalendar method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testDeleteCalendar()
	{
		$this->http->expects($this->once())->method('delete')->will($this->returnCallback('emptyCalendarCallback'));
		$result = $this->object->deleteCalendar('calendarID');
		$this->assertEquals($result, true);
	}

	/**
	 * Tests the createCalendar method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testCreateCalendar()
	{
		$this->http->expects($this->once())->method('post')->will($this->returnCallback('jsonDataCalendarCallback'));
		$result = $this->object->createCalendar('Title', array('option' => 'value'));
		$this->assertEquals($result, array('items' => array('1' => 1, '2' => 2)));
	}

	/**
	 * Tests the editCalendar method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testEditCalendar()
	{
		$this->http->expects($this->once())->method('put')->will($this->returnCallback('jsonDataCalendarCallback'));
		$result = $this->object->editCalendar('calendarID', array('option' => 'value'));
		$this->assertEquals($result, array('items' => array('1' => 1, '2' => 2)));
	}

	/**
	 * Tests the deleteEvent method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testDeleteEvent()
	{
		$this->http->expects($this->once())->method('delete')->will($this->returnCallback('emptyCalendarCallback'));
		$result = $this->object->deleteCalendar('calendarID', 'eventID');
		$this->assertEquals($result, true);
	}

	/**
	 * Tests the getEvent method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGetEvent()
	{
		$this->http->expects($this->once())->method('get')->will($this->returnCallback('jsonCalendarCallback'));
		$result = $this->object->getEvent('calendarID', 'eventID', array('option' => 'value'));
		$this->assertEquals($result, array('items' => array('1' => 1, '2' => 2)));
	}

	/**
	 * Tests the createCalendar method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testCreateEvent()
	{
		$this->http->expects($this->exactly(9))->method('post')->will($this->returnCallback('jsonDataCalendarCallback'));
		$timezone = new DateTimeZone('Europe/London');
		$start = new DateTime('now');
		$end = new DateTime;
		$end->setTimestamp(time() + 3600)->setTimeZone($timezone);

		$result = $this->object->createEvent('calendarID', time(), time() + 100000, array('option' => 'value'));
		$this->assertEquals($result, array('items' => array('1' => 1, '2' => 2)));

		$result = $this->object->createEvent('calendarID', time(), false, array('option' => 'value'));
		$this->assertEquals($result, array('items' => array('1' => 1, '2' => 2)));

		$result = $this->object->createEvent('calendarID', false, false, array('option' => 'value'));
		$this->assertEquals($result, array('items' => array('1' => 1, '2' => 2)));

		$result = $this->object->createEvent('calendarID', '1-1-2000', '1-2-2012', array('option' => 'value'), false);
		$this->assertEquals($result, array('items' => array('1' => 1, '2' => 2)));

		$result = $this->object->createEvent('calendarID', '1-1-2000', '1-2-2012', array('option' => 'value'), false, true);
		$this->assertEquals($result, array('items' => array('1' => 1, '2' => 2)));

		$result = $this->object->createEvent('calendarID', $start, $end, array('option' => 'value'));
		$this->assertEquals($result, array('items' => array('1' => 1, '2' => 2)));

		$result = $this->object->createEvent('calendarID', $start, $end, array('option' => 'value'), true);
		$this->assertEquals($result, array('items' => array('1' => 1, '2' => 2)));

		$result = $this->object->createEvent('calendarID', $start, $end, array('option' => 'value'), 'America/Chicago');
		$this->assertEquals($result, array('items' => array('1' => 1, '2' => 2)));

		$result = $this->object->createEvent('calendarID', $start, $end, array('option' => 'value'), $timezone);
		$this->assertEquals($result, array('items' => array('1' => 1, '2' => 2)));
	}

	/**
	 * Tests the listRecurrences method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testListRecurrences()
	{
		$this->http->expects($this->once())->method('get')->will($this->returnCallback('jsonCalendarCallback'));
		$result = $this->object->listRecurrences('calendarID', 'eventID', array('option' => 'value'));
		$this->assertEquals($result, array('1' => 1, '2' => 2));
	}

	/**
	 * Tests the listEvents method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testListEvents()
	{
		$this->http->expects($this->once())->method('get')->will($this->returnCallback('jsonCalendarCallback'));
		$result = $this->object->listEvents('calendarID', array('option' => 'value'));
		$this->assertEquals($result, array('1' => 1, '2' => 2));
	}

	/**
	 * Tests the moveEvent method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testMoveEvent()
	{
		$this->http->expects($this->once())->method('post')->will($this->returnCallback('jsonDataCalendarCallback'));
		$result = $this->object->moveEvent('calendarID', 'eventID', 'newCalendarID');
		$this->assertEquals($result, array('items' => array('1' => 1, '2' => 2)));
	}

	/**
	 * Tests the editCalendar method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testEditEvent()
	{
		$this->http->expects($this->once())->method('put')->will($this->returnCallback('jsonDataCalendarCallback'));
		$result = $this->object->editEvent('calendarID', 'eventID', array('option' => 'value'));
		$this->assertEquals($result, array('items' => array('1' => 1, '2' => 2)));
	}
}

/**
 * Dummy method
 *
 * @param   string   $url      Path to the resource.
 * @param   mixed    $data     Either an associative array or a string to be sent with the request.
 * @param   array    $headers  An array of name-value pairs to include in the header of the request.
 * @param   integer  $timeout  Read timeout in seconds.
 *
 * @return  JHttpResponse
 *
 * @since   12.2
 */
function jsonDataCalendarCallback($url, $data, array $headers = null, $timeout = null)
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
 * @param   mixed    $data     Either an associative array or a string to be sent with the request.
 * @param   array    $headers  An array of name-value pairs to include in the header of the request.
 * @param   integer  $timeout  Read timeout in seconds.
 *
 * @return  JHttpResponse
 *
 * @since   12.2
 */
function emptyDataCalendarCallback($url, $data, array $headers = null, $timeout = null)
{
	$response->code = 200;
	$response->headers = array('Content-Type' => 'text/html');
	$response->body = '';

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
function jsonCalendarCallback($url, array $headers = null, $timeout = null)
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
function emptyCalendarCallback($url, array $headers = null, $timeout = null)
{
	$response->code = 200;
	$response->headers = array('Content-Type' => 'text/html');
	$response->body = '';

	return $response;
}
