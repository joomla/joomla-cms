<?php
/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\String\Tests;

use Joomla\String\Inflector;
use Joomla\Test\TestHelper;

/**
 * Test for the Inflector class.
 *
 * @link   http://en.wikipedia.org/wiki/English_plural
 * @since  1.0
 */
class InflectorTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var    Inflector
	 * @since  1.0
	 */
	protected $inflector;

	/**
	 * Method to seed data to testIsCountable.
	 *
	 * @return  array
	 *
	 * @since   1.0
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
	 * @since   1.0
	 */
	public function seedSinglePlural()
	{
		return array(
			// Regular plurals
			array('bus', 'buses'),
			array('notify', 'notifies'),
			array('click', 'clicks'),

			// Almost regular plurals.
			array('photo', 'photos'),
			array('zero', 'zeros'),

			// Irregular identicals
			array('salmon', 'salmon'),

			// Irregular plurals
			array('ox', 'oxen'),
			array('quiz', 'quizes'),
			array('status', 'statuses'),
			array('matrix', 'matrices'),
			array('index', 'indices'),
			array('vertex', 'vertices'),
			array('hive', 'hives'),

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
	 * Sets up the fixture.
	 *
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->inflector = Inflector::getInstance(true);
	}

	/**
	 * Method to test Inflector::addRule().
	 *
	 * @return  void
	 *
	 * @covers  Joomla\String\Inflector::addRule
	 * @since   1.0
	 */
	public function testAddRule()
	{
		// Case 1
		TestHelper::invoke($this->inflector, 'addRule', '/foo/', 'singular');

		$rules = TestHelper::getValue($this->inflector, 'rules');

		$this->assertContains(
			'/foo/',
			$rules['singular'],
			'Checks if the singular rule was added correctly.'
		);

		// Case 2
		TestHelper::invoke($this->inflector, 'addRule', '/bar/', 'plural');

		$rules = TestHelper::getValue($this->inflector, 'rules');

		$this->assertContains(
			'/bar/',
			$rules['plural'],
			'Checks if the plural rule was added correctly.'
		);

		// Case 3
		TestHelper::invoke($this->inflector, 'addRule', array('/goo/', '/car/'), 'singular');

		$rules = TestHelper::getValue($this->inflector, 'rules');

		$this->assertContains(
			'/goo/',
			$rules['singular'],
			'Checks if an array of rules was added correctly (1).'
		);

		$this->assertContains(
			'/car/',
			$rules['singular'],
			'Checks if an array of rules was added correctly (2).'
		);
	}

	/**
	 * Method to test Inflector::addRule().
	 *
	 * @return  void
	 *
	 * @covers  Joomla\String\Inflector::addRule
	 * @expectedException  InvalidArgumentException
	 * @since   1.0
	 */
	public function testAddRuleException()
	{
		TestHelper::invoke($this->inflector, 'addRule', new \stdClass, 'singular');
	}

	/**
	 * Method to test Inflector::getCachedPlural().
	 *
	 * @return  void
	 *
	 * @covers  Joomla\String\Inflector::getCachedPlural
	 * @since   1.0
	 */
	public function testGetCachedPlural()
	{
		// Reset the cache.
		TestHelper::setValue($this->inflector, 'cache', array('foo' => 'bar'));

		$this->assertFalse(
			TestHelper::invoke($this->inflector, 'getCachedPlural', 'bar'),
			'Checks for an uncached plural.'
		);

		$this->assertEquals(
			'bar',
			TestHelper::invoke($this->inflector, 'getCachedPlural', 'foo'),
			'Checks for a cached plural word.'
		);
	}

	/**
	 * Method to test Inflector::getCachedSingular().
	 *
	 * @return  void
	 *
	 * @covers  Joomla\String\Inflector::getCachedSingular
	 * @since   1.0
	 */
	public function testGetCachedSingular()
	{
		// Reset the cache.
		TestHelper::setValue($this->inflector, 'cache', array('foo' => 'bar'));

		$this->assertFalse(
			TestHelper::invoke($this->inflector, 'getCachedSingular', 'foo'),
			'Checks for an uncached singular.'
		);

		$this->assertThat(
			TestHelper::invoke($this->inflector, 'getCachedSingular', 'bar'),
			$this->equalTo('foo'),
			'Checks for a cached singular word.'
		);
	}

	/**
	 * Method to test Inflector::matchRegexRule().
	 *
	 * @return  void
	 *
	 * @covers  Joomla\String\Inflector::matchRegexRule
	 * @since   1.0
	 */
	public function testMatchRegexRule()
	{
		$this->assertThat(
			TestHelper::invoke($this->inflector, 'matchRegexRule', 'xyz', 'plural'),
			$this->equalTo('xyzs'),
			'Checks pluralising against the basic regex.'
		);

		$this->assertThat(
			TestHelper::invoke($this->inflector, 'matchRegexRule', 'xyzs', 'singular'),
			$this->equalTo('xyz'),
			'Checks singularising against the basic regex.'
		);

		$this->assertFalse(
			TestHelper::invoke($this->inflector, 'matchRegexRule', 'xyz', 'singular'),
			'Checks singularising against an unmatched regex.'
		);
	}

	/**
	 * Method to test Inflector::setCache().
	 *
	 * @return  void
	 *
	 * @covers  Joomla\String\Inflector::setCache
	 * @since   1.0
	 */
	public function testSetCache()
	{
		TestHelper::invoke($this->inflector, 'setCache', 'foo', 'bar');

		$cache = TestHelper::getValue($this->inflector, 'cache');

		$this->assertThat(
			$cache['foo'],
			$this->equalTo('bar'),
			'Checks the cache was set.'
		);

		TestHelper::invoke($this->inflector, 'setCache', 'foo', 'car');

		$cache = TestHelper::getValue($this->inflector, 'cache');

		$this->assertThat(
			$cache['foo'],
			$this->equalTo('car'),
			'Checks an existing value in the cache was reset.'
		);
	}

	/**
	 * Method to test Inflector::addCountableRule().
	 *
	 * @return  void
	 *
	 * @covers  Joomla\String\Inflector::addCountableRule
	 * @since   1.0
	 */
	public function testAddCountableRule()
	{
		// Add string.
		$this->inflector->addCountableRule('foo');

		$rules = TestHelper::getValue($this->inflector, 'rules');

		$this->assertContains(
			'foo',
			$rules['countable'],
			'Checks a countable rule was added.'
		);

		// Add array.
		$this->inflector->addCountableRule(array('goo', 'car'));

		$rules = TestHelper::getValue($this->inflector, 'rules');

		$this->assertContains(
			'car',
			$rules['countable'],
			'Checks a countable rule was added by array.'
		);
	}

	/**
	 * Method to test Inflector::addWord().
	 *
	 * @return  void
	 *
	 * @covers  Joomla\String\Inflector::addWord
	 * @since   1.2.0
	 */
	public function testAddWord()
	{
		$this->assertEquals(
			$this->inflector,
			$this->inflector->addWord('foo')
		);

		$cache = TestHelper::getValue($this->inflector, 'cache');

		$this->assertArrayHasKey('foo', $cache);

		$this->assertEquals(
			'foo',
			$cache['foo']
		);

		$this->assertEquals(
			$this->inflector,
			$this->inflector->addWord('bar', 'foo')
		);

		$cache = TestHelper::getValue($this->inflector, 'cache');

		$this->assertArrayHasKey('bar', $cache);

		$this->assertEquals(
			'foo',
			$cache['bar']
		);
	}

	/**
	 * Method to test Inflector::addPluraliseRule().
	 *
	 * @return  void
	 *
	 * @covers  Joomla\String\Inflector::addPluraliseRule
	 * @since   1.0
	 */
	public function testAddPluraliseRule()
	{
		$chain = $this->inflector->addPluraliseRule(array('/foo/', '/bar/'));

		$this->assertThat(
			$chain,
			$this->identicalTo($this->inflector),
			'Checks chaining.'
		);

		$rules = TestHelper::getValue($this->inflector, 'rules');

		$this->assertCOntains(
			'/bar/',
			$rules['plural'],
			'Checks a pluralisation rule was added.'
		);
	}

	/**
	 * Method to test Inflector::addSingulariseRule().
	 *
	 * @return  void
	 *
	 * @covers  Joomla\String\Inflector::addSingulariseRule
	 * @since   1.0
	 */
	public function testAddSingulariseRule()
	{
		$chain = $this->inflector->addSingulariseRule(array('/foo/', '/bar/'));

		$this->assertThat(
			$chain,
			$this->identicalTo($this->inflector),
			'Checks chaining.'
		);

		$rules = TestHelper::getValue($this->inflector, 'rules');

		$this->assertContains(
			'/bar/',
			$rules['singular'],
			'Checks a singularisation rule was added.'
		);
	}

	/**
	 * Method to test Inflector::getInstance().
	 *
	 * @return  void
	 *
	 * @covers  Joomla\String\Inflector::getInstance
	 * @since   1.0
	 */
	public function testGetInstance()
	{
		$this->assertInstanceOf(
			'Joomla\\String\\Inflector',
			Inflector::getInstance(),
			'Check getInstance returns the right class.'
		);

		// Inject an instance an test.
		TestHelper::setValue($this->inflector, 'instance', new \stdClass);

		$this->assertThat(
			Inflector::getInstance(),
			$this->equalTo(new \stdClass),
			'Checks singleton instance is returned.'
		);

		$this->assertInstanceOf(
			'Joomla\\String\\Inflector',
			Inflector::getInstance(true),
			'Check getInstance a fresh object with true argument even though the instance is set to something else.'
		);
	}

	/**
	 * Method to test Inflector::isCountable().
	 *
	 * @param   string   $input     A string.
	 * @param   boolean  $expected  The expected result of the function call.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\String\Inflector::isCountable
	 * @dataProvider  seedIsCountable
	 * @since   1.0
	 */
	public function testIsCountable($input, $expected)
	{
		$this->assertThat(
			$this->inflector->isCountable($input),
			$this->equalTo($expected)
		);
	}

	/**
	 * Method to test Inflector::isPlural().
	 *
	 * @param   string  $singular  The singular form of a word.
	 * @param   string  $plural    The plural form of a word.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\String\Inflector::isPlural
	 * @dataProvider  seedSinglePlural
	 * @since   1.0
	 */
	public function testIsPlural($singular, $plural)
	{
		$this->assertTrue(
			$this->inflector->isPlural($plural),
			'Checks the plural is a plural.'
		);

		if ($singular != $plural)
		{
			$this->assertFalse(
				$this->inflector->isPlural($singular),
				'Checks the singular is not plural.'
			);
		}
	}

	/**
	 * Method to test Inflector::isSingular().
	 *
	 * @param   string  $singular  The singular form of a word.
	 * @param   string  $plural    The plural form of a word.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\String\Inflector::isSingular
	 * @dataProvider  seedSinglePlural
	 * @since   1.0
	 */
	public function testIsSingular($singular, $plural)
	{
		$this->assertTrue(
			$this->inflector->isSingular($singular),
			'Checks the singular is a singular.'
		);

		if ($singular != $plural)
		{
			$this->assertFalse(
				$this->inflector->isSingular($plural),
				'Checks the plural is not singular.'
			);
		}
	}

	/**
	 * Method to test Inflector::toPlural().
	 *
	 * @param   string  $singular  The singular form of a word.
	 * @param   string  $plural    The plural form of a word.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\String\Inflector::toPlural
	 * @dataProvider  seedSinglePlural
	 * @since   1.0
	 */
	public function testToPlural($singular, $plural)
	{
		$this->assertThat(
			$this->inflector->toPlural($singular),
			$this->equalTo($plural)
		);
	}

	/**
	 * Method to test Inflector::toPlural().
	 *
	 * @return  void
	 *
	 * @covers  Joomla\String\Inflector::toPlural
	 * @since   1.2.0
	 */
	public function testToPluralAlreadyPlural()
	{
		$this->assertFalse($this->inflector->toPlural('buses'));
	}

	/**
	 * Method to test Inflector::toPlural().
	 *
	 * @param   string  $singular  The singular form of a word.
	 * @param   string  $plural    The plural form of a word.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\String\Inflector::toSingular
	 * @dataProvider  seedSinglePlural
	 * @since   1.0
	 */
	public function testToSingular($singular, $plural)
	{
		$this->assertThat(
			$this->inflector->toSingular($plural),
			$this->equalTo($singular)
		);
	}

	/**
	 * Method to test Inflector::toPlural().
	 *
	 * @return  void
	 *
	 * @covers  Joomla\String\Inflector::toSingular
	 * @since   1.2.0
	 */
	public function testToSingularRetFalse()
	{
		// Assertion for already singular
		$this->assertFalse($this->inflector->toSingular('bus'));

		$this->assertFalse($this->inflector->toSingular('foo'));
	}
}
