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
class LanguageManager0001Test extends JoomlaWebdriverTestCase
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
	 * test to check all available input fields
	 *
	 * @return void
	 *
	 * @test
	 */
	public function getAllInputFields_ScreenDisplayed_EqualExpected()
	{
		$this->languageManagerPage = $this->getPageObject('LanguageManagerPage');
		$this->languageManagerPage->clickButton('toolbar-new');
		$languageEditPage = $this->getPageObject('LanguageEditPage');
		$testElements = $languageEditPage->getAllInputFields(array('details', 'metadata', 'site_name'));
		$actualFields = array();

		foreach ($testElements as $el)
		{
			$el->labelText = (substr($el->labelText, -2) == ' *') ? substr($el->labelText, 0, -2) : $el->labelText;
			$actualFields[] = array('label' => $el->labelText, 'id' => $el->id, 'type' => $el->tag, 'tab' => $el->tab);
		}

		$this->assertEquals($languageEditPage->inputFields, $actualFields);
		$languageEditPage->clickButton('toolbar-cancel');
		$this->languageManagerPage = $this->getPageObject('LanguageManagerPage');
	}

	/**
	 * ttest to open edit screen
	 *
	 * @return void
	 *
	 * @test
	 */
	public function constructor_OpenEditScreen_LanguageEditOpened()
	{
		$this->languageManagerPage = $this->getPageObject('LanguageManagerPage');
		$this->languageManagerPage->clickButton('new');
		$languageEditPage = $this->getPageObject('LanguageEditPage');
		$languageEditPage->clickButton('cancel');
		$this->languageManagerPage = $this->getPageObject('LanguageManagerPage');
	}

	/**
	 * test get the tab IDs
	 *
	 * @return void
	 *
	 * @test
	 */
	public function getTabIds_ScreenDisplayed_EqualExpected()
	{
		$this->languageManagerPage = $this->getPageObject('LanguageManagerPage');
		$this->languageManagerPage->clickButton('toolbar-new');
		$languageEditPage = $this->getPageObject('LanguageEditPage');
		$textArray = $languageEditPage->getTabIds();
		$this->assertEquals($languageEditPage->tabs, $textArray, 'Tab labels should match expected values.');
		$languageEditPage->clickButton('toolbar-cancel');
		$this->languageManagerPage = $this->getPageObject('LanguageManagerPage');
	}

	/**
	 * add language with default fields
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addLanguage_WithFieldDefaults_LanguageAdded()
	{
		$salt = rand();
		$langName = 'Test' . $salt;
		$this->languageManagerPage = $this->getPageObject('LanguageManagerPage');
		$this->assertFalse($this->languageManagerPage->getRowNumber($langName), 'Test Lang should not be present');
		$this->languageManagerPage->addLanguage($langName);
		$message = $this->languageManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Content Language successfully saved') >= 0, 'Content Language save should return success');
		$this->assertGreaterThanOrEqual(1, $this->languageManagerPage->getRowNumber($langName), 'Test lang should be present');
		$this->languageManagerPage->trashAndDelete($langName);
		$this->assertFalse($this->languageManagerPage->getRowNumber($langName), 'Test lang should not be present');
	}

	/**
	 * add language with given input fields
	 *
	 * @return void
	 *
	 * @test
	 */
	public function addLanguage_WithGivenFields_LanguageAdded()
	{
		$salt = rand();
		/*Other than the Default Value*/
		$langName = 'lang' . $salt;
		$lang_title_native = 'Sample' . $salt;
		$url_code = 'Sample' . $salt;
		$image_prefix = 'us';
		$language_tag = 'Sample';
		$this->languageManagerPage = $this->getPageObject('LanguageManagerPage');
		$this->assertFalse($this->languageManagerPage->getRowNumber($langName), 'Test lang should not be present');
		$this->languageManagerPage->addLanguage($langName, $lang_title_native, $url_code, $image_prefix, $language_tag);
		$message = $this->languageManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Content Language successfully saved') >= 0, 'Content Language save should return success');
		$this->assertGreaterThanOrEqual(1, $this->languageManagerPage->getRowNumber($langName), 'Test lang should be present');
		$values = $this->languageManagerPage->getFieldValues('LanguageEditPage', $langName, array('Title', 'Title Native'));
		$this->assertEquals(array($langName, $lang_title_native), $values, 'Actual name, native name should match expected');
		$this->languageManagerPage->trashAndDelete($langName);
		$this->assertFalse($this->languageManagerPage->getRowNumber($langName), 'Test lang should not be present');
	}

	/**
	 * test to edit the values of input fields
	 *
	 * @return void
	 *
	 * @test
	 */
	public function editLanguage_ChangeFields_FieldsChanged()
	{
		$salt = rand();
		/*Other than the Default Value*/
		$langName = 'lang' . $salt;
		$new_lang_name = 'new_sample_Title';
		$lang_title_native = 'Sample' . $salt;
		$url_code = 'Sample' . $salt;
		$image_prefix = 'us';
		$language_tag = 'Sample';
		$this->languageManagerPage = $this->getPageObject('LanguageManagerPage');
		$this->assertFalse($this->languageManagerPage->getRowNumber($langName), 'Test lang should not be present');
		$this->languageManagerPage->addLanguage($langName, $lang_title_native, $url_code, $image_prefix, $language_tag);
		$message = $this->languageManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Content Language successfully saved') >= 0, 'Content Language save should return success');
		$this->languageManagerPage->editLanguage($langName, array('Title' => 'new_sample_Title', 'Title Native' => 'Default'));
		$values = $this->languageManagerPage->getFieldValues('LanguageEditPage', $new_lang_name, array('Title', 'Title Native'));
		$this->assertEquals(array('new_sample_Title', 'Default'), $values, 'Actual values should match expected');
		$this->languageManagerPage->trashAndDelete($new_lang_name);
	}

	/**
	 * test to change the state of the language
	 *
	 * @return void
	 *
	 * @test
	 */
	public function changeLanguageState_ChangeEnabledUsingToolbar_EnabledChanged()
	{
		$this->languageManagerPage = $this->getPageObject('LanguageManagerPage');
		$this->languageManagerPage->addLanguage('Test Lang');
		$state = $this->languageManagerPage->getState('Test Lang');
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->languageManagerPage->changeLanguageState('Test Lang', 'unpublished');
		$state = $this->languageManagerPage->getState('Test Lang');
		$this->assertEquals('unpublished', $state, 'State should be unpublished');
		$this->languageManagerPage->trashAndDelete('Test Lang');
	}
}
