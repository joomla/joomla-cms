<?php
/**
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
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
		$this->jPrint ("Starting testCreateRemoveCategory\n");
		$this->setUp();
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->click("link=Control Panel");
		$this->waitForPageToLoad("30000");

		$this->jPrint("Navigate to Category Manager." . "\n");
		$this->click("link=Category Manager");
		$this->waitForPageToLoad("30000");
		$this->jPrint("New Category." . "\n");
		$this->click("//div[@id='toolbar-new']/button");
		$this->waitForPageToLoad("30000");
		$this->jPrint("Create new Category and save." . "\n");
		$this->type("jform_title", "Functional Test Category");
		$this->select("jform_parent_id", "label=- No parent -");
		$this->select("jform_published", "label=Published");
		$this->click("//div[@id='toolbar-save']/button");
		$this->waitForPageToLoad("30000");
		$this->jPrint("Check that Category is there." . "\n");

		$this->type("filter_search", "Functional Test");
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");

		$this->assertEquals("Functional Test Category", $this->getText("link=Functional Test Category"));
		$this->jPrint("Open for editing and change parent from ROOT to Joomla! and save" . "\n");
		$this->click("link=Functional Test Category");
		$this->waitForPageToLoad("30000");
		$this->select("jform_parent_id", "label=- - Joomla!");
		$this->click("//div[@id='toolbar-save']/button");
		$this->waitForPageToLoad("30000");
		$this->jPrint("Check that category is there." . "\n");
		$this->assertEquals("Functional Test Category", $this->getText("link=Functional Test Category"));
		$this->click("link=Functional Test Category");
		$this->waitForPageToLoad("30000");
		$this->click("//div[@id='toolbar-cancel']/button");
		$this->waitForPageToLoad("30000");
		$this->jPrint("Send new category to Trash." . "\n");
		$this->click("link=Functional Test Category");
		$this->waitForPageToLoad("30000");
		$this->select("jform_published", "label=Trashed");
		$this->click("//div[@id='toolbar-save']/button");
		$this->waitForPageToLoad("30000");
		$this->jPrint("Check that new category is not shown." . "\n");
		$this->assertFalse($this->isElementPresent("link=Functional Test Category"));
		$this->jPrint("Filter Trashed categories." . "\n");
		$this->select("filter_published", "label=Trashed");
		$this->clickGo();
		$this->waitForPageToLoad("30000");
		$this->jPrint("Select all trashed categories and delete." . "\n");
		$this->click("checkall-toggle");
		$this->click("//div[@id='toolbar-delete']/button");
		$this->waitForPageToLoad("30000");
		$this->jPrint("Check that new category is not shown." . "\n");
		$this->assertFalse($this->isElementPresent("link=Functional Test Category"));
		$this->jPrint("Change filter to Select State." . "\n");
		$this->click("//button[@type='button'][contains(@onclick, \".value=''\")]");
		$this->waitForPageToLoad("30000");
		$this->select("filter_published", "label=- Select Status -");
		$this->waitForPageToLoad("30000");
		$this->jPrint("Check that new category is not shown." . "\n");
		$this->assertFalse($this->isElementPresent("link=Functional Test Category"));
		$this->jPrint("Change filter to Select State." . "\n");
		$this->click("//button[@type='button'][contains(@onclick, \".value=''\")]");
		$this->waitForPageToLoad("30000");
		$this->jPrint("Check that reordering still works." . "\n");
		$this->jPrint("Check that Templates and Modules categories are in original order." . "\n");
		$this->assertContains("Templates", $this->getTable("//table[@class='table table-striped'].11.3"));
		$this->assertContains("Beez3", $this->getTable("//table[@class='table table-striped'].12.3"));
		$this->assertContains("Protostar", $this->getTable("//table[@class='table table-striped'].13.3"));
		$this->assertContains("Modules", $this->getTable("//table[@class='table table-striped'].5.3"));
		$this->assertContains("Content Modules", $this->getTable("//table[@class='table table-striped'].6.3"));
		$this->assertContains("User Modules", $this->getTable("//table[@class='table table-striped'].7.3"));
		$this->assertContains("Display Modules", $this->getTable("//table[@class='table table-striped'].8.3"));
		$this->assertContains("Utility Modules", $this->getTable("//table[@class='table table-striped'].9.3"));
		$this->assertContains("Navigation Module", $this->getTable("//table[@class='table table-striped'].10.3"));

		$this->select("filter_level", "value=4");
		$this->waitForPageToLoad("30000");
		$this->jPrint("Move Modules category down one (below Templates)." . "\n");

		$this->mouseDownAt("//tr/td/a[contains(text(), 'Modules')]/../../td[1]/span", "");
		$this->mouseMoveAt("//tr/td/a[contains(text(), 'Templates')]/../../td[1]/span", "0,30");
		$this->mouseUpAt("//tr/td/a[contains(text(), 'Templates')]/../../td[1]/span", "");
		sleep(2);


		$this->select("filter_level", "value=");
		$this->waitForPageToLoad("30000");
		$this->jPrint("Check that Templates and Modules categories are in new order." . "\n");
		$this->assertContains("Templates", $this->getTable("//table[@class='table table-striped'].5.3"));
		$this->assertContains("Beez3", $this->getTable("//table[@class='table table-striped'].6.3"));
		$this->assertContains("Protostar", $this->getTable("//table[@class='table table-striped'].7.3"));
		$this->assertContains("Modules", $this->getTable("//table[@class='table table-striped'].8.3"));
		$this->assertContains("Content Modules", $this->getTable("//table[@class='table table-striped'].9.3"));
		$this->assertContains("User Modules", $this->getTable("//table[@class='table table-striped'].10.3"));
		$this->assertContains("Display Modules", $this->getTable("//table[@class='table table-striped'].11.3"));
		$this->assertContains("Utility Modules", $this->getTable("//table[@class='table table-striped'].12.3"));
		$this->assertContains("Navigation Module", $this->getTable("//table[@class='table table-striped'].13.3"));


		$this->jPrint("Move Modules category back up (above Templates, below Components)." . "\n");
		$this->select("filter_level", "value=4");
		$this->waitForPageToLoad("30000");
		$this->mouseDownAt("//tr/td/a[contains(text(), 'Modules')]/../../td[1]/span", "");
		$this->mouseMoveAt("//tr/td/a[contains(text(), 'Templates')]/../../td[1]/span", "0,5");
		$this->mouseUpAt("//tr/td/a[contains(text(), 'Templates')]/../../td[1]/span", "");
		sleep(2);


		$this->select("filter_level", "value=");
		$this->waitForPageToLoad("30000");
		$this->jPrint("Check that Templates and Modules categories are in original order." . "\n");
		$this->assertContains("Templates", $this->getTable("//table[@class='table table-striped'].11.3"));
		$this->assertContains("Beez3", $this->getTable("//table[@class='table table-striped'].12.3"));
		$this->assertContains("Protostar", $this->getTable("//table[@class='table table-striped'].13.3"));
		$this->assertContains("Modules", $this->getTable("//table[@class='table table-striped'].5.3"));
		$this->assertContains("Content Modules", $this->getTable("//table[@class='table table-striped'].6.3"));
		$this->assertContains("User Modules", $this->getTable("//table[@class='table table-striped'].7.3"));
		$this->assertContains("Display Modules", $this->getTable("//table[@class='table table-striped'].8.3"));
		$this->assertContains("Utility Modules", $this->getTable("//table[@class='table table-striped'].9.3"));
		$this->assertContains("Navigation Module", $this->getTable("//table[@class='table table-striped'].10.3"));
		$this->doAdminLogout();
		$this->jPrint("Finished control_panel0004Test.php/testCreateRemoveCategory." . "\n");
		$this->deleteAllVisibleCookies();
	}

	function testCategorySaveOrder()
	{
		$this->jPrint ("Starting testCategorySaveOrder\n");
		$this->setUp();
		$this->gotoAdmin();
		$this->setDefaultTemplate('Hathor');
		$this->doAdminLogin();
		$this->click("link=Control Panel");
		$this->waitForPageToLoad("30000");

		$this->jPrint("Navigate to Category Manager." . "\n");
		$this->click("link=Category Manager");
		$this->waitForPageToLoad("30000");

		$this->jPrint ("Check that Save Order icon and entry fields are only active with Order ascending sort\n");
		$this->assertTrue($this->isElementPresent("//a[contains(@href, 'saveorder')][contains(@class, 'saveorder')]"));
		$this->assertFalse($this->isElementPresent("//input[@class='text-area-order'][@disabled='disabled']"));
		$this->jPrint ("Sort Order by descending to verify the Save Order icon is hidden and the entry field is disabled\n");
		$this->click("link=Ordering");
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isElementPresent("//a[contains(@href, 'saveorder')][contains(@class, 'saveorder')]"));
		$this->assertFalse($this->isElementPresent("//span[@class='state downarrow']"));
		$this->assertTrue($this->isElementPresent("//input[@class='text-area-order'][@disabled='disabled']"));
		$this->jPrint ("Sort by Access and Language to verify all ordering items either hidden or disabled\n");
		$this->click("link=Access");
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isElementPresent("//a[contains(@href, 'saveorder')][contains(@class, 'saveorder')]"));
		$this->assertFalse($this->isElementPresent("//span[@class='state downarrow']"));
		$this->assertTrue($this->isElementPresent("//input[@class='text-area-order'][@disabled='disabled']"));
		$this->click("link=Language");
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isElementPresent("//a[contains(@href, 'saveorder')][contains(@class, 'saveorder')]"));
		$this->assertFalse($this->isElementPresent("//span[@class='state downarrow']"));
		$this->assertTrue($this->isElementPresent("//input[@class='text-area-order'][@disabled='disabled']"));
		$this->click("link=Ordering");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//a[contains(@href, 'saveorder')][contains(@class, 'saveorder')]"));
		$this->assertFalse($this->isElementPresent("//input[@class='text-area-order'][@disabled='disabled']"));

		$this->jPrint ("Check initial ordering of categories\n");
		$this->assertContains("Components", $this->getTable("//table[@class='adminlist'].4.1"));
		$this->assertContains("Modules", $this->getTable("//table[@class='adminlist'].5.1"));
		$this->assertContains("Templates", $this->getTable("//table[@class='adminlist'].11.1"));
		$this->assertContains("Languages", $this->getTable("//table[@class='adminlist'].14.1"));
		$this->assertContains("Plugins", $this->getTable("//table[@class='adminlist'].15.1"));

		$this->jPrint ("change the order of categories and click Save Order\n");
		$this->type("xpath=(//input[@name='order[]'])[4]", "5");
		$this->type("xpath=(//input[@name='order[]'])[5]", "4");
		$this->type("xpath=(//input[@name='order[]'])[11]", "3");
		$this->type("xpath=(//input[@name='order[]'])[14]", "2");
		$this->type("xpath=(//input[@name='order[]'])[15]", "1");

		$this->click("//a[contains(@href, 'saveorder')][contains(@class, 'saveorder')]");
		$this->waitForPageToLoad("30000");

		$this->jPrint ("check that orders have changed\n");
		$this->assertContains("Plugins", $this->getTable("//table[@class='adminlist'].4.1"));
		$this->assertContains("Languages", $this->getTable("//table[@class='adminlist'].5.1"));
		$this->assertContains("Templates", $this->getTable("//table[@class='adminlist'].6.1"));
		$this->assertContains("Modules", $this->getTable("//table[@class='adminlist'].9.1"));
		$this->assertContains("Components", $this->getTable("//table[@class='adminlist'].15.1"));
		$this->assertTrue($this->isElementPresent("//div[@id='system-message-container'][contains(., 'success')]"));

		$this->jPrint ("put the categories back in the original order and click Save Order\n");
		$this->type("xpath=(//input[@name='order[]'])[4]", "5");
		$this->type("xpath=(//input[@name='order[]'])[5]", "4");
		$this->type("xpath=(//input[@name='order[]'])[6]", "3");
		$this->type("xpath=(//input[@name='order[]'])[9]", "2");
		$this->type("xpath=(//input[@name='order[]'])[15]", "1");
		$this->click("//a[contains(@href, 'saveorder')][contains(@class, 'saveorder')]");
		$this->waitForPageToLoad("30000");

		$this->jPrint ("Check for success message and that order has been put back to original\n");
		$this->assertTrue($this->isElementPresent("//div[@id='system-message-container'][contains(., 'success')]"));
		$this->assertContains("Components", $this->getTable("//table[@class='adminlist'].4.1"));
		$this->assertContains("Modules", $this->getTable("//table[@class='adminlist'].5.1"));
		$this->assertContains("Templates", $this->getTable("//table[@class='adminlist'].11.1"));
		$this->assertContains("Languages", $this->getTable("//table[@class='adminlist'].14.1"));
		$this->assertContains("Plugins", $this->getTable("//table[@class='adminlist'].15.1"));

		$this->jPrint ("Try pressing save order with no form changes\n");
		$this->click("//a[contains(@href, 'saveorder')][contains(@class, 'saveorder')]");
		$this->waitForPageToLoad("30000");
		$this->jPrint ("Check that there is no success message and that orders haven't changed\n");
		$this->assertFalse($this->isElementPresent("//div[@id='system-message-container'][contains(., 'success')]"));
		$this->assertContains("Components", $this->getTable("//table[@class='adminlist'].4.1"));
		$this->assertContains("Modules", $this->getTable("//table[@class='adminlist'].5.1"));
		$this->assertContains("Templates", $this->getTable("//table[@class='adminlist'].11.1"));
		$this->assertContains("Languages", $this->getTable("//table[@class='adminlist'].14.1"));
		$this->assertContains("Plugins", $this->getTable("//table[@class='adminlist'].15.1"));
		$this->doAdminLogout();
		$this->setDefaultTemplate('isis');
		$this->jPrint("Finished control_panel0004Test.php/testCategorySaveOrder." . "\n");
		$this->deleteAllVisibleCookies();
	}

	function testMenuItemSaveOrder()
	{
		$this->jPrint ("Starting testMenuItemSaveOrder\n");
		$this->setUp();
		$this->gotoAdmin();
		$this->setDefaultTemplate('Hathor');
		$this->doAdminLogin();
		$this->click("link=Control Panel");
		$this->waitForPageToLoad("30000");

		$this->jPrint ("Open About Joomla! menu and make sure the Save Order icon and arrows are visible and the fields are enabled\n");
		$this->click("link=About Joomla");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//a[contains(@href, 'saveorder')][contains(@class, 'saveorder')]"));
		$this->assertFalse($this->isElementPresent("//input[@class='text-area-order'][@disabled='disabled']"));
		$this->assertTrue($this->isElementPresent("//i[@class='icon-downarrow']"));
		$this->jPrint ("Reverse the ordering and make sure the Save Order icon is hidden and fields are disabled\n");
		$this->jPrint ("But the up and down arrows are enabled.\n");
		$this->click("link=Ordering");
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isElementPresent("//a[contains(@href, 'saveorder')][contains(@class, 'saveorder')]"));
		$this->assertTrue($this->isElementPresent("//input[@class='text-area-order'][@disabled='disabled']"));
		$this->assertFalse($this->isElementPresent("//i[@class='icon-downarrow']"));
		$this->jPrint ("Change sort to Access and Language and make sure all three are disabled\n");
		$this->click("link=Access");
		$this->waitForPageToLoad("30000");

		$this->assertFalse($this->isElementPresent("//a[contains(@href, 'saveorder')][contains(@class, 'saveorder')]"));
		$this->assertTrue($this->isElementPresent("//input[@class='text-area-order'][@disabled='disabled']"));
		$this->assertFalse($this->isElementPresent("//i[@class='icon-downarrow']"));
		$this->click("link=Language");
		$this->waitForPageToLoad("30000");

		$this->assertFalse($this->isElementPresent("//a[contains(@href, 'saveorder')][contains(@class, 'saveorder')]"));
		$this->assertTrue($this->isElementPresent("//input[@class='text-area-order'][@disabled='disabled']"));
		$this->assertFalse($this->isElementPresent("//i[@class='icon-downarrow']"));
		$this->jPrint ("Change sort back to Ordering and make sure all three are enabled\n");
		$this->click("link=Ordering");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//a[contains(@href, 'saveorder')][contains(@class, 'saveorder')]"));
		$this->assertFalse($this->isElementPresent("//input[@class='text-area-order'][@disabled='disabled']"));
		$this->assertTrue($this->isElementPresent("//input[@class='text-area-order']"));
		$this->assertTrue($this->isElementPresent("//i[@class='icon-downarrow']"));
		$this->jPrint ("Check the starting order of the menu items\n");
		$this->assertContains("Single Article", $this->getTable("//table[@class='adminlist'].6.1"));
		$this->assertContains("Article Categories", $this->getTable("//table[@class='adminlist'].7.1"));
		$this->assertContains("Article Category Blog", $this->getTable("//table[@class='adminlist'].8.1"));
		$this->assertContains("Article Category List", $this->getTable("//table[@class='adminlist'].9.1"));

		$this->jPrint ("Reverse the order of 4 articles\n");
		$this->type("//form[@id='adminForm']//table/tbody/tr[6]/td[4]/input", "4");
		$this->type("//form[@id='adminForm']//table/tbody/tr[7]/td[4]/input", "3");
		$this->type("//form[@id='adminForm']//table/tbody/tr[8]/td[4]/input", "2");
		$this->type("//form[@id='adminForm']//table/tbody/tr[9]/td[4]/input", "1");

		$this->jPrint ("Click Save Order and check that the order changed\n");
		$this->click("//a[contains(@href, 'saveorder')][contains(@class, 'saveorder')]");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//div[@id='system-message-container'][contains(., 'success')]"));
		$this->assertContains("Single Article", $this->getTable("//table[@class='adminlist'].9.1"));
		$this->assertContains("Article Categories", $this->getTable("//table[@class='adminlist'].8.1"));
		$this->assertContains("Article Category Blog", $this->getTable("//table[@class='adminlist'].7.1"));
		$this->assertContains("Article Category List", $this->getTable("//table[@class='adminlist'].6.1"));

		$this->jPrint ("Change the ordering back and click Save Order\n");
		$this->type("//form[@id='adminForm']//table/tbody/tr[6]/td[4]/input", "4");
		$this->type("//form[@id='adminForm']//table/tbody/tr[7]/td[4]/input", "3");
		$this->type("//form[@id='adminForm']//table/tbody/tr[8]/td[4]/input", "2");
		$this->type("//form[@id='adminForm']//table/tbody/tr[9]/td[4]/input", "1");
		$this->click("//a[contains(@href, 'saveorder')][contains(@class, 'saveorder')]");
		$this->waitForPageToLoad("30000");

		$this->jPrint ("Check the ordering is back to original order\n");
		$this->assertTrue($this->isElementPresent("//div[@id='system-message-container'][contains(., 'success')]"));
		$this->assertContains("Single Article", $this->getTable("//table[@class='adminlist'].6.1"));
		$this->assertContains("Article Categories", $this->getTable("//table[@class='adminlist'].7.1"));
		$this->assertContains("Article Category Blog", $this->getTable("//table[@class='adminlist'].8.1"));
		$this->assertContains("Article Category List", $this->getTable("//table[@class='adminlist'].9.1"));

		$this->jPrint ("Click Save Order when nothing has been changed and make sure it doesn't to anything\n");
		$this->click("//a[contains(@href, 'saveorder')][contains(@class, 'saveorder')]");
		$this->waitForPageToLoad("30000");
		$this->assertFalse($this->isElementPresent("//div[@id='system-message-container'][contains(., 'success')]"));
		$this->assertContains("Single Article", $this->getTable("//table[@class='adminlist'].6.1"));
		$this->assertContains("Article Categories", $this->getTable("//table[@class='adminlist'].7.1"));
		$this->assertContains("Article Category Blog", $this->getTable("//table[@class='adminlist'].8.1"));
		$this->assertContains("Article Category List", $this->getTable("//table[@class='adminlist'].9.1"));
		$this->jPrint ("Done with control_panel0004Test/testMenuItemSaveOrder\n");
		$this->doAdminLogout();
		$this->setDefaultTemplate('isis');
		$this->deleteAllVisibleCookies();
	}

	function testMenuItemOrderUpDown()
	{
		$this->jPrint ("Starting testMenuItemOrderUpDown\n");
		$this->setUp();
		$this->gotoAdmin();
		$this->setDefaultTemplate('Hathor');
		$this->doAdminLogin();
		$this->jPrint ("Navigate to Menu Items -> About Joomla menu\n");
		$this->click("link=About Joomla");
		$this->waitForPageToLoad("30000");

		$this->jPrint ("Check that Contact Component and Content Component Menu Items are in original order\n");
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

		$this->jPrint ("Move Content Component Menu Item Down One\n");
		$this->click("//table[@class='adminlist']/tbody//tr//td/a[contains(text(), 'Content Component')]/../../td//a[@title='Move Down']");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//div[@id='system-message-container'][contains(., 'success')]"));
		$this->jPrint ("Check that Contact Component and Content Component Menu Items are in new order\n");
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

		$this->jPrint ("Move Content Component Menu Item Up One\n");
		$this->click("//table[@class='adminlist']/tbody//tr//td/a[contains(text(), 'Content Component')]/../../td//a[@title='Move Up']");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//div[@id='system-message-container'][contains(., 'success')]"));
		$this->jPrint ("Check that Contact Component and Content Component Menu Items are in original order\n");
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

		$this->jPrint ("Move Getting Started menu item down one\n");
		$this->click("//table[@class='adminlist']/tbody//tr//td/a[contains(text(), 'Getting Started')]/../../td//a[@title='Move Down']");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//div[@id='system-message-container'][contains(., 'success')]"));
		$this->jPrint ("Check that Using Joomla! is now in first row\n");
		$this->assertContains("Using Joomla!", $this->getTable("//table[@class='adminlist'].1.1"));
		$this->jPrint ("Move Getting Started back up one\n");
		$this->select("limit", "value=0");
		$this->clickGo();
		$this->waitForPageToLoad("30000");
		$this->click("//table[@class='adminlist']/tbody//tr//td/a[contains(text(), 'Getting Started')]/../../td//a[@title='Move Up']");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//div[@id='system-message-container'][contains(., 'success')]"));
		$this->jPrint ("Check that Getting Started is now in first row\n");
		$this->assertContains("Getting Started", $this->getTable("//table[@class='adminlist'].1.1"));
		$this->select("limit", "value=20");
		$this->clickGo();
		$this->waitForPageToLoad("30000");

		$this->jPrint ("Test moving Weblinks categories\n");
		$this->click("Link=Weblinks");
		$this->waitForPageToLoad("30000");
		$this->click("//a[contains(@href, 'option=com_categories&extension=com_weblinks')]");
		$this->waitForPageToLoad("30000");
		$this->jPrint ("Move weblinks Uncatgorised up\n");
		$this->click("//table[@class='adminlist']/tbody//tr//td/a[contains(text(), 'Uncategorised')]/../../td//a[@title='Move Up']");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//div[@id='system-message-container'][contains(., 'success')]"));
		$this->assertContains("Uncategorised", $this->getTable("//table[@class='adminlist'].1.1"));
		$this->jPrint ("Move weblinks Uncatgorised back down\n");
		$this->click("//table[@class='adminlist']/tbody//tr//td/a[contains(text(), 'Uncategorised')]/../../td//a[@title='Move Down']");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//div[@id='system-message-container'][contains(., 'success')]"));
		$this->assertContains("Sample Data-Weblinks", $this->getTable("//table[@class='adminlist'].1.1"));

		$this->jPrint ("Done with control_panel0004Test/testMenuItemOrderUpDown\n");
		$this->doAdminLogout();
		$this->setDefaultTemplate('isis');
		$this->deleteAllVisibleCookies();
	}
}

