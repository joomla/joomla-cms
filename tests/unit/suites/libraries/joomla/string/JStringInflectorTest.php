<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  String
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test for the JStringInflector class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  String
 * @link        http://en.wikipedia.org/wiki/English_plural
 * @since       12.1
 */
class JStringInflectorTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JStringInflector
	 * @since  12.1
	 */
	protected $inflector;

	/**
	 * Method to seed data to testIsCountable.
	 *
	 * @return  array
	 *
	 * @since   12.1
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
	 * @since   12.1
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
	 * @since   12.1
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->inflector = JStringInflector::getInstance(true);
	}

	/**
	 * Method to test JStringInflector::_addRule().
	 *
	 * @return  void
	 *
	 * @since   12.1
	 * @covers  JStringInflector::_addRule
	 */
	public function test_addRule()
	{
		// Case 1
		TestReflection::invoke($this->inflector, '_addRule', '/foo/', 'singular');

		$rules = TestReflection::getValue($this->inflector, '_rules');

		$this->assertThat(
			in_array('/foo/', $rules['singular']),
			$this->isTrue(),
			'Checks if the singular rule was added correctly.'
		);

		// Case 2
		TestReflection::invoke($this->inflector, '_addRule', '/bar/', 'plural');

		$rules = TestReflection::getValue($this->inflector, '_rules');

		$this->assertThat(
			in_array('/bar/', $rules['plural']),
			$this->isTrue(),
			'Checks if the plural rule was added correctly.'
		);

		// Case 3
		TestReflection::invoke($this->inflector, '_addRule', array('/goo/', '/car/'), 'singular');

		$rules = TestReflection::getValue($this->inflector, '_rules');

		$this->assertThat(
			in_array('/goo/', $rules['singular']),
			$this->isTrue(),
			'Checks if an array of rules was added correctly (1).'
		);

		$this->assertThat(
			in_array('/car/', $rules['singular']),
			$this->isTrue(),
			'Checks if an array of rules was added correctly (2).'
		);
	}

	/**
	 * Method to test JStringInflector::_addRule().
	 *
	 * @return  void
	 *
	 * @since   12.1
	 * @expectedException  InvalidArgumentException
	 * @covers  JStringInflector::_addRule
	 */
	public function test_addRuleException()
	{
		TestReflection::invoke($this->inflector, '_addRule', new stdClass, 'singular');
	}

	/**
	 * Method to test JStringInflector::_getCachedPlural().
	 *
	 * @return  void
	 *
	 * @since   12.1
	 * @covers  JStringInflector::_getCachedPlural
	 */
	public function test_getCachedPlural()
	{
		// Reset the cache.
		TestReflection::setValue($this->inflector, '_cache', array('foo' => 'bar'));

		$this->assertThat(
			TestReflection::invoke($this->inflector, '_getCachedPlural', 'bar'),
			$this->isFalse(),
			'Checks for an uncached plural.'
		);

		$this->assertThat(
			TestReflection::invoke($this->inflector, '_getCachedPlural', 'foo'),
			$this->equalTo('bar'),
			'Checks for a cached plural word.'
		);
	}

	/**
	 * Method to test JStringInflector::_getCachedSingular().
	 *
	 * @return  void
	 *
	 * @since   12.1
	 * @covers  JStringInflector::_getCachedSingular
	 */
	public function test_getCachedSingular()
	{
		// Reset the cache.
		TestReflection::setValue($this->inflector, '_cache', array('foo' => 'bar'));

		$this->assertThat(
			TestReflection::invoke($this->inflector, '_getCachedSingular', 'foo'),
			$this->isFalse(),
			'Checks for an uncached singular.'
		);

		$this->assertThat(
			TestReflection::invoke($this->inflector, '_getCachedSingular', 'bar'),
			$this->equalTo('foo'),
			'Checks for a cached singular word.'
		);
	}

	/**
	 * Method to test JStringInflector::_matchRegexRule().
	 *
	 * @return  void
	 *
	 * @since   12.1
	 * @covers  JStringInflector::_matchRegexRule
	 */
	public function test_matchRegexRule()
	{
		$this->assertThat(
			TestReflection::invoke($this->inflector, '_matchRegexRule', 'xyz', 'plural'),
			$this->equalTo('xyzs'),
			'Checks pluralising against the basic regex.'
		);

		$this->assertThat(
			TestReflection::invoke($this->inflector, '_matchRegexRule', 'xyzs', 'singular'),
			$this->equalTo('xyz'),
			'Checks singularising against the basic regex.'
		);

		$this->assertThat(
			TestReflection::invoke($this->inflector, '_matchRegexRule', 'xyz', 'singular'),
			$this->isFalse(),
			'Checks singularising against an unmatched regex.'
		);
	}

	/**
	 * Method to test JStringInflector::_setCache().
	 *
	 * @return  void
	 *
	 * @since   12.1
	 * @covers  JStringInflector::_setCache
	 */
	public function test_setCache()
	{
		TestReflection::invoke($this->inflector, '_setCache', 'foo', 'bar');

		$cache = TestReflection::getValue($this->inflector, '_cache');

		$this->assertThat(
			$cache['foo'],
			$this->equalTo('bar'),
			'Checks the cache was set.'
		);

		TestReflection::invoke($this->inflector, '_setCache', 'foo', 'car');

		$cache = TestReflection::getValue($this->inflector, '_cache');

		$this->assertThat(
			$cache['foo'],
			$this->equalTo('car'),
			'Checks an existing value in the cache was reset.'
		);
	}

	/**
	 * Method to test JStringInflector::addCountableRule().
	 *
	 * @return  void
	 *
	 * @since   12.1
	 * @covers  JStringInflector::addCountableRule
	 */
	public function testAddCountableRule()
	{
		// Add string.
		$this->inflector->addCountableRule('foo');

		$rules = TestReflection::getValue($this->inflector, '_rules');

		$this->assertThat(
			in_array('foo', $rules['countable']),
			$this->isTrue(),
			'Checks a countable rule was added.'
		);

		// Add array.
		$this->inflector->addCountableRule(array('goo', 'car'));

		$rules = TestReflection::getValue($this->inflector, '_rules');

		$this->assertThat(
			in_array('car', $rules['countable']),
			$this->isTrue(),
			'Checks a countable rule was added by array.'
		);
	}

	/**
	 * Method to test JStringInflector::addPluraliseRule().
	 *
	 * @return  void
	 *
	 * @since   12.1
	 * @covers  JStringInflector::addPluraliseRule
	 */
	public function testAddPluraliseRule()
	{
		$chain = $this->inflector->addPluraliseRule(array('/foo/', '/bar/'));

		$this->assertThat(
			$chain,
			$this->identicalTo($this->inflector),
			'Checks chaining.'
		);

		$rules = TestReflection::getValue($this->inflector, '_rules');

		$this->assertThat(
			in_array('/bar/', $rules['plural']),
			$this->isTrue(),
			'Checks a pluralisation rule was added.'
		);
	}

	/**
	 * Method to test JStringInflector::addSingulariseRule().
	 *
	 * @return  void
	 *
	 * @since   12.1
	 * @covers  JStringInflector::addSingulariseRule
	 */
	public function testAddSingulariseRule()
	{
		$chain = $this->inflector->addSingulariseRule(array('/foo/', '/bar/'));

		$this->assertThat(
			$chain,
			$this->identicalTo($this->inflector),
			'Checks chaining.'
		);

		$rules = TestReflection::getValue($this->inflector, '_rules');

		$this->assertThat(
			in_array('/bar/', $rules['singular']),
			$this->isTrue(),
			'Checks a singularisation rule was added.'
		);
	}

	/**
	 * Method to test JStringInflector::getInstance().
	 *
	 * @return  void
	 *
	 * @since   12.1
	 * @covers  JStringInflector::getInstance
	 */
	public function testGetInstance()
	{
		$this->assertInstanceOf(
			'JStringInflector',
			JStringInflector::getInstance(),
			'Check getInstance returns the right class.'
		);

		// Inject an instance an test.
		TestReflection::setValue($this->inflector, '_instance', new stdClass);

		$this->assertThat(
			JStringInflector::getInstance(),
			$this->equalTo(new stdClass),
			'Checks singleton instance is returned.'
		);

		$this->assertInstanceOf(
			'JStringInflector',
			JStringInflector::getInstance(true),
			'Check getInstance a fresh object with true argument even though the instance is set to something else.'
		);
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
	 * @since   12.1
	 * @covers  JStringInflector::isCountable
	 */
	public function testIsCountable($input, $expected)
	{
		$this->assertThat(
			$this->inflector->isCountable($input),
			$this->equalTo($expected)
		);
	}

	/**
	 * Method to test JStringInflector::isPlural().
	 *
	 * @param   string  $singular  The singular form of a word.
	 * @param   string  $plural    The plural form of a word.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedSinglePlural
	 * @since   12.1
	 * @covers  JStringInflector::isPlural
	 */
	public function testIsPlural($singular, $plural)
	{
		$this->assertThat(
			$this->inflector->isPlural($plural),
			$this->isTrue(),
			'Checks the plural is a plural.'
		);

		if ($singular != $plural)
		{
			$this->assertThat(
				$this->inflector->isPlural($singular),
				$this->isFalse(),
				'Checks the singular is not plural.'
			);
		}
	}

	/**
	 * Method to test JStringInflector::isSingular().
	 *
	 * @param   string  $singular  The singular form of a word.
	 * @param   string  $plural    The plural form of a word.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedSinglePlural
	 * @since   12.1
	 * @covers  JStringInflector::isSingular
	 */
	public function testIsSingular($singular, $plural)
	{
		$this->assertThat(
			$this->inflector->isSingular($singular),
			$this->isTrue(),
			'Checks the singular is a singular.'
		);

		if ($singular != $plural)
		{
			$this->assertThat(
				$this->inflector->isSingular($plural),
				$this->isFalse(),
				'Checks the plural is not singular.'
			);
		}
	}

	/**
	 * Method to test JStringInflector::toPlural().
	 *
	 * @param   string  $singular  The singular form of a word.
	 * @param   string  $plural    The plural form of a word.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedSinglePlural
	 * @since   12.1
	 * @covers  JStringInflector::toPlural
	 */
	public function testToPlural($singular, $plural)
	{
		$this->assertThat(
			$this->inflector->toPlural($singular),
			$this->equalTo($plural)
		);
	}

	/**
	 * Method to test JStringInflector::toPlural().
	 *
	 * @param   string  $singular  The singular form of a word.
	 * @param   string  $plural    The plural form of a word.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedSinglePlural
	 * @since   12.1
	 * @covers  JStringInflector::toSingular
	 */
	public function testToSingular($singular, $plural)
	{
		$this->assertThat(
			$this->inflector->toSingular($plural),
			$this->equalTo($singular)
		);
	}
}
