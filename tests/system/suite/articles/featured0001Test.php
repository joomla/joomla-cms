<?php
/**
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
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
		$this->jPrint ("Starting testFeaturedOrder.\n");
		$this->gotoAdmin();
		$this->doAdminLogin();

		$this->jPrint ("Change global param to no category order\n");
		$this->click("link=Article Manager");
		$this->waitForPageToLoad("30000");
		$this->click("//div[@id='toolbar-options']/button");
		$this->waitForPageToLoad("30000");
		$this->click("//a[contains(@href, 'shared')]");
		$this->select("jform_orderby_pri", "value=none");
		$this->click("//button[contains(@onclick, 'component.save')]");
		$this->waitForPageToLoad("30000");

		$this->jPrint ("Reverse the article order on the front page\n");
		$this->setDefaultTemplate('Hathor');
		$this->doAdminLogin();
		$this->click("link=Featured Articles");
		$this->waitForPageToLoad("30000");
		$this->click("link=Ordering");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//i[@class='icon-downarrow']"));
		$this->type("//input[@name='order[]' and @value='1']", "4");
		$this->type("//input[@name='order[]' and @value='2']", "3");
		$this->type("//input[@name='order[]' and @value='3']", "2");
		$this->type("//input[@name='order[]' and @value='4']", "1");
		$this->click("//a[contains(@href, 'saveorder')]");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//div[@id='system-message-container']//p[contains(text(), 'success')]"));

		$this->jPrint ("Go to front page and check article order\n");
		$this->gotoSite();
		$this->assertEquals("Professionals", $this->getText("//div[contains(@class, 'leading-0')]/h2"), "Professionals article should be intro");
		$this->assertTrue((bool) preg_match("/^[\s\S]*Upgraders[\s\S]*Beginners[\s\S]*Joomla![\s\S]*$/", $this->getText(
			"//div[contains(@class, 'items-row')]")), "Order in columns should be Upgrader, Beginners, Joomla!");

		$this->jPrint ("Go to back end and change order back to original\n");
		$this->gotoAdmin();
		$this->click("link=Featured Articles");
		$this->waitForPageToLoad("30000");
		$this->type("//input[@name='order[]' and @value='1']", "4");
		$this->type("//input[@name='order[]' and @value='2']", "3");
		$this->type("//input[@name='order[]' and @value='3']", "2");
		$this->type("//input[@name='order[]' and @value='4']", "1");
		$this->click("//a[contains(@href, 'saveorder')]");
		$this->waitForPageToLoad("30000");

		$this->jPrint ("Check that the save order was successful\n");
		$this->assertTrue($this->isElementPresent("//div[@id='system-message-container']//p[contains(text(), 'success')]"));

		$this->jPrint ("Go to site and check that the articles are in original order.\n");
		$this->gotoSite();
		$this->assertEquals("Joomla!", $this->getText("//div[contains(@class, 'leading-0')]/h2"), "Joomla! should be intro article");
		$this->assertTrue((bool) preg_match("/^[\s\S]*Beginners[\s\S]*Upgraders[\s\S]*Professionals[\s\S]*$/", $this->getText(
			"//div[contains(@class, 'items-row')]")), "Articles should be Beginners, Upgraders, Professionals");

		$this->jPrint ("Go back to back end and change menu item to sort by alpha\n");
		$this->setDefaultTemplate('isis');
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->click("link=Menus");
		$this->click("link=Main Menu");
		$this->waitForPageToLoad("30000");
		$this->click("//td/a[contains(.,  'Home')]");
		$this->waitForPageToLoad("30000");
		$this->click("//li/a[contains(text(), 'Advanced Options')]");
		$this->select("jform_params_orderby_sec", "value=alpha");
		$this->click("//div[@id='toolbar-save']/button");
		$this->waitForPageToLoad("30000");

		$this->jPrint ("Goto front page and check alpha article order \n");
		$this->gotoSite();
		$this->assertEquals("Beginners", $this->getText("//div[contains(@class, 'leading-0')]/h2"), "Beginners should be intro article");
		$this->assertEquals("Joomla!", $this->getText("//div[contains(@class, 'item column-1')]/h2"), "Joomla! should be col 1");
		$this->assertEquals("Professionals", $this->getText("//div[contains(@class, 'item column-2')]/h2"), "Professionals should be col 2");
		$this->assertEquals("Upgraders", $this->getText("//div[contains(@class, 'item column-3')]/h2"), "Upgrades should be col 3");
		$this->assertTrue((bool) preg_match("/^[\s\S]*Joomla![\s\S]*Professionals[\s\S]*Upgraders[\s\S]*$/", $this->getText(
			"//div[contains(@class, 'items-row')]")), "Articles should be Joomla!, Professionals, Upgraders");

		$this->jPrint ("Go back to back end and change parameters back.\n");
		$this->gotoAdmin();
		$this->click("link=Menus");
		$this->click("link=Main Menu");
		$this->waitForPageToLoad("30000");
		$this->jPrint ("Edit the Home Menu Item to change sorting back\n");
		$this->click("//td/a[contains(.,  'Home')]");
		$this->waitForPageToLoad("30000");
		$this->click("//li/a[contains(text(), 'Advanced Options')]");
		$this->select("jform_params_orderby_sec", "value=front");
		$this->click("//div[@id='toolbar-save']/button");
		$this->waitForPageToLoad("30000");
		$this->click("link=Article Manager");
		$this->waitForPageToLoad("30000");
		$this->click("//div[@id='toolbar-options']/button");
		$this->waitForPageToLoad("30000");
		$this->click("//a[contains(@href, 'shared')]");
		$this->select("jform_orderby_pri", "value=order");
		$this->click("//button[contains(@onclick, 'component.save')]");
		$this->waitForPageToLoad("30000");

		$this->jPrint ("Done with featured0001Test\n");
		$this->click("link=Control Panel");
		$this->waitForPageToLoad("30000");
		$this->doAdminLogout();
		$this->deleteAllVisibleCookies();
	}

}
