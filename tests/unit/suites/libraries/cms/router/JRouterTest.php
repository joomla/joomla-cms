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
	 * @param   array   $functions
	 * @param   string  $expected
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
}
