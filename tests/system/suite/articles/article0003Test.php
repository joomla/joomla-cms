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
class Article0003 extends SeleniumJoomlaTestCase
{
	function testArchivedState()
	{
		$this->setUp();
		$this->jPrint ("Starting testArchivedState.\n");
		$this->jPrint ("Go to front end and check Archived Articles.\n");
		$link = $this->cfg->path . 'index.php/using-joomla/extensions/components/content-component/archived-articles';
		$this->gotoSite();
		$this->open($link);
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("link=exact:What's New in 1.5?"));
		$this->jPrint ("Go to back end and change Joomla category to Archived state.\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->changeState('Joomla!', 'Article Manager', 'Category', 'archive');
		$this->doAdminLogout();
		$this->jPrint ("Go to front end and check that Latest Users and Content are now archived.\n");
		$this->gotoSite();
		$this->open($link);
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("link=Latest Users Module"));
		$this->assertTrue($this->isElementPresent("link=Content"));
		$this->jPrint ("Go to back end and change Joomla category to Published state" . "\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->changeState('Joomla!', 'Article Manager', 'Category', 'publish');
		$this->doAdminLogout();
		$this->jPrint ("Go to front end and check Archived layout\n");
		$this->gotoSite();
		$this->open($link);
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("link=exact:What's New in 1.5?"));
		$this->assertFalse($this->isElementPresent("link=Latest Users Module"));
		$this->jPrint ("Go to back end and change Beginners article to Archived state\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->changeState('Beginners', 'Article Manager', 'Article', 'archive');
		$this->doAdminLogout();
		$this->jPrint ("Go to Archived layout and check that Beginners article now shows\n");
		$this->gotoSite();
		$this->open($link);
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("link=Beginners"));
		$this->jPrint ("Go to back end and set Beginners back to Published\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->changeState('Beginners', 'Article Manager', 'Article', 'publish');
		$this->doAdminLogout();
		$this->jPrint ("Go to front end and make sure Beginners no longer shows on Archived layout\n");
		$this->gotoSite();
		$this->open($link);
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("link=exact:What's New in 1.5?"));
		$this->assertFalse($this->isElementPresent("link=Beginners"));

