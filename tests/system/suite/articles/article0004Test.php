<?php
/**
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * checks that all menu choices are shown in back end
 */

require_once 'SeleniumJoomlaTestCase.php';

/**
 * @group ControlPanel
 */
class Article0004 extends SeleniumJoomlaTestCase
{
	function testBatchAcessLevels()
	{
		$this->jPrint ("Starting testBatchAcessLevels\n");
		$this->setUp();
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->click("link=Article Manager");
		$this->waitForPageToLoad("30000");
		$this->jPrint ("Check that first three articles are Public Access Level\n");
		$this->assertEquals("Public", $this->getTable("//form[@id='adminForm']//table.1.4"));
		$this->assertEquals("Public", $this->getTable("//form[@id='adminForm']//table.2.4"));
		$this->assertEquals("Public", $this->getTable("//form[@id='adminForm']//table.3.4"));
		$this->jPrint ("Select first three articles\n");
		$this->click("cb0");
		$this->click("cb1");
		$this->click("cb2");
		$this->jPrint ("Batch change to Special access\n");
		$this->select("batch-access", "label=Special");
		$this->click("//button[@type='submit' and @onclick=\"Joomla.submitbutton('article.batch');\"]");
		$this->waitForPageToLoad("30000");
		$this->jPrint ("Check for success message\n");
		$this->assertTrue($this->isElementPresent("//div[@id=\"system-message-container\"][contains(., 'success')]"));

		$this->jPrint ("Check that first three articles are Special Access Level\n");
		$this->assertEquals("Special", $this->getTable("//form[@id='adminForm']//table.1.4"));
		$this->assertEquals("Special", $this->getTable("//form[@id='adminForm']//table.2.4"));
		$this->assertEquals("Special", $this->getTable("//form[@id='adminForm']//table.3.4"));
		$this->jPrint ("Change back to Public and check\n");
		$this->click("cb0");
		$this->click("cb1");
		$this->click("cb2");
		$this->select("batch-access", "label=Public");
		$this->click("//button[@type='submit' and @onclick=\"Joomla.submitbutton('article.batch');\"]");
		$this->waitForPageToLoad("30000");
		$this->jPrint ("Check for success message\n");
		$this->assertTrue($this->isElementPresent("//div[@id=\"system-message-container\"][contains(., 'success')]"));
		$this->assertEquals("Public", $this->getTable("//form[@id='adminForm']//table.1.4"));
		$this->assertEquals("Public", $this->getTable("//form[@id='adminForm']//table.2.4"));
		$this->assertEquals("Public", $this->getTable("//form[@id='adminForm']//table.3.4"));

		$this->jPrint ("Finished testBatchAcessLevels\n");

		$this->deleteAllVisibleCookies();
	}

	function testBatchCopy()
	{
		$this->jPrint ("Starting testBatchCopy\n");
		$this->setUp();
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->click("link=Article Manager");
		$this->waitForPageToLoad("30000");
		$this->jPrint ("Check that first three articles are as expected\n");
		$this->assertStringStartsWith('Administrator Components', $this->getTable("//form[@id='adminForm']//table.1.3"));
		$this->assertStringStartsWith('Archive Module', $this->getTable("//form[@id='adminForm']//table.2.3"));
		$this->assertStringStartsWith('Article Categories Module', $this->getTable("//form[@id='adminForm']//table.3.3"));
		$this->jPrint ("Select first three articles and batch copy to Park Site\n");
		$this->click("cb0");
		$this->click("cb1");
		$this->click("cb2");
		$this->select("batch-category-id", "label=- Park Site");
		$this->click("batch[move_copy]c");
		$this->click("//button[@type='submit' and @onclick=\"Joomla.submitbutton('article.batch');\"]");
		$this->waitForPageToLoad("30000");
		$this->jPrint ("Check for success message\n");
		$this->assertTrue($this->isElementPresent("//div[@id=\"system-message-container\"][contains(., 'success')]"));
		$this->jPrint ("Check that new articles are in Park Site category\n");
		$this->select("filter_category_id", "label=- Park Site");
		$this->waitForPageToLoad("30000");
		$this->assertStringStartsWith('Administrator Components', $this->getTable("//form[@id='adminForm']//table.1.3"));
		$this->assertStringStartsWith('Archive Module', $this->getTable("//form[@id='adminForm']//table.2.3"));
		$this->assertStringStartsWith('Article Categories Module', $this->getTable("//form[@id='adminForm']//table.3.3"));
		$this->jPrint ("Trash and delete new articles\n");
		$this->click("cb0");
		$this->click("cb1");
		$this->click("cb2");
		$this->click("//div[@id='toolbar-trash']/button");
		$this->waitForPageToLoad("30000");
		$this->select("filter_published", "label=Trashed");
		$this->waitForPageToLoad("30000");
		$this->click("checkall-toggle");
		$this->click("//div[@id='toolbar-delete']/button");
		$this->waitForPageToLoad("30000");
		$this->select("filter_published", "label=- Select Status -");
		$this->waitForPageToLoad("30000");
		$this->select("filter_category_id", "label=- Select Category -");
		$this->waitForPageToLoad("30000");

		$this->jPrint ("Check that first three articles are as expected\n");
		$this->assertStringStartsWith('Administrator Components', $this->getTable("//form[@id='adminForm']//table.1.3"));
		$this->assertStringStartsWith('Archive Module', $this->getTable("//form[@id='adminForm']//table.2.3"));
		$this->assertStringStartsWith('Article Categories Module', $this->getTable("//form[@id='adminForm']//table.3.3"));

		$this->jPrint ("Test copying to same category\n");
		$this->jPrint ("Select first article and copy to Components\n");
		$this->assertStringStartsWith('Administrator Components', $this->getTable("//form[@id='adminForm']//table.1.3"));
		$this->click("cb0");
		$this->click("batch[move_copy]c");
		$this->select("batch-category-id", "label=- - - Components");
		$this->click("//button[@type='submit' and @onclick=\"Joomla.submitbutton('article.batch');\"]");
		$this->waitForPageToLoad("30000");
		$this->jPrint ("Check for success message\n");
		$this->assertTrue($this->isElementPresent("//div[@id=\"system-message-container\"][contains(., 'success')]"));
		$this->jPrint ("Check that new article is created with correct name and alias\n");
		$this->assertStringStartsWith('Administrator Components', $this->getTable("//form[@id='adminForm']//table.2.3"));
		$this->jPrint ("Trash and delete new article\n");
		$this->click("cb1");
		$this->click("//div[@id='toolbar-trash']/button");
		$this->waitForPageToLoad("30000");
		$this->select("filter_published", "label=Trashed");
		$this->waitForPageToLoad("30000");
		$this->click("checkall-toggle");
		$this->click("//div[@id='toolbar-delete']/button");
		$this->waitForPageToLoad("30000");
		$this->select("filter_published", "label=- Select Status -");
		$this->waitForPageToLoad("30000");

		$this->jPrint ("Finished testBatchCopy\n");

		$this->deleteAllVisibleCookies();
	}

