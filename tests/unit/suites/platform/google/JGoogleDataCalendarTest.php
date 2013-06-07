<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JGoogleDataCalendar.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Google
 * @since       12.3
 */
class JGoogleDataCalendarTest extends TestCase
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
		parent::setUp();

		$_SERVER['HTTP_HOST'] = 'mydomain.com';
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';
		$_SERVER['REQUEST_URI'] = '/index.php';
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		$this->options = new JRegistry;
		$this->http = $this->getMock('JHttp', array('head', 'get', 'delete', 'trace', 'post', 'put', 'patch'), array($this->options));
		$this->input = new JInput;
		$this->oauth = new JOAuth2Client($this->options, $this->http, $this->input);
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
	 * Tests the removeCalendar method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testRemoveCalendar()
	{
		$this->http->expects($this->once())->method('delete')->will($this->returnCallback('emptyCalendarCallback'));
		$result = $this->object->removeCalendar('calendarID');
		$this->assertTrue($result);
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
		$this->assertTrue($result);
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
		$this->assertTrue($result);
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
		$result = $this->object->deleteEvent('calendarID', 'eventID');
		$this->assertTrue($result);
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
	 * Tests the createEvent method with a bad start date
	 *
	 * @group	JGoogle
	 * @expectedException InvalidArgumentException
	 * @return void
	 */
	public function testCreateEventStartException()
	{
		$this->object->createEvent('calendarID', array(true));
	}

	/**
	 * Tests the createEvent method with a bad end date
	 *
	 * @group	JGoogle
	 * @expectedException InvalidArgumentException
	 * @return void
	 */
	public function testCreateEventEndException()
	{
		$this->object->createEvent('calendarID', time(), array(true));
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

		$functions['removeCalendar'] = array('calendarID');
		$functions['getCalendar'] = array('calendarID');
		$functions['addCalendar'] = array('calendarID', array('option' => 'value'));
		$functions['listCalendars'] = array(array('option' => 'value'));
		$functions['editCalendarSettings'] = array('calendarID', array('option' => 'value'));
		$functions['clearCalendar'] = array('calendarID');
		$functions['deleteCalendar'] = array('calendarID');
		$functions['createCalendar'] = array('Title', array('option' => 'value'));
		$functions['editCalendar'] = array('calendarID', array('option' => 'value'));
		$functions['deleteEvent'] = array('calendarID', 'eventID');
		$functions['getEvent'] = array('calendarID', 'eventID', array('option' => 'value'));
		$functions['createEvent'] = array('calendarID', time(), time() + 100000, array('option' => 'value'));
		$functions['listRecurrences'] = array('calendarID', 'eventID', array('option' => 'value'));
		$functions['listEvents'] = array('calendarID', array('option' => 'value'));
		$functions['moveEvent'] = array('calendarID', 'eventID', 'newCalendarID');
		$functions['editEvent'] = array('calendarID', 'eventID', array('option' => 'value'));

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
		$this->http->expects($this->atLeastOnce())->method('get')->will($this->returnCallback('calendarExceptionCallback'));
		$this->http->expects($this->atLeastOnce())->method('delete')->will($this->returnCallback('calendarExceptionCallback'));
		$this->http->expects($this->atLeastOnce())->method('post')->will($this->returnCallback('calendarDataExceptionCallback'));
		$this->http->expects($this->atLeastOnce())->method('put')->will($this->returnCallback('calendarDataExceptionCallback'));

		$functions['removeCalendar'] = array('calendarID');
		$functions['getCalendar'] = array('calendarID');
		$functions['addCalendar'] = array('calendarID', array('option' => 'value'));
		$functions['listCalendars'] = array(array('option' => 'value'));
		$functions['editCalendarSettings'] = array('calendarID', array('option' => 'value'));
		$functions['clearCalendar'] = array('calendarID');
		$functions['deleteCalendar'] = array('calendarID');
		$functions['createCalendar'] = array('Title', array('option' => 'value'));
		$functions['editCalendar'] = array('calendarID', array('option' => 'value'));
		$functions['deleteEvent'] = array('calendarID', 'eventID');
		$functions['getEvent'] = array('calendarID', 'eventID', array('option' => 'value'));
		$functions['createEvent'] = array('calendarID', time(), time() + 100000, array('option' => 'value'));
		$functions['listRecurrences'] = array('calendarID', 'eventID', array('option' => 'value'));
		$functions['listEvents'] = array('calendarID', array('option' => 'value'));
		$functions['moveEvent'] = array('calendarID', 'eventID', 'newCalendarID');
		$functions['editEvent'] = array('calendarID', 'eventID', array('option' => 'value'));

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
 * @param   mixed    $data     Either an associative array or a string to be sent with the request.
 * @param   array    $headers  An array of name-value pairs to include in the header of the request.
 * @param   integer  $timeout  Read timeout in seconds.
 *
 * @return  JHttpResponse
 *
 * @since   12.3
 */
function jsonDataCalendarCallback($url, $data, array $headers = null, $timeout = null)
{
	$response = new stdClass;

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
 * @since   12.3
 */
function emptyDataCalendarCallback($url, $data, array $headers = null, $timeout = null)
{
	$response = new stdClass;

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
 * @since   12.3
 */
function jsonCalendarCallback($url, array $headers = null, $timeout = null)
{
	$response = new stdClass;

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
 * @since   12.3
 */
function emptyCalendarCallback($url, array $headers = null, $timeout = null)
{
	$response = new stdClass;

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
 * @since   12.3
 */
function calendarExceptionCallback($url, array $headers = null, $timeout = null)
{
	$response = new stdClass;

	$response->code = 200;
	$response->headers = array('Content-Type' => 'application/json');
	$response->body = 'BADDATA';

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
 * @since   12.3
 */
function calendarDataExceptionCallback($url, $data, array $headers = null, $timeout = null)
{
	$response = new stdClass;

	$response->code = 200;
	$response->headers = array('Content-Type' => 'application/json');
	$response->body = 'BADDATA';

	return $response;
}
