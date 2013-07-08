<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  OAuth1
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JOAuth1Message.
 *
 * @package     Joomla.UnitTest
 * @subpackage  JOAuth1
 *
 * @since       12.3
 */
class JOAuth1MessageTest extends TestCase
{
	/**
	 * The test object.
	 * @var JOAuth1Message
	 */
	protected $object;

	/**
	 * The nonce object.
	 * @var JOAuth1Nonce
	 */
	protected $nonce;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->nonce = $this->getMockBuilder('JOAuth1Nonce')
			->disableOriginalConstructor()
			->getMock();

		$this->object = new JOAuth1Message(null, $this->nonce);
	}

	/**
	 * Tests the get method.
	 *
	 * @return void
	 *
	 * @since  12.3
	 */
	public function test__get()
	{
		TestReflection::setValue($this->object, '_parameters', array('oauth_token' => 'MYTOKEN'));

		$this->assertEquals('MYTOKEN', $this->object->token);
	}

	/**
	 * Tests the set method.
	 *
	 * @return void
	 *
	 * @since  12.3
	 */
	public function test__set()
	{
		$this->object->token = 'MYSETTOKEN';

		$this->assertEquals(array('oauth_token' => 'MYSETTOKEN'), TestReflection::getValue($this->object, '_parameters'));
	}

	/**
	 * Tests the bind method.
	 *
	 * @return void
	 *
	 * @since  12.3
	 */
	public function testBind()
	{
		$this->object->bind(
			array(
				'oauth_token' => 'MY_BIND_TOKEN',
				'oauth_signature' => 'MY_BIND_SIGNATURE',
				'oauth_signature_method' => 'MY_BIND_SIGNATURE_METHOD',
				'oauth_verifier' => 'MY_BIND_VERIFIER',
				'oauth_authoriser' => 'MY_BIND_AUTHORISER'
			)
		);

		$this->assertEquals(
			array(
				'oauth_token' => 'MY_BIND_TOKEN',
				'oauth_signature' => 'MY_BIND_SIGNATURE',
				'oauth_signature_method' => 'MY_BIND_SIGNATURE_METHOD',
				'oauth_verifier' => 'MY_BIND_VERIFIER'
			),
			TestReflection::getValue($this->object, '_parameters')
		);
	}

	/**
	 * Data provider for isValid and sign methods.
	 *
	 * @return  array  Array of provided data.
	 *
	 * @since  12.3
	 */
	public function dataIsValid()
	{
		return array(
			'Valid Signature, valid Nonce' =>	array(
				array(
					'oauth_consumer_key' => '9djdj82h48djs9d2',
					'oauth_token' => 'kkk9d7dh3k39sjv7',
					'oauth_signature_method' => 'HMAC-SHA1',
					'oauth_timestamp' => '137131201',
					'oauth_nonce' => '23gasdga',
					'oauth_signature' => 'n82/12ljHlCH9ACjMeiBj0z8jXQ='
				),
				'http://example.com/request?b5=%3D%253D&a3=a&c%40=&a2=r%20b',
				'POST',
				'clientsecret',
				'credentialsecret',
				'n82/12ljHlCH9ACjMeiBj0z8jXQ=',
				true,	// Valid nonce.
				true
			),
			'Valid Signature, invalid Nonce' =>	array(
				array(
					'oauth_consumer_key' => '9djdj82h48djs9d2',
					'oauth_token' => 'kkk9d7dh3k39sjv7',
					'oauth_signature_method' => 'PLAINTEXT',
					'oauth_timestamp' => '137131201',
					'oauth_nonce' => '23gasdga',
					'oauth_signature' => 'clientsecret&credentialsecret'
				),
				'http://example.com/request?b5=%3D%253D&a3=a&c%40=&a2=r%20b',
				'POST',
				'clientsecret',
				'credentialsecret',
				'clientsecret&credentialsecret',
				false,	// Invalid nonce.
				false
			),
			'Invalid Signature, valid Nonce' =>	array(
				array(
					'oauth_consumer_key' => '9djdj82h48djs9d2',
					'oauth_token' => 'kkk9d7dh3k39sjv7',
					'oauth_signature_method' => 'HMAC-SHA1',
					'oauth_timestamp' => '137131201',
					'oauth_nonce' => '23gasdga',
					'oauth_signature' => 'bYT5CMsGcbgUdFHObYMEfcx6bsw%3D'
				),
				'http://example.com/request?b5=%3D%253D&a3=a&c%40=&a2=r%20b',
				'POST',
				'clientsecret',
				'credentialsecret',
				'n82/12ljHlCH9ACjMeiBj0z8jXQ=',
				true,	// Invalid nonce.
				false
			)
		);
	}

	/**
	 * Tests the isValid method.
	 *
	 * @param   array    $parameters        The message parameters.
	 * @param   string   $requestUrl        The request URL.
	 * @param   string   $requestMethod     The request method.
	 * @param   string   $clientSecret      The client key secret.
	 * @param   string   $credentialSecret  The token secret.
	 * @param   string   $signature         The calculated signature result.
	 * @param   boolean  $nonceValid        The nonce validation result.
	 * @param   boolean  $expected          The expected value.
	 *
	 * @return  void
	 *
	 * @since  12.3
	 *
	 * @dataProvider  dataIsValid
	 */
	public function testIsValid($parameters, $requestUrl, $requestMethod, $clientSecret, $credentialSecret, $signature, $nonceValid, $expected)
	{
		$object = $this->getMockBuilder('JOAuth1Message')
			->setConstructorArgs(array($parameters, $this->nonce))
			->setMethods(array('sign'))
			->getMock();

		$this->nonce->expects($this->once())
			->method('validate')
			->with($parameters['oauth_nonce'], $parameters['oauth_consumer_key'], $parameters['oauth_timestamp'], $parameters['oauth_token'])
			->will($this->returnValue($nonceValid));

		$object->expects($this->once())
			->method('sign')
			->with($requestUrl, $requestMethod, $clientSecret, $credentialSecret)
			->will($this->returnValue($signature));

		$this->assertEquals($expected, $object->isValid($requestUrl, $requestMethod, $clientSecret, $credentialSecret));
	}

	/**
	 * Tests the sign() method.
	 *
	 * @param   array    $parameters        The message parameters.
	 * @param   string   $requestUrl        The request URL.
	 * @param   string   $requestMethod     The request method.
	 * @param   string   $clientSecret      The client key secret.
	 * @param   string   $credentialSecret  The token secret.
	 * @param   string   $signature         The calculated signature result.
	 * @param   boolean  $nonceValid        The nonce validation result. Not used for this test.
	 * @param   boolean  $expected          The expected value for the isValid method. Not used for this test.
	 *
	 * @return  void
	 *
	 * @dataProvider  dataIsValid
	 *
	 * @since   12.3
	 */
	public function testSign($parameters, $requestUrl, $requestMethod, $clientSecret, $credentialSecret, $signature, $nonceValid, $expected)
	{
		$this->object = new JOAuth1Message($parameters, $this->nonce);

		$this->assertEquals($signature, $this->object->sign($requestUrl, $requestMethod, $clientSecret, $credentialSecret));
	}
}