		$this->jPrint ("Finished testArchivedState\n");
		$this->deleteAllVisibleCookies();
	}

	function testSingleArticleState()
	{
		$this->jPrint ("Starting testSingleArticleState\n");
		$this->jPrint ("Go to Category Manager and set Extensions category to Unpublished\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->changeState('Extensions', 'Article Manager', 'Category', 'unpublish');
		$this->doAdminLogout();
		$this->jPrint ("Go to Site -> Content Components article and check that you get not found notice\n");
		$link = $this->cfg->path . 'index.php/using-joomla/extensions/components/content-component';
		$this->gotoSite();
		// Need 'true' in second argument if you will get a 404 error. Otherwise, test fails.
		$this->open($link, 'true');
		$this->assertTrue($this->isElementPresent("//div[@id='content'][contains(., 'requested page cannot be found')]"));
		$this->jPrint ("Log in to Site and check that you now can see Content Component article\n");
		$this->gotoSite();
		$this->doFrontEndLogin();
		$this->gotoSite();
		$this->open($link);
		$this->assertTrue($this->isElementPresent("link=Content"));
		$this->assertTrue($this->isElementPresent("//li[@class='edit-icon']"));
		$this->jPrint ("Log out of site and into back end\n");
		$this->jPrint ("Go to Category Manager and set Extensions back to published\n");
		$this->gotoSite();
		$this->doFrontEndLogout();
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->changeState('Extensions', 'Article Manager', 'Category', 'publish');
		$this->jPrint ("Change Getting Started article to Unpublished\n");
		$this->changeState('Getting Started', 'Article Manager', 'Article', 'unpublish');
		$this->doAdminLogout();
		$this->jPrint ("Go back to site and check that you get notice on Getting Started page\n");
		$this->gotoSite();
		$this->click("link=Getting Started");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("//div[@id='content'][contains(., 'requested page cannot be found')]"));
		$this->jPrint ("Log in to Site and check that Getting Started is now shown and editable\n");
		$this->gotoSite();
		$this->doFrontEndLogin();
		$this->click("link=Getting Started");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Getting Started"));
		$this->assertTrue($this->isElementPresent("//li[@class='edit-icon']"));
		$this->jPrint ("Log out of Site\n");
		$this->gotoSite();
		$this->doFrontEndLogout();
		$this->jPrint ("Go to back end and change state of Getting Started to Published\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->changeState('Getting Started', 'Article Manager', 'Article', 'publish');
		$this->jPrint ("Check that Getting Started is again visible\n");
		$this->doAdminLogout();
		$this->gotoSite();
		$this->click("link=Getting Started");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Getting Started"));
		$this->jPrint ("Now check that it also works with Archived state\n");
		$this->jPrint ("Goto back end and change article to Archived state\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->changeState('Getting Started', 'Article Manager', 'Article', 'archive');
		$this->doAdminLogout();
		$this->jPrint ("Check that article is still visible\n");
		$this->gotoSite();
		$this->click("link=Getting Started");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Getting Started"));
		$this->jPrint ("Change Category to Archived and check \n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->changeState('Sample Data-Articles', 'Article Manager', 'Category', 'archive');
		$this->gotoSite();
		$this->jPrint ("Check that article is still visible\n");
		$this->click("link=Getting Started");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Getting Started"));
		$this->jPrint ("Change Category and Article state back to published\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->changeState('Sample Data-Articles', 'Article Manager', 'Category', 'publish');
		$this->changeState('Getting Started', 'Article Manager', 'Article', 'publish');
		$this->doAdminLogout();
		$this->jPrint ("finished testSingleArticleState\n");
		$this->deleteAllVisibleCookies();
	}

	function testFeaturedState()
	{
		$this->jPrint ("Starting testFeaturedState\n");
		$this->jPrint ("Change Sample Data-Articles category to Unpublished state\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->click("link=Category Manager");
		$this->waitForPageToLoad("30000");
		$this->togglePublished('Sample Data-Articles', 'Category');
		$this->doAdminLogout();
		$this->jPrint ("Check that no articles show on Home page\n");
		$this->gotoSite();
		$this->doFrontEndLogout();
		$this->assertFalse($this->isElementPresent("link=Beginners"));
		$this->jPrint ("Log in to Site and check that articles now show as unpublished and editable\n");
		$this->gotoSite();
		$this->doFrontEndLogin();
		$this->assertTrue($this->isElementPresent("link=Joomla!"));
		$this->assertTrue($this->isElementPresent("link=Professionals"));
		$this->assertTrue($this->isElementPresent("//div[@class='system-unpublished']/h2[contains(., 'Joomla!')]"));
		$this->assertTrue($this->isElementPresent("//li[@class='edit-icon']"));
		$this->doFrontEndLogout();
		$this->gotoAdmin();
		$this->doAdminLogin();

		$this->jPrint ("Change Sample Data-Articles category to Archived state\n");
		$this->changeState('Sample Data-Articles', 'Article Manager', 'Category', 'archive');
		$this->doAdminLogout();
		$this->jPrint ("Check that no articles show on Home page\n");
		$this->gotoSite();
		$this->assertFalse($this->isElementPresent("link=Beginners"));
		$this->jPrint ("Log in to Site and check that articles now show as unpublished and editable\n");
		$this->gotoSite();
		$this->doFrontEndLogin();
		$this->assertTrue($this->isElementPresent("link=Joomla!"));
		$this->assertTrue($this->isElementPresent("link=Professionals"));
		$this->assertTrue($this->isElementPresent("//div[@class='system-unpublished']/h2[contains(., 'Joomla!')]"));
		$this->assertTrue($this->isElementPresent("//li[@class='edit-icon']"));
		$this->doFrontEndLogout();
		$this->gotoAdmin();
		$this->doAdminLogin();

		$this->jPrint ("Publish Sample Data-Articles Category and Unpublish Beginners article\n");
		$this->changeState('Sample Data-Articles', 'Article Manager', 'Category', 'publish');
		$this->changeState('Beginners', 'Article Manager', 'Article', 'unpublish');
		$this->doAdminLogout();
		$this->jPrint ("Check that Beginners is not shown but that other articles are shown\n");
		$this->gotoSite();
		$this->assertTrue($this->isElementPresent("link=Joomla!"));
		$this->assertFalse($this->isElementPresent("link=Beginners"));
		$this->jPrint ("Log into Site and Check that Beginners is shown as unpublished\n");
		$this->doFrontEndLogin();
		$this->assertTrue($this->isElementPresent("//div[@class='system-unpublished']/h2[contains(., 'Beginners')]"));
		$this->assertTrue($this->isElementPresent("//div[@class='system-unpublished']//a[contains(text(),'Edit')]"));
		$this->doFrontEndLogout();

		$this->jPrint ("Set Beginners article to Archived state\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->changeState('Beginners', 'Article Manager', 'Article', 'archive');
		$this->doAdminLogout();
		$this->jPrint ("Check that Beginners is not shown but that other articles are shown\n");
		$this->gotoSite();
		$this->assertTrue($this->isElementPresent("link=Joomla!"));
		$this->assertFalse($this->isElementPresent("link=Beginners"));
		$this->jPrint ("Log into Site and Check that Beginners is shown as published\n");
		$this->doFrontEndLogin();
		$this->assertTrue($this->isElementPresent("//div[@class='blog-featured']//a[contains(text(),'Edit')]"));
		$this->doFrontEndLogout();

		$this->jPrint ("Set Beginners article to Trashed state\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->changeState('Beginners', 'Article Manager', 'Article', 'trash');
		$this->doAdminLogout();
		$this->jPrint ("Check that Beginners is not shown but that other articles are shown\n");
		$this->gotoSite();
		$this->assertTrue($this->isElementPresent("link=Joomla!"));
		$this->assertFalse($this->isElementPresent("link=Beginners"));
		$this->jPrint ("Log into Site and Check that Beginners is shown as published\n");
		$this->doFrontEndLogin();
		$this->assertTrue($this->isElementPresent("//div[@class='blog-featured']//a[contains(text(),'Edit')]"));
		$this->doFrontEndLogout();

		$this->jPrint ("Set Beginners back to Published state\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->changeState('Beginners', 'Article Manager', 'Article', 'publish');
		$this->doAdminLogout();
		$this->jPrint ("Check that Beginners is shown\n");
		$this->gotoSite();
		$this->doFrontEndLogout();
		$this->assertTrue($this->isElementPresent("link=Joomla!"));
		$this->assertTrue($this->isElementPresent("link=Beginners"));
		$this->jPrint ("Finished testFeaturedState\n");

		$this->deleteAllVisibleCookies();
	}

	function testAllCategoriesState()
	{
		$this->jPrint ("Start testAllCategoriesState\n");
		$this->jPrint ("Set Park Site category to Unpublished\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->changeState('Park Site', 'Article Manager', 'Category', 'unpublish');
		$this->doAdminLogout();
		$link = $this->cfg->path . 'index.php/using-joomla/extensions/components/content-component/article-categories';
		$this->gotoSite();
		$this->open($link);
		$this->waitForPageToLoad("30000");
		$this->jPrint ("Check that Park Site, Park Blog, and Photo Gallery don't show in Categories layout\n");
		$this->assertFalse($this->isElementPresent("link=Park Site"));
		$this->assertFalse($this->isElementPresent("link=Park Blog"));
		$this->jPrint ("Check that Joomla!, Extensions, and Fruit Shop Site still show\n");
		$this->assertTrue($this->isElementPresent("link=Joomla!"));
		$this->assertTrue($this->isElementPresent("link=Extensions"));
		$this->assertTrue($this->isElementPresent("link=Fruit Shop Site"));
		$this->jPrint ("Check that Unpublished categories don't show when logged into front end\n");
		$this->gotoSite();
		$this->doFrontEndLogin();
		$this->gotoSite();
		$this->open($link);
		$this->waitForPageToLoad("30000");
		$this->jPrint ("Check that Park Site, Park Blog, and Photo Gallery don't show in Categories layout\n");
		$this->assertFalse($this->isElementPresent("link=Park Site"));
		$this->assertFalse($this->isElementPresent("link=Park Blog"));
		$this->jPrint ("Check that Joomla!, Extensions, and Fruit Shop Site still show\n");
		$this->assertTrue($this->isElementPresent("link=Joomla!"));
		$this->assertTrue($this->isElementPresent("link=Extensions"));
		$this->assertTrue($this->isElementPresent("link=Fruit Shop Site"));
		$this->jPrint ("Set Park Site category to Archived and repeat tests\n");
		$this->gotoSite();
		$this->doFrontEndLogout();
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->changeState('Park Site', 'Article Manager', 'Category', 'archive');
		$this->doAdminLogout();
		$this->gotoSite();
		$this->gotoSite();
		$this->open($link);
		$this->waitForPageToLoad("30000");
		$this->jPrint ("Check that Park Site, Park Blog, and Photo Gallery don't show in Categories layout\n");
		$this->assertFalse($this->isElementPresent("link=Park Site"));
		$this->assertFalse($this->isElementPresent("link=Park Blog"));
		$this->jPrint ("Check that Joomla!, Extensions, and Fruit Shop Site still show\n");
		$this->assertTrue($this->isElementPresent("link=Joomla!"));
		$this->assertTrue($this->isElementPresent("link=Extensions"));
		$this->assertTrue($this->isElementPresent("link=Fruit Shop Site"));

		$this->jPrint ("Change back to Published\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->changeState('Park Site', 'Article Manager', 'Category', 'publish');
		$this->doAdminLogout();
		$this->jPrint ("Finished testAllCategoriesState\n");

		$this->deleteAllVisibleCookies();
	}

	function testCategoryBlogState()
	{
		$this->jPrint ("Starting testCategoryBlogState\n");
		$link = $this->cfg->path . 'index.php/using-joomla/extensions/components/content-component/article-category-blog';
		$this->gotoSite();
		$this->open($link);
		$this->waitForPageToLoad("30000");
		$this->jPrint ("Check initial conditions Second Blog Show and First Blog Post\n");
		$this->assertTrue($this->isElementPresent("//div[@class='blog']//h2/a[contains(., 'Second Blog Post')]"));

		$this->jPrint ("Unpublish Park Site category\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->changeState('Park Site', 'Article Manager', 'Category', 'unpublish');
		$this->doAdminLogout();
		$this->gotoSite();
		// Need to add 'true' arguement for open() when you expect a 404 error (otherwise, the test aborts)
		$this->open($link, 'true');
		$this->waitForPageToLoad("30000");
		$this->jPrint ("Check that Category not found message shows\n");
		$this->assertTrue($this->isTextPresent("Category not found"));
		$this->jPrint ("Log in to site and check that Category not found message still shows\n");
		$this->gotoSite();
		$this->doFrontEndLogin();
		$this->open($link, 'true');
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Category not found"));
		$this->gotoSite();
		$this->doFrontEndLogout();

		$this->jPrint ("Change Park Site category to archived status and check that Category not found still shows \n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->changeState('Park Site', 'Article Manager', 'Category', 'archive');
		$this->gotoSite();
		$this->open($link, 'true');
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Category not found"));

		$this->jPrint ("Change Park Blog category back to published\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->changeState('Park Site', 'Article Manager', 'Category', 'publish');

		$this->jPrint ("Change First Blog Post to Unpublished and check that it doesn't show\n");
		$this->changeState('First Blog', 'Article Manager', 'Article', 'unpublish');
		$this->doAdminLogout();
		$this->gotoSite();
		$this->open($link);
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("link=Second Blog Post"));
		$this->assertFalse($this->isElementPresent("link=First Blog Post"));

		$this->jPrint ("Log into site and check that First Blog Post shows as Unpublished\n");
		$this->gotoSite();
		$this->doFrontEndLogin();
		$this->open($link);
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("link=Second Blog Post"));
		$this->assertTrue($this->isElementPresent("link=First Blog Post"));
		$this->assertTrue($this->isElementPresent("//div[contains(@class, 'system-unpublished')]//h2[contains(., 'First Blog')]"));

		$this->jPrint ("Change First Blog state to Archived\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->changeState('First Blog', 'Article Manager', 'Article', 'archive');

		$this->jPrint ("Check that First Blog Post now shows as published when logged in\n");
		$this->doAdminLogout();
		$this->gotoSite();
		$this->doFrontEndLogin();
		$this->open($link);
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isElementPresent("link=Second Blog Post"));
		$this->assertTrue($this->isElementPresent("link=First Blog Post"));
		$this->assertFalse($this->isElementPresent("//div[@class='system-unpublished']//h2[contains(., 'First Blog')]"));

		$this->jPrint ("Log out of Front End\n");
		$this->gotoSite();
		$this->doFrontEndLogout();

		$this->jPrint ("Log into back end and change First Blog Post back to published\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->changeState('First Blog', 'Article Manager', 'Article', 'publish');

		$this->jPrint ("Finished testCategoryBlogState\n");

		$this->deleteAllVisibleCookies();
	}

	function testCategoryListState()
	{
		$this->jPrint ("Starting testCategoryListState\n");
		$link = $this->cfg->path . 'index.php/using-joomla/extensions/components/content-component/article-category-list';
		$this->gotoSite();
		$this->open($link);
		$this->waitForPageToLoad("30000");
		$this->jPrint ("Check initial conditions for list\n");
		$this->assertStringEndsWith("Beginners", $this->getText("//tbody/tr[1]/td/a"));
		$this->assertStringEndsWith("Getting Help", $this->getText("//tbody/tr[2]/td/a"));

		$this->jPrint ("Unpublish Joomla! category\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->changeState('Joomla!', 'Article Manager', 'Category', 'unpublish');
		$this->doAdminLogout();
		$this->gotoSite();
		$this->open($link, 'true');
		$this->waitForPageToLoad("30000");
		$this->jPrint ("Check that Category not found message shows\n");
		$this->assertTrue($this->isTextPresent("Category not found"));
		$this->jPrint ("Log in to site and check that Category not found message still shows\n");
		$this->gotoSite();
		$this->doFrontEndLogin();
		$this->open($link, 'true');
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Category not found"));
		$this->gotoSite();
		$this->doFrontEndLogout();

		$this->jPrint ("Change Joomla! category to archived status and check that Category not found still shows \n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->changeState('Joomla!', 'Article Manager', 'Category', 'archive');
		$this->gotoSite();
		$this->open($link, 'true');
		$this->waitForPageToLoad("30000");
		$this->jPrint ("Check that Category not found message shows\n");
		$this->assertTrue($this->isTextPresent("Category not found"));

		$this->jPrint ("Change Joomla! category back to published\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->changeState('Joomla!', 'Article Manager', 'Category', 'publish');

		$this->jPrint ("Change Getting Help article to Unpublished and check that it doesn't show\n");
		$this->changeState('Getting Help', 'Article Manager', 'Article', 'unpublish');
		$this->doAdminLogout();
		$this->gotoSite();
		$this->open($link);
		$this->waitForPageToLoad("30000");
		$this->assertStringEndsWith("Beginners", $this->getText("//tbody/tr[1]/td/a"));
		$this->assertStringEndsWith("Getting Started", $this->getText("//tbody/tr[2]/td/a"));

		$this->jPrint ("Log into site and check that Getting Help shows as Unpublished\n");
		$this->gotoSite();
		$this->doFrontEndLogin();
		$this->open($link);
		$this->waitForPageToLoad("30000");
		$this->assertStringEndsWith("Beginners", $this->getText("//tbody/tr[1]/td/a"));

		$this->assertStringEndsWith("Getting Help", $this->getText("//tbody/tr[2]/td/a"));
		$this->assertTrue($this->isElementPresent("//div[@class='category-list']//a[contains(text(), 'Getting Help')]/../span[contains(text(), 'Unpublished')]"));

		$this->jPrint ("Change Getting Help state to Archived\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->changeState('Getting Help', 'Article Manager', 'Article', 'archive');

		$this->jPrint ("Check that Getting Help now shows as published when logged in\n");
		$this->doAdminLogout();
		$this->gotoSite();
		$this->doFrontEndLogin();
		$this->open($link);
		$this->waitForPageToLoad("30000");
		$this->assertStringEndsWith("Beginners", $this->getText("//tbody/tr[1]/td/a"));

		$this->assertStringEndsWith("Getting Help", $this->getText("//tbody/tr[2]/td/a"));

		$this->jPrint ("Log out of Front End\n");
		$this->gotoSite();
		$this->doFrontEndLogout();

		$this->jPrint ("Log into back end and change Getting Help back to published\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->changeState('Getting Help', 'Article Manager', 'Article', 'publish');
		$this->doAdminLogout();

		$this->jPrint ("Finished testCategoryListState\n");

		$this->deleteAllVisibleCookies();
	}

}
