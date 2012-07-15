<?php
/**
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * Basic test of add, edit, and delete Content Category from back end.
 */

require_once 'SeleniumJoomlaTestCase.php';

/**
 * @group ControlPanel
 */
class ControlPanel0004 extends SeleniumJoomlaTestCase
{

	function testCreateRemoveCategory()
	{
		echo "Starting testCreateRemoveCategory\n";
		$this->setUp();
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->click("link=Control Panel");
		$this->waitForPageToLoad("30000");

		print("Navigate to Category Manager." . "\n");
		$this->click("link=Category Manager");
		$this->waitForPageToLoad("30000");
		print("New Category." . "\n");
		$this->click("//li[@id='toolbar-new']/a/span");
		$this->waitForPageToLoad("30000");
		print("Create new Category and save." . "\n");
		$this->type("jform_title", "Functional Test Category");
		$this->select("jform_parent_id", "label=- No parent -");
		$this->select("jform_published", "label=Published");
		$this->click("//li[@id='toolbar-save']/a/span");
		$this->waitForPageToLoad("30000");
		print("Check that Category is there." . "\n");

		$this->type("filter_search", "Functional Test");
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");

		$this->assertEquals("Functional Test Category", $this->getText("link=Functional Test Category"));
		print("Open for editing and change parent from ROOT to Joomla! and save" . "\n");
		$this->click("link=Functional Test Category");
		$this->waitForPageToLoad("30000");
		$this->select("jform_parent_id", "label=- - Joomla!");
		$this->click("//li[@id='toolbar-save']/a/span");
		$this->waitForPageToLoad("30000");
		print("Check that category is there." . "\n");
		$this->assertEquals("Functional Test Category", $this->getText("link=Functional Test Category"));
		$this->click("link=Functional Test Category");
		$this->waitForPageToLoad("30000");
		$this->click("//li[@id='toolbar-cancel']/a/span");
		$this->waitForPageToLoad("30000");
		print("Send new category to Trash." . "\n");
		$this->click("link=Functional Test Category");
		$this->waitForPageToLoad("30000");
		$this->select("jform_published", "label=Trashed");
		$this->click("//li[@id='toolbar-save']/a/span");
		$this->waitForPageToLoad("30000");
		print("Check that new category is not shown." . "\n");
		$this->assertFalse($this->isElementPresent("link=Functional Test Category"));
		print("Filter Trashed categories." . "\n");
		$this->select("filter_published", "label=Trashed");
		$this->clickGo();
		$this->waitForPageToLoad("30000");
		print("Select all trashed categories and delete." . "\n");
		$this->click("checkall-toggle");
		$this->click("//li[@id='toolbar-delete']/a/span");
		$this->waitForPageToLoad("30000");
		print("Check that new category is not shown." . "\n");
		$this->assertFalse($this->isElementPresent("link=Functional Test Category"));
		print("Change filter to Select State." . "\n");
		$this->click("//button[@type='button']");
		$this->waitForPageToLoad("30000");
		$this->select("filter_published", "label=- Select Status -");
		$this->waitForPageToLoad("30000");
		print("Check that new category is not shown." . "\n");
		$this->assertFalse($this->isElementPresent("link=Functional Test Category"));
		print("Change filter to Select State." . "\n");
		$this->click("//button[@type='button']");
		$this->waitForPageToLoad("30000");
		print("Check that reordering still works." . "\n");
		print("Move Modules category down one." . "\n");
		$this->click("//table[@class='adminlist']/tbody//tr//td/a[contains(text(), 'Modules')]/../../td//a[@title='Move Down']");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//dl[@id='system-message'][contains(., 'success')]"));
		print("Check that Templates and Modules categories are in new order." . "\n");
		$this->assertContains("Templates", $this->getTable("//table[@class='adminlist'].5.1"));
		$this->assertContains("Beez 20", $this->getTable("//table[@class='adminlist'].6.1"));
		$this->assertContains("Beez 5", $this->getTable("//table[@class='adminlist'].7.1"));
		$this->assertContains("Atomic", $this->getTable("//table[@class='adminlist'].8.1"));
		$this->assertContains("Modules", $this->getTable("//table[@class='adminlist'].9.1"));
		$this->assertContains("Content Modules", $this->getTable("//table[@class='adminlist'].10.1"));
		$this->assertContains("User Modules", $this->getTable("//table[@class='adminlist'].11.1"));
		$this->assertContains("Display Modules", $this->getTable("//table[@class='adminlist'].12.1"));
		$this->assertContains("Utility Modules", $this->getTable("//table[@class='adminlist'].13.1"));
		$this->assertContains("Navigation Module", $this->getTable("//table[@class='adminlist'].14.1"));
		print("Move Modules category up one." . "\n");
		$this->click("//table[@class='adminlist']/tbody//tr//td/a[contains(text(), 'Modules')]/../../td//a[@title='Move Up']");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//dl[@id='system-message'][contains(., 'success')]"));
		print("Check that Templates and Modules categories are in original order." . "\n");
		$this->assertContains("Templates", $this->getTable("//table[@class='adminlist'].11.1"));
		$this->assertContains("Beez 20", $this->getTable("//table[@class='adminlist'].12.1"));
		$this->assertContains("Beez 5", $this->getTable("//table[@class='adminlist'].13.1"));
		$this->assertContains("Atomic", $this->getTable("//table[@class='adminlist'].14.1"));
		$this->assertContains("Modules", $this->getTable("//table[@class='adminlist'].5.1"));
		$this->assertContains("Content Modules", $this->getTable("//table[@class='adminlist'].6.1"));
		$this->assertContains("User Modules", $this->getTable("//table[@class='adminlist'].7.1"));
		$this->assertContains("Display Modules", $this->getTable("//table[@class='adminlist'].8.1"));
		$this->assertContains("Utility Modules", $this->getTable("//table[@class='adminlist'].9.1"));
		$this->assertContains("Navigation Module", $this->getTable("//table[@class='adminlist'].10.1"));
		$this->doAdminLogout();
		print("Finished control_panel0004Test.php/testCreateRemoveCategory." . "\n");
		$this->deleteAllVisibleCookies();
	}

