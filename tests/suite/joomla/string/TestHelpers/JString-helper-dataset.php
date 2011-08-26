<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Utilities
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

class JStringTest_DataSet
{
	/**
	 * Tests for JString::increment.
	 *
	 * Each element contains $haystack, $needle, $offset, $expect,
	 *
	 * @var    array
	 * @since  11.3
	 */
	static public $increment = array(
		// string, style, number, expected
		'First default increment' => array('title', null, 0, 'title (2)'),
		'Second default increment' => array('title(2)', null, 0, 'title(3)'),
		'First dash increment' => array('title', 'dash', 0, 'title-2'),
		'Second dash increment' => array('title-2', 'dash', 0, 'title-3'),
		'Set default increment' => array('title', null, 4, 'title (4)'),
		'Unknown style fallback to default' => array('title', 'foo', 0, 'title (2)'),
	);

	/**
	 * Tests for JString::strpos.
	 *
	 * Each element contains $haystack, $needle, $offset, $expect,
	 *
	 * @var    array
	 * @since  11.2
	 */
	static public $strposTests = array(
		array('missing', 'sing', 0, 3),
		array('missing', 'sting', 0, false),
		array('missing', 'ing', 0, 4),
		array(' объектов на карте с', 'на карте', 0, 10),
		array('на карте с', 'на карте', 0, 0),
		array('на карте с', 'на каррте', 0, false),
		array('на карте с', 'на карте', 2, false),
		array('missing', 'sing', false, 3)
	);

	/**
	 * @var    array
	 * @since  11.2
	 */
	static public $strrposTests = array(
		array('missing', 'sing', 0, 3),
		array('missing', 'sting', 0, false),
		array('missing', 'ing', 0, 4),
		array(' объектов на карте с', 'на карте', 0, 10),
		array('на карте с', 'на карте', 0, 0),
		array('на карте с', 'на каррте', 0, false),
		array('на карте с', 'карт', 2, 3)
	);

	/**
	 * @var    array
	 * @since  11.2
	 */
	static public $substrTests = array(
		array('Mississauga', 4, false, 'issauga'),
		array(' объектов на карте с', 10, false, 'на карте с'),
		array(' объектов на карте с', 10, 5, 'на ка'),
		array(' объектов на карте с', -4, false, 'те с'),
		array(' объектов на карте с', 99, false, false)
	);

	/**
	 * @var    array
	 * @since  11.2
	 */
	static public $strtolowerTests = array(
		array('Joomla! Rocks', 'joomla! rocks')
	);

	/**
	 * @var    array
	 * @since  11.2
	 */
	static public $strtoupperTests = array(
		array('Joomla! Rocks', 'JOOMLA! ROCKS')
	);

	/**
	 * @var    array
	 * @since  11.2
	 */
	static public $strlenTests = array(
		array('Joomla! Rocks', 13)
	);

	/**
	 * @var    array
	 * @since  11.2
	 */
	static public $str_ireplaceTests = array(
		array('Pig', 'cow', 'the pig jumped', false, 'the cow jumped'),
		array('Pig', 'cow', 'the pig jumped', true, 'the cow jumped'),
		array('Pig', 'cow', 'the pig jumped over the cow', true, 'the cow jumped over the cow'),
		array(array('PIG', 'JUMPED'), array('cow', 'hopped'), true, 'the pig jumped over the pig', 'the cow hopped over the cow'),
		array('шил', 'биш', 'Би шил идэй чадна', true, 'Би биш идэй чадна')
	);

	/**
	 * @var    array
	 * @since  11.2
	 */
	static public $str_splitTests = array(
		array('string', 1, array('s','t','r','i','n','g')),
		array('string', 2, array('st','ri','ng')),
		array('волн', 3, array('вол','н')),
		array('волн', 1, array('в','о','л','н'))
	);

