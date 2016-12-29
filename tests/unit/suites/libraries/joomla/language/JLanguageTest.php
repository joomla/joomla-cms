<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

jimport('joomla.filesystem.folder');

require_once __DIR__ . '/JLanguageInspector.php';

if (!class_exists('En_GBLocalise'))
{
	require_once __DIR__ . '/data/language/en-GB/en-GB.localise.php';
}

/**
 * Test class for JLanguage.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Language
 * @since       11.1
 */
class JLanguageTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var JLanguage
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$path = JPATH_TESTS . '/tmp/language';

		if (is_dir($path))
		{
			JFolder::delete($path);
		}

		JFolder::copy(__DIR__ . '/data/language', $path);

		$this->object = new JLanguage;
		$this->inspector = new JLanguageInspector('', true);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
		JFolder::delete(JPATH_TESTS . '/tmp/language');
		unset($this->object);
		unset($this->inspector);
		parent::tearDown();
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testGetInstance()
	{
		$instance = JLanguage::getInstance(null);
		$this->assertInstanceOf('JLanguage', $instance);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testConstruct()
	{
		// @codingStandardsIgnoreStart
		// @todo check the instanciating new classes without brackets sniff
		$instance = new JLanguage(null, true);
		// @codingStandardsIgnoreEnd

		$this->assertInstanceOf('JLanguage', $instance);
		$this->assertTrue($instance->getDebug());

		// @codingStandardsIgnoreStart
		// @todo check the instanciating new classes without brackets sniff
		$instance = new JLanguage(null, false);
		// @codingStandardsIgnoreEnd
		$this->assertInstanceOf('JLanguage', $instance);
		$this->assertFalse($instance->getDebug());
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function test_()
	{
		$string1 = 'delete';
		$string2 = "delete's";

		$this->assertEquals(
			'delete',
			$this->object->_($string1, false),
			'Line: ' . __LINE__ . ' Exact case should match when javascript safe is false '
		);

		$this->assertNotEquals(
			'Delete',
			$this->object->_($string1, false),
			'Line: ' . __LINE__ . ' Should be case sensitive when javascript safe is false'
		);

		$this->assertEquals(
			'delete',
			$this->object->_($string1, true),
			'Line: ' . __LINE__ . ' Exact case match should work when javascript safe is true'
		);

		$this->assertNotEquals(
			'Delete',
			$this->object->_($string1, true),
			'Line: ' . __LINE__ . ' Should be case sensitive when javascript safe is true'
		);

		$this->assertEquals(
			'delete\'s',
			$this->object->_($string2, false),
			'Line: ' . __LINE__ . ' Exact case should match when javascript safe is false '
		);

		$this->assertNotEquals(
			'Delete\'s',
			$this->object->_($string2, false),
			'Line: ' . __LINE__ . ' Should be case sensitive when javascript safe is false'
		);

		$this->assertEquals(
			"delete\'s",
			$this->object->_($string2, true),
			'Line: ' . __LINE__ . ' Exact case should match when javascript safe is true, also it calls addslashes (\' => \\\') '
		);

		$this->assertNotEquals(
			"Delete\'s",
			$this->object->_($string2, true),
			'Line: ' . __LINE__ . ' Should be case sensitive when javascript safe is true,, also it calls addslashes (\' => \\\') '
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testTransliterate()
	{
		$string1 = 'Así';
		$string2 = 'EÑE';

		$this->assertEquals(
			'asi',
			$this->object->transliterate($string1),
			'Line: ' . __LINE__
		);

		$this->assertNotEquals(
			'Asi',
			$this->object->transliterate($string1),
			'Line: ' . __LINE__
		);

		$this->assertNotEquals(
			'Así',
			$this->object->transliterate($string1),
			'Line: ' . __LINE__
		);

		$this->assertEquals(
			'ene',
			$this->object->transliterate($string2),
			'Line: ' . __LINE__
		);

		$this->assertNotEquals(
			'ENE',
			$this->object->transliterate($string2),
			'Line: ' . __LINE__
		);

		$this->assertNotEquals(
			'EÑE',
			$this->object->transliterate($string2),
			'Line: ' . __LINE__
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testGetTransliterator()
	{
		$lang = new JLanguage('');

		// The first time you run the method returns NULL
		// Only if there is an setTransliterator, this test is wrong
		$this->assertNull(
			$lang->getTransliterator()
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testSetTransliterator()
	{
		$function1 = 'phpinfo';
		$function2 = 'print';
		$lang = new JLanguage('');

		// Note: set -> $funtion1: set returns NULL and get returns $function1
		$this->assertNull(
			$lang->setTransliterator($function1)
		);

		$get = $lang->getTransliterator();
		$this->assertEquals(
			$function1,
			$get,
			'Line: ' . __LINE__
		);

		$this->assertNotEquals(
			$function2,
			$get,
			'Line: ' . __LINE__
		);

		// Note: set -> $function2: set returns $function1 and get retuns $function2
		$set = $lang->setTransliterator($function2);
		$this->assertEquals(
			$function1,
			$set,
			'Line: ' . __LINE__
		);

		$this->assertNotEquals(
			$function2,
			$set,
			'Line: ' . __LINE__
		);

		$this->assertEquals(
			$function2,
			$lang->getTransliterator(),
			'Line: ' . __LINE__
		);

		$this->assertNotEquals(
			$function1,
			$lang->getTransliterator(),
			'Line: ' . __LINE__
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testGetPluralSuffixes()
	{
		$this->assertEquals(
			array('0'),
			$this->object->getPluralSuffixes(0),
			'Line: ' . __LINE__
		);

		$this->assertEquals(
			array('1'),
			$this->object->getPluralSuffixes(1),
			'Line: ' . __LINE__
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testGetPluralSuffixesCallback()
	{
		$lang = new JLanguage('');

		$this->assertTrue(
			is_callable($lang->getPluralSuffixesCallback())
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testSetPluralSuffixesCallback()
	{
		$function1 = 'phpinfo';
		$function2 = 'print';
		$lang = new JLanguage('');

		$this->assertTrue(
			is_callable($lang->getPluralSuffixesCallback())
		);

		$this->assertTrue(
			is_callable($lang->setPluralSuffixesCallback($function1))
		);

		$get = $lang->getPluralSuffixesCallback();
		$this->assertEquals(
			$function1,
			$get,
			'Line: ' . __LINE__
		);

		$this->assertNotEquals(
			$function2,
			$get,
			'Line: ' . __LINE__
		);

		// Note: set -> $function2: set returns $function1 and get retuns $function2
		$set = $lang->setPluralSuffixesCallback($function2);
		$this->assertEquals(
			$function1,
			$set,
			'Line: ' . __LINE__
		);

		$this->assertNotEquals(
			$function2,
			$set,
			'Line: ' . __LINE__
		);

		$this->assertEquals(
			$function2,
			$lang->getPluralSuffixesCallback(),
			'Line: ' . __LINE__
		);

		$this->assertNotEquals(
			$function1,
			$lang->getPluralSuffixesCallback(),
			'Line: ' . __LINE__
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testGetIgnoredSearchWords()
	{
		$lang = new JLanguage('');

		$this->assertEquals(
			array('and', 'in', 'on'),
			$lang->getIgnoredSearchWords(),
			'Line: ' . __LINE__
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testGetIgnoredSearchWordsCallback()
	{
		$lang = new JLanguage('');

		$this->assertTrue(
			is_callable($lang->getIgnoredSearchWordsCallback())
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testSetIgnoredSearchWordsCallback()
	{
		$function1 = 'phpinfo';
		$function2 = 'print';
		$lang = new JLanguage('');

		$this->assertTrue(
			is_callable($lang->getIgnoredSearchWordsCallback())
		);

		// Note: set -> $funtion1: set returns NULL and get returns $function1
		$this->assertTrue(
			is_callable($lang->setIgnoredSearchWordsCallback($function1))
		);

		$get = $lang->getIgnoredSearchWordsCallback();
		$this->assertEquals(
			$function1,
			$get,
			'Line: ' . __LINE__
		);

		$this->assertNotEquals(
			$function2,
			$get,
			'Line: ' . __LINE__
		);

		// Note: set -> $function2: set returns $function1 and get retuns $function2
		$set = $lang->setIgnoredSearchWordsCallback($function2);
		$this->assertEquals(
			$function1,
			$set,
			'Line: ' . __LINE__
		);

		$this->assertNotEquals(
			$function2,
			$set,
			'Line: ' . __LINE__
		);

		$this->assertEquals(
			$function2,
			$lang->getIgnoredSearchWordsCallback(),
			'Line: ' . __LINE__
		);

		$this->assertNotEquals(
			$function1,
			$lang->getIgnoredSearchWordsCallback(),
			'Line: ' . __LINE__
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testGetLowerLimitSearchWord()
	{
		$lang = new JLanguage('');

		$this->assertEquals(
			3,
			$lang->getLowerLimitSearchWord(),
			'Line: ' . __LINE__
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testGetLowerLimitSearchWordCallback()
	{
		$lang = new JLanguage('');

		$this->assertTrue(
			is_callable($lang->getLowerLimitSearchWordCallback())
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testSetLowerLimitSearchWordCallback()
	{
		$function1 = 'phpinfo';
		$function2 = 'print';
		$lang = new JLanguage('');

		$this->assertTrue(
			is_callable($lang->getLowerLimitSearchWordCallback())
		);

		// Note: set -> $funtion1: set returns NULL and get returns $function1
		$this->assertTrue(
			is_callable($lang->setLowerLimitSearchWordCallback($function1))
		);

		$get = $lang->getLowerLimitSearchWordCallback();
		$this->assertEquals(
			$function1,
			$get,
			'Line: ' . __LINE__
		);

		$this->assertNotEquals(
			$function2,
			$get,
			'Line: ' . __LINE__
		);

		// Note: set -> $function2: set returns $function1 and get retuns $function2
		$set = $lang->setLowerLimitSearchWordCallback($function2);
		$this->assertEquals(
			$function1,
			$set,
			'Line: ' . __LINE__
		);

		$this->assertNotEquals(
			$function2,
			$set,
			'Line: ' . __LINE__
		);

		$this->assertEquals(
			$function2,
			$lang->getLowerLimitSearchWordCallback(),
			'Line: ' . __LINE__
		);

		$this->assertNotEquals(
			$function1,
			$lang->getLowerLimitSearchWordCallback(),
			'Line: ' . __LINE__
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testGetUpperLimitSearchWord()
	{
		$lang = new JLanguage('');

		$this->assertEquals(
			200,
			$lang->getUpperLimitSearchWord(),
			'Line: ' . __LINE__
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testGetUpperLimitSearchWordCallback()
	{
		$lang = new JLanguage('');

		$this->assertTrue(
			is_callable($lang->getUpperLimitSearchWordCallback())
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testSetUpperLimitSearchWordCallback()
	{
		$function1 = 'phpinfo';
		$function2 = 'print';
		$lang = new JLanguage('');

		$this->assertTrue(
			is_callable($lang->getUpperLimitSearchWordCallback())
		);

		// Note: set -> $funtion1: set returns NULL and get returns $function1
		$this->assertTrue(
			is_callable($lang->setUpperLimitSearchWordCallback($function1))
		);

		$get = $lang->getUpperLimitSearchWordCallback();
		$this->assertEquals(
			$function1,
			$get,
			'Line: ' . __LINE__
		);

		$this->assertNotEquals(
			$function2,
			$get,
			'Line: ' . __LINE__
		);

		// Note: set -> $function2: set returns $function1 and get retuns $function2
		$set = $lang->setUpperLimitSearchWordCallback($function2);
		$this->assertEquals(
			$function1,
			$set,
			'Line: ' . __LINE__
		);

		$this->assertNotEquals(
			$function2,
			$set,
			'Line: ' . __LINE__
		);

		$this->assertEquals(
			$function2,
			$lang->getUpperLimitSearchWordCallback(),
			'Line: ' . __LINE__
		);

		$this->assertNotEquals(
			$function1,
			$lang->getUpperLimitSearchWordCallback(),
			'Line: ' . __LINE__
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testGetSearchDisplayedCharactersNumber()
	{
		$lang = new JLanguage('');

		$this->assertEquals(
			200,
			$lang->getSearchDisplayedCharactersNumber(),
			'Line: ' . __LINE__
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testGetSearchDisplayedCharactersNumberCallback()
	{
		$lang = new JLanguage('');

		$this->assertTrue(
			is_callable($lang->getSearchDisplayedCharactersNumberCallback())
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testSetSearchDisplayedCharactersNumberCallback()
	{
		$function1 = 'phpinfo';
		$function2 = 'print';
		$lang = new JLanguage('');

		$this->assertTrue(
			is_callable($lang->getSearchDisplayedCharactersNumberCallback())
		);

		// Note: set -> $funtion1: set returns NULL and get returns $function1
		$this->assertTrue(
			is_callable($lang->setSearchDisplayedCharactersNumberCallback($function1))
		);

		$get = $lang->getSearchDisplayedCharactersNumberCallback();
		$this->assertEquals(
			$function1,
			$get,
			'Line: ' . __LINE__
		);

		$this->assertNotEquals(
			$function2,
			$get,
			'Line: ' . __LINE__
		);

		// Note: set -> $function2: set returns $function1 and get retuns $function2
		$set = $lang->setSearchDisplayedCharactersNumberCallback($function2);
		$this->assertEquals(
			$function1,
			$set,
			'Line: ' . __LINE__
		);

		$this->assertNotEquals(
			$function2,
			$set,
			'Line: ' . __LINE__
		);

		$this->assertEquals(
			$function2,
			$lang->getSearchDisplayedCharactersNumberCallback(),
			'Line: ' . __LINE__
		);

		$this->assertNotEquals(
			$function1,
			$lang->getSearchDisplayedCharactersNumberCallback(),
			'Line: ' . __LINE__
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testExists()
	{
		$this->assertFalse(
			$this->object->exists(null)
		);

		$basePath = __DIR__ . '/data';

		$this->assertTrue(
			$this->object->exists('en-GB', $basePath)
		);

		$this->assertFalse(
			$this->object->exists('es-ES', $basePath)
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testParse()
	{
		$strings = $this->inspector->parse(__DIR__ . '/data/good.ini');

		$this->assertThat(
			$strings,
			$this->logicalNot($this->equalTo(array())),
			'Line: ' . __LINE__ . ' good ini file should load properly.'
		);

		$this->assertEquals(
			$strings,
			array('FOO' => 'Bar'),
			'Line: ' . __LINE__ . ' test that the strings were parsed correctly.'
		);

		$strings = $this->inspector->parse(__DIR__ . '/data/bad.ini');

		$this->assertEquals(
			$strings,
			array(),
			'Line: ' . __LINE__ . ' bad ini file should not load properly.'
		);
	}

	/**
	 * Test debugFile reports no errors on a good file
	 *
	 * @return void
	 */
	public function testVerifyDebugFileFindsNoErrorsInAGoodFile()
	{
		$this->assertSame(0, $this->object->debugFile(__DIR__ . '/data/good.ini'));
	}

	/**
	 * Test debugFile reports errors on a bad file
	 *
	 * @return void
	 */
	public function testVerifyDebugFileFindsErrorsInABadFile()
	{
		$this->assertGreaterThan(0, $this->object->debugFile(__DIR__ . '/data/bad.ini'));
	}

	/**
	 * Test debugFile throws an Exception when the file does not exist
	 *
	 * @expectedException  InvalidArgumentException
	 *
	 * @return void
	 */
	public function testVerifyDebugFileThrowsAnExceptionWhenTheFileDoesNotExist()
	{
		$this->object->debugFile(__DIR__ . '/data/not-existing.ini');
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testGet()
	{
		$this->assertNull(
			$this->object->get('noExist')
		);

		$this->assertEquals(
			'abc',
			$this->object->get('noExist', 'abc')
		);

		// Note: property = tag, returns en-GB (default language)
		$this->assertEquals(
			'en-GB',
			$this->object->get('tag')
		);

		// Note: property = name, returns English (en-GB) (default language)
		$this->assertEquals(
			'English (en-GB)',
			$this->object->get('name')
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testGetName()
	{
		$this->assertEquals(
			'English (en-GB)',
			$this->object->getName()
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testGetPaths()
	{
		// Without extension, retuns NULL
		$this->assertNull(
			$this->object->getPaths('')
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testGetTag()
	{
		$this->assertEquals(
			'en-GB',
			$this->object->getTag()
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testIsRtl()
	{
		$this->assertFalse(
			$this->object->isRtl()
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testGetSetDebug()
	{
		$current = $this->object->getDebug();
		$this->assertEquals(
			$current,
			$this->object->setDebug(true),
			'Line: ' . __LINE__
		);

		$this->object->setDebug(false);
		$this->assertFalse(
			$this->object->getDebug(),
			'Line: ' . __LINE__
		);

		$this->object->setDebug(true);
		$this->assertTrue(
			$this->object->getDebug(),
			'Line: ' . __LINE__
		);

		$this->object->setDebug(0);
		$this->assertFalse(
			$this->object->getDebug(),
			'Line: ' . __LINE__
		);

		$this->object->setDebug(1);
		$this->assertTrue(
			$this->object->getDebug(),
			'Line: ' . __LINE__
		);

		$this->object->setDebug('');
		$this->assertFalse(
			$this->object->getDebug(),
			'Line: ' . __LINE__
		);

		$this->object->setDebug('test');
		$this->assertTrue(
			$this->object->getDebug(),
			'Line: ' . __LINE__
		);

		$this->object->setDebug('0');
		$this->assertFalse(
			$this->object->getDebug(),
			'Line: ' . __LINE__
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testGetDefault()
	{
		$this->assertEquals(
			'en-GB',
			$this->object->getDefault(),
			'Line: ' . __LINE__
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testSetDefault()
	{
		$this->object->setDefault('de-DE');
		$this->assertEquals(
			'de-DE',
			$this->object->getDefault(),
			'Line: ' . __LINE__
		);
		$this->object->setDefault('en-GB');
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testGetOrphans()
	{
		$this->assertEquals(
			array(),
			$this->object->getOrphans(),
			'Line: ' . __LINE__
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testGetUsed()
	{
		$this->assertEquals(
			array(),
			$this->object->getUsed(),
			'Line: ' . __LINE__
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testHasKey()
	{
		// Key doesn't exist, returns false
		$this->assertFalse(
			$this->object->hasKey('com_admin.key')
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testGetMetadata()
	{
		// Language doesn't exist, retun NULL
		$this->assertNull(
			$this->inspector->getMetadata('es-ES')
		);

		// In this case, returns array with default language
		// - same operation of get method with metadata property
		$options = array(
			'name'       => 'English (en-GB)',
			'nativeName' => 'English (United Kingdom)',
			'tag'        => 'en-GB',
			'rtl'        => '0',
			'locale'     => 'en_GB.utf8, en_GB.UTF-8, en_GB, eng_GB, en, english, english-uk, uk, gbr, britain, england, great britain,' .
				' uk, united kingdom, united-kingdom',
			'firstDay'   => '0',
			'weekEnd'    => '0,6',
			'calendar'   => 'gregorian',
		);

		// Language exists, returns array with values
		$this->assertEquals(
			$options,
			$this->inspector->getMetadata('en-GB')
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testGetKnownLanguages()
	{
		// This method returns a list of known languages
		$basePath = __DIR__ . '/data';

		$option1 = array(
			'name'       => 'English (en-GB)',
			'nativeName' => 'English (United Kingdom)',
			'tag'        => 'en-GB',
			'rtl'        => '0',
			'locale'     => 'en_GB.utf8, en_GB.UTF-8, en_GB, eng_GB, en, english, english-uk, uk, gbr, britain, england, great britain,' .
				' uk, united kingdom, united-kingdom',
			'firstDay'   => '0',
			'weekEnd'    => '0,6',
			'calendar'   => 'gregorian',
		);

		$listCompareEqual1 = array(
			'en-GB' => $option1,
		);

		$list = JLanguage::getKnownLanguages($basePath);
		$this->assertEquals(
			$listCompareEqual1,
			$list,
			'Line: ' . __LINE__
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testGetLanguagePath()
	{
		$basePath = 'test';

		// $language = null, returns language directory
		$this->assertEquals(
			'test/language',
			JLanguage::getLanguagePath($basePath, null),
			'Line: ' . __LINE__
		);

		// $language = value (en-GB, for example), returns en-GB language directory
		$this->assertEquals(
			'test/language/en-GB',
			JLanguage::getLanguagePath($basePath, 'en-GB'),
			'Line: ' . __LINE__
		);

		// With no argument JPATH_BASE should be returned
		$this->assertEquals(
			JPATH_BASE . '/language',
			JLanguage::getLanguagePath(),
			'Line: ' . __LINE__
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testSetLanguage()
	{
		$this->assertEquals(
			'en-GB',
			$this->object->setLanguage('es-ES'),
			'Line: ' . __LINE__
		);

		$this->assertEquals(
			'es-ES',
			$this->object->setLanguage('en-GB'),
			'Line: ' . __LINE__
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testParseLanguageFiles()
	{
		$dir    = __DIR__ . '/data/language';
		$option = array(
			'name'       => 'English (en-GB)',
			'nativeName' => 'English (United Kingdom)',
			'tag'        => 'en-GB',
			'rtl'        => '0',
			'locale'     => 'en_GB.utf8, en_GB.UTF-8, en_GB, eng_GB, en, english, english-uk, uk, gbr, britain, england,' .
				' great britain, uk, united kingdom, united-kingdom',
			'firstDay'   => '0',
			'weekEnd'    => '0,6',
			'calendar'   => 'gregorian',
		);

		$expected = array(
			'en-GB' => $option
		);

		$result = JLanguage::parseLanguageFiles($dir);

		$this->assertEquals(
			$expected,
			$result,
			'Line: ' . __LINE__
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testParseXMLLanguageFile()
	{
		$option = array(
			'name'       => 'English (en-GB)',
			'nativeName' => 'English (United Kingdom)',
			'tag'        => 'en-GB',
			'rtl'        => '0',
			'locale'     => 'en_GB.utf8, en_GB.UTF-8, en_GB, eng_GB, en, english, english-uk, uk, gbr, britain, england, great britain,' .
				' uk, united kingdom, united-kingdom',
			'firstDay'   => '0',
			'weekEnd'    => '0,6',
			'calendar'   => 'gregorian',
		);

		$path = __DIR__ . '/data/language/en-GB/en-GB.xml';

		$this->assertEquals(
			$option,
			JLanguage::parseXMLLanguageFile($path),
			'Line: ' . __LINE__
		);

		//todo:  OK to remove this unused assignment and following assertion? Or is some implementation missing?

		$path2 = __DIR__ . '/data/language/es-ES/es-ES.xml';
		$this->assertEquals(
			$option,
			JLanguage::parseXMLLanguageFile($path),
			'Line: ' . __LINE__
		);
	}

	/**
	 * Test...
	 *
	 * @expectedException  RuntimeException
	 *
	 * @return void
	 */
	public function testParseXMLLanguageFileException()
	{
		$path = __DIR__ . '/data/language/es-ES/es-ES.xml';

		JLanguage::parseXMLLanguageFile($path);
	}
}
