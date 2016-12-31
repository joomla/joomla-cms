<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JLoader::register('HtmlView', __DIR__ . '/stubs/thtml.php');
JLoader::register('JModelMock', __DIR__ . '/mocks/JModelMock.php');

/**
 * Tests for the JViewHtml class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  View
 * @since       12.1
 */
class JViewHtmlTest extends TestCase
{
	/**
	 * @var    JViewHtml
	 * @since  12.1
	 */
	private $_instance;

	/**
	 * Tests the __construct method.
	 *
	 * @return  void
	 *
	 * @covers  JViewHtml::__construct
	 * @since   12.1
	 */
	public function test__construct()
	{
		$this->assertAttributeEquals(new SplPriorityQueue, 'paths', $this->_instance, 'Check default paths.');

		$model = JModelMock::create($this);
		$paths = new SplPriorityQueue;
		$paths->insert('foo', 1);

		$this->_instance = new HtmlView($model, $paths);
		$this->assertAttributeSame($paths, 'paths', $this->_instance, 'Check default paths.');
	}

	/**
	 * Tests the __toString method.
	 *
	 * @return  void
	 *
	 * @covers  JViewHtml::__toString
	 * @since   12.1
	 */
	public function test__toString()
	{
		// Set up a priority queue.
		$paths = $this->_instance->getPaths();
		$paths->insert(__DIR__ . '/layouts1', 1);

		$this->_instance->setLayout('olivia');
		$this->assertEquals($this->_instance->setLayout('olivia'), (string) $this->_instance);
	}

	/**
	 * Tests the escape method.
	 *
	 * @return  void
	 *
	 * @covers  JViewHtml::escape
	 * @since   12.1
	 */
	public function testEscape()
	{
		$this->assertEquals('&quot;', $this->_instance->escape('"'));
	}

	/**
	 * Tests the getLayout method.
	 *
	 * @return  void
	 *
	 * @covers  JViewHtml::getLayout
	 * @since   12.1
	 */
	public function testGetLayout()
	{
		TestReflection::setValue($this->_instance, 'layout', 'foo');

		$this->assertEquals('foo', $this->_instance->getLayout());
	}

	/**
	 * Tests the getPath method.
	 *
	 * @return  void
	 *
	 * @covers  JViewHtml::getPath
	 * @since   12.1
	 */
	public function testGetPath()
	{
		// Set up a priority queue.
		$paths = $this->_instance->getPaths();
		$paths->insert(__DIR__ . '/layouts1', 1);
		$paths->insert(__DIR__ . '/layouts2', 2);

		// Use of realpath to ensure test works for on all platforms
		$this->assertEquals(realpath(__DIR__ . '/layouts2/olivia.php'), $this->_instance->getPath('olivia'));
		$this->assertEquals(realpath(__DIR__ . '/layouts1/peter.php'), $this->_instance->getPath('peter'));
		$this->assertEquals(realpath(__DIR__ . '/layouts2/fauxlivia.php'), $this->_instance->getPath('fauxlivia'));
		$this->assertEquals(realpath(__DIR__ . '/layouts1/fringe/division.php'), $this->_instance->getPath('fringe/division'));

		// $this->assertEquals(realpath(__DIR__ . '/layouts1/astrid.phtml'), $this->_instance->getPath('astrid', 'phtml'));
		$this->assertFalse($this->_instance->getPath('walter'));

		// Check dirty path.
		$this->assertEquals(realpath(__DIR__ . '/layouts1/fringe/division.php'), $this->_instance->getPath('fringe//\\division'));
	}

	/**
	 * Tests the getPaths method.
	 *
	 * @return  void
	 *
	 * @covers  JViewHtml::getPaths
	 * @since   12.1
	 */
	public function testGetPaths()
	{
		// Inject a known value into the property.
		TestReflection::setValue($this->_instance, 'paths', 'paths');

		// Check dirty path.
		$this->assertEquals('paths', $this->_instance->getPaths());
	}

	/**
	 * Tests the render method.
	 *
	 * @return  void
	 *
	 * @covers  JViewHtml::render
	 * @since   12.1
	 */
	public function testRender()
	{
		// Set up a priority queue.
		$paths = $this->_instance->getPaths();
		$paths->insert(__DIR__ . '/layouts1', 1);
		$paths->insert(__DIR__ . '/layouts2', 2);

		$this->_instance->setLayout('olivia');
		$this->assertEquals('Peter\'s Olivia', $this->_instance->render());
	}

	/**
	 * Tests the render method.
	 *
	 * @return  void
	 *
	 * @covers  JViewHtml::render
	 * @since   12.1
	 *
	 * @expectedException  RuntimeException
	 */
	public function testRender_exception()
	{
		$this->_instance->render();
	}

	/**
	 * Tests the setLayout method.
	 *
	 * @return  void
	 *
	 * @covers  JViewHtml::setLayout
	 * @since   12.1
	 */
	public function testSetLayout()
	{
		$result = $this->_instance->setLayout('fringe/division');
		$this->assertAttributeSame('fringe/division', 'layout', $this->_instance);
		$this->assertSame($this->_instance, $result);
	}

	/**
	 * Tests the setPaths method.
	 *
	 * @return  void
	 *
	 * @covers  JViewHtml::setPaths
	 * @since   12.1
	 */
	public function testSetPaths()
	{
		$paths = new SplPriorityQueue;
		$paths->insert('bar', 99);

		$result = $this->_instance->setPaths($paths);
		$this->assertAttributeSame($paths, 'paths', $this->_instance);
		$this->assertSame($this->_instance, $result);
	}

	/**
	 * Tests the loadPaths method.
	 *
	 * @return  void
	 *
	 * @covers  JViewHtml::loadPaths
	 * @since   12.1
	 */
	public function testLoadPaths()
	{
		$this->assertEquals(new SplPriorityQueue, TestReflection::invoke($this->_instance, 'loadPaths'));
	}

	/**
	 * Setup the tests.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function setUp()
	{
		parent::setUp();

		$model = JModelMock::create($this);

		$this->_instance = new HtmlView($model);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		unset($this->_instance);
		parent::tearDown();
	}
}
