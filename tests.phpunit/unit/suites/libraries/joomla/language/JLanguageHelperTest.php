<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JLanguageHelper.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Language
 * @since       11.1
 */
class JLanguageHelperTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testCreateLanguageList()
	{
		$option = array(
			'text' => 'English (United Kingdom)',
			'value' => 'en-GB',
			'selected' => 'selected="selected"'
		);
		$listCompareEqual = array(
			0 => $option,
		);

		$list = JLanguageHelper::createLanguageList('en-GB', __DIR__ . '/data', false);
		$this->assertEquals(
			$listCompareEqual,
			$list
		);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testDetectLanguage()
	{
		$lang = JLanguageHelper::detectLanguage();

		// Since we're running in a CLI context we can only check the defualt value
		$this->assertNull(
			$lang
		);
	}
}
