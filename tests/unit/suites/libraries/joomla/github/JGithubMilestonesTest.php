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
 * @since       12.3
 */
class JGithubMilestonesTest extends PHPUnit_Framework_TestCase
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
	 * @var    JGithubPulls  Object under test.
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
	 * @access protected
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->options = new JRegistry;
		$this->client = $this->getMock('JGithubHttp', array('get', 'post', 'delete', 'patch', 'put'));

		$this->object = new JGithubMilestones($this->options, $this->client);
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
		$class = new ReflectionClass('JGithubMilestones');
		$method = $class->getMethod($name);
		$method->setAccessible(true);

		return $method;
	}

	/**
	 * Tests the create method
	 *
	 * @return void
	 *
	 * @since  12.3
	 */
	public function testCreate()
	{
		$returnData = new stdClass;
		$returnData->code = 201;
		$returnData->body = $this->sampleString;

		$milestone = new stdClass;
		$milestone->title = 'My Milestone';
		$milestone->state = 'open';
		$milestone->description = 'This milestone is impossible';
		$milestone->due_on = '2012-12-25T20:09:31Z';

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/milestones', json_encode($milestone))
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->create('joomla', 'joomla-platform', 'My Milestone', 'open', 'This milestone is impossible', '2012-12-25T20:09:31Z'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the create method - failure
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
	 *
	 * @since  12.3
	 */
	public function testCreateFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 501;
		$returnData->body = $this->errorString;

		$milestone = new stdClass;
		$milestone->title = 'My Milestone';
		$milestone->state = 'open';
		$milestone->description = 'This milestone is impossible';
		$milestone->due_on = '2012-12-25T20:09:31Z';

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/milestones', json_encode($milestone))
			->will($this->returnValue($returnData));

		$this->object->create('joomla', 'joomla-platform', 'My Milestone', 'open', 'This milestone is impossible', '2012-12-25T20:09:31Z');
	}

	/**
	 * Tests the edit method
	 *
	 * @return void
	 *
	 * @since  12.3
	 */
	public function testEdit()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$milestone = new stdClass;
		$milestone->state = 'closed';

		$this->client->expects($this->once())
			->method('patch')
			->with('/repos/joomla/joomla-platform/milestones/523', json_encode($milestone))
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->edit('joomla', 'joomla-platform', 523, null, 'closed'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the edit method with all parameters
	 *
	 * @return void
	 *
	 * @since  12.3
	 */
	public function testEditAllParameters()
	{

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$milestone = new stdClass;
		$milestone->title = 'This is the revised title.';
		$milestone->state = 'closed';
		$milestone->description = 'This describes it perfectly.';
		$milestone->due_on = '2012-12-25T20:09:31Z';

		$this->client->expects($this->once())
			->method('patch')
			->with('/repos/joomla/joomla-platform/milestones/523', json_encode($milestone))
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->edit('joomla', 'joomla-platform', 523, 'This is the revised title.', 'closed', 'This describes it perfectly.',
				'2012-12-25T20:09:31Z'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the edit method - failure
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
	 *
	 * @since  12.3
	 */
	public function testEditFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$milestone = new stdClass;
		$milestone->state = 'closed';

		$this->client->expects($this->once())
		->method('patch')
		->with('/repos/joomla/joomla-platform/milestones/523', json_encode($milestone))
		->will($this->returnValue($returnData));

		$this->object->edit('joomla', 'joomla-platform', 523, null, 'closed');
	}

	/**
	 * Tests the get method
	 *
	 * @return void
	 *
	 * @since  12.3
	 */
	public function testGet()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/milestones/523')
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
	 *
	 * @since  12.3
	 */
	public function testGetFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/milestones/523')
			->will($this->returnValue($returnData));

		$this->object->get('joomla', 'joomla-platform', 523);
	}

	/**
	 * Tests the getList method
	 *
	 * @return void
	 *
	 * @since  12.3
	 */
	public function testGetList()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('get')
		->with('/repos/joomla/joomla-platform/milestones?state=open&sort=due_date&direction=desc')
		->will($this->returnValue($returnData));

		$this->assertThat(
				$this->object->getList('joomla', 'joomla-platform'),
				$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getList method - failure
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
	 *
	 * @since  12.3
	 */
	public function testGetListFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('get')
		->with('/repos/joomla/joomla-platform/milestones?state=open&sort=due_date&direction=desc')
		->will($this->returnValue($returnData));

		$this->object->getList('joomla', 'joomla-platform');
	}

	/**
	 * Tests the delete method
	 *
	 * @return void
	 *
	 * @since  12.3
	 */
	public function testDelete()
	{
		$returnData = new stdClass;
		$returnData->code = 204;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
		->method('delete')
		->with('/repos/joomla/joomla-platform/milestones/254')
		->will($this->returnValue($returnData));

		$this->object->delete('joomla', 'joomla-platform', 254);
	}

	/**
	 * Tests the delete method - failure
	 *
	 * @expectedException  DomainException
	 *
	 * @return void
	 *
	 * @since  12.3
	 */
	public function testDeleteFailure()
	{
		$returnData = new stdClass;
		$returnData->code = 504;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
		->method('delete')
		->with('/repos/joomla/joomla-platform/milestones/254')
		->will($this->returnValue($returnData));

		$this->object->delete('joomla', 'joomla-platform', 254);
	}
}
