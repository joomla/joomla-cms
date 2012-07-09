<?php
/**
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * checks that all menu choices are shown in back end
 */

require_once 'SeleniumJoomlaTestCase.php';

/**
 * @group ControlPanel
 */
class Module0002 extends SeleniumJoomlaTestCase
{
	function testModuleDisplay()
	{
		$this->setUp();
		echo ("Starting ". __FUNCTION__ . "\n");
		$this->gotoSite();
		echo ("Check that login form is present"."\n");
		$this->assertTrue($this->isTextPresent("Login Form"));

		echo "Check navigation modules.\n";
		$link = $this->cfg->path . 'index.php/using-joomla/extensions/modules';
		$this->open($link);
		$this->click("link=Navigation Modules");
		$this->waitForPageToLoad("30000");
		$this->click("link=Breadcrumbs Module");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//div[@class='breadcrumbs']/a[contains(text(),'Home')]"));
		$this->assertTrue($this->isElementPresent("//div[@class='breadcrumbs']/a[contains(text(),'Navigation Modules')]"));

		echo "Check content modules.\n";
		$this->click("link=Content Modules");
		$this->waitForPageToLoad("30000");
		$this->click("link=Most Read Content");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("link=Australian Parks"));
		$this->assertTrue($this->isElementPresent("link=Fruit Shop"));
		$this->click("link=News Flash");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("News Flash"));
		$this->click("link=Latest Articles");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("link=Beginners"));
		$this->assertTrue($this->isElementPresent("link=Options"));
		$this->click("link=Archive");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("link=January, 2011"));
		$this->click("link=Related Items");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("link=Archive Module"));
		$this->assertTrue($this->isElementPresent("link=Most Read Content"));
		$this->click("link=Article Categories");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("link=Growers"));
		$this->assertTrue($this->isElementPresent("link=Recipes"));
		$this->click("link=Article Category");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("link=Koala"));
		$this->assertTrue($this->isElementPresent("link=Wobbegone"));

		echo "Check user modules.\n";
		$this->click("link=User Modules");
		$this->waitForPageToLoad("30000");
		$this->click("link=Who's Online");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//h3[contains(text(),\"Who's Online\")]"));

		$this->deleteAllVisibleCookies();
	}

}

