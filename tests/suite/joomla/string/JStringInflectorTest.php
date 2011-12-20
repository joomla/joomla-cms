<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  String
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/string/inflector.php';

/**
 * Test for the JStringInflector class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  String
 * @since       11.4
 * @link        http://en.wikipedia.org/wiki/English_plural
 */
class JStringInflectorTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JStringInflector
	 * @since  11.4
	 */
	protected $inflector;

	/**
	 * Method to seed data to testIsCountable.
	 *
	 * @return  array
	 *
	 * @since   11.4
	 */
	public function seedIsCountable()
	{
		return array(
			array('id', true),
			array('title', false),
		);
	}

	/**
	 * Method to seed data to testToPlural.
	 *
	 * @return  array
	 *
	 * @since   11.4
	 */
	public function seedToPlural()
	{
		return array(
			// Regular plurals
			array('bus', 'buses'),
			array('notify', 'notifies'),
			array('clicks', 'clicks'),

			// Almost regular plurals.
			array('photo', 'photos'),
			array('zero', 'zeros'),

			// Irregular identicals
			array('salmon', 'salmon'),

			// Irregular -(e)n
			array('ox', 'oxen'),

			// Ablaut plurals
			array('foot', 'feet'),
			array('goose', 'geese'),
			array('louse', 'lice'),
			array('man', 'men'),
			array('mouse', 'mice'),
			array('tooth', 'teeth'),
			array('woman', 'women'),
		);
	}

	/**
	 * Method to seed data to testToSingular.
	 *
	 * @return  array
	 *
	 * @since   11.4
	 */
	public function seedToSingular()
	{
		return array(
			array('clicks', 'click'),
			array('buses', 'bus'),
			array('notifies', 'notify'),
		);
	}

	/**
	 * Sets up the fixture.
	 *
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function setUp()
	{
		$this->inflector = JStringInflector::getInstance();
	}

	/**
	 * Method to test JStringInflector::isCountable().
	 *
	 * @param   string   $input     A string.
	 * @param   boolean  $expected  The expected result of the function call.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedIsCountable
	 * @since   11.4
	 */
	public function testIsCountable($input, $expected)
	{
		$this->assertThat(
			$this->inflector->isCountable($input),
			$this->equalTo($expected)
		);
	}

	/**
	 * Method to test JStringInflector::toPlural().
	 *
	 * @param   string   $input     A string.
	 * @param   boolean  $expected  The expected result of the function call.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedToPlural
	 * @since   11.4
	 */
	public function testToPlural($input, $expected)
	{
		$this->assertThat(
			$this->inflector->toPlural($input),
			$this->equalTo($expected)
		);
	}

	/**
	 * Method to test JStringInflector::toSingular().
	 *
	 * @param   string   $input     A string.
	 * @param   boolean  $expected  The expected result of the function call.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedToSingular
	 * @since   11.4
	 */
	public function testToSingular($input, $expected)
	{
		$this->assertThat(
			$this->inflector->toSingular($input),
			$this->equalTo($expected)
		);
	}
}
