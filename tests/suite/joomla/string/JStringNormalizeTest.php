<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  String
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/string/stringnormalize.php';

/**
 * JStringNormalizeTest
 *
 * @package     Joomla.UnitTest
 * @subpackage  String
 * @since       11.3
 */
class JStringNormalizeTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Method to test JStringNormalize::toCamelCase().
	 * 
	 * @dataProvider  seedToCamelCase
	 */
	public function testToCamelCase($expected, $input)
	{
		$this->assertEquals($expected, JStringNormalize::toCamelcase($input));
	}
	
	/**
	 * Method to test JStringNormalize::toDashSeparated().
	 * 
	 * @dataProvider  seedToDashSeparated
	 */
	public function testToDashSeparated($expected, $input)
	{
		$this->assertEquals($expected, JStringNormalize::toDashSeparated($input));
	}
	
	/**
	 * Method to test JStringNormalize::toSpaceSeparated().
	 * 
	 * @dataProvider  seedToSpaceSeparated
	 */
	public function testToSpaceSeparated($expected, $input)
	{
		$this->assertEquals($expected, JStringNormalize::toSpaceSeparated($input));
	}
	
	/**
	 * Method to test JStringNormalize::toUnderscoreSeparated().
	 * 
	 * @dataProvider  seedToUnderscoreSeparated
	 */
	public function testToUnderscoreSeparated($expected, $input)
	{
		$this->assertEquals($expected, JStringNormalize::toUnderscoreSeparated($input));
	}
	
	/**
	 * Method to test JStringNormalize::toVariable().
	 * 
	 * @dataProvider  seedToVariable
	 */
	public function testToVariable($expected, $input)
	{
		$this->assertEquals($expected, JStringNormalize::toVariable($input));
	}
	
	/**
	 * Method to test JStringNormalize::toKey().
	 * 
	 * @dataProvider  seedToKey
	 */
	public function testToKey($expected, $input)
	{
		$this->assertEquals($expected, JStringNormalize::toKey($input));
	}
	
	/**
	 * Method to seed data to testToCamelCase.
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