	function testCategorySaveOrder()
	{
		echo "Starting testCategorySaveOrder\n";
		$this->setUp();
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->click("link=Control Panel");
		$this->waitForPageToLoad("30000");

		print("Navigate to Category Manager." . "\n");
		$this->click("link=Category Manager");
		$this->waitForPageToLoad("30000");

		echo "Check that Save Order icon and entry fields are only active with Order ascending sort\n";
		$this->assertTrue($this->isElementPresent("//a[contains(@href, 'saveorder')][@class = 'saveorder']"));
		$this->assertFalse($this->isElementPresent("//input[@class='text-area-order'][@disabled='disabled']"));
		echo "Sort Order by descending to verify the Save Order icon is hidden and the entry field is disabled\n";
		$this->click("link=Ordering");
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isElementPresent("//a[contains(@href, 'saveorder')][@class = 'saveorder']"));
		$this->assertFalse($this->isElementPresent("//span[@class='state downarrow']"));
		$this->assertTrue($this->isElementPresent("//input[@class='text-area-order'][@disabled='disabled']"));
		echo "Sort by Access and Language to verify all ordering items either hidden or disabled\n";
		$this->click("link=Access");
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isElementPresent("//a[contains(@href, 'saveorder')][@class = 'saveorder']"));
		$this->assertFalse($this->isElementPresent("//span[@class='state downarrow']"));
		$this->assertTrue($this->isElementPresent("//input[@class='text-area-order'][@disabled='disabled']"));
		$this->click("link=Language");
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isElementPresent("//a[contains(@href, 'saveorder')][@class = 'saveorder']"));
		$this->assertFalse($this->isElementPresent("//span[@class='state downarrow']"));
		$this->assertTrue($this->isElementPresent("//input[@class='text-area-order'][@disabled='disabled']"));
		$this->click("link=Ordering");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//a[contains(@href, 'saveorder')][@class = 'saveorder']"));
		$this->assertFalse($this->isElementPresent("//input[@class='text-area-order'][@disabled='disabled']"));

		echo "Check initial ordering of categories\n";
		$this->assertContains("Components", $this->getTable("//table[@class=\"adminlist\"].4.1"));
		$this->assertContains("Modules", $this->getTable("//table[@class=\"adminlist\"].5.1"));
		$this->assertContains("Templates", $this->getTable("//table[@class=\"adminlist\"].11.1"));
		$this->assertContains("Languages", $this->getTable("//table[@class=\"adminlist\"].15.1"));
		$this->assertContains("Plugins", $this->getTable("//table[@class=\"adminlist\"].16.1"));

		echo "change the order of categories and click Save Order\n";
		$this->type("//form[@id='adminForm']/table/tbody/tr[4]/td[4]/input", "5");
		$this->type("//form[@id='adminForm']/table/tbody/tr[5]/td[4]/input", "4");
		$this->type("//form[@id='adminForm']/table/tbody/tr[11]/td[4]/input", "3");
		$this->type("//form[@id='adminForm']/table/tbody/tr[15]/td[4]/input", "2");
		$this->type("//form[@id='adminForm']/table/tbody/tr[16]/td[4]/input", "1");
		$this->click("//a[contains(@href, 'saveorder')][@class = 'saveorder']");
		$this->waitForPageToLoad("30000");

		echo "check that orders have changed\n";
		$this->assertContains("Plugins", $this->getTable("//table[@class=\"adminlist\"].4.1"));
		$this->assertContains("Languages", $this->getTable("//table[@class=\"adminlist\"].5.1"));
		$this->assertContains("Templates", $this->getTable("//table[@class=\"adminlist\"].6.1"));
		$this->assertContains("Modules", $this->getTable("//table[@class=\"adminlist\"].10.1"));
		$this->assertContains("Components", $this->getTable("//table[@class=\"adminlist\"].16.1"));
		$this->assertTrue($this->isElementPresent("//dl[@id='system-message'][contains(., 'success')]"));

		echo "put the categories back in the original order and click Save Order\n";
		$this->type("//form[@id='adminForm']/table/tbody/tr[4]/td[4]/input", "5");
		$this->type("//form[@id='adminForm']/table/tbody/tr[5]/td[4]/input", "4");
		$this->type("//form[@id='adminForm']/table/tbody/tr[6]/td[4]/input", "3");
		$this->type("//form[@id='adminForm']/table/tbody/tr[10]/td[4]/input", "2");
		$this->type("//form[@id='adminForm']/table/tbody/tr[16]/td[4]/input", "1");
		$this->click("//a[contains(@href, 'saveorder')][@class = 'saveorder']");
		$this->waitForPageToLoad("30000");

		echo "Check for success message and that order has been put back to original\n";
		$this->assertTrue($this->isElementPresent("//dl[@id='system-message'][contains(., 'success')]"));
		$this->assertContains("Components", $this->getTable("//table[@class=\"adminlist\"].4.1"));
		$this->assertContains("Modules", $this->getTable("//table[@class=\"adminlist\"].5.1"));
		$this->assertContains("Templates", $this->getTable("//table[@class=\"adminlist\"].11.1"));
		$this->assertContains("Languages", $this->getTable("//table[@class=\"adminlist\"].15.1"));
		$this->assertContains("Plugins", $this->getTable("//table[@class=\"adminlist\"].16.1"));

		echo "Try pressing save order with no form changes\n";
		$this->click("//a[contains(@href, 'saveorder')][@class = 'saveorder']");
		$this->waitForPageToLoad("30000");
		echo "Check that there is no success message and that orders haven't changed\n";
		$this->assertFalse($this->isElementPresent("//dl[@id='system-message'][contains(., 'success')]"));
		$this->assertContains("Components", $this->getTable("//table[@class=\"adminlist\"].4.1"));
		$this->assertContains("Modules", $this->getTable("//table[@class=\"adminlist\"].5.1"));
		$this->assertContains("Templates", $this->getTable("//table[@class=\"adminlist\"].11.1"));
		$this->assertContains("Languages", $this->getTable("//table[@class=\"adminlist\"].15.1"));
		$this->assertContains("Plugins", $this->getTable("//table[@class=\"adminlist\"].16.1"));
		$this->doAdminLogout();
		print("Finished control_panel0004Test.php/testCategorySaveOrder." . "\n");
		$this->deleteAllVisibleCookies();
	}

	function testMenuItemSaveOrder()
	{
		echo "Starting testMenuItemSaveOrder\n";
		$this->setUp();
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->click("link=Control Panel");
		$this->waitForPageToLoad("30000");

		echo "Open About Joomla! menu and make sure the Save Order icon and arrows are visible and the fields are enabled\n";
		$this->click("link=About Joomla");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//a[contains(@href, 'saveorder')][@class = 'saveorder']"));
		$this->assertFalse($this->isElementPresent("//input[@class='text-area-order'][@disabled='disabled']"));
		$this->assertTrue($this->isElementPresent("//span[@class='state downarrow']"));
		echo "Reverse the ordering and make sure the Save Order icon is hidden and fields are disabled\n";
		echo "But the up and down arrows are enabled.\n";
		$this->click("link=Ordering");
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isElementPresent("//a[contains(@href, 'saveorder')][@class = 'saveorder']"));
		$this->assertTrue($this->isElementPresent("//input[@class='text-area-order'][@disabled='disabled']"));
		$this->assertFalse($this->isElementPresent("//span[@class='state downarrow']"));
		echo "Change sort to Access and Language and make sure all three are disabled\n";
		$this->click("link=Access");
		$this->waitForPageToLoad("30000");

		$this->assertFalse($this->isElementPresent("//a[contains(@href, 'saveorder')][@class = 'saveorder']"));
		$this->assertTrue($this->isElementPresent("//input[@class='text-area-order'][@disabled='disabled']"));
		$this->assertFalse($this->isElementPresent("//span[@class='state downarrow']"));
		$this->click("link=Language");
		$this->waitForPageToLoad("30000");

		$this->assertFalse($this->isElementPresent("//a[contains(@href, 'saveorder')][@class = 'saveorder']"));
		$this->assertTrue($this->isElementPresent("//input[@class='text-area-order'][@disabled='disabled']"));
		$this->assertFalse($this->isElementPresent("//span[@class='state downarrow']"));
		echo "Change sort back to Ordering and make sure all three are enabled\n";
		$this->click("link=Ordering");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//a[contains(@href, 'saveorder')][@class = 'saveorder']"));
		$this->assertFalse($this->isElementPresent("//input[@class='text-area-order'][@disabled='disabled']"));
		$this->assertTrue($this->isElementPresent("//input[@class='text-area-order']"));
		$this->assertTrue($this->isElementPresent("//span[@class='state downarrow']"));
		echo "Check the starting order of the menu items\n";
		$this->assertContains("Single Article", $this->getTable("//table[@class='adminlist'].6.1"));
		$this->assertContains("Article Categories", $this->getTable("//table[@class='adminlist'].7.1"));
		$this->assertContains("Article Category Blog", $this->getTable("//table[@class='adminlist'].8.1"));
		$this->assertContains("Article Category List", $this->getTable("//table[@class='adminlist'].9.1"));

		echo "Reverse the order of 4 articles\n";
		$this->type("//form[@id='adminForm']/table/tbody/tr[6]/td[4]/input", "4");
		$this->type("//form[@id='adminForm']/table/tbody/tr[7]/td[4]/input", "3");
		$this->type("//form[@id='adminForm']/table/tbody/tr[8]/td[4]/input", "2");
		$this->type("//form[@id='adminForm']/table/tbody/tr[9]/td[4]/input", "1");

		echo "Click Save Order and check that the order changed\n";
		$this->click("//a[contains(@href, 'saveorder')][@class = 'saveorder']");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//dl[@id='system-message'][contains(., 'success')]"));
		$this->assertContains("Single Article", $this->getTable("//table[@class='adminlist'].9.1"));
		$this->assertContains("Article Categories", $this->getTable("//table[@class='adminlist'].8.1"));
		$this->assertContains("Article Category Blog", $this->getTable("//table[@class='adminlist'].7.1"));
		$this->assertContains("Article Category List", $this->getTable("//table[@class='adminlist'].6.1"));

		echo "Change the ordering back and click Save Order\n";
		$this->type("//form[@id='adminForm']/table/tbody/tr[6]/td[4]/input", "4");
		$this->type("//form[@id='adminForm']/table/tbody/tr[7]/td[4]/input", "3");
		$this->type("//form[@id='adminForm']/table/tbody/tr[8]/td[4]/input", "2");
		$this->type("//form[@id='adminForm']/table/tbody/tr[9]/td[4]/input", "1");
		$this->click("//a[contains(@href, 'saveorder')][@class = 'saveorder']");
		$this->waitForPageToLoad("30000");

		echo "Check the ordering is back to original order\n";
		$this->assertTrue($this->isElementPresent("//dl[@id='system-message'][contains(., 'success')]"));
		$this->assertContains("Single Article", $this->getTable("//table[@class='adminlist'].6.1"));
		$this->assertContains("Article Categories", $this->getTable("//table[@class='adminlist'].7.1"));
		$this->assertContains("Article Category Blog", $this->getTable("//table[@class='adminlist'].8.1"));
		$this->assertContains("Article Category List", $this->getTable("//table[@class='adminlist'].9.1"));

		echo "Click Save Order when nothing has been changed and make sure it doesn't to anything\n";
		$this->click("//a[contains(@href, 'saveorder')][@class = 'saveorder']");
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isElementPresent("//dl[@id='system-message'][contains(., 'success')]"));
		$this->assertContains("Single Article", $this->getTable("//table[@class='adminlist'].6.1"));
		$this->assertContains("Article Categories", $this->getTable("//table[@class='adminlist'].7.1"));
		$this->assertContains("Article Category Blog", $this->getTable("//table[@class='adminlist'].8.1"));
		$this->assertContains("Article Category List", $this->getTable("//table[@class='adminlist'].9.1"));
		echo "Done with control_panel0004Test/testMenuItemSaveOrder\n";
		$this->doAdminLogout();
		$this->deleteAllVisibleCookies();
	}

	function testMenuItemOrderUpDown()
	{
		echo "Starting testMenuItemOrderUpDown\n";
		$this->setUp();
		$this->gotoAdmin();
		$this->doAdminLogin();
		echo "Navigate to Menu Items -> About Joomla menu\n";
		$this->click("link=About Joomla");
		$this->waitForPageToLoad("30000");

		echo "Check that Contact Component and Content Component Menu Items are in original order\n";
		$this->assertContains("Contact Component", $this->getTable("//table[@class='adminlist'].13.1"));
		$this->assertContains("Contact Categories", $this->getTable("//table[@class='adminlist'].14.1"));
		$this->assertContains("Contact Single Category", $this->getTable("//table[@class='adminlist'].15.1"));
		$this->assertContains("Single Contact", $this->getTable("//table[@class='adminlist'].16.1"));
		$this->assertContains("Featured Contacts", $this->getTable("//table[@class='adminlist'].17.1"));
		$this->assertContains("Content Component", $this->getTable("//table[@class='adminlist'].5.1"));
		$this->assertContains("Single Article", $this->getTable("//table[@class='adminlist'].6.1"));
		$this->assertContains("Article Categories", $this->getTable("//table[@class='adminlist'].7.1"));
		$this->assertContains("Article Category Blog", $this->getTable("//table[@class='adminlist'].8.1"));
		$this->assertContains("Article Category List", $this->getTable("//table[@class='adminlist'].9.1"));
		$this->assertContains("Featured Articles", $this->getTable("//table[@class='adminlist'].10.1"));

		echo "Move Content Component Menu Item Down One\n";
		$this->click("//table[@class='adminlist']/tbody//tr//td/a[contains(text(), 'Content Component')]/../../td//a[@title='Move Down']");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//dl[@id='system-message'][contains(., 'success')]"));
		echo "Check that Contact Component and Content Component Menu Items are in new order\n";
		$this->assertContains("Contact Component", $this->getTable("//table[@class='adminlist'].5.1"));
		$this->assertContains("Contact Categories", $this->getTable("//table[@class='adminlist'].6.1"));
		$this->assertContains("Contact Single Category", $this->getTable("//table[@class='adminlist'].7.1"));
		$this->assertContains("Single Contact", $this->getTable("//table[@class='adminlist'].8.1"));
		$this->assertContains("Featured Contacts", $this->getTable("//table[@class='adminlist'].9.1"));
		$this->assertContains("Content Component", $this->getTable("//table[@class='adminlist'].10.1"));
		$this->assertContains("Single Article", $this->getTable("//table[@class='adminlist'].11.1"));
		$this->assertContains("Article Categories", $this->getTable("//table[@class='adminlist'].12.1"));
		$this->assertContains("Article Category Blog", $this->getTable("//table[@class='adminlist'].13.1"));
		$this->assertContains("Article Category List", $this->getTable("//table[@class='adminlist'].14.1"));
		$this->assertContains("Featured Articles", $this->getTable("//table[@class='adminlist'].15.1"));

		echo "Move Content Component Menu Item Up One\n";
		$this->click("//table[@class='adminlist']/tbody//tr//td/a[contains(text(), 'Content Component')]/../../td//a[@title='Move Up']");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//dl[@id='system-message'][contains(., 'success')]"));
		echo "Check that Contact Component and Content Component Menu Items are in original order\n";
		$this->assertContains("Contact Component", $this->getTable("//table[@class='adminlist'].13.1"));
		$this->assertContains("Contact Categories", $this->getTable("//table[@class='adminlist'].14.1"));
		$this->assertContains("Contact Single Category", $this->getTable("//table[@class='adminlist'].15.1"));
		$this->assertContains("Single Contact", $this->getTable("//table[@class='adminlist'].16.1"));
		$this->assertContains("Featured Contacts", $this->getTable("//table[@class='adminlist'].17.1"));
		$this->assertContains("Content Component", $this->getTable("//table[@class='adminlist'].5.1"));
		$this->assertContains("Single Article", $this->getTable("//table[@class='adminlist'].6.1"));
		$this->assertContains("Article Categories", $this->getTable("//table[@class='adminlist'].7.1"));
		$this->assertContains("Article Category Blog", $this->getTable("//table[@class='adminlist'].8.1"));
		$this->assertContains("Article Category List", $this->getTable("//table[@class='adminlist'].9.1"));
		$this->assertContains("Featured Articles", $this->getTable("//table[@class='adminlist'].10.1"));

		echo "Move Getting Started menu item down one\n";
		$this->click("//table[@class='adminlist']/tbody//tr//td/a[contains(text(), 'Getting Started')]/../../td//a[@title='Move Down']");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//dl[@id='system-message'][contains(., 'success')]"));
		echo "Check that Using Joomla! is now in first row\n";
		$this->assertContains("Using Joomla!", $this->getTable("//table[@class='adminlist'].1.1"));
		echo "Move Getting Started back up one\n";
		$this->select("limit", "label=All");
		$this->waitForPageToLoad("30000");
		$this->click("//table[@class='adminlist']/tbody//tr//td/a[contains(text(), 'Getting Started')]/../../td//a[@title='Move Up']");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//dl[@id='system-message'][contains(., 'success')]"));
		echo "Check that Getting Started is now in first row\n";
		$this->assertContains("Getting Started", $this->getTable("//table[@class='adminlist'].1.1"));
		$this->select("limit", "label=20");
		$this->waitForPageToLoad("30000");

		echo "Test moving Weblinks categories\n";
		$this->click("//ul[@id='menu-com-weblinks']/li[2]/a");
		$this->waitForPageToLoad("30000");
		echo "Move weblinks Uncatgorised up\n";
		$this->click("//table[@class='adminlist']/tbody//tr//td/a[contains(text(), 'Uncategorised')]/../../td//a[@title='Move Up']");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//dl[@id='system-message'][contains(., 'success')]"));
		$this->assertContains("Uncategorised", $this->getTable("//table[@class='adminlist'].1.1"));
		echo "Move weblinks Uncatgorised back down\n";
		$this->click("//table[@class='adminlist']/tbody//tr//td/a[contains(text(), 'Uncategorised')]/../../td//a[@title='Move Down']");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//dl[@id='system-message'][contains(., 'success')]"));
		$this->assertContains("Sample Data-Weblinks", $this->getTable("//table[@class='adminlist'].1.1"));

		echo "Done with control_panel0004Test/testMenuItemOrderUpDown\n";
		$this->doAdminLogout();
		$this->deleteAllVisibleCookies();
	}
}

