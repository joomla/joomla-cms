<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Github
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JGithubAccount.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Github
 *
 * @since       11.1
 */
class JGithubPackageAuthorizationsTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JRegistry  Options for the GitHub object.
	 * @since  12.3
	 */
	protected $options;

	/**
	 * @var    JGithubHttp  Mock client object.
	 * @since  12.3
	 */
	protected $client;

	/**
	 * @var    JGithubPackageAuthorization  Object under test.
	 * @since  12.3
	 */
	protected $object;

	/**
	 * @var    string  Sample JSON string.
	 * @since  12.3
	 */
	protected $sampleString = '{"a":1,"b":2,"c":3,"d":4,"e":5}';

	/**
	 * @var    string  Sample JSON error message.
	 * @since  12.3
	 */
	protected $errorString = '{"message": "Generic Error"}';

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->options = new JRegistry;
		$this->client = $this->getMock('JGithubHttp', array('get', 'post', 'delete', 'patch', 'put'));

		$this->object = new JGithubPackageAuthorization($this->options, $this->client);
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		unset($this->options);
		unset($this->client);
		unset($this->object);
		parent::tearDown();
	}

	/**
	 * Tests the createAuthorisation method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testCreate()
	{
		$returnData = new stdClass;
		$returnData->code = 201;
		$returnData->body = $this->sampleString;

		$authorisation = new stdClass;
		$authorisation->scopes = array('public_repo');
		$authorisation->note = 'My test app';
		$authorisation->note_url = 'https://www.joomla.org';

		$this->client->expects($this->once())
			->method('post')
			->with('/authorizations', json_encode($authorisation))
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->create(array('public_repo'), 'My test app', 'https://www.joomla.org'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createAuthorisation method - simulated failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testCreateFailure()
	{
		$exception = false;

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$authorisation = new stdClass;
		$authorisation->scopes = array('public_repo');
		$authorisation->note = 'My test app';
		$authorisation->note_url = 'https://www.joomla.org';

		$this->client->expects($this->once())
			->method('post')
			->with('/authorizations', json_encode($authorisation))
			->will($this->returnValue($returnData));

		try
		{
			$this->object->create(array('public_repo'), 'My test app', 'https://www.joomla.org');
		}
		catch (DomainException $e)
		{
			$exception = true;

			$this->assertThat(
				$e->getMessage(),
				$this->equalTo(json_decode($this->errorString)->message)
			);
		}
		$this->assertTrue($exception);
	}

	/**
	 * Tests the deleteAuthorisation method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testDelete()
	{
		$returnData = new stdClass;
		$returnData->code = 204;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('delete')
			->with('/authorizations/42')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->delete(42),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the deleteAuthorisation method - simulated failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testDeleteFailure()
	{
		$exception = false;

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('delete')
			->with('/authorizations/42')
			->will($this->returnValue($returnData));

		try
		{
			$this->object->delete(42);
		}
		catch (DomainException $e)
		{
			$exception = true;

			$this->assertThat(
				$e->getMessage(),
				$this->equalTo(json_decode($this->errorString)->message)
			);
		}
		$this->assertTrue($exception);
	}

	/**
	 * Tests the editAuthorisation method - Add scopes
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testEditAddScopes()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$authorisation = new stdClass;
		$authorisation->add_scopes = array('public_repo', 'gist');
		$authorisation->note = 'My test app';
		$authorisation->note_url = 'https://www.joomla.org';

		$this->client->expects($this->once())
			->method('patch')
			->with('/authorizations/42', json_encode($authorisation))
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->edit(42, array(), array('public_repo', 'gist'), array(), 'My test app', 'https://www.joomla.org'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the editAuthorisation method - Remove scopes
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testEditRemoveScopes()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$authorisation = new stdClass;
		$authorisation->remove_scopes = array('public_repo', 'gist');
		$authorisation->note = 'My test app';
		$authorisation->note_url = 'https://www.joomla.org';

		$this->client->expects($this->once())
			->method('patch')
			->with('/authorizations/42', json_encode($authorisation))
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->edit(42, array(), array(), array('public_repo', 'gist'), 'My test app', 'https://www.joomla.org'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the editAuthorisation method - Scopes param
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testEditScopes()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$authorisation = new stdClass;
		$authorisation->scopes = array('public_repo', 'gist');
		$authorisation->note = 'My test app';
		$authorisation->note_url = 'https://www.joomla.org';

		$this->client->expects($this->once())
			->method('patch')
			->with('/authorizations/42', json_encode($authorisation))
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->edit(42, array('public_repo', 'gist'), array(), array(), 'My test app', 'https://www.joomla.org'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the editAuthorisation method - simulated failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testEditFailure()
	{
		$exception = false;

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$authorisation = new stdClass;
		$authorisation->add_scopes = array('public_repo', 'gist');
		$authorisation->note = 'My test app';
		$authorisation->note_url = 'https://www.joomla.org';

		$this->client->expects($this->once())
			->method('patch')
			->with('/authorizations/42', json_encode($authorisation))
			->will($this->returnValue($returnData));

		try
		{
			$this->object->edit(42, array(), array('public_repo', 'gist'), array(), 'My test app', 'https://www.joomla.org');
		}
		catch (DomainException $e)
		{
			$exception = true;

			$this->assertThat(
				$e->getMessage(),
				$this->equalTo(json_decode($this->errorString)->message)
			);
		}
		$this->assertTrue($exception);
	}

	/**
	 * Tests the editAuthorisation method - too many scope params
	 *
	 * @return  void
	 *
	 * @since   12.3
	 *
	 * @expectedException  RuntimeException
	 */
	public function testEditTooManyScopes()
	{
		$this->object->edit(42, array(), array('public_repo', 'gist'), array('public_repo', 'gist'), 'My test app', 'https://www.joomla.org');
	}

	/**
	 * Tests the getAuthorisation method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGet()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/authorizations/42')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->get(42),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getAuthorisation method - failure
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @expectedException  DomainException
	 */
	public function testGetFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/authorizations/42')
			->will($this->returnValue($returnData));

		$this->object->get(42);
	}

	/**
	 * Tests the getAuthorisations method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetList()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/authorizations')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getList(),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getAuthorisations method - failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 *
	 * @expectedException  DomainException
	 */
	public function testGetListFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/authorizations')
			->will($this->returnValue($returnData));

		$this->object->getList();
	}

	/**
	 * Tests the getRateLimit method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetRateLimit()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/rate_limit')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getRateLimit(),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getRateLimit method - failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 *
	 * @expectedException  DomainException
	 */
	public function testGetRateLimitFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/rate_limit')
			->will($this->returnValue($returnData));

		$this->object->getRateLimit();
	}

	public function testGetAuthorizationLink()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = 'https://github.com/login/oauth/authorize?client_id=12345'
			. '&redirect_uri=aaa&scope=bbb&state=ccc';

		$this->assertThat(
			$this->object->getAuthorizationLink('12345', 'aaa', 'bbb', 'ccc'),
			$this->equalTo($returnData->body)
		);
	}

	public function testRequestToken()
	{
		$returnData = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = '';

		$this->client->expects($this->once())
			->method('post')
			->with('https://github.com/login/oauth/access_token')
			->will($this->returnValue($returnData));


		$this->assertThat(
			$this->object->requestToken('12345', 'aaa', 'bbb', 'ccc'),
			$this->equalTo($returnData->body)
		);
	}
	public function testRequestTokenJson()
	{
		$returnData = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = '';

		$this->client->expects($this->once())
			->method('post')
			->with('https://github.com/login/oauth/access_token')
			->will($this->returnValue($returnData));


		$this->assertThat(
			$this->object->requestToken('12345', 'aaa', 'bbb', 'ccc', 'json'),
			$this->equalTo($returnData->body)
		);
	}
	public function testRequestTokenXml()
	{
		$returnData = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = '';

		$this->client->expects($this->once())
			->method('post')
			->with('https://github.com/login/oauth/access_token')
			->will($this->returnValue($returnData));


		$this->assertThat(
			$this->object->requestToken('12345', 'aaa', 'bbb', 'ccc', 'xml'),
			$this->equalTo($returnData->body)
		);
	}

	/**
	 * @expectedException UnexpectedValueException
	 */
	public function testRequestTokenInvalidFormat()
	{
		$returnData = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = '';

		$this->object->requestToken('12345', 'aaa', 'bbb', 'ccc', 'invalid');
	}
}
