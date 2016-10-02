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
	 * @var    JHttpResponse  Mock response object.
	 * @since  12.3
	 */
	protected $response;

	/**
	 * @var    JGithubPackageIssues  Object under test.
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

		$this->object = new JGithubPackageIssues($this->options, $this->client);
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
		unset($this->response);
		unset($this->object);
		parent::tearDown();
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

		$issue = new stdClass;
		$issue->title = 'My issue';
		$issue->assignee = 'JoeUser';
		$issue->milestone = '11.5';
		$issue->labels = array();
		$issue->body = 'These are my changes - please review them';

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/issues', json_encode($issue))
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->create('joomla', 'joomla-platform', 'My issue', 'These are my changes - please review them', 'JoeUser', '11.5', array()),
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

		$issue = new stdClass;
		$issue->title = 'My issue';
		$issue->assignee = 'JoeUser';
		$issue->milestone = '11.5';
		$issue->labels = array();
		$issue->body = 'These are my changes - please review them';

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/issues', json_encode($issue))
			->will($this->returnValue($this->response));

		$this->object->create('joomla', 'joomla-platform', 'My issue', 'These are my changes - please review them', 'JoeUser', '11.5', array());
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

		$issue = new stdClass;
		$issue->body = 'My Insightful Comment';

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/issues/523/comments', json_encode($issue))
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->createComment('joomla', 'joomla-platform', 523, 'My Insightful Comment'),
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

		$issue = new stdClass;
		$issue->body = 'My Insightful Comment';

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/issues/523/comments', json_encode($issue))
			->will($this->returnValue($this->response));

		$this->object->createComment('joomla', 'joomla-platform', 523, 'My Insightful Comment');
	}

	/**
	 * Tests the createLabel method
	 *
	 * @return void
	 */
	public function testCreateLabel()
	{
		$this->response->code = 201;
		$this->response->body = $this->sampleString;

		$issue = new stdClass;
		$issue->name = 'My Insightful Label';
		$issue->color = 'My Insightful Color';

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/labels', json_encode($issue))
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->createLabel('joomla', 'joomla-platform', 'My Insightful Label', 'My Insightful Color'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createLabel method - failure
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
	 */
	public function testCreateLabelFailure()
	{
		$this->response->code = 501;
		$this->response->body = $this->errorString;

		$issue = new stdClass;
		$issue->name = 'My Insightful Label';
		$issue->color = 'My Insightful Color';

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/labels', json_encode($issue))
			->will($this->returnValue($this->response));

		$this->object->createLabel('joomla', 'joomla-platform', 'My Insightful Label', 'My Insightful Color');
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
			->with('/repos/joomla/joomla-platform/issues/comments/254')
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
			->with('/repos/joomla/joomla-platform/issues/comments/254')
			->will($this->returnValue($this->response));

		$this->object->deleteComment('joomla', 'joomla-platform', 254);
	}

	/**
	 * Tests the deleteLabel method
	 *
	 * @return void
	 */
	public function testDeleteLabel()
	{
		$this->response->code = 204;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('delete')
			->with('/repos/joomla/joomla-platform/labels/254')
			->will($this->returnValue($this->response));

		$this->object->deleteLabel('joomla', 'joomla-platform', 254);
	}

	/**
	 * Tests the deleteLabel method - failure
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
	 */
	public function testDeleteLabelFailure()
	{
		$this->response->code = 504;
		$this->response->body = $this->errorString;

		$this->client->expects($this->once())
			->method('delete')
			->with('/repos/joomla/joomla-platform/labels/254')
			->will($this->returnValue($this->response));

		$this->object->deleteLabel('joomla', 'joomla-platform', 254);
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
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->edit('joomla', 'joomla-platform', 523, 'Closed', 'My issue', 'These are my changes - please review them',
				'JoeAssignee', '12.2', array('Fixed')
			),
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

		$issue = new stdClass;
		$issue->title = 'My issue';
		$issue->body = 'These are my changes - please review them';
		$issue->state = 'Closed';

		$this->client->expects($this->once())
			->method('patch')
			->with('/repos/joomla/joomla-platform/issues/523', json_encode($issue))
			->will($this->returnValue($this->response));

		$this->object->edit('joomla', 'joomla-platform', 523, 'Closed', 'My issue', 'These are my changes - please review them');
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

		$issue = new stdClass;
		$issue->body = 'This comment is now even more insightful';

		$this->client->expects($this->once())
			->method('patch')
			->with('/repos/joomla/joomla-platform/issues/comments/523', json_encode($issue))
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

		$issue = new stdClass;
		$issue->body = 'This comment is now even more insightful';

		$this->client->expects($this->once())
			->method('patch')
			->with('/repos/joomla/joomla-platform/issues/comments/523', json_encode($issue))
			->will($this->returnValue($this->response));

		$this->object->editComment('joomla', 'joomla-platform', 523, 'This comment is now even more insightful');
	}

	/**
	 * Tests the editLabel method
	 *
	 * @return void
	 */
	public function testEditLabel()
	{
		$this->response->code = 200;
		$this->response->body = $this->sampleString;

		$issue = new stdClass;
		$issue->name = 'This label is now even more insightful';
		$issue->color = 'This color is now even more insightful';

		$this->client->expects($this->once())
			->method('patch')
			->with('/repos/joomla/joomla-platform/labels/523', json_encode($issue))
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->editLabel('joomla', 'joomla-platform', 523, 'This label is now even more insightful', 'This color is now even more insightful'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the editLabel method - failure
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
	 */
	public function testEditLabelFailure()
	{
		$this->response->code = 500;
		$this->response->body = $this->errorString;

		$issue = new stdClass;
		$issue->name = 'This label is now even more insightful';
		$issue->color = 'This color is now even more insightful';

		$this->client->expects($this->once())
			->method('patch')
			->with('/repos/joomla/joomla-platform/labels/523', json_encode($issue))
			->will($this->returnValue($this->response));

		$this->object->editLabel('joomla', 'joomla-platform', 523, 'This label is now even more insightful', 'This color is now even more insightful');
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
			->with('/repos/joomla/joomla-platform/issues/523')
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
			->with('/repos/joomla/joomla-platform/issues/523')
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
			->with('/repos/joomla/joomla-platform/issues/comments/523')
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
			->with('/repos/joomla/joomla-platform/issues/comments/523')
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
			->with('/repos/joomla/joomla-platform/issues/523/comments')
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
			->with('/repos/joomla/joomla-platform/issues/523/comments')
			->will($this->returnValue($this->response));

		$this->object->getComments('joomla', 'joomla-platform', 523);
	}

	/**
	 * Tests the getLabel method
	 *
	 * @return void
	 */
	public function testGetLabel()
	{
		$this->response->code = 200;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/labels/My Insightful Label')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->getLabel('joomla', 'joomla-platform', 'My Insightful Label'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getLabel method - failure
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
	 */
	public function testGetLabelFailure()
	{
		$this->response->code = 500;
		$this->response->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/labels/My Insightful Label')
			->will($this->returnValue($this->response));

		$this->object->getLabel('joomla', 'joomla-platform', 'My Insightful Label');
	}

	/**
	 * Tests the getLabels method
	 *
	 * @return void
	 */
	public function testGetLabels()
	{
		$this->response->code = 200;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/labels')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->getLabels('joomla', 'joomla-platform'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getLabels method - failure
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
	 */
	public function testGetLabelsFailure()
	{
		$this->response->code = 500;
		$this->response->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/labels')
			->will($this->returnValue($this->response));

		$this->object->getLabels('joomla', 'joomla-platform');
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
			->with('/issues')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->getList(),
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
			->with('/issues')
			->will($this->returnValue($this->response));

		$this->object->getList();
	}

	/**
	 * Tests the getListByRepository method
	 *
	 * @return void
	 */
	public function testGetListByRepository()
	{
		$this->response->code = 200;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/issues')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->getListByRepository('joomla', 'joomla-platform'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getListByRepository method with all parameters
	 *
	 * @return void
	 */
	public function testGetListByRepositoryAll()
	{
		$date = new JDate('January 1, 2012 12:12:12');

		$this->response->code = 200;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with(
				'/repos/joomla/joomla-platform/issues?milestone=25&state=closed&assignee=none&' .
				'mentioned=joomla-jenkins&labels=bug&sort=created&direction=asc&since=2012-01-01T12:12:12+00:00'
			)
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->getListByRepository(
				'joomla',
				'joomla-platform',
				'25',
				'closed',
				'none',
				'joomla-jenkins',
				'bug',
				'created',
				'asc',
				$date
			),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getListByRepository method - failure
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
	 */
	public function testGetListByRepositoryFailure()
	{
		$this->response->code = 500;
		$this->response->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/issues')
			->will($this->returnValue($this->response));

		$this->object->getListByRepository('joomla', 'joomla-platform');
	}
}
