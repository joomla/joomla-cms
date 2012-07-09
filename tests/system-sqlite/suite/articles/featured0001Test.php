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
class Featured0001Test extends SeleniumJoomlaTestCase
{
	function testFeaturedOrder()
	{
		$this->setUp();
		echo "Starting testFeaturedOrder.\n";
		$this->gotoAdmin();
		$this->doAdminLogin();

		echo "Change global param to no category order\n";
		$this->click("link=Article Manager");
		$this->waitForPageToLoad("30000");
		$this->jClick('Options');
		$this->click("//dl[@id='config-tabs-com_content_configuration']/dt[4]/span");
		$this->select("jform_orderby_pri", "label=No Order");
		$this->click("//button[contains(@onclick, 'component.save')]");
		for ($second = 0;; $second++)
		{
			if ($second >= 15) $this->fail("timeout");
			try
			{
				if (!$this->isElementPresent("//dl[contains(@id, 'configuration')]")) break;
			}
			catch (Exception $e)
			{
			}
			sleep(1);
		}
		sleep(3);
		echo "Reverse the article order on the front page\n";
		$this->click("link=Featured Articles");
		$this->waitForPageToLoad("30000");
		$this->click("link=Ordering");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//img[contains(@src, 'sort_asc.png')]"));
		$this->type("//input[@name='order[]' and @value='1']", "4");
		$this->type("//input[@name='order[]' and @value='2']", "3");
		$this->type("//input[@name='order[]' and @value='3']", "2");
		$this->type("//input[@name='order[]' and @value='4']", "1");
		$this->click("//a[contains(@href, 'saveorder')]");
		$this->waitForPageToLoad("30000");
		$this->assertTrue((bool) preg_match("#^[\s\S]*success[\s\S]*$#", $this->getText("//dl[@id='system-message']")));

		echo "Go to front page and check article order\n";
		$this->gotoSite();
		$this->assertEquals("Professionals", $this->getText("//div[@class='leading-0']/h2"), "Professionals article should be intro");
		$this->assertTrue((bool) preg_match("/^[\s\S]*Upgraders[\s\S]*Beginners[\s\S]*Joomla![\s\S]*$/", $this->getText(
			"//div[@class='items-row cols-3 row-0']")), "Order in columns should be Upgrader, Beginners, Joomla!");

		echo "Go to back end and change order back to original\n";
		$this->gotoAdmin();
		$this->click("link=Featured Articles");
		$this->waitForPageToLoad("30000");
		$this->type("//input[@name='order[]' and @value='1']", "4");
		$this->type("//input[@name='order[]' and @value='2']", "3");
		$this->type("//input[@name='order[]' and @value='3']", "2");
		$this->type("//input[@name='order[]' and @value='4']", "1");
		$this->click("//a[contains(@href, 'saveorder')]");
		$this->waitForPageToLoad("30000");

		echo "Check that the save order was successful\n";
		$this->assertTrue((bool) preg_match("/^[\s\S]*success[\s\S]*$/", $this->getText("//dl[@id='system-message']")),
			"No success message on save order");

		echo "Go to site and check that the articles are in original order.\n";
		$this->gotoSite();
		$this->assertEquals("Joomla!", $this->getText("//div[@class='leading-0']/h2"), "Joomla! should be intro article");
		$this->assertTrue((bool) preg_match("/^[\s\S]*Beginners[\s\S]*Upgraders[\s\S]*Professionals[\s\S]*$/", $this->getText(
			"//div[@class='items-row cols-3 row-0']")), "Articles should be Beginners, Upgraders, Professionals");

		echo "Go back to back end and change menu item to sort by alpha\n";
		$this->gotoAdmin();
		$this->click("link=Main Menu");
		$this->waitForPageToLoad("30000");
		$this->click("//td/a[contains(.,  'Home')]");
		$this->waitForPageToLoad("30000");
		$this->click("//h3[@id='advanced-options']/a/span");
		$this->select("jform_params_orderby_sec", "label=Title Alphabetical");
		$this->click("//li[@id='toolbar-save']/a/span");
		$this->waitForPageToLoad("30000");

		echo "Goto front page and check alpha article order \n";
		$this->gotoSite();
		$this->assertEquals("Beginners", $this->getText("//div[@class='leading-0']/h2"), "Beginners should be intro article");
		$this->assertEquals("Joomla!", $this->getText("//div[@class='item column-1']/h2"), "Joomla! should be col 1");
		$this->assertEquals("Professionals", $this->getText("//div[@class='item column-2']/h2"), "Professionals should be col 2");
		$this->assertEquals("Upgraders", $this->getText("//div[@class='item column-3']/h2"), "Upgrades should be col 3");
		$this->assertTrue((bool) preg_match("/^[\s\S]*Joomla![\s\S]*Professionals[\s\S]*Upgraders[\s\S]*$/", $this->getText(
			"//div[@class='items-row cols-3 row-0']")), "Articles should be Joomla!, Professionals, Upgraders");

		echo "Go back to back end and change parameters back.\n";
		$this->gotoAdmin();
		$this->click("link=Main Menu");
		$this->waitForPageToLoad("30000");
		echo "Edit the Home Menu Item to change sorting back\n";
		$this->click("//td/a[contains(.,  'Home')]");
		$this->waitForPageToLoad("30000");
		$this->click("//h3[@id='advanced-options']/a/span");
		$this->select("jform_params_orderby_sec", "label=Featured Articles Order");
		$this->click("//li[@id='toolbar-save']/a/span");
		$this->waitForPageToLoad("30000");
		$this->click("link=Article Manager");
		$this->waitForPageToLoad("30000");
		$this->jClick('Options');
		$this->click("//dl[@id='config-tabs-com_content_configuration']/dt[4]/span");
		$this->select("jform_orderby_pri", "label=Category Manager Order");
		$this->click("//button[contains(@onclick, 'component.save')]");
		for ($second = 0;; $second++)
		{
			if ($second >= 15) $this->fail("timeout");
			try
			{
				if (!$this->isElementPresent("//dl[@id='config-tabs-com_content_configuration']")) break;
			}
			catch (Exception $e)
			{
			}
			sleep(1);
		}

		echo "Done with featured0001Test\n";
		$this->click("link=Control Panel");
		$this->waitForPageToLoad("30000");
		$this->doAdminLogout();
		$this->deleteAllVisibleCookies();
	}

}
