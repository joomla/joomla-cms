<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Github
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JGithubPackageGitignore.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Github
 *
 * @since       ¿
 */
class JGithubPackageGitignoreTest extends PHPUnit_Framework_TestCase
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
	 * @var JGithubPackageGitignore
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @since   ¿
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->options  = new JRegistry;
		$this->client   = $this->getMockBuilder('JGithubHttp')->setMethods(array('get', 'post', 'delete', 'patch', 'put'))->getMock();
		$this->response = $this->getMockBuilder('JHttpResponse')->getMock();

		$this->object = new JGithubPackageGitignore($this->options, $this->client);
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
		unset($this->options, $this->client, $this->response, $this->object);
		parent::tearDown();
	}

	/**
	 * Tests the getList method.
	 *
	 * @return  void
	 *
	 * @since   ¿
	 */
	public function testGetList()
	{
		$this->response->code = 200;
		$this->response->body = '[
    "Actionscript",
    "Android",
    "AppceleratorTitanium",
    "Autotools",
    "Bancha",
    "C",
    "C++"
    ]';

		$this->client->expects($this->once())
			->method('get')
			->with('/gitignore/templates', 0, 0)
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->getList(),
			$this->equalTo(json_decode($this->response->body))
		);
	}

	/**
	 * Tests the get method.
	 *
	 * @return  void
	 *
	 * @since   ¿
	 */
	public function testGet()
	{
		$this->response->code = 200;
		$this->response->body = '{
    "name": "C",
    "source": "# Object files\n*.o\n\n# Libraries\n*.lib\n*.a\n\n# Shared objects (inc. Windows DLLs)\n*.dll\n*.so\n*.so.*\n*.dylib\n\n# Executables\n*.exe\n*.out\n*.app\n"
    }';

		$this->client->expects($this->once())
			->method('get')
			->with('/gitignore/templates/C', array(), 0)
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->get('C'),
			$this->equalTo(json_decode($this->response->body))
		);

	}

	/**
	 * Tests the get method with raw return data.
	 *
	 * @return  void
	 *
	 * @since   ¿
	 */
	public function testGetRaw()
	{
		$this->response->code = 200;
		$this->response->body = '# Object files
     *.o

    # Libraries
     *.lib
     *.a

    # Shared objects (inc. Windows DLLs)
     *.dll
     *.so
     *.so.*
     *.dylib

    # Executables
     *.exe
     *.out
     *.app
';

		$this->client->expects($this->once())
			->method('get')
			->with('/gitignore/templates/C', array('Accept' => 'application/vnd.github.raw+json'), 0)
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->get('C', true),
			$this->equalTo($this->response->body)
		);
	}

	/**
	 * Tests the get method with failure.
	 *
	 * @expectedException DomainException
	 *
	 * @since   ¿
	 * @return  void
	 */
	public function testGetFailure()
	{
		$this->response->code = 404;
		$this->response->body = '{"message":"Not found"}';

		$this->client->expects($this->once())
			->method('get')
			->with('/gitignore/templates/X', array(), 0)
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->get('X'),
			$this->equalTo(json_decode($this->response->body))
		);
	}

}
