<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Router
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JRouter.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Router
 * @since       3.1
 */
class JRouterTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Object under test
	 *
	 * @var    JRouter
	 * @since  3.1
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->object = new JRouter;
	}

	/**
	 * Test...
	 *
	 * @todo Implement testGetInstance().
	 *
	 * @return void
	 */
	public function testGetInstance()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testParse().
	 *
	 * @return void
	 */
	public function testParse()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testBuild().
	 *
	 * @return void
	 */
	public function testBuild()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testGetMode().
	 *
	 * @return void
	 */
	public function testGetMode()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testSetMode().
	 *
	 * @return void
	 */
	public function testSetMode()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Cases for testSetVar
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function casesSetVar()
	{
		$cases = array();
		$cases[] = array(array(), 'myvar', 'myvalue', true, 'myvalue');
		$cases[] = array(array(), 'myvar', 'myvalue', false, null);
		$cases[] = array(array('myvar' => 'myvalue1'), 'myvar', 'myvalue2', true, 'myvalue2');
		$cases[] = array(array('myvar' => 'myvalue1'), 'myvar', 'myvalue2', false, 'myvalue2');

		return $cases;
	}

	/**
	 * Tests the setVar method
	 *
	 * @param   array    $vars      An associative array with variables
	 * @param   string   $var       The name of the variable
	 * @param   mixed    $value     The value of the variable
	 * @param   boolean  $create    If True, the variable will be created if it doesn't exist yet
	 * @param   string   $expected  Expected return value
	 *
	 * @return  void
	 *
	 * @dataProvider  casesSetVar
	 * @since         3.1
	 */
	public function testSetVar($vars, $var, $value, $create, $expected)
	{
		$this->object->setVars($vars, false);
		$this->object->setVar($var, $value, $create);
		$this->assertEquals($this->object->getVar($var), $expected, __METHOD__ . ':' . __LINE__ . ': value is not expected');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testSetVars().
	 *
	 * @return void
	 */
	public function testSetVars()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testGetVar().
	 *
	 * @return void
	 */
	public function testGetVar()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testGetVars().
	 *
	 * @return void
	 */
	public function testGetVars()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testAttachBuildRule().
	 *
	 * @return void
	 */
	public function testAttachBuildRule()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testAttachParseRule().
	 *
	 * @return void
	 */
	public function testAttachParseRule()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testParseRawRoute().
	 *
	 * @return void
	 */
	public function testParseRawRoute()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testParseSefRoute().
	 *
	 * @return void
	 */
	public function testParseSefRoute()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testBuildRawRoute().
	 *
	 * @return void
	 */
	public function testBuildRawRoute()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testBuildSefRoute().
	 *
	 * @return void
	 */
	public function testBuildSefRoute()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Cases for testProcessParseRules
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function casesProcessParseRules()
	{
		$cases = array();
		$cases[] = array(array(), array());
		$cases[] = array(
			array(
				function (&$router, &$uri)
				{
					return array('myvar' => 'myvalue');
				}
			),
			array('myvar' => 'myvalue')
		);
		$cases[] = array(
			array(
				function (&$router, &$uri)
				{
					return array('myvar1' => 'myvalue1');
				},
				function (&$router, &$uri)
				{
					return array('myvar2' => 'myvalue2');
				},
			),
			array('myvar1' => 'myvalue1', 'myvar2' => 'myvalue2')
		);
		$cases[] = array(
			array(
				function (&$router, &$uri)
				{
					return array('myvar1' => 'myvalue1');
				},
				function (&$router, &$uri)
				{
					return array('myvar2' => 'myvalue2');
				},
				function (&$router, &$uri)
				{
					return array('myvar1' => 'myvalue3');
				},
			),
			array('myvar1' => 'myvalue1', 'myvar2' => 'myvalue2')
		);

		return $cases;
	}

	/**
	 * test_processParseRules().
	 *
	 * @param   array   $functions  @todo
	 * @param   string  $expected   @todo
	 *
	 * @dataProvider casesProcessParseRules
	 *
	 * @return void
	 */
	public function testProcessParseRules($functions, $expected)
	{
		$myuri = 'http://localhost';
		$stub = $this->getMock('JRouter', array('parseRawRoute'));
		$stub->expects($this->any())->method('parseRawRoute')->will($this->returnValue(array()));

		foreach ($functions as $function)
		{
			$stub->attachParseRule($function);
		}

		$this->assertEquals($stub->parse($myuri), $expected, __METHOD__ . ':' . __LINE__ . ': value is not expected');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testProcessBuildRules().
	 *
	 * @return void
	 */
	public function testProcessBuildRules()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testCreateURI().
	 *
	 * @return void
	 */
	public function testCreateURI()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testEncodeSegments().
	 *
	 * @return void
	 */
	public function testEncodeSegments()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testDecodeSegments().
	 *
	 * @return void
	 */
	public function testDecodeSegments()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}
}
