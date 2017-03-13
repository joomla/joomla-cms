<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JGoogle.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Google
 * @since       12.3
 */
class JGoogleTest extends TestCase
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
	 * @var    JGoogleAuth  The authentication wrapper for sending requests to Google.
	 */
	protected $auth;

	/**
	 * @var    JGoogle  Object under test.
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
		$this->object = new JGoogle($this->options, $this->auth);
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
	 * Tests the magic __get method - data
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function test__GetData()
	{
		$this->options->set('clientid', '1075367716947.apps.googleusercontent.com');
		$this->options->set('redirecturi', 'http://j.aaronschmitz.com/web/calendar-test');
		$this->assertThat(
			$this->object->data('Plus'),
			$this->isInstanceOf('JGoogleDataPlus')
		);
		$this->assertThat(
			$this->object->data('Picasa'),
			$this->isInstanceOf('JGoogleDataPicasa')
		);
		$this->assertThat(
			$this->object->data('Adsense'),
			$this->isInstanceOf('JGoogleDataAdsense')
		);
		$this->assertThat(
			$this->object->data('Calendar'),
			$this->isInstanceOf('JGoogleDataCalendar')
		);
		$this->assertNull($this->object->data('NotAClass'));
	}

	/**
	 * Tests the magic __get method - embed
	 *
	 * @group	JGoogle
	 * @return void
	 */
	public function test__GetEmbed()
	{
		$this->assertThat(
			$this->object->embed('Maps'),
			$this->isInstanceOf('JGoogleEmbedMaps')
		);
		$this->assertThat(
			$this->object->embed('Analytics'),
			$this->isInstanceOf('JGoogleEmbedAnalytics')
		);
		$this->assertNull($this->object->embed('NotAClass'));
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
}
