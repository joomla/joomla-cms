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
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->options = new JRegistry;
		$this->client = $this->getMock('JGithubHttp', array('get', 'post', 'delete', 'patch', 'put'));

		$this->object = new JGithubIssues($this->options, $this->client);
	}

	/**
	 * Tests the create method
	 *
	 * @return void
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
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
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
	 *
	 * @return void
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
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
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
	 * Tests the createLabel method
	 *
	 * @return void
	 */
	public function testCreateLabel()
	{
		$returnData = new stdClass;
		$returnData->code = 201;
		$returnData->body = $this->sampleString;

		$issue = new stdClass;
		$issue->name = 'My Insightful Label';
		$issue->color = 'My Insightful Color';

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/labels', json_encode($issue))
			->will($this->returnValue($returnData));

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
		$returnData = new stdClass;
		$returnData->code = 501;
		$returnData->body = $this->errorString;

		$issue = new stdClass;
		$issue->name = 'My Insightful Label';
		$issue->color = 'My Insightful Color';

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/labels', json_encode($issue))
			->will($this->returnValue($returnData));

		$this->object->createLabel('joomla', 'joomla-platform', 'My Insightful Label', 'My Insightful Color');
	}

	/**
	 * Tests the deleteComment method
	 *
	 * @return void
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
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
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
	 * Tests the deleteLabel method
	 *
	 * @return void
	 */
	public function testDeleteLabel()
	{
		$returnData = new stdClass;
		$returnData->code = 204;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('delete')
			->with('/repos/joomla/joomla-platform/labels/254')
			->will($this->returnValue($returnData));

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
		$returnData = new stdClass;
		$returnData->code = 504;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('delete')
			->with('/repos/joomla/joomla-platform/labels/254')
			->will($this->returnValue($returnData));

		$this->object->deleteLabel('joomla', 'joomla-platform', 254);
	}

	/**
	 * Tests the edit method
	 *
	 * @return void
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
	 *
	 * @return void
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
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
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
	 * Tests the editLabel method
	 *
	 * @return void
	 */
	public function testEditLabel()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$issue = new stdClass;
		$issue->name = 'This label is now even more insightful';
		$issue->color = 'This color is now even more insightful';

		$this->client->expects($this->once())
			->method('patch')
			->with('/repos/joomla/joomla-platform/labels/523', json_encode($issue))
			->will($this->returnValue($returnData));

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
		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$issue = new stdClass;
		$issue->name = 'This label is now even more insightful';
		$issue->color = 'This color is now even more insightful';

		$this->client->expects($this->once())
			->method('patch')
			->with('/repos/joomla/joomla-platform/labels/523', json_encode($issue))
			->will($this->returnValue($returnData));

		$this->object->editLabel('joomla', 'joomla-platform', 523, 'This label is now even more insightful', 'This color is now even more insightful');
	}

	/**
	 * Tests the get method
	 *
	 * @return void
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
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
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
	 *
	 * @return void
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
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
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
	 *
	 * @return void
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
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
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
	 * Tests the getLabel method
	 *
	 * @return void
	 */
	public function testGetLabel()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/labels/My Insightful Label')
			->will($this->returnValue($returnData));

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
		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/labels/My Insightful Label')
			->will($this->returnValue($returnData));

		$this->object->getLabel('joomla', 'joomla-platform', 'My Insightful Label');
	}

	/**
	 * Tests the getLabels method
	 *
	 * @return void
	 */
	public function testGetLabels()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/labels')
			->will($this->returnValue($returnData));

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
		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/labels')
			->will($this->returnValue($returnData));

		$this->object->getLabels('joomla', 'joomla-platform');
	}

	/**
	 * Tests the getList method
	 *
	 * @return void
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
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
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
	 *
	 * @return void
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
	 * Tests the getListByRepository method with all parameters
	 *
	 * @return void
	 */
	public function testGetListByRepositoryAll()
	{
		$date = new JDate('January 1, 2012 12:12:12');
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with(
				'/repos/joomla/joomla-platform/issues?milestone=25&state=closed&assignee=none&' .
				'mentioned=joomla-jenkins&labels=bug&sort=created&direction=asc&since=2012-01-01T12:12:12+00:00'
			)
			->will($this->returnValue($returnData));

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
