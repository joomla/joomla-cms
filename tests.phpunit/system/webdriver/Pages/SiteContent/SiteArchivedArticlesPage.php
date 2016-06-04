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
 * @package     Joomla.Test
 * @subpackage  Webdriver
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Page class for Front End Archived Articles
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.2
 */
class SiteArchivedArticlesPage extends SitePage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $waitForXpath = "//button[contains(text(),'Filter')]";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $url = '/index.php/using-joomla/extensions/components/content-component/archived-articles';

	/**
	 * Function which returns Title array of Articles on the Home page of Front End
	 *
	 * @return  Array of Article Titles Visible
	 */
	public function getArticleTitles()
	{
		$arrayElement = $this->driver->findElements(By::xPath("//h2//a[contains(text(), '')]"));
		$arrayTitles = array();

		for ($i = 0; $i < count($arrayElement); $i++)
		{
			$arrayTitles[$i] = $arrayElement[$i]->getText();
		}

		return $arrayTitles;
	}
}
