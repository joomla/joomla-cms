<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Github
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/github/github.php';
require_once JPATH_PLATFORM.'/joomla/github/http.php';
require_once JPATH_PLATFORM.'/joomla/github/issues.php';

/**
 * Test class for JGithubIssues.
 */
class JGithubIssuesTest extends PHPUnit_Framework_TestCase
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
	 * @var    JGithubIssues  Object under test.
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
	 */
	protected function setUp()
	{
		$this->options = new JRegistry;
		$this->client = $this->getMock('JGithubHttp', array('get', 'post', 'delete', 'patch', 'put'));

		$this->object = new JGithubIssues($this->options, $this->client);
	}

	/**
	 * Tests the create method
	 */
	public function testCreate()
	{
		$returnData = new stdClass;
		$returnData->code = 201;
		$returnData->body = $this->sampleString;

		$issue = new stdClass;
		$issue->title = 'My issue';
		$issue->assignee = 'JoeUser';
		$issue->milestone = '11.5';
		$issue->labels = array();
		$issue->body = 'These are my changes - please review them';

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/issues', json_encode($issue))
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->create('joomla', 'joomla-platform', 'My issue', 'These are my changes - please review them', 'JoeUser', '11.5', array()),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the create method - failure
	 * @expectedException  DomainException
	 */
	public function testCreateFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 501;
		$returnData->body = $this->errorString;

		$issue = new stdClass;
		$issue->title = 'My issue';
		$issue->assignee = 'JoeUser';
		$issue->milestone = '11.5';
		$issue->labels = array();
		$issue->body = 'These are my changes - please review them';

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/issues', json_encode($issue))
			->will($this->returnValue($returnData));

		$this->object->create('joomla', 'joomla-platform', 'My issue', 'These are my changes - please review them', 'JoeUser', '11.5', array());
	}

	/**
	 * Tests the createComment method
	 */
	public function testCreateComment()
	{
		$returnData = new stdClass;
		$returnData->code = 201;
		$returnData->body = $this->sampleString;

		$issue = new stdClass;
		$issue->body = 'My Insightful Comment';

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/issues/523/comments', json_encode($issue))
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createComment('joomla', 'joomla-platform', 523, 'My Insightful Comment'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createComment method - failure
	 * @expectedException  DomainException
	 */
	public function testCreateCommentFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 501;
		$returnData->body = $this->errorString;

		$issue = new stdClass;
		$issue->body = 'My Insightful Comment';

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/issues/523/comments', json_encode($issue))
			->will($this->returnValue($returnData));

		$this->object->createComment('joomla', 'joomla-platform', 523, 'My Insightful Comment');
	}

	/**
	 * Tests the deleteComment method
	 */
	public function testDeleteComment()
	{
		$returnData = new stdClass;
		$returnData->code = 204;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('delete')
			->with('/repos/joomla/joomla-platform/issues/comments/254')
			->will($this->returnValue($returnData));

		$this->object->deleteComment('joomla', 'joomla-platform', 254);
	}

	/**
	 * Tests the deleteComment method - failure
	 * @expectedException  DomainException
	 */
	public function testDeleteCommentFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 504;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('delete')
			->with('/repos/joomla/joomla-platform/issues/comments/254')
			->will($this->returnValue($returnData));

		$this->object->deleteComment('joomla', 'joomla-platform', 254);
	}

	/**
	 * Tests the edit method
	 */
	public function testEdit()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$issue = new stdClass;
		$issue->title = 'My issue';
		$issue->body = 'These are my changes - please review them';
		$issue->state = 'Closed';
		$issue->assignee = 'JoeAssignee';
		$issue->milestone = '12.2';
		$issue->labels = array('Fixed');


		$this->client->expects($this->once())
			->method('patch')
			->with('/repos/joomla/joomla-platform/issues/523', json_encode($issue))
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->edit('joomla', 'joomla-platform', 523, 'Closed', 'My issue', 'These are my changes - please review them',
				'JoeAssignee', '12.2', array('Fixed')),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the edit method - failure
	 * @expectedException  DomainException
	 */
	public function testEditFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$issue = new stdClass;
		$issue->title = 'My issue';
		$issue->body = 'These are my changes - please review them';
		$issue->state = 'Closed';

		$this->client->expects($this->once())
			->method('patch')
			->with('/repos/joomla/joomla-platform/issues/523', json_encode($issue))
			->will($this->returnValue($returnData));

		$this->object->edit('joomla', 'joomla-platform', 523, 'Closed', 'My issue', 'These are my changes - please review them');
	}

	/**
	 * Tests the editComment method
	 */
	public function testEditComment()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$issue = new stdClass;
		$issue->body = 'This comment is now even more insightful';

		$this->client->expects($this->once())
			->method('patch')
			->with('/repos/joomla/joomla-platform/issues/comments/523', json_encode($issue))
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->editComment('joomla', 'joomla-platform', 523, 'This comment is now even more insightful'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the editComment method - failure
	 * @expectedException  DomainException
	 */
	public function testEditCommentFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$issue = new stdClass;
		$issue->body = 'This comment is now even more insightful';

		$this->client->expects($this->once())
			->method('patch')
			->with('/repos/joomla/joomla-platform/issues/comments/523', json_encode($issue))
			->will($this->returnValue($returnData));

		$this->object->editComment('joomla', 'joomla-platform', 523, 'This comment is now even more insightful');
	}

	/**
	 * Tests the get method
	 */
	public function testGet()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/issues/523')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->get('joomla', 'joomla-platform', 523),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the get method - failure
	 * @expectedException  DomainException
	 */
	public function testGetFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/issues/523')
			->will($this->returnValue($returnData));

		$this->object->get('joomla', 'joomla-platform', 523);
	}

	/**
	 * Tests the getComment method
	 */
	public function testGetComment()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/issues/comments/523')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getComment('joomla', 'joomla-platform', 523),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getComment method - failure
	 * @expectedException  DomainException
	 */
	public function testGetCommentFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/issues/comments/523')
			->will($this->returnValue($returnData));

		$this->object->getComment('joomla', 'joomla-platform', 523);
	}

	/**
	 * Tests the getComments method
	 */
	public function testGetComments()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/issues/523/comments')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getComments('joomla', 'joomla-platform', 523),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getComments method - failure
	 * @expectedException  DomainException
	 */
	public function testGetCommentsFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/issues/523/comments')
			->will($this->returnValue($returnData));

		$this->object->getComments('joomla', 'joomla-platform', 523);
	}

	/**
	 * Tests the getList method
	 */
	public function testGetList()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/issues')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getList(),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getList method - failure
	 * @expectedException  DomainException
	 */
	public function testGetListFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/issues')
			->will($this->returnValue($returnData));

		$this->object->getList();
	}

	/**
	 * Tests the getListByRepository method
	 */
	public function testGetListByRepository()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/issues')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getListByRepository('joomla', 'joomla-platform'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getListByRepository method - failure
	 * @expectedException  DomainException
	 */
	public function testGetListByRepositoryFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/issues')
			->will($this->returnValue($returnData));

		$this->object->getListByRepository('joomla', 'joomla-platform');
	}
}
