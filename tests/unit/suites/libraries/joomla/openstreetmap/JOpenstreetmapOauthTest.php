<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Openstreetmap
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JOpenstreetmapOauth.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Openstreetmap
 * @since       13.1
 */
class JOpenstreetmapOauthTest extends TestCase
{
	/**
	 * @var    JRegistry  Options for the Openstreetmap object.
	 * @since  13.1
	 */
	protected $options;

	/**
	 * @var    JHttp  Mock http object.
	 * @since  13.1
	 */
	protected $client;

	/**
	 * @var    JInput The input object to use in retrieving GET/POST data.
	 * @since  13.1
	 */
	protected $input;

	/**
	 * @var    JOpenstreetmapOauth  Authentication object for the Twitter object.
	 * @since  13.1
	 */
	protected $oauth;

	/**
	 * @var    string  Sample string.
	 * @since  13.1
	 */
	protected $sampleString = 'Test String';

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';
		$_SERVER['REQUEST_URI'] = '/index.php';
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		$key = "app_key";
		$secret = "app_secret";
		$my_url = "http://127.0.0.1/eclipse/joomla-platform/osm_test.php";

		$this->options = new JRegistry;
		$this->input = new JInput;
		$this->client = $this->getMock('JHttp', array('get', 'post', 'delete', 'put'));

		$this->options->set('consumer_key', $key);
		$this->options->set('consumer_secret', $secret);
		$this->options->set('callback', $my_url);
		$this->oauth = new JOpenstreetmapOauth($this->options, $this->client, $this->input);
		$this->oauth->setToken(array('key' => $key, 'secret' => $secret));
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
	}

	/**
	 * Provides test data for request format detection.
	 *
	 * @return array
	 *
	 * @since 13.1
	 */
	public function seedVerifyCredentials()
	{
		// Code, body, expected
		return array(
				array(200, $this->sampleString, true)
		);
	}

	/**
	 * Tests the verifyCredentials method
	 *
	 * @param   integer  $code      The return code.
	 * @param   string   $body      The JSON string.
	 * @param   boolean  $expected  Expected return value.
	 *
	 * @return  void
	 *
	 * @dataProvider seedVerifyCredentials
	 * @since   13.1
	 */
	public function testVerifyCredentials($code, $body, $expected)
	{

		$returnData = new stdClass;
		$returnData->code = $code;
		$returnData->body = $body;

		$this->assertThat(
				$this->oauth->verifyCredentials(),
				$this->equalTo($expected)
		);
	}
}
