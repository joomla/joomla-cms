<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JGoogle.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Google
 * @since       12.3
 */
class JGoogleEmbedAnalyticsTest extends TestCase
{
	/**
	 * @var    JRegistry  Options for the JOAuth2Client object.
	 */
	protected $options;

	/**
	 * @var    JUri  URI of the page being rendered.
	 */
	protected $uri;

	/**
	 * @var    JGoogle  Object under test.
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

		$this->options = new JRegistry;

		$this->uri = new JUri;
		$this->object = new JGoogleEmbedAnalytics($this->options, $this->uri);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		unset($this->options);
		unset($this->uri);
		unset($this->object);
		parent::tearDown();
	}

	/**
	 * Tests the getCode method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGetCode()
	{
		$this->object->setOption('code', 'abcdefghijklmnopqrstuvwxyz');
		$code = $this->object->getCode();
		$this->assertEquals($code, 'abcdefghijklmnopqrstuvwxyz');
	}

	/**
	 * Tests the setCode method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testSetCode()
	{
		$this->object->setCode('abcdefghijklmnopqrstuvwxyz');
		$code = $this->object->getOption('code');
		$this->assertEquals($code, 'abcdefghijklmnopqrstuvwxyz');
	}

	/**
	 * Tests the isAsync method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testIsAsync()
	{
		$async = $this->object->isAsync();
		$this->assertTrue($async);

		$this->object->setOption('async', false);
		$async = $this->object->isAsync();
		$this->assertFalse($async);

		$this->object->setOption('async', true);
		$async = $this->object->isAsync();
		$this->assertTrue($async);
	}

	/**
	 * Tests the useAsync method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testUseAsync()
	{
		$this->object->useAsync();
		$async = $this->object->getOption('async');
		$this->assertTrue($async);
	}

	/**
	 * Tests the useAsync method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testUseSync()
	{
		$this->object->useSync();
		$async = $this->object->getOption('async');
		$this->assertFalse($async);
	}

	/**
	 * Tests the addCall method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testAddCall()
	{
		$call = $this->object->addCall('method', array('value'));
		$this->assertEquals($call, array('name' => 'method', 'params' => array('value')));
	}

	/**
	 * Tests the listCalls method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testListCalls()
	{
		$calls = $this->object->listCalls();
		$this->assertEquals($calls, array());

		$this->object->setOption('calls', array(array('name' => 'method', 'params' => array('value'))));
		$calls = $this->object->listCalls();
		$this->assertEquals($calls, array(array('name' => 'method', 'params' => array('value'))));
	}

	/**
	 * Tests the deleteCalls method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testDeleteCalls()
	{
		$call0 = $this->object->addCall('method1');
		$call1 = $this->object->addCall('method2');
		$call2 = $this->object->addCall('method3', array('key' => 'value'));

		$call = $this->object->deleteCall();
		$this->assertEquals($call, $call2);

		$call3 = $this->object->addCall('method4');

		$call = $this->object->deleteCall(1);
		$this->assertEquals($call, $call1);
		$call = $this->object->deleteCall(0);
		$this->assertEquals($call, $call0);
		$call = $this->object->deleteCall();
		$this->assertEquals($call, $call3);
	}

	/**
	 * Tests the createCall method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testCreateCall()
	{
		$this->object->useAsync();
		$output = $this->object->createCall('method', array(false));
		$this->assertEquals($output, "_gaq.push(['method',false]);");

		$this->object->useSync();
		$output = $this->object->createCall('method', array(false));
		$this->assertEquals($output, "pageTracker.method(false);");
	}

	/**
	 * Tests the addCustomVar method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testAddCustomVar()
	{
		$call = $this->object->addCustomVar(3, 'hello', 4, 2);
		$this->assertEquals($call, array('name' => '_setCustomVar', 'params' => array(3, 'hello', 4, 2)));
	}

	/**
	 * Tests the createCall method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testCreateCustomVar()
	{
		$output = $this->object->createCustomVar(3, 'hello', 4, 2);
		$this->assertEquals($output, $this->object->createCall('_setCustomVar', array(3, 'hello', 4, 2)));
	}

	/**
	 * Tests the addEvent method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testAddEvent()
	{
		$call = $this->object->addEvent('shopping', 'click', 'double', 4, true);
		$this->assertEquals($call, array('name' => '_trackEvent', 'params' => array('shopping', 'click', 'double', 4, true)));
	}

	/**
	 * Tests the createCall method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testCreateEvent()
	{
		$output = $this->object->createEvent('shopping', 'click', 'double', 4, true);
		$this->assertEquals($output, $this->object->createCall('_trackEvent', array('shopping', 'click', 'double', 4, true)));
	}

	/**
	 * Tests the getHeader method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGetHeader()
	{
		// Sync
		$this->object->useSync();
		$header = $this->object->getHeader();
		$this->assertEquals('', $header);

		// Sync
		$this->object->useAsync();
		$this->object->setCode('123456');
		$this->object->addCall('method1', array('hello', true, 5, null));
		$this->object->addEvent('shopping', 'click', 'double', 4, true);
		$this->object->addCustomVar(3, 'hello', 4, 2);

		$header = $this->object->getHeader();

		$this->assertContains("_gaq.push(['_setAccount', '123456']);", $header);
		$this->assertContains('_gaq.push([\'method1\',"hello",true,5,null]);', $header);
		$this->assertContains('_gaq.push([\'_trackEvent\',"shopping","click","double",4,true]);', $header);
		$this->assertContains('_gaq.push([\'_setCustomVar\',3,"hello",4,2]);', $header);
	}

	/**
	 * Tests the getHeader method without a code
	 *
	 * @group	JGoogle
	 * @expectedException UnexpectedValueException
	 * @return void
	 */
	public function testGetHeaderException()
	{
		$this->object->getHeader();
	}

	/**
	 * Tests the getBody method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testGetBody()
	{
		$this->object->setCode('123456');

		// Async
		$this->object->useAsync();
		$body = $this->object->getBody();
		$this->assertContains("ga.src = 'http://www.google-analytics.com/ga.js';", $body);

		// Sync
		$this->object->useSync();

		$this->object->addCall('method1', array('hello', true, 5, null));
		$this->object->addEvent('shopping', 'click', 'double', 4, true);
		$this->object->addCustomVar(3, 'hello', 4, 2);

		$body = $this->object->getBody();

		$this->assertContains("document.write(unescape(\"%3Cscript src='http://www.google-analytics.com/ga.js' " .
			"type='text/javascript'%3E%3C/script%3E\"));", $body
		);
		$this->assertContains("var pageTracker = _gat._getTracker('123456');", $body);

		$this->assertContains('pageTracker.method1("hello",true,5,null);', $body);
		$this->assertContains('pageTracker._trackEvent("shopping","click","double",4,true);', $body);
		$this->assertContains('pageTracker._setCustomVar(3,"hello",4,2);', $body);
	}

	/**
	 * Tests the getBody method without a code
	 *
	 * @group	JGoogle
	 * @expectedException UnexpectedValueException
	 * @return void
	 */
	public function testGetBodyException()
	{
		$this->object->getBody();
	}

	/**
	 * Tests the echoHeader method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testEchoHeader()
	{
		$this->object->setCode('123456');

		$header = $this->object->getHeader();
		$this->expectOutputString($header);
		$this->object->echoHeader();
	}

	/**
	 * Tests the echoBody method
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function testEchoBody()
	{
		$this->object->setCode('123456');

		$body = $this->object->getBody();
		$this->expectOutputString($body);
		$this->object->echoBody();
	}
}
