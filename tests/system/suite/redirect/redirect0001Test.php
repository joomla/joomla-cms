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
class Redirect0001Test extends SeleniumJoomlaTestCase
{
	function testCreateRedirect()
	{
		$this->setUp();
		$this->jPrint ("Starting testCreateRedirect.\n");
		$this->gotoAdmin();
		$this->doAdminLogin();

		$this->jPrint ("Navigate to redirect and create new redirect.\n");
		$this->click("link=Redirect");
		$this->waitForPageToLoad("30000");
		$this->click("//div[@id='toolbar-new']/button");
		$this->waitForPageToLoad("30000");
		$this->jPrint ("Set redirect from bad url to Getting Started\n");
		$badLink = $this->cfg->host . $this->cfg->path . 'index.php/using-joomla/extensions/components/content-component/single-articlexxx';
		$goodLink = $this->cfg->host . $this->cfg->path . 'index.php/getting-started';
		$this->type("jform_old_url", $badLink);
		$this->type("jform_new_url", $goodLink);
		$this->click("//div[@id='toolbar-save']/button");
		$this->waitForPageToLoad("30000");

		$this->jPrint("Goto plugin manager and enable redirect plugin\n");
		$this->click("link=Plug-in Manager");
		$this->waitForPageToLoad("30000");

		$this->type("filter_search", "redirect");
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");
		$this->click("cb0");
		$this->click("//div[@id='toolbar-publish']/button");
		$this->waitForPageToLoad("30000");
		$this->doAdminLogout();

		$this->jPrint ("Go to front end and try bad url \n");
		$this->gotoSite();
		$this->open($badLink);
		$this->waitForPageToLoad("30000");
		$this->jPrint ("Check that we get the Getting Started page\n");
		$this->assertStringEndsWith('index.php/getting-started', $this->getLocation(), 'URL should be Getting Started');
		$this->assertTrue($this->isElementPresent("//div[@class='page-header']/h2[contains(., 'Getting Started')]"));

		$this->jPrint ("Go to back end and delete the redirect\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->click("link=Redirect");
		$this->waitForPageToLoad("30000");
		$this->click("checkall-toggle");
		$this->click("//div[@id='toolbar-trash']/button");
		$this->waitForPageToLoad("30000");
		$this->select("filter_state", "label=Trashed");
		$this->waitForPageToLoad("30000");
		$this->click("checkall-toggle");
		$this->click("//div[@id='toolbar-delete']/button");
		$this->waitForPageToLoad("30000");
		$this->select("filter_state", "label=- Select Status -");
		$this->waitForPageToLoad("30000");
		$this->doAdminLogout();
		$this->deleteAllVisibleCookies();
	}

	function testDeleteRedirect()
	{
		$this->jPrint ("Starting testDeleteRedirect.\n");
		$this->deleteAllVisibleCookies();
		$this->gotoAdmin();
		$this->doAdminLogin();

		$badLink = $this->cfg->host . $this->cfg->path . 'index.php/using-joomla/extensions/components/content-component/single-articlexxx';
		$goodLink = $this->cfg->host . $this->cfg->path . 'index.php/getting-started';
		$this->jPrint ("Go to front end and try bad URL now without redirect\n");
		$this->gotoSite();
		$this->open($badLink, "true"); // need true to allow selenium to load error page
		$this->assertTrue($this->isElementPresent("//h1[contains(text(),'page cannot be found')]"));

		$this->jPrint ("Go to back end and check that this link has been added as a redirect\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->click("link=Redirect");
		$this->waitForPageToLoad("30000");
		$this->jPrint ("Check that bad link is there\n");
		$this->assertTrue($this->isElementPresent("//td/a[@title='" . $badLink . "']"));

		$this->jPrint ("Delete redirect item and log out\n");
		$this->click("checkall-toggle");
		$this->click("//div[@id='toolbar-trash']/button");
		$this->waitForPageToLoad("30000");
		$this->select("filter_state", "label=Trashed");
		$this->waitForPageToLoad("30000");
		$this->click("checkall-toggle");
		$this->click("//div[@id='toolbar-delete']/button");
		$this->waitForPageToLoad("30000");
		$this->select("filter_state", "label=- Select Status -");
		$this->waitForPageToLoad("30000");

		$this->jPrint("Goto plugin manager and disable redirect plugin\n");
		$this->click("link=Plug-in Manager");
		$this->waitForPageToLoad("30000");

		$this->type("filter_search", "redirect");
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");
		$this->click("cb0");
		$this->click("//div[@id='toolbar-unpublish']/button");
		$this->waitForPageToLoad("30000");
		$this->doAdminLogout();
	}

}
