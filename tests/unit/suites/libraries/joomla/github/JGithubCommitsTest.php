<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Github
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JGitHubCommits.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Github
 *
 * @since       11.1
 */
class JGitHubCommitsTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JRegistry  Options for the GitHub object.
	 * @since  12.1
	 */
	protected $options;

	/**
	 * @var    JGithubHttp  Mock client object.
	 * @since  12.1
	 */
	protected $client;

	/**
	 * @var    JGithubCommits  Object under test.
	 * @since  12.1
	 */
	protected $object;

	/**
	 * @var    string  Sample JSON string.
	 * @since  12.1
	 */
	protected $sampleString = '{"a":1,"b":2,"c":3,"d":4,"e":5}';

	/**
	 * @var    string  Sample JSON error message.
	 * @since  12.1
	 */
	protected $errorString = '{"message": "Generic Error"}';

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->options = new JRegistry;
		$this->client = $this->getMockBuilder('JGithubHttp')
			->setMethods(array('get', 'post', 'delete', 'patch', 'put'))
			->getMock();

		$this->object = new JGithubCommits($this->options, $this->client);
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
	 * Tests the create method
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testCreate()
	{
		$returnData = new stdClass;
		$returnData->code = 201;
		$returnData->body = $this->sampleString;

		$pull = new stdClass;
		$pull->message = 'My latest commit';
		$pull->tree = 'abc1234';
		$pull->parents = array('def5678');

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/git/commits', json_encode($pull))
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->create('joomla', 'joomla-platform', 'My latest commit', 'abc1234', array('def5678')),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the create method - simulated failure
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testCreateFailure()
	{
		$exception = false;

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$pull = new stdClass;
		$pull->message = 'My latest commit';
		$pull->tree = 'abc1234';
		$pull->parents = array('def5678');

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/git/commits', json_encode($pull))
			->will($this->returnValue($returnData));

		try
		{
			$this->object->create('joomla', 'joomla-platform', 'My latest commit', 'abc1234', array('def5678'));
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
	 * Tests the createCommitComment method
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testCreateCommitComment()
	{
		$returnData = new stdClass;
		$returnData->code = 201;
		$returnData->body = $this->sampleString;

		// The comment data
		$comment = new stdClass;
		$comment->body = 'My Insightful Comment';
		$comment->commit_id = 'abc1234';
		$comment->line = 1;
		$comment->path = 'path/to/file';
		$comment->position = 254;

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/commits/abc1234/comments', json_encode($comment))
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createCommitComment('joomla', 'joomla-platform', 'abc1234', 'My Insightful Comment', 1, 'path/to/file', 254),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createCommitComment method - simulated failure
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testCreateCommitCommentFailure()
	{
		$exception = false;

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// The comment data
		$comment = new stdClass;
		$comment->body = 'My Insightful Comment';
		$comment->commit_id = 'abc1234';
		$comment->line = 1;
		$comment->path = 'path/to/file';
		$comment->position = 254;

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/commits/abc1234/comments', json_encode($comment))
			->will($this->returnValue($returnData));

		try
		{
			$this->object->createCommitComment('joomla', 'joomla-platform', 'abc1234', 'My Insightful Comment', 1, 'path/to/file', 254);
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
	 * Tests the deleteCommitComment method
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testDeleteCommitComment()
	{
		$returnData = new stdClass;
		$returnData->code = 204;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('delete')
			->with('/repos/joomla/joomla-platform/comments/42')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->deleteCommitComment('joomla', 'joomla-platform', 42),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the deleteCommitComment method - simulated failure
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testDeleteCommitCommentFailure()
	{
		$exception = false;

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('delete')
			->with('/repos/joomla/joomla-platform/comments/42')
			->will($this->returnValue($returnData));

		try
		{
			$this->object->deleteCommitComment('joomla', 'joomla-platform', 42);
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
	 * Tests the editCommitComment method
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testEditCommitComment()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// The comment data
		$comment = new stdClass;
		$comment->body = 'My Insightful Comment';

		$this->client->expects($this->once())
			->method('patch')
			->with('/repos/joomla/joomla-platform/comments/42', json_encode($comment))
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->editCommitComment('joomla', 'joomla-platform', 42, 'My Insightful Comment'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the editCommitComment method - simulated failure
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testEditCommitCommentFailure()
	{
		$exception = false;

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// The comment data
		$comment = new stdClass;
		$comment->body = 'My Insightful Comment';

		$this->client->expects($this->once())
			->method('patch')
			->with('/repos/joomla/joomla-platform/comments/42', json_encode($comment))
			->will($this->returnValue($returnData));

		try
		{
			$this->object->editCommitComment('joomla', 'joomla-platform', 42, 'My Insightful Comment');
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
	 * Tests the getCommit method
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGetCommit()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/commits/abc1234')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getCommit('joomla', 'joomla-platform', 'abc1234'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getCommit method - failure
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @expectedException  DomainException
	 */
	public function testGetCommitFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/commits/abc1234')
			->will($this->returnValue($returnData));

		$this->object->getCommit('joomla', 'joomla-platform', 'abc1234');
	}

	/**
	 * Tests the getCommitComment method
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGetCommitComment()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/comments/42')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getCommitComment('joomla', 'joomla-platform', 42),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getCommitComment method - failure
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @expectedException  DomainException
	 */
	public function testGetCommitCommentFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/comments/42')
			->will($this->returnValue($returnData));

		$this->object->getCommitComment('joomla', 'joomla-platform', 42);
	}

	/**
	 * Tests the getCommitComments method
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGetCommitComments()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/commits/abc1234/comments')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getCommitComments('joomla', 'joomla-platform', 'abc1234'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getCommitComments method - failure
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @expectedException  DomainException
	 */
	public function testGetCommitCommentsFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/commits/abc1234/comments')
			->will($this->returnValue($returnData));

		$this->object->getCommitComments('joomla', 'joomla-platform', 'abc1234');
	}

	/**
	 * Tests the getDiff method
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGetDiff()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/compare/master...staging')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getDiff('joomla', 'joomla-platform', 'master', 'staging'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getDiff method - failure
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @expectedException  DomainException
	 */
	public function testGetDiffFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/compare/master...staging')
			->will($this->returnValue($returnData));

		$this->object->getDiff('joomla', 'joomla-platform', 'master', 'staging');
	}

	/**
	 * Tests the getList method
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGetList()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/commits')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getList('joomla', 'joomla-platform'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getList method - failure
	 *
	 * @return  void
	 *
	 * @since   12.1
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
			->with('/repos/joomla/joomla-platform/commits')
			->will($this->returnValue($returnData));

		$this->object->getList('joomla', 'joomla-platform');
	}

	/**
	 * Tests the getListComments method
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGetListComments()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/comments')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getListComments('joomla', 'joomla-platform'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getListComments method - failure
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @expectedException  DomainException
	 */
	public function testGetListCommentsFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/comments')
			->will($this->returnValue($returnData));

		$this->object->getListComments('joomla', 'joomla-platform');
	}
}
