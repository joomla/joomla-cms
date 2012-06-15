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
class Redirect0001Test extends SeleniumJoomlaTestCase
{
	function testCreateRedirect()
	{
		$this->setUp();
		echo "Starting testCreateRedirect.\n";
		$this->gotoAdmin();
		$this->doAdminLogin();

		echo "Navigate to redirect and create new redirect.\n";
		$this->click("link=Redirect");
		$this->waitForPageToLoad("30000");
		$this->click("//li[@id='toolbar-new']/a/span");
		$this->waitForPageToLoad("30000");
		echo "Set redirect from bad url to Getting Started\n";
		$badLink = $this->cfg->host . $this->cfg->path . 'index.php/using-joomla/extensions/components/content-component/single-articlexxx';
		$goodLink = $this->cfg->host . $this->cfg->path . 'index.php/getting-started';
		$this->type("jform_old_url", $badLink);
		$this->type("jform_new_url", $goodLink);
		$this->click("//li[@id='toolbar-save']/a/span");
		$this->waitForPageToLoad("30000");
		$this->doAdminLogout();

		echo "Go to front end and try bad url \n";
		$this->gotoSite();
		$this->open($badLink);
		$this->waitForPageToLoad("30000");
		echo "Check that we get the Getting Started page\n";
		$this->assertTrue($this->isElementPresent("//div[@id='main']//h2[contains(., 'Getting Started')]"));

		echo "Go to back end and delete the redirect\n";
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->click("link=Redirect");
		$this->waitForPageToLoad("30000");
		$this->click("checkall-toggle");
		$this->click("//li[@id='toolbar-trash']/a/span");
		$this->waitForPageToLoad("30000");
		$this->select("filter_state", "label=Trashed");
		$this->waitForPageToLoad("30000");
		$this->click("checkall-toggle");
		$this->click("//li[@id='toolbar-delete']/a/span");
		$this->waitForPageToLoad("30000");
		$this->select("filter_state", "label=- Select Status -");
		$this->waitForPageToLoad("30000");
		$this->doAdminLogout();

		echo "Go to front end and try bad URL now without redirect";
		$this->gotoSite();
		$this->open($badLink, "true"); // need true to allow selenium to load error page
		$this->assertTrue($this->isElementPresent("//h2[contains(text(),'error')]"));

		echo "Go to back end and check that this link has been added as a redirect\n";
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->click("link=Redirect");
		$this->waitForPageToLoad("30000");
		echo "Check that bad link is there\n";
		$this->assertTrue($this->isElementPresent("//td/a[@title='" . $badLink . "']"));

		echo "Delete redirect item and log out\n";
		$this->click("checkall-toggle");
		$this->click("//li[@id='toolbar-trash']/a/span");
		$this->waitForPageToLoad("30000");
		$this->select("filter_state", "label=Trashed");
		$this->waitForPageToLoad("30000");
		$this->click("checkall-toggle");
		$this->click("//li[@id='toolbar-delete']/a/span");
		$this->waitForPageToLoad("30000");
		$this->select("filter_state", "label=- Select Status -");
		$this->waitForPageToLoad("30000");
		$this->doAdminLogout();
	}

}
