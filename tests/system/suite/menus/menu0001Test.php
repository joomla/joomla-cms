
<?php
/**
 * @version		$Id$
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * checks that all menu choices are shown in back end
 */

require_once 'SeleniumJoomlaTestCase.php';

/**
 * @group ControlPanel
 */
class Menu0001 extends SeleniumJoomlaTestCase
{

	function testMenuItemAdd()
	{
		echo "Starting testMenuItemAdd()\n";
		$this->gotoAdmin();
		$this->doAdminLogin();
		echo "Navigate to Menu Manager and add new menu\n";
		$this->click("link=Menu Manager");
		$this->waitForPageToLoad("30000");
		$this->click("//li[@id='toolbar-new']/a/span");
		$this->waitForPageToLoad("30000");
		$this->type("jform_title", "Functional Test Menu");
		$this->type("jform_menutype", "function-test-menu");
		$this->type("jform_menudescription", "Menu for testing");
		$this->click("//li[@id='toolbar-save']/a/span");
		$this->waitForPageToLoad("30000");

		echo "Navigate to Menu Item Manager\n";
		$this->click("link=Menu Items");
		$this->waitForPageToLoad("30000");
		echo "Add new menu item\n";
		$this->click("//li[@id='toolbar-new']/a/span");
		$this->waitForPageToLoad("60000");

		echo "Select the menu item type\n";
		$this->click("//input[@value='Select']");

		for ($second = 0; ; $second++) {
			if ($second >= 60) $this->fail("timeout");
			try {
				if ($this->isElementPresent("//div[contains(@id, 'sbox-content')]")) break;
			} catch (Exception $e) {}
			sleep(1);
		}
		echo "Select Single Article\n";
		$this->click("link=Single Article");
		$this->waitForPageToLoad("60000");
		echo "Enter menu item info\n";
		$this->type("jform_title", "Functional Test Menu Item");
		$this->select("jform_published", "label=Published");
		$this->select("jform_menutype", "label=Functional Test Menu");
		echo "Open Select Article modal\n";
		$this->click("link=Change");

		for ($second = 0; ; $second++) {
			if ($second >= 60) $this->fail("timeout");
			try {
				if ($this->isElementPresent("//iframe[contains(@src, 'jSelectArticle')]")) break;
			} catch (Exception $e) {}
			sleep(1);
		}
		// test sleep command for hudson error
		sleep(3);
		$this->click("link=Australian Parks");

		for ($second = 0; ; $second++) {
			if ($second >= 60) $this->fail("timeout");
			try {
				if ($this->isElementPresent("//div[contains(@id, 'menu-sliders')]")) break;
			} catch (Exception $e) {}
			sleep(1);
		}
		
		$this->click("//li[@id='toolbar-save']/a/span");
		$this->waitForPageToLoad("30000");

		echo "Navigate to Module Manager and add new menu module\n";
		$this->click("link=Module Manager");
		$this->waitForPageToLoad("30000");
		echo "Select New Module\n";
		$this->click("//li[@id='toolbar-popup-Popup']/a/span");
		for ($second = 0; ; $second++) {
			if ($second >= 10) $this->fail("timeout");
			try {
				 if ($this->isElementPresent("//ul[@id='new-modules-list']")) break;
			} catch (Exception $e) {}
			sleep(1);
		}
		echo "Select Menu module\n";
		$this->click("link=Menu");
		$this->waitForPageToLoad("30000");

		echo "Fill in menu name and info\n";

		$this->type("jform_title", "Functional Test Menu");
		$this->select("jform_published", "label=Published");
		$this->select("jform[assignment]", "label=No pages");
		$this->select("jform_position", "label=position-7");
   	 	$this->select("jform[assignment]", "label=On all pages");
		$this->select("jform_params_menutype", "label=Functional Test Menu");
		echo "Save menu\n";
		$this->click("//li[@id='toolbar-save']/a/span");
		$this->waitForPageToLoad("30000");

		echo "Navigate to Front End and make sure new menu is there\n";
		$this->gotoSite();
		$this->assertTrue($this->isTextPresent("Functional Test Menu"));
		$this->assertTrue($this->isElementPresent("link=Functional Test Menu Item"));

		echo "Click on new menu choice and make sure article shows\n";
		$this->click("link=Functional Test Menu Item");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Australian Parks"));

		echo "Navigate to back end\n";
		$this->gotoAdmin();

		echo "Navigate to Module Manager and delete new menu module\n";
		$this->click("link=Module Manager");
		$this->waitForPageToLoad("30000");
		$this->type("filter_search", "functional test");
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");
		$this->click("cb0");
		$this->click("toggle");
		$this->click("//li[@id='toolbar-trash']/a/span");
		$this->waitForPageToLoad("30000");

		echo "Navigate to Menu Item Manager and delete new menu item\n";
		$this->click("link=Functional Test Menu");
		$this->waitForPageToLoad("30000");
		$this->click("toggle");
		$this->click("//li[@id='toolbar-trash']/a/span");
		$this->waitForPageToLoad("30000");

		echo "Navigate to Menu Manager and delete new menu\n";
		$this->click("//ul[@id='submenu']/li[1]/a");
		$this->waitForPageToLoad("30000");
		$this->click("cb6");
		$this->click("//li[@id='toolbar-delete']/a/span");
		sleep(2);
		$this->assertTrue((bool)preg_match("/^Are you sure you want to delete/",$this->getConfirmation()));

		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Menu type successfully deleted"));
		echo "Navigate to front end and make sure menu is not shown\n";
		$this->gotoSite();
		$this->assertFalse($this->isTextPresent("Functional Test Menu"));
		$this->assertFalse($this->isElementPresent("link=Functional Test Menu Item"));

		$this->gotoAdmin();
		$this->doAdminLogout();
		echo "Finished testMenuItemAdd()\n";
	}
}

