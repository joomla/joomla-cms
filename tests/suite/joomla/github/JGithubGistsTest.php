<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Github
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/github/github.php';
require_once JPATH_PLATFORM.'/joomla/github/http.php';
require_once JPATH_PLATFORM.'/joomla/github/gists.php';

/**
 * Test class for JGithubGists.
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
	 * @var    JGithubGists  Object under test.
	 * @since  11.4
	 */
	protected $object;

	/**
	 * @var    string  Sample JSON string.
	 * @since  11.4
	 */
	protected $sampleString = '{"a":1,"b":2,"c":3,"d":4,"e":5}';

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

		$this->object = new JGithubGists($this->options, $this->client);
	}

	/**
	 * Tests the createComment method
	 */
	public function testCreateComment()
	{
		$returnData = new stdClass;
		$returnData->code = 201;
		$returnData->body = $this->sampleString;

		$gist = new stdClass;
		$gist->body = 'My Insightful Comment';

		$this->client->expects($this->once())
			->method('post')
			->with('/gists/523/comments', json_encode($gist))
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createComment(523, 'My Insightful Comment'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the delete method
	 */
	public function testDelete()
	{
		$returnData = new stdClass;
		$returnData->code = 204;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('delete')
			->with('/gists/254')
			->will($this->returnValue($returnData));

		$this->object->delete(254);
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
			->with('/gists/comments/254')
			->will($this->returnValue($returnData));

		$this->object->deleteComment(254);
	}

	/**
	 * Tests the editComment method
	 */
	public function testEditComment()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$pull = new stdClass;
		$pull->body = 'This comment is now even more insightful';

		$this->client->expects($this->once())
			->method('patch')
			->with('/gists/comments/523', json_encode($pull))
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->editComment(523, 'This comment is now even more insightful'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the fork method
	 */
	public function testFork()
	{
		$returnData = new stdClass;
		$returnData->code = 201;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('post')
			->with('/gists/523/fork')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->fork(523),
			$this->equalTo(json_decode($this->sampleString))
		);
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
			->with('/gists/523')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->get(523),
			$this->equalTo(json_decode($this->sampleString))
		);
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
			->with('/gists/comments/523')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getComment(523),
			$this->equalTo(json_decode($this->sampleString))
		);
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
			->with('/gists/523/comments')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getComments(523),
			$this->equalTo(json_decode($this->sampleString))
		);
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
			->with('/gists')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getList(),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getListByUser method
	 */
	public function testGetListByUser()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/users/joomla/gists')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getListByUser('joomla'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getListPublic method
	 */
	public function testGetListPublic()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/gists/public')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getListPublic(),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getListStarred method
	 */
	public function testGetListStarred()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/gists/starred')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getListStarred(),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the isStarred method when the gist has been starred
	 */
	public function testIsStarredTrue()
	{
		$returnData = new stdClass;
		$returnData->code = 204;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/gists/523/star')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->isStarred(523),
			$this->equalTo(true)
		);
	}

	/**
	 * Tests the isStarred method when the gist has not been starred
	 */
	public function testIsStarredFalse()
	{
		$returnData = new stdClass;
		$returnData->code = 404;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/gists/523/star')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->isStarred(523),
			$this->equalTo(false)
		);
	}

	/**
	 * Tests the star method
	 */
	public function testStar()
	{
		$returnData = new stdClass;
		$returnData->code = 204;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('put')
			->with('/gists/523/star', '')
			->will($this->returnValue($returnData));

		$this->object->star(523);
	}

	/**
	 * Tests the unstar method
	 */
	public function testUnstar()
	{
		$returnData = new stdClass;
		$returnData->code = 204;
		$returnData->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('delete')
			->with('/gists/523/star')
			->will($this->returnValue($returnData));

		$this->object->unstar(523);
	}
}
