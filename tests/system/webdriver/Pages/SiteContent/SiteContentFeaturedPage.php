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
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Home Page Class
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.2
 */
class SiteContentFeaturedPage extends SitePage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $waitForXpath = "//div[@class='blog-featured']";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $url = '/index.php';

	/**
	 * Function which returns Title array of Articles on the Home page of Front End
	 *
	 * @return  Array of Article Titles Visible
	 */
	public function getArticleTitles()
	{
		$arrayElement = $this->driver->findElements(By::xPath("//h2//a[contains(text(), '')]"));
		$arrayTitles = array();

		for ($i = 0;$i < count($arrayElement);$i++)
		{
			$arrayTitles[$i] = $arrayElement[$i]->getText();
		}

		return $arrayTitles;
	}

	/**
	 * Function which returns Title array of Contacts on the Home page of Front End
	 *
	 * @return  Array of Contacts Titles Visible
	 */
	public function getContactTitles()
	{
		$arrayElement = $this->driver->findElements(By::xPath("//a[contains(text(), '')]"));
		$arrayTitles = array();

		for ($i = 0;$i < count($arrayElement);$i++)
		{
			$arrayTitles[$i] = $arrayElement[$i]->getText();
		}

		return $arrayTitles;
	}

	/**
	 * Function which returns Text array of Article content on the Home page of Front End
	 *
	 * @return  Array of Article Content Visible
	 */
	public function getArticleText()
	{
		$arrayElement = $this->driver->findElements(By::xPath("//p[contains(text(),'')]"));
		$arrayText = array();

		for ($i = 0;$i < count($arrayElement);$i++)
		{
			$arrayText[$i] = $arrayElement[$i]->getText();
		}

		return $arrayText;
	}

	/**
	 * Function which opens the article in editing mode at the front end
	 *
	 * @param   string  $articleTitle  Title of the article which we are going to edit
	 * 
	 * @return  null
	 */
	public function clickEditArticle($articleTitle)
	{
		$d = $this->driver;
		$d->findElement(By::xPath("//a[contains(text(),'" . $articleTitle . "')]/../../div//a/span[contains(@class, 'icon-cog')]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//a[contains(text(),'" . $articleTitle . "')]/../../div//a/span[contains(@class, 'icon-edit')]"), 10);
		$d->findElement(By::xPath("//a[contains(text(),'" . $articleTitle . "')]/../../div//a/span[contains(@class, 'icon-edit')]"))->click();
	}

	/**
	 * Function to check if the edit icon is present on the page or not
	 *
	 * @return  boolean 
	 */
	public function isEditPresent()
	{
		$arrayElement = $this->driver->findElements(By::xPath("//a[contains(text(), 'Edit')]"));

		if (count($arrayElement) > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Function to check if the unpublished tag is present for a article or not
	 *
	 * @param   string  $articleTitle  stores the name of the article
	 *
	 * @return  boolean
	 */
	public function isUnpublishedPresent($articleTitle)
	{
		$arrayElement = $this->driver->findElements(By::xPath("//div[@class='system-unpublished']/h2[contains(., '" . $articleTitle . "')]"));

		if (count($arrayElement) > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * check whether the item is present on the site or not given the xpath variable.
	 *
	 * @param   string  $itemName  stores the name of the item to be searched
	 * @param   string  $arg       xpath variable
	 *
	 * @return boolean
	 */
	public function itemExist($itemName, $arg)
	{
		$driver = $this->driver;
		$path = "//" . $arg . "[contains(text(),'" . $itemName . "')]";
		$driver->waitForElementUntilIsPresent(By::xPath($path), 60);
		$element = $driver->findElement(By::xPath($path));

		if ($element != null)
		{
			return true;
		}

		return false;
	}

	/**
	 * function to click an item
	 *
	 * @param   string  $itemName  stores the item name to be clicked
	 *
	 * @return void
	 */
	public function itemClick($itemName)
	{
		$driver = $this->driver;
		$path = "//a[contains(text(),'" . $itemName . "')]";
		$driver->waitForElementUntilIsPresent(By::xPath($path), 60);
		$driver->findElement(By::xPath($path))->click();
	}
	
	/**
	 * check if the voting option is available on the front end
	 *
	 * @return boolean
	 */
	public function checkVote()
	{
		$driver = $this->driver;
		$element = $driver->findElement(By::xPath("//input[@name='submit_vote']"));

		if ($element != null)
		{
			return true;
		}

		return false;
	}
}
