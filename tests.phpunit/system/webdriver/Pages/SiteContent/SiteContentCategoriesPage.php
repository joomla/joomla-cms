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
 * Home Page Class
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.2
 */
class SiteContentCategoriesPage extends SitePage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.2
	 */
    protected $waitForXpath = "//main[@id='content']/ul/li[7]/span[contains(text(),'Article Categories')]";

    /**
     * URL used to uniquely identify this page
     *
     * @var    string
     * @since  3.2
     */
	protected $url = 'index.php/using-joomla/extensions/components/content-component/article-categories';

    /**
     * Function which returns Title array of Categories present on the Page
     *
     * 
     * @return  Array of Category Titles Visible
     */
	public function getCategoryTitles()
    {
		$arrayElement = $this->driver->findElements(By::xPath("//h3//a[contains(text(), '')]"));
		$arrayTitles = array();

		for ($i = 0;$i < count($arrayElement);$i++)
		{
			$arrayTitles[$i] = $arrayElement[$i]->getText();
		}

		return $arrayTitles;
	}
}
