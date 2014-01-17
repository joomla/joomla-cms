<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  String
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */


/**
 * JStringNormaliseTest
 *
 * @package     Joomla.UnitTest
 * @subpackage  String
 * @since       11.3
 */
class JStringNormaliseTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function getFromCamelCaseData()
	{
		return array(
			// Note: string, expected
			array('FooBarABCDef', array('Foo', 'Bar', 'ABC', 'Def')),
			array('JFooBar', array('J', 'Foo', 'Bar')),
			array('J001FooBar002', array('J001', 'Foo', 'Bar002')),
			array('abcDef', array('abc', 'Def')),
			array('abc_defGhi_Jkl', array('abc_def', 'Ghi_Jkl')),
			array('ThisIsA_NASAAstronaut', array('This', 'Is', 'A_NASA', 'Astronaut')),
			array('JohnFitzgerald_Kennedy', array('John', 'Fitzgerald_Kennedy')),
		);
	}

	/**
	 * Method to test JStringNormalise::fromCamelCase(string, false).
	 *
	 * @param   string  $expected  The expected value from the method.
	 * @param   string  $input     The input value for the method.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedFromCamelCase
	 * @since   11.3
	 * @covers  JStringNormalise::fromCamelcase
	 */
	public function testFromCamelCase_nongrouped($expected, $input)
	{
		$this->assertEquals($expected, JStringNormalise::fromCamelcase($input));
	}

	/**
	 * Method to test JStringNormalise::fromCamelCase(string, true).
	 *
	 * @param   string  $input     The input value for the method.
	 * @param   string  $expected  The expected value from the method.
	 *
	 * @return  void
	 *
	 * @dataProvider  getFromCamelCaseData
	 * @since   11.3
	 * @covers  JStringNormalise::fromCamelcase
	 */
	public function testFromCamelCase_grouped($input, $expected)
	{
		$this->assertEquals($expected, JStringNormalise::fromCamelcase($input, true));
	}

	/**
	 * Method to test JStringNormalise::toCamelCase().
	 *
	 * @param   string  $expected  The expected value from the method.
	 * @param   string  $input     The input value for the method.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedToCamelCase
	 * @since   11.3
	 * @covers  JStringNormalise::toCamelcase
	 */
	public function testToCamelCase($expected, $input)
	{
		$this->assertEquals($expected, JStringNormalise::toCamelcase($input));
	}

	/**
	 * Method to test JStringNormalise::toDashSeparated().
	 *
	 * @param   string  $expected  The expected value from the method.
	 * @param   string  $input     The input value for the method.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedToDashSeparated
	 * @since   11.3
	 * @covers  JStringNormalise::toDashSeparated
	 */
	public function testToDashSeparated($expected, $input)
	{
		$this->assertEquals($expected, JStringNormalise::toDashSeparated($input));
	}

	/**
	 * Method to test JStringNormalise::toSpaceSeparated().
	 *
	 * @param   string  $expected  The expected value from the method.
	 * @param   string  $input     The input value for the method.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedToSpaceSeparated
	 * @since   11.3
	 * @covers  JStringNormalise::toSpaceSeparated
	 */
	public function testToSpaceSeparated($expected, $input)
	{
		$this->assertEquals($expected, JStringNormalise::toSpaceSeparated($input));
	}

	/**
	 * Method to test JStringNormalise::toUnderscoreSeparated().
	 *
	 * @param   string  $expected  The expected value from the method.
	 * @param   string  $input     The input value for the method.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedToUnderscoreSeparated
	 * @since   11.3
	 * @covers  JStringNormalise::toUnderscoreSeparated
	 */
	public function testToUnderscoreSeparated($expected, $input)
	{
		$this->assertEquals($expected, JStringNormalise::toUnderscoreSeparated($input));
	}

	/**
	 * Method to test JStringNormalise::toVariable().
	 *
	 * @param   string  $expected  The expected value from the method.
	 * @param   string  $input     The input value for the method.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedToVariable
	 * @since   11.3
	 * @covers  JStringNormalise::toVariable
	 */
	public function testToVariable($expected, $input)
	{
		$this->assertEquals($expected, JStringNormalise::toVariable($input));
	}

	/**
	 * Method to test JStringNormalise::toKey().
	 *
	 * @param   string  $expected  The expected value from the method.
	 * @param   string  $input     The input value for the method.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedToKey
	 * @since   11.3
	 * @covers  JStringNormalise::toKey
	 */
	public function testToKey($expected, $input)
	{
		$this->assertEquals($expected, JStringNormalise::toKey($input));
	}

	/**
	 * Method to seed data to testFromCamelCase.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function seedFromCamelCase()
	{
		return array(
			array('Foo Bar', 'FooBar'),
			array('foo Bar', 'fooBar'),
			array('Foobar', 'Foobar'),
			array('foobar', 'foobar')
		);
	}

	/**
	 * Method to seed data to testToCamelCase.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function seedToCamelCase()
	{
		return array(
			array('FooBar', 'Foo Bar'),
			array('FooBar', 'Foo-Bar'),
			array('FooBar', 'Foo_Bar'),
			array('FooBar', 'foo bar'),
			array('FooBar', 'foo-bar'),
			array('FooBar', 'foo_bar'),
		);
	}

	/**
	 * Method to seed data to testToDashSeparated.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function seedToDashSeparated()
	{
		return array(
			array('Foo-Bar', 'Foo Bar'),
			array('Foo-Bar', 'Foo-Bar'),
			array('Foo-Bar', 'Foo_Bar'),
			array('foo-bar', 'foo bar'),
			array('foo-bar', 'foo-bar'),
			array('foo-bar', 'foo_bar'),
			array('foo-bar', 'foo   bar'),
			array('foo-bar', 'foo---bar'),
			array('foo-bar', 'foo___bar'),
		);
	}

	/**
	 * Method to seed data to testToSpaceSeparated.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function seedToSpaceSeparated()
	{
		return array(
			array('Foo Bar', 'Foo Bar'),
			array('Foo Bar', 'Foo-Bar'),
			array('Foo Bar', 'Foo_Bar'),
			array('foo bar', 'foo bar'),
			array('foo bar', 'foo-bar'),
			array('foo bar', 'foo_bar'),
			array('foo bar', 'foo   bar'),
			array('foo bar', 'foo---bar'),
			array('foo bar', 'foo___bar'),
		);
	}

	/**
	 * Method to seed data to testToUnderscoreSeparated.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function seedToUnderscoreSeparated()
	{
		return array(
			array('Foo_Bar', 'Foo Bar'),
			array('Foo_Bar', 'Foo-Bar'),
			array('Foo_Bar', 'Foo_Bar'),
			array('foo_bar', 'foo bar'),
			array('foo_bar', 'foo-bar'),
			array('foo_bar', 'foo_bar'),
			array('foo_bar', 'foo   bar'),
			array('foo_bar', 'foo---bar'),
			array('foo_bar', 'foo___bar'),
		);
	}

	/**
	 * Method to seed data to testToVariable.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function seedToVariable()
	{
		return array(
			array('myFooBar', 'My Foo Bar'),
			array('myFooBar', 'My Foo-Bar'),
			array('myFooBar', 'My Foo_Bar'),
			array('myFooBar', 'my foo bar'),
			array('myFooBar', 'my foo-bar'),
			array('myFooBar', 'my foo_bar'),
		);
	}

	/**
	 * Method to seed data to testToKey.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function seedToKey()
	{
		return array(
			array('foo_bar', 'Foo Bar'),
			array('foo_bar', 'Foo-Bar'),
			array('foo_bar', 'Foo_Bar'),
			array('foo_bar', 'foo bar'),
			array('foo_bar', 'foo-bar'),
			array('foo_bar', 'foo_bar'),
		);
	}
}
