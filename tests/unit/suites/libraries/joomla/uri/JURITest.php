<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Uri
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
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
	 * Set up JUri environment depending upon arguments.
	 * Allows to replace the environment for some tests.
	 *
	 * @param string $httpHost
	 * @param bool   $https
	 * @param string $scriptName
	 * @param string $query
	 * @param string $fragment
	 *
	 * @return void
	 *
	 * @since   11.1
	 */
	private function setUpLocal($httpHost, $https, $scriptName, $query = null, $fragment = null)
	{
		JUri::reset();

		$_SERVER['HTTP_HOST'] = $httpHost;
		if ($https)
		{
			$_SERVER['HTTPS'] = 'on';
		}
		else
		{
			unset($_SERVER['HTTPS']);
		}
		$_SERVER['SCRIPT_NAME'] = $scriptName;
		$_SERVER['PHP_SELF'] = $scriptName;
		$_SERVER['REQUEST_URI'] = $scriptName;
		if (!empty($query))
		{
			$_SERVER['REQUEST_URI'] .= '?' . $query;
		}
		if (!empty($fragment))
		{
			$_SERVER['REQUEST_URI'] .= '#' . $fragment;
		}

		$this->object = new JUri;
	}

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
		self::setUpLocal('www.example.com:80', false, '/joomla/index.php', 'var=value 10');
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

		self::setUpLocal('www.example.com', false, '/joomla/index.php', 'var=value 10');
		$this->assertFalse(
			JUri::isInternal('www.myotherexample.com'),
			'www.myotherexample.com should NOT be resolved as internal'
		);

		self::setUpLocal('www.example.com:443', true, '/joomla/index.php', 'var=value 10');
		$this->assertFalse(
			JUri::isInternal('www.myotherexample.com'),
			'www.myotherexample.com should NOT be resolved as internal'
		);

		self::setUpLocal('www.example.com', true, '/joomla/index.php', 'var=value 10');
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

		self::setUpLocal('www.example.com', false, '/joomla/index.php', 'var=value 10');
		$this->assertFalse(
			JUri::isInternal('myotherexample.com'),
			'myotherexample.com should NOT be resolved as internal'
		);

		self::setUpLocal('www.example.com:443', true, '/joomla/index.php', 'var=value 10');
		$this->assertFalse(
			JUri::isInternal('myotherexample.com'),
			'myotherexample.com should NOT be resolved as internal'
		);

		self::setUpLocal('www.example.com', true, '/joomla/index.php', 'var=value 10');
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
			'http://www.myotherexample.com should NOT be resolved as internal'
		);

		self::setUpLocal('www.example.com', false, '/joomla/index.php', 'var=value 10');
		$this->assertFalse(
			JUri::isInternal('http://www.myotherexample.com'),
			'http://www.myotherexample.com should NOT be resolved as internal'
		);

		self::setUpLocal('www.example.com:443', true, '/joomla/index.php', 'var=value 10');
		$this->assertFalse(
			JUri::isInternal('http://www.myotherexample.com'),
			'http://www.myotherexample.com should NOT be resolved as internal'
		);

		self::setUpLocal('www.example.com', true, '/joomla/index.php', 'var=value 10');
		$this->assertFalse(
			JUri::isInternal('http://www.myotherexample.com'),
			'http://www.myotherexample.com should NOT be resolved as internal'
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

		self::setUpLocal('www.example.com', false, '/joomla/index.php', 'var=value 10');
		$this->assertTrue(
			JUri::isInternal('index.php?option=com_something'),
			'index.php?option=com_something should be internal'
		);

		self::setUpLocal('www.example.com:443', true, '/joomla/index.php', 'var=value 10');
		$this->assertTrue(
			JUri::isInternal('index.php?option=com_something'),
			'index.php?option=com_something should be internal'
		);

		self::setUpLocal('www.example.com', true, '/joomla/index.php', 'var=value 10');
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

		self::setUpLocal('www.example.com:443', true, '/joomla/index.php', 'var=value 10');
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
	public function testisInternalWhenInternalWithDomainAndSchemeAndNoPort()
	{
		self::setUpLocal('www.example.com', false, '/joomla/index.php', 'var=value 10');
		$this->assertTrue(
			JUri::isInternal(JUri::base() . 'index.php?option=com_something'),
			JUri::base() . 'index.php?option=com_something should be internal'
		);

		self::setUpLocal('www.example.com', true, '/joomla/index.php', 'var=value 10');
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
		self::setUpLocal('www.example.com:80', false, '/index.php', 'var=value 10');
		$this->assertTrue(
			JUri::isInternal(JUri::base() . 'index.php?option=com_something'),
			JUri::base() . 'index.php?option=com_something should be internal'
		);

		self::setUpLocal('www.example.com:443', true, '/index.php', 'var=value 10');
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
	public function testisInternalWhenInternalWithDomainAndSchemeAndNoPortNoSubFolder()
	{
		self::setUpLocal('www.example.com', false, '/index.php', 'var=value 10');
		$this->assertTrue(
			JUri::isInternal(JUri::base() . 'index.php?option=com_something'),
			JUri::base() . 'index.php?option=com_something should be internal'
		);

		self::setUpLocal('www.example.com', true, '/index.php', 'var=value 10');
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

		self::setUpLocal('www.example.com:443', true, '/joomla/index.php', 'var=value 10');
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
	public function testisInternalWhenNOTInternalWithDomainAndSchemeAndNoPortAndIndex()
	{
		self::setUpLocal('www.example.com', false, '/joomla/index.php', 'var=value 10');
		$this->assertFalse(
			JUri::isInternal('http://www.myotherexample.com/index.php?option=com_something'),
			'http://www.myotherexample.com/index.php?option=com_something should NOT be internal'
		);

		self::setUpLocal('www.example.com', true, '/joomla/index.php', 'var=value 10');
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

		self::setUpLocal('www.example.com', false, '/joomla/index.php', 'var=value 10');
		$this->assertFalse(
			JUri::isInternal('www.myotherexample.com/index.php?option=com_something'),
			'www.myotherexample.comindex.php?option=com_something should NOT be internal'
		);

		self::setUpLocal('www.example.com:443', true, '/joomla/index.php', 'var=value 10');
		$this->assertFalse(
			JUri::isInternal('www.myotherexample.com/index.php?option=com_something'),
			'www.myotherexample.comindex.php?option=com_something should NOT be internal'
		);

		self::setUpLocal('www.example.com', true, '/joomla/index.php', 'var=value 10');
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

		self::setUpLocal('www.example.com', false, '/joomla/index.php', 'var=value 10');
		$this->assertFalse(
			JUri::isInternal('/customDevScript.php'),
			'/customDevScript.php should NOT be internal'
		);

		self::setUpLocal('www.example.com:443', true, '/joomla/index.php', 'var=value 10');
		$this->assertFalse(
			JUri::isInternal('/customDevScript.php'),
			'/customDevScript.php should NOT be internal'
		);

		self::setUpLocal('www.example.com', true, '/joomla/index.php', 'var=value 10');
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

		self::setUpLocal('www.example.com', false, '/joomla/index.php', 'var=value 10');
		$this->assertFalse(
			JUri::isInternal('/customDevScript.php?www.example.com'),
			'/customDevScript.php?www.example.com should NOT be internal'
		);

		self::setUpLocal('www.example.com:443', true, '/joomla/index.php', 'var=value 10');
		$this->assertFalse(
			JUri::isInternal('/customDevScript.php?www.example.com'),
			'/customDevScript.php?www.example.com should NOT be internal'
		);

		self::setUpLocal('www.example.com', true, '/joomla/index.php', 'var=value 10');
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

		self::setUpLocal('www.example.com', false, '/joomla/index.php', 'var=value 10');
		$this->assertFalse(
			JUri::isInternal('www.otherexample.com/www.example.com'),
			'www.otherexample.com/www.example.com should NOT be internal'
		);

		self::setUpLocal('www.example.com:443', true, '/joomla/index.php', 'var=value 10');
		$this->assertFalse(
			JUri::isInternal('www.otherexample.com/www.example.com'),
			'www.otherexample.com/www.example.com should NOT be internal'
		);

		self::setUpLocal('www.example.com', true, '/joomla/index.php', 'var=value 10');
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

		self::setUpLocal('www.example.com:443', true, '/joomla/index.php', 'var=value 10');
		$this->assertTrue(
			JUri::isInternal('www.example.com:443'),
			'www.example.com:443 should be internal'
		);
	}

	/**
	 * Test hardening of JUri::isInternal against non internal links
	 *
	 * @return void
	 *
	 * @covers JUri::isInternal
	 */
	public function testSchemeHostMatchNoPort()
	{
		$this->assertTrue(
			JUri::isInternal('http://www.example.com/joomla/index.php'),
			'http://www.example.com/joomla/index.php should be internal'
		);

		self::setUpLocal('www.example.com', false, '/joomla/index.php', 'var=value 10');
		$this->assertTrue(
			JUri::isInternal('http://www.example.com/joomla/index.php'),
			'http://www.example.com/joomla/index.php should be internal'
		);

		self::setUpLocal('www.example.com:443', true, '/joomla/index.php', 'var=value 10');
		$this->assertTrue(
			JUri::isInternal('https://www.example.com/joomla/index.php'),
			'https://www.example.com/joomla/index.php should be internal'
		);

		self::setUpLocal('www.example.com', true, '/joomla/index.php', 'var=value 10');
		$this->assertTrue(
			JUri::isInternal('https://www.example.com/joomla/index.php'),
			'https://www.example.com/joomla/index.php should be internal'
		);
	}

	/**
	 * Test hardening of JUri::isInternal against non internal links
	 *
	 * @return void
	 *
	 * @covers JUri::isInternal
	 */
	public function testSchemeDifferHostMatchNoPort()
	{
		$this->assertTrue(
			JUri::isInternal('https://www.example.com/joomla/index.php'),
			'https://www.example.com/joomla/index.php should be internal'
		);

		self::setUpLocal('www.example.com', false, '/joomla/index.php', 'var=value 10');
		$this->assertTrue(
			JUri::isInternal('https://www.example.com/joomla/index.php'),
			'https://www.example.com/joomla/index.php should be internal'
		);

		self::setUpLocal('www.example.com:443', true, '/joomla/index.php', 'var=value 10');
		$this->assertTrue(
			JUri::isInternal('http://www.example.com/joomla/index.php'),
			'http://www.example.com/joomla/index.php should be internal'
		);

		self::setUpLocal('www.example.com', true, '/joomla/index.php', 'var=value 10');
		$this->assertTrue(
			JUri::isInternal('http://www.example.com/joomla/index.php'),
			'http://www.example.com/joomla/index.php should be internal'
		);
	}

	/**
	 * Test hardening of JUri::isInternal against non internal links
	 *
	 * @return void
	 *
	 * @covers JUri::isInternal
	 */
	public function testSchemeHostMatchStandardPort()
	{
		$this->assertTrue(
			JUri::isInternal('http://www.example.com:80/joomla/index.php'),
			'http://www.example.com:80/joomla/index.php should be internal'
		);

		self::setUpLocal('www.example.com', false, '/joomla/index.php', 'var=value 10');
		$this->assertTrue(
			JUri::isInternal('http://www.example.com:80/joomla/index.php'),
			'http://www.example.com:80/joomla/index.php should be internal'
		);

		self::setUpLocal('www.example.com:443', true, '/joomla/index.php', 'var=value 10');
		$this->assertTrue(
			JUri::isInternal('https://www.example.com:443/joomla/index.php'),
			'https://www.example.com:443/joomla/index.php should be internal'
		);

		self::setUpLocal('www.example.com', true, '/joomla/index.php', 'var=value 10');
		$this->assertTrue(
			JUri::isInternal('https://www.example.com:443/joomla/index.php'),
			'https://www.example.com:443/joomla/index.php should be internal'
		);
	}

	/**
	 * Test hardening of JUri::isInternal against non internal links
	 *
	 * @return void
	 *
	 * @covers JUri::isInternal
	 */
	public function testSchemeDifferHostMatchStandardPort()
	{
		$this->assertTrue(
			JUri::isInternal('https://www.example.com:443/joomla/index.php'),
			'https://www.example.com:443/joomla/index.php should be internal'
		);

		self::setUpLocal('www.example.com', false, '/joomla/index.php', 'var=value 10');
		$this->assertTrue(
			JUri::isInternal('https://www.example.com:443/joomla/index.php'),
			'https://www.example.com:443/joomla/index.php should be internal'
		);

		self::setUpLocal('www.example.com:443', true, '/joomla/index.php', 'var=value 10');
		$this->assertTrue(
			JUri::isInternal('http://www.example.com:80/joomla/index.php'),
			'http://www.example.com:80/joomla/index.php should be internal'
		);

		self::setUpLocal('www.example.com', true, '/joomla/index.php', 'var=value 10');
		$this->assertTrue(
			JUri::isInternal('http://www.example.com:80/joomla/index.php'),
			'http://www.example.com:80/joomla/index.php should be internal'
		);
	}

	/**
	 * Test hardening of JUri::isInternal against non internal links
	 *
	 * @return void
	 *
	 * @covers JUri::isInternal
	 */
	public function testSchemeHostMatchNonStandardPort()
	{
		$this->assertFalse(
			JUri::isInternal('http://www.example.com:8080/joomla/index.php'),
			'http://www.example.com:8080/joomla/index.php should NOT be internal'
		);

		self::setUpLocal('www.example.com', false, '/joomla/index.php', 'var=value 10');
		$this->assertFalse(
			JUri::isInternal('http://www.example.com:8080/joomla/index.php'),
			'http://www.example.com:8080/joomla/index.php should NOT be internal'
		);

		self::setUpLocal('www.example.com:443', true, '/joomla/index.php', 'var=value 10');
		$this->assertFalse(
			JUri::isInternal('https://www.example.com:8443/joomla/index.php'),
			'https://www.example.com:8443/joomla/index.php should NOT be internal'
		);

		self::setUpLocal('www.example.com', true, '/joomla/index.php', 'var=value 10');
		$this->assertFalse(
			JUri::isInternal('https://www.example.com:8443/joomla/index.php'),
			'https://www.example.com:8443/joomla/index.php should NOT be internal'
		);
	}

	/**
	 * Test hardening of JUri::isInternal against non internal links
	 *
	 * @return void
	 *
	 * @covers JUri::isInternal
	 */
	public function testSchemeDifferHostMatchNonStandardPort()
	{
		$this->assertFalse(
			JUri::isInternal('https://www.example.com:8443/joomla/index.php'),
			'https://www.example.com:8443/joomla/index.php should NOT be internal'
		);

		self::setUpLocal('www.example.com', false, '/joomla/index.php', 'var=value 10');
		$this->assertFalse(
			JUri::isInternal('https://www.example.com:8443/joomla/index.php'),
			'https://www.example.com:8443/joomla/index.php should NOT be internal'
		);

		self::setUpLocal('www.example.com:443', true, '/joomla/index.php', 'var=value 10');
		$this->assertFalse(
			JUri::isInternal('http://www.example.com:8080/joomla/index.php'),
			'http://www.example.com:8080/joomla/index.php should NOT be internal'
		);

		self::setUpLocal('www.example.com', true, '/joomla/index.php', 'var=value 10');
		$this->assertFalse(
			JUri::isInternal('http://www.example.com:8080/joomla/index.php'),
			'http://www.example.com:8080/joomla/index.php should NOT be internal'
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
