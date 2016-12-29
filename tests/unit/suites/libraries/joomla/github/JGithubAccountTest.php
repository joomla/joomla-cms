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
class JGithubAccountTest extends PHPUnit_Framework_TestCase
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
	 * @var    JGithubAccount  Object under test.
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
		$this->client = $this->getMockBuilder('JGithubHttp')
						->setMethods(array('get', 'post', 'delete', 'patch', 'put'))
						->getMock();

		$this->object = new JGithubAccount($this->options, $this->client);
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
		unset($this->options, $this->client, $this->object);
		parent::tearDown();
	}

	/**
	 * Tests the createAuthorisation method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testCreateAuthorisation()
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
			$this->object->createAuthorisation(array('public_repo'), 'My test app', 'https://www.joomla.org'),
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
	public function testCreateAuthorisationFailure()
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
			$this->object->createAuthorisation(array('public_repo'), 'My test app', 'https://www.joomla.org');
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
	public function testDeleteAuthorisation()
	{
		$returnData = new stdClass;
		$returnData->code = 204;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('delete')
			->with('/authorizations/42')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->deleteAuthorisation(42),
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
	public function testDeleteAuthorisationFailure()
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
			$this->object->deleteAuthorisation(42);
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
	public function testEditAuthorisationAddScopes()
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
			$this->object->editAuthorisation(42, array(), array('public_repo', 'gist'), array(), 'My test app', 'https://www.joomla.org'),
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
	public function testEditAuthorisationRemoveScopes()
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
			$this->object->editAuthorisation(42, array(), array(), array('public_repo', 'gist'), 'My test app', 'https://www.joomla.org'),
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
	public function testEditAuthorisationScopes()
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
			$this->object->editAuthorisation(42, array('public_repo', 'gist'), array(), array(), 'My test app', 'https://www.joomla.org'),
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
	public function testEditAuthorisationFailure()
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
			$this->object->editAuthorisation(42, array(), array('public_repo', 'gist'), array(), 'My test app', 'https://www.joomla.org');
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
	public function testEditAuthorisationTooManyScopes()
	{
		$this->object->editAuthorisation(42, array(), array('public_repo', 'gist'), array('public_repo', 'gist'), 'My test app', 'https://www.joomla.org');
	}

	/**
	 * Tests the getAuthorisation method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetAuthorisation()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/authorizations/42')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getAuthorisation(42),
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
	public function testGetAuthorisationFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/authorizations/42')
			->will($this->returnValue($returnData));

		$this->object->getAuthorisation(42);
	}

	/**
	 * Tests the getAuthorisations method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetAuthorisations()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/authorizations')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getAuthorisations(),
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
	public function testGetAuthorisationsFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/authorizations')
			->will($this->returnValue($returnData));

		$this->object->getAuthorisations();
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
}
