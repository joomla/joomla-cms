<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JDatabaseQueryElement.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Database
 * @since       11.1
 */
class JDatabaseQueryElementTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Test cases for append and __toString
	 *
	 * Each test case provides
	 * - array    element    the base element for the test, given as hash
	 *                 name => element_name,
	 *                 elements => element array,
	 *                 glue => glue
	 * - array    appendElement    the element to be appended (same format as above)
	 * - array     expected    array of elements that should be the value of the elements attribute after the merge
	 * - string    expected value of __toString() for element after append
	 *
	 * @return  array
	 *
	 * @since   11.1
	 */
	public function dataTestAppend()
	{
		return array(
			'array-element' => array(
				array(
					'name' => 'SELECT',
					'elements' => array(),
					'glue' => ','
				),
				array(
					'name' => 'FROM',
					'elements' => array('my_table_name'),
					'glue' => ','
				),
				array(
					'name' => 'FROM',
					'elements' => array('my_table_name'),
					'glue' => ','
				),
				PHP_EOL . 'SELECT ' . PHP_EOL . 'FROM my_table_name',
			),
			'non-array-element' => array(
				array(
					'name' => 'SELECT',
					'elements' => array(),
					'glue' => ','
				),
				array(
					'name' => 'FROM',
					'elements' => array('my_table_name'),
					'glue' => ','
				),
				array(
					'name' => 'FROM',
					'elements' => array('my_table_name'),
					'glue' => ','
				),
				PHP_EOL . 'SELECT ' . PHP_EOL . 'FROM my_table_name',
			)
		);
	}

	/**
	 * Test cases for constructor
	 *
	 * Each test case provides
	 * - array    element    the base element for the test, given as hash
	 *                 name => element_name,
	 *                 elements => array or string
	 *                 glue => glue
	 * - array    expected values in same hash format
	 *
	 * @return array
	 */
	public function dataTestConstruct()
	{
		return array(
			'array-element' => array(
				array(
					'name' => 'FROM',
					'elements' => array('field1', 'field2'),
					'glue' => ','
				),
				array(
					'name' => 'FROM',
					'elements' => array('field1', 'field2'),
					'glue' => ','
				)
			),
			'non-array-element' => array(
				array(
					'name' => 'TABLE',
					'elements' => 'my_table_name',
					'glue' => ','
				),
				array(
					'name' => 'TABLE',
					'elements' => array('my_table_name'),
					'glue' => ','
				)
			)
		);
	}

	/**
	 * Test data for test__toString.
	 *
	 * @return  array
	 *
	 * @since   11.1
	 */
	public function dataTestToString()
	{
		return array(
			// @todo name, elements, glue, expected.
			array(
				'FROM',
				'table1',
				',',
				PHP_EOL . "FROM table1"
			),
			array(
				'SELECT',
				array('column1', 'column2'),
				',',
				PHP_EOL . "SELECT column1,column2"
			),
			array(
				'()',
				array('column1', 'column2'),
				',',
				PHP_EOL . "(column1,column2)"
			),
			array(
				'CONCAT()',
				array('column1', 'column2'),
				',',
				PHP_EOL . "CONCAT(column1,column2)"
			),
		);
	}

	/**
	 * Test the class constructor.
	 *
	 * @param   array  $element   values for base element
	 * @param   array  $expected  values for expected fields
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @dataProvider  dataTestConstruct
	 */
	public function test__Construct($element, $expected)
	{
		$baseElement = new JDatabaseQueryElement($element['name'], $element['elements'], $element['glue']);

		$this->assertAttributeEquals(
			$expected['name'], 'name', $baseElement, 'Line ' . __LINE__ . ' name should be set'
		);

		$this->assertAttributeEquals(
			$expected['elements'], 'elements', $baseElement, 'Line ' . __LINE__ . ' elements should be set'
		);

		$this->assertAttributeEquals(
			$expected['glue'], 'glue', $baseElement, 'Line ' . __LINE__ . ' glue should be set'
		);
	}

	/**
	 * Test the __toString magic method.
	 *
	 * @param   string  $name      The name of the element.
	 * @param   mixed   $elements  String or array.
	 * @param   string  $glue      The glue for elements.
	 * @param   string  $expected  The expected value.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @dataProvider  dataTestToString
	 */
	public function test__toString($name, $elements, $glue, $expected)
	{
		$e = new JDatabaseQueryElement($name, $elements, $glue);

		$this->assertThat(
			(string) $e,
			$this->equalTo($expected)
		);
	}

	/**
	 * Test the append method.
	 *
	 * @param   array   $element   base element values
	 * @param   array   $append    append element values
	 * @param   array   $expected  expected element values for elements field after append
	 * @param   string  $string    expected value of toString (not used in this test)
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @dataProvider dataTestAppend
	 */
	public function testAppend($element, $append, $expected, $string)
	{
		$baseElement = new JDatabaseQueryElement($element['name'], $element['elements'], $element['glue']);
		$appendElement = new JDatabaseQueryElement($append['name'], $append['elements'], $append['glue']);
		$expectedElement = new JDatabaseQueryElement($expected['name'], $expected['elements'], $expected['glue']);
		$baseElement->append($appendElement);
		$this->assertAttributeEquals(array($expectedElement), 'elements', $baseElement);
	}

	/**
	 * Tests the JDatabaseQueryElement::__clone method properly clones an array.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function test__clone_array()
	{
		$baseElement = new JDatabaseQueryElement($name = null, $elements = null);

		$baseElement->testArray = array();

		$cloneElement = clone($baseElement);

		$baseElement->testArray[] = 'a';

		$this->assertNotSame($baseElement, $cloneElement);
		$this->assertCount(0, $cloneElement->testArray);
	}

	/**
	 * Tests the JDatabaseQueryElement::__clone method properly clones an object.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function test__clone_object()
	{
		$baseElement = new JDatabaseQueryElement($name = null, $elements = null);

		$baseElement->testObject = new stdClass;

		$cloneElement = clone($baseElement);

		$this->assertNotSame($baseElement, $cloneElement);
		$this->assertNotSame($baseElement->testObject, $cloneElement->testObject);
	}
}
