<?php
/**
 * @version		$Id$
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * checks that all menu choices are shown in back end
 */

require_once 'SeleniumJoomlaTestCase.php';

/**
 * @group ControlPanel
 */
class Featured0002Test extends SeleniumJoomlaTestCase
{
	function testOrderDown()
	{
		$this->setUp();
		echo "Starting testOrderDown.\n";
		$this->gotoAdmin();
		$this->doAdminLogin();

		$this->click("link=Main Menu");
		$this->waitForPageToLoad("30000");

		echo "Open Home menu item and change to 0 leading, 7 intro, alpha sort.\n";
		$this->click("//td/a['Home']");
		$this->waitForPageToLoad("30000");
		$this->click("//h3[@id='advanced-options']/a/span");
		$this->type("jform_params_num_leading_articles", "0");
		$this->type("jform_params_num_intro_articles", "7");
		$this->select("jform_params_multi_column_order", "label=Down");
		$this->select("jform_params_orderby_pri", "label=No Order");
		$this->select("jform_params_orderby_sec", "label=Title Alphabetical");
		$this->click("//li[@id='toolbar-save']/a/span");
		$this->waitForPageToLoad("30000");

		echo "Select featured articles in article manager.\n";
		$this->click("link=Article Manager");
		$this->waitForPageToLoad("30000");
		$this->toggleFeatured('Administrator Components');
		$this->toggleFeatured('Archive Module');
		$this->toggleFeatured('Article Categories Module');
		$this->toggleFeatured('Articles Category Module');

		echo "Go to front page and check that articles are in desired order.\n";
		$this->gotoSite();
		$this->assertEquals("Administrator Components", $this->getText("//div[@class='items-row cols-3 row-0']/div[@class='item column-1']/h2"), "Admin Comp should be r0c1");
		$this->assertEquals("Archive Module", $this->getText("//div[@class='items-row cols-3 row-1']/div[@class='item column-1']/h2"), "Archive should be r1c1");
		$this->assertEquals("Article Categories Module", $this->getText("//div[@class='items-row cols-3 row-2']/div[@class='item column-1']/h2"), "Article Categories should be r2c1");
		$this->assertEquals("Articles Category Module", $this->getText("//div[@class='items-row cols-3 row-0']/div[@class='item column-2']/h2"), "Articles Modules should be r0c2");
		$this->assertEquals("Beginners", $this->getText("//div[@class='items-row cols-3 row-1']/div[@class='item column-2']/h2"), "Joomla! should be r1c2");
		$this->assertEquals("Joomla!", $this->getText("//div[@class='items-row cols-3 row-0']/div[@class='item column-3']/h2"), "Beginners should be r0c3");
		$this->assertEquals("Professionals", $this->getText("//div[@class='items-row cols-3 row-1']/div[@class='item column-3']/h2"), "Professionals should be r1c3");
		echo "Go to admin and change sort to reverse alpha.\n";
		$this->gotoAdmin();
		$this->click("link=Main Menu");
		$this->waitForPageToLoad("30000");
		$this->click("//td/a['Home']");
		$this->waitForPageToLoad("30000");
		$this->click("//h3[@id='advanced-options']/a/span");
		$this->select("jform_params_orderby_sec", "label=Title Reverse Alphabetical");
		$this->click("//li[@id='toolbar-save']/a/span");
		$this->waitForPageToLoad("30000");
		echo "Go to front page and check article order.\n";
		$this->gotoSite();
		$this->assertEquals("Upgraders", $this->getText("//div[@class='items-row cols-3 row-0']/div[@class='item column-1']/h2"));
		$this->assertEquals("Professionals", $this->getText("//div[@class='items-row cols-3 row-1']/div[@class='item column-1']/h2"));
		$this->assertEquals("Joomla!", $this->getText("//div[@class='items-row cols-3 row-2']/div[@class='item column-1']/h2"));
		$this->assertEquals("Beginners", $this->getText("//div[@class='items-row cols-3 row-0']/div[@class='item column-2']/h2"));
		$this->assertEquals("Articles Category Module", $this->getText("//div[@class='items-row cols-3 row-1']/div[@class='item column-2']/h2"));
		$this->assertEquals("Article Categories Module", $this->getText("//div[@class='items-row cols-3 row-0']/div[@class='item column-3']/h2"));
		$this->assertEquals("Archive Module", $this->getText("//div[@class='items-row cols-3 row-1']/div[@class='item column-3']/h2"));
		$this->gotoAdmin();
		echo "Go back to article manager and unselect featured articles.\n";
		$this->click("link=Article Manager");
		$this->waitForPageToLoad("30000");
		$this->toggleFeatured('Administrator Components');
		$this->toggleFeatured('Archive Module');
		$this->toggleFeatured('Article Categories Module');
		$this->toggleFeatured('Articles Category Module');
		echo "Change Home menu item params back to original values.\n";
		$this->click("link=Main Menu");
		$this->waitForPageToLoad("30000");
		$this->click("//td/a['Home']");
		$this->waitForPageToLoad("30000");
		$this->click("//h3[@id='advanced-options']/a/span");
		$this->type("jform_params_num_leading_articles", "1");
		$this->type("jform_params_num_intro_articles", "3");
		$this->select("jform_params_multi_column_order", "label=Across");
		$this->select("jform_params_orderby_pri", "label=Use Global");
		$this->select("jform_params_orderby_sec", "label=Featured Articles Order");
		$this->click("//li[@id='toolbar-save']/a/span");
		$this->waitForPageToLoad("30000");
		echo "Go back to site and make sure original articles are in right positions.\n";
		$this->gotoSite();
		$this->assertEquals("Beginners", $this->getText("//div[@class='items-row cols-3 row-0']/div[@class='item column-1']/h2"));
		$this->assertEquals("Upgraders", $this->getText("//div[@class='items-row cols-3 row-0']/div[@class='item column-2']/h2"));
		$this->assertEquals("Professionals", $this->getText("//div[@class='items-row cols-3 row-0']/div[@class='item column-3']/h2"));
		echo "Go back to back end and log out.\n";
		$this->gotoAdmin();
		$this->doAdminLogout();
		echo "Done with featured0002Test\n";
		$this->deleteAllVisibleCookies();
	}

}
