<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Router
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JRouterAdministrator.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Router
 * @group       Router
 * @since       3.0
 */
class JRouterAdministratorTest extends TestCase
{
	/**
	 * Backup of the SERVER superglobal
	 *
	 * @var    array
	 * @since  3.1
	 */
	protected $server;

	/**
	 * Class being tested
	 *
	 * @var    JRouterAdministrator
	 * @since  3.0
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->server = $_SERVER;

		$_SERVER['HTTP_HOST']   = 'example.com';
		$_SERVER['SCRIPT_NAME'] = '';

		JUri::reset();

		$this->object = new JRouterAdministrator;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function tearDown()
	{
		$_SERVER = $this->server;
		unset($this->server, $this->object);

		parent::tearDown();
	}

	/**
	 * Tests the parse method
	 *
	 * @return  void
	 * @testdox JRouterAdministrator::parse() returns an array
	 * @since   3.0
	 */
	public function testParse()
	{
		$uri = JUri::getInstance('http://localhost');

		$vars = $this->object->parse($uri);
		$this->assertInternalType('array', $vars);
	}

	/**
	 * Tests the build method
	 *
	 * @return  void
	 * @testdox JRouterAdministrator::build() returns an instance of JUri
	 * @since   3.1
	 */
	public function testBuild()
	{
		$uri = JUri::getInstance('http://localhost/joomla-cms/intro/to/joomla');

		$this->assertInstanceOf('JUri', $this->object->build($uri));

		$this->assertEquals('/joomla-cms/intro/to/joomla', $uri->getPath());
	}
}
