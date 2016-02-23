<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Github
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JGithubPulls.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Github
 *
 * @since       11.1
 */
class JGithubPullsTest extends PHPUnit_Framework_TestCase
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
	 * @var    JGithubPackagePulls  Object under test.
	 * @since  11.4
	 */
	protected $object;

	/**
	 * @var    string  Sample JSON string.
	 * @since  11.4
	 */
	protected $sampleString = '{"a":1,"b":2,"c":3,"d":4,"e":5}';

	/**
	 * @var    string  Sample JSON error message.
	 * @since  11.4
	 */
	protected $errorString = '{"message": "Generic Error"}';

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
		$this->client = $this->getMock('JGithubHttp', array('get', 'post', 'delete', 'patch', 'put'));
		$this->response = $this->getMock('JHttpResponse');

		$this->object = new JGithubPackagePulls($this->options, $this->client);
	}

	/**
	 * Test...
	 *
	 * @param   string  $name  The method name.
	 *
	 * @return string
	 */
	protected function getMethod($name)
	{
		$class = new ReflectionClass('JGithubPulls');
		$method = $class->getMethod($name);
		$method->setAccessible(true);

		return $method;
	}

	/**
	 * Tests the create method
	 *
	 * @return void
	 */
	public function testCreate()
	{
		$this->response->code = 201;
		$this->response->body = $this->sampleString;

		$pull = new stdClass;
		$pull->title = 'My Pull Request';
		$pull->base = 'staging';
		$pull->head = 'joomla-jenkins:mychanges';
		$pull->body = 'These are my changes - please review them';

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/pulls', json_encode($pull))
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->create('joomla', 'joomla-platform', 'My Pull Request', 'staging', 'joomla-jenkins:mychanges',
				'These are my changes - please review them'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the create method - failure
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
	 */
	public function testCreateFailure()
	{
		$this->response->code = 501;
		$this->response->body = $this->errorString;

		$pull = new stdClass;
		$pull->title = 'My Pull Request';
		$pull->base = 'staging';
		$pull->head = 'joomla-jenkins:mychanges';
		$pull->body = 'These are my changes - please review them';

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/pulls', json_encode($pull))
			->will($this->returnValue($this->response));

		$this->object->create('joomla', 'joomla-platform', 'My Pull Request', 'staging', 'joomla-jenkins:mychanges',
			'These are my changes - please review them');
	}

	/**
	 * Tests the createComment method
	 *
	 * @return void
	 */
	public function testCreateComment()
	{
		$this->response->code = 201;
		$this->response->body = $this->sampleString;

		$pull = new stdClass;
		$pull->body = 'My Insightful Comment';
		$pull->commit_id = 'abcde12345';
		$pull->path = '/path/to/file';
		$pull->position = 254;

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/pulls/523/comments', json_encode($pull))
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->createComment('joomla', 'joomla-platform', 523, 'My Insightful Comment', 'abcde12345', '/path/to/file', 254),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createComment method - failure
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
	 */
	public function testCreateCommentFailure()
	{
		$this->response->code = 501;
		$this->response->body = $this->errorString;

		$pull = new stdClass;
		$pull->body = 'My Insightful Comment';
		$pull->commit_id = 'abcde12345';
		$pull->path = '/path/to/file';
		$pull->position = 254;

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/pulls/523/comments', json_encode($pull))
			->will($this->returnValue($this->response));

		$this->object->createComment('joomla', 'joomla-platform', 523, 'My Insightful Comment', 'abcde12345', '/path/to/file', 254);
	}

	/**
	 * Tests the createCommentReply method
	 *
	 * @return void
	 */
	public function testCreateCommentReply()
	{
		$this->response->code = 201;
		$this->response->body = $this->sampleString;

		$pull = new stdClass;
		$pull->body = 'My Insightful Comment';
		$pull->in_reply_to = 434;

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/pulls/523/comments', json_encode($pull))
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->createCommentReply('joomla', 'joomla-platform', 523, 'My Insightful Comment', 434),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createCommentReply method - failure
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
	 */
	public function testCreateCommentReplyFailure()
	{
		$this->response->code = 501;
		$this->response->body = $this->errorString;

		$pull = new stdClass;
		$pull->body = 'My Insightful Comment';
		$pull->in_reply_to = 434;

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/pulls/523/comments', json_encode($pull))
			->will($this->returnValue($this->response));

		$this->object->createCommentReply('joomla', 'joomla-platform', 523, 'My Insightful Comment', 434);
	}

	/**
	 * Tests the createFromIssue method
	 *
	 * @return void
	 */
	public function testCreateFromIssue()
	{
		$this->response->code = 201;
		$this->response->body = $this->sampleString;

		$pull = new stdClass;
		$pull->issue = 254;
		$pull->base = 'staging';
		$pull->head = 'joomla-jenkins:mychanges';

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/pulls', json_encode($pull))
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->createFromIssue('joomla', 'joomla-platform', 254, 'staging', 'joomla-jenkins:mychanges'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createFromIssue method - failure
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
	 */
	public function testCreateFromIssueFailure()
	{
		$this->response->code = 501;
		$this->response->body = $this->errorString;

		$pull = new stdClass;
		$pull->issue = 254;
		$pull->base = 'staging';
		$pull->head = 'joomla-jenkins:mychanges';

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/pulls', json_encode($pull))
			->will($this->returnValue($this->response));

		$this->object->createFromIssue('joomla', 'joomla-platform', 254, 'staging', 'joomla-jenkins:mychanges');
	}

	/**
	 * Tests the deleteComment method
	 *
	 * @return void
	 */
	public function testDeleteComment()
	{
		$this->response->code = 204;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('delete')
			->with('/repos/joomla/joomla-platform/pulls/comments/254')
			->will($this->returnValue($this->response));

		$this->object->deleteComment('joomla', 'joomla-platform', 254);
	}

	/**
	 * Tests the deleteComment method - failure
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
	 */
	public function testDeleteCommentFailure()
	{
		$this->response->code = 504;
		$this->response->body = $this->errorString;

		$this->client->expects($this->once())
			->method('delete')
			->with('/repos/joomla/joomla-platform/pulls/comments/254')
			->will($this->returnValue($this->response));

		$this->object->deleteComment('joomla', 'joomla-platform', 254);
	}

	/**
	 * Tests the edit method
	 *
	 * @return void
	 */
	public function testEdit()
	{
		$this->response->code = 200;
		$this->response->body = $this->sampleString;

		$pull = new stdClass;
		$pull->title = 'My Pull Request';
		$pull->body = 'These are my changes - please review them';
		$pull->state = 'Closed';

		$this->client->expects($this->once())
			->method('patch')
			->with('/repos/joomla/joomla-platform/pulls/523', json_encode($pull))
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->edit('joomla', 'joomla-platform', 523, 'My Pull Request', 'These are my changes - please review them', 'Closed'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the edit method - failure
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
	 */
	public function testEditFailure()
	{
		$this->response->code = 500;
		$this->response->body = $this->errorString;

		$pull = new stdClass;
		$pull->title = 'My Pull Request';
		$pull->body = 'These are my changes - please review them';

		$this->client->expects($this->once())
			->method('patch')
			->with('/repos/joomla/joomla-platform/pulls/523', json_encode($pull))
			->will($this->returnValue($this->response));

		$this->object->edit('joomla', 'joomla-platform', 523, 'My Pull Request', 'These are my changes - please review them');
	}

	/**
	 * Tests the editComment method
	 *
	 * @return void
	 */
	public function testEditComment()
	{
		$this->response->code = 200;
		$this->response->body = $this->sampleString;

		$pull = new stdClass;
		$pull->body = 'This comment is now even more insightful';

		$this->client->expects($this->once())
			->method('patch')
			->with('/repos/joomla/joomla-platform/pulls/comments/523', json_encode($pull))
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->editComment('joomla', 'joomla-platform', 523, 'This comment is now even more insightful'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the editComment method - failure
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
	 */
	public function testEditCommentFailure()
	{
		$this->response->code = 500;
		$this->response->body = $this->errorString;

		$pull = new stdClass;
		$pull->body = 'This comment is now even more insightful';

		$this->client->expects($this->once())
			->method('patch')
			->with('/repos/joomla/joomla-platform/pulls/comments/523', json_encode($pull))
			->will($this->returnValue($this->response));

		$this->object->editComment('joomla', 'joomla-platform', 523, 'This comment is now even more insightful');
	}

	/**
	 * Tests the get method
	 *
	 * @return void
	 */
	public function testGet()
	{
		$this->response->code = 200;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/pulls/523')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->get('joomla', 'joomla-platform', 523),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the get method - failure
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
	 */
	public function testGetFailure()
	{
		$this->response->code = 500;
		$this->response->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/pulls/523')
			->will($this->returnValue($this->response));

		$this->object->get('joomla', 'joomla-platform', 523);
	}

	/**
	 * Tests the getComment method
	 *
	 * @return void
	 */
	public function testGetComment()
	{
		$this->response->code = 200;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/pulls/comments/523')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->getComment('joomla', 'joomla-platform', 523),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getComment method - failure
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
	 */
	public function testGetCommentFailure()
	{
		$this->response->code = 500;
		$this->response->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/pulls/comments/523')
			->will($this->returnValue($this->response));

		$this->object->getComment('joomla', 'joomla-platform', 523);
	}

	/**
	 * Tests the getComments method
	 *
	 * @return void
	 */
	public function testGetComments()
	{
		$this->response->code = 200;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/pulls/523/comments')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->getComments('joomla', 'joomla-platform', 523),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getComments method - failure
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
	 */
	public function testGetCommentsFailure()
	{
		$this->response->code = 500;
		$this->response->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/pulls/523/comments')
			->will($this->returnValue($this->response));

		$this->object->getComments('joomla', 'joomla-platform', 523);
	}

	/**
	 * Tests the getCommits method
	 *
	 * @return void
	 */
	public function testGetCommits()
	{
		$this->response->code = 200;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/pulls/523/commits')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->getCommits('joomla', 'joomla-platform', 523),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getCommits method - failure
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
	 */
	public function testGetCommitsFailure()
	{
		$this->response->code = 500;
		$this->response->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/pulls/523/commits')
			->will($this->returnValue($this->response));

		$this->object->getCommits('joomla', 'joomla-platform', 523);
	}

	/**
	 * Tests the getFiles method
	 *
	 * @return void
	 */
	public function testGetFiles()
	{
		$this->response->code = 200;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/pulls/523/files')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->getFiles('joomla', 'joomla-platform', 523),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getFiles method - failure
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
	 */
	public function testGetFilesFailure()
	{
		$this->response->code = 500;
		$this->response->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/pulls/523/files')
			->will($this->returnValue($this->response));

		$this->object->getFiles('joomla', 'joomla-platform', 523);
	}

	/**
	 * Tests the getList method
	 *
	 * @return void
	 */
	public function testGetList()
	{
		$this->response->code = 200;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/pulls?state=closed')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->getList('joomla', 'joomla-platform', 'closed'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getList method - failure
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
	 */
	public function testGetListFailure()
	{
		$this->response->code = 500;
		$this->response->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/pulls')
			->will($this->returnValue($this->response));

		$this->object->getList('joomla', 'joomla-platform');
	}

	/**
	 * Tests the isMerged method when the pull request has been merged
	 *
	 * @return void
	 */
	public function testIsMergedTrue()
	{
		$this->response->code = 204;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/pulls/523/merge')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->isMerged('joomla', 'joomla-platform', 523),
			$this->equalTo(true)
		);
	}

	/**
	 * Tests the isMerged method when the pull request has not been merged
	 *
	 * @return void
	 */
	public function testIsMergedFalse()
	{
		$this->response->code = 404;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/pulls/523/merge')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->isMerged('joomla', 'joomla-platform', 523),
			$this->equalTo(false)
		);
	}

	/**
	 * Tests the isMerged method when the request fails
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
	 */
	public function testIsMergedFailure()
	{
		$this->response->code = 504;
		$this->response->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/pulls/523/merge')
			->will($this->returnValue($this->response));

		$this->object->isMerged('joomla', 'joomla-platform', 523);
	}

	/**
	 * Tests the merge method
	 *
	 * @return void
	 */
	public function testMerge()
	{
		$this->response->code = 200;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('put')
			->with('/repos/joomla/joomla-platform/pulls/523/merge')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->merge('joomla', 'joomla-platform', 523),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the merge method - failure
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
	 */
	public function testMergeFailure()
	{
		$this->response->code = 500;
		$this->response->body = $this->errorString;

		$this->client->expects($this->once())
			->method('put')
			->with('/repos/joomla/joomla-platform/pulls/523/merge')
			->will($this->returnValue($this->response));

		$this->object->merge('joomla', 'joomla-platform', 523);
	}
}
