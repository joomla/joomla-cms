<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  OAuth
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/oauth/v1aclient.php';
include_once __DIR__ . '/stubs/JOauthV1aclientInspector.php';

/**
 * Test class for JOauth1aClient.
 *
 * @package     Joomla.UnitTest
 * @subpackage  OAuth
 * @since       12.2
 */
class JOauthV1aclientTest extends TestCase
{
	/**
	 * @var    Input  input for the Oauth object.
	 * @since  12.2
	 */
	protected $input;

	/**
	 * @var    JRegistry  Options for the Oauth object.
	 * @since  12.2
	 */
	protected $options;

	/**
	 * @var    JHttp  Mock http object.
	 * @since  12.2
	 */
	protected $client;

	/**
	 * An instance of the object to test.
	 *
	 * @var    JOauth1aClientInspector
	 * @since  11.3
	 */
	protected $class;

	/**
	 * @var    string  Sample JSON string.
	 * @since  12.2
	 */
	protected $sampleString = '{"a":1,"b":2,"c":3,"d":4,"e":5}';

	/**
	 * @var    string  Sample JSON error message.
	 * @since  12.2
	 */
	protected $errorString = '{"errorCode":401, "message": "Generic error"}';

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$key = "TEST_KEY";
		$secret = "TEST_SECRET";
		$my_url = "TEST_URL";

		$this->options = new JRegistry;
		$this->client = $this->getMock('JHttp', array('get', 'post', 'delete', 'put'));
		$this->input = new JInput;

		$this->options->set('consumer_key', $key);
		$this->options->set('consumer_secret', $secret);
		$this->options->set('callback', $my_url);
		$this->object = new JOauthV1aclientInspector($this->options, $this->client, $this->input);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
		JFactory::$session = null;
	}

	/**
	* Provides test data.
	*
	* @return array
	*
	* @since 12.2
	*/
	public function seedAuth()
	{
		// Token and fail
		return array(
			array(array('key' => 'valid', 'secret' => 'valid'), false),
			array(null, false),
			array(null, true)
			);
	}

	/**
	 * Tests the auth method
	 *
	 * @return  void
	 *
	 * @dataProvider seedAuth
	 * @since   12.2
	 */
	public function testAuth($token, $fail)
	{
		// Already got some credentials stored?
		if (!is_null($token))
		{
			$this->object->setToken($token);
			$result = $this->object->auth();
			$this->assertEquals($result, $token);
		}
		else
		{
			$this->object->setOption('requestTokenURL', 'https://example.com/request_token');
			$this->object->setOption('authoriseURL', 'https://example.com/authorize');
			$this->object->setOption('accessTokenURL', 'https://example.com/access_token');

			// Request token.
			$returnData = new stdClass;
			$returnData->code = 200;
			$returnData->body = 'oauth_token=token&oauth_token_secret=secret&oauth_callback_confirmed=true';

			$this->client->expects($this->at(0))
				->method('post')
				->with($this->object->getOption('requestTokenURL'))
				->will($this->returnValue($returnData));

			$input = TestReflection::getValue($this->object, 'input');
			$input->set('oauth_verifier', null);
			TestReflection::setValue($this->object, 'input', $input);

			$this->object->auth();

			$token = $this->object->getToken();
			$this->assertEquals($token['key'], 'token');
			$this->assertEquals($token['secret'], 'secret');

			// Access token.
			$input = TestReflection::getValue($this->object, 'input');
			$data = array('oauth_verifier' => 'verifier', 'oauth_token' => 'token');
			TestReflection::setValue($input, 'data', $data);

			// Get mock session
			$mockSession = $this->getMock('JSession', array( '_start', 'get'));

			if ($fail)
			{
				$mockSession->expects($this->at(0))
							->method('get')
							->with('key', null, 'oauth_token')
							->will($this->returnValue('bad'));

				$mockSession->expects($this->at(1))
							->method('get')
							->with('secret', null, 'oauth_token')
							->will($this->returnValue('session'));

	    		JFactory::$session = $mockSession;

				$this->setExpectedException('DomainException');
				$result = $this->object->auth();
			}

    		$mockSession->expects($this->at(0))
    					->method('get')
    					->with('key', null, 'oauth_token')
    					->will($this->returnValue('token'));

    		$mockSession->expects($this->at(1))
    					->method('get')
    					->with('secret', null, 'oauth_token')
    					->will($this->returnValue('secret'));

    		JFactory::$session = $mockSession;

			$returnData = new stdClass;
			$returnData->code = 200;
			$returnData->body = 'oauth_token=token_key&oauth_token_secret=token_secret';

			$this->client->expects($this->at(0))
				->method('post')
				->with($this->object->getOption('accessTokenURL'))
				->will($this->returnValue($returnData));

			$result = $this->object->auth();

			$this->assertEquals($result['key'], 'token_key');
			$this->assertEquals($result['secret'], 'token_secret');
		}
	}
}