	function testBatchMove()
	{
		$this->jPrint ("Starting testBatchMove\n");
		$this->setUp();
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->click("link=Article Manager");
		$this->waitForPageToLoad("30000");
		$this->jPrint ("Check initial values for articles\n");
		$this->assertStringStartsWith('Archive Module', $this->getTable("//form[@id='adminForm']//table.2.3"));
		$this->assertStringStartsWith('Article Categories Module', $this->getTable("//form[@id='adminForm']//table.3.3"));
		$this->assertStringStartsWith('Articles Category Module', $this->getTable("//form[@id='adminForm']//table.4.3"));
		$this->assertTrue(strpos($this->getTable("//form[@id='adminForm']//table.2.3"), 'Category: Content Modules') > 0);
		$this->assertTrue(strpos($this->getTable("//form[@id='adminForm']//table.3.3"), 'Category: Content Modules') > 0);
		$this->assertTrue(strpos($this->getTable("//form[@id='adminForm']//table.4.3"), 'Category: Content Modules') > 0);
		$this->jPrint ("Move Archive Module, Content Modules, Article Categories Module to Languages Category\n");
		$this->click("cb1");
		$this->click("cb2");
		$this->click("cb3");
		$this->select("batch-category-id", "label=- - - Languages");
		$this->click("//button[@type='submit' and @onclick=\"Joomla.submitbutton('article.batch');\"]");
		$this->waitForPageToLoad("30000");
		$this->jPrint ("Check for success message\n");
		$this->assertTrue($this->isElementPresent("//div[@id=\"system-message-container\"][contains(., 'success')]"));
		$this->jPrint ("Check that articles moved to new category\n");
		$this->assertStringStartsWith('Archive Module', $this->getTable("//form[@id='adminForm']//table.2.3"));
		$this->assertStringStartsWith('Article Categories Module', $this->getTable("//form[@id='adminForm']//table.3.3"));
		$this->assertStringStartsWith('Articles Category Module', $this->getTable("//form[@id='adminForm']//table.4.3"));
		$this->assertTrue(strpos($this->getTable("//form[@id='adminForm']//table.2.3"), 'Category: Languages') > 0);

		$this->assertTrue(strpos($this->getTable("//form[@id='adminForm']//table.3.3"), 'Category: Languages') > 0);

		$this->assertTrue(strpos($this->getTable("//form[@id='adminForm']//table.4.3"), 'Category: Languages') > 0);
		$this->jPrint ("Move articles back to original category\n");
		$this->click("cb1");
		$this->click("cb2");
		$this->click("cb3");
		$this->select("batch-category-id", "label=- - - - Content Modules");
		$this->click("//button[@type='submit' and @onclick=\"Joomla.submitbutton('article.batch');\"]");
		$this->waitForPageToLoad("30000");
		$this->jPrint ("Check for success message\n");
		$this->assertTrue($this->isElementPresent("//div[@id=\"system-message-container\"][contains(., 'success')]"));
		$this->jPrint ("Check that articles are back to original category\n");
		$this->assertStringStartsWith('Archive Module', $this->getTable("//form[@id='adminForm']//table.2.3"));
		$this->assertStringStartsWith('Article Categories Module', $this->getTable("//form[@id='adminForm']//table.3.3"));
		$this->assertStringStartsWith('Articles Category Module', $this->getTable("//form[@id='adminForm']//table.4.3"));
		$this->assertTrue(strpos($this->getTable("//form[@id='adminForm']//table.2.3"), 'Category: Content Modules') > 0);
		$this->assertTrue(strpos($this->getTable("//form[@id='adminForm']//table.3.3"), 'Category: Content Modules') > 0);
		$this->assertTrue(strpos($this->getTable("//form[@id='adminForm']//table.4.3"), 'Category: Content Modules') > 0);

		$this->jPrint ("Finished testBatchMove\n");

		$this->deleteAllVisibleCookies();

	}


}
