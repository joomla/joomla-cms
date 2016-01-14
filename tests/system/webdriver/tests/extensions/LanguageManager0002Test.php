<?php
/**
 * @package     Joomla.Test
 * @subpackage  Webdriver
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once 'JoomlaWebdriverTestCase.php';

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;

/**
 * This class tests the  Language: Add / Edit  Screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class LanguageManager0002Test extends JoomlaWebdriverTestCase
{
	/**
	 * The page class being tested.
	 *
	 * @var     LanguageManagerPage
	 * @since   3.0
	 */
	protected $languageManagerPage = null;

	/**
	 * Login to back end and navigate to menu Language Manager.
	 *
	 * @return void
	 *
	 * @since   3.0
	 */
	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->tagManagerPage = $cpPage->clickMenu('Language Manager', 'LanguageManagerPage');
		$this->driver->findElement(By::xPath("//ul/li/a[@href='index.php?option=com_languages&view=languages']"))->click();
	}

	/**
	 * Logout and close test.
	 *
	 * @return void
	 *
	 * @since   3.0
	 */
	public function tearDown()
	{
		$this->doAdminLogout();
		parent::tearDown();
	}

	/**
	 * get list of filters and match it with expected IDs
	 *
	 * @return void
	 *
	 * @test
	 */
	public function getFilters_GetListOfFilters_ShouldMatchExpected()
	{
		$this->languageManagerPage = $this->getPageObject('LanguageManagerPage');
		$actualIds = $this->languageManagerPage->getFilters();
		$expectedIds = array_values($this->languageManagerPage->filters);
		$this->assertEquals($expectedIds, $actualIds, 'Filter ids should match expected');
	}

	/**
	 * checking the working of published and unpublished filters
	 *
	 * @return void
	 *
	 * @test
	 */
	public function setFilter_SetFilterValues_ShouldExecuteFilter()
	{
		$salt = rand();
		$langName = 'Test Lang' . $salt;
		$this->languageManagerPage = $this->getPageObject('LanguageManagerPage');
		$this->languageManagerPage->addLanguage($langName);
		$message = $this->languageManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Content Language successfully saved') >= 0, 'Test Lang save should return success');
		$test = $this->languageManagerPage->setFilter('filter_published', 'Unpublished');
		$this->assertFalse($this->languageManagerPage->getRowNumber($langName), 'Lang should not show');
		$test = $this->languageManagerPage->setFilter('filter_published', 'Published');
		$this->assertGreaterThanOrEqual(1, $this->languageManagerPage->getRowNumber($langName), 'Lang should be present');
		$this->languageManagerPage->trashAndDelete($langName);
		$this->assertFalse($this->languageManagerPage->getRowNumber($langName), 'Lang should not be present');
	}

	/**
	 * creating two language one published and one unpublished and the verifying its existence
	 *
	 * @return void
	 *
	 * @test
	 */
	public function setFilter_TestFilters_ShouldFilterTags()
	{
		$salt = rand();
		/*Other than the Default Value */
		$langName_1 = 'Test Lang 1';
		$langName_2 = 'Test Lang 2';
		$lang_title_native = 'Sample2' . $salt;
		$url_code = 'Sample2' . $salt;
		$image_prefix = 'af';
		$language_tag = 'Sample2';

		$this->languageManagerPage = $this->getPageObject('LanguageManagerPage');

		$this->languageManagerPage->addLanguage($langName_1);
		$message = $this->languageManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Content Language successfully saved') >= 0, 'Test Lang Save should return success');
		$state = $this->languageManagerPage->getState($langName_1);
		$this->assertEquals('published', $state, 'Initial state should be published');

		$this->languageManagerPage->addLanguage($langName_2, $lang_title_native, $url_code, $image_prefix, $language_tag);
		$message = $this->languageManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Content Language successfully saved') >= 0, 'Test Lang save should return success');
		$state = $this->languageManagerPage->getState($langName_2);
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->languageManagerPage->changeLanguageState($langName_2, 'unpublished');

		$test = $this->languageManagerPage->setFilter('filter_published', 'Unpublished');
		$this->assertFalse($this->languageManagerPage->getRowNumber($langName_1), 'Lang should not show');
		$this->assertGreaterThanOrEqual(1, $this->languageManagerPage->getRowNumber($langName_2), 'Lang should be in row 1');

		$test = $this->languageManagerPage->setFilter('filter_published', 'Published');
		$this->assertFalse($this->languageManagerPage->getRowNumber($langName_2), 'Lang should not show');
		$this->assertGreaterThanOrEqual(1, $this->languageManagerPage->getRowNumber($langName_1), 'Lang should be in row 2');

		$this->languageManagerPage->setFilter('Select Status', 'Select Status');
		$this->languageManagerPage->trashAndDelete($langName_1);
		$this->languageManagerPage->trashAndDelete($langName_2);
	}
}
