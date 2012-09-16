<?php
/**
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * Creates test group and assigns priviledges with the ACL.
 */
require_once 'SeleniumJoomlaTestCase.php';

class Menu0002 extends SeleniumJoomlaTestCase
{

	public function testSelectTypeWithoutSave()
	{
		// get logged in
		$this->setUp();
		$this->gotoAdmin();
		echo "starting testSelectTypeWithoutSave\n";
		$this->doAdminLogin();

		echo "Add new menu item to User Menu\n";
		$this->click("link=User Menu");
		$this->waitForPageToLoad("30000");

		$this->click("//div[@id='toolbar-new']/button");
		$this->waitForPageToLoad("30000");

		echo "Enter the Title\n";
		$this->type("jform_title", "Test Menu Item");
		echo "Select the menu item type\n";
		$this->click("//a[contains(text(), 'Select')]");
		$this->waitforElement("//div[contains(@id, 'sbox-window')]");
		sleep(2);

		echo "Select External URL\n";
		$this->click("Link=System Links");
		$this->click("//a[contains(., 'External URL')]");
		$this->waitForPageToLoad("60000");

		echo "Check that name is still there\n";
		$this->assertEquals("Test Menu Item", $this->getValue("jform_title"));

		echo "Save the menu item\n";
		$this->click("//div[@id='toolbar-apply']/button");
		$this->waitForPageToLoad("30000");
		echo "Change the title\n";
		$this->type("jform_title", "Test Menu Item - Edit");
		echo "Change the menu item type\n";
		$this->click("//a[contains(text(), 'Select')]");
		$this->waitforElement("//div[contains(@id, 'sbox-window')]");

		$this->click("Link=System Links");
		$this->click("//a[contains(., 'Menu Item Alias')]");
		$this->waitForPageToLoad("60000");
		echo "Check that new name is still there\n";
		$this->assertEquals("Test Menu Item - Edit", $this->getValue("jform_title"));

		echo "Change the title again\n";
		$this->type("jform_title", "Test Menu Item - Edit Again");
		$this->click("//a[contains(text(), 'Select')]");
		$this->waitforElement("//div[contains(@id, 'sbox-window')]");
		$this->click("Link=System Links");
		$this->click("//a[contains(., 'Text Separator')]");
		$this->waitForPageToLoad("30000");
		$this->assertEquals("Test Menu Item - Edit Again", $this->getValue("jform_title"));

		$this->click("//div[@id='toolbar-cancel']/button");
		$this->waitForPageToLoad("30000");

		$this->type("filter_search", "Test Menu Item");

		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");

		$this->click("cb0");
		$this->click("//div[@id='toolbar-trash']/button");
		$this->waitForPageToLoad("30000");
		$this->select("filter_published", "label=Trashed");
		$this->waitForPageToLoad("30000");
		$this->click("cb0");
		$this->click("//div[@id='toolbar-delete']/button");
		$this->waitForPageToLoad("30000");

		$this->doAdminLogout();
		echo "finishing testSelectTypeWithoutSave\n";
		$this->deleteAllVisibleCookies();
	}

	public function testSelectAndSave()
	{
		// get logged in
		echo "starting testSelectAndSave\n";
		$this->setUp();
		$this->gotoAdmin();
		$this->doAdminLogin();

		echo "Add new menu item to User Menu\n";
		$this->click("link=User Menu");
		$this->waitForPageToLoad("30000");

		$this->click("//div[@id='toolbar-new']/button");
		$this->waitForPageToLoad("30000");

		$saltGroup = mt_rand();

		$this->type("jform_title", "Test Menu Item" . $saltGroup);
		$this->click("//a[contains(text(), 'Select')]");
		$this->waitforElement("//div[contains(@id, 'sbox-window')]");

		echo "Select a menu item type\n";
		$this->click("link=Newsfeeds");
		$this->click("//a[contains(., 'List All News Feed Categories')]");
		$this->waitForPageToLoad("60000");

		echo "Make sure our changes were kept\n";
		$this->assertEquals("Test Menu Item" . $saltGroup, $this->getValue("jform_title"), 'Our title edits were not retained.');
		$this->click("//div[@id='toolbar-apply']/button");
		$this->waitForPageToLoad("30000");

		echo "Edit the title again and select a different type\n";
		$this->type("jform_title", "Test Menu Item - Edit");
		$this->click("//a[contains(text(), 'Select')]");
		$this->waitforElement("//div[contains(@id, 'sbox-window')]");

		$this->click("Link=Contacts");
		$this->click("//a[contains(., 'Single Contact')]");
		$this->waitForPageToLoad("30000");
		$this->assertEquals("Test Menu Item - Edit", $this->getValue("jform_title"), 'Our title edits were not retained.');

		$this->type("jform_title", "Test Menu Item - Edit Again");
		$this->click("//a[contains(text(), 'Select')]");
		$this->waitforElement("//div[contains(@id, 'sbox-window')]");
		$this->click("//a[contains(., 'External URL')]");
		$this->waitForPageToLoad("30000");

		$this->assertEquals("Test Menu Item - Edit Again", $this->getValue("jform_title"), 'Our title edits were not retained.');
		$this->click("//div[@id='toolbar-cancel']/button");
		$this->waitForPageToLoad("30000");

		echo "Clean up - we trash and delete our item and then log out\n";
		$this->type("filter_search", "Test Menu Item" . $saltGroup);
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");
		$this->click("cb0");
		$this->click("//div[@id='toolbar-trash']/button");
		$this->waitForPageToLoad("30000");
		$this->select("filter_published", "label=Trashed");
		$this->waitForPageToLoad("30000");
		$this->click("cb0");
		$this->click("//div[@id='toolbar-delete']/button");
		$this->waitForPageToLoad("30000");

		$this->gotoAdmin();
		$this->doAdminLogout();
		echo "finishing testSelectAndSave\n";
		$this->deleteAllVisibleCookies();
	}
}
