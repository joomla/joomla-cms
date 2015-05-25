<?php
/**
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * checks that all menu choices are shown in back end
 */

require_once 'SeleniumJoomlaTestCase.php';

/**
 *
 */
class Cache0001Test extends SeleniumJoomlaTestCase
{
	function testContentCache()
	{
		$this->setUp();
		$this->jPrint ("Starting testContentCache.\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->jPrint ("Set caching to progressive.\n");
		$this->setCache('on-full');

		$this->jPrint ("Test Single article.\n");
		$this->jPrint ("Show the Australian Parks article layout in front end\n");
		$link = $this->cfg->path . 'index.php/using-joomla/extensions/components/content-component/single-article';
		$this->gotoSite();
		$this->open($link, 'true');
		$this->assertTrue($this->isElementPresent("link=Australian Parks"));
		$this->jPrint ("Unpublish Australian Parks article and check that it no longer shows\n");
		$this->changeState('Australian Parks', 'Article Manager', 'Article', 'unpublish');
		$this->gotoSite();
		$this->open($link, 'true');
		$this->assertTrue($this->isElementPresent("//div[@id='content'][contains(., 'requested page cannot be found')]"));
		$this->jPrint ("Publish Australian Parks article and check that it again shows\n");
		$this->gotoAdmin();
		$this->changeState('Australian Parks', 'Article Manager', 'Article', 'publish');
		$this->gotoSite();
		$this->open($link, 'true');
		$this->assertTrue($this->isElementPresent("link=Australian Parks"));

		$this->jPrint ("Test Article Categories \n");
		$link = $this->cfg->path . 'index.php/using-joomla/extensions/components/content-component/article-categories';
		$this->open($link, 'true');
		$this->assertTrue($this->isElementPresent("link=Content Modules"));
		$this->jPrint ("Unpublish Content Modules and check that it is no longer shown\n");
		$this->gotoAdmin();
		$this->changeState('Content Modules', 'Article Manager', 'Category', 'unpublish');
		$this->gotoSite();
		$this->open($link, 'true');
		$this->assertFalse($this->isElementPresent("link=Content Modules"));
		$this->jPrint ("Republish Content Modules and make sure it is shown\n");
		$this->changeState('Content Modules', 'Article Manager', 'Category', 'publish');
		$this->gotoSite();
		$this->open($link, 'true');
		$this->assertTrue($this->isElementPresent("link=Content Modules"));


		$this->jPrint ("Test Article Category List\n");
		$link = $this->cfg->path . 'index.php/using-joomla/extensions/components/content-component/article-category-blog';
		$this->open($link, 'true');
		$this->assertTrue($this->isElementPresent("link=*First Blog Post*"));
		$this->jPrint ("Change First Blog Post to different category and check that it is no longer shown\n");
		$this->gotoAdmin();
		$this->changeCategory('First Blog Post', 'Article Manager', 'Park Site');
		$this->gotoSite();
		$this->open($link, 'true');
		$this->assertFalse($this->isElementPresent("link=*First Blog Post*"));
		$this->jPrint ("Change First Blog Post back to Park Blog and make sure it is shown\n");
		$this->changeCategory('First Blog Post', 'Article Manager', 'Park Blog');
		$this->gotoSite();
		$this->open($link, 'true');
		$this->assertTrue($this->isElementPresent("link=*First Blog Post*"));

		$this->jPrint ("Test Article Category List\n");
		$link = $this->cfg->path . 'index.php/using-joomla/extensions/components/content-component/article-category-list';
		$this->open($link, 'true');
		$this->assertTrue($this->isElementPresent("//td[@class='list-title'][contains(.,'Professionals')]"));
		$this->jPrint ("Change Professionals to different category and check that it is no longer shown\n");
		$this->gotoAdmin();
		$this->changeCategory('Professionals', 'Article Manager', 'Uncategorised');
		$this->gotoSite();
		$this->open($link, 'true');
		$this->assertFalse($this->isElementPresent("//td[@class='list-title'][contains(.,'Professionals')]"));
		$this->jPrint ("Change Professionals back to Joomla! and make sure it is shown\n");
		$this->changeCategory('Professionals', 'Article Manager', 'Joomla!');
		$this->gotoSite();
		$this->open($link, 'true');
		$this->assertTrue($this->isElementPresent("//td[@class='list-title'][contains(.,'Professionals')]"));

		$this->jPrint ("Test Article Featured\n");
		$link = $this->cfg->path . 'index.php/using-joomla/extensions/components/content-component/featured-articles';
		$this->open($link, 'true');
		$this->assertTrue($this->isElementPresent("//div[@class='blog-featured']//h2[contains(., 'Beginners')]"));

		$this->jPrint ("Set Beginners to not be featured and check that it is no longer shown\n");
		$this->gotoAdmin();
		$this->click("link=Article Manager");
		$this->waitForPageToLoad("30000");
		$this->type("filter_search", "Beginners");
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");
		$this->jPrint ("Open Beginners for editing and change Featured to no\n");
		$this->click("link=Beginners");
		$this->waitForPageToLoad("30000");
		$this->select("jform_featured", "label=No");
		$this->click("//div[@id='toolbar-save']/button");
		$this->waitForPageToLoad("30000");
		$this->gotoSite();
		$this->open($link, 'true');
		$this->assertFalse($this->isElementPresent("//div[@class='blog-featured']//h2[contains(., 'Beginners')]"));
		$this->jPrint ("Set it back to Featured and make sure it is shown\n");
		$this->gotoAdmin();
		$this->click("link=Article Manager");
		$this->waitForPageToLoad("30000");
		$this->type("filter_search", "Beginners");
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");
		$this->jPrint ("Open Beginners for editing and change Featured to yes\n");
		$this->click("link=Beginners");
		$this->waitForPageToLoad("30000");
		$this->select("jform_featured", "label=Yes");
		$this->click("//div[@id='toolbar-save']/button");
		$this->waitForPageToLoad("30000");
		$this->gotoSite();
		$this->open($link, 'true');
		$this->assertTrue($this->isElementPresent("//div[@class='blog-featured']//h2[contains(., 'Beginners')]"));

		$this->jPrint ("Test Archived Articles \n");
		$link = $this->cfg->path . 'index.php/using-joomla/extensions/components/content-component/archived-articles';
		$this->open($link, 'true');
		$this->assertTrue($this->isElementPresent("//div[@id='archive-items']//h2/a[contains(., 'New in 1.5')]"));
		$this->assertFalse($this->isElementPresent("//div[@id='archive-items']//h2/a[contains(., 'Australian Parks')]"));
		$this->jPrint ("Archive Australian Parks article and check that it is now shown on archive layout\n");
		$this->gotoAdmin();
		$this->changeState('Australian Parks', 'Article Manager', 'Article', 'archive');
		$this->gotoSite();
		$this->open($link, 'true');
		$this->assertTrue($this->isElementPresent("//div[@id='archive-items']//h2/a[contains(., \"What's New\")]"));
		$this->assertTrue($this->isElementPresent("//div[@id='archive-items']//h2/a[contains(., 'Australian Parks')]"));
		$this->jPrint ("Republish Australian Parks article and make sure it is no longer shown on archive layout\n");
		$this->changeState('Australian Parks', 'Article Manager', 'Article', 'publish');
		$this->gotoSite();
		$this->open($link, 'true');
		$this->assertTrue($this->isElementPresent("//div[@id='archive-items']//h2/a[contains(., \"What's New\")]"));
		$this->assertFalse($this->isElementPresent("//div[@id='archive-items']//h2/a[contains(., 'Australian Parks')]"));

		$this->gotoAdmin();
		$this->doAdminLogout();
	}

	function testNewsFeedCache()
	{
		$this->setUp();
		$this->jPrint ("Starting testNewsfeedCache.\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->jPrint ("Set caching to progressive.\n");
		$this->setCache('on-full');

		$this->jPrint ("Check caching for Newsfeed Categories\n");
		$link = $this->cfg->path . 'index.php/using-joomla/extensions/components/news-feeds-component/new-feed-categories';
		$this->open($link, 'true');
		$this->assertTrue($this->isElementPresent("link=Sample Data-Newsfeeds"));
		$this->jPrint ("Unpublish Sample Data-Newsfeeds and check that it is not shown\n");
		$this->gotoAdmin();
		$this->changeState('Sample Data-Newsfeeds', 'Newsfeeds', 'Category', 'unpublish');
		$this->gotoSite();
		$this->open($link, 'true');
		$this->assertFalse($this->isElementPresent("link=Sample Data-Newsfeeds"));
		$this->jPrint ("Publish Sample Data-Newsfeeds and check that it is now shown\n");
		$this->changeState('Sample Data-Newsfeeds', 'Newsfeeds', 'Category', 'publish');
		$this->gotoSite();
		$this->open($link, 'true');
		$this->assertTrue($this->isElementPresent("link=Sample Data-Newsfeeds"));

		$this->jPrint ("Check caching for Single Newsfeed\n");
		$link = $this->cfg->path . 'index.php/using-joomla/extensions/components/news-feeds-component/single-news-feed';
		$this->open($link, 'true');
		$this->assertTrue($this->isElementPresent("link=Joomla! Connect"));
		$this->jPrint ("Unpublish Newsfeed and check that it is not shown\n");
		$this->gotoAdmin();
		$this->changeState('Joomla! Connect', 'Newsfeeds', 'Newsfeeds', 'unpublish');
		$this->gotoSite();
		$this->open($link, 'true');
		$this->assertTrue($this->isElementPresent("//div[@id='content'][contains(., 'requested page cannot be found')]"));
		$this->jPrint ("Publish JoomlaConnect and check that it is now shown\n");
		$this->changeState('Joomla! Connect', 'Newsfeeds', 'Newsfeeds', 'publish');
		$this->gotoSite();
		$this->open($link, 'true');
		$this->assertTrue($this->isElementPresent("link=Joomla! Connect"));

		$this->jPrint ("Test Newsfeed Category List\n");
		$link = $this->cfg->path . 'index.php/using-joomla/extensions/components/news-feeds-component/news-feed-category';
		$this->open($link, 'true');
		$this->assertTrue($this->isElementPresent("//div[@class='list-title'][contains(.,'Joomla! Announcements')]"));
		$this->assertTrue($this->isElementPresent("//div[@class='list-title'][contains(.,'Joomla! Connect')]"));
		$this->jPrint ("Change Joomla! Connect to different category and check that it is no longer shown\n");
		$this->gotoAdmin();
		$this->changeCategory('Joomla! Connect', 'Newsfeeds', 'Uncategorised');
		$this->gotoSite();
		$this->open($link, 'true');
		$this->assertTrue($this->isElementPresent("//div[@class='list-title'][contains(.,'Joomla! Announcements')]"));
		$this->assertFalse($this->isElementPresent("//div[@class='list-title'][contains(.,'Joomla! Connect')]"));
		$this->jPrint ("Change Joomla! Connect back to Sample Data-Newsfeeds and make sure it is shown\n");
		$this->changeCategory('Joomla! Connect', 'Newsfeeds', 'Sample Data-Newsfeeds');
		$this->gotoSite();
		$this->open($link, 'true');
		$this->assertTrue($this->isElementPresent("//div[@class='list-title'][contains(.,'Joomla! Announcements')]"));
		$this->assertTrue($this->isElementPresent("//div[@class='list-title'][contains(.,'Joomla! Connect')]"));

	}

	function testModuleEnableCache()
	{
		$this->setUp();
		$this->jPrint ("Starting testModuleEnableCache.\n");
		$this->gotoAdmin();
		$this->doAdminLogin();
		$this->jPrint ("Set caching to progressive.\n");
		$this->setCache('on-full');

		$this->jPrint ("Check that login form shown on home page \n");
		$this->gotoSite();
		$this->assertTrue($this->isElementPresent("//form[@id='login-form']"));
		$this->jPrint ("Unpublish login form\n");
		$this->gotoAdmin();
		$this->click("link=Module Manager");
		$this->waitForPageToLoad("30000");
		$this->type("filter_search", "login form");
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");
		$this->click("link=Login Form");
		$this->waitForPageToLoad("30000");
		$this->click("//label[contains(., 'Unpublished')]");
		$this->click("//div[@id='toolbar-save']/button");
		$this->waitForPageToLoad("30000");
		$this->jPrint ("Check that login form not shown on home page\n");
		$this->gotoSite();
		$this->assertFalse($this->isElementPresent("//form[@id='login-form']"));
		$this->jPrint ("Publish login form and check that it is now shown\n");
		$this->gotoAdmin();
		$this->click("link=Module Manager");
		$this->waitForPageToLoad("30000");
		$this->type("filter_search", "login form");
		$this->click("//button[@type='submit']");
		$this->waitForPageToLoad("30000");
		$this->click("link=Login Form");
		$this->waitForPageToLoad("30000");
		$this->click("//label[contains(., 'Published')]");
		$this->click("//div[@id='toolbar-save']/button");
		$this->waitForPageToLoad("30000");
		$this->gotoSite();
		$this->assertTrue($this->isElementPresent("//form[@id='login-form']"));
		$this->gotoAdmin();

		$this->jPrint ("Clear cache files.\n");
		$this->click("link=Clear Cache");
		$this->waitForPageToLoad("30000");
		$this->click("name=checkall-toggle");
		$this->click("//div[@id='toolbar-delete']/button");
		$this->waitForPageToLoad("30000");

		$this->click("name=checkall-toggle");
		$this->click("//div[@id='toolbar-delete']/button");
		$this->waitForPageToLoad("30000");

		$this->jPrint ("Set caching to off.\n");
		$this->setCache('off');
		$this->doAdminLogout();

	}

}