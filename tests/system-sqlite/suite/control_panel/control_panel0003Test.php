<?php
/**
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * that you can add, edit, and delete article from article manager
 */

require_once 'SeleniumJoomlaTestCase.php';

/**
 * @group ControlPanel
 */
class ControlPanel0003 extends SeleniumJoomlaTestCase
{


	function testCreateRemoveArticle()
	{
		$this->setUp();
		$this->gotoAdmin();
		$this->doAdminLogin();
		// Use No Editor
		$this->setEditor('no editor');

		$this->click("link=Control Panel");
		$this->waitForPageToLoad("30000");
		print("Load article manager." . "\n");
		$this->click("link=Article Manager");
		$this->waitForPageToLoad("30000");

		$this->click("//li[@id='toolbar-new']/a/span");
		$this->waitForPageToLoad("30000");

		print("Enter article title" . "\n");
		$this->type("jform_title", "Com_Content001 Test Article");

		print("Enter some text" . "\n");
		$this->type("id=jform_articletext", "<p>This is test text for an article</p>");
// 		$this->typeKeys("tinymce", "This is test text for an article");

		print("Save the article" . "\n");
		$this->click("//li[@id='toolbar-save']/a/span");
		$this->waitForPageToLoad("30000");
		print("Filter on new article" . "\n");
		$this->type("filter_search", "Com_Content001");
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");
		print("Check that article title is listed in Article Manager" . "\n");
		$this->assertEquals("Com_Content001 Test Article", $this->getText("link=Com_Content001 Test Article"));
		print("Open Article for editing" . "\n");
		$this->click("link=Com_Content001 Test Article");
		$this->waitForPageToLoad("30000");
		// test sleep command for hudson error
		sleep(3);
		print("Check that title and text are correct" . "\n");
		$this->assertTrue($this->isElementPresent("//textarea[contains(., 'This is test text')]"));
		$this->assertEquals("Com_Content001 Test Article", $this->getValue("jform_title"));
		print("Cancel edit" . "\n");
		$this->click("//li[@id='toolbar-cancel']/a/span");
		$this->waitForPageToLoad("30000");
		print("Send article to trash" . "\n");
		$this->click("link=Com_Content001 Test Article");
		$this->waitForPageToLoad("30000");
		$this->select("jform_state", "label=Trashed");
		$this->click("//option[@value='-2']");
		$this->click("//li[@id='toolbar-save']/a/span");
		$this->waitForPageToLoad("30000");
		print("Check that article is no longer shown in article manager" . "\n");
		$this->assertFalse($this->isTextPresent("Com_Content001 Test Article"));

		print("Delete article from trash" . "\n");
		$this->select("filter_published", "label=Trashed");
		$this->clickGo();
		$this->waitForPageToLoad("30000");
		$this->click("checkall-toggle");
		$this->click("//li[@id='toolbar-delete']/a/span");
		$this->waitForPageToLoad("30000");
		$this->select("filter_published", "label=- Select Status -");
		$this->clickGo();
		$this->waitForPageToLoad("30000");

		print("Clear Article manager filter" . "\n");
		$this->click("//button[@type='button']");
		$this->waitForPageToLoad("30000");

		// Set editor back to Tiny
		$this->setEditor('Tiny');

		$this->doAdminLogout();
		print("Finished control_panel0003Test.php." . "\n");
		$this->deleteAllVisibleCookies();
	}
}

