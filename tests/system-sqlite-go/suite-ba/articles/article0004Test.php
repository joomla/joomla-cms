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
class Article0004 extends SeleniumJoomlaTestCase
{
	function testBatchAcessLevels()
	{
		echo "Starting testBatchAcessLevels\n";
		$this->setUp();
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->click("link=Article Manager");
		$this->waitForPageToLoad("30000");
		echo "Check that first three articles are Public Access Level\n";
		$this->assertEquals("Public", $this->getTable("//form[@id='adminForm']/table.1.6"));
		$this->assertEquals("Public", $this->getTable("//form[@id='adminForm']/table.2.6"));
		$this->assertEquals("Public", $this->getTable("//form[@id='adminForm']/table.3.6"));
		echo "Select first three articles\n";
		$this->click("cb0");
		$this->click("cb1");
		$this->click("cb2");
		echo "Batch change to Special access\n";
		$this->select("batch-access", "label=Special");
		$this->click("//button[@type='submit' and @onclick=\"Joomla.submitbutton('article.batch');\"]");
		$this->waitForPageToLoad("30000");
		echo "Check for success message\n";
		$this->assertTrue($this->isElementPresent("//dl[@id=\"system-message\"][contains(., 'success')]"));

		echo "Check that first three articles are Special Access Level\n";
		$this->assertEquals("Special", $this->getTable("//form[@id='adminForm']/table.1.6"));
		$this->assertEquals("Special", $this->getTable("//form[@id='adminForm']/table.2.6"));
		$this->assertEquals("Special", $this->getTable("//form[@id='adminForm']/table.3.6"));
		echo "Change back to Public and check\n";
		$this->click("cb0");
		$this->click("cb1");
		$this->click("cb2");
		$this->select("batch-access", "label=Public");
		$this->click("//button[@type='submit' and @onclick=\"Joomla.submitbutton('article.batch');\"]");
		$this->waitForPageToLoad("30000");
		echo "Check for success message\n";
		$this->assertTrue($this->isElementPresent("//dl[@id=\"system-message\"][contains(., 'success')]"));
		$this->assertEquals("Public", $this->getTable("//form[@id='adminForm']/table.1.6"));
		$this->assertEquals("Public", $this->getTable("//form[@id='adminForm']/table.2.6"));
		$this->assertEquals("Public", $this->getTable("//form[@id='adminForm']/table.3.6"));

		echo "Finished testBatchAcessLevels\n";

		$this->deleteAllVisibleCookies();
	}

	function testBatchCopy()
	{
		echo "Starting testBatchCopy\n";
		$this->setUp();
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->click("link=Article Manager");
		$this->waitForPageToLoad("30000");
		echo "Check that first three articles are as expected\n";
		$this->assertTrue((bool) strpos($this->getTable("//form[@id='adminForm']/table.1.1"), 'Alias: administrator-components'));
		$this->assertEquals("Components", $this->getTable("//form[@id='adminForm']/table.1.4"));
		$this->assertTrue((bool) strpos($this->getTable("//form[@id='adminForm']/table.2.1"), '(Alias: archive-module'));
		$this->assertEquals("Content Modules", $this->getTable("//form[@id='adminForm']/table.2.4"));
		$this->assertTrue((bool) strpos($this->getTable("//form[@id='adminForm']/table.3.1"), 'Alias: article-categories-module'));
		$this->assertEquals("Content Modules", $this->getTable("//form[@id='adminForm']/table.3.4"));
		echo "Select first three articles and batch copy to Park Site\n";
		$this->click("cb0");
		$this->click("cb1");
		$this->click("cb2");
		$this->select("batch-category-id", "label=- Park Site");
		$this->click("batch[move_copy]c");
		$this->click("//button[@type='submit' and @onclick=\"Joomla.submitbutton('article.batch');\"]");
		$this->waitForPageToLoad("30000");
		echo "Check for success message\n";
		$this->assertTrue($this->isElementPresent("//dl[@id=\"system-message\"][contains(., 'success')]"));
		echo "Check that new articles are in Park Site category\n";
		$this->select("filter_category_id", "label=- Park Site");
		$this->waitForPageToLoad("30000");
		$this->assertTrue((bool) strpos($this->getTable("//form[@id='adminForm']/table.1.1"), 'Alias: administrator-components'));
		$this->assertTrue((bool) strpos($this->getTable("//form[@id='adminForm']/table.2.1"), '(Alias: archive-module'));
		$this->assertTrue((bool) strpos($this->getTable("//form[@id='adminForm']/table.3.1"), 'Alias: article-categories-module'));
		echo "Trash and delete new articles\n";
		$this->click("cb0");
		$this->click("cb1");
		$this->click("cb2");
		$this->click("//li[@id='toolbar-trash']/a/span");
		$this->waitForPageToLoad("30000");
		$this->select("filter_published", "label=Trashed");
		$this->waitForPageToLoad("30000");
		$this->click("checkall-toggle");
		$this->click("//li[@id='toolbar-delete']/a/span");
		$this->waitForPageToLoad("30000");
		$this->select("filter_published", "label=- Select Status -");
		$this->waitForPageToLoad("30000");
		$this->select("filter_category_id", "label=- Select Category -");
		$this->waitForPageToLoad("30000");

		echo "Check that first three articles are as expected\n";
		$this->assertTrue((bool) strpos($this->getTable("//form[@id='adminForm']/table.1.1"), 'Alias: administrator-components'));
		$this->assertEquals("Components", $this->getTable("//form[@id='adminForm']/table.1.4"));
		$this->assertTrue((bool) strpos($this->getTable("//form[@id='adminForm']/table.2.1"), 'Alias: archive-module'));
		$this->assertEquals("Content Modules", $this->getTable("//form[@id='adminForm']/table.2.4"));
		$this->assertTrue((bool) strpos($this->getTable("//form[@id='adminForm']/table.3.1"), 'Alias: article-categories-module'));
		$this->assertEquals("Content Modules", $this->getTable("//form[@id='adminForm']/table.3.4"));

		echo "Test copying to same category\n";
		echo "Select first article and copy to Components\n";
		$this->assertEquals("Components", $this->getTable("//form[@id='adminForm']/table.1.4"));
		$this->click("cb0");
		$this->click("batch[move_copy]c");
		$this->select("batch-category-id", "label=- - - Components");
		$this->click("//button[@type='submit' and @onclick=\"Joomla.submitbutton('article.batch');\"]");
		$this->waitForPageToLoad("30000");
		echo "Check for success message\n";
		$this->assertTrue($this->isElementPresent("//dl[@id=\"system-message\"][contains(., 'success')]"));
		echo "Check that new article is created with correct name and alias\n";
		$this->assertTrue((bool) strpos($this->getTable("//form[@id='adminForm']/table.2.1"), 'Alias: administrator-components-2'));
		$this->assertEquals("Components", $this->getTable("//form[@id='adminForm']/table.2.4"));
		echo "Trash and delete new article\n";
		$this->click("cb1");
		$this->click("//li[@id='toolbar-trash']/a/span");
		$this->waitForPageToLoad("30000");
		$this->select("filter_published", "label=Trashed");
		$this->waitForPageToLoad("30000");
		$this->click("checkall-toggle");
		$this->click("//li[@id='toolbar-delete']/a/span");
		$this->waitForPageToLoad("30000");
		$this->select("filter_published", "label=- Select Status -");
		$this->waitForPageToLoad("30000");

		echo "Finished testBatchCopy\n";

		$this->deleteAllVisibleCookies();
	}

