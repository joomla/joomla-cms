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
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
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
	protected $waitForXpath =  "//main[@id='content']/ul/li[2]/span[contains(text(),'Home')]";

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
	 * 
	 * @return  Array of Article Titles Visible
	 */	
	public function getArticleTitles()
	{
		$arrayElement=$this->driver->findElements(By::xPath("//h2//a[contains(text(), '')]"));
		$arrayTitles = array();
		for($i=0;$i<count($arrayElement);$i++)
		{
			$arrayTitles[$i]=$arrayElement[$i]->getText();
		}
		return $arrayTitles; 
	}
	
	/**
	 * Function to check if the unpublished tag is present for a article or not
	 *
	 * 
	 * @return  boolean 
	 */	
	public function isUnpublishedPresent($articleTitle)
	{
		$arrayElement=$this->driver->findElements(By::xPath("//div[@class='system-unpublished']/h2[contains(., '" . $articleTitle . "')]"));
		if(count($arrayElement>0))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
