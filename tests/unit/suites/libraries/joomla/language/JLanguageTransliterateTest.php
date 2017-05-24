<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JLanguageTransliterate.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Language
 */
class JLanguageTransliterateTest extends PHPUnit_Framework_TestCase
{
	public function dataNames()
	{
		return array(
			array('Weiß', 'Weiss', 0),
			array('Goldmann', 'Goldmann', 0),
			array('Göbel', 'Goebel', 0),
			array('Weiss', 'Weiss', 0),
			array('Göthe', 'Goethe', 0),
			array('Götz', 'Goetz', 0),
			array('Weßling', 'Wessling', 0),
			array('Šíleně', 'Silene', 0),
			array('žluťoučký', 'zlutoucky', 0),
			array('Vašek', 'Vasek', 0),
			array('úpěl', 'upel', 0),
			array('olol', 'olol', 0),
			array('Göbel', 'Goebel', -1),
			array('Göbel', 'Göbel', 1)
		);
	}

	/**
	 * @param   string $word
	 * @param   string $result
	 * @param   string $case
	 *
	 * @dataProvider dataNames
	 */
	public function testUtf8LatinToAscii($word, $result, $case)
	{
		$this->assertEquals($result, JLanguageTransliterate::utf8_latin_to_ascii($word, $case));
	}
}