	function testBatchMove()
	{
		echo "Starting testBatchMove\n";
		$this->setUp();
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->click("link=Article Manager");
		$this->waitForPageToLoad("30000");
		echo "Check initial values for articles";
		$this->assertTrue((bool) strpos($this->getTable("//form[@id='adminForm']/table.2.1"), 'Alias: archive-module'));
		$this->assertEquals("Content Modules", $this->getTable("//form[@id='adminForm']/table.2.4"));
		$this->assertTrue((bool) strpos($this->getTable("//form[@id='adminForm']/table.3.1"), 'Alias: article-categories-module'));
		$this->assertEquals("Content Modules", $this->getTable("//form[@id='adminForm']/table.3.4"));
		$this->assertTrue((bool) strpos($this->getTable("//form[@id='adminForm']/table.4.1"), 'Alias: articles-category-module'));
		$this->assertEquals("Content Modules", $this->getTable("//form[@id='adminForm']/table.4.4"));
		echo "Move Archive Module, Content Modules, Article Categories Module to Languages Category\n";
		$this->click("cb1");
		$this->click("cb2");
		$this->click("cb3");
		$this->select("batch-category-id", "label=- - - Languages");
		$this->click("//button[@type='submit' and @onclick=\"Joomla.submitbutton('article.batch');\"]");
		$this->waitForPageToLoad("30000");
		echo "Check for success message\n";
		$this->assertTrue($this->isElementPresent("//dl[@id=\"system-message\"][contains(., 'success')]"));
		echo "Check that articles movd to new category\n";
		$this->assertTrue((bool) strpos($this->getTable("//form[@id='adminForm']/table.2.1"), 'Alias: archive-module'));
		$this->assertEquals("Languages", $this->getTable("//form[@id='adminForm']/table.2.4"));
		$this->assertTrue((bool) strpos($this->getTable("//form[@id='adminForm']/table.3.1"), 'Alias: article-categories-module'));
		$this->assertEquals("Languages", $this->getTable("//form[@id='adminForm']/table.3.4"));
		$this->assertTrue((bool) strpos($this->getTable("//form[@id='adminForm']/table.4.1"), 'Alias: articles-category-module'));
		$this->assertEquals("Languages", $this->getTable("//form[@id='adminForm']/table.4.4"));
		echo "Move articles back to original category\n";
		$this->click("cb1");
		$this->click("cb2");
		$this->click("cb3");
		$this->select("batch-category-id", "label=- - - - Content Modules");
		$this->click("//button[@type='submit' and @onclick=\"Joomla.submitbutton('article.batch');\"]");
		$this->waitForPageToLoad("30000");
		echo "Check for success message\n";
		$this->assertTrue($this->isElementPresent("//dl[@id=\"system-message\"][contains(., 'success')]"));
		echo "Check that articles are back to original category\n";
		$this->assertTrue((bool) strpos($this->getTable("//form[@id='adminForm']/table.2.1"), 'Alias: archive-module'));
		$this->assertEquals("Content Modules", $this->getTable("//form[@id='adminForm']/table.2.4"));
		$this->assertTrue((bool) strpos($this->getTable("//form[@id='adminForm']/table.3.1"), 'Alias: article-categories-module'));
		$this->assertEquals("Content Modules", $this->getTable("//form[@id='adminForm']/table.3.4"));
		$this->assertTrue((bool) strpos($this->getTable("//form[@id='adminForm']/table.4.1"), 'Alias: articles-category-module'));
		$this->assertEquals("Content Modules", $this->getTable("//form[@id='adminForm']/table.4.4"));

		echo "Finished testBatchMove\n";

		$this->deleteAllVisibleCookies();

	}


}
