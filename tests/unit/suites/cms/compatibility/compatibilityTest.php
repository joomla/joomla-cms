<?php
/**
 * @package    Joomla.UnitTest
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters. All rights reserved.
 * @license    GNU General Public License
 */

/**
 * Tests for the JCmsCompatibility class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Utilities
 * @since       3.0.3
 */
class compatibilityTest extends PHPUnit_Framework_TestCase
{
	/**
	 * The instance of the object to test.
	 *
	 * @var    JCmsCompatibility
	 * @since  3.0.3
	 */
	private $_instance;

	/**
	 * Seeds the testCheck method.
	 *
	 * @return  array
	 *
	 * @since   3.0.3
	 */
	public function seedCheck()
	{
		return array(
			'Joomla - pass' => array(true, '2.5', 'joomla'),
			'Joomla - too low' => array(false, '2.4', 'joomla'),
			'Joomla - too high' => array(false, '3', 'joomla'),
			'Joomla - exclude left edge fail' => array(false, '2.5.1', 'joomla'),
			'Joomla - exclude right edge fail' => array(false, '2.5.3', 'joomla'),
			'Joomla - exclude right edge pass' => array(true, '2.5.4', 'joomla'),
			'PHP - pass' => array(true, '5.4', 'PHP'),
			'PHP - fail' => array(false, '5.2', 'php'),
			'com_foo - pass' => array(true, '0.0.1', 'com_foo'),
			'com_foo - fail' => array(false, '1.2.4', 'com_FOO'),
		);
	}

	/**
	 * Seeds the testCheckRules method.
	 *
	 * @return  array
	 *
	 * @since   3.0.3
	 */
	public function seedCheckRules()
	{
		return array(
			'From only - left edge' => array(
					true,
					'<versions from="2.5.8" />',
					'2.5.8'
			),
			'From only - too low' => array(
					false,
					'<versions from="2.5.8" />',
					'2.5.7'
			),
			'To only - left edge' => array(
					true,
					'<versions to="2.5.8" />',
					'2.5.8'
			),
			'To only - too low' => array(
					false,
					'<versions to="2.5.8" />',
					'2.5.9'
			),
			'Single - left edge' => array(
					true,
					'<versions from="2.5.8" to="2.5.99" />',
					'2.5.8'
			),
			'Single - right edge' => array(
					true,
					'<versions from="2.5.8" to="2.5.99" />',
					'2.5.99'
			),
			'Single - too low' => array(
					false,
					'<versions from="2.5.8" to="2.5.99" />',
					'2.5.7'
			),
			'Single - too high' => array(
					false,
					'<versions from="2.5.8" to="2.5.99" />',
					'2.5.100'
			),
			'Multiple - passes' => array(
					true,
					'<versions from="2.5.8" to="2.5.99" /><versions from="3.0" />',
					'3.0.1'
			),
			'Multiple - fails' => array(
					false,
					'<versions from="2.5.8" to="2.5.99" /><versions from="3.0" />',
					'2.5.7'
			),
		);
	}

	/**
	 * Checks the __construct method.
	 *
	 * @return  void
	 *
	 * @covers  JCompatibility::__construct
	 * @since   3.0.3
	 */
	public function test__construct()
	{
		$this->assertAttributeNotEmpty('compatibilities', $this->_instance);
	}

	/**
	 * Checks the check method.
	 *
	 * @param   boolean  $expected  The expected result from the check method.
	 * @param   string   $version   The version number to check.
	 * @param   string   $with      The context of the check (compare 'with').
	 *
	 * @return  void
	 *
	 * @covers        JCompatibility::check
	 * @dataProvider  seedCheck
	 * @since         3.0.3
	 */
	public function testCheck($expected, $version, $with)
	{
		$this->assertEquals($expected, $this->_instance->check($version, $with));
	}

	/**
	 * Checks the check method with an expected exception.
	 *
	 * @return  void
	 *
	 * @covers             JCompatibility::check
	 * @expectedException  InvalidArgumentException
	 * @since              3.0.3
	 */
	public function testCheck_exception()
	{
		$this->_instance->check('1', 'foobar');
	}

	/**
	 * Checks the checkRules method.
	 *
	 * @param   boolean  $expected  The expected result from the checkRules method.
	 * @param   string   $data      The XML data for the versions tags.
	 * @param   string   $version   The version number to check.
	 *
	 * @return  void
	 *
	 * @covers        JCompatibility::checkRules
	 * @dataProvider  seedCheckRules
	 * @since         3.0.3
	 */
	public function testCheckRules($expected, $data, $version)
	{
		$xml = new SimpleXMLElement('<root>' . $data . '</root>');
		$this->assertEquals($expected, TestReflection::invoke($this->_instance, 'checkRules', $xml, $version));
	}

	/**
	 * Sets up the fixture. This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.0.3
	 */
	protected function setUp()
	{
		$data = file_get_contents(__DIR__ . '/compatibilityTest.xml');
		$xml = new SimpleXMLElement($data);
		$this->_instance = new JCompatibility($xml->compatibilities);
	}
}
