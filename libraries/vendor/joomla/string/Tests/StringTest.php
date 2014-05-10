<?php
/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\String\Tests;

use Joomla\String\String;

/**
 * Test class for String.
 *
 * @since  1.0
 */
class StringTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var    String
	 * @since  1.0
	 */
	protected $object;

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedTestIncrement()
	{
		return array(
			// Note: string, style, number, expected
			'First default increment' => array('title', null, 0, 'title (2)'),
			'Second default increment' => array('title(2)', null, 0, 'title(3)'),
			'First dash increment' => array('title', 'dash', 0, 'title-2'),
			'Second dash increment' => array('title-2', 'dash', 0, 'title-3'),
			'Set default increment' => array('title', null, 4, 'title (4)'),
			'Unknown style fallback to default' => array('title', 'foo', 0, 'title (2)'),
		);
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedTestStrpos()
	{
		return array(
			array('missing', 'sing', 0, 3),
			array('missing', 'sting', 0, false),
			array('missing', 'ing', 0, 4),
			array(' объектов на карте с', 'на карте', 0, 10),
			array('на карте с', 'на карте', 0, 0),
			array('на карте с', 'на каррте', 0, false),
			array('на карте с', 'на карте', 2, false),
			array('missing', 'sing', false, 3)
		);
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedTestGetStrrpos()
	{
		return array(
			array('missing', 'sing', 0, 3),
			array('missing', 'sting', 0, false),
			array('missing', 'ing', 0, 4),
			array(' объектов на карте с', 'на карте', 0, 10),
			array('на карте с', 'на карте', 0, 0),
			array('на карте с', 'на каррте', 0, false),
			array('на карте с', 'карт', 2, 3)
		);
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedTestSubstr()
	{
		return array(
			array('Mississauga', 4, false, 'issauga'),
			array(' объектов на карте с', 10, false, 'на карте с'),
			array(' объектов на карте с', 10, 5, 'на ка'),
			array(' объектов на карте с', -4, false, 'те с'),
			array(' объектов на карте с', 99, false, false)
		);
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedTestStrtolower()
	{
		return array(
			array('Joomla! Rocks', 'joomla! rocks')
		);
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedTestStrtoupper()
	{
		return array(
			array('Joomla! Rocks', 'JOOMLA! ROCKS')
		);
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedTestStrlen()
	{
		return array(
			array('Joomla! Rocks', 13)
		);
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedTestStr_ireplace()
	{
		return array(
			array('Pig', 'cow', 'the pig jumped', false, 'the cow jumped'),
			array('Pig', 'cow', 'the pig jumped', true, 'the cow jumped'),
			array('Pig', 'cow', 'the pig jumped over the cow', true, 'the cow jumped over the cow'),
			array(array('PIG', 'JUMPED'), array('cow', 'hopped'), 'the pig jumped over the pig', true, 'the cow hopped over the cow'),
			array('шил', 'биш', 'Би шил идэй чадна', true, 'Би биш идэй чадна'),
			array('/', ':', '/test/slashes/', true, ':test:slashes:'),
		);
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedTestStr_split()
	{
		return array(
			array('string', 1, array('s', 't', 'r', 'i', 'n', 'g')),
			array('string', 2, array('st', 'ri', 'ng')),
			array('волн', 3, array('вол', 'н')),
			array('волн', 1, array('в', 'о', 'л', 'н'))
		);
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedTestStrcasecmp()
	{
		return array(
			array('THIS IS STRING1', 'this is string1', false, 0),
			array('this is string1', 'this is string2', false, -1),
			array('this is string2', 'this is string1', false, 1),
			array('бгдпт', 'бгдпт', false, 0),
			array('àbc', 'abc', array('fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR.UTF-8@euro', 'French_Standard', 'french', 'fr_FR', 'fre_FR'), 1),
			array('àbc', 'bcd', array('fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR.UTF-8@euro', 'French_Standard', 'french', 'fr_FR', 'fre_FR'), -1),
			array('é', 'è', array('fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR.UTF-8@euro', 'French_Standard', 'french', 'fr_FR', 'fre_FR'), -1),
			array('É', 'é', array('fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR.UTF-8@euro', 'French_Standard', 'french', 'fr_FR', 'fre_FR'), 0),
			array('œ', 'p', array('fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR.UTF-8@euro', 'French_Standard', 'french', 'fr_FR', 'fre_FR'), -1),
			array('œ', 'n', array('fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR.UTF-8@euro', 'French_Standard', 'french', 'fr_FR', 'fre_FR'), 1),
		);
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedTestStrcmp()
	{
		return array(
			array('THIS IS STRING1', 'this is string1', false, -1),
			array('this is string1', 'this is string2', false, -1),
			array('this is string2', 'this is string1', false, 1),
			array('a', 'B', false, 1),
			array('A', 'b', false, -1),
			array('Àbc', 'abc', array('fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR.UTF-8@euro', 'French_Standard', 'french', 'fr_FR', 'fre_FR'), 1),
			array('Àbc', 'bcd', array('fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR.UTF-8@euro', 'French_Standard', 'french', 'fr_FR', 'fre_FR'), -1),
			array('É', 'è', array('fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR.UTF-8@euro', 'French_Standard', 'french', 'fr_FR', 'fre_FR'), -1),
			array('é', 'È', array('fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR.UTF-8@euro', 'French_Standard', 'french', 'fr_FR', 'fre_FR'), -1),
			array('Œ', 'p', array('fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR.UTF-8@euro', 'French_Standard', 'french', 'fr_FR', 'fre_FR'), -1),
			array('Œ', 'n', array('fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR.UTF-8@euro', 'French_Standard', 'french', 'fr_FR', 'fre_FR'), 1),
			array('œ', 'N', array('fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR.UTF-8@euro', 'French_Standard', 'french', 'fr_FR', 'fre_FR'), 1),
			array('œ', 'P', array('fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR.UTF-8@euro', 'French_Standard', 'french', 'fr_FR', 'fre_FR'), -1),
		);
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedTestStrcspn()
	{
		return array(
			array('subject <a> string <a>', '<>', false, false, 8),
			array('Би шил {123} идэй {456} чадна', '}{', null, false, 7),
			array('Би шил {123} идэй {456} чадна', '}{', 13, 10, 5)
		);
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedTestStristr()
	{
		return array(
			array('haystack', 'needle', false),
			array('before match, after match', 'match', 'match, after match'),
			array('Би шил идэй чадна', 'шил', 'шил идэй чадна')
		);
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedTestStrrev()
	{
		return array(
			array('abc def', 'fed cba'),
			array('Би шил', 'лиш иБ')
		);
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedTestStrspn()
	{
		return array(
			array('A321 Main Street', '0123456789', 1, 2, 2),
			array('321 Main Street', '0123456789', null, 2, 2),
			array('A321 Main Street', '0123456789', null, 10, 0),
			array('321 Main Street', '0123456789', null, null, 3),
			array('Main Street 321', '0123456789', null, -3, 0),
			array('321 Main Street', '0123456789', null, -13, 2),
			array('321 Main Street', '0123456789', null, -12, 3),
			array('A321 Main Street', '0123456789', 0, null, 0),
			array('A321 Main Street', '0123456789', 1, 10, 3),
			array('A321 Main Street', '0123456789', 1, null, 3),
			array('Би шил идэй чадна', 'Би', null, null, 2),
			array('чадна Би шил идэй чадна', 'Би', null, null, 0)
		);
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedTestSubstr_replace()
	{
		return array(
			array('321 Main Street', 'Broadway Avenue', 4, false, '321 Broadway Avenue'),
			array('321 Main Street', 'Broadway', 4, 4, '321 Broadway Street'),
			array('чадна Би шил идэй чадна', '我能吞', 6, false, 'чадна 我能吞'),
			array('чадна Би шил идэй чадна', '我能吞', 6, 2, 'чадна 我能吞 шил идэй чадна')
		);
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedTestLtrim()
	{
		return array(
			array('   abc def', null, 'abc def'),
			array('   abc def', '', '   abc def'),
			array(' Би шил', null, 'Би шил'),
			array("\t\n\r\x0BБи шил", null, 'Би шил'),
			array("\x0B\t\n\rБи шил", "\t\n\x0B", "\rБи шил"),
			array("\x09Би шил\x0A", "\x09\x0A", "Би шил\x0A"),
			array('1234abc', '0123456789', 'abc')
		);
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedTestRtrim()
	{
		return array(
			array('abc def   ', null, 'abc def'),
			array('abc def   ', '', 'abc def   '),
			array('Би шил ', null, 'Би шил'),
			array("Би шил\t\n\r\x0B", null, 'Би шил'),
			array("Би шил\r\x0B\t\n", "\t\n\x0B", "Би шил\r"),
			array("\x09Би шил\x0A", "\x09\x0A", "\x09Би шил"),
			array('1234abc', 'abc', '01234')
		);
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedTestTrim()
	{
		return array(
			array('  abc def   ', null, 'abc def'),
			array('  abc def   ', '', '  abc def   '),
			array('   Би шил ', null, 'Би шил'),
			array("\t\n\r\x0BБи шил\t\n\r\x0B", null, 'Би шил'),
			array("\x0B\t\n\rБи шил\r\x0B\t\n", "\t\n\x0B", "\rБи шил\r"),
			array("\x09Би шил\x0A", "\x09\x0A", "Би шил"),
			array('1234abc56789', '0123456789', 'abc')
		);
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedTestUcfirst()
	{
		return array(
			array('george', null, null, 'George'),
			array('мога', null, null, 'Мога'),
			array('ψυχοφθόρα', null, null, 'Ψυχοφθόρα'),
			array('dr jekill and mister hyde', ' ', null, 'Dr Jekill And Mister Hyde'),
			array('dr jekill and mister hyde', ' ', '_', 'Dr_Jekill_And_Mister_Hyde'),
			array('dr jekill and mister hyde', ' ', '', 'DrJekillAndMisterHyde'),
		);
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedTestUcwords()
	{
		return array(
			array('george washington', 'George Washington'),
			array("george\r\nwashington", "George\r\nWashington"),
			array('мога', 'Мога'),
			array('αβγ δεζ', 'Αβγ Δεζ'),
			array('åbc öde', 'Åbc Öde')
		);
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedTestTranscode()
	{
		return array(
			array('Åbc Öde €100', 'UTF-8', 'ISO-8859-1', "\xc5bc \xd6de EUR100"),
			array(array('Åbc Öde €100'), 'UTF-8', 'ISO-8859-1', null),
		);
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function seedTestValid()
	{
		return array(
			array('george Мога Ž Ψυχοφθόρα ฉันกินกระจกได้ 我能吞下玻璃而不伤身体 ', true),
			array("\xFF ABC", false),
			array("0xfffd ABC", true),
			array('', true)
		);
	}

	/**
	 * Test...
	 *
	 * @param   string  $string    @todo
	 * @param   string  $style     @todo
	 * @param   string  $number    @todo
	 * @param   string  $expected  @todo
	 *
	 * @return  void
	 *
	 * @dataProvider  seedTestIncrement
	 * @since      1.0
	 * @covers        Joomla\String\String::increment
	 */
	public function testIncrement($string, $style, $number, $expected)
	{
		$this->assertThat(
			String::increment($string, $style, $number),
			$this->equalTo($expected)
		);
	}

	// @codingStandardsIgnoreStart
	// @todo Arguments with default values must be at the end of the argument list

	/**
	 * Test...
	 *
	 * @param   string   $haystack  @todo
	 * @param   string   $needle    @todo
	 * @param   integer  $offset    @todo
	 * @param   string   $expect    @todo
	 *
	 * @return  void
	 *
	 * @dataProvider  seedTestStrpos
	 * @since      1.0
	 * @covers        Joomla\String\String::strpos
	 */
	public function testStrpos($haystack, $needle, $offset = 0, $expect)
	{
		$actual = String::strpos($haystack, $needle, $offset);
		$this->assertEquals($expect, $actual);
	}

	// @codingStandardsIgnoreEnd

	// @codingStandardsIgnoreStart
	// @todo Arguments with default values must be at the end of the argument list

	/**
	 * Test...
	 *
	 * @param   string   $haystack  @todo
	 * @param   string   $needle    @todo
	 * @param   integer  $offset    @todo
	 * @param   string   $expect    @todo
	 *
	 * @return  array
	 *
	 * @dataProvider  seedTestGetStrrpos
	 * @since      1.0
	 * @covers        Joomla\String\String::strrpos
	 */
	public function testStrrpos($haystack, $needle, $offset = 0, $expect)
	{
		$actual = String::strrpos($haystack, $needle, $offset);
		$this->assertEquals($expect, $actual);
	}

	// @codingStandardsIgnoreEnd

	// @codingStandardsIgnoreStart
	// @todo Arguments with default values must be at the end of the argument list

	/**
	 * Test...
	 *
	 * @param   string    $string  @todo
	 * @param   string    $start   @todo
	 * @param   bool|int  $length  @todo
	 * @param   string    $expect  @todo
	 *
	 * @return  array
	 *
	 * @dataProvider  seedTestSubstr
	 * @since      1.0
	 * @covers        Joomla\String\String::substr
	 */
	public function testSubstr($string, $start, $length = false, $expect)
	{
		$actual = String::substr($string, $start, $length);
		$this->assertEquals($expect, $actual);
	}

	// @codingStandardsIgnoreEnd

	/**
	 * Test...
	 *
	 * @param   string  $string  @todo
	 * @param   string  $expect  @todo
	 *
	 * @return  array
	 *
	 * @dataProvider  seedTestStrtolower
	 * @since      1.0
	 * @covers        Joomla\String\String::strtolower
	 */
	public function testStrtolower($string, $expect)
	{
		$actual = String::strtolower($string);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $string  @todo
	 * @param   string  $expect  @todo
	 *
	 * @return  array
	 *
	 * @dataProvider  seedTestStrtoupper
	 * @since      1.0
	 * @covers        Joomla\String\String::strtoupper
	 */
	public function testStrtoupper($string, $expect)
	{
		$actual = String::strtoupper($string);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $string  @todo
	 * @param   string  $expect  @todo
	 *
	 * @return  array
	 *
	 * @dataProvider  seedTestStrlen
	 * @since      1.0
	 * @covers        Joomla\String\String::strlen
	 */
	public function testStrlen($string, $expect)
	{
		$actual = String::strlen($string);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string   $search   @todo
	 * @param   string   $replace  @todo
	 * @param   string   $subject  @todo
	 * @param   integer  $count    @todo
	 * @param   string   $expect   @todo
	 *
	 * @return  array
	 *
	 * @dataProvider  seedTestStr_ireplace
	 * @since      1.0
	 * @covers        Joomla\String\String::str_ireplace
	 */
	public function testStr_ireplace($search, $replace, $subject, $count, $expect)
	{
		$actual = String::str_ireplace($search, $replace, $subject, $count);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $string        @todo
	 * @param   string  $split_length  @todo
	 * @param   string  $expect        @todo
	 *
	 * @return  array
	 *
	 * @dataProvider  seedTestStr_split
	 * @since      1.0
	 * @covers        Joomla\String\String::str_split
	 */
	public function testStr_split($string, $split_length, $expect)
	{
		$actual = String::str_split($string, $split_length);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $string1  @todo
	 * @param   string  $string2  @todo
	 * @param   string  $locale   @todo
	 * @param   string  $expect   @todo
	 *
	 * @return  array
	 *
	 * @dataProvider  seedTestStrcasecmp
	 * @since      1.0
	 * @covers        Joomla\String\String::strcasecmp
	 */
	public function testStrcasecmp($string1, $string2, $locale, $expect)
	{
		// Convert the $locale param to a string if it is an array
		if (is_array($locale))
		{
			$locale = "'" . implode("', '", $locale) . "'";
		}

		if (substr(php_uname(), 0, 6) == 'Darwin' && $locale != false)
		{
			$this->markTestSkipped('Darwin bug prevents foreign conversion from working properly');
		}
		elseif ($locale != false && !setlocale(LC_COLLATE, $locale))
		{
			$this->markTestSkipped("Locale {$locale} is not available.");
		}
		else
		{
			$actual = String::strcasecmp($string1, $string2, $locale);

			if ($actual != 0)
			{
				$actual = $actual / abs($actual);
			}

			$this->assertEquals($expect, $actual);
		}
	}

	/**
	 * Test...
	 *
	 * @param   string  $string1  @todo
	 * @param   string  $string2  @todo
	 * @param   string  $locale   @todo
	 * @param   string  $expect   @todo
	 *
	 * @return  array
	 *
	 * @dataProvider  seedTestStrcmp
	 * @since      1.0
	 * @covers        Joomla\String\String::strcmp
	 */
	public function testStrcmp($string1, $string2, $locale, $expect)
	{
		// Convert the $locale param to a string if it is an array
		if (is_array($locale))
		{
			$locale = "'" . implode("', '", $locale) . "'";
		}

		if (substr(php_uname(), 0, 6) == 'Darwin' && $locale != false)
		{
			$this->markTestSkipped('Darwin bug prevents foreign conversion from working properly');
		}
		elseif ($locale != false && !setlocale(LC_COLLATE, $locale))
		{
			// If the locale is not available, we can't have to transcode the string and can't reliably compare it.
			$this->markTestSkipped("Locale {$locale} is not available.");
		}
		else
		{
			$actual = String::strcmp($string1, $string2, $locale);

			if ($actual != 0)
			{
				$actual = $actual / abs($actual);
			}

			$this->assertEquals($expect, $actual);
		}
	}

	/**
	 * Test...
	 *
	 * @param   string   $haystack  @todo
	 * @param   string   $needles   @todo
	 * @param   integer  $start     @todo
	 * @param   integer  $len       @todo
	 * @param   string   $expect    @todo
	 *
	 * @return  array
	 *
	 * @dataProvider  seedTestStrcspn
	 * @since      1.0
	 * @covers        Joomla\String\String::strcspn
	 */
	public function testStrcspn($haystack, $needles, $start, $len, $expect)
	{
		$actual = String::strcspn($haystack, $needles, $start, $len);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $haystack  @todo
	 * @param   string  $needle    @todo
	 * @param   string  $expect    @todo
	 *
	 * @return  array
	 *
	 * @dataProvider  seedTestStristr
	 * @since      1.0
	 * @covers        Joomla\String\String::stristr
	 */
	public function testStristr($haystack, $needle, $expect)
	{
		$actual = String::stristr($haystack, $needle);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $string  @todo
	 * @param   string  $expect  @todo
	 *
	 * @return  array
	 *
	 * @dataProvider  seedTestStrrev
	 * @since      1.0
	 * @covers        Joomla\String\String::strrev
	 */
	public function testStrrev($string, $expect)
	{
		$actual = String::strrev($string);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string   $subject  @todo
	 * @param   string   $mask     @todo
	 * @param   integer  $start    @todo
	 * @param   integer  $length   @todo
	 * @param   string   $expect   @todo
	 *
	 * @return  array
	 *
	 * @dataProvider  seedTestStrspn
	 * @since      1.0
	 * @covers        Joomla\String\String::strspn
	 */
	public function testStrspn($subject, $mask, $start, $length, $expect)
	{
		$actual = String::strspn($subject, $mask, $start, $length);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string   $string       @todo
	 * @param   string   $replacement  @todo
	 * @param   integer  $start        @todo
	 * @param   integer  $length       @todo
	 * @param   string   $expect       @todo
	 *
	 * @return  array
	 *
	 * @dataProvider  seedTestSubstr_replace
	 * @since      1.0
	 * @covers        Joomla\String\String::substr_replace
	 */
	public function testSubstr_replace($string, $replacement, $start, $length, $expect)
	{
		$actual = String::substr_replace($string, $replacement, $start, $length);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $string    @todo
	 * @param   string  $charlist  @todo
	 * @param   string  $expect    @todo
	 *
	 * @return  array
	 *
	 * @dataProvider  seedTestLtrim
	 * @since      1.0
	 * @covers        Joomla\String\String::ltrim
	 */
	public function testLtrim($string, $charlist, $expect)
	{
		if ($charlist === null)
		{
			$actual = String::ltrim($string);
		}
		else
		{
			$actual = String::ltrim($string, $charlist);
		}

		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $string    @todo
	 * @param   string  $charlist  @todo
	 * @param   string  $expect    @todo
	 *
	 * @return  array
	 *
	 * @dataProvider  seedTestRtrim
	 * @since      1.0
	 * @covers        Joomla\String\String::rtrim
	 */
	public function testRtrim($string, $charlist, $expect)
	{
		if ($charlist === null)
		{
			$actual = String::rtrim($string);
		}
		else
		{
			$actual = String::rtrim($string, $charlist);
		}

		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $string    @todo
	 * @param   string  $charlist  @todo
	 * @param   string  $expect    @todo
	 *
	 * @return  array
	 *
	 * @dataProvider  seedTestTrim
	 * @since      1.0
	 * @covers        Joomla\String\String::trim
	 */
	public function testTrim($string, $charlist, $expect)
	{
		if ($charlist === null)
		{
			$actual = String::trim($string);
		}
		else
		{
			$actual = String::trim($string, $charlist);
		}

		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $string        @todo
	 * @param   string  $delimiter     @todo
	 * @param   string  $newDelimiter  @todo
	 * @param   string  $expect        @todo
	 *
	 * @return  array
	 *
	 * @dataProvider  seedTestUcfirst
	 * @since      1.0
	 * @covers        Joomla\String\String::ucfirst
	 */
	public function testUcfirst($string, $delimiter, $newDelimiter, $expect)
	{
		$actual = String::ucfirst($string, $delimiter, $newDelimiter);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $string  @todo
	 * @param   string  $expect  @todo
	 *
	 * @return  array
	 *
	 * @dataProvider  seedTestUcwords
	 * @since      1.0
	 * @covers        Joomla\String\String::ucwords
	 */
	public function testUcwords($string, $expect)
	{
		$actual = String::ucwords($string);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $source         @todo
	 * @param   string  $from_encoding  @todo
	 * @param   string  $to_encoding    @todo
	 * @param   string  $expect         @todo
	 *
	 * @return  array
	 *
	 * @dataProvider  seedTestTranscode
	 * @since      1.0
	 * @covers        Joomla\String\String::transcode
	 */
	public function testTranscode($source, $from_encoding, $to_encoding, $expect)
	{
		$actual = String::transcode($source, $from_encoding, $to_encoding);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $string  @todo
	 * @param   string  $expect  @todo
	 *
	 * @return  array
	 *
	 * @dataProvider  seedTestValid
	 * @since      1.0
	 * @covers        Joomla\String\String::valid
	 */
	public function testValid($string, $expect)
	{
		$actual = String::valid($string);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $string  @todo
	 * @param   string  $expect  @todo
	 *
	 * @return  array
	 *
	 * @dataProvider  seedTestValid
	 * @since      1.0
	 * @covers        Joomla\String\String::compliant
	 */
	public function testCompliant($string, $expect)
	{
		$actual = String::compliant($string);
		$this->assertEquals($expect, $actual);
	}
}
