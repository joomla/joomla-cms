<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Page
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;
use SeleniumClient\WebElement;

/**
 * Class for the back-end control panel screen.
 *
 */
abstract class AdminManagerPage extends AdminPage
{
	public $toolbar = array();

	/**
	 *
	 * @var AdminEditPage
	 */
	public $editItem = null;

	/**
	 *
	 * @var array $filters  Associative array as label => id for filter select element
	 */
	public $filters = array();

	public function checkAll()
	{
 		$el = $this->driver->findElement(By::xPath("//thead//input[@name='checkall-toggle' or @name='toggle']"));

 		// Work-around for intermittant bug with click() on checkboxes -- click until checked
 		while (!$el->isSelected())
 		{
 				$el->click();
 		}
	}

	public function clickItem($name)
	{
		$this->searchFor($name);
		$this->driver->findElement(By::xPath("//tbody/tr/td//a[contains(text(), '". $name . "')]"))->click();
	}

	/**
	 * Returns an array of field values from an edit screen.
	 *
	 * @param string  $itemName    Name of item (user name, article title, and so on)
	 * @param array   $fieldNames  Array of field labels to get values of.
	 */
	public function getFieldValues($className, $itemName, $fieldNames)
	{
		$this->clickItem($itemName);
		$this->editItem = $this->test->getPageObject($className);
		$result = array();
		if (is_array($fieldNames))
		{
			foreach ($fieldNames as $name)
			{
				$result[] = $this->editItem->getFieldValue($name);
			}
		}
		$this->editItem->saveAndClose();
		$this->searchFor();
		return $result;
	}

	public function getFilters()
	{
		$container = $this->driver->findElement(By::xPath("//div[contains(@class, 'filter-select') or contains(@class, 'js-stools')]"));
		$elements = $container->findElements(By::tagName('select'));
		$result = array();
		// @var WebdriverElement $el
		foreach($elements as $el)
		{
			$result[] = $el->getAttribute('id');
		}
		return $result;
	}

	public function getToolbarElements()
	{
		return $this->driver->findElements(By::xPath("//div[@id='toolbar']/div[contains(@id, 'toolbar-')]"));
	}

	public function getSubMenuList()
	{
		return $this->driver->findElement(By::id('submenu'));
	}

	/**
	 * Checks a table for a row containing the desired text
	 *
	 * @param string $name  Text that identifies the desired row
	 *
	 * @return mixed row that contains the text or false if row not found
	 */
	public function getRowNumber($name)
	{
		$result = false;
		$tableElements = $this->driver->findElements(By::xPath("//tbody"));
		if (isset($tableElements[0]))
		{
			$rowElements = $this->driver->findElement(By::xPath("//tbody"))->findElements(By::tagName('tr'));
			$count = count($rowElements);
			for ($i = 0; $i < $count; $i ++)
			{
				$rowText = $rowElements[$i]->getText();
				if (strpos(strtolower($rowText), strtolower($name)) !== false)
				{
					$result = $i + 1;
					break;
				}
			}
		}
		return $result;
	}

	public function getRowText($name)
	{
		$result = false;
		$rowElements = $this->driver->findElement(By::xPath("//tbody"))->findElements(By::tagName('tr'));
		$count = count($rowElements);
		for ($i = 0; $i < $count; $i++)
		{
			$rowText = $rowElements[$i]->getText();
			if (strpos($rowText, $name) !== false)
			{
				$result = $rowText;
				break;
			}
		}
		return $result;
	}

	public function orderAndGetRowNumbers($orderings, $rows)
	{
		$result = array();

		foreach ($orderings as $order)
		{
			$result[$order] = array();

			// Check to see whether there is a separate sort direction list control
			$directionTable = $this->driver->findElements(By::id('directionTable_chzn'));
			if (count($directionTable) == 0)
			{
				$this->setOrder($order . ' ascending');
			}
			else
			{
				$this->setOrder($order);
			}

			foreach ($rows as $row)
			{
				$result[$order]['ascending'][] = $this->getRowNumber($row);
			}

			if (count($directionTable) == 0)
			{
				$this->setOrder($order . ' descending');
			}
			else
			{
				$this->setOrderDirection('Descending');
			}

			foreach ($rows as $row)
			{
				$result[$order]['descending'][] = $this->getRowNumber($row);
			}
		}
		return $result;
	}

	public function searchFor($search = false)
	{
		if ($search)
		{
			$el = $this->driver->findElement(By::id('filter_search'));
			$el->clear();
			$el->sendKeys($search);

			// In some cases we have to click the button twice since using bootstrap tooltips. (Not sure why.)
			$this->driver->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		}
		else
		{
			$this->driver->findElement(By::xPath("//button[@title='Clear' or @title='Reset' or @data-original-title='Reset' or @data-original-title='Clear']"))->click();
		}
		return $this->test->getPageObject(get_class($this));
	}

