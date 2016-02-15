<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JGoogleDataPlus.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Google
 * @since       12.3
 */
class JGoogleDataPlusTest extends TestCase
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
	 * @var    JGoogleDataPlus  Object under test.
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
		$this->object = new JGoogleDataPlus($this->options, $this->auth);

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
	 * Tests the magic __get method - people
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function test__GetPeople()
	{
		$this->assertThat(
			$this->object->people,
			$this->isInstanceOf('JGoogleDataPlusPeople')
		);
	}

	/**
	 * Tests the magic __get method - activities
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function test__GetActivities()
	{
		$this->assertThat(
			$this->object->activities,
			$this->isInstanceOf('JGoogleDataPlusActivities')
		);
	}

	/**
	 * Tests the magic __get method - comments
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function test__GetComments()
	{
		$this->assertThat(
			$this->object->comments,
			$this->isInstanceOf('JGoogleDataPlusComments')
		);
	}

	/**
	 * Tests the magic __get method - other (non existent)
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function test__GetOther()
	{
		$this->assertThat(
			$this->object->other,
			$this->isNull()
		);
	}
}
