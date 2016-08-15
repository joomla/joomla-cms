<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Component
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/stubs/componentrouter.php';

/**
 * Test class for JComponentRouterLegacy.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Component
 * @since       3.4
 */
class JComponentRouterLegacyTest extends TestCase
{
	/**
	 * Object under test
	 *
	 * @var    JComponentRouterLegacy
	 * @since  3.4
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->object = new JComponentRouterLegacy('Comtest');
	}

	/**
	 * Test JComponentRouterLegacy::__construct
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @covers  JComponentRouterLegacy::__construct
	 */
	public function testConstruct()
	{
		$this->assertInstanceOf('JComponentRouterInterface', $this->object);
		$this->assertInstanceOf('JComponentRouterLegacy', $this->object);
	}

	/**
	 * Test JComponentRouterLegacy::preprocess
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @covers  JComponentRouterLegacy::preprocess
	 */
	public function testPreprocess()
	{
		$this->assertEquals(array(), $this->object->preprocess(array()));
		$this->assertEquals(
			array('option' => 'com_test', 'view' => 'test'),
			$this->object->preprocess(array('option' => 'com_test', 'view' => 'test'))
		);
	}

	/**
	 * Test JComponentRouterLegacy::build
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @covers  JComponentRouterLegacy::build
	 */
	public function testBuild()
	{
		$array = array();
		$this->assertEquals($array,	$this->object->build($array));
		$query = array('option' => 'com_test');
		$segments = array('option-com_test');
		$this->assertEquals($segments, $this->object->build($query));
		$query = array('option' => 'com_test', '42' => 'test-test');
		$segments = array('option-com_test', '42-test-test');
		$this->assertEquals($segments, $this->object->build($query));
		$object = new JComponentRouterLegacy('fake');
		$query = array('option' => 'com_test', '42' => 'test-test');
		$this->assertEquals(array(), $object->build($query));
	}

	/**
	 * Test JComponentRouterLegacy::parse
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @covers  JComponentRouterLegacy::parse
	 */
	public function testParse()
	{
		$array = array();
		$this->assertEquals($array,	$this->object->parse($array));
		$query = array('option' => 'com_test');
		$segments = array('option-com_test');
		$this->assertEquals($query, $this->object->parse($segments));
		$query = array('option' => 'com_test', '42' => 'test-test');
		$segments = array('option-com_test', '42-test-test');
		$this->assertEquals($query, $this->object->parse($segments));
		$object = new JComponentRouterLegacy('fake');
		$segments = array('option-com_test', '42-test-test');
		$this->assertEquals(array(), $object->parse($segments));
	}
}