	public function setFilter($idOrLabel, $value)
	{
		$el = $this->driver->findElements(By::xPath("//button"));
		foreach($el as $element)
		{
			if ($element->getAttribute('data-original-title') == 'Filter the list items.')
			{
				$element->click();
				if($value == 'Select Status')
				{
					$element->click();
				}
			}
		}
		$filters = array_change_key_case($this->filters, CASE_LOWER);
		$idOrLabel = strtolower($idOrLabel);
		$filterId = '';
		if (in_array($idOrLabel, $this->filters))
		{
			$filterId = $idOrLabel;
		}
		else
		{
			foreach ($this->filters as $label => $id)
			{
				if (stripos($label, $idOrLabel) !== false)
				{
					$filterId = $id;
					break;
				}
			}

		}
		if ($filterId)
		{
			$el = $this->driver->findElement(By::xPath("//div[@id='" . $filterId . "_chzn']/a"));
			if (!$el->isDisplayed())
			{
				$elements = $this->driver->findElements(By::xPath("//button[contains(., 'Search tools')]"));
				if (isset($elements[0]))
				{
					while (!$el->isDisplayed())
					{
						$elements[0]->click();
						sleep(2);
					}
				}
			}

			// Open and close the list to create the li elements on the page
			$el = $this->driver->findElement(By::xPath("//div[@id='" . $filterId . "_chzn']/a/div/b"));
			$test = $el->isDisplayed();
			$el->click();
			sleep(2);
			$selectElementArray = $this->driver->findElements(By::xPath("//div[@id='" . $filterId . "_chzn']//ul[@class='chzn-results']/li[contains(.,'" . $value . "')]"));
			if (count($selectElementArray) == 1)
			{
				$selectElement = $selectElementArray[0];
			}
			else
			{
				return false;
			}

			while (!$selectElement->isDisplayed())
			{
				sleep(2);
				$el->click();
			}
			$selectElement->click();
			$this->driver->waitForElementUntilIsPresent(By::xPath($this->waitForXpath));
		}
		return $this->test->getPageObject(get_class($this));
	}

	public function setOrder($value)
	{
		$container = $this->driver->findElement(By::xPath("//div[@id='list_fullordering_chzn' or @id='sortTable_chzn']/a"));
		$container->click();
		$el = $this->driver->findElement(By::xPath("//div[@id='list_fullordering_chzn' or @id='sortTable_chzn']//ul[@class='chzn-results']/li[contains(.,'" . $value . "')]"));
		// Make sure the container is opened. Not sure why we need this, but sometimes the $el is not displayed after the first click. This seems to fix it.
		while (!$el->isDisplayed())
		{
			$container->click();
		}
		$el->click();
		$this->driver->waitForElementUntilIsPresent(By::xPath($this->waitForXpath));

		return $this->test->getPageObject(get_class($this));

	}

	public function setOrderDirection($value)
	{
		$this->driver->findElement(By::xPath("//div[@id='directionTable_chzn']/a/div/b"))->click();
		$el = $this->driver->findElement(By::xPath("//div[@id='directionTable_chzn']//ul[@class='chzn-results']/li[contains(.,'" . $value . "')]"));
		$displayedBefore = $el->isDisplayed();
		$container = $this->driver->findElement(By::xPath("//div[@id='directionTable_chzn']/a"));
		while (!$el->isDisplayed())
		{
			$container->click();
		}
		$displayedAfter = $el->isDisplayed();
		$el->click();
		$this->driver->waitForElementUntilIsPresent(By::xPath($this->waitForXpath));

		return $this->test->getPageObject(get_class($this));
	}

	public function trashAndDelete($name)
	{
		$this->setFilter('Status', 'All');
		$this->searchFor($name);
		$this->checkAll();
		$this->clickButton('toolbar-trash');
		$this->driver->waitForElementUntilIsPresent(By::xPath($this->waitForXpath));
		$this->searchFor();
		$this->setFilter('Status', 'Trashed');
		$this->checkAll();
		$this->clickButton('Empty trash');
		$this->driver->waitForElementUntilIsPresent(By::xPath($this->waitForXpath));
		$this->setFilter('Status', 'Select Status');
		$this->driver->waitForElementUntilIsPresent(By::xPath($this->waitForXpath));
	}

	public function delete($name)
	{
		$this->searchFor($name);
		$el = $this->driver->findElement(By::name("checkall-toggle"));
		while(!$el->isSelected())
		{
			$el->click();
		}
		$this->driver->findElement(By::id("filter_search"))->click();
		sleep(2);
		$this->clickButton('toolbar-delete');
		$this->driver->waitForElementUntilIsPresent(By::xPath($this->waitForXpath));
		$this->searchFor();
	}

}
