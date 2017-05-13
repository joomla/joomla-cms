<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Uri
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JUri.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Uri
 * @since       11.1
 */
class JUriTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * @var    JUri
	 */
	protected $object;

	/**
	 * Backup of the SERVER superglobal
	 *
	 * @var    array
	 * @since  3.6
	 */
	protected $backupServer;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function setUp()
	{
		parent::setUp();
		$this->backupServer = $_SERVER;
		JUri::reset();

		$_SERVER['HTTP_HOST'] = 'www.example.com:80';
		$_SERVER['SCRIPT_NAME'] = '/joomla/index.php';
		$_SERVER['PHP_SELF'] = '/joomla/index.php';
		$_SERVER['REQUEST_URI'] = '/joomla/index.php?var=value 10';

		$this->object = new JUri;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 *
	 * @see     \PHPUnit\Framework\TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		$_SERVER = $this->backupServer;
		unset($this->backupServer);
		unset($this->object);
		parent::tearDown();
	}

	/**
	 * Test the getInstance method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @covers  JUri::getInstance
	 */
	public function testGetInstance()
	{
		$customUri = JUri::getInstance('http://someuser:somepass@www.example.com:80/path/file.html?var=value#fragment');
		$defaultUri = JUri::getInstance();

		$this->assertNotSame(
			$customUri,
			$defaultUri,
			'JUri::getInstance() should not return the same object for different URIs'
		);
	}

	/**
	 * Test the root method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @covers  JUri::root
	 */
	public function testRoot()
	{
		$this->assertSame(
			JUri::root(false, '/administrator'),
			'http://www.example.com:80/administrator/'
		);
	}

	/**
	 * Test the current method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @covers  JUri::current
	 */
	public function testCurrent()
	{
		$this->assertSame(
			JUri::current(),
			'http://www.example.com:80/joomla/index.php'
		);
	}

	/**
	 * Test the parse method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @covers  JUri::parse
	 */
	public function testParse()
	{
		$this->assertTrue($this->object->parse('http://someuser:somepass@www.example.com:80/path/file.html?var=value&amp;test=true#fragment'));
	}

	/**
	 * Test the buildQuery method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @covers  JUri::buildQuery
	 */
	public function testBuildQuery()
	{
		$params = array(
			'field' => array(
				'price' => array(
					'from' => 5,
					'to' => 10,
				),
				'name' => 'foo'
			),
			'v' => 45);

		$expected = 'field[price][from]=5&field[price][to]=10&field[name]=foo&v=45';
		$this->assertEquals($expected, JUri::buildQuery($params), 'The query string was not built correctly.');
	}

	/**
	 * Test the setPath method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @covers  JUri::setPath
	 */
	public function testSetPath()
	{
		$this->object->setPath('/this/is/a/path/to/a/file.htm');

		$this->assertAttributeSame(
			'/this/is/a/path/to/a/file.htm',
			'path',
			$this->object,
			"The URI's path attribute was not set correctly."
		);
	}

	/**
	 * Test hardening of JUri::isInternal against non internal links
	 *
	 * @return void
	 *
	 * @covers JUri::isInternal
	 */
	public function testparsewhennoschemegiven()
	{
		$this->object->parse('www.myotherexample.com');
		$this->assertFalse(JUri::isInternal('www.myotherexample.com'));
	}

	/**
	 * Test hardening of JUri::isInternal against non internal links
	 *
	 * @return void
	 *
	 * @covers JUri::isInternal
	 */
	public function testsefurl()
	{
		$this->object->parse('/login');
		$this->assertFalse(JUri::isInternal('/login'));
	}

	/**
	 * Test hardening of JUri::isInternal against non internal links
	 *
	 * @return void
	 *
	 * @covers JUri::isInternal
	 */
	public function testisInternalWithNoSchemeAndNotInternal()
	{
		$this->assertFalse(
			JUri::isInternal('www.myotherexample.com'),
			'www.myotherexample.com should NOT be resolved as internal'
		);
	}

	/**
	 * Test hardening of JUri::isInternal against non internal links
	 *
	 * @return void
	 *
	 * @covers JUri::isInternal
	 */
	public function testisInternalWithNoSchemeAndNoHostnameAndNotInternal()
	{
		$this->assertFalse(
			JUri::isInternal('myotherexample.com'),
			'myotherexample.com should NOT be resolved as internal'
		);
	}

	/**
	 * Test hardening of JUri::isInternal against non internal links
	 *
	 * @return void
	 *
	 * @covers JUri::isInternal
	 */
	public function testisInternalWithSchemeAndNotInternal()
	{
		$this->assertFalse(
			JUri::isInternal('http://www.myotherexample.com'),
			'http://www.myotherexample.com should NOT be resolved as  internal'
		);
	}

	/**
	 * Test hardening of JUri::isInternal against non internal links
	 *
	 * @return void
	 *
	 * @covers JUri::isInternal
	 */
	public function testisInternalWhenInternalWithNoDomainOrScheme()
	{
		$this->assertTrue(
			JUri::isInternal('index.php?option=com_something'),
			'index.php?option=com_something should be internal'
		);
	}

	/**
	 * Test hardening of JUri::isInternal against non internal links
	 *
	 * @return void
	 *
	 * @covers JUri::isInternal
	 */
	public function testisInternalWhenInternalWithDomainAndSchemeAndPort()
	{
		$this->assertTrue(
			JUri::isInternal(JUri::base() . 'index.php?option=com_something'),
			JUri::base() . 'index.php?option=com_something should be internal'
		);
	}

	/**
	 * Test hardening of JUri::isInternal against non internal links
	 *
	 * @return void
	 *
	 * @covers JUri::isInternal
	 */
	public function testisInternalWhenInternalWithDomainAndSchemeAndPortNoSubFolder()
	{
		JUri::reset();

		$_SERVER['HTTP_HOST'] = 'www.example.com:80';
		$_SERVER['SCRIPT_NAME'] = '/index.php';
		$_SERVER['PHP_SELF'] = '/index.php';
		$_SERVER['REQUEST_URI'] = '/index.php?var=value 10';

		$this->object = new JUri;

		$this->assertTrue(
			JUri::isInternal(JUri::base() . 'index.php?option=com_something'),
			JUri::base() . 'index.php?option=com_something should be internal'
		);
	}

	/**
	 * Test hardening of JUri::isInternal against non internal links
	 *
	 * @return void
	 *
	 * @covers JUri::isInternal
	 */
	public function testisInternalWhenNOTInternalWithDomainAndSchemeAndPortAndIndex()
	{
		$this->assertFalse(
			JUri::isInternal('http://www.myotherexample.com/index.php?option=com_something'),
			'http://www.myotherexample.com/index.php?option=com_something should NOT be internal'
		);
	}

	/**
	 * Test hardening of JUri::isInternal against non internal links
	 *
	 * @return void
	 *
	 * @covers JUri::isInternal
	 */
	public function testisInternalWhenNOTInternalWithDomainAndNoSchemeAndPortAndIndex()
	{
		$this->assertFalse(
			JUri::isInternal('www.myotherexample.com/index.php?option=com_something'),
			'www.myotherexample.comindex.php?option=com_something should NOT be internal'
		);
	}

	/**
	 * Test hardening of JUri::isInternal against non internal links
	 *
	 * @return void
	 *
	 * @covers JUri::isInternal
	 */
	public function testisInternal3rdPartyDevs()
	{
		$this->assertFalse(
			JUri::isInternal('/customDevScript.php'),
			'/customDevScript.php should NOT be internal'
		);
	}

	/**
	 * Test hardening of JUri::isInternal against non internal links
	 *
	 * @return void
	 *
	 * @covers JUri::isInternal
	 */
	public function testAppendingOfBaseToTheEndOfTheUrl()
	{
		$this->assertFalse(
			JUri::isInternal('/customDevScript.php?www.example.com'),
			'/customDevScript.php?www.example.com should NOT be internal'
		);
	}

	/**
	 * Test hardening of JUri::isInternal against non internal links
	 *
	 * @return void
	 *
	 * @covers JUri::isInternal
	 */
	public function testAppendingOfBaseToTheEndOfTheUrl2()
	{
		$this->assertFalse(
			JUri::isInternal('www.otherexample.com/www.example.com'),
			'www.otherexample.com/www.example.com should NOT be internal'
		);
	}

	/**
	 * Test hardening of JUri::isInternal against non internal links
	 *
	 * @return void
	 *
	 * @covers JUri::isInternal
	 */
	public function testSchemeEmptyButHostAndPortMatch()
	{
		$this->assertTrue(
			JUri::isInternal('www.example.com:80'),
			'www.example.com:80 should be internal'
		);
	}

	/**
	 * Test hardening of JUri::isInternal against non internal links
	 *
	 * @return void
	 *
	 * @covers JUri::isInternal
	 */
	public function testPregMatch()
	{
		$this->assertFalse(
			JUri::isInternal('wwwhexample.com'),
			'wwwhexample.com should NOT be internal'
		);
	}
}
