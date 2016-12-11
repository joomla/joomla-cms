<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Github
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JGithubGists.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Github
 *
 * @since       11.1
 */
class JGithubGistsTest extends PHPUnit_Framework_TestCase
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
	 * @var    JGithubPackageGists  Object under test.
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
		$this->client = $this->getMockBuilder('JGithubHttp')->setMethods(array('get', 'post', 'delete', 'patch', 'put'))->getMock();
		$this->response = $this->getMockBuilder('JHttpResponse')->getMock();

		$this->object = new JGithubPackageGists($this->options, $this->client);
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
		$returnData = new stdClass;
		$returnData->code = 201;
		$returnData->body = $this->sampleString;

		// Build the request data.
		$data = json_encode(
			array(
				'files' => array(
					'file2.txt' => array('content' => 'This is the second file')
				),
				'public' => true,
				'description' => 'This is a gist'
			)
		);

		$this->client->expects($this->once())
			->method('post')
			->with('/gists', $data)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->create(
				array(
					'file2.txt' => 'This is the second file'
				),
				true,
				'This is a gist'
			),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the create method loading file content from a file
	 *
	 * @return void
	 */
	public function testCreateGistFromFile()
	{
		$returnData = new stdClass;
		$returnData->code = 201;
		$returnData->body = $this->sampleString;

		// Build the request data.
		$data = json_encode(
			array(
				'files' => array(
					'gittest' => array('content' => 'GistContent' . PHP_EOL)
				),
				'public' => true,
				'description' => 'This is a gist'
			)
		);

		$this->client->expects($this->once())
			->method('post')
			->with('/gists', $data)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->create(
				array(
					JPATH_TEST_STUBS . '/gittest'
				),
				true,
				'This is a gist'
			),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the create method loading file content from a file - file does not exist
	 *
	 * @expectedException InvalidArgumentException
	 *
	 * @return void
	 */
	public function testCreateGistFromFileNotFound()
	{
		$returnData = new stdClass;
		$returnData->code = 501;
		$returnData->body = $this->sampleString;

		$this->object->create(
			array(
				JPATH_BASE . '/gittest_notfound'
			),
			true,
			'This is a gist'
		);
	}

	/**
	 * Tests the create method
	 *
	 * @return void
	 */
	public function testCreateFailure()
	{
		$exception = false;

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Build the request data.
		$data = json_encode(
			array('files' => array(), 'public' => true, 'description' => 'This is a gist')
		);

		$this->client->expects($this->once())
			->method('post')
			->with('/gists', $data)
			->will($this->returnValue($returnData));

		try
		{
			$this->object->create(array(), true, 'This is a gist');
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
	 * Tests the createComment method - simulated failure
	 *
	 * @return void
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
	 * Tests the createComment method - simulated failure
	 *
	 * @return void
	 */
	public function testCreateCommentFailure()
	{
		$exception = false;

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$gist = new stdClass;
		$gist->body = 'My Insightful Comment';

		$this->client->expects($this->once())
			->method('post')
			->with('/gists/523/comments', json_encode($gist))
			->will($this->returnValue($returnData));

		try
		{
			$this->object->createComment(523, 'My Insightful Comment');
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
	 * Tests the delete method
	 *
	 * @return void
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
	 * Tests the delete method - simulated failure
	 *
	 * @return void
	 */
	public function testDeleteFailure()
	{
		$exception = false;

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('delete')
			->with('/gists/254')
			->will($this->returnValue($returnData));

		try
		{
			$this->object->delete(254);
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
			->with('/gists/comments/254')
			->will($this->returnValue($returnData));

		$this->object->deleteComment(254);
	}

	/**
	 * Tests the deleteComment method - simulated failure
	 *
	 * @return void
	 */
	public function testDeleteCommentFailure()
	{
		$exception = false;

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('delete')
			->with('/gists/comments/254')
			->will($this->returnValue($returnData));

		try
		{
			$this->object->deleteComment(254);
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
	 * Tests the edit method
	 *
	 * @return void
	 */
	public function testEdit()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Build the request data.
		$data = json_encode(
			array(
				'description' => 'This is a gist',
				'public' => true,
				'files' => array(
					'file1.txt' => array('content' => 'This is the first file'),
					'file2.txt' => array('content' => 'This is the second file')
				)
			)
		);

		$this->client->expects($this->once())
			->method('patch')
			->with('/gists/512', $data)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->edit(
				512,
				array(
					'file1.txt' => 'This is the first file',
					'file2.txt' => 'This is the second file'
				),
				true,
				'This is a gist'
			),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the edit method - simulated failure
	 *
	 * @return void
	 */
	public function testEditFailure()
	{
		$exception = false;

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Build the request data.
		$data = json_encode(
			array(
				'description' => 'This is a gist',
				'public' => true,
				'files' => array(
					'file1.txt' => array('content' => 'This is the first file'),
					'file2.txt' => array('content' => 'This is the second file')
				)
			)
		);

		$this->client->expects($this->once())
			->method('patch')
			->with('/gists/512', $data)
			->will($this->returnValue($returnData));

		try
		{
			$this->object->edit(
				512,
				array(
					'file1.txt' => 'This is the first file',
					'file2.txt' => 'This is the second file'
				),
				true,
				'This is a gist'
			);
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
	 * Tests the editComment method
	 *
	 * @return void
	 */
	public function testEditComment()
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$gist = new stdClass;
		$gist->body = 'This comment is now even more insightful';

		$this->client->expects($this->once())
			->method('patch')
			->with('/gists/comments/523', json_encode($gist))
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->editComment(523, 'This comment is now even more insightful'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the editComment method - simulated failure
	 *
	 * @return void
	 */
	public function testEditCommentFailure()
	{
		$exception = false;

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$gist = new stdClass;
		$gist->body = 'This comment is now even more insightful';

		$this->client->expects($this->once())
			->method('patch')
			->with('/gists/comments/523', json_encode($gist))
			->will($this->returnValue($returnData));

		try
		{
			$this->object->editComment(523, 'This comment is now even more insightful');
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
	 * Tests the fork method
	 *
	 * @return void
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
	 * Tests the fork method - simulated failure
	 *
	 * @return void
	 */
	public function testForkFailure()
	{
		$exception = false;

		$returnData = new stdClass;
		$returnData->code = 501;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('post')
			->with('/gists/523/fork')
			->will($this->returnValue($returnData));

		try
		{
			$this->object->fork(523);
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
			->with('/gists/523')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->get(523),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the get method - simulated failure
	 *
	 * @return void
	 */
	public function testGetFailure()
	{
		$exception = false;

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/gists/523')
			->will($this->returnValue($returnData));

		try
		{
			$this->object->get(523);
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
			->with('/gists/comments/523')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getComment(523),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getComment method - simulated failure
	 *
	 * @return void
	 */
	public function testGetCommentFailure()
	{
		$exception = false;

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/gists/comments/523')
			->will($this->returnValue($returnData));

		try
		{
			$this->object->getComment(523);
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
			->with('/gists/523/comments')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getComments(523),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getComments method - simulated failure
	 *
	 * @return void
	 */
	public function testGetCommentsFailure()
	{
		$exception = false;

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/gists/523/comments')
			->will($this->returnValue($returnData));

		try
		{
			$this->object->getComments(523);
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
			->with('/gists')
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getList(),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getList method - simulated failure
	 *
	 * @return void
	 */
	public function testGetListFailure()
	{
		$exception = false;

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/gists')
			->will($this->returnValue($returnData));

		try
		{
			$this->object->getList();
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
	 * Tests the getListByUser method
	 *
	 * @return void
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
	 * Tests the getListByUser method - simulated failure
	 *
	 * @return void
	 */
	public function testGetListByUserFailure()
	{
		$exception = false;

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/users/joomla/gists')
			->will($this->returnValue($returnData));

		try
		{
			$this->object->getListByUser('joomla');
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
	 * Tests the getListPublic method
	 *
	 * @return void
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
	 * Tests the getListPublic method - simulated failure
	 *
	 * @return void
	 */
	public function testGetListPublicFailure()
	{
		$exception = false;

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/gists/public')
			->will($this->returnValue($returnData));

		try
		{
			$this->object->getListPublic();
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
	 * Tests the getListStarred method
	 *
	 * @return void
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
	 * Tests the getListStarred method - simulated failure
	 *
	 * @return void
	 */
	public function testGetListStarredFailure()
	{
		$exception = false;

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/gists/starred')
			->will($this->returnValue($returnData));

		try
		{
			$this->object->getListStarred();
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
	 * Tests the isStarred method when the gist has been starred
	 *
	 * @return void
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
	 *
	 * @return void
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
	 * Tests the isStarred method expecting a failure response
	 *
	 * @return void
	 */
	public function testIsStarredFailure()
	{
		$exception = false;

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/gists/523/star')
			->will($this->returnValue($returnData));

		try
		{
			$this->object->isStarred(523);
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
	 * Tests the star method
	 *
	 * @return void
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
	 * Tests the star method - simulated failure
	 *
	 * @return void
	 */
	public function testStarFailure()
	{
		$exception = false;

		$returnData = new stdClass;
		$returnData->code = 504;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('put')
			->with('/gists/523/star', '')
			->will($this->returnValue($returnData));

		try
		{
			$this->object->star(523);
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
	 * Tests the unstar method
	 *
	 * @return void
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

	/**
	 * Tests the unstar method - simulated failure
	 *
	 * @return void
	 */
	public function testUnstarFailure()
	{
		$exception = false;

		$returnData = new stdClass;
		$returnData->code = 504;
		$returnData->body = $this->errorString;

		$this->client->expects($this->once())
			->method('delete')
			->with('/gists/523/star')
			->will($this->returnValue($returnData));

		try
		{
			$this->object->unstar(523);
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
}