	/**
	 * @var    array
	 * @since  11.2
	 */
	static public $strcasecmpTests = array (
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

	/**
	 * @var    array
	 * @since  11.2
	 */
	static public $strcmpTests = array (
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

	/**
	 * @var    array
	 * @since  11.2
	 */
	static public $strcspnTests = array (
		array('subject <a> string <a>', '<>', false, false, 8),
		array('Би шил {123} идэй {456} чадна', '}{', null, false, 7),
		array('Би шил {123} идэй {456} чадна', '}{', 13, 10, 5)
	);

	/**
	 * @var    array
	 * @since  11.2
	 */
	static public $stristrTests = array (
		array('haystack', 'needle', false),
		array('before match, after match', 'match', 'match, after match'),
		array('Би шил идэй чадна', 'шил', 'шил идэй чадна')
	);

	/**
	 * @var    array
	 * @since  11.2
	 */
	static public $strrevTests = array (
		array('abc def', 'fed cba'),
		array('Би шил', 'лиш иБ')
	);

	/**
	 * @var    array
	 * @since  11.2
	 */
	static public $strspnTests = array (
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

	/**
	 * @var    array
	 * @since  11.2
	 */
	static public $substr_replaceTests = array (
		array('321 Main Street', 'Broadway Avenue', 4, false, '321 Broadway Avenue'),
		array('321 Main Street', 'Broadway', 4, 4, '321 Broadway Street'),
		array('чадна Би шил идэй чадна', '我能吞', 6, false, 'чадна 我能吞'),
		array('чадна Би шил идэй чадна', '我能吞', 6, 2, 'чадна 我能吞 шил идэй чадна')
	);

	/**
	 * Test data for JString::ltrim.
	 *
	 * @var    array  Elements of array($string, $charlist, $expect)
	 * @since  11.2
	 */
	static public $ltrimTests = array (
		array('   abc def', null, 'abc def'),
		array('   abc def', '', '   abc def'),
		array(' Би шил', null, 'Би шил'),
		array("\t\n\r\x0BБи шил", null, 'Би шил'),
		array("\x0B\t\n\rБи шил", "\t\n\x0B", "\rБи шил"),
		array("\x09Би шил\x0A", "\x09\x0A", "Би шил\x0A"),
		array('1234abc', '0123456789', 'abc')
	);

	/**
	 * @var    array
	 * @since  11.2
	 */
	static public $rtrimTests = array (
		array('abc def   ', null, 'abc def'),
		array('abc def   ', '', 'abc def   '),
		array('Би шил ', null, 'Би шил'),
		array("Би шил\t\n\r\x0B", null, 'Би шил'),
		array("Би шил\r\x0B\t\n", "\t\n\x0B", "Би шил\r"),
		array("\x09Би шил\x0A", "\x09\x0A", "\x09Би шил"),
		array('1234abc', 'abc', '01234')
	);

	/**
	 * @var    array
	 * @since  11.2
	 */
	static public $trimTests = array (
		array('  abc def   ', null, 'abc def'),
		array('  abc def   ', '', '  abc def   '),
		array('   Би шил ', null, 'Би шил'),
		array("\t\n\r\x0BБи шил\t\n\r\x0B", null, 'Би шил'),
		array("\x0B\t\n\rБи шил\r\x0B\t\n", "\t\n\x0B", "\rБи шил\r"),
		array("\x09Би шил\x0A", "\x09\x0A", "Би шил"),
		array('1234abc56789', '0123456789', 'abc')
	);

	/**
	 * @var    array
	 * @since  11.2
	 */
	static public $ucfirstTests = array (
		array('george', 'George'),
		array('мога', 'Мога'),
		array('ψυχοφθόρα', 'Ψυχοφθόρα')
	);

	/**
	 * @var    array
	 * @since  11.2
	 */
	static public $ucwordsTests = array (
		array('george washington', 'George Washington'),
		array("george\r\nwashington", "George\r\nWashington"),
		array('мога', 'Мога'),
		array('αβγ δεζ', 'Αβγ Δεζ'),
		array('åbc öde', 'Åbc Öde')
	);

	/**
	 * @var    array
	 * @since  11.2
	 */
	static public $transcodeTests = array (
		array('Åbc Öde €100', 'UTF-8', 'ISO-8859-1', "\xc5bc \xd6de EUR100"),
		array(array('Åbc Öde €100'), 'UTF-8', 'ISO-8859-1', null),
	);

	/**
	 * @var    array
	 * @since  11.2
	 */
	static public $validTests = array (
		array('george Мога Ž Ψυχοφθόρα ฉันกินกระจกได้ 我能吞下玻璃而不伤身体 ', true),
		array("\xFF ABC", false),
		array("0xfffd ABC", true),
		array('', true)
	);
}
