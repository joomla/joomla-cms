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
class ArticleManagerPage extends AdminManagerPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $waitForXpath = "//ul/li/a[@href='index.php?option=com_content']";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $url = 'administrator/index.php?option=com_content';

	/**
	 * Array of filter id values for this page
	 *
	 * @var    array
	 * @since  3.0
	 */
	public $filters = array(
			'Sort Table By:' => 'list_fullordering',
			'20' => 'list_limit',
			'Select Status' => 'filter_published',
			'Select Category' => 'filter_category_id',
			'Select Max Levels' => 'filter_level',
			'Select Access' => 'filter_access',
			'Select Author' => 'filter_author_id',
			'Select Language' => 'filter_language',
			'Select Tag' => 'filter_tag'
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
			'Featured' => 'toolbar-featured',
			'Archive' => 'toolbar-archive',
			'Check In' => 'toolbar-checkin',
			'Trash' => 'toolbar-trash',
			'Empty Trash' => 'toolbar-delete',
			'Batch' => 'toolbar-batch',
			'Options' => 'toolbar-options',
			'Help' => 'toolbar-help',
			);

	/**
	 * Add a new Article item in the Article Manager: Article Manager Screen.
	 *
	 * @param string $name Test Article Title
	 *
	 * @param string $category Test Article Category
	 *
	 * @return ArticleManagerPage
	 */
	public function addArticle($name = 'Testing Articles', $category = 'Sample Data-Articles', $otherFields = array())
	{
		$new_name = $name;
		$this->clickButton('toolbar-new');
		$articleEditPage = $this->test->getPageObject('ArticleEditPage');
		$articleEditPage->setFieldValues(array(
			'Title' => $name,
			'Category' => $category
		));

		if (count($otherFields > 0))
		{
			$articleEditPage->setFieldValues($otherFields);
		}

		$articleEditPage->clickButton('toolbar-save');
		$this->test->getPageObject('ArticleManagerPage');
	}

	/**
	 * Edit a Article item in the Article Manager: Article Manager Screen.
	 *
	 * @param string   $name	   Title field
	 * @param array    $fields     associative array of fields in the form label => value.
	 *
	 * @return  void
	 */
	public function editArticle($name, $fields = array())
	{
		$this->clickItem($name);
		$articleEditPage = $this->test->getPageObject('ArticleEditPage');
		$articleEditPage->setFieldValues($fields);
		$articleEditPage->clickButton('toolbar-save');
		$this->test->getPageObject('ArticleManagerPage');
		$this->searchFor();
	}

	/**
	 * Get state  of a Article in Article Manager Screen: Article Manager.
	 *
	 * @param string   $name	   Article Title field
	 *
	 * @return  State of the Article //Published or Unpublished
	 */
	public function getState($name)
	{
		$this->setFilter('Select Status', 'All');
		$this->searchFor($name);
		$row = $this->getRowNumber($name);
		$text = $this->driver->findElement(By::xPath("//tbody/tr[" . $row . "]/td[3]/div/a/i[1]"))->getAttribute('class');

		switch ($text)
		{
			case 'icon-publish':
				$result = 'published';
				break;

			case 'icon-unpublish':
				$result = 'unpublished';
				break;

			case 'icon-archive':
				$result = 'archived';
				break;

			default:
				$result = false;
		}

		$this->searchFor();

		return $result;
	}

	/**
	 * Change state of a Article Item in Article Manager Screen
	 *
	 * @param string   $name	   Article Title field
	 * @param string   $state      State of the Article
	 *
	 * @return  void
	 */
	public function changeArticleState($name, $state = 'published')
	{
		$this->setFilter('Status', 'All');
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
		elseif(strtolower($state) == 'archived')
		{
			$container = $this->driver->findElement(By::xPath("//tr[@class='row0']/td[3]/div[@class='btn-group']/button"));
			$container->click();
			$el = $this->driver->findElement(By::xPath("//li/a[contains(@onclick, 'articles.archive')]"));
			$el->click();
			$this->driver->waitForElementUntilIsPresent(By::xPath($this->waitForXpath));
		}
		elseif(strtolower($state) == 'trashed')
		{
			$this->clickButton('toolbar-trash');
			$this->driver->waitForElementUntilIsPresent(By::xPath($this->waitForXpath));
		}

		$this->searchFor();
	}

	/**
	 * change the Filter state for the page
	 *
	 * @param string $label 	Filter Label which is to be changed
	 * @param string $value 	Value to which the filter is to be changed
	 *
	 * @return void
	 */
	public function changeFilter($label, $value)
	{
		$this->setFilter($label, $value);
	}

	/**
	 * get access level of article
	 *
	 * @param string   $name	   Article Title field
	 *
	 * @return  accesslevel
	 */
	public function getAccessLevel($name)
	{
		$this->searchFor($name);
		$row = $this->getRowNumber($name);
		$text = $this->driver->findElement(By::xPath("//tbody/tr[" . $row . "]/td[5]"))->getText();
		$this->searchFor();

		return $text;
	}

	/**
	 * Change access level of article
	 *
	 * @param string   $name	      Article Title field
	 * @param string   $accessLevel   Desired Access level to which we want it to change to
	 *
	 * @return  void
	 */
	public function changeAccessLevel($name, $accessLevel)
	{
		$this->searchFor($name);
		$this->checkAll();
		$this->clickButton('toolbar-batch');
		$this->driver->waitForElementUntilIsPresent(By::xPath("//div[@class='modal hide fade in']"));
		$this->driver->findElement(By::xPath("//div[@id='batch_access_chzn']/a"))->click();
		$this->driver->findElement(By::xPath("//div[@id='batch_access_chzn']//ul[@class='chzn-results']/li[contains(.,'" . $accessLevel . "')]"))->click();
		$this->driver->findElement(By::xPath("//button[contains(text(),'Process')]"))->click();
		$this->driver->waitForElementUntilIsPresent(By::xPath($this->waitForXpath));
		$this->searchFor();
	}

	/**
	 * Fetch Category of Article
	 *
	 * @param string $name 		Article Name for which the Categoory is to be Returned
	 *
	 * @return categoryName
	 */
	public function getCategoryName($name)
	{
		$this->searchFor($name);
		$row = $this->getRowNumber($name);
		$categoryName = $this->driver->findElement(By::xPath("//tbody/tr[" . $row . "]/td[4]/div/div[@class = 'small']"))->getText();

		return $categoryName;
	}

	/**
	 * Function that does Batch Process for Articles, Copy, Move articles
	 *
	 * @param string $articleName	 	Article for which Batch Processing is to be done
	 * @param string $searchString	 	Value entered in the drop down to filter the results
	 * @param string $newCategory		Category to which the Article is to be moved or copied
	 * @param string $action			Action to be taken, either Move or Copy
	 *
	 * @return void
	 */
	public function doBatchAction($articleName, $searchString, $newCategory, $action)
	{
		$this->searchFor($articleName);
		$row = $this->getRowNumber($articleName);
		$this->driver->findElement(By::xPath("//input[@id='cb" . ($row - 1) . "']"))->click();
		$this->clickButton('toolbar-batch');
		$this->driver->waitForElementUntilIsPresent(By::xPath("//div[@class='modal hide fade in']"));
		$this->driver->findElement(By::xPath("//div[@id='batch_category_id_chzn']/a"))->click();
		$this->driver->findElement(By::xPath("//div[@id='batch_category_id_chzn']/div/div/input"))->sendKeys($searchString);
		$this->driver->findElement(By::xPath("//div[@id='batch_category_id_chzn']//ul[@class = 'chzn-results']/li[contains(.,'" . $newCategory . "')]"))->click();

		if (strtolower($action) == 'copy')
		{
			$this->driver->findElement(By::xPath("//input[@id='batch[move_copy]c']"))->click();
		}
		else
		{
			$this->driver->findElement(By::XPath("//input[@id='batch[move_copy]m']"))->click();
		}

		$this->driver->findElement(By::xPath("//button[contains(text(),'Process')]"))->click();
		$this->driver->waitForElementUntilIsPresent(By::xPath($this->waitForXpath));
	}

	/**
	 * change the Category Filter from Article Manager Page
	 *
	 * @param string $category Name of the Category to which the filter is to be set to
	 *
	 * @return void
	 */
	public function changeCategoryFilter($category = 'Select Category')
	{
		$filterElement = $this->driver->findElement(By::xPath("//div[@id='filter_category_id_chzn']/a"));

		if (! $filterElement->isDisplayed())
		{
			$elements = $this->driver->findElements(By::xPath("//button[contains(., 'Search tools')]"));

			if (isset($elements[0]))
			{
				$elements[0]->click();
				sleep(3);
			}
		}

		$filterElement->click();
		$this->driver->findElement(By::xPath("//div[@id='filter_category_id_chzn']/div/div/input"))->sendKeys(substr($category, 0, 2));
		$this->driver->findElement(By::xPath("//div[@id='filter_category_id_chzn']//ul[@class='chzn-results']/li[contains(.,'" . $category . "')]"))->click();
		$this->driver->waitForElementUntilIsPresent(By::xPath($this->waitForXpath));
	}
}
