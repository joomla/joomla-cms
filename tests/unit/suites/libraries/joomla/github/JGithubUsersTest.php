<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Github
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JGithubIssues.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Github
 *
 * @since       11.1
 */
class JGithubUsersTest extends PHPUnit_Framework_TestCase
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
	 * @var    JHttpResponse  Mock response object.
	 * @since  12.3
	 */
	protected $response;

	/**
	 * @var    JGithubPackageUsers  Object under test.
	 * @since  11.4
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->options = new JRegistry;
		$this->client  = $this->getMock('JGithubHttp', array('get', 'post', 'delete', 'patch', 'put'));
		$this->response = $this->getMock('JHttpResponse');

		$this->object = new JGithubPackageUsers($this->options, $this->client);
	}

	/**
	 * Tests the getUser method
	 *
	 * @return void
	 */
	public function testGetUser()
	{
		$this->response->code = 200;
		$this->response->body = '{
  "login": "octocat",
  "id": 1,
  "avatar_url": "https://github.com/images/error/octocat_happy.gif",
  "gravatar_id": "somehexcode",
  "url": "https://api.github.com/users/octocat",
  "name": "monalisa octocat",
  "company": "GitHub",
  "blog": "https://github.com/blog",
  "location": "San Francisco",
  "email": "octocat@github.com",
  "hireable": false,
  "bio": "There once was...",
  "public_repos": 2,
  "public_gists": 1,
  "followers": 20,
  "following": 0,
  "html_url": "https://github.com/octocat",
  "created_at": "2008-01-14T04:33:35Z",
  "type": "User"
}';

		$this->client->expects($this->once())
			->method('get')
			->with('/users/joomla', 0, 0)
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->getUser('joomla'),
			$this->equalTo(json_decode($this->response->body))
		);
	}

	/**
	 * Tests the getUser method with failure
	 *
	 * @expectedException  DomainException
	 * @return void
	 */
	public function testGetUserFailure()
	{
		$this->response->code = 404;
		$this->response->body = '{"message":"Not Found"}';

		$this->client->expects($this->once())
			->method('get')
			->with('/users/nonexistentuser', 0, 0)
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->getUser('nonexistentuser'),
			$this->equalTo(json_decode($this->response->body))
		);
	}

	/**
	 * Tests the getAuthenticatedUser method
	 *
	 * @return void
	 */
	public function testGetAuthenticatedUser()
	{
		$this->response->code = 200;
		$this->response->body = '{
  "login": "octocat",
  "id": 1,
  "avatar_url": "https://github.com/images/error/octocat_happy.gif",
  "gravatar_id": "somehexcode",
  "url": "https://api.github.com/users/octocat",
  "name": "monalisa octocat",
  "company": "GitHub",
  "blog": "https://github.com/blog",
  "location": "San Francisco",
  "email": "octocat@github.com",
  "hireable": false,
  "bio": "There once was...",
  "public_repos": 2,
  "public_gists": 1,
  "followers": 20,
  "following": 0,
  "html_url": "https://github.com/octocat",
  "created_at": "2008-01-14T04:33:35Z",
  "type": "User",
  "total_private_repos": 100,
  "owned_private_repos": 100,
  "private_gists": 81,
  "disk_usage": 10000,
  "collaborators": 8,
  "plan": {
    "name": "Medium",
    "space": 400,
    "collaborators": 10,
    "private_repos": 20
  }
}';

		$this->client->expects($this->once())
			->method('get')
			->with('/user', 0, 0)
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->getAuthenticatedUser('joomla'),
			$this->equalTo(json_decode($this->response->body))
		);
	}

	/**
	 * Tests the GetAuthenticatedUser method with failure
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
	 */
	public function testGetAuthenticatedUserFailure()
	{
		$this->response->code = 401;
		$this->response->body = '{"message":"Requires authentication"}';

		$this->client->expects($this->once())
			->method('get')
			->with('/user', 0, 0)
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->getAuthenticatedUser(),
			$this->equalTo(json_decode($this->response->body))
		);
	}

	/**
	 * Tests the getUsers method
	 *
	 * @return void
	 */
	public function testGetUsers()
	{
		$this->response->code = 200;
		$this->response->body = '[
  {
    "login": "octocat",
    "id": 1,
    "avatar_url": "https://github.com/images/error/octocat_happy.gif",
    "gravatar_id": "somehexcode",
    "url": "https://api.github.com/users/octocat"
  }
],
  {
    "login": "elkuku",
    "id": 33978,
    "avatar_url": "https://github.com/images/error/octocat_happy.gif",
    "gravatar_id": "somehexcode",
    "url": "https://api.github.com/users/elkuku"
  }
]';

		$this->client->expects($this->once())
			->method('get')
			->with('/users', 0, 0)
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->getUsers(),
			$this->equalTo(json_decode($this->response->body))
		);
	}
}
