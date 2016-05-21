<?php

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;
use SeleniumClient\WebElement;

/**
 * @package     Joomla.Test
 * @subpackage  Webdriver
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Page class for the back-end component tags menu.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class LanguageManagerPage extends AdminManagerPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $waitForXpath = "//ul/li/a[@href='index.php?option=com_languages&view=languages']";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $url = 'administrator/index.php?option=com_languages';

	/**
	 * Array of filter id values for this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $filters = array(
			'Select Status' => 'filter_published',
			'Select Access' => 'filter_access',
			);

	/**
	 * Array of toolbar id values for this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $toolbar = array (
			'New' => 'toolbar-new',
			'Edit' => 'toolbar-edit',
			'Publish' => 'toolbar-publish',
			'Unpublish' => 'toolbar-unpublish',
			'Trash' => 'toolbar-trash',
			'Install Language' => 'toolbar-upload',
			'Empty Trash' => 'toolbar-delete',
			'Options' => 'toolbar-options',
			'Help' => 'toolbar-help',
			);

	/**
	 * Add a new Language item in the Language Manager: Component screen.
	 *
	 * @param   string   $title          Test Language Name
	 *
	 * @param   string   $native_title   Native Title for the Test Language
	 *
	 * @param   string   $url			 URL for the Test Language
	 *
	 * @param   string   $image_prefix   image prefix for the test Language
	 *
	 * @param   string 	 $language_tag    Tag for the test language
	 *
	 * @return  LanguageManagerPage
	 */
	public function addLanguage($title='Test Lang', $native_title='Default', $url='Default', $image_prefix='us', $language_tag='Default')
	{
		$new_name = $title;
		$this->clickButton('toolbar-new');
		$languageEditPage = $this->test->getPageObject('LanguageEditPage');
		$languageEditPage->setFieldValues(array('Title' => $title, 'Title Native' => $native_title, 'URL Language Code' => $url, 'Image Prefix' => $image_prefix, 'Language Tag' => $language_tag));
		$languageEditPage->clickButton('toolbar-save');
		$this->test->getPageObject('LanguageManagerPage');

	}

	/**
	 * Edit a Language Content item in the Language Manager: Language-Content Screen Items screen.
	 *
	 * @param string   $name	   Language Title field
	 * @param array    $fields     associative array of fields in the form label => value.
	 *
	 * @return  void
	 */
	public function editLanguage($name, $fields)
	{
		$this->clickItem($name);
		$languageEditPage = $this->test->getPageObject('LanguageEditPage');
		$languageEditPage->setFieldValues($fields);
		$languageEditPage->clickButton('toolbar-save');
		$this->test->getPageObject('LanguageManagerPage');
		$this->searchFor();
	}

	/**
	 * Get state  of a Language item in the Language Manager: Language Items screen.
	 *
	 * @param string   $name	   Language Title field
	 *
	 * @return  State of the Language //Published or Unpublished
	 */
	public function getState($name)
	{
		$result = false;
		$row = $this->getRowNumber($name);
		$text = $this->driver->findElement(By::xPath("//tbody/tr[" . $row . "]/td[3]/a"))->getAttribute(@onclick);

		if (strpos($text, 'languages.unpublish') > 0)
		{
			$result = 'published';
		}

		if (strpos($text, 'languages.publish') > 0)
		{
			$result = 'unpublished';
		}

		return $result;
	}

	/**
	 * Change state of a Language item in the Language Manager: Language Items screen.
	 *
	 * @param string   $name	   Language Title field
	 * @param string   $state      State of the Language
	 *
	 * @return  void
	 */
	public function changeLanguageState($name, $state = 'published')
	{
		$this->searchFor($name);
		$this->checkAll();

		if (strtolower($state) == 'published')
		{
			$this->clickButton('toolbar-publish');
			$this->driver->waitForElementUntilIsPresent(By::xPath($this->waitForXpath));
		}
		elseif (strtolower($state) == 'unpublished')
		{
			$this->clickButton('toolbar-unpublish');
			$this->driver->waitForElementUntilIsPresent(By::xPath($this->waitForXpath));
		}

		$this->searchFor();
	}
}
