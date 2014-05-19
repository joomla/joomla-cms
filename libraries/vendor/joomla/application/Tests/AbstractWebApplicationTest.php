<?php
/**
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application\Tests;

use Joomla\Application\AbstractWebApplication;
use Joomla\Application\Web\WebClient;
use Joomla\Registry\Registry;
use Joomla\Test\TestConfig;
use Joomla\Test\TestHelper;

include_once __DIR__ . '/Stubs/ConcreteWeb.php';

/**
 * Test class for Joomla\Application\AbstractWebApplication.
 *
 * @since  1.0
 */
class AbstractWebApplicationTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Value for test host.
	 *
	 * @var    string
	 * @since  1.0
	 */
	const TEST_HTTP_HOST = 'mydomain.com';

	/**
	 * Value for test user agent.
	 *
	 * @var    string
	 * @since  1.0
	 */
	const TEST_USER_AGENT = 'Mozilla/5.0';

	/**
	 * Value for test user agent.
	 *
	 * @var    string
	 * @since  1.0
	 */
	const TEST_REQUEST_URI = '/index.php';

	/**
	 * An instance of the class to test.
	 *
	 * @var    ConcreteWeb
	 * @since  1.0
	 */
	protected $instance;

	/**
	 * Data for detectRequestUri method.
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function getDetectRequestUriData()
	{
		return array(
			// HTTPS, PHP_SELF, REQUEST_URI, HTTP_HOST, SCRIPT_NAME, QUERY_STRING, (resulting uri)
			array(null, '/j/index.php', '/j/index.php?foo=bar', 'joom.la:3', '/j/index.php', '', 'http://joom.la:3/j/index.php?foo=bar'),
			array('on', '/j/index.php', '/j/index.php?foo=bar', 'joom.la:3', '/j/index.php', '', 'https://joom.la:3/j/index.php?foo=bar'),
			array(null, '', '', 'joom.la:3', '/j/index.php', '', 'http://joom.la:3/j/index.php'),
			array(null, '', '', 'joom.la:3', '/j/index.php', 'foo=bar', 'http://joom.la:3/j/index.php?foo=bar'),
		);
	}

	/**
	 * Data for fetchConfigurationData method.
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function getRedirectData()
	{
		return array(
			// Note: url, base, request, (expected result)
			array('/foo', 'http://j.org/', 'http://j.org/index.php?v=1.0', 'http://j.org/foo'),
			array('foo', 'http://j.org/', 'http://j.org/index.php?v=1.0', 'http://j.org/foo'),
		);
	}

	/**
	 * Tests the Joomla\Application\AbstractWebApplication::__construct method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function test__construct()
	{
		$this->assertInstanceOf(
			'Joomla\\Input\\Input',
			$this->instance->input,
			'Input property wrong type'
		);

		$this->assertInstanceOf(
			'Joomla\Registry\Registry',
			TestHelper::getValue($this->instance, 'config'),
			'Config property wrong type'
		);

		$this->assertInstanceOf(
			'Joomla\\Application\\Web\\WebClient',
			$this->instance->client,
			'Client property wrong type'
		);

		// TODO Test that configuration data loaded.

		$this->assertThat(
			$this->instance->get('execution.datetime'),
			$this->greaterThan('2001'),
			'Tests execution.datetime was set.'
		);

		$this->assertThat(
			$this->instance->get('execution.timestamp'),
			$this->greaterThan(1),
			'Tests execution.timestamp was set.'
		);

		$this->assertThat(
			$this->instance->get('uri.base.host'),
			$this->equalTo('http://' . self::TEST_HTTP_HOST),
			'Tests uri base host setting.'
		);
	}

	/**
	 * Tests the Joomla\Application\AbstractWebApplication::__construct method with dependancy injection.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function test__constructDependancyInjection()
	{
		$mockInput = $this->getMock('Joomla\\Input\\Input', array('test'), array(), '', false);
		$mockInput
			->expects($this->any())
			->method('test')
			->will(
			$this->returnValue('ok')
		);

		$mockConfig = $this->getMock('Joomla\Registry\Registry', array('test'), array(null), '', true);
		$mockConfig
			->expects($this->any())
			->method('test')
			->will(
			$this->returnValue('ok')
		);

		$mockClient = $this->getMock('Joomla\\Application\\Web\\WebClient', array('test'), array(), '', false);
		$mockClient
			->expects($this->any())
			->method('test')
			->will(
			$this->returnValue('ok')
		);

		$inspector = new ConcreteWeb($mockInput, $mockConfig, $mockClient);

		$this->assertThat(
			$inspector->input->test(),
			$this->equalTo('ok'),
			'Tests input injection.'
		);

		$this->assertThat(
			TestHelper::getValue($inspector, 'config')->test(),
			$this->equalTo('ok'),
			'Tests config injection.'
		);

		$this->assertThat(
			$inspector->client->test(),
			$this->equalTo('ok'),
			'Tests client injection.'
		);
	}

	/**
	 * Tests the Joomla\Application\AbstractWebApplication::allowCache method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testAllowCache()
	{
		$this->assertThat(
			$this->instance->allowCache(),
			$this->isFalse(),
			'Return value of allowCache should be false by default.'
		);

		$this->assertThat(
			$this->instance->allowCache(true),
			$this->isTrue(),
			'Return value of allowCache should return the new state.'
		);

		$this->assertThat(
			TestHelper::getValue($this->instance, 'response')->cachable,
			$this->isTrue(),
			'Checks the internal cache property has been set.'
		);
	}

	/**
	 * Tests the Joomla\Application\AbstractWebApplication::appendBody method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testAppendBody()
	{
		// Similulate a previous call to setBody or appendBody.
		TestHelper::getValue($this->instance, 'response')->body = array('foo');

		$this->instance->appendBody('bar');

		$this->assertThat(
			TestHelper::getValue($this->instance, 'response')->body,
			$this->equalTo(
				array('foo', 'bar')
			),
			'Checks the body array has been appended.'
		);

		$this->instance->appendBody(true);

		$this->assertThat(
			TestHelper::getValue($this->instance, 'response')->body,
			$this->equalTo(
				array('foo', 'bar', '1')
			),
			'Checks that non-strings are converted to strings.'
		);
	}

	/**
	 * Tests the Joomla\Application\AbstractWebApplication::clearHeaders method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testClearHeaders()
	{
		// Fill the header array with an arbitrary value.
		TestHelper::setValue(
			$this->instance,
			'response',
			(object) array(
				'cachable' => null,
				'headers' => array('foo'),
				'body' => array(),
			)
		);

		$this->instance->clearHeaders();

		$this->assertEquals(
			array(),
			TestHelper::getValue($this->instance, 'response')->headers,
			'Checks the headers were cleared.'
		);
	}

	/**
	 * Tests the Joomla\Application\AbstractWebApplication::close method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testClose()
	{
		// Make sure the application is not already closed.
		$this->assertSame(
			$this->instance->closed,
			null,
			'Checks the application doesn\'t start closed.'
		);

		$this->instance->close(3);

		// Make sure the application is closed with code 3.
		$this->assertSame(
			$this->instance->closed,
			3,
			'Checks the application was closed with exit code 3.'
		);
	}

	/**
	 * Tests the Joomla\Application\AbstractWebApplication::compress method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testCompressWithGzipEncoding()
	{
		// Fill the header body with a value.
		TestHelper::setValue(
			$this->instance,
			'response',
			(object) array(
				'cachable' => null,
				'headers' => null,
				'body' => array('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do
					eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim
					veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
					consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum
					dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident,
					sunt in culpa qui officia deserunt mollit anim id est laborum.'),
			)
		);

		// Load the client encoding with a value.
		TestHelper::setValue(
			$this->instance,
			'client',
			(object) array(
				'encodings' => array('gzip', 'deflate'),
			)
		);

		TestHelper::invoke($this->instance, 'compress');

		// Ensure that the compressed body is shorter than the raw body.
		$this->assertThat(
			strlen($this->instance->getBody()),
			$this->lessThan(471),
			'Checks the compressed output is smaller than the uncompressed output.'
		);

		// Ensure that the compression headers were set.
		$this->assertThat(
			TestHelper::getValue($this->instance, 'response')->headers,
			$this->equalTo(
				array(
					0 => array('name' => 'Content-Encoding', 'value' => 'gzip'),
					1 => array('name' => 'X-Content-Encoded-By', 'value' => 'Joomla')
				)
			),
			'Checks the headers were set correctly.'
		);
	}

	/**
	 * Tests the Joomla\Application\AbstractWebApplication::compress method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testCompressWithDeflateEncoding()
	{
		// Fill the header body with a value.
		TestHelper::setValue(
			$this->instance,
			'response',
			(object) array(
				'cachable' => null,
				'headers' => null,
				'body' => array('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do
					eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim
					veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
					consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum
					dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident,
					sunt in culpa qui officia deserunt mollit anim id est laborum.'),
			)
		);

		// Load the client encoding with a value.
		TestHelper::setValue(
			$this->instance,
			'client',
			(object) array(
				'encodings' => array('deflate', 'gzip'),
			)
		);

		TestHelper::invoke($this->instance, 'compress');

		// Ensure that the compressed body is shorter than the raw body.
		$this->assertThat(
			strlen($this->instance->getBody()),
			$this->lessThan(471),
			'Checks the compressed output is smaller than the uncompressed output.'
		);

		// Ensure that the compression headers were set.
		$this->assertThat(
			TestHelper::getValue($this->instance, 'response')->headers,
			$this->equalTo(
				array(
					0 => array('name' => 'Content-Encoding', 'value' => 'deflate'),
					1 => array('name' => 'X-Content-Encoded-By', 'value' => 'Joomla')
				)
			),
			'Checks the headers were set correctly.'
		);
	}

	/**
	 * Tests the Joomla\Application\AbstractWebApplication::compress method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testCompressWithNoAcceptEncodings()
	{
		$string = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do
					eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim
					veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
					consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum
					dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident,
					sunt in culpa qui officia deserunt mollit anim id est laborum.';

		// Replace \r\n -> \n to ensure same length on all platforms
		// Fill the header body with a value.
		TestHelper::setValue(
			$this->instance,
			'response',
			(object) array(
				'cachable' => null,
				'headers' => null,
				'body' => array(str_replace("\r\n", "\n", $string)),
			)
		);

		// Load the client encoding with a value.
		TestHelper::setValue(
			$this->instance,
			'client',
			(object) array(
				'encodings' => array(),
			)
		);

		TestHelper::invoke($this->instance, 'compress');

		// Ensure that the compressed body is the same as the raw body since there is no compression.
		$this->assertThat(
			strlen($this->instance->getBody()),
			$this->equalTo(471),
			'Checks the compressed output is the same as the uncompressed output -- no compression.'
		);

		// Ensure that the compression headers were not set.
		$this->assertThat(
			TestHelper::getValue($this->instance, 'response')->headers,
			$this->equalTo(null),
			'Checks the headers were set correctly.'
		);
	}

	/**
	 * Tests the Joomla\Application\AbstractWebApplication::compress method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testCompressWithHeadersSent()
	{
		$string = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do
					eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim
					veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
					consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum
					dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident,
					sunt in culpa qui officia deserunt mollit anim id est laborum.';

		// Replace \r\n -> \n to ensure same length on all platforms
		// Fill the header body with a value.
		TestHelper::setValue(
			$this->instance,
			'response',
			(object) array(
				'cachable' => null,
				'headers' => null,
				'body' => array(str_replace("\r\n", "\n", $string)),
			)
		);

		// Load the client encoding with a value.
		TestHelper::setValue(
			$this->instance,
			'client',
			(object) array(
				'encodings' => array('gzip', 'deflate'),
			)
		);

		// Set the headers sent flag to true.
		$this->instance->headersSent = true;

		TestHelper::invoke($this->instance, 'compress');

		// Set the headers sent flag back to false.
		$this->instance->headersSent = false;

		// Ensure that the compressed body is the same as the raw body since there is no compression.
		$this->assertThat(
			strlen($this->instance->getBody()),
			$this->equalTo(471),
			'Checks the compressed output is the same as the uncompressed output -- no compression.'
		);

		// Ensure that the compression headers were not set.
		$this->assertThat(
			TestHelper::getValue($this->instance, 'response')->headers,
			$this->equalTo(null),
			'Checks the headers were set correctly.'
		);
	}

	/**
	 * Tests the Joomla\Application\AbstractWebApplication::compress method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testCompressWithUnsupportedEncodings()
	{
		$string = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do
					eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim
					veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
					consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum
					dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident,
					sunt in culpa qui officia deserunt mollit anim id est laborum.';

		// Replace \r\n -> \n to ensure same length on all platforms
		// Fill the header body with a value.
		TestHelper::setValue(
			$this->instance,
			'response',
			(object) array(
				'cachable' => null,
				'headers' => null,
				'body' => array(str_replace("\r\n", "\n", $string)),
			)
		);

		// Load the client encoding with a value.
		TestHelper::setValue(
			$this->instance,
			'client',
			(object) array(
				'encodings' => array('foo', 'bar'),
			)
		);

		TestHelper::invoke($this->instance, 'compress');

		// Ensure that the compressed body is the same as the raw body since there is no supported compression.
		$this->assertThat(
			strlen($this->instance->getBody()),
			$this->equalTo(471),
			'Checks the compressed output is the same as the uncompressed output -- no supported compression.'
		);

		// Ensure that the compression headers were not set.
		$this->assertThat(
			TestHelper::getValue($this->instance, 'response')->headers,
			$this->equalTo(null),
			'Checks the headers were set correctly.'
		);
	}

	/**
	 * Tests the Joomla\Application\AbstractWebApplication::detectRequestUri method.
	 *
	 * @param   string  $https        @todo
	 * @param   string  $phpSelf      @todo
	 * @param   string  $requestUri   @todo
	 * @param   string  $httpHost     @todo
	 * @param   string  $scriptName   @todo
	 * @param   string  $queryString  @todo
	 * @param   string  $expects      @todo
	 *
	 * @return  void
	 *
	 * @dataProvider getDetectRequestUriData
	 * @since   1.0
	 */
	public function testDetectRequestUri($https, $phpSelf, $requestUri, $httpHost, $scriptName, $queryString, $expects)
	{
		if ($https !== null)
		{
			$_SERVER['HTTPS'] = $https;
		}

		$_SERVER['PHP_SELF'] = $phpSelf;
		$_SERVER['REQUEST_URI'] = $requestUri;
		$_SERVER['HTTP_HOST'] = $httpHost;
		$_SERVER['SCRIPT_NAME'] = $scriptName;
		$_SERVER['QUERY_STRING'] = $queryString;

		$this->assertThat(
			TestHelper::invoke($this->instance, 'detectRequestUri'),
			$this->equalTo($expects)
		);
	}

	/**
	 * Test the execute method
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Application\AbstractWebApplication::execute
	 * @since   1.0
	 */
	public function testExecute()
	{
		$this->instance->doExecute = false;
		$this->instance->headers = array();

		// Check doExecute was fired.
		$this->instance->execute();
		$this->assertTrue($this->instance->doExecute);

		// Check the respond method was called.
		$this->assertContains('Content-Type: text/html; charset=utf-8', $this->instance->headers[0]);

		// @todo Check compress
	}

	/**
	 * Tests the Joomla\Application\AbstractWebApplication::getBody method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testGetBody()
	{
		// Fill the header body with an arbitrary value.
		TestHelper::setValue(
			$this->instance,
			'response',
			(object) array(
				'cachable' => null,
				'headers' => null,
				'body' => array('foo', 'bar'),
			)
		);

		$this->assertThat(
			$this->instance->getBody(),
			$this->equalTo('foobar'),
			'Checks the default state returns the body as a string.'
		);

		$this->assertThat(
			$this->instance->getBody(),
			$this->equalTo($this->instance->getBody(false)),
			'Checks the default state is $asArray = false.'
		);

		$this->assertThat(
			$this->instance->getBody(true),
			$this->equalTo(array('foo', 'bar')),
			'Checks that the body is returned as an array.'
		);
	}

	/**
	 * Tests the Joomla\Application\AbstractWebApplication::getHeaders method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testGetHeaders()
	{
		// Fill the header body with an arbitrary value.
		TestHelper::setValue(
			$this->instance,
			'response',
			(object) array(
				'cachable' => null,
				'headers' => array('ok'),
				'body' => null,
			)
		);

		$this->assertThat(
			$this->instance->getHeaders(),
			$this->equalTo(array('ok')),
			'Checks the headers part of the response is returned correctly.'
		);
	}

	/**
	 * Tests the Joomla\Application\AbstractWebApplication::loadSystemUris method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testLoadSystemUrisWithSiteUriSet()
	{
		// Set the site_uri value in the configuration.
		$config = new Registry(array('site_uri' => 'http://test.joomla.org/path/'));
		TestHelper::setValue($this->instance, 'config', $config);

		TestHelper::invoke($this->instance, 'loadSystemUris');

		$this->assertThat(
			TestHelper::getValue($this->instance, 'config')->get('uri.base.full'),
			$this->equalTo('http://test.joomla.org/path/'),
			'Checks the full base uri.'
		);

		$this->assertThat(
			TestHelper::getValue($this->instance, 'config')->get('uri.base.host'),
			$this->equalTo('http://test.joomla.org'),
			'Checks the base uri host.'
		);

		$this->assertThat(
			TestHelper::getValue($this->instance, 'config')->get('uri.base.path'),
			$this->equalTo('/path/'),
			'Checks the base uri path.'
		);

		$this->assertThat(
			TestHelper::getValue($this->instance, 'config')->get('uri.media.full'),
			$this->equalTo('http://test.joomla.org/path/media/'),
			'Checks the full media uri.'
		);

		$this->assertThat(
			TestHelper::getValue($this->instance, 'config')->get('uri.media.path'),
			$this->equalTo('/path/media/'),
			'Checks the media uri path.'
		);
	}

	/**
	 * Tests the Joomla\Application\AbstractWebApplication::loadSystemUris method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testLoadSystemUrisWithoutSiteUriSet()
	{
		TestHelper::invoke($this->instance, 'loadSystemUris', 'http://joom.la/application');

		$this->assertThat(
			TestHelper::getValue($this->instance, 'config')->get('uri.base.full'),
			$this->equalTo('http://joom.la/'),
			'Checks the full base uri.'
		);

		$this->assertThat(
			TestHelper::getValue($this->instance, 'config')->get('uri.base.host'),
			$this->equalTo('http://joom.la'),
			'Checks the base uri host.'
		);

		$this->assertThat(
			TestHelper::getValue($this->instance, 'config')->get('uri.base.path'),
			$this->equalTo('/'),
			'Checks the base uri path.'
		);

		$this->assertThat(
			TestHelper::getValue($this->instance, 'config')->get('uri.media.full'),
			$this->equalTo('http://joom.la/media/'),
			'Checks the full media uri.'
		);

		$this->assertThat(
			TestHelper::getValue($this->instance, 'config')->get('uri.media.path'),
			$this->equalTo('/media/'),
			'Checks the media uri path.'
		);
	}

	/**
	 * Tests the Joomla\Application\AbstractWebApplication::loadSystemUris method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testLoadSystemUrisWithoutSiteUriWithMediaUriSet()
	{
		// Set the media_uri value in the configuration.
		$config = new Registry(array('media_uri' => 'http://cdn.joomla.org/media/'));
		TestHelper::setValue($this->instance, 'config', $config);

		TestHelper::invoke($this->instance, 'loadSystemUris', 'http://joom.la/application');

		$this->assertThat(
			TestHelper::getValue($this->instance, 'config')->get('uri.base.full'),
			$this->equalTo('http://joom.la/'),
			'Checks the full base uri.'
		);

		$this->assertThat(
			TestHelper::getValue($this->instance, 'config')->get('uri.base.host'),
			$this->equalTo('http://joom.la'),
			'Checks the base uri host.'
		);

		$this->assertThat(
			TestHelper::getValue($this->instance, 'config')->get('uri.base.path'),
			$this->equalTo('/'),
			'Checks the base uri path.'
		);

		$this->assertThat(
			TestHelper::getValue($this->instance, 'config')->get('uri.media.full'),
			$this->equalTo('http://cdn.joomla.org/media/'),
			'Checks the full media uri.'
		);

		// Since this is on a different domain we need the full url for this too.
		$this->assertThat(
			TestHelper::getValue($this->instance, 'config')->get('uri.media.path'),
			$this->equalTo('http://cdn.joomla.org/media/'),
			'Checks the media uri path.'
		);
	}

	/**
	 * Tests the Joomla\Application\AbstractWebApplication::loadSystemUris method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testLoadSystemUrisWithoutSiteUriWithRelativeMediaUriSet()
	{
		// Set the media_uri value in the configuration.
		$config = new Registry(array('media_uri' => '/media/'));
		TestHelper::setValue($this->instance, 'config', $config);

		TestHelper::invoke($this->instance, 'loadSystemUris', 'http://joom.la/application');

		$this->assertThat(
			TestHelper::getValue($this->instance, 'config')->get('uri.base.full'),
			$this->equalTo('http://joom.la/'),
			'Checks the full base uri.'
		);

		$this->assertThat(
			TestHelper::getValue($this->instance, 'config')->get('uri.base.host'),
			$this->equalTo('http://joom.la'),
			'Checks the base uri host.'
		);

		$this->assertThat(
			TestHelper::getValue($this->instance, 'config')->get('uri.base.path'),
			$this->equalTo('/'),
			'Checks the base uri path.'
		);

		$this->assertThat(
			TestHelper::getValue($this->instance, 'config')->get('uri.media.full'),
			$this->equalTo('http://joom.la/media/'),
			'Checks the full media uri.'
		);

		// Since this is on a different domain we need the full url for this too.
		$this->assertThat(
			TestHelper::getValue($this->instance, 'config')->get('uri.media.path'),
			$this->equalTo('/media/'),
			'Checks the media uri path.'
		);
	}

	/**
	 * Tests the Joomla\Application\AbstractWebApplication::prependBody method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testPrependBody()
	{
		// Similulate a previous call to a body method.
		TestHelper::getValue($this->instance, 'response')->body = array('foo');

		$this->instance->prependBody('bar');

		$this->assertThat(
			TestHelper::getValue($this->instance, 'response')->body,
			$this->equalTo(
				array('bar', 'foo')
			),
			'Checks the body array has been prepended.'
		);

		$this->instance->prependBody(true);

		$this->assertThat(
			TestHelper::getValue($this->instance, 'response')->body,
			$this->equalTo(
				array('1', 'bar', 'foo')
			),
			'Checks that non-strings are converted to strings.'
		);
	}

	/**
	 * Tests the Joomla\Application\AbstractWebApplication::redirect method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testRedirect()
	{
		$base = 'http://j.org/';
		$url = 'index.php';

		// Inject the client information.
		TestHelper::setValue(
			$this->instance,
			'client',
			(object) array(
				'engine' => WebClient::GECKO,
			)
		);

		// Inject the internal configuration.
		$config = new Registry;
		$config->set('uri.base.full', $base);

		TestHelper::setValue($this->instance, 'config', $config);

		$this->instance->redirect($url, false);

		$this->assertThat(
			$this->instance->headers,
			$this->equalTo(
				array(
					array('HTTP/1.1 303 See other', true, null),
					array('Location: ' . $base . $url, true, null),
					array('Content-Type: text/html; charset=utf-8', true, null),
				)
			)
		);
	}

	/**
	 * Tests the Joomla\Application\AbstractWebApplication::redirect method with headers already sent.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testRedirectWithHeadersSent()
	{
		$base = 'http://j.org/';
		$url = 'index.php';

		// Emulate headers already sent.
		$this->instance->headersSent = true;

		// Inject the internal configuration.
		$config = new Registry;
		$config->set('uri.base.full', $base);

		TestHelper::setValue($this->instance, 'config', $config);

		// Capture the output for this test.
		ob_start();
		$this->instance->redirect('index.php');
		$buffer = ob_get_contents();
		ob_end_clean();

		$this->assertThat(
			$buffer,
			$this->equalTo("<script>document.location.href='{$base}{$url}';</script>\n")
		);
	}

	/**
	 * Tests the Joomla\Application\AbstractWebApplication::redirect method with headers already sent.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testRedirectWithJavascriptRedirect()
	{
		$url = 'http://j.org/index.php?phi=Φ';

		// Inject the client information.
		TestHelper::setValue(
			$this->instance,
			'client',
			(object) array(
				'engine' => WebClient::TRIDENT,
			)
		);

		// Capture the output for this test.
		ob_start();
		$this->instance->redirect($url);
		$buffer = ob_get_contents();
		ob_end_clean();

		$this->assertThat(
			trim($buffer),
			$this->equalTo(
				'<html><head>'
					. '<meta http-equiv="content-type" content="text/html; charset=utf-8" />'
					. "<script>document.location.href='{$url}';</script>"
					. '</head><body></body></html>'
			)
		);
	}

	/**
	 * Tests the Joomla\Application\AbstractWebApplication::redirect method with moved option.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testRedirectWithMoved()
	{
		$url = 'http://j.org/index.php';

		// Inject the client information.
		TestHelper::setValue(
			$this->instance,
			'client',
			(object) array(
				'engine' => WebClient::GECKO,
			)
		);

		$this->instance->redirect($url, true);

		$this->assertThat(
			$this->instance->headers,
			$this->equalTo(
				array(
					array('HTTP/1.1 301 Moved Permanently', true, null),
					array('Location: ' . $url, true, null),
					array('Content-Type: text/html; charset=utf-8', true, null),
				)
			)
		);
	}

	/**
	 * Tests the Joomla\Application\AbstractWebApplication::redirect method with assorted URL's.
	 *
	 * @param   string  $url       @todo
	 * @param   string  $base      @todo
	 * @param   string  $request   @todo
	 * @param   string  $expected  @todo
	 *
	 * @return  void
	 *
	 * @dataProvider  getRedirectData
	 * @since   1.0
	 */
	public function testRedirectWithUrl($url, $base, $request, $expected)
	{
		// Inject the client information.
		TestHelper::setValue(
			$this->instance,
			'client',
			(object) array(
				'engine' => WebClient::GECKO,
			)
		);

		// Inject the internal configuration.
		$config = new Registry;
		$config->set('uri.base.full', $base);
		$config->set('uri.request', $request);

		TestHelper::setValue($this->instance, 'config', $config);

		$this->instance->redirect($url, false);

		$this->assertThat(
			$this->instance->headers[1][0],
			$this->equalTo('Location: ' . $expected)
		);
	}

	/**
	 * Tests the Joomla\Application\AbstractWebApplication::respond method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testRespond()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the Joomla\Application\AbstractWebApplication::sendHeaders method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testSendHeaders()
	{
		// Similulate a previous call to a setHeader method.
		TestHelper::getValue($this->instance, 'response')->headers = array(
			array('name' => 'Status', 'value' => 200),
			array('name' => 'X-JWeb-SendHeaders', 'value' => 'foo'),
		);

		$this->assertThat(
			$this->instance->sendHeaders(),
			$this->identicalTo($this->instance),
			'Check chaining.'
		);

		$this->assertThat(
			$this->instance->headers,
			$this->equalTo(
				array(
					array('Status: 200', null, 200),
					array('X-JWeb-SendHeaders: foo', true, null),
				)
			)
		);
	}

	/**
	 * Tests the Joomla\Application\AbstractWebApplication::setBody method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testSetBody()
	{
		$this->instance->setBody('foo');

		$this->assertThat(
			TestHelper::getValue($this->instance, 'response')->body,
			$this->equalTo(
				array('foo')
			),
			'Checks the body array has been reset.'
		);

		$this->instance->setBody(true);

		$this->assertThat(
			TestHelper::getValue($this->instance, 'response')->body,
			$this->equalTo(
				array('1')
			),
			'Checks reset and that non-strings are converted to strings.'
		);
	}

	/**
	 * Tests the Joomla\Application\AbstractWebApplication::setHeader method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testSetHeader()
	{
		// Fill the header body with an arbitrary value.
		TestHelper::setValue(
			$this->instance,
			'response',
			(object) array(
				'cachable' => null,
				'headers' => array(
					array('name' => 'foo', 'value' => 'bar'),
				),
				'body' => null,
			)
		);

		$this->instance->setHeader('foo', 'car');
		$this->assertThat(
			TestHelper::getValue($this->instance, 'response')->headers,
			$this->equalTo(
				array(
					array('name' => 'foo', 'value' => 'bar'),
					array('name' => 'foo', 'value' => 'car')
				)
			),
			'Tests that a header is added.'
		);

		$this->instance->setHeader('foo', 'car', true);
		$this->assertThat(
			TestHelper::getValue($this->instance, 'response')->headers,
			$this->equalTo(
				array(
					array('name' => 'foo', 'value' => 'car')
				)
			),
			'Tests that headers of the same name are replaced.'
		);
	}

	/**
	 * Tests the setSession method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Application\AbstractWebApplication::getSession
	 * @covers  Joomla\Application\AbstractWebApplication::setSession
	 * @since   1.0
	 */
	public function testSetSession()
	{
		$mockSession = $this->getMock('Joomla\Session\Session', array('test'), array(), '', false);

		$this->assertSame($this->instance, $this->instance->setSession($mockSession), 'Checks chainging.');
		$this->assertNull($this->instance->getSession()->test(), 'Checks the session was set with the new object.');
	}

	/**
	 * Test...
	 *
	 * @covers Joomla\Application\AbstractWebApplication::isSSLConnection
	 *
	 * @return void
	 */
	public function testIsSSLConnection()
	{
		unset($_SERVER['HTTPS']);

		$this->assertThat(
			$this->instance->isSSLConnection(),
			$this->equalTo(false)
		);

		$_SERVER['HTTPS'] = 'on';

		$this->assertThat(
			$this->instance->isSSLConnection(),
			$this->equalTo(true)
		);
	}

	/**
	 * Test getFormToken
	 *
	 * @covers  Joomla\Application\AbstractWebApplication::getFormToken
	 *
	 * @return void
	 */
	public function testGetFormToken()
	{
		$mockSession = $this->getMock('Joomla\\Session\\Session');

		$this->instance->setSession($mockSession);
		$this->instance->set('secret', 'abc');
		$expected = md5('abc' . 0 . $this->instance->getSession()->getToken());
		$this->assertEquals(
			$expected,
			$this->instance->getFormToken(),
			'Form token should be calculated as above.'
		);
	}

	/**
	 * Setup for testing.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function setUp()
	{
		$_SERVER['HTTP_HOST'] = self::TEST_HTTP_HOST;
		$_SERVER['HTTP_USER_AGENT'] = self::TEST_USER_AGENT;
		$_SERVER['REQUEST_URI'] = self::TEST_REQUEST_URI;
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		// Get a new ConcreteWeb instance.
		$this->instance = new ConcreteWeb;
	}
}
