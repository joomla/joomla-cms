<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Github
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JGithubEvents.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Github
 * @since       12.3
 */
class JGithubPackageActivityEventsTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JRegistry  Options for the GitHub object.
	 * @since  11.4
	 */
	protected $options;

	/**
	 * @var    JGithubHttp  Mock client object.
	 * @since  11.4
	 */
	protected $client;

	/**
	 * @var    JGithubPackageActivityEvents  Object under test.
	 * @since  11.4
	 */
	protected $object;

	/**
	 * @var string
	 * @since  11.4
	 */
	protected $response = '[
  {
    "type": "Event",
    "public": true,
    "payload": {

    },
    "repo": {
      "id": 3,
      "name": "octocat/Hello-World",
      "url": "https://api.github.com/repos/octocat/Hello-World"
    },
    "actor": {
      "login": "octocat",
      "id": 1,
      "avatar_url": "https://github.com/images/error/octocat_happy.gif",
      "gravatar_id": "somehexcode",
      "url": "https://api.github.com/users/octocat"
    },
    "org": {
      "login": "octocat",
      "id": 1,
      "avatar_url": "https://github.com/images/error/octocat_happy.gif",
      "gravatar_id": "somehexcode",
      "url": "https://api.github.com/users/octocat"
    },
    "created_at": "2011-09-06T17:26:27Z",
    "id": "12345"
  }
]';

	/**
	 * @var string
	 * @since  11.4
	 */
	protected $owner = 'joomla';

	/**
	 * @var string
	 * @since  11.4
	 */
	protected $repo = 'joomla-platform';

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @since   12.4
	 * @return  void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->options = new JRegistry;
		$this->client  = $this->getMockBuilder('JGithubHttp')->setMethods(array('get', 'post', 'delete', 'patch', 'put'))->getMock();

		$this->object = new JGithubPackageActivityEvents($this->options, $this->client);
	}

	/**
	 * Tests the getPublic method
	 *
	 * @since   12.4
	 * @return  void
	 */
	public function testGetPublic()
	{
		$returnData       = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = $this->response;

		$this->client->expects($this->once())
			->method('get')
			->with('/events', 0, 0)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getPublic(),
			$this->equalTo(json_decode($returnData->body))
		);

	}

	/**
	 * Tests the getRepository method
	 *
	 * @since   12.4
	 * @return  void
	 */
	public function testGetRepository()
	{
		$returnData       = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = $this->response;

		$path = '/repos/' . $this->owner . '/' . $this->repo . '/events';

		$this->client->expects($this->once())
			->method('get')
			->with($path, 0, 0)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getRepository($this->owner, $this->repo),
			$this->equalTo(json_decode($returnData->body))
		);
	}

	/**
	 * Tests the getIssue method
	 *
	 * @since   12.4
	 * @return  void
	 */
	public function testGetIssue()
	{
		$returnData       = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = $this->response;

		$path = '/repos/' . $this->owner . '/' . $this->repo . '/issues/events';

		$this->client->expects($this->once())
			->method('get')
			->with($path, 0, 0)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getIssue($this->owner, $this->repo),
			$this->equalTo(json_decode($returnData->body))
		);
	}

	/**
	 * Tests the getNetwork method
	 *
	 * @since   12.4
	 * @return  void
	 */
	public function testGetNetwork()
	{
		$returnData       = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = $this->response;

		$path = '/networks/' . $this->owner . '/' . $this->repo . '/events';

		$this->client->expects($this->once())
			->method('get')
			->with($path, 0, 0)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getNetwork($this->owner, $this->repo),
			$this->equalTo(json_decode($returnData->body))
		);
	}

	/**
	 * Tests the getOrg method
	 *
	 * @since   12.4
	 * @return  void
	 */
	public function testGetOrg()
	{
		$returnData       = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = $this->response;

		$path = '/orgs/' . $this->owner . '/events';

		$this->client->expects($this->once())
			->method('get')
			->with($path, 0, 0)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getOrg($this->owner, $this->repo),
			$this->equalTo(json_decode($returnData->body))
		);
	}

	/**
	 * Tests the getUser method
	 *
	 * @since   12.4
	 * @return  void
	 */
	public function testGetUser()
	{
		$returnData       = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = $this->response;

		$path = '/users/' . $this->owner . '/received_events';

		$this->client->expects($this->once())
			->method('get')
			->with($path, 0, 0)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getUser($this->owner),
			$this->equalTo(json_decode($returnData->body))
		);
	}

	/**
	 * Tests the getUserPublic method
	 *
	 * @since   12.4
	 * @return  void
	 */
	public function testGetUserPublic()
	{
		$returnData       = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = $this->response;

		$path = '/users/' . $this->owner . '/received_events/public';

		$this->client->expects($this->once())
			->method('get')
			->with($path, 0, 0)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getUserPublic($this->owner),
			$this->equalTo(json_decode($returnData->body))
		);
	}

	/**
	 * Tests the getByUser method
	 *
	 * @since   12.4
	 * @return  void
	 */
	public function testGetByUser()
	{
		$returnData       = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = $this->response;

		$path = '/users/' . $this->owner . '/events';

		$this->client->expects($this->once())
			->method('get')
			->with($path, 0, 0)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getByUser($this->owner),
			$this->equalTo(json_decode($returnData->body))
		);
	}

	/**
	 * Tests the getByUserPublic method
	 *
	 * @since   12.4
	 * @return  void
	 */
	public function testGetByUserPublic()
	{
		$returnData       = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = $this->response;

		$path = '/users/' . $this->owner . '/events/public';

		$this->client->expects($this->once())
			->method('get')
			->with($path, 0, 0)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getByUserPublic($this->owner),
			$this->equalTo(json_decode($returnData->body))
		);
	}

	/**
	 * Tests the getUserOrg method
	 *
	 * @since   12.4
	 * @return  void
	 */
	public function testGetUserOrg()
	{
		$returnData       = new JHttpResponse;
		$returnData->code = 200;
		$returnData->body = $this->response;

		$path = '/users/' . $this->owner . '/events/orgs/' . $this->repo;

		$this->client->expects($this->once())
			->method('get')
			->with($path, 0, 0)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getUserOrg($this->owner, $this->repo),
			$this->equalTo(json_decode($returnData->body))
		);
	}
}
