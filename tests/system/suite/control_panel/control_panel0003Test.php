<?php
/**
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
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
		$this->jPrint("Load article manager." . "\n");
		$this->click("link=Article Manager");
		$this->waitForPageToLoad("30000");

		$this->click("//div[@id='toolbar-new']/button");
		$this->waitForPageToLoad("30000");

		$this->jPrint("Enter article title" . "\n");
		$this->type("jform_title", "Com_Content001 Test Article");

		$this->jPrint("Enter some text" . "\n");
		$this->type("id=jform_articletext", "<p>This is test text for an article</p>");
// 		$this->typeKeys("tinymce", "This is test text for an article");

		$this->jPrint("Save the article" . "\n");
		$this->click("//div[@id='toolbar-save']/button");
		$this->waitForPageToLoad("30000");
		$this->jPrint("Filter on new article" . "\n");
		$this->type("filter_search", "Com_Content001");
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");
		$this->jPrint("Check that article title is listed in Article Manager" . "\n");
		$this->assertEquals("Com_Content001 Test Article", $this->getText("link=Com_Content001 Test Article"));
		$this->jPrint("Open Article for editing" . "\n");
		$this->click("link=Com_Content001 Test Article");
		$this->waitForPageToLoad("30000");
		// test sleep command for hudson error
		sleep(3);
		$this->jPrint("Check that title and text are correct" . "\n");
		$this->assertTrue($this->isElementPresent("//textarea[contains(., 'This is test text')]"));
		$this->assertEquals("Com_Content001 Test Article", $this->getValue("jform_title"));
		$this->jPrint("Cancel edit" . "\n");
		$this->click("//div[@id='toolbar-cancel']/button");
		$this->waitForPageToLoad("30000");
		$this->jPrint("Send article to trash" . "\n");
		$this->click("link=Com_Content001 Test Article");
		$this->waitForPageToLoad("30000");
		$this->select("jform_state", "label=Trashed");
		$this->click("//option[@value='-2']");
		$this->click("//div[@id='toolbar-save']/button");
		$this->waitForPageToLoad("30000");
		$this->jPrint("Check that article is no longer shown in article manager" . "\n");
		$this->assertFalse($this->isTextPresent("Com_Content001 Test Article"));

		$this->jPrint("Delete article from trash" . "\n");
		$this->select("filter_published", "label=Trashed");
		$this->clickGo();
		$this->waitForPageToLoad("30000");
		$this->click("checkall-toggle");
		$this->click("//div[@id='toolbar-delete']/button");
		$this->waitForPageToLoad("30000");
		$this->select("filter_published", "label=- Select Status -");
		$this->clickGo();
		$this->waitForPageToLoad("30000");

		$this->jPrint("Clear Article manager filter" . "\n");
		$this->click("//button[@type='button']");
		$this->waitForPageToLoad("30000");

		// Set editor back to Tiny
		$this->setEditor('Tiny');

		$this->doAdminLogout();
		$this->jPrint("Finished control_panel0003Test.php." . "\n");
		$this->deleteAllVisibleCookies();
	}
}

