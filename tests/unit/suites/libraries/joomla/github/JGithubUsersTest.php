<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Github
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/github/github.php';
require_once JPATH_PLATFORM . '/joomla/github/http.php';
require_once JPATH_PLATFORM . '/joomla/github/issues.php';

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
	 * @var    JGithubUsers  Object under test.
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

		$this->object = new JGithubUsers($this->options, $this->client);
	}

	/**
	 * Tests the getUser method
	 *
	 * @return void
	 */
	public function testGetUser()
	{
		$returnData       = new stdClass;
		$returnData->code = 200;
		$returnData->body = '{
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
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getUser('joomla'),
			$this->equalTo(json_decode($returnData->body))
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
		$returnData       = new stdClass;
		$returnData->code = 404;
		$returnData->body = '{"message":"Not Found"}';

		$this->client->expects($this->once())
			->method('get')
			->with('/users/nonexistentuser', 0, 0)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getUser('nonexistentuser'),
			$this->equalTo(json_decode($returnData->body))
		);
	}

	/**
	 * Tests the getAuthenticatedUser method
	 *
	 * @return void
	 */
	public function testGetAuthenticatedUser()
	{
		$returnData       = new stdClass;
		$returnData->code = 200;
		$returnData->body = '{
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
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getAuthenticatedUser('joomla'),
			$this->equalTo(json_decode($returnData->body))
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
		$returnData       = new stdClass;
		$returnData->code = 401;
		$returnData->body = '{"message":"Requires authentication"}';

		$this->client->expects($this->once())
			->method('get')
			->with('/user', 0, 0)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getAuthenticatedUser(),
			$this->equalTo(json_decode($returnData->body))
		);
	}

	/**
	 * Tests the getUsers method
	 *
	 * @return void
	 */
	public function testGetUsers()
	{
		$returnData       = new stdClass;
		$returnData->code = 200;
		$returnData->body = '[
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
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getUsers(),
			$this->equalTo(json_decode($returnData->body))
		);
	}
}
