<?php
/**
 * @version		$Id$
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
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
		print("Open for editing and change parent from ROOT to News and save" . "\n");
		$this->click("link=Functional Test Category");
		$this->waitForPageToLoad("30000");
		$this->select("jform_parent_id", "label=- - News");
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
		$this->assertFalse($this->isTextPresent("Functional Test Category"));
		print("Filter Trashed categories." . "\n");
		$this->select("filter_published", "label=Trash");
		$this->clickGo();
		$this->waitForPageToLoad("30000");
		print("Select all trashed categories and delete." . "\n");
		$this->click("checkall-toggle");
		$this->click("//li[@id='toolbar-delete']/a/span");
		$this->waitForPageToLoad("30000");
		print("Check that new category is not shown." . "\n");
		$this->assertFalse($this->isTextPresent("Functional Test Category"));
		print("Change filter to Select State." . "\n");
		$this->click("//button[@type='button']");
		$this->waitForPageToLoad("30000");
		$this->select("filter_published", "label=- Select State -");
		$this->clickGo();
		$this->waitForPageToLoad("30000");

		print("Check that new category is not shown." . "\n");
		$this->assertFalse($this->isTextPresent("Functional Test Category"));
		print("Check that reordering still works." . "\n");
		print("Move Modules category up one." . "\n");
		$this->click("//table[@class='adminlist']/tbody//tr//td/a[contains(text(), 'Modules')]/../../td//a[@title='Move Up']");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Item successfully reordered"));
		print("Move Modules category down one." . "\n");
		$this->click("//table[@class='adminlist']/tbody//tr//td/a[contains(text(), 'Modules')]/../../td//a[@title='Move Down']");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Item successfully reordered"));
		$this->doAdminLogout();
		print("Finished control_panel0004Test.php/testCreateRemoveCategory." . "\n");

	}

	function testCategorySaveOrder()
	{
		$this->setUp();
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->click("link=Control Panel");
		$this->waitForPageToLoad("30000");

		print("Navigate to Category Manager." . "\n");
		$this->click("link=Category Manager");
		$this->waitForPageToLoad("30000");
		
		echo "Check that Save Order icon is shown only with Order ascending sort\n";
		$this->assertTrue($this->isElementPresent("//a[contains(@href, 'saveorder')][@class = 'saveorder']"));
		$this->click("link=Ordering");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//a[@class =  'saveorder inactive']"));
		$this->click("link=Access");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//a[@class =  'saveorder inactive']"));
		$this->click("link=Language");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//a[@class =  'saveorder inactive']"));
		$this->click("link=Ordering");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//a[contains(@href, 'saveorder')][@class = 'saveorder']"));
		
		echo "Check initial ordering of categories\n";
		$this->assertEquals("Components (Alias: components)", $this->getTable("//table[@class=\"adminlist\"].4.1"));
		$this->assertEquals("Modules (Alias: modules)", $this->getTable("//table[@class=\"adminlist\"].5.1"));
		$this->assertEquals("Templates (Alias: templates)", $this->getTable("//table[@class=\"adminlist\"].6.1"));
		$this->assertEquals("Languages (Alias: languages)", $this->getTable("//table[@class=\"adminlist\"].7.1"));
		$this->assertEquals("Plugins (Alias: plugins)", $this->getTable("//table[@class=\"adminlist\"].8.1"));
		
		echo "change the order of categories and click Save Order\n";
		$this->type("//div[@id='element-box']/div[2]/form/table/tbody/tr[4]/td[4]/input", "5");
		$this->type("//div[@id='element-box']/div[2]/form/table/tbody/tr[5]/td[4]/input", "4");
		$this->type("//div[@id='element-box']/div[2]/form/table/tbody/tr[6]/td[4]/input", "3");
		$this->type("//div[@id='element-box']/div[2]/form/table/tbody/tr[7]/td[4]/input", "2");
		$this->type("//div[@id='element-box']/div[2]/form/table/tbody/tr[8]/td[4]/input", "1");
		$this->click("//a[contains(@href, 'saveorder')][@class = 'saveorder']");
		$this->waitForPageToLoad("30000");
		
		echo "check that orders have changed\n";
		$this->assertEquals("Plugins (Alias: plugins)", $this->getTable("//table[@class=\"adminlist\"].4.1"));
		$this->assertEquals("Languages (Alias: languages)", $this->getTable("//table[@class=\"adminlist\"].5.1"));
		$this->assertEquals("Templates (Alias: templates)", $this->getTable("//table[@class=\"adminlist\"].6.1"));
		$this->assertEquals("Modules (Alias: modules)", $this->getTable("//table[@class=\"adminlist\"].7.1"));
		$this->assertEquals("Components (Alias: components)", $this->getTable("//table[@class=\"adminlist\"].8.1"));
		$this->assertTrue($this->isTextPresent("Item successfully reordered"));
		
		echo "put the categories back in the original order and click Save Order\n";
		$this->type("//div[@id='element-box']/div[2]/form/table/tbody/tr[4]/td[4]/input", "5");
		$this->type("//div[@id='element-box']/div[2]/form/table/tbody/tr[5]/td[4]/input", "4");
		$this->type("//div[@id='element-box']/div[2]/form/table/tbody/tr[6]/td[4]/input", "3");
		$this->type("//div[@id='element-box']/div[2]/form/table/tbody/tr[7]/td[4]/input", "2");
		$this->type("//div[@id='element-box']/div[2]/form/table/tbody/tr[8]/td[4]/input", "1");
		$this->click("//a[contains(@href, 'saveorder')][@class = 'saveorder']");
		$this->waitForPageToLoad("30000");
		
		echo "Check for success message and that order has been put back to original\n";
		$this->assertTrue($this->isTextPresent("Item successfully reordered"));
		$this->assertEquals("Components (Alias: components)", $this->getTable("//table[@class=\"adminlist\"].4.1"));
		$this->assertEquals("Modules (Alias: modules)", $this->getTable("//table[@class=\"adminlist\"].5.1"));
		$this->assertEquals("Templates (Alias: templates)", $this->getTable("//table[@class=\"adminlist\"].6.1"));
		$this->assertEquals("Languages (Alias: languages)", $this->getTable("//table[@class=\"adminlist\"].7.1"));
		$this->assertEquals("Plugins (Alias: plugins)", $this->getTable("//table[@class=\"adminlist\"].8.1"));
		
		echo "Try pressing save order with no form changes\n";
		$this->click("//a[contains(@href, 'saveorder')][@class = 'saveorder']");
		$this->waitForPageToLoad("30000");
		echo "Check that there is no success message and that orders haven't changed\n";
		$this->assertFalse($this->isTextPresent("Item successfully reordered"));
		$this->assertEquals("Components (Alias: components)", $this->getTable("//table[@class=\"adminlist\"].4.1"));
		$this->assertEquals("Modules (Alias: modules)", $this->getTable("//table[@class=\"adminlist\"].5.1"));
		$this->assertEquals("Templates (Alias: templates)", $this->getTable("//table[@class=\"adminlist\"].6.1"));
		$this->assertEquals("Languages (Alias: languages)", $this->getTable("//table[@class=\"adminlist\"].7.1"));
		$this->assertEquals("Plugins (Alias: plugins)", $this->getTable("//table[@class=\"adminlist\"].8.1"));
		$this->doAdminLogout();
		print("Finished control_panel0004Test.php/testCategorySaveOrder." . "\n");
	}
}
?>
