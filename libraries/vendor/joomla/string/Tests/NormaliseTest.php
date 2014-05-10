<?php
/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\String\Tests;

use Joomla\String\Normalise;

/**
 * NormaliseTest
 *
 * @since  1.0
 */
class NormaliseTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Method to seed data to testFromCamelCase.
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedTestFromCamelCase()
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
	 * Method to seed data to testFromCamelCase.
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedTestFromCamelCase_nongrouped()
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
	 * @since   1.0
	 */
	public function seedTestToCamelCase()
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
	 * @since   1.0
	 */
	public function seedTestToDashSeparated()
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
	 * @since   1.0
	 */
	public function seedTestToSpaceSeparated()
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
	 * @since   1.0
	 */
	public function seedTestToUnderscoreSeparated()
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
	 * @since   1.0
	 */
	public function seedTestToVariable()
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
	 * @since   1.0
	 */
	public function seedTestToKey()
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

	/**
	 * Method to test Normalise::fromCamelCase(string, false).
	 *
	 * @param   string  $expected  The expected value from the method.
	 * @param   string  $input     The input value for the method.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedTestFromCamelCase_nongrouped
	 * @since      1.0
	 * @covers        Joomla\String\Normalise::fromCamelcase
	 */
	public function testFromCamelCase_nongrouped($expected, $input)
	{
		$this->assertEquals($expected, Normalise::fromCamelcase($input));
	}

	/**
	 * Method to test Normalise::fromCamelCase(string, true).
	 *
	 * @param   string  $input     The input value for the method.
	 * @param   string  $expected  The expected value from the method.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedTestFromCamelCase
	 * @since      1.0
	 * @covers        Joomla\String\Normalise::fromCamelcase
	 */
	public function testFromCamelCase_grouped($input, $expected)
	{
		$this->assertEquals($expected, Normalise::fromCamelcase($input, true));
	}

	/**
	 * Method to test Normalise::toCamelCase().
	 *
	 * @param   string  $expected  The expected value from the method.
	 * @param   string  $input     The input value for the method.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedTestToCamelCase
	 * @since      1.0
	 * @covers        Joomla\String\Normalise::toCamelcase
	 */
	public function testToCamelCase($expected, $input)
	{
		$this->assertEquals($expected, Normalise::toCamelcase($input));
	}

	/**
	 * Method to test Normalise::toDashSeparated().
	 *
	 * @param   string  $expected  The expected value from the method.
	 * @param   string  $input     The input value for the method.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedTestToDashSeparated
	 * @since      1.0
	 * @covers        Joomla\String\Normalise::toDashSeparated
	 */
	public function testToDashSeparated($expected, $input)
	{
		$this->assertEquals($expected, Normalise::toDashSeparated($input));
	}

	/**
	 * Method to test Normalise::toSpaceSeparated().
	 *
	 * @param   string  $expected  The expected value from the method.
	 * @param   string  $input     The input value for the method.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedTestToSpaceSeparated
	 * @since      1.0
	 * @covers        Joomla\String\Normalise::toSpaceSeparated
	 */
	public function testToSpaceSeparated($expected, $input)
	{
		$this->assertEquals($expected, Normalise::toSpaceSeparated($input));
	}

	/**
	 * Method to test Normalise::toUnderscoreSeparated().
	 *
	 * @param   string  $expected  The expected value from the method.
	 * @param   string  $input     The input value for the method.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedTestToUnderscoreSeparated
	 * @since      1.0
	 * @covers        Joomla\String\Normalise::toUnderscoreSeparated
	 */
	public function testToUnderscoreSeparated($expected, $input)
	{
		$this->assertEquals($expected, Normalise::toUnderscoreSeparated($input));
	}

	/**
	 * Method to test Normalise::toVariable().
	 *
	 * @param   string  $expected  The expected value from the method.
	 * @param   string  $input     The input value for the method.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedTestToVariable
	 * @since      1.0
	 * @covers        Joomla\String\Normalise::toVariable
	 */
	public function testToVariable($expected, $input)
	{
		$this->assertEquals($expected, Normalise::toVariable($input));
	}

	/**
	 * Method to test Normalise::toKey().
	 *
	 * @param   string  $expected  The expected value from the method.
	 * @param   string  $input     The input value for the method.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedTestToKey
	 * @since      1.0
	 * @covers        Joomla\String\Normalise::toKey
	 */
	public function testToKey($expected, $input)
	{
		$this->assertEquals($expected, Normalise::toKey($input));
	}
}
