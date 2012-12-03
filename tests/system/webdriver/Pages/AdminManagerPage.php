<?php

require_once 'AdminPage.php';

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
		$this->driver->findElement(By::xPath("//thead//input[@name='checkall-toggle' or @name='toggle']"))->click();
	}

	public function clickItem($name)
	{
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
		return $result;
	}

	public function getFilters()
	{
		$container = $this->driver->findElement(By::xPath("//div[contains(@class, 'filter-select')]"));
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

	public function getRowNumber($name)
	{
		$result = false;
		$rowElements = $this->driver->findElement(By::xPath("//tbody"))->findElements(By::tagName('tr'));
		$count = count($rowElements);
		for ($i = 0; $i < $count; $i++)
		{
			$rowText = $rowElements[$i]->getText();
			if (strpos(strtolower($rowText), strtolower($name)) !== false)
			{
				$result = $i + 1;
				break;
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

	public function searchFor($search = false)
	{
		if ($search)
		{
			$el = $this->driver->findElement(By::id('filter_search'));
			$el->clear();
			$el->sendKeys($search);
			$this->driver->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		}
		else
		{
			$this->driver->findElement(By::xPath("//div[@id='filter-bar']//button[@title='Clear' or @title='Reset' or @data-original-title='Reset']"))->click();
		}
		return $this->test->getPageObject(get_class($this));
	}

	public function setFilter($idOrLabel, $value)
	{
		$result = false;
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
			$container = $this->driver->findElement(By::xPath("//div[@id='" . $filterId . "_chzn']"));
			$this->driver->findElement(By::xPath("//div[@id='" . $filterId . "_chzn']/a"))->click();
			$this->driver->findElement(By::xPath("//div[@id='" . $filterId . "_chzn']//ul[@class='chzn-results']/li[contains(.,'" . $value . "')]"))->click();
			$this->driver->waitForElementUntilIsPresent(By::xPath($this->waitForXpath));
			$result =  true;
		}
		return $result;
	}

}